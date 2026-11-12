<?php
require_once __DIR__ . '/../includes/init.php';
requireLogin();

$db = getDB();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: techniciens.php');
    exit;
}

$stmt = $db->prepare('SELECT * FROM techniciens WHERE id = ?');
$stmt->execute([$id]);
$tech = $stmt->fetch();

if (!$tech) {
    header('Location: techniciens.php?msg=notfound');
    exit;
}

$stats = $db->prepare('
    SELECT 
        COUNT(*) as total,
        COALESCE(AVG(note_technicien), 0) as note_moyenne,
        COALESCE(AVG(note_service), 0) as service_moyen,
        COALESCE(AVG(note_qualite), 0) as qualite_moyenne,
        COALESCE(AVG(note_prix), 0) as prix_moyen,
        COALESCE(AVG(note_globale), 0) as globale_moyenne
    FROM evaluations 
    WHERE technicien_id = ?
');
$stats->execute([$id]);
$stats = $stats->fetch();

$evaluations = $db->prepare('
    SELECT e.*, c.nom, c.prenom, c.email, c.entreprise
    FROM evaluations e
    JOIN clients c ON e.client_id = c.id
    WHERE e.technicien_id = ?
    ORDER BY e.date_evaluation DESC
');
$evaluations->execute([$id]);
$evaluations = $evaluations->fetchAll();

$notes_count = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
foreach ($evaluations as $e) {
    $n = $e['note_technicien'];
    if ($n >= 1 && $n <= 5) {
        $notes_count[$n]++;
    }
}
$max_count = max($notes_count) > 0 ? max($notes_count) : 1;

function noteColor($note) {
    if ($note >= 4.5) return '#27ae60';
    if ($note >= 3.5) return '#2ecc71';
    if ($note >= 2.5) return '#e67e22';
    if ($note >= 1.5) return '#e74c3c';
    return '#c0392b';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche <?= sanitize($tech['prenom'] . ' ' . $tech['nom']) ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= url('css/style.css') ?>">
</head>
<body>
    <nav class="navbar no-print">
        <div class="nav-brand">
            <img src="<?= url('assets/images/logo.jpg') ?>" alt="AERIA">
            <div class="nav-brand-text">
                <div class="nav-brand-title">AERIA</div>
                <div class="nav-brand-sub">Satisfaction Client</div>
            </div>
        </div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="clients.php">Clients</a>
            <a href="techniciens.php">Techniciens</a>
            <a href="evaluations.php">Evaluations</a>
            <a href="logout.php">Deconnexion</a>
        </div>
    </nav>

    <main class="container">
        <div class="page-actions no-print">
            <a href="techniciens.php" class="btn btn-ghost">&larr; Retour</a>
            <button onclick="window.print()" class="btn btn-accent">Imprimer la fiche</button>
        </div>

        <div class="form-card" style="max-width:900px">
            <div class="fiche-header">
                <div class="fiche-photo"><?= strtoupper(substr($tech['prenom'],0,1) . substr($tech['nom'],0,1)) ?></div>
                <div class="fiche-info">
                    <h1><?= sanitize($tech['prenom'] . ' ' . $tech['nom']) ?></h1>
                    <p>Service : <strong><?= sanitize($tech['service'] ?? 'N/A') ?></strong></p>
                    <p>Statut : <span class="badge <?= $tech['actif'] ? 'badge-success' : 'badge-danger' ?>"><?= $tech['actif'] ? 'Actif' : 'Inactif' ?></span></p>
                    <p>Membre depuis le <?= date('d/m/Y', strtotime($tech['date_ajout'])) ?></p>
                </div>
            </div>

            <div class="stats-row">
                <div class="stat-mini">
                    <h3 style="color:var(--primary)"><?= $stats['total'] ?></h3>
                    <p>Evaluations recues</p>
                </div>
                <div class="stat-mini">
                    <h3 style="color:<?= noteColor($stats['note_moyenne']) ?>"><?= number_format($stats['note_moyenne'], 1) ?>/5</h3>
                    <p>Note moyenne tech.</p>
                </div>
                <div class="stat-mini">
                    <h3 style="color:var(--accent)"><?= number_format($stats['service_moyen'], 1) ?>/5</h3>
                    <p>Moy. service</p>
                </div>
                <div class="stat-mini">
                    <h3 style="color:var(--green)"><?= number_format($stats['qualite_moyenne'], 1) ?>/5</h3>
                    <p>Moy. qualite</p>
                </div>
                <div class="stat-mini">
                    <h3 style="color:var(--primary-light)"><?= number_format($stats['globale_moyenne'], 1) ?>/5</h3>
                    <p>Moy. globale</p>
                </div>
            </div>

            <?php if ($stats['total'] > 0): ?>
            <h2 style="margin-bottom:0.75rem">Repartition des notes</h2>
            <div class="bar-chart">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <div class="bar-row">
                        <div class="bar-label"><?= $i ?>*</div>
                        <div class="bar-track">
                            <div class="bar-fill" style="width:<?= ($notes_count[$i] / $max_count) * 100 ?>%;background:<?= noteColor($i) ?>"><?= $notes_count[$i] ?></div>
                        </div>
                        <div class="bar-count"><?= $notes_count[$i] ?></div>
                    </div>
                <?php endfor; ?>
            </div>
            <?php endif; ?>

            <h2 style="margin:1.5rem 0 0.75rem">Detail des evaluations</h2>
            <?php if (empty($evaluations)): ?>
                <p class="empty">Aucune evaluation pour ce technicien.</p>
            <?php else: ?>
                <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date evaluation</th>
                            <th>Date travaux</th>
                            <th>Client</th>
                            <th>Entreprise</th>
                            <th>Note tech.</th>
                            <th>Service</th>
                            <th>Qualite</th>
                            <th>Globale</th>
                            <th>Travail</th>
                            <th>Horaires</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($evaluations as $e): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($e['date_evaluation'])) ?></td>
                                <td><?= $e['date_travaux'] ? date('d/m/Y', strtotime($e['date_travaux'])) : '-' ?></td>
                                <td><?= sanitize($e['prenom'] . ' ' . $e['nom']) ?></td>
                                <td><?= sanitize($e['entreprise'] ?? '-') ?></td>
                                <td>
                                    <?php if ($e['note_technicien']): ?>
                                        <span class="badge badge-<?= $e['note_technicien'] >= 4 ? 'success' : ($e['note_technicien'] >= 3 ? 'warning' : 'danger') ?>">
                                            <?= $e['note_technicien'] ?>/5
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?= $e['note_service'] ?>/5</td>
                                <td><?= $e['note_qualite'] ?>/5</td>
                                <td>
                                    <span class="badge badge-<?= $e['note_globale'] >= 4 ? 'success' : ($e['note_globale'] >= 3 ? 'warning' : 'danger') ?>">
                                        <?= $e['note_globale'] ?>/5
                                    </span>
                                </td>
                                <td title="<?= sanitize($e['travail_realise'] ?? '') ?>"><?= $e['travail_realise'] ? sanitize(mb_strimwidth($e['travail_realise'], 0, 30, '...')) : '-' ?></td>
                                <td><?= $e['heure_debut'] ? $e['heure_debut'] . ' - ' . ($e['heure_fin'] ?: '?') : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <div class="footer">
        <p>AERIA - Aeroport International d'Abidjan</p>
    </div>
</body>
</html>
