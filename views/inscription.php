<?php
// Démarre la session si ce n'est pas déjà fait
session_start();

// Générer un token CSRF si ce n'est pas déjà fait
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Générer un token unique
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Fonction pour valider l'email
        function validateEmail(email) {
            const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
            return regex.test(email);
        }

        // Fonction pour valider le numéro de téléphone
        function validatePhoneNumber(phone) {
            const regex = /^[0-9]{10}$/; // Format classique français
            return regex.test(phone);
        }

        // Vérification du formulaire avant soumission
        document.querySelector("form").addEventListener("submit", function(event) {
            const email = document.getElementById("email").value;
            const phone = document.getElementById("téléphone").value;

            // Validation de l'email
            if (!validateEmail(email)) {
                alert("L'email n'est pas valide.");
                event.preventDefault(); // Empêcher la soumission du formulaire
            }

            // Validation du numéro de téléphone
            if (!validatePhoneNumber(phone)) {
                alert("Le numéro de téléphone n'est pas valide. Il doit contenir 10 chiffres.");
                event.preventDefault(); // Empêcher la soumission du formulaire
            }
        });
    </script>
</head>
<body>
    <div class="container mt-5">
        <h2>Formulaire d'inscription</h2>
        <form action="../controllers/inscription_controller.php" method="POST">
            <!-- Champ caché avec le token CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="mb-3">
                <label for="nom" class="form-label">Nom</label>
                <input type="text" class="form-control" id="nom" name="nom" required>
            </div>
            <div class="mb-3">
                <label for="prénom" class="form-label">Prénom</label>
                <input type="text" class="form-control" id="prénom" name="prénom" required>
            </div>
            <div class="mb-3">
                <label for="date_naissance" class="form-label">Date de naissance</label>
                <input type="date" class="form-control" id="date_naissance" name="date_naissance" required>
            </div>
            <div class="mb-3">
                <label for="adresse_postale" class="form-label">Adresse postale</label>
                <input type="text" class="form-control" id="adresse_postale" name="adresse_postale" required>
            </div>
            <div class="mb-3">
                <label for="téléphone" class="form-label">Numéro de téléphone</label>
                <input type="text" class="form-control" id="téléphone" name="téléphone" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="mot_de_passe" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
            </div>
            <a href="../index.php" class="btn btn-secondary">Retour</a>
            <button type="submit" class="btn btn-primary">S'inscrire</button>
        </form>
    </div>
</body>
</html>
