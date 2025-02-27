<?php
// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    header("Location: connexion.php");
    exit();
}

// Inclure la configuration de la base de données
include '../config/database.php';

// Vérifier si l'ID du rendez-vous est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Rediriger vers la page de profil avec un message d'erreur
    $_SESSION['message'] = "Erreur: Aucun rendez-vous spécifié.";
    header("Location: profile.php");
    exit();
}

$rendez_vous_id = intval($_GET['id']);
$utilisateur_id = $_SESSION['id'];

// Vérifier si le rendez-vous appartient à l'utilisateur
$stmt = $conn->prepare("SELECT id FROM rendez_vous WHERE id = ? AND utilisateur_id = ?");
$stmt->bind_param("ii", $rendez_vous_id, $utilisateur_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Le rendez-vous n'existe pas ou n'appartient pas à l'utilisateur
    $_SESSION['message'] = "Erreur: Vous n'êtes pas autorisé à annuler ce rendez-vous.";
    header("Location: profile.php");
    exit();
}

// Mettre à jour le statut du rendez-vous
$stmt = $conn->prepare("UPDATE rendez_vous SET status = 'annulé' WHERE id = ?");
$stmt->bind_param("i", $rendez_vous_id);

if ($stmt->execute()) {
    $_SESSION['message'] = "Votre rendez-vous a été annulé avec succès.";
} else {
    $_SESSION['message'] = "Erreur lors de l'annulation du rendez-vous.";
}

// Rediriger vers la page de profil
header("Location: profile.php");
exit();