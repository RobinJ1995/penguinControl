# Web server and application

Tested against Debian 13 (trixie) and Ubuntu 26.04, with Apache 2.4 and PHP 8.4.

## Requirements

* PHP **8.4** or newer, with the `mbstring`, `intl`, `xml`, `curl`, `zip`, `gd`
  and `pdo_mysql` extensions
* **MariaDB 10.5** or newer (developed and tested against 12.3)
* Apache 2.4 with `mod_php` — see the note on mpm-itk below
* Composer, and Node.js 20 or newer for the asset build

## Packages

```
apt install apache2 libapache2-mpm-itk libapache2-mod-php \
            php-bz2 php-curl php-imagick php-apcu php-mbstring php-intl \
            php-xml php-zip php-gd php-mysql \
            composer nodejs npm
a2enmod rewrite
```

Two packages that older versions of these instructions listed are gone:

* `php-json` was merged into PHP core in 8.0.
* `php-geoip` has no PHP 8 build and is no longer packaged. Use
  `php-maxminddb` with MaxMind's GeoIP2 databases if you need geolocation.

### Why mpm-itk

The vHosts penguinControl generates use `AssignUserID`, so that each site runs
as its owner, and `php_admin_value open_basedir`, so that each site is confined
to its own directories. Both require **mpm-itk with mod_php**: `AssignUserID`
is an mpm-itk directive, and `php_admin_value` is only honoured by mod_php.
`mpm_event` with PHP-FPM will not work without replacing that design.

`libapache2-mpm-itk` is still packaged in Debian 13 (2.4.7-04-2) and Ubuntu
26.04. It requires the prefork MPM, which installing it selects.

## Application

```
git clone <this repository> /opt/penguinControl
cd /opt/penguinControl
composer install --no-dev --optimize-autoloader
npm ci && npm run build
cp .env.example .env
php artisan key:generate
```

Create the database and its account, then enter the credentials in `.env`. The
account needs `CREATE USER` and `GRANT OPTION` in addition to full rights on
its own schema, because the panel manages each user's DBMS account for them:

```sql
CREATE DATABASE `penguincontrol` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'penguincontrol'@'localhost' IDENTIFIED BY '***********';
GRANT ALL PRIVILEGES ON `penguincontrol`.* TO 'penguincontrol'@'localhost';
GRANT CREATE USER ON *.* TO 'penguincontrol'@'localhost' WITH GRANT OPTION;
```

Then create the schema:

```
php artisan migrate --force
php artisan db:seed --force   # optional: global limits, an admin and a plain user
```

`db:seed` creates the global `user_limit` row that the panel needs in order to
render a user's start page. If you skip the seeder, insert one by hand.

Review `.env` before going live: every feature switch documented there is on by
default except `WEBSITE`.

## Directories

These have to exist and be writable by the web server user:

* `storage/` and `bootstrap/cache/` — the framework's own writable paths
* `public/export/` — where the billing CSV export is written
* `/var/log/apache2/vhost/` — where generated vHosts send their access logs.
  The panel creates this if it can, but Apache refuses to start if a
  `CustomLog` directory is missing, so create it up front.

Set the document root of the panel's own vHost to `/opt/penguinControl/public`,
and allow `.htaccess` overrides there (or inline the rules from
`public/.htaccess`). `mod_rewrite` and `mod_negotiation` are required.

## Privileges

The panel writes Apache vHost files **synchronously, from HTTP requests**
(`App\Models\Vhost::save()`), so the web server user needs write access to
`/etc/apache2/sites-available` and `/etc/apache2/sites-enabled`.

Everything else privileged — creating home directories from `/etc/skel`,
`chown`, reloading Apache, obtaining certificates — happens in system tasks,
which the cron job below drains. That job must run **as root**; nothing in the
codebase escalates on its own.

Add the cron job:

```
* * * * * cd /opt/penguinControl/ && php artisan cron:run -v 2>/dev/null
```

## Let's Encrypt

The old instructions used `ppa:certbot/certbot` and `python-certbot-apache`.
That PPA has been removed and the package was Python 2; use the distribution's
own build:

```
apt install certbot python3-certbot-apache
certbot   # Go through setup
```

Renewal is handled by the `certbot.timer` systemd unit the package installs.
On a system without it, add:

```
0 10 * * * certbot renew -n >/dev/null 2>&1
```

penguinControl invokes `certbot --apache -n -d <host> ...` from the
`vhost_obtain_certificate` system task, so `certbot` must be on root's `PATH`
and the Apache authenticator plugin must be installed.
