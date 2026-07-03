<?php
session_start();
$adminFile = 'db/admin.json';

// Vérification de l'installation et de l'authentification
if (!file_exists($adminFile)) {
    header('Location: setup.php');
    exit;
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Paramètre anti-cache dynamique (timestamp actuel)
$noCache = time(); 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Système Planification - Gantt JSON Engine</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.6.1/frappe-gantt.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.6.1/frappe-gantt.min.js"></script>
    
    <link rel="stylesheet" href="style.css?now=<?= $noCache ?>">
</head>
<body>

    <div class="navbar">
        <h1>Gantt Custom Engine — Espace d'administration</h1>
        <a href="logout.php" class="logout-btn">Déconnexion</a>
    </div>
