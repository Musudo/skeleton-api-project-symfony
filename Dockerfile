#syntax=docker/dockerfile:1

# FrankenPHP 1.x on PHP 8.4 — pinned to honor the project's exact PHP version.
# (Tags follow dunglas/frankenphp:<frankenphp-version>-php<php-version>; "1-php8.4"
#  tracks the latest 8.4 patch on the FrankenPHP 1.x line.)
FROM dunglas/frankenphp:1-php8.4 AS frankenphp_upstream

# ---------- Base: shared by dev and prod ----------
FROM frankenphp_upstream AS frankenphp_base

SHELL ["/bin/bash", "-euxo", "pipefail", "-c"]
WORKDIR /app

# install-php-extensions (bundled in the image) pulls the native libs for each ext.
#   pdo_pgsql -> PostgreSQL   amqp -> RabbitMQ   redis -> Valkey (Redis protocol)
RUN <<-EOF
	apt-get update
	apt-get install -y --no-install-recommends file git
	install-php-extensions \
		@composer \
		amqp \
		apcu \
		intl \
		opcache \
		pdo_pgsql \
		redis \
		zip
	rm -rf /var/lib/apt/lists/*
EOF

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"

COPY --link frankenphp/conf.d/10-app.ini $PHP_INI_DIR/app.conf.d/
COPY --link --chmod=755 frankenphp/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY --link frankenphp/Caddyfile /etc/frankenphp/Caddyfile

ENTRYPOINT ["docker-entrypoint"]

# Liveness via Caddy's admin metrics endpoint.
HEALTHCHECK --start-period=60s CMD php -r 'exit(false === @file_get_contents("http://localhost:2019/metrics", context: stream_context_create(["http" => ["timeout" => 5]])) ? 1 : 0);'
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]

# ---------- Dev ----------
FROM frankenphp_base AS frankenphp_dev

ENV APP_ENV=dev
ENV XDEBUG_MODE=off
# Restart the worker on file changes (hot reload) — see --watch in the dev CMD below.
ENV FRANKENPHP_WORKER_CONFIG=watch

RUN <<-EOF
	mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
	install-php-extensions xdebug
EOF

COPY --link frankenphp/conf.d/20-app.dev.ini $PHP_INI_DIR/app.conf.d/

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile", "--watch"]

# ---------- Prod builder: install vendors + warm the app, then discard ----------
FROM frankenphp_base AS frankenphp_prod_builder

ENV APP_ENV=prod
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY --link frankenphp/conf.d/20-app.prod.ini $PHP_INI_DIR/app.conf.d/

# Copy only the manifests first so vendor install is cached unless they change.
COPY --link composer.* symfony.* ./
RUN composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

COPY --link --exclude=frankenphp/ . ./

RUN <<-EOF
	mkdir -p var/cache var/log
	composer dump-autoload --classmap-authoritative --no-dev
	composer dump-env prod
	composer run-script --no-dev post-install-cmd
	chmod +x bin/console
	chmod -R g=u var
	sync
EOF

# ---------- Prod runtime ----------
FROM frankenphp_base AS frankenphp_prod

ENV APP_ENV=prod
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY --link frankenphp/conf.d/20-app.prod.ini $PHP_INI_DIR/app.conf.d/

# Baked-in application: optimized autoloader, dumped env, no dev deps.
COPY --from=frankenphp_prod_builder --link /app /app
RUN chown -R www-data:www-data /app/var
USER www-data