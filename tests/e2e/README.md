# End-to-end tests

These tests drive penguinControl through a real browser against a real
installation, on the stack `docs/setup/apache.md` describes: Debian 13, Apache
2.4 with mpm-itk and mod_php, cron draining the system task queue, and MariaDB
over the network.

That fidelity is the point. penguinControl is mostly glue between server
components -- it writes Apache vHost files, issues `GRANT` statements, and queues
privileged work for a root cron job -- so testing it against stand-ins would
leave exactly the parts most likely to break on a newer distribution untested.

## Running

```
docker compose -f tests/e2e/docker-compose.yml up --build --exit-code-from runner
```

The runner's exit code is the suite's result. JUnit XML lands in
`tests/e2e/reports/`.

To iterate on a single feature:

```
docker compose -f tests/e2e/docker-compose.yml run --rm runner behave features/vhost.feature
```

### Building behind a TLS-intercepting proxy

Both images accept an optional CA through a build secret, for networks where
outbound HTTPS is re-terminated:

```
docker build --secret id=extra_ca,src=/path/to/ca-bundle.crt \
  -f tests/e2e/app/Dockerfile -t penguincontrol-e2e-app .
docker build --secret id=extra_ca,src=/path/to/ca-bundle.crt \
  -f tests/e2e/runner/Dockerfile -t penguincontrol-e2e-runner tests/e2e
docker compose -f tests/e2e/docker-compose.yml up --no-build --exit-code-from runner
```

Without the secret the step is skipped and the build behaves normally.

### In CI

`.github/workflows/tests.yml` runs this suite on every pull request. It builds the
two images with buildx against the GitHub Actions layer cache and then runs the
same `--no-build` command as above, so the local and CI paths do not diverge.

### Keep the dependency installs above the source copy

`app/Dockerfile` installs the npm and Composer dependencies from the lockfiles
*before* it copies the application in, and generates the optimised autoloader
after. This is load-bearing, not stylistic: with `composer install` below
`COPY . .` a one-line change to a controller re-downloads the whole framework,
which cost about five minutes per build here and would cost it on every pull
request. Keeping the order, a source-only rebuild is around fifteen seconds.

Relatedly, `.dockerignore` excludes `/vendor`. Without that, a development
`vendor/` on the host is copied straight into an image that runs with
`APP_ENV=production`, dev dependencies and all.

## What the containers are

**db** -- `mariadb:12.3`, started with `NO_AUTO_CREATE_USER` in `sql_mode`, which
is the default on this version and the reason `DatabaseCredentials` has to issue
`CREATE USER IF NOT EXISTS` before it can `GRANT`. The panel's own account is
granted `GRANT OPTION`, as `docs/setup/apache.md` says a real install requires.

**panel** -- the application, installed at `/opt/penguinControl` exactly as documented:
`composer install`, `npm run build`, `migrate`, `db:seed`, the crontab line from
the setup instructions, and Apache in the foreground. It runs with `APP_ENV`
set to production, which matters: `DatabaseCredentials` skips managing DBMS
accounts entirely when the environment is `local`.

**runner** -- Python with behave and Playwright driving Chromium.

The panel's service is called `panel` rather than `app` on purpose: `.app` is an
HSTS-preloaded TLD, so Chromium rewrites `http://app/` to `https://app/` and every
request is refused.

## The three things a container cannot do honestly

Each of these is a shim, and each is narrow enough that the code path under test
is still the real one.

**`systemctl`** (`app/bin/systemctl`). There is no init system in a container.
The shim maps `reload apache2` to `apachectl -k graceful` and `is-active apache2`
to the pidfile, so `App\ServiceApache::reload()` and `SystemService::status()`
run their real code and really do act on Apache. Anything else is a no-op.

**`certbot`** (`app/bin/certbot`). A container cannot complete an ACME challenge.
The stub appends its arguments to `/var/log/certbot-invocations.log` and exits 0,
so the command the panel builds and the task plumbing that reaches it are both
covered; actually obtaining a certificate is not.

**Unix accounts.** `AssignUserID` in a generated vHost names a user the operating
system has to be able to resolve. On a real server `libnss-mysql` serves that
from the same tables the panel writes, but that module has been removed from
Debian and Ubuntu (see `docs/setup/nss.md`), so the harness creates matching Unix
users and groups from the seeded rows instead. Without this Apache would refuse
to load any generated vHost, and the interesting assertion -- that the generated
configuration is valid *and* loadable on current Apache -- could not be made.

## The test control plane

`app/bin/control.py` listens on port 9000 inside the panel container and exists
only in the test image; Apache never sees it. behave uses it to reset state
between scenarios and to inspect what the panel wrote to the host: the generated
vHost files, `apache2ctl configtest`, the certbot log, and `laravel.log` when a
scenario fails. Reads are limited to an allow-list of paths.

Every scenario begins with `POST /reset`, which re-migrates and re-seeds, mirrors
the seeded users into Unix accounts, removes any generated vHosts, and reloads
Apache -- so scenarios cannot leak into one another.

## Coverage

| Feature | What it establishes |
| --- | --- |
| `login.feature` | The custom `crypt()` login, and that a failed attempt re-renders the form with 200 and an alert rather than redirecting |
| `vhost.feature` | The generated vHost's contents, its symlink, that `apache2ctl configtest` passes, and that `open_basedir` no longer contains the stray separator |
| `mariadb_credentials.feature` | Per-user DBMS accounts are created, authenticate with the panel password, and are granted only their own databases on MariaDB 12.3 |
| `system_task.feature` | `artisan cron:run` drains the queue: document roots are created and chowned, Apache is reloaded, disk usage is calculated |
| `ftp_and_mail.feature` | FTP and mail rows are written in the shape the FTP daemon and Postfix read, including `mail_forward.mail_domain_id` |
| `staff.feature` | The system check runs, a registration waits for validation, and the billing log starts empty |
| `certbot.feature` | The certificate request path reaches certbot |
