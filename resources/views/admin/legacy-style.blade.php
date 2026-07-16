<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --accent:       #0ea5e9;
        --accent-dark:  #0284c7;
        --accent-2:     #38bdf8;
        --accent-glow:  rgba(14,165,233,0.18);
        --text:         #0f172a;
        --muted:        #64748b;
        --border:       #e2e8f0;
        --surface:      #ffffff;
        --sidebar-w:    240px;
        --success:      #10b981;
        --warning:      #f59e0b;
        --danger:       #ef4444;
    }

    html, body { height: 100%; }

    body {
        font-family: 'Roboto', sans-serif;
        background: linear-gradient(160deg, #e0f2fe 0%, #f0fdf4 50%, #f0f9ff 100%);
        color: var(--text);
        min-height: 100vh;
    }

    /* ── Sidebar ── */
    .sidebar {
        position: fixed; top: 0; left: 0;
        width: var(--sidebar-w); height: 100vh;
        background: var(--surface);
        border-right: 1px solid var(--border);
        display: flex; flex-direction: column;
        z-index: 100;
        box-shadow: 2px 0 16px rgba(14,165,233,0.07);
        transition: transform 0.3s;
    }
    .sidebar-brand {
        padding: 22px 20px 18px;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; gap: 11px;
    }
    .sidebar-logo {
        width: 36px; height: 36px;
        background: linear-gradient(135deg, var(--accent), var(--accent-2));
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 17px;
        box-shadow: 0 4px 12px var(--accent-glow);
        flex-shrink: 0;
    }
    .sidebar-brand-text { font-weight: 700; font-size: 0.9375rem; color: var(--text); line-height: 1.2; }
    .sidebar-brand-text span { display: block; font-size: 0.7rem; font-weight: 400; color: var(--muted); }
    .sidebar-nav { flex: 1; padding: 14px 10px; overflow-y: auto; }
    .nav-section-label {
        font-size: 0.68rem; font-weight: 700; letter-spacing: 0.08em;
        text-transform: uppercase; color: #94a3b8; padding: 10px 10px 6px;
    }
    .nav-item {
        display: flex; align-items: center; gap: 10px;
        padding: 9px 12px; border-radius: 9px;
        font-size: 0.875rem; font-weight: 500; color: var(--muted);
        text-decoration: none; transition: background 0.15s, color 0.15s;
        margin-bottom: 2px;
    }
    .nav-item:hover { background: #f0f9ff; color: var(--accent); }
    .nav-item.active {
        background: linear-gradient(135deg, rgba(14,165,233,0.12), rgba(56,189,248,0.08));
        color: var(--accent-dark); font-weight: 700;
    }
    .sidebar-footer { padding: 14px 10px; border-top: 1px solid var(--border); }
    .user-row {
        display: flex; align-items: center; gap: 10px;
        padding: 8px 10px; border-radius: 9px; transition: background 0.15s;
    }
    .user-row:hover { background: #f0f9ff; cursor: default; }
    .avatar {
        width: 34px; height: 34px;
        background: linear-gradient(135deg, var(--accent), var(--accent-2));
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 13px; font-weight: 700; flex-shrink: 0;
    }
    .user-info { flex: 1; min-width: 0; }
    .user-name { font-size: 0.8125rem; font-weight: 600; color: var(--text); }
    .user-role { font-size: 0.7rem; color: var(--muted); }
    .logout-btn {
        display: flex; align-items: center; gap: 8px;
        width: 100%; padding: 8px 10px; margin-top: 6px;
        background: none; border: 1px solid #fecaca; border-radius: 9px;
        color: var(--danger); font-family: 'Roboto', sans-serif;
        font-size: 0.8125rem; font-weight: 500; cursor: pointer;
        transition: background 0.15s, border-color 0.15s; text-decoration: none;
    }
    .logout-btn:hover { background: #fef2f2; border-color: var(--danger); color: var(--danger); }

    /* ── Main layout ── */
    .main-wrap { margin-left: var(--sidebar-w); min-height: 100vh; display: flex; flex-direction: column; }

    /* ── Topbar ── */
    .topbar {
        position: sticky; top: 0; z-index: 50;
        background: rgba(255,255,255,0.85); backdrop-filter: blur(12px);
        border-bottom: 1px solid var(--border);
        padding: 13px 28px;
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
    }
    .topbar-title { font-size: 1rem; font-weight: 700; color: var(--text); }
    .topbar-sub { font-size: 0.78rem; color: var(--muted); font-weight: 400; }
    .topbar-right { display: flex; align-items: center; gap: 10px; }

    .btn-primary-custom {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 16px;
        background: linear-gradient(135deg, var(--accent), var(--accent-2));
        border: none; border-radius: 9px; color: #fff;
        font-family: 'Roboto', sans-serif; font-size: 0.8375rem; font-weight: 700;
        cursor: pointer; text-decoration: none;
        transition: transform 0.15s, box-shadow 0.2s;
        box-shadow: 0 3px 12px var(--accent-glow); white-space: nowrap;
    }
    .btn-primary-custom:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(14,165,233,0.3); color: #fff; }

    /* ── Page content ── */
    .page-content { padding: 24px 28px 40px; }

    /* ── Section card ── */
    .section-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: 16px; padding: 22px 24px;
        box-shadow: 0 2px 8px rgba(14,165,233,0.06);
        margin-bottom: 22px;
    }
    .section-header {
        display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;
    }
    .section-title { font-size: 0.9375rem; font-weight: 700; color: var(--text); }

    /* ── Table ── */
    .custom-table { width: 100%; border-collapse: collapse; }
    .custom-table thead tr { border-bottom: 2px solid var(--border); }
    .custom-table thead th {
        padding: 9px 14px; font-size: 0.72rem; font-weight: 700;
        letter-spacing: 0.06em; text-transform: uppercase; color: #94a3b8; white-space: nowrap;
    }
    .custom-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background 0.15s; }
    .custom-table tbody tr:hover { background: #f8fbff; }
    .custom-table tbody tr:last-child { border-bottom: none; }
    .custom-table td { padding: 13px 14px; font-size: 0.875rem; color: var(--text); vertical-align: middle; }

    /* ── Badges ── */
    .cat-badge {
        display: inline-flex; align-items: center; padding: 3px 10px;
        border-radius: 20px; font-size: 0.75rem; font-weight: 600; white-space: nowrap;
    }
    .cat-badge.default { background: #f1f5f9; color: var(--muted); }

    .status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;
    }
    .status-badge::before { content:''; width:6px; height:6px; border-radius:50%; background:currentColor; }
    .status-badge.published { background: rgba(16,185,129,0.1); color: #059669; }
    .status-badge.draft     { background: #f1f5f9; color: var(--muted); }
    .status-badge.archived  { background: rgba(245,158,11,0.1); color: #b45309; }

    /* ── Action buttons ── */
    .action-btns { display: flex; gap: 6px; }
    .act-btn {
        width: 30px; height: 30px; border-radius: 7px;
        display: flex; align-items: center; justify-content: center;
        border: 1px solid var(--border); background: var(--surface);
        cursor: pointer; transition: background 0.15s, border-color 0.15s, color 0.15s;
        color: var(--muted); text-decoration: none;
    }
    .act-btn.edit:hover   { background: #eff6ff; border-color: #bfdbfe; color: #2563eb; }
    .act-btn.delete:hover { background: #fef2f2; border-color: #fecaca; color: var(--danger); }

    /* ── Form card ── */
    .form-group { margin-bottom: 18px; }
    .form-label { display: block; font-size: 0.8125rem; font-weight: 500; color: #334155; margin-bottom: 7px; }
    .form-label .required { color: var(--danger); margin-left: 2px; }
    .form-control-custom {
        width: 100%; background: #f8fafc; border: 1.5px solid var(--border);
        border-radius: 10px; padding: 10px 14px;
        font-size: 0.9rem; font-family: 'Roboto', sans-serif; color: var(--text);
        outline: none; transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-control-custom:focus { background: #fff; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(14,165,233,0.15); }
    .form-control-custom.textarea { min-height: 110px; resize: vertical; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

    /* ── Alert flash ── */
    .flash-msg {
        padding: 12px 16px; border-radius: 10px; font-size: 0.875rem;
        display: flex; align-items: center; gap: 8px; margin-bottom: 18px;
    }
    .flash-msg.success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.25); color: #065f46; }
    .flash-msg.error   { background: #fef2f2; border: 1px solid #fecaca; color: var(--danger); }

    /* ── Stat cards (dashboard) ── */
    .stat-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 16px; margin-bottom: 22px; }
    .stat-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: 16px; padding: 22px 22px 18px;
        display: flex; align-items: flex-start; gap: 16px;
        box-shadow: 0 2px 8px rgba(14,165,233,0.06);
        transition: box-shadow 0.2s, transform 0.2s;
    }
    .stat-card:hover { box-shadow: 0 6px 24px rgba(14,165,233,0.12); transform: translateY(-2px); }
    .stat-icon {
        width: 46px; height: 46px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .stat-icon.blue  { background: rgba(14,165,233,0.12); color: var(--accent); }
    .stat-icon.green { background: rgba(16,185,129,0.12); color: var(--success); }
    .stat-icon.amber { background: rgba(245,158,11,0.12);  color: var(--warning); }
    .stat-label { font-size: 0.78rem; font-weight: 500; color: var(--muted); margin-bottom: 4px; }
    .stat-value { font-size: 1.625rem; font-weight: 700; color: var(--text); line-height: 1.1; }
    .stat-trend { font-size: 0.72rem; font-weight: 500; margin-top: 4px; }
    .stat-trend.up   { color: var(--success); }
    .stat-trend.down { color: var(--danger); }

    /* ── Hamburger ── */
    .hamburger {
        display: none; background: none; border: none;
        cursor: pointer; color: var(--muted); padding: 4px;
    }

    /* ── Pagination ── */
    .pagination-wrap { display: flex; justify-content: center; gap: 6px; margin-top: 20px; flex-wrap: wrap; }
    .page-btn {
        min-width: 34px; height: 34px; padding: 0 10px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 8px; border: 1px solid var(--border);
        background: var(--surface); font-size: 0.8rem; font-weight: 500;
        color: var(--muted); text-decoration: none; transition: all 0.15s; cursor: pointer;
    }
    .page-btn:hover { background: #f0f9ff; color: var(--accent); border-color: var(--accent-2); }
    .page-btn.active { background: var(--accent); color: #fff; border-color: var(--accent); }
    .page-btn.disabled { opacity: 0.4; pointer-events: none; }

    /* ── SweetAlert2 custom ── */
    .swal-popup { font-family: 'Roboto', sans-serif !important; border-radius: 18px !important; padding: 28px 24px !important; }
    .swal-title  { font-size: 1.125rem !important; font-weight: 700 !important; color: #0f172a !important; }
    .swal-text   { font-size: 0.875rem !important; color: #64748b !important; line-height: 1.6 !important; }
    .swal-btn-confirm, .swal-btn-cancel {
        font-family: 'Roboto', sans-serif !important; font-size: 0.875rem !important;
        font-weight: 600 !important; border-radius: 9px !important; padding: 9px 20px !important; box-shadow: none !important;
    }
    .swal2-actions { gap: 8px !important; }
    .swal2-timer-progress-bar { background: #0ea5e9 !important; }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .sidebar { transform: translateX(-100%); }
        .sidebar.open { transform: translateX(0); }
        .main-wrap { margin-left: 0; }
        .hamburger { display: flex; }
        .stat-grid { grid-template-columns: 1fr; }
        .page-content { padding: 16px 14px 32px; }
        .topbar { padding: 12px 16px; }
        .form-row { grid-template-columns: 1fr; }
    }
</style>
