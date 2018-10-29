```
apt install apache2 php-bz2 php-curl php-geoip php-json php-imagick php-apcu libapache2-mpm-itk
a2enmod rewrite
```

* Setup database and enter credentials in `.env` configuration file.

Add the following cron job:

```
* * * * * cd /opt/penguinControl/ && php artisan cron:run -v 2>/dev/null
```

# Let's Encrypt

For Let's Encrypt support in the vHosts to work;

```
apt install software-properties-common
add-apt-repository ppa:certbot/certbot
apt update
apt install python-certbot-apache
certbot # Go through setup
crontab -e
```

Add the following cron tasks:
```
0 10 * * * certbot renew -n >/dev/null 2>&1
```

