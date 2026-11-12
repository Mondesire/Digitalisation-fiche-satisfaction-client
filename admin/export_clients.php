<?php
require_once __DIR__ . '/../includes/init.php';
requireLogin();

$db = getDB();
$clients = $db->query('SELECT * FROM clients ORDER BY nom, prenom')->fetchAll();
$date = date('d/m/Y à H:i');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Clients - <?= $date ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; color: #222; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0c2d57; padding-bottom: 15px; }
        .header h1 { font-size: 20px; color: #0c2d57; margin-bottom: 4px; }
        .header p { font-size: 11px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #0c2d57; color: white; padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; }
        td { padding: 7px 10px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background: #f8fafc; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
        .count { font-size: 11px; color: #666; margin-bottom: 10px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:right;margin-bottom:10px">
        <button onclick="window.print()" style="padding:8px 20px;background:#0c2d57;color:white;border:none;border-radius:6px;cursor:pointer;font-size:13px">Imprimer / Enregistrer en PDF</button>
    </div>

    <div class="header">
        <h1>AERIA</h1>
        <h2 style="font-size:14px;color:#0c2d57;margin-bottom:4px">Liste des Clients</h2>
        <p>Genere le <?= $date ?></p>
    </div>

    <p class="count">Total : <?= count($clients) ?> client(s)</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nom</th>
                <th>Prenom</th>
                <th>Email</th>
                <th>Telephone</th>
                <th>Entreprise</th>
                <th>Date d'ajout</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clients as $i => $c): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= sanitize($c['nom']) ?></td>
                    <td><?= sanitize($c['prenom'] ?? '-') ?></td>
                    <td><?= sanitize($c['email'] ?? '-') ?></td>
                    <td><?= sanitize($c['telephone'] ?? '-') ?></td>
                    <td><?= sanitize($c['entreprise'] ?? '-') ?></td>
                    <td><?= date('d/m/Y', strtotime($c['date_ajout'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        AERIA - Aeroport International d'Abidjan &mdash; Document genere automatiquement le <?= $date ?>
    </div>
</body>
</html>
