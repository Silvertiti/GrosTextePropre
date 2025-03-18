<?php

require_once "bootstrap.php";

use App\Model\User;

$user = new User();
$user->setName('John Doe');

$entityManager->persist($user);
$entityManager->flush();

echo "User created with ID " . $user->getId();
