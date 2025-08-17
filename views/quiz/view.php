<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../auth/auth.php';
require_login();

$quizz_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($quizz_id <= 0) {
    $_SESSION['error_message'] = "Quiz non trouvé.";
    redirect(BASE_URL . '/views/quiz/list.php');
}

$pdo = getDbConnection();
if (!$pdo) {
    $_SESSION['error_message'] = "Erreur de connexion à la base de données.";
    redirect(BASE_URL . '/views/quiz/list.php');
}

// Récupérer les informations du quiz
$stmt = $pdo->prepare('SELECT titre, description FROM quizz WHERE id = ?');
$stmt->execute([$quizz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    $_SESSION['error_message'] = "Quiz introuvable.";
    redirect(BASE_URL . '/views/quiz/list.php');
}

// Récupérer les questions avec leur type et leurs réponses
$stmt = $pdo->prepare('
    SELECT q.id, q.texte_question, q.type_question,
           GROUP_CONCAT(r.texte_reponse ORDER BY r.id SEPARATOR "|||") as reponses,
           GROUP_CONCAT(r.est_correcte ORDER BY r.id SEPARATOR "|||") as corrections
    FROM question q
    LEFT JOIN reponse r ON q.id = r.question_id
    WHERE q.quizz_id = ?
    GROUP BY q.id, q.texte_question, q.type_question
    ORDER BY q.id
');
$stmt->execute([$quizz_id]);
$questions_raw = $stmt->fetchAll();

if (empty($questions_raw)) {
    $_SESSION['error_message'] = "Ce quiz ne contient aucune question.";
    redirect(BASE_URL . '/views/quiz/list.php');
}

// Traitement des questions
$questions = [];
foreach ($questions_raw as $q) {
    $reponses = $q['reponses'] ? explode('|||', $q['reponses']) : [];
    $corrections = $q['corrections'] ? explode('|||', $q['corrections']) : [];
    
    $options = [];
    $bonnes_reponses = [];
    
    for ($i = 0; $i < count($reponses); $i++) {
        $options[] = $reponses[$i];
        if (isset($corrections[$i]) && $corrections[$i] == '1') {
            $bonnes_reponses[] = $reponses[$i];
        }
    }
    
    $questions[] = [
        'id' => $q['id'],
        'question' => $q['texte_question'],
        'type' => $q['type_question'], // Maintenant on a le type explicite !
        'options' => $options,
        'bonnes_reponses' => $bonnes_reponses
    ];
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? '';

// Vérifier si l'utilisateur a déjà passé ce quiz (sauf admin/modérateur)
$deja_passe = false;
$resultat_existant = null;

if (!in_array($user_role, ['ADMIN', 'MODERATEUR'])) {
    $stmt = $pdo->prepare('
        SELECT score, date_passage,
               (SELECT COUNT(*) FROM question WHERE quizz_id = ?) as total_questions
        FROM resultat_quiz 
        WHERE user_id = ? AND quizz_id = ? 
        ORDER BY date_passage DESC 
        LIMIT 1
    ');
    $stmt->execute([$quizz_id, $user_id, $quizz_id]);
    $resultat_existant = $stmt->fetch();
    
    if ($resultat_existant) {
        $deja_passe = true;
    }
}

// define('ROOT_PATH', realpath(__DIR__ . '/../../'));
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', realpath(__DIR__ . '/../../'));
}
$page_title = $quiz['titre'];
include ROOT_PATH . '/includes/header.php';
?>

<?php if ($deja_passe): ?>
<!-- Affichage du résultat déjà obtenu -->
<div class="min-h-screen bg-custom flex items-center justify-center py-10">
    <div class="w-full max-w-2xl bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-secondary mb-4"><?php echo htmlspecialchars($quiz['titre']); ?></h1>
            <div class="bg-accent text-white p-4 rounded-lg mb-4">
                <h2 class="text-xl font-bold mb-2">Quiz déjà terminé !</h2>
                <div class="text-2xl font-bold">
                    Score : <?php echo $resultat_existant['score']; ?> / <?php echo $resultat_existant['total_questions']; ?>
                </div>
                <div class="text-sm mt-2">
                    Passé le <?php echo date('d/m/Y à H:i', strtotime($resultat_existant['date_passage'])); ?>
                </div>
            </div>
        </div>
        
        <div class="mb-8">
            <details class="bg-light-gray p-4 rounded">
                <summary class="cursor-pointer font-bold text-lg text-secondary mb-4">
                    Voir la correction complète
                </summary>
                <div class="space-y-4 mt-4">
                    <?php foreach ($questions as $index => $question): ?>
                    <div class="bg-white p-4 rounded border">
                        <h3 class="font-bold text-custom mb-2">
                            Question <?php echo $index + 1; ?> : <?php echo htmlspecialchars($question['question']); ?>
                        </h3>
                        <div class="text-accent font-semibold">
                            <?php if ($question['type'] === 'texte'): ?>
                                Réponse : <?php echo htmlspecialchars($question['bonnes_reponses'][0] ?? ''); ?>
                            <?php else: ?>
                                Bonne(s) réponse(s) : <?php echo implode(', ', array_map('htmlspecialchars', $question['bonnes_reponses'])); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </details>
        </div>
        
        <div class="text-center">
            <a href="<?php echo BASE_URL; ?>/views/quiz/list.php" 
               class="inline-block px-6 py-3 bg-primary text-white rounded hover:bg-secondary transition">
                Retour à la liste des quiz
            </a>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Interface du quiz -->
<div class="min-h-screen bg-custom flex items-center justify-center py-10">
    <div class="quiz-container w-full max-w-3xl bg-white rounded-lg shadow-lg p-8">
        <!-- En-tête du quiz -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-secondary mb-2"><?php echo htmlspecialchars($quiz['titre']); ?></h1>
            <p class="text-custom text-lg"><?php echo htmlspecialchars($quiz['description']); ?></p>
            <div class="mt-4 text-sm text-muted-custom">
                <span id="question-counter">Question 1 sur <?php echo count($questions); ?></span>
            </div>
        </div>

        <!-- Barre de progression -->
        <div class="mb-6">
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div id="progress-bar" class="bg-primary h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>

        <!-- Conteneur de la question -->
        <div id="question-container" class="question-form mb-8">
            <div id="question-text" class="text-xl font-semibold text-custom mb-6"></div>
            <div id="options-container" class="space-y-3"></div>
            <div id="feedback-container" class="mt-4 hidden"></div>
        </div>

        <!-- Boutons d'action -->
        <div class="flex justify-between items-center">
            <div class="space-x-4">
                <button id="prev-btn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 transition hidden">
                    Précédent
                </button>
                <button id="next-btn" class="px-6 py-2 bg-primary text-white rounded hover:bg-secondary transition">
                    Suivant
                </button>
                <button id="finish-btn" class="px-6 py-2 bg-accent text-white rounded hover:bg-primary transition hidden">
                    Terminer le quiz
                </button>
            </div>
        </div>

        <!-- Résultat final -->
        <div id="result-container" class="hidden text-center mt-8">
            <div class="bg-light-gray p-8 rounded-lg">
                <h2 class="text-2xl font-bold text-secondary mb-4">Quiz terminé !</h2>
                <div class="text-4xl font-bold text-accent mb-4">
                    <span id="final-score"></span> / <?php echo count($questions); ?>
                </div>
                <div id="result-message" class="text-lg text-custom mb-6"></div>
                <a href="<?php echo BASE_URL; ?>/views/quiz/list.php" 
                   class="inline-block px-6 py-3 bg-primary text-white rounded hover:bg-secondary transition">
                    Retour à la liste des quiz
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Données du quiz
const quizData = {
    id: <?php echo $quizz_id; ?>,
    titre: <?php echo json_encode($quiz['titre']); ?>,
    questions: <?php echo json_encode($questions); ?>
};

const userId = <?php echo $user_id; ?>;

// Variables de l'état du quiz
let currentQuestionIndex = 0;
let userAnswers = [];
let score = 0;
let quizCompleted = false;

// Éléments DOM
const questionContainer = document.getElementById('question-container');
const questionText = document.getElementById('question-text');
const optionsContainer = document.getElementById('options-container');
const feedbackContainer = document.getElementById('feedback-container');
const questionCounter = document.getElementById('question-counter');
const progressBar = document.getElementById('progress-bar');
// const currentScoreSpan = document.getElementById('current-score');
const prevBtn = document.getElementById('prev-btn');
const nextBtn = document.getElementById('next-btn');
const finishBtn = document.getElementById('finish-btn');
const resultContainer = document.getElementById('result-container');
const finalScoreSpan = document.getElementById('final-score');
const resultMessage = document.getElementById('result-message');

// Initialisation du quiz
function initQuiz() {
    userAnswers = new Array(quizData.questions.length).fill(null);
    displayQuestion();
}

// Affichage d'une question
function displayQuestion() {
    const question = quizData.questions[currentQuestionIndex];
    
    // Mise à jour des informations
    questionText.textContent = question.question;
    questionCounter.textContent = `Question ${currentQuestionIndex + 1} sur ${quizData.questions.length}`;
    progressBar.style.width = `${((currentQuestionIndex + 1) / quizData.questions.length) * 100}%`;
    
    // Effacer le contenu précédent
    optionsContainer.innerHTML = '';
    feedbackContainer.classList.add('hidden');
    
    // Créer les options selon le type de question
    if (question.type === 'texte') {
        createTextInput(question);
    } else if (question.type === 'choix_unique') {
        createSingleChoice(question);
    } else if (question.type === 'choix_multiple') {
        createMultipleChoice(question);
    }
    
    // Restaurer la réponse précédente si elle existe
    if (userAnswers[currentQuestionIndex] !== null) {
        restorePreviousAnswer();
    }
    
    // Mise à jour des boutons
    updateButtons();
}

// Création d'un champ texte
function createTextInput(question) {
    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'w-full px-4 py-3 border rounded-lg text-lg focus:outline-none focus:ring-2 focus:ring-primary';
    input.placeholder = 'Saisissez votre réponse...';
    input.id = 'text-answer';
    
    input.addEventListener('input', function() {
        userAnswers[currentQuestionIndex] = this.value.trim();
    });
    
    optionsContainer.appendChild(input);
}

// Création de choix unique
function createSingleChoice(question) {
    question.options.forEach((option, index) => {
        const button = document.createElement('button');
        button.className = 'option-button w-full text-left px-4 py-3 border rounded-lg hover:bg-gray-50 transition';
        button.textContent = option;
        button.dataset.value = option;
        button.dataset.index = index;
        
        button.addEventListener('click', function() {
            // Désélectionner tous les autres boutons
            document.querySelectorAll('.option-button').forEach(btn => {
                btn.classList.remove('bg-primary', 'text-white');
                btn.classList.add('hover:bg-gray-50');
            });
            
            // Sélectionner ce bouton
            this.classList.add('bg-primary', 'text-white');
            this.classList.remove('hover:bg-gray-50');
            
            userAnswers[currentQuestionIndex] = option; // String pour choix unique
        });
        
        optionsContainer.appendChild(button);
    });
}

// Création de choix multiples
function createMultipleChoice(question) {
    question.options.forEach((option, index) => {
        const button = document.createElement('button');
        button.className = 'option-button w-full text-left px-4 py-3 border rounded-lg hover:bg-gray-50 transition';
        button.textContent = option;
        button.dataset.value = option;
        button.dataset.index = index;
        
        button.addEventListener('click', function() {
            if (!userAnswers[currentQuestionIndex]) {
                userAnswers[currentQuestionIndex] = [];
            }
            
            const answers = userAnswers[currentQuestionIndex];
            const optionIndex = answers.indexOf(option);
            
            if (optionIndex > -1) {
                // Désélectionner
                answers.splice(optionIndex, 1);
                this.classList.remove('bg-primary', 'text-white');
                this.classList.add('hover:bg-gray-50');
            } else {
                // Sélectionner
                answers.push(option);
                this.classList.add('bg-primary', 'text-white');
                this.classList.remove('hover:bg-gray-50');
            }
        });
        
        optionsContainer.appendChild(button);
    });
}

// Restauration de la réponse précédente
function restorePreviousAnswer() {
    const question = quizData.questions[currentQuestionIndex];
    const answer = userAnswers[currentQuestionIndex];
    
    if (question.type === 'texte') {
        const input = document.getElementById('text-answer');
        if (input && answer) {
            input.value = answer;
        }
    } else if (question.type === 'choix_unique') {
        const buttons = document.querySelectorAll('.option-button');
        if (answer) {
            buttons.forEach(btn => {
                if (btn.dataset.value === answer) {
                    btn.classList.add('bg-primary', 'text-white');
                    btn.classList.remove('hover:bg-gray-50');
                }
            });
        }
    } else if (question.type === 'choix_multiple') {
        const buttons = document.querySelectorAll('.option-button');
        if (answer && Array.isArray(answer)) {
            buttons.forEach(btn => {
                if (answer.includes(btn.dataset.value)) {
                    btn.classList.add('bg-primary', 'text-white');
                    btn.classList.remove('hover:bg-gray-50');
                }
            });
        }
    }
}

// Mise à jour des boutons
function updateButtons() {
    const isFirstQuestion = currentQuestionIndex === 0;
    const isLastQuestion = currentQuestionIndex === quizData.questions.length - 1;
    
    prevBtn.classList.toggle('hidden', isFirstQuestion);
    nextBtn.classList.toggle('hidden', isLastQuestion);
    finishBtn.classList.toggle('hidden', !isLastQuestion);
}

// Vérification si une réponse est donnée
function hasAnswer() {
    const answer = userAnswers[currentQuestionIndex];
    const question = quizData.questions[currentQuestionIndex];
    
    if (question.type === 'texte') {
        return answer && answer.length > 0;
    } else if (question.type === 'choix_unique') {
        return answer && answer.length > 0;
    } else if (question.type === 'choix_multiple') {
        return answer && Array.isArray(answer) && answer.length > 0;
    }
    
    return false;
}

// Calcul du score - MAINTENANT AVEC LE TYPE EXPLICITE !
function calculateScore() {
    score = 0;
    
    quizData.questions.forEach((question, index) => {
        const userAnswer = userAnswers[index];
        
        if (!userAnswer) return;
        
        let isCorrect = false;
        
        if (question.type === 'texte') {
            // Comparaison insensible à la casse pour les questions texte
            const userText = userAnswer.toLowerCase().trim();
            const correctText = (question.bonnes_reponses[0] || '').toLowerCase().trim();
            isCorrect = userText === correctText;
            
        } else if (question.type === 'choix_unique') {
            // Pour choix unique : comparer directement la réponse
            isCorrect = question.bonnes_reponses.includes(userAnswer);
            
        } else if (question.type === 'choix_multiple') {
            // Pour choix multiple : comparer les tableaux triés
            if (Array.isArray(userAnswer)) {
                const userSorted = [...userAnswer].sort();
                const correctSorted = [...question.bonnes_reponses].sort();
                isCorrect = JSON.stringify(userSorted) === JSON.stringify(correctSorted);
            }
        }
        
        if (isCorrect) {
            score++;
        }
    });
    
    // currentScoreSpan.textContent = score;
}

// Sauvegarde du résultat (ancien save_result.php)
async function saveResult() {
    try {
        const response = await fetch('save_result.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId,
                quizz_id: quizData.id,
                score: score
            })
        });
        
        const result = await response.json();
        
        if (!result.success) {
            console.error('Erreur lors de la sauvegarde:', result.message);
            notifications.error('Erreur', 'Impossible de sauvegarder votre résultat');
        }
    } catch (error) {
        console.error('Erreur lors de la sauvegarde:', error);
        notifications.error('Erreur', 'Impossible de sauvegarder votre résultat');
    }
}

// Affichage des résultats
function showResults() {
    calculateScore();
    
    questionContainer.style.display = 'none';
    document.querySelector('.flex.justify-between').style.display = 'none';
    resultContainer.classList.remove('hidden');
    
    finalScoreSpan.textContent = score;
    
    const percentage = (score / quizData.questions.length) * 100;
    let message = '';
    
    if (percentage >= 80) {
        message = 'Excellent ! Vous maîtrisez parfaitement le sujet.';
    } else if (percentage >= 60) {
        message = 'Bien joué ! Vous avez de bonnes connaissances.';
    } else if (percentage >= 40) {
        message = 'Pas mal, mais vous pouvez encore vous améliorer.';
    } else {
        message = 'Il serait bon de réviser ce sujet.';
    }
    
    resultMessage.textContent = message;
    
    // Sauvegarder le résultat
    saveResult();
}

// Gestion des événements
prevBtn.addEventListener('click', function() {
    if (currentQuestionIndex > 0) {
        currentQuestionIndex--;
        displayQuestion();
    }
});

nextBtn.addEventListener('click', function() {
    if (!hasAnswer()) {
        notifications.warning('Attention', 'Veuillez répondre à cette question avant de continuer.');
        return;
    }
    
    if (currentQuestionIndex < quizData.questions.length - 1) {
        currentQuestionIndex++;
        displayQuestion();
    }
});

finishBtn.addEventListener('click', function() {
    if (!hasAnswer()) {
        notifications.warning('Attention', 'Veuillez répondre à cette question avant de terminer.');
        return;
    }
    
    showResults();
});

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    initQuiz();
});
</script>

<?php endif; ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>