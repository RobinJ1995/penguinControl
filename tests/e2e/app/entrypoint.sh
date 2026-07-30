#!/bin/bash
set -euo pipefail

cd "${APP_DIR:-/opt/penguinControl}"

echo "==> Waiting for the database"
for _ in $(seq 1 60); do
	if mariadb -h "$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e 'SELECT 1' "$DB_DATABASE" >/dev/null 2>&1; then
		break
	fi
	sleep 1
done

echo "==> Granting the panel's account the rights it needs"
# docs/setup/apache.md: the panel manages each user's own DBMS account, so its
# own account needs CREATE USER and GRANT OPTION.
mariadb -h "$DB_HOST" -uroot -p"$DB_ROOT_PASSWORD" -e "
	GRANT ALL PRIVILEGES ON *.* TO '$DB_USERNAME'@'%' WITH GRANT OPTION;
	FLUSH PRIVILEGES;"

echo "==> Configuring the application"
if [ ! -f .env ]; then
	cp .env.example .env
fi
{
	echo
	echo "# Written by the end-to-end harness entrypoint"
	# A real install runs in production, and it matters: DatabaseCredentials
	# skips managing DBMS accounts entirely when the environment is 'local'.
	echo "APP_ENV=production"
	echo "APP_DEBUG=false"
	echo "APP_URL=http://localhost"
	echo "DB_HOST=$DB_HOST"
	echo "DB_PORT=$DB_PORT"
	echo "DB_DATABASE=$DB_DATABASE"
	echo "DB_USERNAME=$DB_USERNAME"
	echo "DB_PASSWORD=$DB_PASSWORD"
	echo "SERVER_IP=127.0.0.1"
	# No SMTP server here; the Mailables still render, they just are not delivered
	echo "MAIL_MAILER=log"
	# Without these the seeder generates random passwords, and the behave suite logs in
	# with fixed ones. See DatabaseSeeder::passwordFor ()
	echo "SEED_ADMIN_PASSWORD=admin"
	echo "SEED_USER_PASSWORD=penguin"
} >> .env
php artisan key:generate --force

echo "==> Creating the schema"
php artisan migrate --force
php artisan db:seed --force

echo "==> Mirroring the seeded panel users into Unix accounts"
# See control.py: on a real server libnss-mysql does this from the same tables.
python3 - <<'PY'
import sys
sys.path.insert(0, '/usr/local/bin')
import importlib.util
spec = importlib.util.spec_from_file_location('control', '/usr/local/bin/control.py')
control = importlib.util.module_from_spec(spec)
spec.loader.exec_module(control)
for row in control.seeded_accounts():
    for step in control.ensure_unix_account(*row):
        if step['exitcode']:
            print('WARN', step['command'], step['stderr'].strip())
PY

echo "==> Installing the cron job from docs/setup/apache.md"
echo "* * * * * cd ${APP_DIR} && php artisan cron:run -v 2>/dev/null" > /etc/cron.d/penguincontrol
echo "" >> /etc/cron.d/penguincontrol
chmod 0644 /etc/cron.d/penguincontrol
crontab /etc/cron.d/penguincontrol
cron

echo "==> Starting the test control plane on :9000"
python3 /usr/local/bin/control.py &

echo "==> Starting Apache"
exec apache2ctl -DFOREGROUND
