# syntax=docker/dockerfile:1.7
# The images of the phem/ui-base demo, served by FrankenPHP in worker mode. The build context is
# the root of the repository: the demo requires phem/ui-base from it (a Composer path repository),
# and phem/phem from Packagist. vendor/bin/phemc runs the phemc binary of that phem/phem release.
#
# The last stage is the production image. It copies demo/.env.local (not in git) with its
# APP_SECRET. `make demo` writes it.
#
#   docker build -f demo/etc/Dockerfile -t phem-ui-base-demo .
#   docker run --rm -p 8080:8080 phem-ui-base-demo
#
# The `watch` stage serves the repository of a bind mount at /app in the dev environment, and
# restarts the workers after a change (etc/watch). `make demo-watch` runs it.
#
#   docker build -f demo/etc/Dockerfile --target watch -t phem-ui-base-demo-watch .
#   docker run --rm -p 8080:8080 -v "$PWD:/app" phem-ui-base-demo-watch
ARG FRANKENPHP_IMAGE=dunglas/frankenphp:1-php8.5

# --- FrankenPHP with Composer ---
FROM ${FRANKENPHP_IMAGE} AS base
RUN apt-get update \
 && apt-get install -y --no-install-recommends git unzip \
 && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# --- The demo in the dev environment, on the repository of a bind mount ---
FROM base AS watch
ENV APP_ENV=dev APP_DEBUG=1
WORKDIR /app/demo
CMD ["etc/watch"]

# --- The demo in production ---
FROM base
ENV APP_ENV=prod APP_DEBUG=0
COPY . /app
WORKDIR /app/demo
# composer.lock is not in git, so the build resolves the packages. The cache:warmup step comes
# after phemc build, because composer update boots the kernel before the components exist.
RUN composer update --no-dev --optimize-autoloader --no-interaction --no-progress \
 && bin/console importmap:install \
 && vendor/bin/phemc build src --examples-of phem/ui-base --no-warmup \
 && bin/console tailwind:build --minify \
 && rm -rf var/cache/prod \
 && bin/console cache:warmup \
 && bin/console asset-map:compile
COPY demo/etc/php-prod.ini /usr/local/etc/php/conf.d/zz-demo.ini
CMD ["frankenphp", "run", "--config", "etc/Caddyfile"]
