CREATE TABLE IF NOT EXISTS techniciens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100),
    specialite VARCHAR(150),
    actif TINYINT(1) DEFAULT 1,
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

ALTER TABLE evaluations
    ADD COLUMN technicien_id INT NULL AFTER client_id,
    ADD CONSTRAINT fk_eval_technicien FOREIGN KEY (technicien_id) REFERENCES techniciens(id) ON DELETE SET NULL;

INSERT INTO techniciens (nom, prenom, specialite) VALUES
('Dupont', 'Jean', 'Plomberie'),
('Martin', 'Claire', 'Electricite'),
('Leroy', 'Thomas', 'Climatisation');
