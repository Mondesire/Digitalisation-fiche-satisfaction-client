ALTER TABLE evaluations
    ADD COLUMN note_technicien INT NULL AFTER technicien_id,
    ADD COLUMN travail_realise TEXT NULL AFTER note_technicien,
    ADD COLUMN heure_debut TIME NULL AFTER travail_realise,
    ADD COLUMN heure_fin TIME NULL AFTER heure_debut;
