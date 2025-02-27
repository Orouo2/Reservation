<?php
// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    header("Location: connexion.php");
    exit();
}

// Générer un token CSRF si ce n'est pas déjà fait
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Créer un nouveau token
}

// Inclure la configuration de la base de données
include '../config/database.php';

// Récupérer l'id de l'utilisateur connecté
$id = $_SESSION['id'];

// Si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Token CSRF invalide.");
    }

    // Récupérer les nouvelles informations de l'utilisateur
    $nom = $_POST['nom'];
    $prénom = $_POST['prénom'];
    $date_naissance = $_POST['date_naissance'];
    $adresse_postale = $_POST['adresse_postale'];
    $téléphone = $_POST['téléphone'];
    $email_modifié = $_POST['email'];

    // Vérification de l'unicité de l'email
    if ($email_modifié !== $_SESSION['email']) {
        $sql = "SELECT id FROM utilisateurs WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email_modifié);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                echo "Cet email est déjà utilisé.";
                exit();
            }
        }
    }

    // Mettre à jour les informations dans la base de données en utilisant l'ID
    $sql = "UPDATE utilisateurs SET nom = ?, prénom = ?, date_naissance = ?, adresse_postale = ?, téléphone = ?, email = ? WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssssi", $nom, $prénom, $date_naissance, $adresse_postale, $téléphone, $email_modifié, $id);
        if ($stmt->execute()) {
            // Mettre à jour l'email en session après la mise à jour dans la base de données
            $_SESSION['email'] = $email_modifié;
            echo "Vos informations ont été mises à jour avec succès.";
        } else {
            echo "Erreur lors de la mise à jour des informations.";
        }
    }
}

// Récupérer les informations actuelles de l'utilisateur depuis la base de données
$sql = "SELECT * FROM utilisateurs WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id);
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
    <title>Modifier Profil</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Modifier vos informations personnelles</h2>
        <form method="post" action="modifier_profile.php">
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
            </div>
            <div class="form-group">
                <label for="prénom">Prénom</label>
                <input type="text" class="form-control" id="prénom" name="prénom" value="<?php echo htmlspecialchars($user['prénom']); ?>" required>
            </div>
            <div class="form-group">
                <label for="date_naissance">Date de naissance</label>
                <input type="date" class="form-control" id="date_naissance" name="date_naissance" value="<?php echo htmlspecialchars($user['date_naissance']); ?>" required>
            </div>
            <div class="form-group">
                <label for="adresse_postale">Adresse postale</label>
                <input type="text" class="form-control" id="adresse_postale" name="adresse_postale" value="<?php echo htmlspecialchars($user['adresse_postale']); ?>" required>
            </div>
            <div class="form-group">
                <label for="téléphone">Numéro de téléphone</label>
                <input type="text" class="form-control" id="téléphone" name="téléphone" value="<?php echo htmlspecialchars($user['téléphone']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
        </form>
        <a href="profile.php" class="btn btn-secondary mt-3">Retour au profil</a>
    </div>
</body>
</html>
