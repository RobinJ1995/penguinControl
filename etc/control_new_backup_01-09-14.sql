-- MySQL dump 10.15  Distrib 10.0.13-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: control_new
-- ------------------------------------------------------
-- Server version	10.0.13-MariaDB-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `apache_vhost_virtual`
--

DROP TABLE IF EXISTS `apache_vhost_virtual`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apache_vhost_virtual` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `docroot` varchar(256) NOT NULL,
  `basedir` varchar(255) NOT NULL,
  `servername` varchar(256) NOT NULL,
  `serveralias` tinytext NOT NULL,
  `serveradmin` varchar(64) NOT NULL,
  `custom` text NOT NULL COMMENT 'raw data, wordt toegevoegd achter aan de vhost (na eventuele redirect)',
  `cgi` tinyint(1) NOT NULL DEFAULT '1',
  `ssl` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: http, 1: https, 2:https with redirect',
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  CONSTRAINT `apache_vhost_virtual_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `apache_vhost_virtual`
--

LOCK TABLES `apache_vhost_virtual` WRITE;
/*!40000 ALTER TABLE `apache_vhost_virtual` DISABLE KEYS */;
INSERT INTO `apache_vhost_virtual` VALUES (1,3216,'public_html','/home/users/r/runes/public_html','tomnys.eu','www.tomnys.eu','runes@sinners.be','',0,0,0),(2,3673,'public_html/','/home/users/r/robinj1995/public_html/','trololol.be','','robinj1995@sinners.be','',0,0,0),(6,3673,'public_html/','','robinj1995.sinners.be','www.robinj1995.sinners.be','robinj1995@sinners.be','',1,0,1),(9,3675,'/home/users/y/yerlix/public_html','','yerlix.sinners.be','www.yerlix.sinners.be','yerlix@sinners.be','',1,0,1),(11,3676,'/home/users/j/jospiet/public_html','','jospiet.sinners.be','www.jospiet.sinners.be','jospiet@sinners.be','',1,0,1);
/*!40000 ALTER TABLE `apache_vhost_virtual` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ftp_user_virtual`
--

DROP TABLE IF EXISTS `ftp_user_virtual`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ftp_user_virtual` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `user` varchar(50) NOT NULL,
  `passwd` varchar(128) NOT NULL,
  `dir` tinytext NOT NULL,
  `locked` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`user`),
  KEY `uid` (`uid`),
  CONSTRAINT `ftp_user_virtual_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COMMENT='virtuele ftp user tabel';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ftp_user_virtual`
--

LOCK TABLES `ftp_user_virtual` WRITE;
/*!40000 ALTER TABLE `ftp_user_virtual` DISABLE KEYS */;
INSERT INTO `ftp_user_virtual` VALUES (2,3675,'yerlix','$6$rounds=10234$1a64f27a86f97d5c$dTGN7hfmrEuSbiG9hLKZReKi.kxHNnEPCgHtPbYu8HWPe/v0rqwKLgkQ9hdQx7eO2zm9MWJwS5RBAEmowKiXa/','/home/users/y/yerlix',''),(4,3676,'jospiet','$6$rounds=11365$e493e7f6d56bcc5d$SyAGzvl4WGBBQBl4TYlY1DIrcodhCekc.LNyPT20.Lj.YDSZTfWxb1LgNTHpgcT0amHGMOkOixsiH4bhaouWz0','/home/users/j/jospiet','');
/*!40000 ALTER TABLE `ftp_user_virtual` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geschiedenis`
--

DROP TABLE IF EXISTS `geschiedenis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `geschiedenis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `startdatum` date NOT NULL,
  `einddatum` date NOT NULL,
  `beschrijving` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geschiedenis`
--

LOCK TABLES `geschiedenis` WRITE;
/*!40000 ALTER TABLE `geschiedenis` DISABLE KEYS */;
INSERT INTO `geschiedenis` VALUES (1,'1997-08-01','1997-09-01','Enkele duistere internetters uit de Kempen wisselen al lachend ideen uit over een nieuwe studentenvereniging aan de Katholieke Hogeschool Kempen. Het bleef toen bij dromen...'),(2,'1997-10-01','1997-10-01','Vier zotte zielen (rib, pedro, abraham en dot) vinden elkaar en slaan de handen in elkaar. Ze zouden proberen om in een Geel een studentenvereniging op te richten die de drempel tot computers in het algemeen en het Internet in het bijzonder wil verlagen. Inspiratie werd gezocht bij <a href=\"http://www.ulyssis.org/\">ULYSSIS</a> in Leuven.\r\nEr worden doelstellingen geschreven, begrotingen ontworpen, KHK-battle-plans opgesteld,... en er wordt na lang twijfelen gekozen voor de mooie naam Student Information Networking. De toen opgestelde agenda voorzag dat SIN eind februari operationeel zou zijn, in het beste geval!!'),(3,'1997-11-01','1997-11-01','Na wat gelobby bij docenten en directie wordt de eerste officiele vergadering belegd met de mensen van den A-blok. De uitkomst van de vergadering overtreft de stoutste dromen! Niet alleen was de vereniging goedgekeud. Er zijn ook weinig beperkingen opgelegd en de KHK zou de verening voor een groot stuk financieren. Een week later wordt de al server gekocht! Alles komt in een stroomversnelling terecht.'),(4,'1997-12-01','1997-12-01','De server wordt geleverd en direct afgevoerd naar Rib\'s thuis. De KHK is immers tijdens de vakantie gesloten. Na een week installeren, configureren, opnieuw installeren, opnieuw configuren,... verhuist de server naar zijn vaste stek op de KHK. Het is nu alleen nog wachten op de netwerkverbinding...'),(5,'1998-01-01','1998-01-01','Op een koude woensdagvoormiddag trekken Pedro en Rib hun stoute schoenen aan en duiken samen met Frank Rommes in de catacomben van de KHK. Enkele uren worden vloeren opgebroken, plafonds verwijderd, kabels getrokken,... Maar na een halve dag zwoegen is het zover: walhalla (zo is de server gedoopt) had een netwerkverbinding!\r\nDe partiele examens leggen alles 2 weken stil. Maar geen nood, de dagen na de partieels wordt er hard gewerkt zodat de server klaar is voor het grote werk. SIN is operationeel, 2 maanden voor de stoutste verwachtingen !-)'),(6,'2004-05-01','2004-05-01','Er gebeurden opmerkelijk veel crashes en de server \"asgard\" deed heel raar. De oorzaak bleek een kapot moederbord te zijn. Geen probleem, de belangrijkste services zoals DNS draaiden verder op de oudste server \"walhalla\".'),(7,'2004-08-01','2004-08-01','De walhalla server heeft de geest gegeven op augustus. Alles was verbrand binnen in, moederbord, CPU, voeding en harde schijven met natuurlijk bijbehorende dataverlies.\r\nEr wordt direct gezocht naar geld voor een nieuwe server, Sovo (nu Stuvo) wordt gecontacteerd en zij zijn bereid het geld hiervoor ter onze beschikking te stellen. Het team gaat naarstig op zoek naar een nieuwe server.'),(8,'2004-09-01','2004-09-01','De nieuwe server wordt besteld, het is een Dell Poweredge 1600SC geworden.'),(9,'2004-10-01','2004-10-01','Onze nieuwe server, welke Bambi gedoopt is, is aangekomen op de khk. Het team begint direct met de installatie van het nieuwe paradepaardje van SIN. En een week later is deze server up and running en bieden de eerste leden zich aan.'),(10,'2004-12-01','2004-12-01','Bambi is volledig klaar, en een grote actie naar de studenten toe geeft ons een totaal leden aantal van rond de 200 leden, een groot succes, het grootste leden aantal sinds jaren.'),(11,'2005-09-01','2005-12-01','SIN ging op zoek naar nieuwe servers en vond deze. In het totaal staan er nu 5 servers met elk hun eigen functie, database, file, backups, .... Ook werd er een intern gigabit netwerk opgestart en hardeschijven aangekocht om zo een capaciteit van 400 GIG in raid 5 te bekomen.'),(12,'2006-09-01','2006-09-01','Na lang wachten was het eindelijk zo ver. SIN heeft een eigen lokaal(tje) gekregen voor onze servers. Dit stond al jaren op ons verlanglijstje om downtime te verkomen door grapjassen die de servers in het labo gingen uitschakelen. Het serverpark van SIN is wederom groter geworden. Swan en Durga worden toegevoegd aan de 5 bestaande servers. Swan is even krachtig als Bambi, ons huidige werkpaard, en wordt ingezet om onze SQL database te hosten op haar SCSI schijven. Durga wordt aangesteld als nieuwe backup pc.'),(13,'2008-10-01','2008-10-01','Ondertussen hebben we 7 operationele servers: De gloednieuwe Squid (dual quadcore) voor apache, ssh en ProFTP, Prometheus als fileserver, Swan als database, Xena als xen development platform, Sentinel als onze trouwe gateway, Enigma als de nieuwe standalone mailserver en Bambi die binnenkort de fileserver zal worden.'),(14,'2009-02-01','2009-02-01','We hebben weer een migratie achter de rug, waarbij we het gigabit netwerk met jumbo frames opnieuw operationeel maakten en bambi is de volwaardige fileserver geworden (met 1TB bruikbare schijfruimte in raid 5). We maken prometheus klaar voor zijn nieuwe taak als backupserver en alle netwerkkabels zijn mooi gelabeld om het onderhoud in de toekomst te vergemakkelijken.'),(15,'2009-12-01','2009-12-01','Er wordt gemigreerd naar een infrastructuur gebaseerd op virtualisatie. Een gebrek aan planning en testing zorgt er voor dat het proces niet van een leien dakje loopt, en de resulterende infrastructuur niet de beoogde voordelen biedt. Op het einde van het academiejaar zijn de planningen voor een volgende migratie, tijdens de zomermaanden, volop aan de gang...'),(16,'2010-08-01','2010-08-01','Na veel geknoei wordt er besloten om af te stappen van virtualisatie. Hoewel er veel toekomst in zit, zijn er niet genoeg actieve staffleden die ervaring hebben met de gevirtualizeerde structuur. Een migratie werd gepland en vlot uitgevoerd. SIN zit met het meerendeel van haar services terug op \'Squid\' (die zijn oorspronkelijke naam dus terug erft). SIN\'s serverpark bestaat nu uit Squid (web, shell, mail, files), Swan (db), Bambi (backup) en Targa (staff en games).'),(17,'2010-10-01','2010-10-01','Hoewel SINcontrol al enkele jaren trouw dienst heeft gedaan, vinden we het tijd voor een update. Er worden plannen gesmeed en UML schema\'s geschetst voor SINcontrol v2. Verder lijkt Swan tekenen van veroudering te vertonen, en is aan een vernieuwing toe.'),(18,'2010-12-01','2010-12-01','Deze maand krijgen we te maken met twee stroompannes. Na de eerste, die voor problemen zorgde, treffen we de nodige maatregelen. Voor dit kon gebeuren, was de volgende er reeds - met nefaste gevolgen voor Swan. Rhea doet tijdelijk dienst als database-server, terwijl Heaven, de opvolger voor Swan, klaar staat om (na de examens, uiteraard) in dienst te treden.'),(21,'2013-10-01','2013-10-01','We verwelkomen de opvolg(st)er van de main-server Squid; Xena! Een oude bekende onder de SIN hostnames. Hell en Heaven zijn terug naar hun oorspronkelijke eigenaar en Swan nam de taak van database-server opnieuw over van Rhea.\r\n<br /><br />\r\nHet SIN-kot heeft een make-over gekregen met een vernieuwd 19\" rack en actieve verluchting. Het team is ook flink aan het groeien, met een jonge junior-ploeg en nieuw bestuur. We zijn benieuwd naar wat de toekomst brengt!');
/*!40000 ALTER TABLE `geschiedenis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group`
--

DROP TABLE IF EXISTS `group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL DEFAULT 'x',
  `gid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COMMENT='Holds group information for system';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group`
--

LOCK TABLES `group` WRITE;
/*!40000 ALTER TABLE `group` DISABLE KEYS */;
INSERT INTO `group` VALUES (1,'staff','x',50),(2,'users','x',100),(4,'system','x',130),(5,'juniors','x',80),(6,'dev','x',25);
/*!40000 ALTER TABLE `group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mail_domain_virtual`
--

DROP TABLE IF EXISTS `mail_domain_virtual`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mail_domain_virtual` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `domain` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain` (`domain`),
  KEY `uid` (`uid`),
  CONSTRAINT `mail_domain_virtual_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mail_domain_virtual`
--

LOCK TABLES `mail_domain_virtual` WRITE;
/*!40000 ALTER TABLE `mail_domain_virtual` DISABLE KEYS */;
INSERT INTO `mail_domain_virtual` VALUES (1,3673,'trololol.be');
/*!40000 ALTER TABLE `mail_domain_virtual` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mail_forwarding_virtual`
--

DROP TABLE IF EXISTS `mail_forwarding_virtual`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mail_forwarding_virtual` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `source` varchar(80) NOT NULL,
  `destination` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `source` (`source`),
  KEY `uid` (`uid`),
  CONSTRAINT `mail_forwarding_virtual_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mail_forwarding_virtual`
--

LOCK TABLES `mail_forwarding_virtual` WRITE;
/*!40000 ALTER TABLE `mail_forwarding_virtual` DISABLE KEYS */;
/*!40000 ALTER TABLE `mail_forwarding_virtual` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mail_user_virtual`
--

DROP TABLE IF EXISTS `mail_user_virtual`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mail_user_virtual` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `email` varchar(80) NOT NULL,
  `password` varchar(60) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `uid` (`uid`),
  CONSTRAINT `mail_user_virtual_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mail_user_virtual`
--

LOCK TABLES `mail_user_virtual` WRITE;
/*!40000 ALTER TABLE `mail_user_virtual` DISABLE KEYS */;
/*!40000 ALTER TABLE `mail_user_virtual` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medewerker`
--

DROP TABLE IF EXISTS `medewerker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medewerker` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `irc` varchar(32) NOT NULL,
  `status` varchar(32) DEFAULT NULL,
  `functie` tinytext,
  `intresses` tinytext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid_UNIQUE` (`uid`),
  CONSTRAINT `medewerker_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medewerker`
--

LOCK TABLES `medewerker` WRITE;
/*!40000 ALTER TABLE `medewerker` DISABLE KEYS */;
/*!40000 ALTER TABLE `medewerker` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menuitem`
--

DROP TABLE IF EXISTS `menuitem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menuitem` (
  `id` int(4) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `parent` int(4) NOT NULL COMMENT '-1= not in menu, 0=menu header, else id of header',
  `name` varchar(32) NOT NULL COMMENT 'manu name',
  `url` varchar(128) DEFAULT NULL COMMENT 'url',
  `gid_access` int(11) DEFAULT NULL,
  `order` tinyint(1) NOT NULL DEFAULT '0',
  `help` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`),
  KEY `parent` (`parent`),
  KEY `menuitem_ibfk_1_idx` (`gid_access`),
  CONSTRAINT `menuitem_ibfk_1` FOREIGN KEY (`gid_access`) REFERENCES `group` (`gid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menuitem`
--

LOCK TABLES `menuitem` WRITE;
/*!40000 ALTER TABLE `menuitem` DISABLE KEYS */;
INSERT INTO `menuitem` VALUES (1,0,'Gebruiker',NULL,NULL,1,NULL),(2,0,'Websites',NULL,100,2,NULL),(3,0,'FTP','/ftp',100,3,'Elke gebruiker beschikt over 1 FTP gebruiker. Indien je nog een andere ftp gebruiker wenst te hebben, die toegang heeft tot een bepaalde map kan je dit bij sin aanvragen. Dit kan handig zijn bijvoorbeeld als je groepswerken moet doen en je een centraal opslag plaats nodig hebt.'),(4,0,'Databases',NULL,100,3,NULL),(5,0,'Staff',NULL,80,5,NULL),(7,5,'Gebruikers',NULL,50,1,NULL),(8,5,'Websites',NULL,50,2,NULL),(9,5,'E-mail',NULL,50,4,NULL),(10,5,'SIN-website',NULL,50,5,NULL),(11,-1,'Home','/',NULL,0,NULL),(12,1,'Gegevens','/user/start',100,1,NULL),(13,1,'Wachtwoord wijzigen','/user/password',100,2,'Je systeem wachtwoord is het wachtwoord dat je gebruikt om in te loggen via SSH op onze server, om je aan te melden op sincontrol en nog andere diensten .\r\nOm je system wachtwoord te veranderen klik je op \"Wijzig wachtwoord\". Je komt dan op een pagina waar je 3 vragen te zien krijgt: Je oud systeem wachtwoord, je nieuw wachtwoord en nogmaals je nieuw wachtwoord (ter controle). Vervolgens klik je op de knop \'Submit\'.\r\nIndien je je oud wachtwoord niet meer kunt herinneren hoef je maar een mail te sturen naar onze helpdesk (helpdesk@sin.khk.be) ze zullen je met plezier verder helpen.\r\n\r\nJe kan ook je S-schijf wachtwoord synchroniseren met je systeem wachtwoord. Zet dan een vink bij \"Sync Paswoorden\". Zo heb je dan 1 wachtwoord voor zowel het systeem als S-schijf (in plaats van 2 aparte wachtwoorden). Het is natuurlijk altijd veiliger om 2 aparte wachtwoorden te gebruiken.<br />'),(14,1,'Inloggen','/user/login',NULL,3,NULL),(15,1,'Uitloggen','/user/logout',100,4,NULL),(16,1,'Wachtwoord vergeten','/user/password/forgot',NULL,5,NULL),(17,1,'Registreren','/user/register',NULL,3,NULL),(18,2,'vHosts','/website/vhost',100,1,'Een virtual host is in feite een \'virtuele website\' dat je kan toevoegen aan je bestaande account. Bij SIN krijg je dus enkele extra virtuele hosts ter beschikking waar naar je kan verwijzen met een subdomein. Een voorbeeld van een subdomein is dus je eigen account bij SIN. SIN heeft als domein: sin.khk.be. SIN geeft je als gebruiker dan een eigen subdomein, namelijk: je-gebruikers-naam.sin.khk.be.\r\nDus als je bijvoorbeeld een forum op je SIN website hebt kan je er nu simpel naar verwijzen door: http://forum.je-gebruikers-naam.sin.khk.be.\r\n\r\nHoe stel je nu een Virtual host in?\r\nDoor te klikken op de link \'virtual hosts\'. Je krijgt nu een pagina te zien waarin je een heleboel informatie kan invullen. Je zal zien dat je al een virtual host hebt, namelijk die van SIN zelf.\r\n\r\nVoeg een vhost toe\r\nDocument root: dit is bijvoorbeeld de \'map\' waar je forum, blog, foto\'s enz in staan. Vul dit in met de juiste map naam die je hebt aangemaakt in je public_html. Bijvoorbeeld: Als je een forum hebt in forum/ moet je dan gewoon forum ingeven.\r\n\r\nServernaam: Dit is dus de naam die je wilt toewijzen aan je nieuwe virtuele host. Dus als het over een forum gaat, kan je het ook logischerwijze forum.je-gebruikers-naam.sin.khk.be noemen.\r\n\r\nServeradminw: Dit is dus een e-mail adres (waar iemand contact kan opnemen met je)\r\n\r\nServeralias: Dit is geen verplicht veld. Maar je kan het wel gebruiken om meerdere namen aan ??n subdomein toe te wijzen. Bijvoorbeeld als je een forum hebt en je subdomein wijst al naar forum.je-gebruikers-naam.sin.khk.be maar je wilt NOG een naam bijvoorbeeld \'babbel\' kan je in serveralias zetten: babbel.je-gebruikers-naam.sin.khk.be.\r\n\r\nType: Hier geef je aan welke type subdomein het gaat zijn. Standaard staat het op \'http only\' wat normaal goed zal zijn. Indien je gebruik maakt van encryptie (dus https) kan je dit ook aanduiden.\r\n\r\nCGI enabled: Als je aangemaakte subdomein gebruik maakt van cgi scripts dan kan je cgi enabled op \"yes\" zetten. Standaard staat dit op \"no\"\r\n\r\nAls alles in orde is, en als alle invulvakken (buiten serveralias) zijn ingevuld zal je dus een nieuwe virtuele host ter beschikking hebben.'),(19,2,'Statistieken','/website/stats',100,2,'Stats kunnen zeer handig zijn als je wil zien wie je website is komen bezoeken, welke tijdstippen, van welke landen, welke besturingssysteem ze draaien en welke browser ze gebruiken.\r\nDit is eerder handig voor mensen die hun website willen optimaliseren voor hun grootste doelgroep (dus bijvoorbeeld Windows gebruikers, die gebruik maken van Internet Explorer).'),(20,2,'Versiebeheer','/website/versioncontrol',100,3,'Subversion is een versie beheer systeem.\r\nHet maakt het mogelijk om bijvoorbeeld voor een programeer project op te volgen wie welke code heeft geschreven en terug te gaan naar vorige versies als er iets fout loopt.\r\n\r\nVoor meer informatie kan je <a href=\"http://www.brambring.nl/Main/Subversion\">hier</a> terecht.\r\n\r\nEen stap-voor-stap handleiding over hoe je subversion gebruikt vind je <a href=\"http://sin.khk.be/?p=documentatie/subversion\">hier</a>.'),(22,4,'Databases','/database',100,1,'Hier krijg je de mogelijkheid om een nieuwe database aan te maken. Vul gewoon een gepaste naam in voor de gewenste database. Bijvoorbeeld: Als je een forum wil implementeren is het gemakkelijk om als database naam \"forum\" te kiezen (wel zonder aanhalingstekens natuurlijk). Zo wordt je database uiteindelijk &lt;sin-gebruikersnaam&gt;_forum.'),(23,4,'Gebruikers','/db/user',100,2,'Hier kan je een mysql gebruiker toevoegen, verwijderen of bewerken.\r\n\r\nUsername: kies een gebruikers naam. Bijvoorbeeld, als je een forum wil implementeren op je site, kan je voor de gemakkelijkheid kiezen voor \"forum\". Zo wordt je SQL gebruikersnaam &lt;sin-gebruikersnaam&gt;_forum.\r\n\r\nPassword: kies een deftige, veilige wachtwoord:\r\nRe-enter password: Voer je wachtwoord een 2de keer in.\r\n\r\nDoor het vinkje van CreateDB aan te zetten word er een database gemaakt met dezelfde naam als de gebruiker.\r\nOok worden alle rechten voor deze database toegewezen aan deze gebruiker.\r\n\r\nKlik zo op submit en een nieuwe gebruiker is aangemaakt.'),(24,4,'Permissies','/db/permissions',100,3,'Hier kan je een gebruiker aan een database koppelen en rechten instellen die en gebruiker zal hebben op de database'),(25,43,'Algemeen','/mail',100,1,NULL),(26,43,'Domeinen','/mail/domain',100,2,'Indien je wilt dat de mailserver van SIN de mails van een bepaalde domein (die je in bezit hebt) wil laten afhandelen. Geef je dit dan hier in.\r\nDit is voor geavanceerde gebruikers.'),(27,43,'E-mailadressen','/mail/user',100,3,'Hier staan alle mail accounts die gekoppeld zijn aan je SIN account.'),(28,43,'Doorstuuradressen','/mail/forwarding',100,4,'Als je wilt dat bepaalde mail omgeleid wordt naar een andere adres, kan je dit hier allemaal invullen. Bijvoorbeeld, stel dat je je sin mail wenst te omleiden naar een hotmail account geef je bij Van uw SIN mail account in (gebruikersnaam@sin.khk.be) en Naar vul je dus een andere mail account in bijvoorbeeld (gebruiker@hotmail.com). Nu wordt alle mail die naar je sin account gestuurd wordt, omgeleid naar je hotmail.'),(29,7,'Gebruikers','/staff/user',25,1,'Hier kan je gebruikers aanpassen, verwijderen of toevoegen'),(30,7,'Groepen','/staff/groups',50,2,'Hier kan je groepen aanpassen, verwijderen of toevoegen'),(31,7,'Misbruik','/staff/abuse',50,3,NULL),(32,5,'FTP','/staff/ftp',50,3,NULL),(33,8,'DNS-records','/staff/website/dns',50,1,NULL),(34,8,'vHosts','/staff/website/vhosts',50,2,NULL),(35,8,'Statistieken','/staff/website/stats',50,3,NULL),(36,9,'Domeinen','/staff/mail/domain',50,4,NULL),(37,9,'E-mailaddressen','/staff/mail/user',50,5,NULL),(38,9,'Doorstuuradressen','/staff/mail/forwarding',50,6,NULL),(39,10,'Nieuws','/staff/site/nieuws',50,0,NULL),(40,10,'Medewerkers','/staff/site/medewerkers',50,0,NULL),(41,10,'Geschiedenis','/staff/site/geschiedenis',50,0,NULL),(42,-1,'Error','/error',NULL,0,NULL),(43,0,'E-mail',NULL,100,4,NULL);
/*!40000 ALTER TABLE `menuitem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nieuws`
--

DROP TABLE IF EXISTS `nieuws`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nieuws` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `text` text NOT NULL,
  `dat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nieuws`
--

LOCK TABLES `nieuws` WRITE;
/*!40000 ALTER TABLE `nieuws` DISABLE KEYS */;
INSERT INTO `nieuws` VALUES (1,'Downtime 18-08-2009 tot 24-08-2009','De eerder gemelde downtime heeft langer aangesleept dan gedacht. Enkele malen slaagden we er in de services terug draaiende te krijgen. Na een tijd (van enkele uren tot enkele dagen) kregen we echter steeds te maken met dezelfde problemen, blijkbaar allen gerelateerd aan de hitte van de laatste week.\r\n\r\nMomenteel draaien alle services terug stabiel, al gaan we nog enkele aanpassingen doorvoeren en bepaalde delen van de infrastructuur vervangen. Hierdoor zal het gepland onderhoud op 5 september nog steeds doorgaan. Afhankelijk van onze leveranciers zal er nog een extra gepland onderhoud volgen voor de nieuwe aanpassingen die nodig zijn.\r\n\r\nNogmaals onze excuses voor deze lange periode van slechte bereikbaarheid.','2009-08-24 02:02:27'),(2,'Downtime 18-08-2009','De diensten van SIN waren enkele uren onbereikbaar. Deze plotse storing is ondertussen opgelost, al wordt er nog onderzoek gedaan naar de specifieke aard en oorzaak van het probleem. Zo hopen we in de toekomst dergelijke onverwachte downtime te voorkomen.\r\n\r\nWij bieden onze excuses aan voor het ongemak.','2009-08-17 18:04:03'),(3,'Gepland onderhoud 05-09-2009','Op 5 september 2009, tijdens de (na)middag, zullen alle services die SIN aanbiedt tijdelijk down gaan. Reden hiervoor is de ingebruikname van een 19\" rack.\r\n\r\nAan de infrastructuur van de services zal theoretisch niets veranderen, alleen de fysieke organisatie van het serverpark wordt danig onder handen genomen. Hierdoor zou de downtime beperkt moeten blijven, volgens de planning zouden alle services ten laatste om 17h terug online moeten zijn.\r\n\r\nEen klein stapje voor de gebruiker, maar een ultrafenomenastische sprong voor de onderhoudbaarheid (lees: geek-status) van het SIN serverpark!','2009-08-05 18:05:15'),(4,'Nieuwe service: Subversion','SIN introduceert een gloednieuwe service. Iedere gebruiker kan een <a href=\"http://subversion.tigris.org/\">Subversion</a> repository aanmaken op zijn/haar persoonlijke account, welke beschikbaar is voor (een team van) meerdere gebruikers.<br/><br/>Alle details in verband met het opzetten van Subversion voor je account zijn te vinden onder de <a href=\"http://sin.khk.be/?p=documentatie/subversion\">Subversion</a> pagina in de <a href=\"http://sin.khk.be/?p=documentatie\">documentatie</a> op de website.<br/>','2009-06-17 02:05:54'),(5,'Upgrade Mysql','Vandaag hebben we een upgrade uitgevoerd van mysql 5.0 naar mysql 5.1.\r\n\r\n<a href=\"http://dev.mysql.com/doc/refman/5.1/en/mysql-nutshell.html\" target=\"_blank\">Hier</a> vind u een lijst van de features die toegevoegd zijn in mysql 5.1.\r\n\r\nHet SIN team','2009-11-05 14:27:12'),(6,'FTP probleem fixed','Sommige gebruikers ondervonden een delay tijdens het verbinden naar de SIN FTP server. Dit probleem is ondertussen opgelost.\r\n\r\nMoesten er nog problemen voorvallen aarzel dan niet om ons te contacteren via <a href=\"http://sin.khk.be/?p=irc\">IRC</a> of via <a href=\"mailto:sin@sin.khk.be\">e-mail</a>.\r\n\r\nHet SIN team','2009-11-19 09:10:03'),(7,'DownTime afgelopen','Ondertussen zijn de meeste services terug online.\r\n\r\nVoor volgende services is de te gebruiken hostname veranderd:\r\n\r\nShell = shell.sin.khk.be\r\nFTP/S-schijf = ftp.sin.khk.be\r\n\r\nOnze excuses voor het ongemak.\r\n\r\nHet SIN team','2009-12-21 12:30:09'),(8,'Servers op nieuwe IPv6 standaard','SIN is trots u te melden, dat we sinds gisteren op alle servers behalve de gateway, IPv6 draaiende hebben gekregen.<br/><br/>\r\nDe meeste netwerken gebruiken nog het -ondertussen 20 jaar oude- IPv4 protocol. Hoewel men hier miljoenen IP-adressen mee kan vormen, krimpt het aantal vrije adressen snel, en zullen deze over een aantal jaar op zijn.<br/>\r\nIPv6 zorgt dat ?lke aardbewoner maar liefst 50 000 quadriljoen adressen ter beschikking kan krijgen, meer als voldoende dus.<br/>\r\nDeze nieuwe standaard is bij zowat alle nieuwste besturingssystemen al ondersteund, maar wordt nog niet veel gebruikt momenteel. SIN neemt daarom een voorbeeldfunctie aan, met een heel IPv6-enabled serverpark.<br/><br/>\r\nMet dank aan de KHKempen voor het beschikbaar stellen van een IPv6 range voor SIN.','2010-02-24 10:16:20'),(9,'S-schijf terug online!','Onze s-schijf is terug online! Het bat-bestand kan je <a href=\"http://sin.khk.be/?p=bat\">hier</a> downloaden!\r\nMoest u nog problemen ondervinden <a href=mailto:sin@sin.khk.be>mail ons</a>!\r\n\r\nHet SIN team','2010-02-28 12:16:03'),(10,'Inschrijvingen dit academiejaar afgesloten','De inschrijvingen voor de SIN accounts zijn voor dit academiejaar afgesloten. De inschrijvingen openen terug volgend academiejaar (september 2010).\r\n\r\nMoest u problemen ondervinden met 1 van onze services mag u ons nog altijd <a href=\"mailto:sin@sin.khk.be\">contacteren</a>.\r\n\r\nAlvast veel succes met de examens!\r\n\r\nHet SIN team','2010-05-11 18:18:19'),(11,'Geplande downtime: migratie (04/09/2010)','Na het uitwerken van een geheel nieuw migratieplan weten we nu ook wanneer de uitvoering hiervan kan plaatsvinden. In samenspraak met de KHK is deze datum vastgelegd op zaterdag 4 september 2010.\r\n\r\nGezien de omvang van de migratie, die een reorganisatie van onze gehele infrastructuur inhoudt, gaan we er van uit dat onze diensten de hele dag niet beschikbaar zullen zijn. In geval van complicaties kan de duur van dit onderhoud uitgebreid worden naar zondag 5 september 2010 - al hopen we uiteraard dat dit niet nodig zal zijn.\r\n\r\nAlvast dank voor uw begrip,\r\nHet SIN team','2010-08-16 09:05:36'),(12,'Opening inschrijvingen academiejaar 2010-2011','De inschrijvingen openen op de eerste dag van het academiejaar (maandag 20 september). Na inschrijving zal uw persoonlijke SIN account binnen de 24 uur geactiveerd worden. De account kost 5 euro voor een heel academiejaar en wordt verrekend via de schoolrekening! Een complete lijst van onze services vindt u <a href=\"http://sin.khk.be/?p=wat\">hier</a>.\r\n\r\nHet SIN team','2010-09-09 10:40:37'),(13,'Verdere vernieuwing van de infrastructuur','Sinds de vorige migratie is er ?rg veel veranderd. Onze hele infrastructuur werd volledig herbekeken. De migratie van de basisdiensten is uiteindelijk goed verlopen. Momenteel worden de andere (interne) diensten eveneens vernieuwd.\r\n\r\nZo is er reeds een volledig nieuw backup-systeem op poten gezet, dat meermaals per dag backups maakt die eenvoudig teruggezet kunnen worden - zelfs door de gebruiker zelf!\r\n\r\nEr staan nog een hoop interessante projecten op stapel, stay tuned!\r\n\r\nHet SIN Team','2010-10-02 12:54:18'),(14,'Downtime op 27/10/2010','Morgen namiddag vanaf 15h zal sin mogelijk even down zijn. We gaan een noodzakelijke serverupdate doen die wat tijd in beslag kan nemen. Alvast bedankt voor uw begrip.\r\n\r\nHet SIN team','2010-10-26 11:25:28'),(15,'Downtime door stroomuitval','Door een stroomuitval op zondag 5/12/2010 in heel Geel is SIN een tijdje offline geweest. De SIN staff heeft er vandaag alles aan gedaan om de servers zo snel mogelijk terug online te krijgen, en met succes!!! Ondertussen zijn we terug online en moeten alle problemen opgelost zijn.\r\n\r\nOnze excuses voor eventuele ongemakken.\r\n\r\nHet SIN team','2010-12-06 17:43:19'),(16,'Downtime 17-21/12/2010','Helaas kregen we terug met een stroomuitval te maken, en dit zo kort bij de examenperiode. Spijtig genoeg waren de voorbereidingen op een volgende stroomuitval nog volop aan de gang, gezien de korte opeenvolging van de twee stroompannes.\r\n\r\nGelukkig kunnen we bij deze melden dat SIN ondertussen wel weer operationeel is, met behulp van een tijdelijke vervanging voor de database-server. We hopen binnenkort een definitieve vervanging voor deze server te implementeren. Concrete plannen hieromtrent zullen uiteraard hier gemeld worden!\r\n\r\nMet dank voor uw begrip,\r\nHet SIN team','2010-12-21 16:25:46'),(17,'Prettige Feesten!','Prettige feesten en een heel gelukkig nieuwjaar voor iedereen! \r\n\r\nTot in het nieuwe jaar ;-)\r\n\r\nHet SIN team','2010-12-25 19:28:17'),(18,'Geplande downtime 19-03-2011','Dit weekend zal het SIN-team aanpassingen doorvoeren aan de infrastructuur van het serverlokaal. \r\n\r\nHierbij worden geen aanpassingen gedaan aan de publieke services. De migratie betreft voornamelijk een herinrichting waardoor volgende migraties veel vlotter zullen kunnen verlopen. Ook kleiner onderhoud zal sneller en eenvoudiger uitgevoerd kunnen worden, zodat dit niet meer dient te gebeuren tijdens een grote migratie. Het voordeel mag duidelijk zijn; migraties die minder lang aanslepen! :-)\r\n\r\nVerder wordt Rhea, de database-server, opnieuw gemigreerd van de huidige tijdelijke oplossing (ge?mplementeerd na de dood van Swan - dewelke we tijdens de migratie nogmaals zullen proberen te redden) naar een \'iets meer permanente\' oplossing die ons uitgeleend werd; een IBM x225! Nogmaals, dit betreft geen aanpassing van de service. Het is slechts een \'verhuizing\' die, wederom, vlot zou moeten verlopen.\r\n\r\nDe werken vatten aan om 9h. De services zullen <b>vanaf 1h (19/03/2011)</b> onbereikbaar zijn met betrekking tot de backups, <b>tot ten laatste \'s avonds die dag</b>. Alvast onze excuses voor het eventuele ongemak.\r\n\r\nHet SIN team','2011-03-17 13:04:20'),(20,'Ongeplande downtime (op bevel) 29/06 - 05/07','Na een downtime van bijna een week, zijn onze services terug online!\r\n\r\nOns netwerk werd afgesloten door de SIN staff, op bevel van de <a href=\"http://www.polfed-fedpol.be/\">Federale Politie</a>, gesteund door <a href=\"http://www.anti-piracy.be/nl/\">BAF</a>. We kunnen hier verder geen informatie over vrijgeven, en kunnen enkel hopen op uw begrip.\r\n\r\nOnze excuses voor deze uitzonderlijk lange downtime.\r\n\r\nHet SIN team','2011-07-05 09:54:40'),(21,'Mogelijke downtime op maandag en dinsdag 8 en 9 augustus','Omwille van onderhoudswerken aan onze servers is het mogelijk dat SIN op maandag en dinsdag 8&9 augustus even offline kan zijn. De downtime zou beperkt moeten blijven tot maximum een uur.\r\n\r\nHet SIN team\r\n\r\n','2011-08-06 09:03:17'),(22,'Samen met de studenten start SIN het academiejaar 2011-2012','... al zijn we stiekem al een hele tijd druk bezig !-)\r\n\r\n(Her)inschrijven kan vanaf 19 september, ondertussen wordt de registratie-module uitvoerig getest. \r\n\r\nBij een herinschrijving blijft alle data behouden (websites, databanken, ...), net zoals je alles hebt achtergelaten dus.\r\n\r\nHuidige actieve accounts blijven geldig tot 1 oktober. Herinschrijven kan daarna nog steeds, al zal het systeem jouw account ondertussen op non-actief plaatsen.\r\n\r\nBen je een kersverse student (of een oude rot ;) met een (on)gezonde interesse in ICT, hardware, netwerken en/of Linux? Als je bereid bent om jouw huidige kennis serieus uit te breiden, doe ons dan een <a href=\"mailto:sin@sin.khk.be\">mailtje</a> (sin@sin.khk.be) of <a href=\"http://sin.khk.be/?p=irc\">spring eens binnen op IRC</a> (#sin op irc.krey.net) :)\r\n\r\nAlvast succes met het nieuwe jaar!\r\n\r\nHet SIN team','2011-09-05 16:07:20'),(23,'Downtime vrijdag 16 september 2011','Tijdens de nacht van donderdag op vrijdag was SIN helaas een tijdje niet bereikbaar.\r\n\r\nHet probleem was in de loop van de ochtend reeds opgelost door onze staffers. Voor de mensen die op de onthaaldagen de presentaties gevolgd hebben; onze excuses voor het ongemak, maar we zijn dus terug ;-)\r\n\r\nInschrijven is dus mogelijk vanaf morgen 19 septebmer, zoals gepland!\r\n\r\nHet SIN team','2011-09-17 09:25:00'),(24,'Mod_python op sin.khk.be!','Sinds vandaag ondersteunt onze webserver ook Python code!\r\n\r\nScripts/pagina\'s met de extensie .py wordt automatisch ge?nterpreteerd door de Python interpreter, vooraleer de output wordt doorgestuurd naar de browser.\r\n\r\nDit verlaagt de drempel voor het gebruik van systemen zoals Django, Plone/Zope etc., al hebben we dit zelf nog niet uitgetest. Onze gebruikers mogen ons altijd op de hoogte houden indien ze hier mee experimenteren.\r\n\r\nDit voorstel werd aangedragen door een gebruiker. Heb je zelf een voorstel voor een bepaalde uitbreiding, nieuwe dienst, ...? We zijn steeds bereikbaar via mail en IRC!\r\n\r\nHet SIN team','2011-09-17 13:11:11'),(25,'Problemen met SSL','Er zijn momenteel enkele problemen met SSL. Dit is vooral zichtbaar bij het gebruik van sincontrol en de (her)inschrijvingen. Wij verontschuldigen ons voor dit ongemak en stellen alles in het werk om dit zo snel mogelijk in orde te brengen.\r\n\r\n<b>UPDATE 10/10/2011, 9h40</b>: De SSL-problemen zijn van de baan! Onze hoofddomeinen (sin.khk.be, sincontrol.sin.khk.be, ...) zijn nu weer bereikbaar via https://.\r\n\r\nHet SIN team','2011-10-01 11:31:27'),(27,'Geplande downtime: 17-12-2011, 9h-17h','Op zaterdag 17 december 2011 zullen we onze eerste migratie van het jaar doorvoeren. Tussen 9h en 17h zullen onze services niet of slecht bereikbaar zijn.\n\nEr staan geen aanpassingen aan de services gepland. Er worden kabels (stroom en netwerk) vervangen, en opnieuw gelabeld. Daarbij zullen we onze gemotiveerde juniors meer uitleg geven over het serverpark - met andere woorden een belangrijke dag dus! :-)\n\nAlvast dank voor uw begrip.\n\nHet SIN team','2011-11-30 18:51:32'),(28,'sinners.be; de nieuwe domeinnaam voor onze gebruikers!','Bij deze heeft SIN de domeinnaam sinners.be geregistreerd - en geactiveerd! Bekijk jouw website nu via jouw nieuw adres!\r\n\r\n<strong>[gebruikersnaam].sinners.be</strong>\r\n\r\nOp aanvraag van de KHK zullen de webruimtes van onze gebruikers, op termijn, naar dit domein verhuizen. Vanaf het volgend academiejaar (2012-2013) zullen de oude adressen (username.sin.khk.be) vervallen.\r\n\r\nWacht zeker niet te lang met het in gebruik nemen van de nieuwe adressen! Des te sneller je jouw nieuw adres bekendheid geeft, des te beter.\r\n\r\n- het SIN team','2012-01-15 16:21:31'),(29,'Sinners.be toegangspunt tot websites','Sinds vandaag is worden alle [gebruikersnaam].sin.khk.be transparant geredirect naar [gebruikersnaam].sinners.be. Het kan zijn dat bepaalde software (forum, cms) aanpassingen nodig hebben, kijk zeker naar het domein en het cookie domein.\r\n\r\nDe volgende stap (in enkele weken), zal zijn dat de deze redirect vervangen wordt door een landingspagina die na x tijd redirect.\r\nOm zo uiteindelijk op 1 september [gebruikersnaam].sin.khk.be niet meer toegankelijk te maken.\r\n\r\nOok zal deze aanpassing doorgevoerd worden in email, uw @sin.khk.be adres zal op 1 september ook niet meer werken.\r\n\r\nVoor alle SIN gerelateerde dingen (sin website, phpmyqdmin, sincontrol, helpdesk) veranderd er niks ','2012-02-27 16:06:39'),(30,'Ook bij SIN start het nieuwe academiejaar!','(Her)inschrijven kan vanaf 17 september, we zullen de registratiepagina klaarmaken tegen die datum.\r\n\r\nBij een herinschrijving blijft alle data behouden (websites, databanken, ...), net zoals je alles hebt achtergelaten dus.\r\n\r\nHuidige actieve accounts blijven geldig tot 1 oktober. Herinschrijven kan daarna nog steeds, al zal het systeem jouw account ondertussen op non-actief plaatsen.\r\n\r\n<b><i>Wijzigingen</i></b>\r\n\r\nDit nieuwe jaar brengt een paar wijzigingen met zich mee, die nu al gebeurd zijn of binnenkort gaan gebeuren. Zo zal iedereen een nieuw subdomein krijgen, vanaf nu heeft iedereen een <b>gebruikersnaam.sinners.be</b> domein. Dit geldt voor zowel je webruimte als email. We willen in de loop van dit jaar ook een aantal nieuwe diensten aanbieden, daarover lees je hier op het nieuws later zeker meer!\r\n\r\n<b><i>Zin om medewerker te worden?</i></b>\r\n\r\nBen je een kersverse student (of een oude rot ;) met een (on)gezonde interesse in ICT, hardware, netwerken en/of Linux? Als je bereid bent om jouw huidige kennis serieus uit te breiden, doe ons dan een <a href=\"mailto:sin@sin.khk.be\">mailtje</a> (sin@sin.khk.be) of <a href=\"http://sin.khk.be/?p=irc\">spring eens binnen op IRC</a> (#sin op irc.krey.net)\r\n\r\nAlvast succes met het nieuwe jaar!\r\n\r\nHet SIN team','2012-09-12 12:58:10'),(31,'Geplande downtime 2012-10-20','Op zaterdag 20 oktober 09:00 begint SIN aan haar eerste migratie van het nieuwe academiejaar. Dit houdt dan ook in dat alle servers en services die dag offline zullen worden gehaald. \r\n\r\nOnze juniors zullen hier hun eerste contact maken met ons \"datacenter\". Al onze leden/sympathisanten zijn die dag welkom om SIN te komen bewonderen in hun volste glorie. Afspraak vanaf 9 uur in P218.\r\n\r\nTen laatste om 21:00 zullen onze services terug online zijn.\r\n\r\nWij excuseren ons alvast voor het mogelijke ongemak.\r\n\r\nHet SIN team','2012-10-18 18:06:54'),(36,'Geplande downtime 2013-02-16','Op zaterdag 16 februari vanaf 9u zal er een migratie doorgevoerd worden. \r\n\r\nHierbij zal er een nieuwe server in gebruik genomen worden. \r\nHierdoor kan het zijn dat onze services zaterdag niet beschikbaar zijn. \r\n\r\nWij excuseren ons alvast voor het mogelijke ongemak. \r\n\r\nHet SIN team','2013-02-14 20:12:44'),(37,'Problemen met SIN vanuit Thomas More','Sinds enige tijd is er een probleem met het kunnen connecteren met SIN vanop het draadloze Thomas More netwerk. Onder andere kan de webruimte niet meer opgevraagd worden vanuit de campus. Wel kan dit vanaf elk ander netwerk of het bekabeld netwerk. \r\n\r\nHiervoor is er al contact opgenomen met Thomas More, maar wanneer er een oplossing komt voor het probleem is niet gekend.\r\n\r\nOnze verontschuldigingen voor het ongemak. \r\n\r\n<b>Update:</b> De problemen met het netwerk zijn opgelost. SIN is weer bereikbaar vanuit de schoolomgeving.\r\n\r\n\r\nHet SIN team','2013-04-15 13:47:15'),(38,'Migratie 2013-05-04','Op zaterdag 4mei voeren we een migratie door. We gaan Xena verder configureren, de juniors zich gekend laten worden binnen SIN en eventueel prutsen met een nieuwe server. \r\nHet is daarom mogelijk dat we die dag down gaan. Hou er dus rekening mee dat SIN die dag niet bereikbaar zou kunnen zijn.','2013-04-22 18:26:35'),(39,'Ook SIN start het nieuwe academiejaar!','(Her)inschrijven kan vanaf 13 september, we zullen de registratiepagina klaarmaken tegen die datum.\r\n\r\nBij een herinschrijving blijft alle data behouden (websites, databanken, ...), net zoals je alles hebt achtergelaten dus.\r\n\r\nHuidige actieve accounts blijven geldig tot 1 oktober. Herinschrijven kan daarna nog steeds, al zal het systeem jouw account ondertussen op non-actief plaatsen.\r\n\r\n<b><i>Zin om medewerker te worden?</i></b>\r\n\r\nBen je een kersverse student (of een oude rot ;) met een (on)gezonde interesse in ICT, hardware, netwerken en/of Linux? Als je bereid bent om jouw huidige kennis serieus uit te breiden, doe ons dan een <a href=\"mailto:sin@sin.khk.be\">mailtje</a> (sin@sin.khk.be) of <a href=\"http://sin.khk.be/?p=irc\">spring eens binnen op IRC</a> (#sin op irc.krey.net)\r\n\r\nAlvast succes met het nieuwe jaar!\r\n\r\nHet SIN team','2013-09-05 14:27:07'),(40,'Nieuwe PHP versie, pas je websites aan','Sinds kort draait op de sin server PHP versie 5.5.\r\nEen van de belangrijkste aanpassingen naar deze versie is dat het gebruik van shorttags ( <? en <?= ) niet meer ondersteund word.\r\nHet is dus belangrijk dat je php code opent met de volledige tag (<?php).\r\nHierdoor zal je website blijven werken zonder errors.\r\n\r\nHet SIN team','2013-09-16 15:49:20'),(41,'Migratie 2013-10-05','Zaterdag 5 oktober gaan we tijdens de migratie een oude server vervangen door een nieuwe. Om dit mogelijk te maken, moet de oude server eerst afgezet worden. Hierdoor is SIN die dag niet bereikbaar.\r\n\r\nWe raden aan om alle taken, opdrachten, ... die tegen maandag 7 oktober af moeten zijn, vroeger af te maken en te uploaden.','2013-09-29 10:00:18'),(42,'s-nummer in plaats van r-nummer of studenten met een @student.kuleuven.be adres','Indien je een s-nummer hebt in plaats van een r-nummer (een mogelijke reden is een overschakeling van KULeuven naar Thomas More), moet je een mail versturen naar <a href=\"mailto:sin@sinners.be\">sin@sinners.be</a> met alle gegevens die je normaal zou invullen op het formulier.\r\n\r\nDit omwille dat het formulier geen s-nummers meer toelaat.\r\n\r\nAls je een @student.kuleuven.be adres hebt, mail ons dan je gegevens door vanaf dit email adres zodat we jou validatiemail juist kunnen sturen.\r\n\r\nEenmaal je ons je gegevens doorgegeven hebt, vullen wij het formulier in en gaat het via de normale weg verder: je krijgt een mail toegestuurd op je schoolmail met een link waarmee je je account kan bevestigen. \r\n\r\nIndien je vragen hebt, kan je ook mailen naar <a href=\"mailto:sin@sinners.be\">sin@sinners.be</a>','2013-10-03 15:52:44'),(43,'Migratie 2013-11-16','Vandaag worden hopelijk de SSL certificaten terug in orde gebracht zodat https://mail.sinners.be en https://sincontrol.sinners.be actief kunnen worden.\r\n\r\nHet zou kunnen dat enkele diensten van SIN offline zullen zijn vandaag maar waarschijnlijk zullen gebruikers niet al te veel hinder ondervinden.','2013-11-16 08:19:09'),(44,'Migratie 2013-12-14','Zaterdag 14 december gaan we onze database migreren naar een nieuwe server. Die server zal dan dienst doen als master waarbij de oude (huidige) server als slave gebruikt zal worden. \r\nEventueel wordt onze oude hoofdserver omgevormd tot een backup server.\r\n\r\nOmstreeks 10u in de ochtend starten we met deze werken. SIN zal die dag niet bereikbaar zijn.','2013-11-24 20:15:21'),(45,'Database master-slave setup','Op woensdag 8 januari 2014 gaan we de database in een master-slave configatie plaatsen. Dit om de performatie en beschikbaarheid te garanderen.\r\n\r\nWe verwachten geen downtime voor de users en alle services zullen blijven werken.\r\nHet is wel mogelijk dat de database tijdelijk onbeschikbaar zal worden, \r\nmaar de statische websites zal je zeker kunnen blijven bezoeken.\r\n\r\nAlvast dank voor uw begrip.\r\n\r\nHet SIN team','2014-01-07 22:26:59'),(48,'Downtime services 28 fabruari tot 3 maart','Door een attack op onze servers, waren onze services ongepland offline.\r\n\r\nDeze attack is ondertussen afgewend en hopelijk voor altijd voorkomen.\r\n\r\nOndertussen hebben we ook alle services opnieuw nagekeken en enkele andere problemen opgelost.\r\n\r\nWij danken jullie voor jullie begrip en geduld tijdens deze downtime.\r\n\r\nMoesten je nog problemen ondervinden, aarzel dan niet om een mailtje te sturen naar sin@sinners.be\r\n\r\nHet SIN team','2014-03-03 22:06:25'),(49,'KHK forum','Na enig overleg is er binnen SIN bepaald dat we het KHK forum gaan stopzetten omdat er niemand dit actief wil beheren. Het beheer van een website behoort niet tot onze functies. \r\nMoest er iemand zich toch geroepen voelen om het KHK forum op zich te nemen, kan hij of zij mailen naar <a href=\"mailto:sin@sinners.be\">sin@sinners.be</a>.\r\n\r\nHet SIN team','2014-03-03 23:01:59'),(50,'Migratie tijdens infodag','Zaterdag 22 maart is er een infodag voorzien op Thomas More. SIN zal zich daar ook kenbaar maken om eventuele leden en medewerkers warm te maken voor SIN.\r\n\r\nOm deze reden kan het zijn dat we een migratie doorvoeren tijdens de infodagen om zo een goed beeld te kunnen geven van wat SIN is. \r\nHet kan dus zijn dat SIN die dag niet bereikbaar is.\r\n\r\nHet SIN team','2014-03-16 12:10:43'),(51,'Heartbleed','Vorige week werdt een kritiek beveiligingsprobleem ontdekt in de veelgebruikte cryptografische library OpenSSL. SIN, net als vele andere websites als Facebook en Google maken gebruik van OpenSSL.\r\nOndertussen zijn onze servers reeds gepatcht tegen dit lek, maar het kan nooit kwaad om uw wachtwoord te wijzigen, aangezien niemand weet hoe lang deze bug al misbruikt werdt voor deze publiek gemaakt werdt. Hetzelfde geldt voor het wachtwoord van uw Google-, Facebook-, en andere accounts.\r\n\r\nWie graag meer informatie heeft over de zogenaamde \"Heartbleed\"-bug kan deze vinden op <a href=\"http://www.heartbleed.com/\">http://www.heartbleed.com/</a>\r\n\r\nVoor wie graag een simpele uitleg heeft, zie onderstaande catroon (bron: <a href=\"http://www.xkcd.com/1354/\">xkcd</a>);\r\n\r\nHet SIN team\r\n\r\n<hr />\r\n<p style=\"text-align: center;\"><img src=\"http://imgs.xkcd.com/comics/heartbleed_explanation.png\" alt=\"[Afbeelding]\" /></p>','2014-04-16 15:34:21');
/*!40000 ALTER TABLE `nieuws` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `passreset`
--

DROP TABLE IF EXISTS `passreset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `passreset` (
  `uid` int(11) NOT NULL,
  `hash` varchar(32) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`),
  CONSTRAINT `passreset_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `passreset`
--

LOCK TABLES `passreset` WRITE;
/*!40000 ALTER TABLE `passreset` DISABLE KEYS */;
/*!40000 ALTER TABLE `passreset` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `session`
--

DROP TABLE IF EXISTS `session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payload` text NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `session`
--

LOCK TABLES `session` WRITE;
/*!40000 ALTER TABLE `session` DISABLE KEYS */;
/*!40000 ALTER TABLE `session` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscribe`
--

DROP TABLE IF EXISTS `subscribe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscribe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_info_id` int(11) NOT NULL,
  `acajaar` varchar(9) NOT NULL,
  `domein` varchar(45) NOT NULL,
  `groep` varchar(45) NOT NULL,
  `boekhouding` tinyint(4) NOT NULL DEFAULT '0',
  `herinschrijving` tinyint(4) NOT NULL DEFAULT '0',
  `hash` varchar(40) DEFAULT 'NOHASH SET ERROR' COMMENT 'null = ingeschreven, 32char=tobevalidated, 40char=tobecreated',
  `lastModif` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `info` (`acajaar`,`user_info_id`),
  KEY `uid` (`acajaar`),
  KEY `gid` (`user_info_id`),
  CONSTRAINT `subscribe_ibfk_1` FOREIGN KEY (`user_info_id`) REFERENCES `user_info` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscribe`
--

LOCK TABLES `subscribe` WRITE;
/*!40000 ALTER TABLE `subscribe` DISABLE KEYS */;
INSERT INTO `subscribe` VALUES (1,2,'2013-2014','ICT','Technologie & Design',0,0,'CREATED','2013-09-13 10:26:39');
/*!40000 ALTER TABLE `subscribe` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_task`
--

DROP TABLE IF EXISTS `system_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_task` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` enum('apachereload','sambareload') NOT NULL COMMENT 'more to be added soon',
  `data` text COMMENT 'Added info for the task',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `done` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_task`
--

LOCK TABLES `system_task` WRITE;
/*!40000 ALTER TABLE `system_task` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_task` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tasklist`
--

DROP TABLE IF EXISTS `tasklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasklist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `due` date NOT NULL,
  `task` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  CONSTRAINT `tasklist_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasklist`
--

LOCK TABLES `tasklist` WRITE;
/*!40000 ALTER TABLE `tasklist` DISABLE KEYS */;
/*!40000 ALTER TABLE `tasklist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT 'Global userid',
  `user_info_id` int(11) NOT NULL,
  `crypt` varchar(255) NOT NULL DEFAULT 'x' COMMENT 'hashed password',
  `gcos` varchar(255) NOT NULL COMMENT 'gcos field',
  `gid` int(11) NOT NULL DEFAULT '100' COMMENT 'main group id',
  `homedir` varchar(255) NOT NULL DEFAULT '' COMMENT 'homedir van de user',
  `shell` varchar(20) NOT NULL DEFAULT '/bin/bash' COMMENT 'shell van de user',
  `lastchange` bigint(20) NOT NULL DEFAULT '1',
  `min` bigint(20) NOT NULL DEFAULT '0',
  `max` bigint(20) NOT NULL DEFAULT '99999',
  `warn` bigint(20) NOT NULL DEFAULT '0',
  `inact` bigint(20) NOT NULL DEFAULT '0',
  `expire` bigint(20) DEFAULT '-1',
  `flag` bigint(20) unsigned NOT NULL DEFAULT '0',
  `smb_lm` varchar(255) NOT NULL,
  `smb_nt` varchar(255) NOT NULL,
  `diskusage` bigint(10) NOT NULL COMMENT 'diskusage, added by a script',
  `svnEnabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'enable=1, disable=0',
  `mailEnabled` tinyint(1) NOT NULL COMMENT 'enable=1, disable=0',
  `remember_token` varchar(100) DEFAULT '' COMMENT 'remember me',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `user_ibfk_3` (`gid`),
  KEY `user_ibfk_2` (`user_info_id`),
  CONSTRAINT `user_ibfk_2` FOREIGN KEY (`user_info_id`) REFERENCES `user_info` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1637 DEFAULT CHARSET=latin1 COMMENT='Holds system group information';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1173,3216,1,'$1$ee8xhOXM$31BaEoZ4q/XRJbHCLZMlp/','Nys Tom, runes@sinners.be',50,'/home/users/r/runes','/bin/bash',15253,0,99999,7,-1,16256,0,'5365A30F7B3ED2870CC3EB564B0F9047','2107BB9ADB3C317DAC43842DA4C5F0C7',241,1,1,''),(1630,3673,2,'$1$wZNK9gZs$kEEMUkWq35ccet0QHm5It/','Robin Jacobs, robinj1995@sinners.be',50,'/home/users/r/robinj1995','/bin/bash',1404558911,0,99999,7,-1,NULL,0,'62B2DE71B1949A262C5AE1F1CFB9210F','1CCCC2F92142811E96E3C8CD29C9659F',435,0,1,'lLjKKBlBsZa8DUXUDryFV74ExN8fz6j21gh2Fc7P4RE32m1rIu6FHm92EuVg'),(1631,3674,3,'$6$rounds=8035$805eca35969ade6b$zzF8Ct0B7I9P28nk6FM3VcbrrRUO/X.9D4SirCq/Euk9xOJ8nsPd.pe262D.ddfyroOaQzLq.93bqy5toM20/.','Stijn Michiels, grottem@sinners.be',50,'/home/users/g/grottem','/bin/bash',1404471751,0,99999,0,0,16344,0,'','',0,0,0,''),(1634,3675,10,'$6$rounds=10234$1a64f27a86f97d5c$dTGN7hfmrEuSbiG9hLKZReKi.kxHNnEPCgHtPbYu8HWPe/v0rqwKLgkQ9hdQx7eO2zm9MWJwS5RBAEmowKiXa/','Yerlix Stessens, yerlix@sinners.be',50,'/home/users/y/yerlix','/bin/zsh',1404562826,0,99999,0,0,16344,0,'','',0,0,0,''),(1636,3676,13,'$6$rounds=11365$e493e7f6d56bcc5d$SyAGzvl4WGBBQBl4TYlY1DIrcodhCekc.LNyPT20.Lj.YDSZTfWxb1LgNTHpgcT0amHGMOkOixsiH4bhaouWz0','Jos Piet, jos@piet.be',100,'/home/users/j/jospiet','/bin/bash',1404644511,0,99999,0,0,16344,0,'','',0,0,0,'OdvvPtpB5qCi8EnPwkGzL2ZXYISt2Nmh6v8MEeCPRahct0CmtmtxOGIFt976');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_group`
--

DROP TABLE IF EXISTS `user_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `gid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_2` (`uid`,`gid`),
  KEY `uid` (`uid`),
  KEY `gid` (`gid`),
  CONSTRAINT `user_group_ibfk_1` FOREIGN KEY (`gid`) REFERENCES `group` (`gid`),
  CONSTRAINT `user_group_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COMMENT='Links system users to system groups';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_group`
--

LOCK TABLES `user_group` WRITE;
/*!40000 ALTER TABLE `user_group` DISABLE KEYS */;
INSERT INTO `user_group` VALUES (1,3216,25),(2,3673,25);
/*!40000 ALTER TABLE `user_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_info`
--

DROP TABLE IF EXISTS `user_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT 'Username',
  `fname` varchar(255) NOT NULL COMMENT 'Firstname',
  `lname` varchar(255) NOT NULL COMMENT 'Lastname',
  `email` varchar(45) NOT NULL COMMENT 'Email',
  `schoolnr` varchar(50) NOT NULL COMMENT 'Student number or Teachers number',
  `lastchange` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `etc` text,
  `validated` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_info`
--

LOCK TABLES `user_info` WRITE;
/*!40000 ALTER TABLE `user_info` DISABLE KEYS */;
INSERT INTO `user_info` VALUES (1,'runes','Tom','Nys','runes@sinners.be','r0258518','2014-07-06 09:31:37',NULL,1),(2,'robinj1995','Robin','Jacobs','robinj1995@sinners.be','r0446734','2014-07-06 09:31:37',NULL,1),(3,'grottem','Stijn','Michiels','grottem@sinners.be','r0000000','2014-07-06 09:31:37',NULL,1),(10,'yerlix','Yerlix','Stessens','yerlix@sinners.be','r0000001','2014-07-06 09:31:37',NULL,1),(13,'jospiet','Jos','Piet','jos@piet.be','r0000002','0000-00-00 00:00:00',NULL,1);
/*!40000 ALTER TABLE `user_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_limit`
--

DROP TABLE IF EXISTS `user_limit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_limit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `ftp_user_virtual` int(5) NOT NULL DEFAULT '1',
  `apache_vhost_virtual` int(5) NOT NULL DEFAULT '3',
  `mail_domain_virtual` int(5) NOT NULL DEFAULT '2',
  `mail_user_virtual` int(5) NOT NULL DEFAULT '5',
  `mail_forwarding_virtual` int(5) NOT NULL DEFAULT '5',
  `diskusage` int(6) NOT NULL DEFAULT '25000',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`),
  CONSTRAINT `user_limit_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_limit`
--

LOCK TABLES `user_limit` WRITE;
/*!40000 ALTER TABLE `user_limit` DISABLE KEYS */;
INSERT INTO `user_limit` VALUES (1,NULL,3,3,2,5,5,25000),(2,3216,1,3,2,5,5,25000),(3,3673,2,5,2,5,5,28000);
/*!40000 ALTER TABLE `user_limit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_log`
--

DROP TABLE IF EXISTS `user_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_info_id` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `nieuw` tinyint(1) NOT NULL DEFAULT '1',
  `boekhouding` tinyint(1) NOT NULL DEFAULT '0' COMMENT '-1 = Niet te factureren // 0 = Nog te factureren // 1 = Gefactureerd',
  PRIMARY KEY (`id`),
  KEY `fk_user_log_1_idx` (`user_info_id`),
  CONSTRAINT `fk_user_log_1` FOREIGN KEY (`user_info_id`) REFERENCES `user_info` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_log`
--

LOCK TABLES `user_log` WRITE;
/*!40000 ALTER TABLE `user_log` DISABLE KEYS */;
INSERT INTO `user_log` VALUES (1,13,'2014-07-06 11:01:51',1,0);
/*!40000 ALTER TABLE `user_log` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-09-01 14:04:59
