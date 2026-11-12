<?php
require_once __DIR__ . '/../includes/init.php';
requireLogin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCSRF($_POST[CSRF_TOKEN_NAME])) {
        die('Token de securite invalide.');
    }

    if ($_POST['action'] === 'delete') {
        $id = intval($_POST['eval_id'] ?? 0);
        if ($id > 0) {
            $stmt = $db->prepare('DELETE FROM evaluations WHERE id = ?');
            $stmt->execute([$id]);
        }
        header('Location: evaluations.php?msg=deleted');
        exit;
    }
}

$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * ITEMS_PER_PAGE;

$total = $db->query('SELECT COUNT(*) FROM evaluations')->fetchColumn();
$evaluations = $db->prepare('
    SELECT e.*, c.nom, c.prenom, c.entreprise, t.nom as tech_nom, t.prenom as tech_prenom
    FROM evaluations e
    JOIN clients c ON e.client_id = c.id
    LEFT JOIN techniciens t ON e.technicien_id = t.id
    ORDER BY e.date_evaluation DESC
    LIMIT ? OFFSET ?
');
$evaluations->execute([ITEMS_PER_PAGE, $offset]);
$evaluations = $evaluations->fetchAll();
$total_pages = ceil($total / ITEMS_PER_PAGE);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluations - <?= SITE_NAME ?></title>
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
            <a href="dashboard.php">Dashboard</a>
            <a href="clients.php">Clients</a>
            <a href="techniciens.php">Techniciens</a>
            <a href="evaluations.php" class="active">Evaluations</a>
            <a href="logout.php">Deconnexion</a>
        </div>
    </nav>

    <main class="container">
        <div class="page-header">
            <h1>Evaluations</h1>
            <span class="count"><?= $total ?> evaluation(s)</span>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success">Action effectuee avec succes.</div>
        <?php endif; ?>

        <?php if (empty($evaluations)): ?>
            <p class="empty">Aucune evaluation.</p>
        <?php else: ?>
            <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Technicien</th>
                        <th>Travail</th>
                        <th>Date travaux</th>
                        <th>Heure</th>
                        <th>Note tech.</th>
                        <th>Service</th>
                        <th>Qualite</th>
                        <th>Prix</th>
                        <th>Globale</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($evaluations as $e): ?>
                        <tr>
                            <td><?= sanitize($e['prenom'] . ' ' . $e['nom']) ?></td>
                            <td><?= $e['tech_nom'] ? sanitize($e['tech_prenom'] . ' ' . $e['tech_nom']) : '<em style="color:var(--gray-500)">-</em>' ?></td>
                            <td title="<?= sanitize($e['travail_realise'] ?? '') ?>"><?= $e['travail_realise'] ? sanitize(mb_strimwidth($e['travail_realise'], 0, 40, '...')) : '-' ?></td>
                            <td><?= $e['date_travaux'] ? date('d/m/Y', strtotime($e['date_travaux'])) : '-' ?></td>
                            <td><?= $e['heure_debut'] ? $e['heure_debut'] . ' - ' . ($e['heure_fin'] ?: '?') : '-' ?></td>
                            <td><?= $e['note_technicien'] ? $e['note_technicien'] . '/5' : '-' ?></td>
                            <td><?= $e['note_service'] ?>/5</td>
                            <td><?= $e['note_qualite'] ?>/5</td>
                            <td><?= $e['note_prix'] ?>/5</td>
                            <td>
                                <span class="badge badge-<?= $e['note_globale'] >= 4 ? 'success' : ($e['note_globale'] >= 3 ? 'warning' : 'danger') ?>">
                                    <?= $e['note_globale'] ?>/5
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($e['date_evaluation'])) ?></td>
                            <td>
                                <form method="POST" action="evaluations.php" class="form-inline" onsubmit="return confirm('Supprimer cette evaluation ?')">
                                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRF() ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="eval_id" value="<?= $e['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="btn btn-sm">&laquo; Precedent</a>
                    <?php endif; ?>
                    <span>Page <?= $page ?> / <?= $total_pages ?></span>
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>" class="btn btn-sm">Suivant &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</body>
</html>
