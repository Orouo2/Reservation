<?php
// Inclure la configuration de la base de données
include '../config/database.php';

// Inclure les classes de PHPMailer
require '../libs/PHPMailer-master/src/PHPMailer.php';
require '../libs/PHPMailer-master/src/SMTP.php';
require '../libs/PHPMailer-master/src/Exception.php';

// Si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];

    // Rechercher l'utilisateur par email
    $sql = "SELECT id, mot_de_passe, status_actif FROM utilisateurs WHERE email = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        // Si l'utilisateur existe
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $password_hash, $status_actif);
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
                    $new_token = bin2hex(random_bytes(16)); // Générer un nouveau token

                    // Mettre à jour le token dans la base de données
                    $update_sql = "UPDATE utilisateurs SET activation_token = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("si", $new_token, $id);
                    $update_stmt->execute();

                    // Envoi du nouvel email de vérification avec le nouveau token
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
                    $mail->addAddress($email); // Email du destinataire

                    // Sujet et message
                    $mail->isHTML(true); // Envoyer en HTML
                    $mail->Subject = 'Activation de votre compte';
                    $mail->Body    = "
                        <html>
                        <head>
                        <title>Activation de votre compte</title>
                        </head>
                        <body>
                        <p>Bonjour,</p>
                        <p>Merci d'avoir tenté de vous connecter. Votre compte n'est pas encore activé. Veuillez cliquer sur le lien ci-dessous pour activer votre compte :</p>
                        <p><a href='http://localhost/reservation/activation.php?token=$new_token'>Activer mon compte</a></p>
                        </body>
                        </html>
                    ";

                    if ($mail->send()) {
                        echo "Un email de vérification a été envoyé.";
                    } else {
                        echo "L'envoi de l'email de vérification a échoué : " . $mail->ErrorInfo;
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
