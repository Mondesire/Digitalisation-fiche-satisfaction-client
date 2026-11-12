<?php
require_once __DIR__ . '/includes/init.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCSRF($_POST[CSRF_TOKEN_NAME])) {
        $message = 'Token de securite invalide. Veuillez recharger la page.';
    } else {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $entreprise = trim($_POST['entreprise'] ?? '');
        $note_service = intval($_POST['note_service'] ?? 0);
        $note_qualite = intval($_POST['note_qualite'] ?? 0);
        $note_prix = intval($_POST['note_prix'] ?? 0);
        $note_globale = intval($_POST['note_globale'] ?? 0);
        $commentaire = trim($_POST['commentaire'] ?? '');
        $technicien_id = intval($_POST['technicien_id'] ?? 0);
        $note_technicien = intval($_POST['note_technicien'] ?? 0);
        $travail_realise = trim($_POST['travail_realise'] ?? '');
        $heure_debut = $_POST['heure_debut'] ?? '';
        $heure_fin = $_POST['heure_fin'] ?? '';
        $date_travaux = $_POST['date_travaux'] ?? '';
        $themes = $_POST['themes'] ?? [];

        if (empty($nom) || $note_service < 1 || $note_service > 5 || $note_qualite < 1 || $note_qualite > 5 || $note_prix < 1 || $note_prix > 5 || $note_globale < 1 || $note_globale > 5) {
            $message = 'Veuillez remplir tous les champs obligatoires correctement.';
        } else {
            $db = getDB();

            $stmt = $db->prepare('INSERT INTO clients (nom, prenom, email, telephone, entreprise) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$nom, $prenom, $email, $telephone, $entreprise]);
            $client_id = $db->lastInsertId();

            $tech_id = $technicien_id > 0 ? $technicien_id : null;
            $tech_note = ($note_technicien >= 1 && $note_technicien <= 5) ? $note_technicien : null;
            $debut = !empty($heure_debut) ? $heure_debut : null;
            $fin = !empty($heure_fin) ? $heure_fin : null;
            $travaux_date = !empty($date_travaux) ? $date_travaux : null;

            $stmt = $db->prepare('INSERT INTO evaluations (client_id, technicien_id, note_technicien, travail_realise, heure_debut, heure_fin, date_travaux, note_service, note_qualite, note_prix, note_globale, commentaire) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$client_id, $tech_id, $tech_note, $travail_realise, $debut, $fin, $travaux_date, $note_service, $note_qualite, $note_prix, $note_globale, $commentaire]);
            $eval_id = $db->lastInsertId();

            if (!empty($themes)) {
                $stmt = $db->prepare('INSERT INTO evaluation_themes (evaluation_id, theme_id) VALUES (?, ?)');
                foreach ($themes as $theme_id) {
                    $validated_theme_id = intval($theme_id);
                    if ($validated_theme_id > 0) {
                        $stmt->execute([$eval_id, $validated_theme_id]);
                    }
                }
            }

            $message = 'Merci pour votre evaluation !';
        }
    }
}

$themes_list = getDB()->query('SELECT * FROM themes ORDER BY nom')->fetchAll();
$techniciens_list = getDB()->query("SELECT id, nom, prenom, service FROM techniciens WHERE actif = 1 ORDER BY nom, prenom")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= url('css/style.css') ?>">
</head>
<body>
    <header class="hero">
        <div class="hero-content">
            <img src="<?= url('assets/images/logo.jpg') ?>" alt="AERIA" class="hero-logo">
            <div class="hero-text">
                <h1>AERIA<br><span>Satisfaction Client</span></h1>
                <p class="subtitle">Aeroport International Félix Houphouët-Boigny d'Abidjan</p>
                <p class="tagline">Votre avis compte pour nous ameliorer</p>
            </div>
        </div>
    </header>

    <main class="container">
        <?php if (!empty($message)): ?>
            <div class="alert <?= strpos($message, 'Merci') !== false ? 'alert-success' : 'alert-error' ?>">
                <?= sanitize($message) ?>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <h2>Donnez votre evaluation</h2>
            <form method="POST" action="" id="evaluationForm">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRF() ?>">

                <fieldset>
                    <legend>Informations personnelles</legend>
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
                </fieldset>

                <fieldset>
                    <legend>Evaluation du service</legend>
                    <div class="rating-group">
                        <label>Service *</label>
                        <div class="stars" data-name="note_service">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <input type="radio" name="note_service" value="<?= $i ?>" id="service<?= $i ?>" required>
                                <label for="service<?= $i ?>" class="star">&#9733;</label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="rating-group">
                        <label>Qualite *</label>
                        <div class="stars" data-name="note_qualite">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <input type="radio" name="note_qualite" value="<?= $i ?>" id="qualite<?= $i ?>" required>
                                <label for="qualite<?= $i ?>" class="star">&#9733;</label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="rating-group">
                        <label>Rapport qualite/prix *</label>
                        <div class="stars" data-name="note_prix">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <input type="radio" name="note_prix" value="<?= $i ?>" id="prix<?= $i ?>" required>
                                <label for="prix<?= $i ?>" class="star">&#9733;</label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="rating-group">
                        <label>Note globale *</label>
                        <div class="stars" data-name="note_globale">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <input type="radio" name="note_globale" value="<?= $i ?>" id="globale<?= $i ?>" required>
                                <label for="globale<?= $i ?>" class="star">&#9733;</label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <?php if (!empty($themes_list)): ?>
                    <div class="form-group">
                        <label>Themes concernes</label>
                        <div class="checkbox-group">
                            <?php foreach ($themes_list as $theme): ?>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="themes[]" value="<?= $theme['id'] ?>">
                                    <?= sanitize($theme['nom']) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </fieldset>

                <fieldset>
                    <legend>Evaluation du technicien</legend>
                    <?php if (!empty($techniciens_list)): ?>
                    <div class="form-group">
                        <label for="technicien_id">Technicien intervenant</label>
                        <select id="technicien_id" name="technicien_id">
                            <option value="0">-- Choisir un technicien --</option>
                            <?php foreach ($techniciens_list as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= sanitize($t['prenom'] . ' ' . $t['nom']) ?> (<?= sanitize($t['service']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="rating-group">
                        <label>Note du technicien</label>
                        <div class="stars" data-name="note_technicien">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <input type="radio" name="note_technicien" value="<?= $i ?>" id="tech<?= $i ?>">
                                <label for="tech<?= $i ?>" class="star">&#9733;</label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="travail_realise">Travail realise</label>
                        <textarea id="travail_realise" name="travail_realise" rows="3" maxlength="500" placeholder="Decrivez le travail effectue par le technicien..."></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_travaux">Date des travaux</label>
                            <input type="date" id="date_travaux" name="date_travaux" max="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group">
                            <label for="heure_debut">Heure de debut</label>
                            <input type="time" id="heure_debut" name="heure_debut">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="heure_fin">Heure de fin</label>
                        <input type="time" id="heure_fin" name="heure_fin" style="max-width:calc(50% - 0.5rem)">
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Commentaire general</legend>
                    <div class="form-group">
                        <label for="commentaire">Commentaire</label>
                        <textarea id="commentaire" name="commentaire" rows="4" maxlength="1000"></textarea>
                    </div>
                </fieldset>

                <button type="submit" class="btn btn-primary btn-block">Envoyer mon evaluation</button>
            </form>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> AERIA - Aeroport International Félix Houphouët-Boigny d'Abidjan</p>
    </footer>

    <script src="<?= url('js/main.js') ?>"></script>
</body>
</html>
