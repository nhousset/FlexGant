<?php
session_start();
header('Content-Type: application/json');

// Protection stricte de l'accès API
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Non autorisé."]);
    exit;
}

$dataFile = 'db/tasks.json';

// ROUTE GET : Récupération des tâches
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (file_exists($dataFile)) {
        echo file_get_contents($dataFile);
    } else {
        echo json_encode([]); // Par défaut, renvoie un tableau vide
    }
    exit;
}

// ROUTE POST : Mise à jour/Sauvegarde complète
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputJSON = file_get_contents('php://input');
    $inputData = json_decode($inputJSON, true);

    if (is_array($inputData)) {
        if (!is_dir('db')) {
            mkdir('db', 0755, true);
        }
        // Utilisation de LOCK_EX pour prémunir des corruptions concurrentes
        $saved = file_put_contents($dataFile, json_encode($inputData, JSON_PRETTY_PRINT), LOCK_EX);
        if ($saved !== false) {
            echo json_encode(["status" => "success", "message" => "Données sauvegardées."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Échec d'écriture sur le disque."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Structure de données invalide."]);
    }
    exit;
}

http_response_code(405);
echo json_encode(["status" => "error", "message" => "Méthode non autorisée."]);
?>
