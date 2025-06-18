<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Enregistre le score d'un utilisateur pour un quiz (appel AJAX)
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
header('Content-Type: application/json');

// Vérifier que l'utilisateur est connecté
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

// Lire le JSON brut
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['user_id'], $input['quizz_id'], $input['score'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit;
}

$user_id = intval($input['user_id']);
$quizz_id = intval($input['quizz_id']);
$score = intval($input['score']);

// Vérification de cohérence (l'utilisateur connecté doit correspondre à user_id)
if ($_SESSION['user_id'] !== $user_id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Utilisateur non autorisé']);
    exit;
}

$pdo = getDbConnection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur BDD']);
    exit;
}

// Insérer le résultat
$stmt = $pdo->prepare('INSERT INTO resultat_quiz (user_id, quizz_id, score, date_passage) VALUES (?, ?, ?, NOW())');
$ok = $stmt->execute([$user_id, $quizz_id, $score]);

if ($ok) {
    echo json_encode(['success' => true]);
} else {
    error_log('Erreur SQL : ' . implode(' | ', $stmt->errorInfo()));
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement']);
}
