<?php

include '../app/libraries/GitLab.php';

$git = new GitLab();

//$user = $git->blockUser(14);
//echo "<pre>",print_r($user),"<pre>";
//$user2 = $git->getUser(14);
//echo "<pre>",print_r($user2),"<pre>";

//$user = $git->deleteUser ();

// toon users
$users = $git->getUsers ();
//echo "<pre>",print_r($users),"<pre>";

//$git->changeAdmin(14, true);


$email='karlos@vanhest.be';
$password = 'karlostest';
$username = 'karlostest1';
$name = 'Karlos van Hest';


//$user = $git->createUser($email, $password, $username, $name);

//$user = $git->getUser(13);

//$user = $git->getUsers();

//$user = $git->changePassword('13','ThomasMore');
