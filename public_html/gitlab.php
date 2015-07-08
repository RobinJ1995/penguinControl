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
echo "<pre>",print_r($users),"<pre>";

//$git->changeAdmin(3, true);


$email='karlos2@vanhest.be';
$password = 'karlostest';
$username = 'karlostest2';
$name = 'Karlos van Hest 2';


//$user = $git->createUser($email, $password, $username, $name);

//$user = $git->getUser(13);



//$user = $git->changePassword('14','ThomasMore');
