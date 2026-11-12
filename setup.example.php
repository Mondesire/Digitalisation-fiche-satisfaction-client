<?php
require_once __DIR__ . '/includes/init.php';

$email = 'mondesire@example.com';
$nom = 'Admin';
$plain_password = 'Mondesire';
$role = 'admin';
$hash = password_hash($plain_password, PASSWORD_BCRYPT);

require_once __DIR__ . '/includes/db.php';
$db = getDB();

$stmt = $db->prepare('DELETE FROM utilisateurs WHERE email = ?');
$stmt->execute([$email]);

$stmt = $db->prepare('INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)');
$stmt->execute([$nom, $email, $hash, $role]);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Admin</title>
    <link rel="stylesheet" href="<?= url('css/style.css') ?>">
</head>
<body>
    <div class="container" style="margin-top:4rem;">
        <div class="form-card">
            <h1>Setup Admin</h1>
            <div class="alert alert-success">
                <strong>Compte admin cree avec succes.</strong><br><br>
                <strong>Email :</strong> <?= sanitize($email) ?><br>
                <strong>Mot de passe :</strong> <?= sanitize($plain_password) ?><br><br>
                <em>Changez ce mot de passe apres la premiere connexion.</em>
            </div>
            <a href="admin/login.php" class="btn btn-primary">Aller a la connexion</a>
        </div>
    </div>
</body>
</html>
