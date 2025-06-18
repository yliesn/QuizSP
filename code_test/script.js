document.addEventListener('DOMContentLoaded', () => {
    const quizContainer = document.getElementById('quiz');
    const quizTitleElement = document.getElementById('quiz-title'); // Nouvel élément pour le titre
    const quizDescriptionElement = document.getElementById('quiz-description'); // Nouvel élément pour la description
    const questionContainer = document.getElementById('question-container');
    const questionText = document.getElementById('question-text');
    const optionsContainer = document.getElementById('options-container');
    const nextButton = document.getElementById('next-button');
    const submitButton = document.getElementById('submit-button');
    const resultContainer = document.getElementById('result-container');
    const scoreSpan = document.getElementById('score');
    const totalQuestionsSpan = document.getElementById('total-questions');
    const restartButton = document.getElementById('restart-button');

    let questions = [];
    let currentQuestionIndex = 0;
    let score = 0;
    let selectedAnswers = [];

    // Charger les questions et les métadonnées depuis le fichier JSON
    async function loadQuizData() {
        try {
            const response = await fetch('quiz.json');
            if (!response.ok) {
                throw new Error(`Erreur de chargement du quiz : ${response.statusText}`);
            }
            const quizData = await response.json();

            // Remplir le titre et la description du quiz
            if (quizData.title) {
                quizTitleElement.textContent = quizData.title;
            }
            if (quizData.description) {
                quizDescriptionElement.textContent = quizData.description;
            }

            // Assigner les questions
            if (quizData.questions && Array.isArray(quizData.questions)) {
                questions = quizData.questions;
                // Mélanger les questions pour un quiz différent à chaque fois
                questions = shuffleArray(questions);
                startQuiz();
            } else {
                throw new Error("Le fichier quiz.json ne contient pas de tableau 'questions' valide.");
            }

        } catch (error) {
            console.error("Impossible de charger les données du quiz :", error);
            quizTitleElement.textContent = "Erreur de chargement";
            quizDescriptionElement.textContent = "Désolé, un problème est survenu lors du chargement du quiz.";
            questionText.textContent = "Veuillez réessayer plus tard.";
            nextButton.classList.add('d-none');
            submitButton.classList.add('d-none');
        }
    }

    // Fonction pour mélanger un tableau (algorithme de Fisher-Yates)
    function shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
        return array;
    }

    function startQuiz() {
        currentQuestionIndex = 0;
        score = 0;
        quizContainer.classList.remove('d-none');
        resultContainer.classList.add('d-none');
        submitButton.classList.add('d-none');
        nextButton.classList.remove('d-none');
        displayQuestion();
    }

    function displayQuestion() {
        if (currentQuestionIndex < questions.length) {
            const currentQuestion = questions[currentQuestionIndex];

            // Animation de la question
            questionContainer.classList.remove('animate');
            void questionContainer.offsetWidth; // Force reflow
            questionContainer.classList.add('animate');

            questionText.textContent = currentQuestion.question;
            optionsContainer.innerHTML = ''; // Nettoyer les options précédentes
            selectedAnswers = []; // Réinitialiser les sélections pour la nouvelle question

            if (currentQuestion.type === 'choix_multiple') {
                currentQuestion.options.forEach(option => {
                    const button = document.createElement('button');
                    button.classList.add('option-button', 'btn', 'btn-outline-secondary', 'text-start', 'w-100');
                    button.textContent = option;
                    button.dataset.answer = option;
                    button.addEventListener('click', () => toggleOption(button, option));
                    optionsContainer.appendChild(button);
                });
            } else if (currentQuestion.type === 'texte') {
                const input = document.createElement('input');
                input.type = 'text';
                input.classList.add('text-input', 'form-control', 'form-control-lg');
                input.placeholder = 'Saisissez votre réponse ici...';
                input.addEventListener('input', (e) => selectedAnswers = [e.target.value.trim()]);
                optionsContainer.appendChild(input);
            }

            // Gérer l'affichage du bouton "Suivant" ou "Voir les résultats"
            if (currentQuestionIndex === questions.length - 1) {
                nextButton.classList.add('d-none');
                submitButton.classList.remove('d-none');
            } else {
                nextButton.classList.remove('d-none');
                submitButton.classList.add('d-none');
            }
        }
    }

    // Fonction pour basculer la sélection d'une option
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

    function checkAnswer() {
        const currentQuestion = questions[currentQuestionIndex];
        let isCorrect = false;

        if (currentQuestion.type === 'choix_multiple') {
            const correctSet = new Set(currentQuestion.reponse_correcte);
            const selectedSet = new Set(selectedAnswers);

            isCorrect = (correctSet.size === selectedSet.size) &&
                        Array.from(selectedSet).every(answer => correctSet.has(answer));

            const buttons = document.querySelectorAll('.option-button');
            buttons.forEach(button => {
                button.disabled = true;
                const optionValue = button.dataset.answer;

                if (currentQuestion.reponse_correcte.includes(optionValue)) {
                    button.classList.remove('btn-outline-secondary', 'btn-primary', 'incorrect');
                    button.classList.add('correct', 'btn-success');
                } else if (selectedAnswers.includes(optionValue) && !currentQuestion.reponse_correcte.includes(optionValue)) {
                    button.classList.remove('btn-outline-secondary', 'btn-primary', 'correct');
                    button.classList.add('incorrect', 'btn-danger');
                } else {
                    button.classList.remove('btn-primary', 'correct', 'incorrect');
                    button.classList.add('btn-outline-secondary');
                    if (!isCorrect && currentQuestion.reponse_correcte.includes(optionValue)) {
                         button.classList.add('btn-success', 'correct');
                    }
                }
            });

        } else if (currentQuestion.type === 'texte') {
            const userAnswer = selectedAnswers[0] || '';
            isCorrect = (userAnswer.toLowerCase() === currentQuestion.reponse_correcte.toLowerCase());

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

        if (isCorrect) {
            score++;
        }

        const allOptions = optionsContainer.querySelectorAll('.option-button, .text-input');
        allOptions.forEach(element => {
            element.disabled = true;
        });
    }

    function showResults() {
        quizContainer.classList.add('d-none');
        resultContainer.classList.remove('d-none');
        scoreSpan.textContent = score;
        totalQuestionsSpan.textContent = questions.length;
    }

    nextButton.addEventListener('click', () => {
        const currentQuestion = questions[currentQuestionIndex];
        let hasResponded = false;

        if (currentQuestion.type === 'choix_multiple') {
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

        if (currentQuestion.type === 'choix_multiple') {
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
        loadQuizData(); // Recharge toutes les données du quiz, y compris titre/description
    });

    // Charger les données du quiz au démarrage
    loadQuizData();
});