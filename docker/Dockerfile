# syntax=docker/dockerfile:1.7
# The dev image: FrankenPHP (PHP 8.5) with Composer. vendor/bin/phemc downloads the phemc binary of
# the installed phem/phem release on its first run.
ARG FRANKENPHP_IMAGE=dunglas/frankenphp:1-php8.5

FROM ${FRANKENPHP_IMAGE}
RUN apt-get update \
 && apt-get install -y --no-install-recommends git unzip \
 && rm -rf /var/lib/apt/lists/*
# The `format` and `locale` props of Meter and Progress (ValueFormat) use NumberFormatter.
RUN install-php-extensions intl
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /app
