<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    header("Location: connexion.php");
    exit;
}

include '../config/database.php';

// Si le formulaire est soumis pour supprimer le compte
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_SESSION['id'];

    // Commencer une transaction pour supprimer les données associées
    $conn->begin_transaction();
    try {
        // Supprimer les rendez-vous associés
        $sql = "DELETE FROM rendez_vous WHERE utilisateur_id = ?";  // Changer ici "id" par "utilisateur_id"
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erreur lors de la préparation de la requête de suppression des rendez-vous.");
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Supprimer l'utilisateur
        $sql = "DELETE FROM utilisateurs WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erreur lors de la préparation de la requête de suppression de l'utilisateur.");
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Commit des changements
        $conn->commit();

        // Détruire la session et rediriger vers la page d'accueil
        session_destroy();
        header("Location: ../index.php");
        exit;
    } catch (Exception $e) {
        // En cas d'erreur, annuler la transaction
        $conn->rollback();
        echo "Une erreur est survenue lors de la suppression du compte. Erreur : " . $e->getMessage();
    }
}
?>

<!-- Confirmation de suppression -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppression du compte</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Êtes-vous sûr de vouloir supprimer votre compte ?</h2>
        <form method="post">
            <button type="submit" class="btn btn-danger">Oui, supprimer mon compte</button>
            <a href="profile.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
