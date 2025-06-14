<?php
// Contrôleur de changement de mot de passe utilisateur
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $old_password = isset($_POST['old_password']) ? $_POST['old_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if (!$user_id) {
        $_SESSION['error_message'] = "Utilisateur non identifié.";
        redirect(BASE_URL . '/views/users/profile.php');
    }
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
        redirect(BASE_URL . '/views/users/profile.php');
    }
    if ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = "Les mots de passe ne correspondent pas.";
        redirect(BASE_URL . '/views/users/profile.php');
    }
    if (strlen($new_password) < 8) {
        $_SESSION['error_message'] = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
        redirect(BASE_URL . '/views/users/profile.php');
    }
    try {
        $db = getDbConnection();
        $stmt = $db->prepare('SELECT mot_de_passe FROM user WHERE id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($old_password, $user['mot_de_passe'])) {
            $_SESSION['error_message'] = "Ancien mot de passe incorrect.";
            redirect(BASE_URL . '/views/users/profile.php');
        }
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $db->prepare('UPDATE user SET mot_de_passe = ? WHERE id = ?');
        $update->execute([$hash, $user_id]);
        $_SESSION['success_message'] = "Mot de passe modifié avec succès.";
        redirect(BASE_URL . '/views/users/profile.php');
    } catch (Exception $e) {
        error_log('Erreur changement mot de passe: ' . $e->getMessage());
        $_SESSION['error_message'] = "Erreur lors du changement de mot de passe.";
        redirect(BASE_URL . '/views/users/profile.php');
    }
} else {
    redirect(BASE_URL . '/views/users/profile.php');
}
