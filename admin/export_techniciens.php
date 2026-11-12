<?php
require_once __DIR__ . '/../includes/init.php';
requireLogin();

$db = getDB();
$techniciens = $db->query('
    SELECT t.*, 
        (SELECT COUNT(*) FROM evaluations e WHERE e.technicien_id = t.id) as nb_eval,
        (SELECT ROUND(AVG(e.note_technicien), 1) FROM evaluations e WHERE e.technicien_id = t.id AND e.note_technicien IS NOT NULL) as note_moy
    FROM techniciens t 
    ORDER BY t.nom, t.prenom
')->fetchAll();
$date = date('d/m/Y à H:i');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Techniciens - <?= $date ?></title>
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
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 600; }
        .badge-active { background: #dcfce7; color: #166534; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }
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
        <h2 style="font-size:14px;color:#0c2d57;margin-bottom:4px">Liste des Techniciens</h2>
        <p>Genere le <?= $date ?></p>
    </div>

    <p class="count">Total : <?= count($techniciens) ?> technicien(s)</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nom</th>
                <th>Prenom</th>
                <th>Service</th>
                <th>Evaluations</th>
                <th>Note moyenne</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($techniciens as $i => $t): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= sanitize($t['nom']) ?></td>
                    <td><?= sanitize($t['prenom'] ?? '-') ?></td>
                    <td><?= sanitize($t['service'] ?? '-') ?></td>
                    <td><?= $t['nb_eval'] ?></td>
                    <td><?= $t['note_moy'] ? $t['note_moy'] . '/5' : '-' ?></td>
                    <td>
                        <span class="badge <?= $t['actif'] ? 'badge-active' : 'badge-inactive' ?>">
                            <?= $t['actif'] ? 'Actif' : 'Inactif' ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        AERIA - Aeroport International d'Abidjan &mdash; Document genere automatiquement le <?= $date ?>
    </div>
</body>
</html>
