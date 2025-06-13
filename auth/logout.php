<?php
/**
 * Script de déconnexion
 * Détruit la session et redirige vers la page de connexion
 */

// Inclure le fichier de configuration
require_once '../config/config.php';
require_once __DIR__ . '/../auth/auth.php';

// Détruire toutes les variables de session
$_SESSION = array();

// Détruire le cookie de session si nécessaire
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Détruire la session
session_destroy();

// Rediriger vers la page de connexion
redirect('index.php');