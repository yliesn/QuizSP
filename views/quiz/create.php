<?php
// Page de création d'utilisateur (admin)
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../auth/auth.php';
require_login();

// Vérifier le rôle admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'ADMIN') {
    $_SESSION['error_message'] = "Accès refusé.";
    redirect(BASE_URL . '/dashboard.php');
}

// Générer un token CSRF
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// define('ROOT_PATH', realpath(__DIR__ . '/../../'));

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', realpath(__DIR__ . '/../../'));
}
$page_title = 'Créer un quiz';
include ROOT_PATH . '/includes/header.php';
?>
<div class="min-h-screen flex items-center justify-center bg-custom">
    <div class="quiz-container w-full max-w-2xl p-8 bg-white rounded-lg shadow-lg">
        <h1 class="text-center mb-8 font-oswald text-3xl text-secondary">Générateur de Quiz</h1>
        <div class="quiz-info-form mb-8">
            <h3 class="font-oswald text-xl mb-4">Informations Générales du Quiz</h3>
            <div class="mb-6">
                <label for="quizTitle" class="block text-custom font-semibold mb-2">Titre du Quiz :</label>
                <input type="text" id="quizTitle" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Ex: Quiz de sécurité incendie" required>
            </div>
            <div class="mb-6">
                <label for="quizDescription" class="block text-custom font-semibold mb-2">Description du Quiz :</label>
                <textarea id="quizDescription" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary" rows="4" placeholder="Ex: Ce quiz teste les connaissances fondamentales..." required></textarea>
            </div>
        </div>
        <div id="quizQuestions"></div>
        <button id="addQuestion" class="w-full py-2 px-4 bg-primary text-white rounded hover:bg-secondary transition mb-3">Ajouter une question</button>
        <button id="generateJson" class="w-full py-2 px-4 bg-primary text-white rounded hover:bg-secondary transition mb-3">Générer le JSON du Quiz</button>
        <button id="downloadJson" class="w-full py-2 px-4 bg-accent text-white rounded hover:bg-primary transition d-none">Ajouter le Quiz en BDD</button>
        <h2 class="text-center mt-8 mb-4 font-oswald text-xl text-secondary">JSON du Quiz Généré :</h2>
        <pre id="jsonOutput" class="rounded p-4 bg-gray-800 text-gray-100"></pre>
    </div>
</div>
<script>
    // JavaScript à remplacer dans views/quiz/create.php
    let quizData = {
        title: "",
        description: "",
        questions: []
    };
    let questionIdCounter = 1;

    document.getElementById('addQuestion').addEventListener('click', addQuestionForm);
    document.getElementById('generateJson').addEventListener('click', generateJson);
    document.getElementById('downloadJson').addEventListener('click', downloadJson);

    function addQuestionForm() {
        const quizQuestionsDiv = document.getElementById('quizQuestions');
        const questionForm = document.createElement('div');
        questionForm.classList.add('question-form', 'mb-4');
        questionForm.dataset.id = questionIdCounter++;

        questionForm.innerHTML = `
            <div class="bg-light-gray p-4 rounded mb-6 border">
                <h3 class="font-oswald">Question n°${questionForm.dataset.id}</h3>
                <div class="mb-3">
                    <label for="questionText_${questionForm.dataset.id}" class="form-label">Question :</label>
                    <textarea id="questionText_${questionForm.dataset.id}" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary" rows="3" placeholder="Entrez la question..." required></textarea>
                </div>

                <div class="mb-3">
                    <label for="questionType_${questionForm.dataset.id}" class="form-label">Type de question :</label>
                    <select id="questionType_${questionForm.dataset.id}" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary" onchange="toggleOptionsAndCorrectAnswerInput(this.value, ${questionForm.dataset.id})">
                        <option value="choix_unique">Choix Unique</option>
                        <option value="choix_multiple">Choix Multiple</option>
                        <option value="texte">Texte</option>
                    </select>
                </div>

                <div id="optionsContainer_${questionForm.dataset.id}" class="options-container mb-3">
                    <label class="form-label">Options :</label>
                    <div id="optionsList_${questionForm.dataset.id}">
                        <div class="option-item">
                            <input type="text" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary option-input" placeholder="Option 1" oninput="updateCorrectAnswersSelection(${questionForm.dataset.id})">
                            <button type="button" class="btn btn-sm py-2 px-4 bg-secondary text-white rounded hover:bg-danger transition remove-option ms-2">Supprimer</button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm py-2 px-4 bg-primary text-white rounded hover:bg-secondary transition mt-2 add-option">Ajouter une option</button>
                </div>

                <div id="correctAnswersSelectionContainer_${questionForm.dataset.id}" class="correct-answers-selection-container mb-3">
                    <label class="form-label">Réponse(s) correcte(s) :</label>
                    <div id="correctAnswersCheckboxes_${questionForm.dataset.id}" class="form-check-group">
                    </div>
                </div>

                <div id="textCorrectAnswerContainer_${questionForm.dataset.id}" class="correct-answers-selection-container hidden mb-3">
                    <label for="textCorrectAnswer_${questionForm.dataset.id}" class="form-label">Réponse correcte :</label>
                    <input type="text" id="textCorrectAnswer_${questionForm.dataset.id}" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Entrez la réponse correcte">
                </div>

                <button type="button" class="btn btn-custom-danger remove-question mt-3 w-100 py-2 px-4 bg-secondary text-white rounded hover:bg-danger transition">Supprimer cette question</button>
            </div>
        `;
        quizQuestionsDiv.appendChild(questionForm);

        // Add event listeners for new elements
        questionForm.querySelector('.add-option').addEventListener('click', () => {
            addOption(questionForm.dataset.id);
            updateCorrectAnswersSelection(questionForm.dataset.id);
        });
        questionForm.addEventListener('click', (event) => {
            if (event.target.classList.contains('remove-option')) {
                event.target.closest('.option-item').remove();
                updateCorrectAnswersSelection(questionForm.dataset.id);
            }
            if (event.target.classList.contains('remove-question')) {
                questionForm.remove();
                reindexQuestions();
            }
        });

        questionForm.querySelectorAll('.option-input').forEach(input => {
            input.addEventListener('input', () => updateCorrectAnswersSelection(questionForm.dataset.id));
        });

        // Défaut : choix unique
        toggleOptionsAndCorrectAnswerInput('choix_unique', questionForm.dataset.id);
        updateCorrectAnswersSelection(questionForm.dataset.id);
    }

    function toggleOptionsAndCorrectAnswerInput(type, id) {
        const optionsContainer = document.getElementById(`optionsContainer_${id}`);
        const correctAnswersSelectionContainer = document.getElementById(`correctAnswersSelectionContainer_${id}`);
        const textCorrectAnswerContainer = document.getElementById(`textCorrectAnswerContainer_${id}`);

        if (type === 'choix_multiple' || type === 'choix_unique') {
            optionsContainer.classList.remove('hidden');
            correctAnswersSelectionContainer.classList.remove('hidden');
            textCorrectAnswerContainer.classList.add('hidden');
            updateCorrectAnswersSelection(id);
        } else if (type === 'texte') {
            optionsContainer.classList.add('hidden');
            correctAnswersSelectionContainer.classList.add('hidden');
            textCorrectAnswerContainer.classList.remove('hidden');
        }
    }

    function addOption(questionId) {
        const optionsList = document.getElementById(`optionsList_${questionId}`);
        const optionItem = document.createElement('div');
        optionItem.classList.add('option-item');
        optionItem.innerHTML = `
            <input type="text" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary option-input" placeholder="Nouvelle option" oninput="updateCorrectAnswersSelection(${questionId})">
            <button type="button" class="btn btn-sm py-2 px-4 bg-secondary text-white rounded hover:bg-danger transition remove-option ms-2">Supprimer</button>
        `;
        optionsList.appendChild(optionItem);
    }

    function updateCorrectAnswersSelection(questionId) {
        const questionType = document.getElementById(`questionType_${questionId}`).value;
        const optionsInputs = document.querySelectorAll(`#optionsList_${questionId} .option-input`);
        const correctAnswersCheckboxesDiv = document.getElementById(`correctAnswersCheckboxes_${questionId}`);
        
        const currentlyCheckedValues = Array.from(correctAnswersCheckboxesDiv.querySelectorAll('input:checked'))
                                        .map(cb => cb.value);

        correctAnswersCheckboxesDiv.innerHTML = '';

        const validOptions = Array.from(optionsInputs).map(input => input.value.trim()).filter(Boolean);

        if (validOptions.length === 0) {
            correctAnswersCheckboxesDiv.innerHTML = '<p class="text-muted">Ajoutez des options pour sélectionner les réponses correctes.</p>';
            return;
        }

        validOptions.forEach((optionValue, index) => {
            const isChecked = currentlyCheckedValues.includes(optionValue) ? 'checked' : '';
            const inputType = questionType === 'choix_multiple' ? 'checkbox' : 'radio';
            const inputName = questionType === 'choix_unique' ? `correct_${questionId}` : '';
            
            const formCheckDiv = document.createElement('div');
            formCheckDiv.classList.add('form-check');
            formCheckDiv.innerHTML = `
                <input class="form-check-input" type="${inputType}" ${inputName ? `name="${inputName}"` : ''} value="${optionValue}" id="correctAnswerOption_${questionId}_${index}" ${isChecked}>
                <label class="form-check-label" for="correctAnswerOption_${questionId}_${index}">
                    ${optionValue}
                </label>
            `;
            correctAnswersCheckboxesDiv.appendChild(formCheckDiv);
        });
    }

    function reindexQuestions() {
        const questionForms = document.querySelectorAll('.question-form');
        questionIdCounter = 1;
        questionForms.forEach(form => {
            form.dataset.id = questionIdCounter;
            form.querySelector('h3').textContent = `Question n°${questionIdCounter}`;
            
            // Update IDs
            const textarea = form.querySelector('textarea');
            textarea.id = `questionText_${questionIdCounter}`;
            form.querySelector('label[for^="questionText_"]').setAttribute('for', `questionText_${questionIdCounter}`);

            const selectElement = form.querySelector('select');
            selectElement.id = `questionType_${questionIdCounter}`;
            selectElement.onchange = function() { toggleOptionsAndCorrectAnswerInput(this.value, questionIdCounter); };

            form.querySelector('.options-container').id = `optionsContainer_${questionIdCounter}`;
            form.querySelector('[id^="optionsList_"]').id = `optionsList_${questionIdCounter}`;
            form.querySelector('[id^="correctAnswersSelectionContainer_"]').id = `correctAnswersSelectionContainer_${questionIdCounter}`;
            form.querySelector('[id^="correctAnswersCheckboxes_"]').id = `correctAnswersCheckboxes_${questionIdCounter}`;
            form.querySelector('[id^="textCorrectAnswerContainer_"]').id = `textCorrectAnswerContainer_${questionIdCounter}`;

            const textInput = form.querySelector('input[id^="textCorrectAnswer_"]');
            if (textInput) {
                textInput.id = `textCorrectAnswer_${questionIdCounter}`;
                form.querySelector('label[for^="textCorrectAnswer_"]').setAttribute('for', `textCorrectAnswer_${questionIdCounter}`);
            }

            form.querySelectorAll('.option-input').forEach(input => {
                input.oninput = () => updateCorrectAnswersSelection(form.dataset.id);
            });

            updateCorrectAnswersSelection(questionIdCounter);
            questionIdCounter++;
        });
    }

    function generateJson() {
        quizData.questions = [];

        const quizTitle = document.getElementById('quizTitle').value.trim();
        const quizDescription = document.getElementById('quizDescription').value.trim();
        const downloadButton = document.getElementById('downloadJson');

        if (!quizTitle || !quizDescription) {
            alert("Veuillez remplir le titre et la description du quiz.");
            document.getElementById('jsonOutput').textContent = "Erreur : Veuillez remplir tous les champs.";
            downloadButton.classList.add('d-none');
            return;
        }

        quizData.title = quizTitle;
        quizData.description = quizDescription;

        const questionForms = document.querySelectorAll('.question-form');
        let isValid = true;

        questionForms.forEach(form => {
            const id = parseInt(form.dataset.id);
            const questionText = form.querySelector('textarea').value.trim();
            const questionType = form.querySelector('select').value;

            if (!questionText) {
                alert(`Veuillez remplir la question n°${id}.`);
                isValid = false;
                return;
            }

            let questionObject = {
                id: id,
                question: questionText,
                type: questionType, // Maintenant on envoie le type explicite
            };

            if (questionType === 'choix_multiple' || questionType === 'choix_unique') {
                const optionsInputs = form.querySelectorAll(`#optionsList_${id} .option-input`);
                const options = Array.from(optionsInputs).map(input => input.value.trim()).filter(Boolean);

                const selectedInputs = form.querySelectorAll(`#correctAnswersCheckboxes_${id} input:checked`);
                const reponseCorrecte = Array.from(selectedInputs).map(input => input.value.trim()).filter(Boolean);

                if (options.length === 0) {
                    alert(`Veuillez ajouter au moins une option pour la question n°${id}.`);
                    isValid = false;
                    return;
                }
                if (reponseCorrecte.length === 0) {
                    alert(`Veuillez sélectionner au moins une réponse correcte pour la question n°${id}.`);
                    isValid = false;
                    return;
                }

                questionObject.options = options;
                questionObject.reponse_correcte = reponseCorrecte;

            } else if (questionType === 'texte') {
                const textCorrectAnswer = form.querySelector(`#textCorrectAnswer_${id}`).value.trim();
                if (!textCorrectAnswer) {
                    alert(`Veuillez entrer la réponse correcte pour la question n°${id}.`);
                    isValid = false;
                    return;
                }
                questionObject.reponse_correcte = textCorrectAnswer;
            }
            
            quizData.questions.push(questionObject);
        });

        if (isValid) {
            document.getElementById('jsonOutput').textContent = JSON.stringify(quizData, null, 2);
            downloadButton.classList.remove('d-none');
        } else {
            document.getElementById('jsonOutput').textContent = "Erreur : Veuillez corriger les problèmes.";
            downloadButton.classList.add('d-none');
            quizData.questions = [];
        }
    }

    async function downloadJson() {
        const jsonString = JSON.stringify(quizData, null, 2);
        const quizTitle = document.getElementById('quizTitle').value.trim();
        const fileName = `quiz_${quizTitle.replace(/[^a-zA-Z0-9]/g, '_').toLowerCase()}.json`;

        try {
            const response = await fetch('../../api/save-quiz.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    filename: fileName,
                    content: jsonString
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                notifications.success('Succès', `Quiz sauvegardé avec succès`);
            } else {
                throw new Error(result.error || 'Erreur inconnue');
            }
        } catch (error) {
            console.error('Erreur:', error);
            notifications.error('Erreur', `Erreur lors de la sauvegarde: ${error.message}`);
        }
    }

    // Ajouter la première question au chargement
    addQuestionForm();
</script>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
<?php if (!empty($message)): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        notifications.<?php echo $message_type; ?>(
            '<?php echo $message_type === 'success' ? 'Succès' : 'Erreur'; ?>',
            '<?php echo addslashes($message); ?>'
        );
    });
</script>
<?php endif; ?>
