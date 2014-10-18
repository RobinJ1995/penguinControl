<?php

include '../app/libraries/GitLab.php';

$git = new GitLab();

$email='karlos.van.hest3ss@gmail.com';
$password = 'karlostest';
$username = 'karlostest3';
$name = 'Karlos van Hest';


//$user = $git->createUser($email, $password, $username, $name);

//$user = $git->getUser('5');

//$user = $git->getUsers();

$user = $git->changePassword('5','ThomasMore');

echo '<pre>',  var_dump($user),'</pre>';