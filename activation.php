<?php
include 'config/database.php';

// Vérifier si le token est présent dans l'URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Vérifier si un utilisateur avec ce token existe
    $sql = "SELECT id, activation_token FROM utilisateurs WHERE activation_token = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();

        // Si le token est trouvé
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $activation_token);
            $stmt->fetch();

            // Activer le compte de l'utilisateur
            $update_sql = "UPDATE utilisateurs SET status_actif = 1, activation_token = NULL WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $id);
            $update_stmt->execute();

            echo "Votre compte a été activé avec succès. Vous pouvez maintenant vous connecter.";
        } else {
            echo "Token invalide.";
        }
    }
} else {
    echo "Aucun token d'activation trouvé.";
}

$conn->close();
?>
