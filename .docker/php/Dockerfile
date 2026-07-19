FROM php:8.3-fpm-bookworm

ARG APP_USER=laravel
ARG APP_UID=1000

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_mysql \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY .docker/php/php.ini /usr/local/etc/php/conf.d/99-app.ini
COPY .docker/php/entrypoint.sh /usr/local/bin/docker-entrypoint

RUN useradd --uid ${APP_UID} --create-home --shell /bin/bash ${APP_USER} \
    && chmod +x /usr/local/bin/docker-entrypoint

WORKDIR /var/www/html

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]
