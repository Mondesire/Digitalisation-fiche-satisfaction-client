<?php
require_once __DIR__ . '/../includes/init.php';
requireLogin();

$db = getDB();

$stats = [
    'total_evaluations' => 0,
    'note_moyenne' => 0,
    'total_clients' => 0,
    'evaluations_mois' => 0,
];

$result = $db->query('SELECT COUNT(*) as total, COALESCE(AVG(note_globale), 0) as moyenne FROM evaluations');
$row = $result->fetch();
$stats['total_evaluations'] = $row['total'];
$stats['note_moyenne'] = round($row['moyenne'], 1);

$result = $db->query('SELECT COUNT(*) as total FROM clients');
$stats['total_clients'] = $result->fetch()['total'];

$result = $db->query("SELECT COUNT(*) as total FROM evaluations WHERE MONTH(date_evaluation) = MONTH(CURRENT_DATE()) AND YEAR(date_evaluation) = YEAR(CURRENT_DATE())");
$stats['evaluations_mois'] = $result->fetch()['total'];

$recent = $db->query('
    SELECT e.*, c.nom, c.prenom, c.entreprise, t.nom as tech_nom, t.prenom as tech_prenom
    FROM evaluations e 
    JOIN clients c ON e.client_id = c.id 
    LEFT JOIN techniciens t ON e.technicien_id = t.id
    ORDER BY e.date_evaluation DESC 
    LIMIT 5
')->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= url('css/style.css') ?>">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <img src="<?= url('assets/images/logo.jpg') ?>" alt="AERIA">
            <div class="nav-brand-text">
                <div class="nav-brand-title">AERIA</div>
                <div class="nav-brand-sub">Satisfaction Client</div>
            </div>
        </div>
        <div class="nav-links">
            <a href="<?= url('admin/dashboard.php') ?>" class="active">Dashboard</a>
            <a href="<?= url('admin/clients.php') ?>">Clients</a>
            <a href="<?= url('admin/techniciens.php') ?>">Techniciens</a>
            <a href="<?= url('admin/evaluations.php') ?>">Evaluations</a>
            <a href="<?= url('admin/logout.php') ?>">Deconnexion</a>
        </div>
    </nav>

    <main class="container">
        <h1>Tableau de bord</h1>
        <p class="welcome">Bienvenue, <?= sanitize($_SESSION['user_nom']) ?></p>

        <div class="stats-grid">
            <div class="stat-card">
                <h3><?= $stats['total_evaluations'] ?></h3>
                <p>Total evaluations</p>
            </div>
            <div class="stat-card">
                <h3><?= $stats['note_moyenne'] ?>/5</h3>
                <p>Note moyenne</p>
            </div>
            <div class="stat-card">
                <h3><?= $stats['total_clients'] ?></h3>
                <p>Clients enregistres</p>
            </div>
            <div class="stat-card">
                <h3><?= $stats['evaluations_mois'] ?></h3>
                <p>Evaluations ce mois</p>
            </div>
        </div>

        <div class="section">
            <h2>Evaluations recentes</h2>
            <?php if (empty($recent)): ?>
                <p class="empty">Aucune evaluation pour le moment.</p>
            <?php else: ?>
                <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Technicien</th>
                            <th>Entreprise</th>
                            <th>Note globale</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent as $eval): ?>
                            <tr>
                                <td><?= sanitize($eval['prenom'] . ' ' . $eval['nom']) ?></td>
                                <td><?= $eval['tech_nom'] ? sanitize($eval['tech_prenom'] . ' ' . $eval['tech_nom']) : '<em style="color:var(--gray-500)">-</em>' ?></td>
                                <td><?= sanitize($eval['entreprise'] ?? '-') ?></td>
                                <td><span class="badge badge-<?= $eval['note_globale'] >= 4 ? 'success' : ($eval['note_globale'] >= 3 ? 'warning' : 'danger') ?>"><?= $eval['note_globale'] ?>/5</span></td>
                                <td><?= date('d/m/Y H:i', strtotime($eval['date_evaluation'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
