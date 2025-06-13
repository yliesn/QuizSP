<?php
// Script de test de connexion à la base de données
require_once __DIR__ . '/config.php';

try {
    $db = getDbConnection();
    if ($db) {
        echo '<p style="color:green;">Connexion à la base de données réussie !</p>';
    } else {
        echo '<p style="color:red;">Échec de la connexion à la base de données.</p>';
    }
} catch (Exception $e) {
    echo '<p style="color:red;">Erreur : ' . htmlspecialchars($e->getMessage()) . '</p>';
}
