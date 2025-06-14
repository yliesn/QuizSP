<?php
// Contrôleur pour activer/désactiver un utilisateur
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/auth.php';
require_login();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'ADMIN') {
    $_SESSION['error_message'] = "Accès refusé.";
    redirect(BASE_URL . '/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $actif = isset($_POST['actif']) ? intval($_POST['actif']) : 0;
    if ($user_id > 0) {
        try {
            $db = getDbConnection();
            $stmt = $db->prepare('UPDATE user SET actif = ? WHERE id = ?');
            $stmt->execute([$actif, $user_id]);
            $_SESSION['success_message'] = $actif ? "Utilisateur activé." : "Utilisateur désactivé.";
        } catch (Exception $e) {
            error_log('Erreur activation utilisateur: ' . $e->getMessage());
            $_SESSION['error_message'] = "Erreur lors de la modification de l'utilisateur.";
        }
    }
}
redirect(BASE_URL . '/views/users/list.php');
