<?php
// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id'])) {  // Vérifier l'ID au lieu de l'email
    header("Location: connexion.php");
    exit();
}

// Inclure la configuration de la base de données
include '../config/database.php';

// Récupérer l'id de l'utilisateur depuis la session
$id = $_SESSION['id'];

// Récupérer les informations de l'utilisateur depuis la base de données en utilisant l'id
$sql = "SELECT * FROM utilisateurs WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id); // Utiliser l'id de l'utilisateur pour la requête
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        echo "Utilisateur introuvable.";
        exit();
    }
}

// Fermer la connexion à la base de données
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Utilisateur</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Bienvenue, <?php echo htmlspecialchars($user['prénom']); ?> <?php echo htmlspecialchars($user['nom']); ?> !</h2>
        <p>Voici vos informations personnelles :</p>
        <table class="table table-bordered">
            <tr>
                <th>Nom</th>
                <td><?php echo htmlspecialchars($user['nom']); ?></td>
            </tr>
            <tr>
                <th>Prénom</th>
                <td><?php echo htmlspecialchars($user['prénom']); ?></td>
            </tr>
            <tr>
                <th>Date de naissance</th>
                <td><?php echo htmlspecialchars($user['date_naissance']); ?></td>
            </tr>
            <tr>
                <th>Adresse postale</th>
                <td><?php echo htmlspecialchars($user['adresse_postale']); ?></td>
            </tr>
            <tr>
                <th>Téléphone</th>
                <td><?php echo htmlspecialchars($user['téléphone']); ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
            </tr>
        </table>

        <a href="modifier_profile.php" class="btn btn-primary">Modifier mes informations</a>
        <a href="deconnexion.php" class="btn btn-danger">Se déconnecter</a>

        <!-- Lien pour la suppression du compte -->
        <a href="confirm_delete.php" class="btn btn-warning">Supprimer mon compte</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
