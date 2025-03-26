<?php
session_start();
require 'config.php'; 

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Récupérer les infos de l'utilisateur
$stmt = $pdo->prepare("SELECT nom, role FROM utilisateurs WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Définir le rôle
$role = $user['role'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accueil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Mon Site</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <?php if ($role === 'admin') : ?>
                    <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Tableau de bord</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_users.php">Gestion Utilisateurs</a></li>
                <?php elseif ($role === 'pilote') : ?>
                    <li class="nav-item"><a class="nav-link" href="pilote_dashboard.php">Tableau de bord</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_offres.php">Gérer les Offres</a></li>
                <?php elseif ($role === 'etudiant') : ?>
                    <li class="nav-item"><a class="nav-link" href="etudiant_dashboard.php">Offres</a></li>
                    <li class="nav-item"><a class="nav-link" href="wishlist.php">Wish List</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="logout.php">Déconnexion</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h2>Bienvenue, <?= htmlspecialchars($user['nom']) ?> !</h2>
</div>
