<?php
// Contrôleur de modification d'utilisateur (admin)
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/auth.php';
require_login();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'ADMIN') {
    $_SESSION['error_message'] = "Accès refusé.";
    redirect(BASE_URL . '/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $login = trim($_POST['login'] ?? '');
    $role = $_POST['role'] ?? 'USER';
    $roles_valides = ['USER', 'MODERATEUR', 'ADMIN', 'JSP1', 'JSP2', 'JSP3', 'JSP4'];
    if (!in_array($role, $roles_valides, true)) {
        $_SESSION['error_message'] = "Rôle utilisateur invalide.";
        redirect(BASE_URL . '/views/users/create.php');
    }
    $actif = isset($_POST['actif']) ? intval($_POST['actif']) : 1;
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Vérification CSRF
    if (empty($csrf_token) || $csrf_token !== ($_SESSION['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = "Token CSRF invalide.";
        redirect(BASE_URL . '/views/users/edit.php?id=' . $user_id);
    }

    // Validation
    if ($user_id <= 0 || !$nom || !$prenom || !$login || !$role) {
        $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
        redirect(BASE_URL . '/views/users/edit.php?id=' . $user_id);
    }

    try {
        $db = getDbConnection();
        // Vérifier l'unicité du login (hors utilisateur courant)
        $stmt = $db->prepare('SELECT id FROM user WHERE login = ? AND id != ?');
        $stmt->execute([$login, $user_id]);
        if ($stmt->fetch()) {
            $_SESSION['error_message'] = "Ce login existe déjà.";
            redirect(BASE_URL . '/views/users/edit.php?id=' . $user_id);
        }
        // Mise à jour
        $stmt = $db->prepare('UPDATE user SET nom = ?, prenom = ?, login = ?, role = ?, actif = ? WHERE id = ?');
        $stmt->execute([$nom, $prenom, $login, $role, $actif, $user_id]);
        if ($new_password!== ''){
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $db->prepare('UPDATE user SET mot_de_passe = ? WHERE id = ?');
            $update->execute([$hash, $user_id]);
        }

        $_SESSION['success_message'] = "Utilisateur modifié avec succès.";

        redirect(BASE_URL . '/views/users/edit.php?id=' . $user_id);
    } catch (Exception $e) {
        error_log('Erreur modification utilisateur: ' . $e->getMessage());
        $_SESSION['error_message'] = "Erreur lors de la modification de l'utilisateur.";
        redirect(BASE_URL . '/views/users/edit.php?id=' . $user_id);
    }
} else {
    redirect(BASE_URL . '/views/users/list.php');
}
