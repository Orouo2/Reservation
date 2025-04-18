-- Création de la base de données si elle n'existe pas déjà
CREATE DATABASE IF NOT EXISTS preault_db_reservation;

-- Utilisation de la base de données
USE preault_db_reservation;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prénom VARCHAR(100) NOT NULL,
    date_naissance DATE NOT NULL,
    adresse_postale VARCHAR(255) NOT NULL,
    téléphone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    status_actif TINYINT(1) DEFAULT 0,
    activation_token VARCHAR(64) DEFAULT NULL,
    token_expiration DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des rendez-vous
CREATE TABLE IF NOT EXISTS rendez_vous (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    date_heure DATETIME NOT NULL,
    status ENUM('confirmé', 'annulé', 'en_attente') NOT NULL DEFAULT 'en_attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);
