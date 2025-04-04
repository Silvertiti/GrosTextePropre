<?php
// Nouveau mot de passe
$newPassword = 'admin'; // Remplacez par le mot de passe souhaité

// Hachage du mot de passe
$hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

// Affichez le mot de passe haché
echo $hashedPassword;
?>