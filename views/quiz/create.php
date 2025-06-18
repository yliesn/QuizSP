<?php
define('ROOT_PATH', realpath(__DIR__ . '/../../'));
$page_title = 'Créer un quiz';
include ROOT_PATH . '/includes/header.php';
?>
<div class="container py-5">
    <div class="quiz-container mx-auto">
        <h1 class="text-center mb-4 font-oswald">Générateur de Quiz JSON</h1>
        <div class="quiz-info-form mb-4">
            <h3 class="font-oswald">Informations Générales du Quiz</h3>
            <div class="mb-3">
                <label for="quizTitle" class="form-label">Titre du Quiz :</label>
                <input type="text" id="quizTitle" class="form-control" placeholder="Ex: Quiz de sécurité incendie" required>
            </div>
            <div class="mb-3">
                <label for="quizDescription" class="form-label">Description du Quiz :</label>
                <textarea id="quizDescription" class="form-control" rows="4" placeholder="Ex: Ce quiz teste les connaissances fondamentales..." required></textarea>
            </div>
        </div>
        <div id="quizQuestions"></div>
        <button id="addQuestion" class="btn btn-custom-primary w-100 mb-3">Ajouter une question</button>
        <button id="generateJson" class="btn btn-custom-primary w-100">Générer le JSON du Quiz</button>
        <button id="downloadJson" class="btn btn-custom-download w-100 d-none">ajout Quiz BDD</button>
        <h2 class="text-center mt-5 mb-3 font-oswald">JSON du Quiz Généré :</h2>
        <pre id="jsonOutput" class="rounded p-4"></pre>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script>
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
                <h3 class="font-oswald">Question n°${questionForm.dataset.id}</h3>
                <div class="mb-3">
                    <label for="questionText_${questionForm.dataset.id}" class="form-label">Question :</label>
                    <textarea id="questionText_${questionForm.dataset.id}" class="form-control" rows="3" placeholder="Entrez la question..." required></textarea>
                </div>

                <div class="mb-3">
                    <label for="questionType_${questionForm.dataset.id}" class="form-label">Type de question :</label>
                    <select id="questionType_${questionForm.dataset.id}" class="form-select" onchange="toggleOptionsAndCorrectAnswerInput(this.value, ${questionForm.dataset.id})">
                        <option value="choix_multiple">Choix Multiple</option>
                        <option value="texte">Texte</option>
                    </select>
                </div>

                <div id="optionsContainer_${questionForm.dataset.id}" class="options-container mb-3">
                    <label class="form-label">Options (pour choix multiple) :</label>
                    <div id="optionsList_${questionForm.dataset.id}">
                        <div class="option-item">
                            <input type="text" class="form-control option-input" placeholder="Option 1" oninput="updateCorrectAnswersSelection(${questionForm.dataset.id})">
                            <button type="button" class="btn btn-sm btn-custom-danger remove-option ms-2">Supprimer</button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-success add-option mt-2">Ajouter une option</button>
                </div>

                <div id="correctAnswersSelectionContainer_${questionForm.dataset.id}" class="correct-answers-selection-container mb-3">
                    <label class="form-label">Réponse(s) correcte(s) (sélectionnez parmi les options) :</label>
                    <div id="correctAnswersCheckboxes_${questionForm.dataset.id}" class="form-check-group">
                        </div>
                </div>

                <div id="textCorrectAnswerContainer_${questionForm.dataset.id}" class="correct-answers-selection-container hidden mb-3">
                    <label for="textCorrectAnswer_${questionForm.dataset.id}" class="form-label">Réponse correcte (pour texte) :</label>
                    <input type="text" id="textCorrectAnswer_${questionForm.dataset.id}" class="form-control" placeholder="Entrez la réponse correcte">
                </div>

                <button type="button" class="btn btn-custom-danger remove-question mt-3 w-100">Supprimer cette question</button>
            `;
            quizQuestionsDiv.appendChild(questionForm);

            // Add event listeners for new elements
            questionForm.querySelector('.add-option').addEventListener('click', () => {
                addOption(questionForm.dataset.id);
                updateCorrectAnswersSelection(questionForm.dataset.id); // Update selection after adding option
            });
            questionForm.addEventListener('click', (event) => {
                if (event.target.classList.contains('remove-option')) {
                    event.target.closest('.option-item').remove();
                    updateCorrectAnswersSelection(questionForm.dataset.id); // Update selection after removing option
                }
                if (event.target.classList.contains('remove-question')) {
                    questionForm.remove();
                    reindexQuestions();
                }
            });

            // Add event listener for input changes on options to update the correct answer selector
            questionForm.querySelectorAll('.option-input').forEach(input => {
                input.addEventListener('input', () => updateCorrectAnswersSelection(questionForm.dataset.id));
            });

            // Ensure initial state is correct for 'choix_multiple'
            toggleOptionsAndCorrectAnswerInput('choix_multiple', questionForm.dataset.id);
            updateCorrectAnswersSelection(questionForm.dataset.id); // Initial population
        }

        function reindexQuestions() {
            const questionForms = document.querySelectorAll('.question-form');
            questionIdCounter = 1;
            questionForms.forEach(form => {
                form.dataset.id = questionIdCounter;
                form.querySelector('h3').textContent = `Question n°${questionIdCounter}`;
                // Update IDs for inputs/labels
                form.querySelector('textarea').id = `questionText_${questionIdCounter}`;
                form.querySelector('label[for^="questionText_"]').setAttribute('for', `questionText_${questionIdCounter}`);

                const selectElement = form.querySelector('select');
                selectElement.id = `questionType_${questionIdCounter}`;
                form.querySelector('label[for^="questionType_"]').setAttribute('for', `questionType_${questionIdCounter}`);
                selectElement.onchange = function() { toggleOptionsAndCorrectAnswerInput(this.value, questionIdCounter); };

                form.querySelector('.options-container').id = `optionsContainer_${questionIdCounter}`;
                form.querySelector('#optionsList_').id = `optionsList_${questionIdCounter}`;

                form.querySelector('.correct-answers-selection-container').id = `correctAnswersSelectionContainer_${questionIdCounter}`;
                form.querySelector('#correctAnswersCheckboxes_').id = `correctAnswersCheckboxes_${questionIdCounter}`;

                const textCorrectAnswerInput = form.querySelector('input[id^="textCorrectAnswer_"]');
                if (textCorrectAnswerInput) {
                    textCorrectAnswerInput.id = `textCorrectAnswer_${questionIdCounter}`;
                    form.querySelector('label[for^="textCorrectAnswer_"]').setAttribute('for', `textCorrectAnswer_${questionIdCounter}`);
                }
                form.querySelector('#textCorrectAnswerContainer_').id = `textCorrectAnswerContainer_${questionIdCounter}`;

                // Re-attach event listeners for options input to ensure correct answers selection updates
                form.querySelectorAll('.option-input').forEach(input => {
                    input.oninput = () => updateCorrectAnswersSelection(form.dataset.id);
                });

                updateCorrectAnswersSelection(questionIdCounter); // Re-populate based on new IDs
                questionIdCounter++;
            });
        }


        function toggleOptionsAndCorrectAnswerInput(type, id) {
            const optionsContainer = document.getElementById(`optionsContainer_${id}`);
            const correctAnswersSelectionContainer = document.getElementById(`correctAnswersSelectionContainer_${id}`);
            const textCorrectAnswerContainer = document.getElementById(`textCorrectAnswerContainer_${id}`);

            if (type === 'choix_multiple') {
                optionsContainer.classList.remove('hidden');
                correctAnswersSelectionContainer.classList.remove('hidden');
                textCorrectAnswerContainer.classList.add('hidden');
                updateCorrectAnswersSelection(id); // Ensure selector is updated when type changes
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
                <input type="text" class="form-control option-input" placeholder="Nouvelle option" oninput="updateCorrectAnswersSelection(${questionId})">
                <button type="button" class="btn btn-sm btn-custom-danger remove-option ms-2">Supprimer</button>
            `;
            optionsList.appendChild(optionItem);
        }

        function updateCorrectAnswersSelection(questionId) {
            const optionsInputs = document.querySelectorAll(`#optionsList_${questionId} .option-input`);
            const correctAnswersCheckboxesDiv = document.getElementById(`correctAnswersCheckboxes_${questionId}`);
            
            // Store currently checked values to re-select them after update
            const currentlyCheckedValues = Array.from(correctAnswersCheckboxesDiv.querySelectorAll('input[type="checkbox"]:checked'))
                                             .map(cb => cb.value);

            correctAnswersCheckboxesDiv.innerHTML = ''; // Clear previous checkboxes

            // Filter out empty options before populating checkboxes
            const validOptions = Array.from(optionsInputs).map(input => input.value.trim()).filter(Boolean);

            if (validOptions.length === 0) {
                correctAnswersCheckboxesDiv.innerHTML = '<p class="text-muted">Ajoutez des options pour sélectionner les réponses correctes.</p>';
                return;
            }

            validOptions.forEach((optionValue, index) => {
                const isChecked = currentlyCheckedValues.includes(optionValue) ? 'checked' : '';
                const formCheckDiv = document.createElement('div');
                formCheckDiv.classList.add('form-check');
                formCheckDiv.innerHTML = `
                    <input class="form-check-input" type="checkbox" value="${optionValue}" id="correctAnswerOption_${questionId}_${index}" ${isChecked}>
                    <label class="form-check-label" for="correctAnswerOption_${questionId}_${index}">
                        ${optionValue}
                    </label>
                `;
                correctAnswersCheckboxesDiv.appendChild(formCheckDiv);
            });
        }

        function generateJson() {
            quizData.questions = []; // Reset questions array for each generation

            const quizTitle = document.getElementById('quizTitle').value.trim();
            const quizDescription = document.getElementById('quizDescription').value.trim();
            const downloadButton = document.getElementById('downloadJson');

            if (!quizTitle) {
                alert("Veuillez entrer un titre pour le quiz.");
                document.getElementById('jsonOutput').textContent = "Erreur : Veuillez remplir le titre du quiz.";
                downloadButton.classList.add('d-none'); // Hide download button on error
                return;
            }
            if (!quizDescription) {
                alert("Veuillez entrer une description pour le quiz.");
                document.getElementById('jsonOutput').textContent = "Erreur : Veuillez remplir la description du quiz.";
                downloadButton.classList.add('d-none'); // Hide download button on error
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
                    alert(`Veuillez remplir la question pour la question n°${id}.`);
                    isValid = false;
                    return;
                }

                let questionObject = {
                    id: id,
                    question: questionText,
                    type: questionType,
                };

                if (questionType === 'choix_multiple') {
                    const optionsInputs = form.querySelectorAll(`#optionsList_${id} .option-input`);
                    const options = Array.from(optionsInputs).map(input => input.value.trim()).filter(Boolean);

                    const selectedCheckboxes = form.querySelectorAll(`#correctAnswersCheckboxes_${id} input[type="checkbox"]:checked`);
                    const reponseCorrecte = Array.from(selectedCheckboxes).map(checkbox => checkbox.value.trim()).filter(Boolean);

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
                        alert(`Veuillez entrer la réponse correcte pour la question texte n°${id}.`);
                        isValid = false;
                        return;
                    }
                    questionObject.reponse_correcte = textCorrectAnswer;
                }
                quizData.questions.push(questionObject);
            });

            if (isValid) {
                document.getElementById('jsonOutput').textContent = JSON.stringify(quizData, null, 2);
                downloadButton.classList.remove('d-none'); // Show download button if JSON is valid
            } else {
                document.getElementById('jsonOutput').textContent = "Erreur : Veuillez corriger les problèmes dans les formulaires.";
                downloadButton.classList.add('d-none'); // Hide download button on error
                quizData.questions = []; // Clear questions if an error occurred
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
                    alert(`Quiz sauvegardé avec succès sur le serveur : ${result.filename}`);
                } else {
                    throw new Error(result.error || 'Erreur inconnue');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert(`Erreur lors de la sauvegarde: ${error.message}`);
            }
        }

        // Add the first question form on page load
        addQuestionForm();
</script>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
