<?php
// Inclure la configuration de la base de données
include '../config/database.php';

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
                    $_SESSION['user_id'] = $id;
                    $_SESSION['email'] = $email;
                    header("Location: ../views/profile.php");
                    exit();
                } else {
                    echo "Votre compte n'est pas encore activé. Veuillez vérifier votre email.";
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
