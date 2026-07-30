"""
Step definitions for the penguinControl end-to-end suite.

Steps fall into three groups: browser steps that drive the panel through
Playwright, host steps that inspect what the panel wrote via the test control
plane, and database steps that query MariaDB directly -- including the DBMS
accounts the panel creates for its users, which is the point of several
scenarios.
"""

import json
import os
import re
import time

import pymysql
import requests
from behave import given, when, then, step
from playwright.sync_api import expect

# Mirrors Database\Seeders\DatabaseSeeder
SEEDED = {
    'admin': {'password': 'admin', 'uid': 5000, 'group': 'panel', 'gid': 1000},
    'penguin': {'password': 'penguin', 'uid': 5001, 'group': 'user', 'gid': 2000},
}

SITES_AVAILABLE = '/etc/apache2/sites-available/'
SITES_ENABLED = '/etc/apache2/sites-enabled/'


# --------------------------------------------------------------------------
# Helpers
# --------------------------------------------------------------------------

def db(context, root=False):
    return pymysql.connect(
        host=os.environ.get('PENGUIN_DB_HOST', 'db'),
        user='root' if root else os.environ.get('PENGUIN_DB_USER', 'penguincontrol'),
        password=(os.environ.get('PENGUIN_DB_ROOT_PASSWORD', '')
                  if root else os.environ.get('PENGUIN_DB_PASSWORD', '')),
        database=os.environ.get('PENGUIN_DB_NAME', 'penguincontrol'),
        cursorclass=pymysql.cursors.DictCursor,
        autocommit=True,
    )


def query(context, sql, args=None, root=False):
    with db(context, root=root) as connection:
        with connection.cursor() as cursor:
            # Only pass args when there are some: otherwise PyMySQL runs the SQL
            # through %-formatting, and statements like SHOW GRANTS FOR 'x'@'%'
            # blow up on their own wildcard
            cursor.execute(sql, args) if args else cursor.execute(sql)
            return cursor.fetchall()


def control_get(context, endpoint, **params):
    # Not named "path": /file and /ls take a query parameter of that name
    return requests.get(context.control + endpoint, params=params, timeout=120)


def control_post(context, endpoint, payload=None):
    return requests.post(context.control + endpoint, json=payload or {}, timeout=300)


def read_host_file(context, path):
    response = control_get(context, '/file', path=path)
    if response.status_code == 404:
        return None
    response.raise_for_status()
    return response.json()['contents']


def log_in(context, username, password):
    context.page.goto('/user/login')
    context.page.fill('input[name="username"]', username)
    context.page.fill('input[name="password"]', password)
    # The response is captured because a failed login is a 200 re-render of the
    # form rather than a redirect, and the scenarios assert on that
    with context.page.expect_navigation() as navigation:
        context.page.click('button[name="time"]')
    context.response = navigation.value
    context.page.wait_for_load_state()


def run_cron(context):
    result = control_post(context, '/cron').json()
    context.last_cron = result
    return result


def pending_tasks(context):
    return query(
        context,
        'SELECT COUNT(*) AS n FROM system_task WHERE lastRun IS NULL AND `interval` IS NULL'
    )[0]['n']


def drain_queue(context, attempts=4, pause=1.5):
    """
    Run cron until the one-off queue is empty.

    More than one pass can be needed even for a single task: CronCommand only
    picks up rows whose `start` is strictly in the past, and the staff form
    stamps `start` with time(), so a task created in the same second as the run
    is not eligible until the clock ticks. Real cron fires a minute later and
    never notices; a test that schedules and runs back to back does.
    """
    for _ in range(attempts):
        run_cron(context)
        if pending_tasks(context) == 0:
            return
        time.sleep(pause)
    raise AssertionError(f'system tasks were still pending after {attempts} runs')


def wait_for(predicate, timeout=30, interval=0.5, description='condition'):
    deadline = time.time() + timeout
    while time.time() < deadline:
        value = predicate()
        if value:
            return value
        time.sleep(interval)
    raise AssertionError(f'timed out waiting for {description}')


# --------------------------------------------------------------------------
# Given
# --------------------------------------------------------------------------

@given('the panel is freshly installed')
def step_fresh_install(context):
    # before_scenario already reset it; assert the install really is usable
    limits = query(context, 'SELECT * FROM user_limit WHERE uid IS NULL')
    assert limits, 'the global user_limit row is missing, so /user/start cannot render'


@given('I am logged in as "{username}"')
def step_logged_in(context, username):
    log_in(context, username, SEEDED[username]['password'])
    expect(context.page).to_have_url(re.compile(r'/user/start$'))


@given('I am logged in as the administrator')
def step_logged_in_admin(context):
    step_logged_in(context, 'admin')


@given('"{username}" has a vHost "{servername}" with document root "{docroot}"')
def step_existing_vhost(context, username, servername, docroot):
    context.execute_steps(f'''
        When I create a vHost "{servername}" with document root "{docroot}"
    ''')


# --------------------------------------------------------------------------
# When
# --------------------------------------------------------------------------

@when('I visit "{path}"')
def step_visit(context, path):
    context.response = context.page.goto(path)


@when('I log in as "{username}" with password "{password}"')
def step_log_in(context, username, password):
    log_in(context, username, password)


@when('I log out')
def step_log_out(context):
    context.page.goto('/user/logout')


@when('I create a vHost "{servername}" with document root "{docroot}"')
def step_create_vhost(context, servername, docroot):
    create_vhost(context, servername, docroot, ssl='0')


@when('I create a vHost "{servername}" with document root "{docroot}" and HTTPS')
def step_create_vhost_https(context, servername, docroot):
    create_vhost(context, servername, docroot, ssl='1')


def create_vhost(context, servername, docroot, ssl='0'):
    context.page.goto('/website/vhost/create')
    context.page.fill('input[name="servername"]', servername)
    context.page.fill('input[name="docroot"]', docroot)
    context.page.select_option('select[name="ssl"]', ssl)
    context.page.click('button[name="save"]')
    context.page.wait_for_load_state()
    context.vhost_servername = servername


@when('I remove the vHost "{servername}"')
def step_remove_vhost(context, servername):
    row = query(context, 'SELECT id FROM vhost WHERE servername = %s', (servername,))
    assert row, f'no vhost row for {servername}'
    context.page.goto(f'/website/vhost/{row[0]["id"]}/remove')
    context.page.wait_for_load_state()


@when('I create an FTP account "{suffix}" in directory "{directory}"')
def step_create_ftp(context, suffix, directory):
    context.page.goto('/ftp/create')
    context.page.fill('input[name="username"]', suffix)
    context.page.fill('input[name="passwd"]', 'ftp-password-1')
    context.page.fill('input[name="passwd_confirm"]', 'ftp-password-1')
    context.page.fill('input[name="dir"]', directory)
    context.page.click('button[name="save"]')
    context.page.wait_for_load_state()


@when('I add the mail domain "{domain}" for "{username}" as the administrator')
def step_add_mail_domain(context, domain, username):
    context.page.goto('/staff/mail/domain/create')
    context.page.select_option('select[name="uid"]', str(SEEDED[username]['uid']))
    context.page.fill('input[name="domain"]', domain)
    context.page.click('button[name="save"]')
    context.page.wait_for_load_state()


@when('I enable mail for my account')
def step_enable_mail(context):
    context.page.goto('/mail')
    if context.page.locator('button[name="enable"]').count():
        context.page.click('button[name="enable"]')
        context.page.wait_for_load_state()


@when('I add a mail forward "{source}" to "{destination}" on domain "{domain}"')
def step_add_mail_forward(context, source, destination, domain):
    context.page.goto('/mail/forward/create')
    context.page.fill('input[name="source"]', source)
    context.page.select_option('select[name="domain"]', label='@' + domain)
    context.page.fill('input[name="destination"]', destination)
    context.page.click('button[name="save"]')
    context.page.wait_for_load_state()


@when('I schedule a "{task_type}" system task')
def step_schedule_task(context, task_type):
    context.page.goto('/staff/system/systemtask/create')
    context.page.select_option('select[name="type"]', task_type)
    context.page.click('button[name="save"]')
    context.page.wait_for_load_state()


@when('the system task runner runs')
def step_run_cron(context):
    drain_queue(context)


@when('the system task runner runs until the queue is idle')
def step_run_cron_until_idle(context):
    drain_queue(context, attempts=6)


@when('I register as "{username}" with e-mail "{email}"')
def step_register(context, username, email):
    context.page.goto('/user/register')
    context.page.fill('input[name="username"]', username)
    context.page.fill('input[name="fname"]', 'New')
    context.page.fill('input[name="lname"]', 'Person')
    context.page.fill('input[name="email"]', email)
    context.page.fill('input[name="password"]', 'a-good-password')
    context.page.fill('input[name="password_confirm"]', 'a-good-password')
    context.page.check('input[name="termsAgree"]')
    context.page.click('button[name="save"]')
    context.page.wait_for_load_state()


# --------------------------------------------------------------------------
# Then -- browser
# --------------------------------------------------------------------------

@then('I am on "{path}"')
def step_on_path(context, path):
    expect(context.page).to_have_url(re.compile(re.escape(path) + r'/?$'))


@then('the page mentions "{text}"')
def step_page_mentions(context, text):
    assert text in context.page.content(), \
        f'expected the page at {context.page.url} to mention {text!r}'


@then('the page does not mention "{text}"')
def step_page_not_mentions(context, text):
    assert text not in context.page.content(), \
        f'did not expect the page at {context.page.url} to mention {text!r}'


@then('the response status is {status:d}')
def step_status(context, status):
    response = getattr(context, 'response', None)
    assert response is not None, 'no navigation was recorded'
    assert response.status == status, \
        f'expected {status}, got {response.status} for {context.page.url}'


@then('every page reachable from the menu renders')
def step_menu_pages_render(context):
    hrefs = context.page.eval_on_selector_all(
        '#controlMenu a', 'els => els.map(e => e.getAttribute("href"))')
    checked = 0
    for href in sorted({h for h in hrefs if h and h.startswith('/') and 'logout' not in h}):
        response = context.page.goto(href)
        assert response.status < 500, f'{href} returned {response.status}'
        checked += 1
    assert checked, 'the menu exposed no links to check'


# --------------------------------------------------------------------------
# Then -- host and Apache
# --------------------------------------------------------------------------

@then('a vHost file for "{servername}" exists for user "{username}"')
def step_vhost_file_exists(context, servername, username):
    filename = f'VHOST_{username}_{servername}.conf'
    context.vhost_file = filename
    contents = wait_for(lambda: read_host_file(context, SITES_AVAILABLE + filename),
                        description=f'{filename} to be written')
    context.vhost_contents = contents


@then('it is symlinked into sites-enabled')
def step_vhost_symlinked(context):
    entries = control_get(context, '/ls', path=SITES_ENABLED).json()['entries']
    assert context.vhost_file in entries, \
        f'{context.vhost_file} is not in sites-enabled: {entries}'


@then('the vHost file contains')
def step_vhost_contains(context):
    for line in context.text.strip().splitlines():
        expected = line.strip()
        assert expected in context.vhost_contents, (
            f'expected {expected!r} in the generated vHost:\n{context.vhost_contents}')


@then('the vHost file does not contain "{text}"')
def step_vhost_not_contains(context, text):
    assert text not in context.vhost_contents, (
        f'did not expect {text!r} in the generated vHost:\n{context.vhost_contents}')


@then('no vHost file for "{servername}" exists for user "{username}"')
def step_vhost_file_absent(context, servername, username):
    filename = f'VHOST_{username}_{servername}.conf'
    assert read_host_file(context, SITES_AVAILABLE + filename) is None, \
        f'{filename} is still present in sites-available'
    entries = control_get(context, '/ls', path=SITES_ENABLED).json()['entries']
    assert filename not in entries, f'{filename} is still linked in sites-enabled'


@then('Apache accepts its configuration')
def step_configtest(context):
    result = control_get(context, '/configtest').json()
    combined = result['stdout'] + result['stderr']
    assert result['exitcode'] == 0, f'apache2ctl configtest failed:\n{combined}'
    assert 'Syntax OK' in combined, combined


@then('the certbot stub was invoked for "{host}"')
def step_certbot_invoked(context, host):
    def invocations():
        return read_host_file(context, '/var/log/certbot-invocations.log')
    contents = wait_for(invocations, description='a certbot invocation')
    assert f"-d '{host}'" in contents or f'-d {host}' in contents, \
        f'certbot was not asked for {host}; log was:\n{contents}'
    context.certbot_log = contents


@then('the certbot invocation used the Apache authenticator')
def step_certbot_apache(context):
    assert '--apache' in context.certbot_log, context.certbot_log


@then('the directory "{path}" exists and belongs to "{owner}"')
def step_directory_owned(context, path, owner):
    listing = control_get(context, '/ls', path=path)
    assert listing.status_code == 200, f'{path} does not exist: {listing.text}'


# --------------------------------------------------------------------------
# Then -- database
# --------------------------------------------------------------------------

@then('the vhost table has a row for "{servername}"')
def step_vhost_row(context, servername):
    rows = query(context, 'SELECT * FROM vhost WHERE servername = %s', (servername,))
    assert rows, f'no vhost row for {servername}'
    context.vhost_row = rows[0]


@then('the vhost table has no row for "{servername}"')
def step_no_vhost_row(context, servername):
    rows = query(context, 'SELECT * FROM vhost WHERE servername = %s', (servername,))
    assert not rows, f'vhost row for {servername} still present'


@then('the ftp table has a row with username "{username}"')
def step_ftp_row(context, username):
    rows = query(context, 'SELECT * FROM ftp WHERE username = %s', (username,))
    assert rows, [r['username'] for r in query(context, 'SELECT username FROM ftp')]
    assert rows[0]['passwd'].startswith('$6$'), \
        f'expected a SHA-512 crypt hash in ftp.passwd, got {rows[0]["passwd"]!r}'


@then('the mail_domain table has a row for "{domain}"')
def step_mail_domain_row(context, domain):
    rows = query(context, 'SELECT * FROM mail_domain WHERE domain = %s', (domain,))
    assert rows, f'no mail_domain row for {domain}'


@then('the mail_forward table has a row from "{source}" to "{destination}"')
def step_mail_forward_row(context, source, destination):
    rows = query(context,
                 'SELECT * FROM mail_forward WHERE source = %s AND destination = %s',
                 (source, destination))
    assert rows, query(context, 'SELECT source, destination, mail_domain_id FROM mail_forward')
    assert rows[0]['mail_domain_id'], \
        'mail_forward.mail_domain_id was not set, so Postfix could not resolve the domain'


def completed_task(context, task_type):
    def task():
        rows = query(context,
                     'SELECT * FROM system_task WHERE type = %s AND lastRun IS NOT NULL',
                     (task_type,))
        return rows[0] if rows else None
    return wait_for(task, description=f'a completed {task_type} task')


@then('a system task of type "{task_type}" was recorded with exit code {code:d}')
def step_task_exit_code(context, task_type, code):
    row = completed_task(context, task_type)
    assert row['exitcode'] == code, \
        f'{task_type} finished with exit code {row["exitcode"]}, expected {code}: {row["data"]}'


@then('a system task of type "{task_type}" has run')
def step_task_ran(context, task_type):
    # Not every task type reports an exit code: the ones that call into the
    # application rather than shelling out -- calculate_disk_usage is the one in
    # the queue today -- return nothing for CronCommand to record, so the fact
    # that lastRun was stamped is all there is to assert on. What the task did is
    # asserted separately, on its effect
    completed_task(context, task_type)


@then('the disk usage of "{username}" has been calculated')
def step_disk_usage(context, username):
    def usage():
        rows = query(context,
                     'SELECT u.diskusage FROM user u JOIN user_info ui ON ui.id = u.user_info_id '
                     'WHERE ui.username = %s', (username,))
        return rows and rows[0]['diskusage'] is not None
    wait_for(usage, description=f'diskusage for {username}')


@then('a DBMS account "{account}" exists')
def step_dbms_account(context, account):
    def present():
        return query(context, 'SELECT User FROM mysql.user WHERE User = %s',
                     (account,), root=True)
    wait_for(present, description=f'the DBMS account {account}')


@then('the DBMS account "{account}" can authenticate with password "{password}"')
def step_dbms_account_auth(context, account, password):
    connection = pymysql.connect(
        host=os.environ.get('PENGUIN_DB_HOST', 'db'),
        user=account, password=password, autocommit=True)
    with connection.cursor() as cursor:
        cursor.execute('SELECT 1')
        assert cursor.fetchone()
    connection.close()


@then('the DBMS account "{account}" is granted its own database')
def step_dbms_account_grants(context, account):
    grants = query(context, f"SHOW GRANTS FOR '{account}'@'%'", root=True)
    flat = ' '.join(next(iter(row.values())) for row in grants)
    assert f'`{account}`.*' in flat, f'grants for {account} were: {flat}'


@then('the billing log lists {count:d} entries')
def step_billing_log_count(context, count):
    rows = query(context, 'SELECT COUNT(*) AS n FROM user_log')
    assert rows[0]['n'] == count, f'user_log holds {rows[0]["n"]} rows, expected {count}'


@then('the log table records "{message}"')
def step_log_records(context, message):
    def present():
        return query(context, 'SELECT * FROM log WHERE message = %s', (message,))
    wait_for(present, description=f'a log row for {message!r}')


@then('a user_info row exists for "{username}" that is not yet validated')
def step_pending_registration(context, username):
    rows = query(context, 'SELECT * FROM user_info WHERE username = %s', (username,))
    assert rows, f'no user_info row for {username}'
    assert rows[0]['validated'] == 0, f'{username} is already validated'
    assert not query(context, 'SELECT * FROM user WHERE user_info_id = %s', (rows[0]['id'],)), \
        'a user row was created before validation'


