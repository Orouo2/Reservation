<?php
include 'config/database.php';

// Vérifier si le token est présent dans l'URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Vérifier si un utilisateur avec ce token existe
    $sql = "SELECT id, activation_token, token_expiration FROM utilisateurs WHERE activation_token = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();

        // Si le token est trouvé
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $activation_token, $token_expiration);
            $stmt->fetch();

            // Vérifier si le token est expiré
            $current_time = date('Y-m-d H:i:s');

            if ($current_time > $token_expiration) {
                echo "<h2>Le token a expiré.</h2>";
                echo "<p>Veuillez demander un nouveau token d'activation.</p>";
            } else {
                // Activer le compte de l'utilisateur
                $update_sql = "UPDATE utilisateurs SET status_actif = 1 WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("i", $id);
                $update_stmt->execute();

                // Afficher un message de confirmation avec un bouton pour se connecter
                echo "<h2>Votre compte a été activé avec succès !</h2>";
                echo "<p>Vous pouvez maintenant vous connecter.</p>";
                echo "<a href='views/connexion.php' class='btn btn-primary'>Se connecter</a>";
            }
        } else {
            echo "Token invalide.";
        }
    }
} else {
    echo "Aucun token d'activation trouvé.";
}

$conn->close();
?>

<!-- Inclusion de Bootstrap -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
