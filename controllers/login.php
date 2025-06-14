<?php
/**
 * Contrôleur de connexion
 * Vérifie les identifiants et gère la session utilisateur
 */
require_once __DIR__ . '/../config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
        $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = "Erreur de validation du formulaire. Veuillez réessayer.";
        redirect('../index.php');
    }

    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
        redirect('../index.php');
    }

    try {
        $db = getDbConnection();
        if (!$db) {
            throw new Exception("Impossible de se connecter à la base de données");
        }
        $stmt = $db->prepare("SELECT id, nom, prenom, login, mot_de_passe, role, actif FROM user WHERE login = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && $user['actif'] && password_verify($password, $user['mot_de_passe'])) {
            session_regenerate_id(true);
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_login'] = $user['login'];
            $_SESSION['login_time'] = time();
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            $updateStmt = $db->prepare("UPDATE user SET date_derniere_connexion = CURRENT_DATE() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            redirect(BASE_URL . '/dashboard.php');
        } elseif ($user && !$user['actif']) {
            $_SESSION['error_message'] = "Votre compte est désactivé. Veuillez contacter l'administrateur.";
            redirect(BASE_URL . '/index.php');
        } else {
            $_SESSION['error_message'] = "Identifiant ou mot de passe incorrect";
            redirect(BASE_URL . '/index.php');
        }
    } catch (Exception $e) {
        error_log('Erreur de connexion: ' . $e->getMessage());
        $_SESSION['error_message'] = "Erreur de connexion au serveur. Veuillez réessayer plus tard.";
        redirect(BASE_URL . '/index.php');
    }
} else {
    redirect('../index.php');
}
