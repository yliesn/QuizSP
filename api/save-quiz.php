<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

try {
    // Récupérer les données JSON envoyées
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['content'])) {
        throw new Exception('Données manquantes');
    }
    
    $content = $input['content'];

    // Décoder le contenu JSON pour l'insertion en BDD
    $quizData = json_decode($content, true);
    if (!$quizData || !isset($quizData['title']) || !isset($quizData['questions'])) {
        throw new Exception('Format de quiz invalide');
    }

    $pdo = getDbConnection();
    if (!$pdo) {
        throw new Exception('Connexion à la base de données impossible');
    }
    $pdo->beginTransaction();
    try {
        // Insertion du quiz
        $stmt = $pdo->prepare('INSERT INTO quizz (titre, description) VALUES (?, ?)');
        $stmt->execute([
            $quizData['title'],
            isset($quizData['description']) ? $quizData['description'] : null
        ]);
        $quizz_id = $pdo->lastInsertId();

        // Insertion des questions et réponses
        foreach ($quizData['questions'] as $question) {
            $stmtQ = $pdo->prepare('INSERT INTO question (quizz_id, texte_question) VALUES (?, ?)');
            $stmtQ->execute([$quizz_id, $question['question']]);
            $question_id = $pdo->lastInsertId();

            if ($question['type'] === 'choix_multiple') {
                foreach ($question['options'] as $option) {
                    $is_correct = in_array($option, $question['reponse_correcte']) ? 1 : 0;
                    $stmtR = $pdo->prepare('INSERT INTO reponse (question_id, texte_reponse, est_correcte) VALUES (?, ?, ?)');
                    $stmtR->execute([$question_id, $option, $is_correct]);
                }
            } elseif ($question['type'] === 'texte') {
                $stmtR = $pdo->prepare('INSERT INTO reponse (question_id, texte_reponse, est_correcte) VALUES (?, ?, 1)');
                $stmtR->execute([$question_id, $question['reponse_correcte']]);
            }
        }
        $pdo->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Quiz enregistré en base de données avec succès',
            'quizz_id' => $quizz_id
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>