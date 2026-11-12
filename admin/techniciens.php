<?php
require_once __DIR__ . '/../includes/init.php';
requireLogin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCSRF($_POST[CSRF_TOKEN_NAME])) {
        die('Token de securite invalide.');
    }

    if ($_POST['action'] === 'add') {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $specialite = trim($_POST['service'] ?? '');

        if (empty($nom)) {
            header('Location: techniciens.php?msg=error_nom');
            exit;
        }

        $stmt = $db->prepare('INSERT INTO techniciens (nom, prenom, service) VALUES (?, ?, ?)');
        $stmt->execute([$nom, $prenom, $specialite]);
        header('Location: techniciens.php?msg=added');
        exit;
    }

    if ($_POST['action'] === 'delete') {
        $id = intval($_POST['technicien_id'] ?? 0);
        if ($id > 0) {
            $stmt = $db->prepare('DELETE FROM techniciens WHERE id = ?');
            $stmt->execute([$id]);
        }
        header('Location: techniciens.php?msg=deleted');
        exit;
    }

    if ($_POST['action'] === 'toggle') {
        $id = intval($_POST['technicien_id'] ?? 0);
        if ($id > 0) {
            $stmt = $db->prepare('UPDATE techniciens SET actif = NOT actif WHERE id = ?');
            $stmt->execute([$id]);
        }
        header('Location: techniciens.php?msg=toggled');
        exit;
    }
}

$search = trim($_GET['q'] ?? '');

$where = '';
$params = [];
if ($search !== '') {
    $where = "WHERE t.nom LIKE ? OR t.prenom LIKE ? OR t.service LIKE ?";
    $like = '%' . $search . '%';
    $params = [$like, $like, $like];
}

$techniciens = $db->prepare('
    SELECT t.*, 
        (SELECT COUNT(*) FROM evaluations e WHERE e.technicien_id = t.id) as nb_eval 
    FROM techniciens t 
    ' . $where . ' 
    ORDER BY t.nom, t.prenom
');
$techniciens->execute($params);
$techniciens = $techniciens->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techniciens - <?= SITE_NAME ?></title>
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
            <a href="techniciens.php" class="active">Techniciens</a>
            <a href="evaluations.php">Evaluations</a>
            <a href="logout.php">Deconnexion</a>
        </div>
    </nav>

    <main class="container">
        <div class="page-header">
            <h1>Techniciens</h1>
            <div class="page-actions">
                <span class="count"><?= count($techniciens) ?> technicien(s)</span>
                <a href="export_techniciens.php" target="_blank" class="btn btn-danger btn-sm">PDF</a>
                <button onclick="document.getElementById('addForm').classList.toggle('hidden')" class="btn btn-primary btn-sm">+ Ajouter</button>
            </div>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] === 'added'): ?>
                <div class="alert alert-success">Technicien ajoute.</div>
            <?php elseif ($_GET['msg'] === 'deleted'): ?>
                <div class="alert alert-success">Technicien supprime.</div>
            <?php elseif ($_GET['msg'] === 'toggled'): ?>
                <div class="alert alert-success">Statut modifie.</div>
            <?php elseif ($_GET['msg'] === 'error_nom'): ?>
                <div class="alert alert-error">Le nom est obligatoire.</div>
            <?php endif; ?>
        <?php endif; ?>

        <div id="addForm" class="form-card hidden">
            <h2>Ajouter un technicien</h2>
            <form method="POST" action="techniciens.php">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRF() ?>">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="prenom">Prenom</label>
                        <input type="text" id="prenom" name="prenom" maxlength="100">
                    </div>
                </div>
                <div class="form-group">
                    <label for="service">Service</label>
                    <input type="text" id="service" name="service" maxlength="150">
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <button type="button" onclick="document.getElementById('addForm').classList.add('hidden')" class="btn btn-ghost btn-sm">Annuler</button>
            </form>
        </div>

        <form method="GET" action="techniciens.php">
            <div class="search-bar">
                <input type="text" name="q" value="<?= sanitize($search) ?>" placeholder="Rechercher un technicien...">
                <button type="submit" class="btn btn-primary">Rechercher</button>
                <?php if ($search): ?>
                    <a href="techniciens.php" class="btn btn-ghost">Effacer</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (empty($techniciens)): ?>
            <p class="empty">Aucun technicien enregistre.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prenom</th>
                        <th>Service</th>
                        <th>Evaluations</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($techniciens as $t): ?>
                        <tr>
                            <td><?= sanitize($t['nom']) ?></td>
                            <td><?= sanitize($t['prenom'] ?? '-') ?></td>
                            <td><?= sanitize($t['service'] ?? '-') ?></td>
                            <td><?= $t['nb_eval'] ?></td>
                            <td>
                                <span class="badge <?= $t['actif'] ? 'badge-success' : 'badge-danger' ?>">
                                    <?= $t['actif'] ? 'Actif' : 'Inactif' ?>
                                </span>
                            </td>
                            <td>
                                <a href="technicien_fiche.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-primary">Fiche</a>
                                <form method="POST" action="techniciens.php" class="form-inline">
                                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRF() ?>">
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="technicien_id" value="<?= $t['id'] ?>">
                                    <button type="submit" class="btn btn-accent btn-sm">
                                        <?= $t['actif'] ? 'Desactiver' : 'Activer' ?>
                                    </button>
                                </form>
                                <form method="POST" action="techniciens.php" class="form-inline" onsubmit="return confirm('Supprimer ce technicien ?')">
                                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRF() ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="technicien_id" value="<?= $t['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>
</html>
