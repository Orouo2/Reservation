<?php
// Inclure la configuration de la base de données
include 'config/database.php';

// Vérifier si l'email est passé en paramètre
if (isset($_GET['email'])) {
    $email = $_GET['email'];

    // Mettre à jour l'état du compte (activer l'utilisateur)
    $sql = "UPDATE utilisateurs SET status_actif = 1 WHERE email = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            echo "Votre compte a été activé avec succès. Vous pouvez maintenant vous connecter.";
        } else {
            echo "Erreur lors de l'activation du compte.";
        }
    }
} else {
    echo "Email manquant.";
}

$conn->close();
?>
