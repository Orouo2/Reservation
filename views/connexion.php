<?php
// Démarrer la session
session_start();

// Vérifier si un token CSRF existe, sinon en générer un
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));  // Génère un token aléatoire
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Connexion</h2>
        <form action="../controllers/connexion_controller.php" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
            </div>

            <!-- Ajouter le token CSRF dans le formulaire -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <a href="../index.php" class="btn btn-secondary">Retour</a>
            <button type="submit" class="btn btn-primary">Se connecter</button>
        </form>
    </div>
</body>
</html>
