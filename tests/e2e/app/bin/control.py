#!/usr/bin/env python3
"""
A test-only control plane for the end-to-end harness.

The behave suite drives penguinControl through a browser, but some of what it
needs to assert lives on the host rather than in a page: the vHost files Apache
was handed, whether Apache accepts them, what the certbot stub was invoked with,
and the state of the system task queue after cron has run.

Rather than reach into the container from outside, the test image runs this on
port 9000. It exists only in the test image and Apache never sees it.

Endpoints:
    POST /reset            drop the DBMS accounts the panel created, re-migrate
                           and re-seed, and mirror the seeded panel users into
                           real Unix users and groups
    POST /cron             run one pass of `artisan cron:run`
    GET  /configtest       apache2ctl configtest
    GET  /file?path=...    read a file, from an allow-list of prefixes
    GET  /ls?path=...      list a directory, same allow-list
    GET  /logs             the tail of storage/logs/laravel.log
    POST /unix-user        create a Unix user and group for a panel user
    GET  /health
"""

import grp
import json
import os
import pwd
import re
import subprocess
import sys
import traceback
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from urllib.parse import urlparse, parse_qs

APP_DIR = os.environ.get('APP_DIR', '/opt/penguinControl')

# Reading files is limited to the places the application legitimately writes to.
READABLE_PREFIXES = (
    '/etc/apache2/sites-available/',
    '/etc/apache2/sites-enabled/',
    '/var/log/apache2/',
    '/var/log/certbot-invocations.log',
    APP_DIR + '/storage/logs/',
    '/home/',
)

USERNAME = re.compile(r'^[a-z_][a-z0-9_-]*$')


def run(cmd, cwd=None):
    p = subprocess.run(cmd, cwd=cwd, capture_output=True, text=True)
    return {
        'command': cmd if isinstance(cmd, str) else ' '.join(cmd),
        'exitcode': p.returncode,
        'stdout': p.stdout,
        'stderr': p.stderr,
    }


def artisan(*args):
    return run(['php', 'artisan', *args], cwd=APP_DIR)


def drop_panel_dbms_accounts():
    """
    Remove the DBMS accounts the panel created for its users.

    Without this they would survive a reset, and a scenario asserting that
    logging in provisions an account would pass on a leftover from an earlier
    one.
    """
    database = os.environ.get('DB_DATABASE', 'penguincontrol')
    sql = (
        "SELECT CONCAT('DROP USER IF EXISTS ', QUOTE(User), '@', QUOTE(Host), ';') "
        "FROM mysql.user "
        "WHERE User LIKE 'pc\\_u%' "
        f"   OR User IN (SELECT username FROM `{database}`.user_info);"
    )
    listing = run(mariadb_root_command(sql))
    statements = [line for line in listing['stdout'].splitlines() if line.strip()]
    if not statements:
        return [listing]
    return [listing, run(mariadb_root_command(' '.join(statements)))]


def mariadb_root_command(sql):
    return [
        'mariadb',
        '-h', os.environ.get('DB_HOST', 'db'),
        '-uroot',
        '-p' + os.environ.get('DB_ROOT_PASSWORD', ''),
        '-N', '-B',
        '-e', sql,
    ]


def seeded_accounts():
    """The panel users the seeder creates, as (username, uid, group, gid, homedir)."""
    sql = (
        'SELECT ui.username, u.uid, g.name, u.gid, u.homedir '
        'FROM user u '
        'JOIN user_info ui ON ui.id = u.user_info_id '
        'LEFT JOIN `group` g ON g.gid = u.gid;'
    )
    out = run([
        'mariadb',
        '-h', os.environ.get('DB_HOST', 'db'),
        '-u', os.environ.get('DB_USERNAME', 'penguincontrol'),
        '-p' + os.environ.get('DB_PASSWORD', ''),
        '-N', '-B',
        os.environ.get('DB_DATABASE', 'penguincontrol'),
        '-e', sql,
    ])
    rows = []
    for line in out['stdout'].splitlines():
        parts = line.split('\t')
        if len(parts) == 5:
            rows.append(parts)
    return rows


def ensure_unix_account(username, uid, group, gid, homedir):
    """
    Mirror a panel user into a real Unix user and group.

    On a real server libnss-mysql serves these straight out of the same tables,
    which is what makes AssignUserID in a generated vHost resolvable. That module
    is no longer packaged (see docs/setup/nss.md), so the harness creates the
    accounts instead -- the point being to prove the generated config is valid
    and loadable, not to re-implement NSS.
    """
    results = []
    if not USERNAME.match(username):
        return [{'command': 'ensure_unix_account', 'exitcode': 1,
                 'stdout': '', 'stderr': 'rejected username ' + username}]

    try:
        existing = grp.getgrnam(group)
        if existing.gr_gid != int(gid):
            return [{'command': 'ensure_unix_account', 'exitcode': 1, 'stdout': '',
                     'stderr': (f'the group {group} already exists at gid {existing.gr_gid}, '
                                f'but the panel expects gid {gid}')}]
    except KeyError:
        results.append(run(['groupadd', '-g', str(gid), group]))

    try:
        pwd.getpwnam(username)
    except KeyError:
        results.append(run([
            'useradd', '-u', str(uid), '-g', str(gid),
            '-d', homedir, '-s', '/bin/bash', '-M', username,
        ]))

    for path in (homedir, os.path.join(homedir, 'logs'), os.path.join(homedir, 'public_html')):
        os.makedirs(path, exist_ok=True)
    results.append(run(['chown', '-R', f'{username}:{group}', homedir]))
    results.append(run(['chmod', '711', homedir]))
    return results


class Handler(BaseHTTPRequestHandler):
    protocol_version = 'HTTP/1.1'

    def log_message(self, fmt, *args):
        pass

    def handle_one_request(self):
        """Answer with the traceback rather than dropping the connection."""
        try:
            super().handle_one_request()
        except Exception:                                          # noqa: BLE001
            trace = traceback.format_exc()
            print(trace, file=sys.stderr)
            try:
                self._send(500, {'error': 'control plane exception', 'traceback': trace})
            except Exception:                                      # noqa: BLE001
                pass

    def _send(self, status, payload):
        body = json.dumps(payload, indent=1).encode()
        self.send_response(status)
        self.send_header('Content-Type', 'application/json')
        self.send_header('Content-Length', str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def _readable(self, path):
        return any(path.startswith(prefix) for prefix in READABLE_PREFIXES)

    def do_GET(self):
        url = urlparse(self.path)
        query = parse_qs(url.query)

        if url.path == '/health':
            return self._send(200, {'ok': True})

        if url.path == '/configtest':
            return self._send(200, run(['apache2ctl', 'configtest']))

        if url.path == '/logs':
            log = os.path.join(APP_DIR, 'storage/logs/laravel.log')
            if not os.path.exists(log):
                return self._send(200, {'contents': ''})
            with open(log, errors='replace') as fh:
                return self._send(200, {'contents': fh.read()[-20000:]})

        if url.path in ('/file', '/ls'):
            path = (query.get('path') or [''])[0]
            if not self._readable(path):
                return self._send(403, {'error': 'path not permitted', 'path': path})
            if url.path == '/ls':
                if not os.path.isdir(path):
                    return self._send(404, {'error': 'not a directory', 'path': path})
                return self._send(200, {'entries': sorted(os.listdir(path))})
            if not os.path.exists(path):
                return self._send(404, {'error': 'not found', 'path': path})
            with open(path, errors='replace') as fh:
                return self._send(200, {'contents': fh.read()})

        return self._send(404, {'error': 'unknown endpoint', 'path': url.path})

    def do_POST(self):
        url = urlparse(self.path)
        length = int(self.headers.get('Content-Length') or 0)
        raw = self.rfile.read(length) if length else b''
        payload = json.loads(raw) if raw else {}

        if url.path == '/reset':
            # Before the schema goes, while user_info can still be read
            steps = drop_panel_dbms_accounts()
            steps.append(artisan('migrate:fresh', '--seed', '--force'))

            for username, uid, group, gid, homedir in seeded_accounts():
                steps.extend(ensure_unix_account(username, uid, group, gid, homedir))

            # Start each scenario from a clean Apache configuration
            for directory in ('/etc/apache2/sites-available', '/etc/apache2/sites-enabled'):
                for entry in os.listdir(directory):
                    if entry.startswith('VHOST_'):
                        os.unlink(os.path.join(directory, entry))
            steps.append(run(['apachectl', '-k', 'graceful']))

            for path in ('/var/log/certbot-invocations.log',
                         os.path.join(APP_DIR, 'storage/logs/laravel.log')):
                if os.path.exists(path):
                    os.unlink(path)

            failed = [s for s in steps if s.get('exitcode')]
            return self._send(200 if not failed else 500,
                              {'steps': steps, 'ok': not failed})

        if url.path == '/cron':
            return self._send(200, artisan('cron:run'))

        if url.path == '/unix-user':
            return self._send(200, {'steps': ensure_unix_account(
                payload['username'], payload['uid'], payload['group'],
                payload['gid'], payload['homedir'])})

        return self._send(404, {'error': 'unknown endpoint', 'path': url.path})


if __name__ == '__main__':
    ThreadingHTTPServer(('0.0.0.0', 9000), Handler).serve_forever()
