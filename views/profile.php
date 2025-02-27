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

// Récupérer l'id de l'utilisateur depuis la session
$id = $_SESSION['id'];

// Récupérer les informations de l'utilisateur depuis la base de données en utilisant l'id
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

// Afficher le message de session s'il existe
$message = '';
if (isset($_SESSION['message'])) {
    $message = '<div class="alert alert-info">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']); // Effacer le message après l'avoir affiché
}

// Traitement du formulaire de contact
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['contact_submit'])) {
    $nom_contact = $_POST['nom_contact'];
    $email_contact = $_POST['email_contact'];
    $message_contact = $_POST['message_contact'];

    // Vérification des champs du formulaire
    if (empty($nom_contact) || empty($email_contact) || empty($message_contact)) {
        $message = '<div class="alert alert-danger">Tous les champs sont obligatoires.</div>';
    } else {
        // Configuration de l'email
        $to = "timothee@preault.com"; // Remplace par l'email de réception
        $subject = "Demande de renseignements - " . $nom_contact;
        $body = "<p>Nom : $nom_contact</p><p>Email : $email_contact</p><p>Message : $message_contact</p>";
        $headers = "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: $email_contact\r\n";

        // Envoi de l'email
        if (mail($to, $subject, $body, $headers)) {
            $message = '<div class="alert alert-success">Votre message a été envoyé avec succès.</div>';
        } else {
            $message = '<div class="alert alert-danger">Une erreur est survenue lors de l\'envoi de votre message. Veuillez réessayer.</div>';
        }
    }
}
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
        <?php echo $message; ?>
        
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

        <div class="mb-4">
            <a href="modifier_profile.php" class="btn btn-primary">Modifier mes informations</a>
            <a href="deconnexion.php" class="btn btn-danger">Se déconnecter</a>
            <a href="confirm_delete.php" class="btn btn-warning">Supprimer mon compte</a>
            <a href="prendre_rendez_vous.php" class="btn btn-success">Prendre un rendez-vous</a>
        </div>

        <?php
        // Récupérer les rendez-vous de l'utilisateur (confirmés)
        $sql = "SELECT * FROM rendez_vous WHERE utilisateur_id = ? AND status = 'confirmé' ORDER BY date_heure ASC";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                echo '<h3>Vos Rendez-vous :</h3>';
                echo '<table class="table table-bordered">';
                echo '<tr><th>Date et Heure</th><th>Statut</th><th>Action</th></tr>';
                while ($rendezvous = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . date('d/m/Y à H:i', strtotime($rendezvous['date_heure'])) . '</td>';
                    echo '<td>' . htmlspecialchars($rendezvous['status']) . '</td>';
                    echo '<td><a href="annuler_rendez_vous.php?id=' . $rendezvous['id'] . '" class="btn btn-sm btn-danger">Annuler</a></td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo "<p>Aucun rendez-vous pour l'instant.</p>";
            }
        }
        // Fermer la connexion à la base de données
        $conn->close();
        ?>

        <h3>Renseignements</h3>
        <form action="../controllers/contact.php" method="post">
            <div class="form-group">
                <label for="nom">Nom :</label>
                <input type="text" name="nom" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="message">Message :</label>
                <textarea name="message" class="form-control" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Envoyer</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
