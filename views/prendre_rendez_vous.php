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
$utilisateur_id = $_SESSION['id'];
$message = '';

// Traitement du formulaire de réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['date']) && isset($_POST['heure'])) {
        $date = $_POST['date'];
        $heure = $_POST['heure'];
        
        // Formatage de la date et l'heure
        $date_heure = $date . ' ' . $heure . ':00';
        
        // Vérifier si le créneau est déjà pris
        $stmt = $conn->prepare("SELECT id FROM rendez_vous WHERE date_heure = ? AND status != 'annulé'");
        $stmt->bind_param("s", $date_heure);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Le créneau est libre, on peut réserver
            // Le statut est directement mis à "confirmé" 
            $stmt = $conn->prepare("INSERT INTO rendez_vous (utilisateur_id, date_heure, status) VALUES (?, ?, 'confirmé')");
            $stmt->bind_param("is", $utilisateur_id, $date_heure);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Votre rendez-vous a été réservé et confirmé avec succès!</div>';
            } else {
                $message = '<div class="alert alert-danger">Une erreur est survenue lors de la réservation.</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">Ce créneau est déjà pris. Veuillez en choisir un autre.</div>';
        }
    }
}

// Obtenir la date actuelle
$aujourdhui = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prendre un rendez-vous</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .creneaux {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            margin-top: 20px;
        }
        .creneau {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
        }
        .creneau:hover {
            background-color: #f8f9fa;
        }
        .creneau.selected {
            background-color: #d1e7dd;
            border-color: #0d6efd;
        }
        .indisponible {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Prendre un rendez-vous</h2>
        
        <?php echo $message; ?>
        
        <form action="prendre_rendez_vous.php" method="post" id="form-reservation">
            <div class="form-group">
                <label for="date">Sélectionnez une date</label>
                <input type="date" class="form-control" id="date" name="date" min="<?php echo $aujourdhui; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Sélectionnez un créneau horaire</label>
                <input type="hidden" id="heure" name="heure" required>
                <div class="creneaux" id="creneaux-container">
                    <!-- Les créneaux seront affichés ici via JavaScript -->
                    <div class="alert alert-info">Veuillez d'abord sélectionner une date</div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" id="btn-reserver" disabled>Réserver</button>
            <a href="profile.php" class="btn btn-secondary">Retour au profil</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            const dateInput = $('#date');
            const creneauxContainer = $('#creneaux-container');
            const heureInput = $('#heure');
            const btnReserver = $('#btn-reserver');
            
            // Heures de rendez-vous de 9h à 18h
            const heures = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];
            
            dateInput.on('change', function() {
                const date = dateInput.val();
                if (!date) return;
                
                // Vérifier si la date sélectionnée est un jour de semaine (lundi-vendredi)
                const jour = new Date(date).getDay();
                if (jour === 0 || jour === 6) { // 0=dimanche, 6=samedi
                    creneauxContainer.html('<div class="alert alert-warning">Les rendez-vous ne sont disponibles que du lundi au vendredi.</div>');
                    btnReserver.prop('disabled', true);
                    return;
                }
                
                // Requête AJAX pour obtenir les créneaux disponibles
                $.ajax({
                    url: 'disponibilites.php',
                    type: 'GET',
                    data: { date: date },
                    dataType: 'json',
                    success: function(data) {
                        creneauxContainer.empty();
                        
                        heures.forEach(function(heure) {
                            // Vérifier si le créneau est disponible
                            if (!data.includes(heure)) {
                                const creneau = $('<div class="creneau"></div>');
                                creneau.text(heure);
                                
                                creneau.on('click', function() {
                                    // Désélectionner tous les créneaux
                                    $('.creneau').removeClass('selected');
                                    
                                    // Sélectionner ce créneau
                                    creneau.addClass('selected');
                                    heureInput.val(heure);
                                    btnReserver.prop('disabled', false);
                                });
                                
                                creneauxContainer.append(creneau);
                            }
                        });
                        
                        if (creneauxContainer.children().length === 0) {
                            creneauxContainer.html('<div class="alert alert-warning">Aucun créneau disponible pour cette date.</div>');
                            btnReserver.prop('disabled', true);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Erreur:', error);
                        creneauxContainer.html('<div class="alert alert-danger">Une erreur est survenue lors de la récupération des créneaux.</div>');
                    }
                });
            });
        });
    </script>
</body>
</html>