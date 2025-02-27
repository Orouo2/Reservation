<?php 
// Inclure la configuration de la base de données
include '../config/database.php';

// Inclure les classes de PHPMailer
require '../libs/PHPMailer-master/src/PHPMailer.php';
require '../libs/PHPMailer-master/src/SMTP.php';
require '../libs/PHPMailer-master/src/Exception.php';

// Vérifier le token CSRF
if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {

    // Si le formulaire est soumis
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Récupérer les données du formulaire
        $email = $_POST['email'];
        $mot_de_passe = $_POST['mot_de_passe'];

    // Rechercher l'utilisateur par email
    $sql = "SELECT id, mot_de_passe, status_actif, nom, prénom, date_naissance, adresse_postale, téléphone FROM utilisateurs WHERE email = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        // Si l'utilisateur existe
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $password_hash, $status_actif, $nom, $prénom, $date_naissance, $adresse_postale, $téléphone);
            $stmt->fetch();

            // Vérifier si le mot de passe est correct
            if (password_verify($mot_de_passe, $password_hash)) {
                // Vérifier si l'utilisateur est actif
                if ($status_actif == 1) {
                    // L'utilisateur est authentifié et actif, redirection vers le profil
                    session_start();
                    $_SESSION['id'] = $id;
                    $_SESSION['email'] = $email;
                    header("Location: ../views/profile.php");
                    exit();
                } else {
                    // Si l'utilisateur n'est pas actif, on génère un nouveau token et on envoie un email
                    // Générer un token d'activation
                    $activation_token = bin2hex(random_bytes(16));  // Générer un token aléatoire de 32 caractères

                    // Définir la date d'expiration (10 minutes à partir de maintenant)
                    $expiration_time = date('Y-m-d H:i:s', strtotime('+10 minutes'));

                    // Insertion dans la base de données avec le token d'activation et l'heure d'expiration
                    $sql = "UPDATE utilisateurs SET activation_token = ?, token_expiration = ? WHERE email = ?";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("sss", $activation_token, $expiration_time, $email);
                        if ($stmt->execute()) {
                            // Envoi de l'email de vérification

                            // Configuration de PHPMailer
                            $mail = new PHPMailer\PHPMailer\PHPMailer();
                            $mail->isSMTP(); // Utiliser SMTP
                            $mail->Host = 'smtp.phpnet.org'; // Serveur SMTP de ton fournisseur
                            $mail->SMTPAuth = true; // Authentification
                            $mail->Username = 'timothee@preault.com'; // Ton adresse email
                            $mail->Password = 'Blenco123!'; // Ton mot de passe email
                            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; // Sécuriser la connexion
                            $mail->Port = 587; // Port SMTP

                            // Expéditeur et destinataire
                            $mail->setFrom('timothee@preault.com', 'service_de_reservation');
                            $mail->addAddress($email, $prénom . ' ' . $nom); // Email du destinataire

                            // Sujet et message
                            $mail->isHTML(true); // Envoyer en HTML
                            $mail->CharSet = 'UTF-8';
                            $mail->Subject = 'Activation de votre compte';
                            $mail->Body    = "
                                <html>
                                <head>
                                <title>Activation de votre compte</title>
                                </head>
                                <body>
                                <p>Bonjour $prénom $nom,</p>
                                <p>Merci de vous être inscrit. Veuillez cliquer sur le lien ci-dessous pour activer votre compte :</p>
                                <p><a href='http://localhost/reservation/activation.php?token=$activation_token'>Activer mon compte</a></p>
                                </body>
                                </html>
                            ";

                            if ($mail->send()) {
                                echo "Un email de vérification a été envoyé.";
                            } else {
                                echo "L'envoi de l'email de vérification a échoué : " . $mail->ErrorInfo;
                            }
                        } else {
                            echo "Erreur lors de l'inscription.";
                        }
                    }
                }
            } else {
                echo "Email ou mot de passe incorrect.";
            }
        } else {
            echo "Aucun utilisateur trouvé avec cet email.";
        }
    }
}

$conn->close();
?>
