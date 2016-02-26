# Uit te voeren commando's

sudo apt-get -y update
sudo add-apt-repository ppa:ondrej/php5-5.6
sudo apt-get -y update
sudo apt-get install apache2 php5 php5-cli phpmyadmin mysql-server phpmyadmin
sudo php5enmod mcrypt
sudo a2enmod rewrite

# DB
Maak in MySql een db aan met de naam, gebruikersnaam en wachtwoord zoals in app/config/database.php staat.
Importeer in deze db de .sql file.

# Extra vhost settings
Binnen de <Directory> en </Directory> moet `AllowOverride all` staan.
Reden: anders werkt de .htaccess file van SIN-Control niet.

# Extra php.ini setings
Deze waarden moeten hoog genoeg staan zodat je de database (momenteel 3 à 4 MB) kan importeren.
upload_max_filesize, memory_limit en post_max_size
Het kan zijn dat de memory_limit en post_max_size groter moeten zijn dan de upload_max_filesize

# Laravel settings (dev env)
bootstrap/start.php
`$env = $app->detectEnvironment (array (
    'local' => array ('pingwing', 'pingu', 'penguino', 'Runesmacher','sincontroldev', 'karlos-ubuntu', 'KARLOS-WIN81', 'karlos-elementary'),
	));`
Uw eigen hostname toevoegen aan de array.