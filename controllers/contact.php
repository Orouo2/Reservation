<?php
// Démarrer la session
session_start();

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $nom = htmlspecialchars($_POST['nom']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    // Vérifier que tous les champs sont remplis
    if (empty($nom) || empty($email) || empty($message)) {
        $_SESSION['message'] = "Tous les champs sont obligatoires.";
        header("Location: ../views/profile.php");
        exit();
    }

    // Inclure PHPMailer
    require '../libs/PHPMailer-master/src/PHPMailer.php';
    require '../libs/PHPMailer-master/src/SMTP.php';
    require '../libs/PHPMailer-master/src/Exception.php';

    // Configurer et envoyer l'email avec PHPMailer
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.phpnet.org'; // À remplacer par ton serveur SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'timothee@preault.com'; // Ton email SMTP
        $mail->Password = 'Blenco123!'; // Ton mot de passe SMTP
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Expéditeur et destinataire
        $mail->setFrom('timothee@preault.com', 'Service de Contact'); // Adresse email de ton domaine
        $mail->addReplyTo($email); // Permet de répondre à l'expéditeur réel
        $mail->addAddress('timothee@preault.com', 'Service Client'); // Mettre l'email qui recevra le message

        // Sujet et contenu du mail
        $mail->isHTML(true);
        $mail->Subject = "Nouveau message de contact";
        $mail->Body    = "<p><strong>Nom :</strong> $nom</p>
                          <p><strong>Email :</strong> $email</p>
                          <p><strong>Message :</strong><br>$message</p>";

        if ($mail->send()) {
            $_SESSION['message'] = "Votre message a été envoyé avec succès.";
        } else {
            $_SESSION['message'] = "Erreur lors de l'envoi du message : " . $mail->ErrorInfo;
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur d'envoi : " . $mail->ErrorInfo;
    }

    header("Location: ../views/profile.php");
    exit();
}
?>
