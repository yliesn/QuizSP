<?php
// Affichage d'un quiz depuis la BDD et enregistrement du résultat
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../config/database.php';
require_login();

// Récupérer l'id du quiz à jouer
$quizz_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($quizz_id <= 0) {
    die('<div style="color:red;text-align:center;margin-top:2em">Quiz non trouvé.</div>');
}

$pdo = getDbConnection();
if (!$pdo) {
    die('<div style="color:red;text-align:center;margin-top:2em">Erreur de connexion à la base de données.</div>');
}

// Charger le quiz, questions et réponses
$stmt = $pdo->prepare('SELECT titre, description FROM quizz WHERE id = ?');
$stmt->execute([$quizz_id]);
$quiz = $stmt->fetch();
if (!$quiz) {
    die('<div style="color:red;text-align:center;margin-top:2em">Quiz introuvable.</div>');
}

$stmt = $pdo->prepare('SELECT * FROM question WHERE quizz_id = ? ORDER BY id');
$stmt->execute([$quizz_id]);
$questions = $stmt->fetchAll();

$questions_full = [];
foreach ($questions as $q) {
    $stmtR = $pdo->prepare('SELECT * FROM reponse WHERE question_id = ?');
    $stmtR->execute([$q['id']]);
    $reponses = $stmtR->fetchAll();
    $q['reponses'] = $reponses;
    $questions_full[] = $q;
}

$user_id = $_SESSION['user_id'];

// Gestion de la soumission du quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = 0;
    $total = count($questions_full);
    foreach ($questions_full as $idx => $q) {
        $user_answer = $_POST['q_' . $q['id']] ?? null;
        $type = count(array_filter($q['reponses'], fn($r) => $r['est_correcte'])) > 1 ? 'choix_multiple' : (count($q['reponses']) > 1 ? 'choix_unique' : 'texte');
        if ($type === 'choix_multiple') {
            $corrects = array_map(fn($r) => $r['texte_reponse'], array_filter($q['reponses'], fn($r) => $r['est_correcte']));
            $user_answer = isset($_POST['q_' . $q['id']]) ? (array)$_POST['q_' . $q['id']] : [];
            if (count($user_answer) === count($corrects) && !array_diff($user_answer, $corrects) && !array_diff($corrects, $user_answer)) {
                $score++;
            }
        } elseif ($type === 'texte') {
            $correct = $q['reponses'][0]['texte_reponse'];
            if (trim(mb_strtolower($user_answer)) === trim(mb_strtolower($correct))) {
                $score++;
            }
        } else { // choix unique
            $correct = null;
            foreach ($q['reponses'] as $r) {
                if ($r['est_correcte']) $correct = $r['texte_reponse'];
            }
            if ($user_answer === $correct) {
                $score++;
            }
        }
    }
    // Stocker le résultat
    $stmt = $pdo->prepare('INSERT INTO resultat_quiz (user_id, quizz_id, score) VALUES (?, ?, ?)');
    $stmt->execute([$user_id, $quizz_id, $score]);
    echo '<div class="result-container p-6 rounded-lg shadow text-center mt-8"><h2 class="font-oswald text-2xl mb-4">Résultat du Quiz</h2><p>Score : <span id="score">' . $score . '</span> / <span id="total-questions">' . $total . '</span></p><a href="list.php" class="inline-block mt-6 px-6 py-2 bg-primary text-white rounded hover:bg-secondary transition">Retour à la liste des quiz</a></div>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['titre']); ?> - Quiz</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="quiz-container mx-auto mt-10">
    <h1 id="quiz-title" class="text-center font-oswald mb-2"><?php echo htmlspecialchars($quiz['titre']); ?></h1>
    <p id="quiz-description" class="text-center mb-6"><?php echo htmlspecialchars($quiz['description']); ?></p>
    <form method="post">
        <?php foreach ($questions_full as $idx => $q): ?>
        <div class="question-container p-6 mb-6 animate">
            <div id="question-text" class="mb-4 font-bold"><?php echo ($idx+1) . '. ' . htmlspecialchars($q['texte_question']); ?></div>
            <div id="options-container">
                <?php
                $correct_count = count(array_filter($q['reponses'], fn($r) => $r['est_correcte']));
                if ($correct_count > 1) {
                    // Choix multiple
                    foreach ($q['reponses'] as $r) {
                        echo '<label class="block mb-2"><input type="checkbox" name="q_' . $q['id'] . '[]" value="' . htmlspecialchars($r['texte_reponse']) . '" class="option-button mr-2"> ' . htmlspecialchars($r['texte_reponse']) . '</label>';
                    }
                } elseif (count($q['reponses']) > 1) {
                    // Choix unique
                    foreach ($q['reponses'] as $r) {
                        echo '<label class="block mb-2"><input type="radio" name="q_' . $q['id'] . '" value="' . htmlspecialchars($r['texte_reponse']) . '" class="option-button mr-2"> ' . htmlspecialchars($r['texte_reponse']) . '</label>';
                    }
                } else {
                    // Texte libre
                    echo '<input type="text" name="q_' . $q['id'] . '" class="text-input" placeholder="Votre réponse...">';
                }
                ?>
            </div>
        </div>
        <?php endforeach; ?>
        <div class="flex justify-center">
            <button type="submit" class="btn-lg btn-primary">Valider mes réponses</button>
        </div>
    </form>
</div>
</body>
</html>
