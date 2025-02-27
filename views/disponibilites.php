<?php
// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Utilisateur non connecté']);
    exit;
}

// Inclure la configuration de la base de données
include '../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['date']) || empty($_GET['date'])) {
    echo json_encode(['error' => 'Date non spécifiée']);
    exit;
}

$date = $_GET['date'];

// Validation de la date
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['error' => 'Format de date invalide']);
    exit;
}

// Récupération des créneaux déjà réservés pour cette date
$stmt = $conn->prepare("
    SELECT DATE_FORMAT(date_heure, '%H:%i') AS heure 
    FROM rendez_vous 
    WHERE DATE(date_heure) = ? 
    AND status != 'annulé'
");
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

$creneaux_reserves = [];
while ($row = $result->fetch_assoc()) {
    $creneaux_reserves[] = $row['heure'];
}

echo json_encode($creneaux_reserves);

// Fermer la connexion
$stmt->close();
$conn->close();