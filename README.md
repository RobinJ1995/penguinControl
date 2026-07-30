# 🐧control

Hosting control panel for Ubuntu/Debian web servers, written in PHP with the
Laravel framework.

Users manage their own Apache vHosts, FTP accounts, mail domains, mailboxes and
forwards, and databases; staff manage the users. Rather than driving daemons
through their own tooling, penguinControl shares its schema with them: glibc
serves Unix users, groups and shadow entries out of these tables through an NSS
module, Postfix reads the mail tables directly, and Apache vHost files are
generated from the `vhost` table. Privileged work is queued in `system_task` and
drained by a cron job.

Originally written for [a student organisation at Thomas More
Kempen](https://sinners.be/). Some of its needs are still baked in — see
`ProblemSolver` and the default vHost names in `StaffMaintenanceController`.

## Requirements

* PHP 8.4 or newer, Laravel 13
* MariaDB 10.5 or newer
* Apache 2.4 with mpm-itk and mod_php — the generated vHosts use `AssignUserID`
  and `php_admin_value`, neither of which works with mpm_event and PHP-FPM
* Composer, and Node.js 20 or newer for the asset build

## Installing

`docs/setup/apache.md` covers the web server, the application and Let's Encrypt.
`docs/setup/nss.md` covers serving Unix users and groups from the database, and
`docs/setup/mail.md` covers Postfix and Dovecot.

In short:

```
composer install --no-dev --optimize-autoloader
npm ci && npm run build
cp .env.example .env && php artisan key:generate
php artisan migrate --force
php artisan db:seed --force      # global limits, an admin and a plain user
```

Then add the cron job that drains the system task queue, as root:

```
* * * * * cd /opt/penguinControl/ && php artisan cron:run -v 2>/dev/null
```

## Testing

```
vendor/bin/phpunit
docker compose -f tests/e2e/docker-compose.yml up --build --exit-code-from runner
```

The first is a fast smoke suite against SQLite. The second stands up a real
install — Debian 13, Apache with mpm-itk and mod_php, cron, MariaDB 12.3 — and
drives it with behave and Playwright. See `tests/e2e/README.md`.

## Contributors

* [Robin Jacobs](https://github.com/RobinJ1995)
* [Karlos van Hest](https://github.com/karlosvh)
* [Denis Bourne](https://github.com/Dieseled-UP)
