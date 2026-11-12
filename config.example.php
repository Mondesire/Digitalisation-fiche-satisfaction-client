<?php
$isProduction = ($_SERVER['SERVER_NAME'] ?? '') !== 'localhost';

define('DB_HOST', $isProduction ? 'localhost' : 'localhost');
define('DB_NAME', $isProduction ? 'votre_nom_bdd' : 'satisfaction_client');
define('DB_USER', $isProduction ? 'votre_user_bdd' : 'root');
define('DB_PASS', $isProduction ? 'Mondesire' : '');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'AERIA - Gestion de Satisfaction Client');
define('SITE_URL', $isProduction ? 'https://votre-domaine.com' : 'http://localhost/Default%20Project');

define('ITEMS_PER_PAGE', 10);
define('CSRF_TOKEN_NAME', 'csrf_token');

session_start();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

if ($isProduction) {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}
