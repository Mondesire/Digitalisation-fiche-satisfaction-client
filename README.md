\# Digitalisation de fiche de satisfaction client



Application web de collecte et de suivi des évaluations de satisfaction client, avec un espace d'administration pour exploiter les résultats.



\## Fonctionnalités



\- Formulaire public de satisfaction client

\- Espace administrateur sécurisé (connexion)

\- Tableau de bord des évaluations

\- Gestion des clients et des techniciens

\- Fiche détaillée par technicien

\- Export des données clients et techniciens



\## Technologies



\- PHP (accès base de données via PDO)

\- MySQL

\- HTML, CSS, JavaScript

\- Apache (.htaccess : protection des dossiers sensibles, compression, cache)



\## Installation (en local)



1\. Cloner le dépôt dans le dossier de votre serveur local (WAMP, XAMPP...)

2\. Créer une base de données MySQL, puis importer dans cet ordre :

&#x20;  - `sql/database.sql`

&#x20;  - `sql/migration\_techniciens.sql`

&#x20;  - `sql/migration\_intervention.sql`

&#x20;  - `sql/migration\_date\_travaux.sql`

3\. Copier `config.example.php` en `config.php` et renseigner vos paramètres de base de données

4\. Copier `setup.example.php` en `setup.php`, renseigner vos identifiants administrateur, puis ouvrir `/setup.php` dans le navigateur pour créer le compte admin

5\. Supprimer `setup.php` après utilisation



\## Notes



\- Les fichiers `config.php` et `setup.php` ne sont pas versionnés (identifiants).

\- Les logos du projet ne sont pas inclus dans ce dépôt.



\## Auteur



Yao Kouadio Mondésire Joël

