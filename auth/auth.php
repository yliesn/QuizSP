<?php
/**
 * Fonctions d'authentification et de gestion de session
 */

// Démarre la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Vérifie si l'utilisateur est connecté
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Force la connexion : redirige vers la page de login si l'utilisateur n'est pas connecté
 */
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['error_message'] = "Vous devez être connecté pour accéder à cette page.";
        // header('Location: /auth/index.php');
        redirect(BASE_URL . '/index.php');  

        exit();
    }
}

/**
 * Déconnecte l'utilisateur
 */
function logout() {
    session_unset();
    session_destroy();
    // header('Location: /index.php');
    redirect(BASE_URL . '/index.php');

    exit();
}
