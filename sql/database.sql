CREATE DATABASE IF NOT EXISTS satisfaction_client
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE satisfaction_client;

CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager') NOT NULL DEFAULT 'admin',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    actif TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100),
    email VARCHAR(150),
    telephone VARCHAR(20),
    entreprise VARCHAR(150),
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    note_service INT NOT NULL CHECK (note_service BETWEEN 1 AND 5),
    note_qualite INT NOT NULL CHECK (note_qualite BETWEEN 1 AND 5),
    note_prix INT NOT NULL CHECK (note_prix BETWEEN 1 AND 5),
    note_globale INT NOT NULL CHECK (note_globale BETWEEN 1 AND 5),
    commentaire TEXT,
    date_evaluation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS themes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS evaluation_themes (
    evaluation_id INT NOT NULL,
    theme_id INT NOT NULL,
    PRIMARY KEY (evaluation_id, theme_id),
    FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE CASCADE,
    FOREIGN KEY (theme_id) REFERENCES themes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO themes (nom, description) VALUES
('Rapidite', 'Temps de reponse et delai de traitement'),
('Professionalisme', 'Competence et courtoise du personnel'),
('Fiabilite', 'Constance de la qualite du service'),
('Accessibilite', 'Facilite de contact et disponibilite');
