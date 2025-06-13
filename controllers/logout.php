<?php
/**
 * Contrôleur de déconnexion
 * Détruit la session et redirige vers la page de connexion
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/auth.php';
logout();
