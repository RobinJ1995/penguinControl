"""
behave hooks for the penguinControl end-to-end suite.

Every scenario starts from a freshly migrated and seeded database, with the
seeded panel users mirrored into Unix accounts and Apache's generated vHosts
cleared, so scenarios cannot leak into one another.
"""

import os
import time

import requests
from playwright.sync_api import sync_playwright

BASE_URL = os.environ.get('PENGUIN_BASE_URL', 'http://panel')
CONTROL_URL = BASE_URL.rsplit(':', 1)[0] if BASE_URL.count(':') > 1 else BASE_URL
CONTROL = os.environ.get('PENGUIN_CONTROL_URL', CONTROL_URL + ':9000')


def wait_for_control(timeout=120):
    deadline = time.time() + timeout
    last = None
    while time.time() < deadline:
        try:
            if requests.get(CONTROL + '/health', timeout=5).ok:
                return
        except requests.RequestException as exc:
            last = exc
        time.sleep(1)
    raise RuntimeError(f'test control plane never became ready at {CONTROL}: {last}')


def before_all(context):
    wait_for_control()
    context.playwright = sync_playwright().start()
    # --no-proxy-server because the app and db containers are reachable directly.
    # Chromium otherwise picks up whatever proxy the host has configured, and
    # treats an empty http_proxy as a proxy of "" -- refusing every connection.
    context.browser = context.playwright.chromium.launch (args=['--no-proxy-server'])
    context.base_url = BASE_URL
    context.control = CONTROL


def after_all(context):
    if getattr(context, 'browser', None):
        context.browser.close()
    if getattr(context, 'playwright', None):
        context.playwright.stop()


def before_scenario(context, scenario):
    response = requests.post(context.control + '/reset', timeout=300)
    if not response.ok:
        raise RuntimeError('database reset failed: ' + response.text)

    context.page_context = context.browser.new_context(base_url=BASE_URL)
    context.page = context.page_context.new_page()
    # Collected so that a failing scenario can report what the browser saw
    context.console = []
    context.page.on('console', lambda msg: context.console.append(f'{msg.type}: {msg.text}'))


def after_scenario(context, scenario):
    if scenario.status == 'failed':
        try:
            print('\n--- URL ---\n' + context.page.url)
            print('\n--- console ---\n' + '\n'.join(context.console[-40:]))
            logs = requests.get(context.control + '/logs', timeout=30).json()
            if logs.get('contents'):
                print('\n--- laravel.log ---\n' + logs['contents'][-6000:])
        except Exception as exc:                                   # noqa: BLE001
            print(f'(could not collect diagnostics: {exc})')

    if getattr(context, 'page_context', None):
        context.page_context.close()
