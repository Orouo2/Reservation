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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
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
        /* Style pour le calendrier */
        .flatpickr-calendar {
            margin: 0 auto;
            max-width: 100%;
        }
        .date-selected {
            background-color: #d1e7dd;
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
                <input type="text" class="form-control" id="date" name="date" placeholder="Cliquez pour sélectionner une date" required readonly>
            </div>
            
            <div class="form-group">
                <label>Sélectionnez un créneau horaire</label>
                <input type="hidden" id="heure" name="heure" required>
                <div class="creneaux" id="creneaux-container">
                    <!-- Les créneaux seront affichés ici via JavaScript -->
                    <div class="alert alert-info">Veuillez d'abord sélectionner une date</div>
                </div>
            </div>
            <a href="profile.php" class="btn btn-secondary">Retour au profil</a>
            <button type="submit" class="btn btn-primary" id="btn-reserver" disabled>Réserver</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/fr.js"></script>
    <script>
        $(document).ready(function() {
            const dateInput = $('#date');
            const creneauxContainer = $('#creneaux-container');
            const heureInput = $('#heure');
            const btnReserver = $('#btn-reserver');
            
            // Heures de rendez-vous de 9h à 18h
            const heures = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];
            
            // Initialiser le calendrier flatpickr
            const flatpickrCalendar = flatpickr(dateInput[0], {
                locale: 'fr',
                dateFormat: 'Y-m-d',
                minDate: 'today',
                disable: [
                    function(date) {
                        // Désactiver les weekends (0 = dimanche, 6 = samedi)
                        return (date.getDay() === 0 || date.getDay() === 6);
                    }
                ],
                onChange: function(selectedDates, dateStr) {
                    if (dateStr) {
                        checkAvailability(dateStr);
                    }
                }
            });
            
            function checkAvailability(date) {
                // Requête AJAX pour obtenir les créneaux disponibles
                $.ajax({
                    url: 'disponibilites.php',
                    type: 'GET',
                    data: { date: date },
                    dataType: 'json',
                    success: function(data) {
                        creneauxContainer.empty();
                        
                        // Obtenir l'heure actuelle pour désactiver les créneaux passés
                        const currentTime = new Date();
                        const currentHour = currentTime.getHours();
                        const currentMinute = currentTime.getMinutes();
                        const currentDate = currentTime.toISOString().split('T')[0];  // Date au format 'Y-m-d'

                        heures.forEach(function(heure) {
                            const [hour, minute] = heure.split(':');
                            
                            // Vérifier si l'heure est déjà passée
                            if (date === currentDate && (parseInt(hour) < currentHour || (parseInt(hour) === currentHour && parseInt(minute) < currentMinute))) {
                                return; // Ne pas afficher les créneaux passés
                            }
                            
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
            }
        });
    </script>
</body>
</html>