<?php
// Configuration de la base de données
$host = 'localhost'; // Nom d'hôte de la base de données
$db = 'reservation'; // Nom de la base de données
$user = 'root'; // Nom d'utilisateur de la base de données
$pass = ''; // Mot de passe de la base de données

// Connexion à la base de données
$conn = new mysqli($host, $user, $pass, $db);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}
?>
