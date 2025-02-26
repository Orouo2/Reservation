<?php

// Inclure la configuration de la base de données
include '../config/database.php';

// Inclure les classes de PHPMailer
require '../libs/PHPMailer-master/src/PHPMailer.php';
require '../libs/PHPMailer-master/src/SMTP.php';
require '../libs/PHPMailer-master/src/Exception.php';

// Si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données
    $nom = $_POST['nom'];
    $prénom = $_POST['prénom'];
    $date_naissance = $_POST['date_naissance'];
    $adresse_postale = $_POST['adresse_postale'];
    $téléphone = $_POST['téléphone'];
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];

    // Vérification de l'unicité de l'email
    $sql = "SELECT id FROM utilisateurs WHERE email = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            echo "Cet email est déjà utilisé.";
        } else {
            // Hashage du mot de passe
            $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_BCRYPT);

            // Générer un token d'activation
            $activation_token = bin2hex(random_bytes(16));  // Générer un token aléatoire de 32 caractères

            // Insertion dans la base de données avec le token d'activation
            $sql = "INSERT INTO utilisateurs (nom, prénom, date_naissance, adresse_postale, téléphone, email, mot_de_passe, activation_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ssssssss", $nom, $prénom, $date_naissance, $adresse_postale, $téléphone, $email, $mot_de_passe_hash, $activation_token);
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
                    $mail->setFrom('timothee@preault.com', 'preault');
                    $mail->addAddress($email, $prénom . ' ' . $nom); // Email du destinataire

                    // Sujet et message
                    $mail->isHTML(true); // Envoyer en HTML
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
        $stmt->close();
    }
}

$conn->close();
?>
