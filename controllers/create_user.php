<?php
// Contrôleur de création d'utilisateur (admin)
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

// Vérifier le rôle admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'ADMIN') {
    $_SESSION['error_message'] = "Accès refusé.";
    redirect(BASE_URL . '/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $login = trim($_POST['login'] ?? '');
    $role = $_POST['role'] ?? 'USER';
    $roles_valides = ['USER', 'MODERATEUR', 'ADMIN', 'JSP1', 'JSP2', 'JSP3', 'JSP4'];
    if (!in_array($role, $roles_valides, true)) {
        $_SESSION['error_message'] = "Rôle utilisateur invalide.";
        redirect(BASE_URL . '/views/users/create.php');
    }
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Vérification CSRF
    if (empty($csrf_token) || $csrf_token !== ($_SESSION['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = "Token CSRF invalide.";
        redirect(BASE_URL . '/views/users/create.php');
    }

    // Validation
    if (!$nom || !$prenom || !$login || !$password || !$confirm_password) {
        $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
        redirect(BASE_URL . '/views/users/create.php');
    }
    if ($password !== $confirm_password) {
        $_SESSION['error_message'] = "Les mots de passe ne correspondent pas.";
        redirect(BASE_URL . '/views/users/create.php');
    }
    if (strlen($password) < 4) {
        $_SESSION['error_message'] = "Le mot de passe doit contenir au moins 8 caractères.";
        redirect(BASE_URL . '/views/users/create.php');
    }

    try {
        $db = getDbConnection();
        // Vérifier l'unicité du login
        $stmt = $db->prepare('SELECT id FROM user WHERE login = ?');
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $_SESSION['error_message'] = "Ce login existe déjà.";
            redirect(BASE_URL . '/views/users/create.php');
        }
        // Insertion
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare('INSERT INTO user (nom, prenom, login, mot_de_passe, role) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$nom, $prenom, $login, $hash, $role]);
        $_SESSION['success_message'] = "Utilisateur créé avec succès.";
        redirect(BASE_URL . '/views/users/create.php');
    } catch (Exception $e) {
        error_log('Erreur création utilisateur: ' . $e->getMessage());
        $_SESSION['error_message'] = "Erreur lors de la création de l'utilisateur.";
        redirect(BASE_URL . '/views/users/create.php');
    }
} else {
    redirect(BASE_URL . '/views/users/create.php');
}
