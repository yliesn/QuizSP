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

// Générer le quiz en JS (affichage dynamique)
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['titre']); ?> - Quiz</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--bs-dark-blue);
        }
        .quiz-container {
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 8px 32px rgba(44,62,80,0.15);
            padding: 2.5rem 2rem 2rem 2rem;
        }
        .question-container {
            background: var(--bs-light-gray);
            border-radius: 0.75rem;
            border: 1.5px solid #bdc3c7;
            box-shadow: 0 2px 8px rgba(44,62,80,0.07);
        }
        .option-button {
            margin-bottom: 1rem;
            font-size: 1.1em;
            padding: 1rem 1.2rem;
            border-radius: 0.5rem;
            border-width: 2px;
            transition: all 0.2s;
        }
        .option-button.selected {
            font-weight: bold;
            box-shadow: 0 0 0 2px var(--bs-primary-blue);
        }
        .option-button.correct {
            font-weight: bold;
            box-shadow: 0 0 0 2px var(--bs-success-green);
        }
        .option-button.incorrect {
            font-weight: bold;
            box-shadow: 0 0 0 2px var(--bs-danger-red);
        }
        .btn-lg {
            min-width: 180px;
        }
        @media (max-width: 600px) {
            .quiz-container { padding: 1rem; }
        }
    </style>
</head>
<body>
<div class="quiz-container">
    <h1 id="quiz-title" class="text-center font-oswald mb-2" style="color:var(--bs-firefighter-red)"><?php echo htmlspecialchars($quiz['titre']); ?></h1>
    <p id="quiz-description" class="text-center mb-6" style="color:var(--bs-text-dark)"><?php echo htmlspecialchars($quiz['description']); ?></p>
    <div id="question-container" class="question-container p-6 mb-6 animate">
        <div id="question-text" class="mb-4 font-bold text-lg"></div>
        <div id="options-container"></div>
    </div>
    <div class="flex justify-center gap-4">
        <button id="next-button" class="btn-lg btn-primary">Suivant</button>
        <button id="submit-button" class="btn-lg btn-success d-none">Voir les résultats</button>
    </div>
    <div id="result-container" class="result-container p-6 rounded-lg shadow text-center mt-8 d-none">
        <h2 class="font-oswald text-2xl mb-4">Résultat du Quiz</h2>
        <p>Score : <span id="score"></span> / <span id="total-questions"></span></p>
        <button id="restart-button" class="btn-lg btn-outline-secondary mt-4">Recommencer</button>
        <a href="list.php" class="inline-block mt-6 px-6 py-2 bg-primary text-white rounded hover:bg-secondary transition">Retour à la liste des quiz</a>
    </div>
</div>
<script>
// Générer les données du quiz depuis PHP vers JS
const questions = <?php echo json_encode(array_map(function($q) {
    $type = count(array_filter($q['reponses'], fn($r) => $r['est_correcte'])) > 1 ? 'choix_multiple' : (count($q['reponses']) > 1 ? 'choix_unique' : 'texte');
    return [
        'id' => $q['id'],
        'question' => $q['texte_question'],
        'type' => $type,
        'options' => $type !== 'texte' ? array_map(fn($r) => $r['texte_reponse'], $q['reponses']) : [],
        'reponse_correcte' => $type === 'choix_multiple' ? array_values(array_map(fn($r) => $r['texte_reponse'], array_filter($q['reponses'], fn($r) => $r['est_correcte']))) : ($type === 'texte' ? $q['reponses'][0]['texte_reponse'] : array_values(array_map(fn($r) => $r['texte_reponse'], array_filter($q['reponses'], fn($r) => $r['est_correcte'])))),
    ];
}, $questions_full)); ?>;
const quizz_id = <?php echo (int)$quizz_id; ?>;
const user_id = <?php echo (int)$user_id; ?>;
</script>
<script>
// --- Version JS inspirée de script.js (lecture BDD) ---
let currentQuestionIndex = 0;
let score = 0;
let selectedAnswers = [];

const questionContainer = document.getElementById('question-container');
const questionText = document.getElementById('question-text');
const optionsContainer = document.getElementById('options-container');
const nextButton = document.getElementById('next-button');
const submitButton = document.getElementById('submit-button');
const resultContainer = document.getElementById('result-container');
const scoreSpan = document.getElementById('score');
const totalQuestionsSpan = document.getElementById('total-questions');
const restartButton = document.getElementById('restart-button');

function startQuiz() {
    currentQuestionIndex = 0;
    score = 0;
    resultContainer.classList.add('d-none');
    questionContainer.classList.remove('d-none');
    submitButton.classList.add('d-none');
    nextButton.classList.remove('d-none');
    displayQuestion();
}

function displayQuestion() {
    if (currentQuestionIndex < questions.length) {
        const currentQuestion = questions[currentQuestionIndex];
        // Animation
        questionContainer.classList.remove('animate');
        void questionContainer.offsetWidth;
        questionContainer.classList.add('animate');
        questionText.textContent = currentQuestion.question;
        optionsContainer.innerHTML = '';
        selectedAnswers = [];
        if (currentQuestion.type === 'choix_multiple') {
            currentQuestion.options.forEach(option => {
                const button = document.createElement('button');
                button.className = 'option-button btn btn-outline-secondary text-start w-100';
                button.textContent = option;
                button.dataset.answer = option;
                button.onclick = () => toggleOption(button, option);
                optionsContainer.appendChild(button);
            });
        } else if (currentQuestion.type === 'choix_unique') {
            currentQuestion.options.forEach(option => {
                const button = document.createElement('button');
                button.className = 'option-button btn btn-outline-secondary text-start w-100';
                button.textContent = option;
                button.dataset.answer = option;
                button.onclick = () => selectUniqueOption(button, option);
                optionsContainer.appendChild(button);
            });
        } else if (currentQuestion.type === 'texte') {
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'text-input form-control form-control-lg';
            input.placeholder = 'Saisissez votre réponse ici...';
            input.oninput = (e) => selectedAnswers = [e.target.value.trim()];
            optionsContainer.appendChild(input);
        }
        // Boutons
        if (currentQuestionIndex === questions.length - 1) {
            nextButton.classList.add('d-none');
            submitButton.classList.remove('d-none');
        } else {
            nextButton.classList.remove('d-none');
            submitButton.classList.add('d-none');
        }
    }
}

function toggleOption(clickedButton, answer) {
    if (clickedButton.classList.contains('selected')) {
        clickedButton.classList.remove('selected', 'btn-primary');
        clickedButton.classList.add('btn-outline-secondary');
        selectedAnswers = selectedAnswers.filter(item => item !== answer);
    } else {
        clickedButton.classList.add('selected', 'btn-primary');
        clickedButton.classList.remove('btn-outline-secondary');
        selectedAnswers.push(answer);
    }
}
function selectUniqueOption(clickedButton, answer) {
    // Un seul choix possible
    selectedAnswers = [answer];
    document.querySelectorAll('.option-button').forEach(btn => {
        btn.classList.remove('selected', 'btn-primary');
        btn.classList.add('btn-outline-secondary');
    });
    clickedButton.classList.add('selected', 'btn-primary');
    clickedButton.classList.remove('btn-outline-secondary');
}

function checkAnswer() {
    const currentQuestion = questions[currentQuestionIndex];
    let isCorrect = false;
    if (currentQuestion.type === 'choix_multiple') {
        // Comparaison stricte des ensembles (ordre et doublons ignorés)
        const correctSet = new Set(currentQuestion.reponse_correcte.map(ans => String(ans).trim()));
        const selectedSet = new Set(selectedAnswers.map(ans => String(ans).trim()));
        isCorrect = (correctSet.size === selectedSet.size) &&
            Array.from(correctSet).every(val => selectedSet.has(val));
        const buttons = document.querySelectorAll('.option-button');
        buttons.forEach(button => {
            button.disabled = true;
            const optionValue = String(button.dataset.answer).trim();
            if (correctSet.has(optionValue)) {
                button.classList.remove('btn-outline-secondary', 'btn-primary', 'incorrect');
                button.classList.add('correct', 'btn-success');
            } else if (selectedSet.has(optionValue) && !correctSet.has(optionValue)) {
                button.classList.remove('btn-outline-secondary', 'btn-primary', 'correct');
                button.classList.add('incorrect', 'btn-danger');
            } else {
                button.classList.remove('btn-primary', 'correct', 'incorrect');
                button.classList.add('btn-outline-secondary');
            }
        });
    } else if (currentQuestion.type === 'choix_unique') {
        const correct = String(currentQuestion.reponse_correcte[0]).trim();
        const selected = selectedAnswers.length === 1 ? String(selectedAnswers[0]).trim() : '';
        isCorrect = selected === correct;
        const buttons = document.querySelectorAll('.option-button');
        buttons.forEach(button => {
            button.disabled = true;
            const optionValue = String(button.dataset.answer).trim();
            if (optionValue === correct) {
                button.classList.remove('btn-outline-secondary', 'btn-primary', 'incorrect');
                button.classList.add('correct', 'btn-success');
            } else if (selected === optionValue && optionValue !== correct) {
                button.classList.remove('btn-outline-secondary', 'btn-primary', 'correct');
                button.classList.add('incorrect', 'btn-danger');
            } else {
                button.classList.remove('btn-primary', 'correct', 'incorrect');
                button.classList.add('btn-outline-secondary');
            }
        });
    } else if (currentQuestion.type === 'texte') {
        const userAnswer = (selectedAnswers[0] || '').trim().toLowerCase();
        const correct = (currentQuestion.reponse_correcte || '').trim().toLowerCase();
        isCorrect = userAnswer === correct;
        const inputElement = optionsContainer.querySelector('.text-input');
        if (inputElement) {
            inputElement.disabled = true;
            if (isCorrect) {
                inputElement.classList.remove('border-secondary', 'border-danger');
                inputElement.classList.add('border-success', 'text-success');
            } else {
                inputElement.classList.remove('border-secondary', 'border-success');
                inputElement.classList.add('border-danger', 'text-danger');
                inputElement.value = `Votre réponse : ${userAnswer} | Correct : ${currentQuestion.reponse_correcte}`;
                inputElement.style.fontSize = '0.9em';
            }
        }
    }
    // Incrémenter le score uniquement si la réponse est correcte
    if (isCorrect) {
        score++;
    }
    const allOptions = optionsContainer.querySelectorAll('.option-button, .text-input');
    allOptions.forEach(element => {
        element.disabled = true;
    });
}

function showResults() {
    questionContainer.classList.add('d-none');
    resultContainer.classList.remove('d-none');
    scoreSpan.textContent = score;
    totalQuestionsSpan.textContent = questions.length;
    // Enregistrer le score en BDD via AJAX
    fetch('save_result.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id, quizz_id, score })
    });
}

nextButton.addEventListener('click', () => {
    const currentQuestion = questions[currentQuestionIndex];
    let hasResponded = false;
    if (currentQuestion.type === 'choix_multiple' || currentQuestion.type === 'choix_unique') {
        hasResponded = selectedAnswers.length > 0;
    } else if (currentQuestion.type === 'texte') {
        hasResponded = selectedAnswers.length > 0 && selectedAnswers[0] !== '';
    }
    if (hasResponded) {
        checkAnswer();
        setTimeout(() => {
            currentQuestionIndex++;
            if (currentQuestionIndex < questions.length) {
                displayQuestion();
            } else {
                showResults();
            }
        }, 700);
    } else {
        alert("Veuillez sélectionner ou saisir votre réponse avant de passer à la question suivante.");
    }
});

submitButton.addEventListener('click', () => {
    const currentQuestion = questions[currentQuestionIndex];
    let hasResponded = false;
    if (currentQuestion.type === 'choix_multiple' || currentQuestion.type === 'choix_unique') {
        hasResponded = selectedAnswers.length > 0;
    } else if (currentQuestion.type === 'texte') {
        hasResponded = selectedAnswers.length > 0 && selectedAnswers[0] !== '';
    }
    if (hasResponded) {
        checkAnswer();
        setTimeout(() => {
            showResults();
        }, 700);
    } else {
        alert("Veuillez sélectionner ou saisir votre réponse avant de voir les résultats.");
    }
});

restartButton.addEventListener('click', () => {
    startQuiz();
});

// Lancer le quiz au chargement
startQuiz();
</script>
</body>
</html>
<?php
// Fichier save_result.php à créer dans code_test pour enregistrer le score en AJAX
// Il doit vérifier l'utilisateur connecté et insérer score dans resultat_quiz
// ...fin...
