# syntax=docker/dockerfile:1
# A penguinControl host as docs/setup/apache.md describes one: Debian 13, Apache
# 2.4 with mpm-itk and mod_php, and cron draining the system task queue.
FROM debian:trixie-slim

ENV DEBIAN_FRONTEND=noninteractive \
    APP_DIR=/opt/penguinControl \
    NODE_EXTRA_CA_CERTS=/etc/ssl/certs/ca-certificates.crt

# Building behind a TLS-intercepting proxy (a corporate egress proxy, or a
# sandbox) needs that proxy's CA before apt can fetch anything over HTTPS, and
# the base image ships no trust store at all. Pass one with
#   docker build --secret id=extra_ca,src=/path/to/ca-bundle.crt
# Without the secret this step does nothing and the build proceeds normally.
RUN --mount=type=secret,id=extra_ca,target=/tmp/extra-ca.crt \
    if [ -s /tmp/extra-ca.crt ]; then \
      mkdir -p /etc/ssl/certs /usr/local/share/ca-certificates ; \
      cp /tmp/extra-ca.crt /usr/local/share/ca-certificates/extra-ca.crt ; \
      cp /tmp/extra-ca.crt /etc/ssl/certs/ca-certificates.crt ; \
      chmod 0644 /usr/local/share/ca-certificates/extra-ca.crt /etc/ssl/certs/ca-certificates.crt ; \
      echo 'Acquire::https::CaInfo "/usr/local/share/ca-certificates/extra-ca.crt";' \
        > /etc/apt/apt.conf.d/99-extra-ca ; \
      sed -i 's|http://deb.debian.org|https://deb.debian.org|g' /etc/apt/sources.list.d/debian.sources ; \
    fi

RUN apt-get update \
 && apt-get install -y --no-install-recommends ca-certificates \
 && update-ca-certificates \
 && apt-get install -y --no-install-recommends \
      apache2 libapache2-mpm-itk libapache2-mod-php \
      php-cli php-mysql php-mbstring php-intl php-xml php-zip php-gd php-curl \
      php-bz2 php-apcu \
      composer nodejs npm cron \
      curl mariadb-client python3 unzip git \
 && rm -rf /var/lib/apt/lists/*

# mpm-itk needs prefork, which its package selects; mod_php has to match.
RUN a2enmod rewrite \
 && a2dismod -f mpm_event 2>/dev/null || true \
 && a2enmod mpm_prefork \
 && a2enmod mpm_itk

WORKDIR ${APP_DIR}

# Dependencies first, so that source edits do not invalidate them. The caches are
# dropped in the same layer, or they end up in the image -- composer's alone is
# several gigabytes.
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_CACHE_DIR=/tmp/composer-cache \
    npm_config_cache=/tmp/npm-cache

COPY package.json package-lock.json* ./
RUN npm ci --no-audit --no-fund && rm -rf /tmp/npm-cache

COPY . .

# Normally the dependencies are installed here. A build context that already
# carries vendor/ -- a CI cache, or a network where composer cannot reach
# GitHub -- is taken as-is instead.
RUN if [ -f vendor/autoload.php ]; then \
      echo '==> Using the vendor directory from the build context' ; \
    else \
      composer install --no-dev --no-scripts --no-interaction --prefer-dist \
        && composer clear-cache ; \
    fi \
 && composer dump-autoload --optimize \
 && rm -rf /tmp/composer-cache

# node_modules is only needed to produce public/build, so it goes afterwards.
RUN npm run build && rm -rf node_modules /tmp/npm-cache

# The application drives `systemctl reload apache2` through App\ServiceApache and
# shells out to certbot for certificates. Neither works in a container, so both
# are shimmed -- the reload really does reload Apache, and the certbot stub
# records the arguments it was called with so tests can assert on them.
COPY tests/e2e/app/bin/systemctl /usr/local/bin/systemctl
COPY tests/e2e/app/bin/certbot /usr/local/bin/certbot
COPY tests/e2e/app/bin/control.py /usr/local/bin/control.py
COPY tests/e2e/app/vhost-penguincontrol.conf /etc/apache2/sites-available/000-penguincontrol.conf
COPY tests/e2e/app/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/systemctl /usr/local/bin/certbot /usr/local/bin/entrypoint.sh \
 && a2dissite 000-default \
 && a2ensite 000-penguincontrol

# What docs/setup/apache.md says has to exist and be writable.
RUN mkdir -p /var/log/apache2/vhost public/export storage/framework/cache/data \
 && chown -R www-data:www-data storage bootstrap/cache public/export \
 # The panel writes vHost files straight from HTTP requests, so the web user
 # needs these two directories. This is the documented deployment requirement.
 && chown -R root:www-data /etc/apache2/sites-available /etc/apache2/sites-enabled /var/log/apache2/vhost \
 && chmod -R g+w /etc/apache2/sites-available /etc/apache2/sites-enabled /var/log/apache2/vhost

EXPOSE 80 9000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
