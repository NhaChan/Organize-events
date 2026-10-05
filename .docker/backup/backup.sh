#!/usr/bin/env bash

set -Eeuo pipefail

BACKUP_ROOT="/backups"
UPLOAD_ROOT="/source/uploads"
BACKUP_TIME="${BACKUP_TIME:-03:00}"
BACKUP_RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-14}"
BACKUP_UID="${BACKUP_UID:-1000}"
BACKUP_GID="${BACKUP_GID:-1000}"
DB_HOST="${DB_HOST:-mysql}"
DB_PORT="${DB_PORT:-3306}"

log() {
    printf '[%s] %s\n' "$(date '+%Y-%m-%d %H:%M:%S %Z')" "$*"
}

validate_config() {
    : "${DB_DATABASE:?DB_DATABASE is required}"
    : "${DB_USERNAME:?DB_USERNAME is required}"
    : "${DB_PASSWORD:?DB_PASSWORD is required}"

    if [[ ! "${BACKUP_TIME}" =~ ^([01][0-9]|2[0-3]):[0-5][0-9]$ ]]; then
        log "BACKUP_TIME must use HH:MM (received: ${BACKUP_TIME})"
        exit 1
    fi

    if [[ ! "${BACKUP_RETENTION_DAYS}" =~ ^[1-9][0-9]*$ ]]; then
        log "BACKUP_RETENTION_DAYS must be a positive integer"
        exit 1
    fi

    if [[ ! "${BACKUP_UID}" =~ ^[0-9]+$ || ! "${BACKUP_GID}" =~ ^[0-9]+$ ]]; then
        log "BACKUP_UID and BACKUP_GID must be numeric"
        exit 1
    fi
}

wait_for_database() {
    local attempt

    for attempt in $(seq 1 30); do
        if MYSQL_PWD="${DB_PASSWORD}" mysqladmin ping \
            --host="${DB_HOST}" \
            --port="${DB_PORT}" \
            --user="${DB_USERNAME}" \
            --silent; then
            return 0
        fi

        log "Waiting for database (${attempt}/30)..."
        sleep 2
    done

    log "Database is unavailable; backup aborted"
    return 1
}

run_backup() {
    local timestamp temp_dir final_dir

    timestamp="$(date '+%Y-%m-%d_%H-%M-%S')"
    temp_dir="${BACKUP_ROOT}/.tmp-${timestamp}-$$"
    final_dir="${BACKUP_ROOT}/${timestamp}"

    mkdir -p "${BACKUP_ROOT}" "${temp_dir}"
    chmod 700 "${BACKUP_ROOT}" "${temp_dir}" 2>/dev/null || true
    chown "${BACKUP_UID}:${BACKUP_GID}" "${BACKUP_ROOT}"

    cleanup_failed_backup() {
        rm -rf -- "${temp_dir}"
    }
    trap cleanup_failed_backup ERR INT TERM

    wait_for_database
    log "Backing up database ${DB_DATABASE}..."
    MYSQL_PWD="${DB_PASSWORD}" mysqldump \
        --host="${DB_HOST}" \
        --port="${DB_PORT}" \
        --user="${DB_USERNAME}" \
        --single-transaction \
        --quick \
        --skip-lock-tables \
        --set-gtid-purged=OFF \
        --skip-masking-policies \
        --routines \
        --events \
        --triggers \
        --hex-blob \
        --no-tablespaces \
        --default-character-set=utf8mb4 \
        "${DB_DATABASE}" | gzip -9 > "${temp_dir}/database.sql.gz"

    log "Backing up uploaded files..."
    if [[ -d "${UPLOAD_ROOT}" ]]; then
        tar -C "${UPLOAD_ROOT}" -czf "${temp_dir}/uploads.tar.gz" .
    else
        tar -czf "${temp_dir}/uploads.tar.gz" --files-from /dev/null
    fi

    {
        printf 'created_at=%s\n' "$(date --iso-8601=seconds)"
        printf 'database=%s\n' "${DB_DATABASE}"
        printf 'timezone=%s\n' "${TZ:-UTC}"
    } > "${temp_dir}/manifest.txt"

    (
        cd "${temp_dir}"
        sha256sum database.sql.gz uploads.tar.gz manifest.txt > SHA256SUMS
    )

    chmod 600 "${temp_dir}"/*
    mv "${temp_dir}" "${final_dir}"
    chown -R "${BACKUP_UID}:${BACKUP_GID}" "${final_dir}"
    trap - ERR INT TERM

    log "Backup completed: ${final_dir}"

    find "${BACKUP_ROOT}" -mindepth 1 -maxdepth 1 -type d \
        ! -name '.tmp-*' -mtime "+${BACKUP_RETENTION_DAYS}" -print \
        -exec rm -rf -- {} +

    find "${BACKUP_ROOT}" -mindepth 1 -maxdepth 1 -type d \
        -name '.tmp-*' -mtime +1 -exec rm -rf -- {} +
}

seconds_until_next_run() {
    local now target today

    now="$(date +%s)"
    today="$(date +%F)"
    target="$(date -d "${today} ${BACKUP_TIME}:00" +%s)"

    if (( target <= now )); then
        target="$(date -d "tomorrow ${BACKUP_TIME}:00" +%s)"
    fi

    printf '%s' "$((target - now))"
}

run_loop() {
    local seconds next_run

    while true; do
        seconds="$(seconds_until_next_run)"
        next_run="$(date -d "+${seconds} seconds" '+%Y-%m-%d %H:%M:%S %Z')"
        log "Next backup: ${next_run}"
        sleep "${seconds}"

        run_backup
    done
}

validate_config

case "${1:-loop}" in
    once)
        run_backup
        ;;
    loop)
        run_loop
        ;;
    *)
        log "Usage: backup.sh [once|loop]"
        exit 1
        ;;
esac
