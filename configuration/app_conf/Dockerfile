# ============================================================
# Pure PHP 8.5-FPM image
# No framework — PDO / PostgreSQL, Redis (sessions), opcache
# ============================================================
FROM php:8.5-fpm

WORKDIR /var/www/html

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions \
    /usr/local/bin/install-php-extensions
RUN chmod +x /usr/local/bin/install-php-extensions

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends unzip zip curl postgresql-client; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/*; \
    install-php-extensions pdo_pgsql pgsql mbstring zip opcache redis

COPY configuration/php/php.ini /usr/local/etc/php/conf.d/custom.ini

COPY configuration/app_conf/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]

