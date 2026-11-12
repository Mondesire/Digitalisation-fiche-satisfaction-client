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
        $email = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $entreprise = trim($_POST['entreprise'] ?? '');

        if (empty($nom)) {
            header('Location: clients.php?msg=error_nom');
            exit;
        }

        $stmt = $db->prepare('INSERT INTO clients (nom, prenom, email, telephone, entreprise) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$nom, $prenom, $email, $telephone, $entreprise]);
        header('Location: clients.php?msg=added');
        exit;
    }

    if ($_POST['action'] === 'delete') {
        $id = intval($_POST['client_id'] ?? 0);
        if ($id > 0) {
            $stmt = $db->prepare('DELETE FROM clients WHERE id = ?');
            $stmt->execute([$id]);
        }
        header('Location: clients.php?msg=deleted');
        exit;
    }
}

$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * ITEMS_PER_PAGE;
$search = trim($_GET['q'] ?? '');

$where = '';
$params = [];
if ($search !== '') {
    $where = "WHERE nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR entreprise LIKE ?";
    $like = '%' . $search . '%';
    $params = [$like, $like, $like, $like];
}

$count_sql = 'SELECT COUNT(*) FROM clients ' . $where;
$total = $db->prepare($count_sql);
$total->execute($params);
$total = $total->fetchColumn();

$sql = "SELECT * FROM clients " . $where . " ORDER BY date_ajout DESC LIMIT ? OFFSET ?";
$params[] = ITEMS_PER_PAGE;
$params[] = $offset;
$clients = $db->prepare($sql);
$clients->execute($params);
$clients = $clients->fetchAll();
$total_pages = ceil($total / ITEMS_PER_PAGE);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clients - <?= SITE_NAME ?></title>
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
            <a href="clients.php" class="active">Clients</a>
            <a href="techniciens.php">Techniciens</a>
            <a href="evaluations.php">Evaluations</a>
            <a href="logout.php">Deconnexion</a>
        </div>
    </nav>

    <main class="container">
        <div class="page-header">
            <h1>Clients</h1>
            <div class="page-actions">
                <span class="count"><?= $total ?> client(s)</span>
                <a href="export_clients.php" target="_blank" class="btn btn-danger btn-sm">PDF</a>
                <button onclick="document.getElementById('addForm').classList.toggle('hidden')" class="btn btn-primary btn-sm">+ Ajouter</button>
            </div>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] === 'added'): ?>
                <div class="alert alert-success">Client ajoute avec succes.</div>
            <?php elseif ($_GET['msg'] === 'deleted'): ?>
                <div class="alert alert-success">Client supprime.</div>
            <?php elseif ($_GET['msg'] === 'error_nom'): ?>
                <div class="alert alert-error">Le nom est obligatoire.</div>
            <?php endif; ?>
        <?php endif; ?>

        <div id="addForm" class="form-card hidden">
            <h2>Ajouter un client</h2>
            <form method="POST" action="clients.php">
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
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="telephone">Telephone</label>
                        <input type="tel" id="telephone" name="telephone" maxlength="20">
                    </div>
                </div>
                <div class="form-group">
                    <label for="entreprise">Entreprise</label>
                    <input type="text" id="entreprise" name="entreprise" maxlength="150">
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <button type="button" onclick="document.getElementById('addForm').classList.add('hidden')" class="btn btn-ghost btn-sm">Annuler</button>
            </form>
        </div>

        <form method="GET" action="clients.php">
            <div class="search-bar">
                <input type="text" name="q" value="<?= sanitize($search) ?>" placeholder="Rechercher un client...">
                <button type="submit" class="btn btn-primary">Rechercher</button>
                <?php if ($search): ?>
                    <a href="clients.php" class="btn btn-ghost">Effacer</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (empty($clients)): ?>
            <p class="empty">Aucun client enregistre.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Prenom</th>
                        <th>Email</th>
                        <th>Entreprise</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $c): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td><?= sanitize($c['nom']) ?></td>
                            <td><?= sanitize($c['prenom'] ?? '-') ?></td>
                            <td><?= sanitize($c['email'] ?? '-') ?></td>
                            <td><?= sanitize($c['entreprise'] ?? '-') ?></td>
                            <td><?= date('d/m/Y', strtotime($c['date_ajout'])) ?></td>
                            <td>
                                <form method="POST" action="clients.php" class="form-inline" onsubmit="return confirm('Supprimer ce client ?')">
                                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRF() ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="client_id" value="<?= $c['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

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
