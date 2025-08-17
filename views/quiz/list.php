<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../auth/auth.php';
require_login();

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', realpath(__DIR__ . '/../../'));
}
$page_title = 'Liste des quiz';
include ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/config/database.php';

$pdo = getDbConnection();
$quizz = [];
if ($pdo) {
    $stmt = $pdo->query('SELECT id, titre, description, date_creation FROM quizz ORDER BY date_creation DESC');
    $quizz = $stmt->fetchAll();
}
?>
<div class="min-h-screen flex flex-col items-center justify-start bg-custom py-10">
    <div class="w-full max-w-4xl bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-2xl font-bold text-secondary font-oswald mb-8 text-center">Liste des Quiz</h1>
        
        <!-- Barre de recherche -->
        <div class="mb-6 flex flex-col sm:flex-row gap-4 items-center justify-between">
            <div class="w-full sm:w-auto flex-1">
                <div class="relative">
                    <input type="text" 
                           id="search-input" 
                           placeholder="Rechercher un quiz par titre ou description..." 
                           class="w-full px-4 py-2 pl-10 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
            </div>
            
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN'): ?>
                <div class="w-full sm:w-auto">
                    <a href="create.php" class="inline-block w-full sm:w-auto px-6 py-2 bg-primary text-white rounded hover:bg-secondary transition text-center">
                        <i class="fas fa-plus mr-2"></i>Nouveau quiz
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Compteur de résultats -->
        <div class="mb-4">
            <span id="result-count" class="text-sm text-muted-custom">
                <?php echo count($quizz); ?> quiz trouvé(s)
            </span>
        </div>

        <?php if (empty($quizz)): ?>
            <div id="no-quiz-message" class="text-center text-custom">Aucun quiz trouvé.</div>
        <?php else: ?>
        <!-- Conteneur des quiz -->
        <div id="quiz-container" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($quizz as $quiz): ?>
            <div class="quiz-card bg-light-gray rounded-lg shadow p-6 flex flex-col justify-between h-full border border-gray-200" 
                 data-title="<?php echo htmlspecialchars(strtolower($quiz['titre'])); ?>"
                 data-description="<?php echo htmlspecialchars(strtolower($quiz['description'])); ?>">
                <div>
                    <h2 class="quiz-title text-xl font-bold text-custom mb-2"><?php echo htmlspecialchars($quiz['titre']); ?></h2>
                    <p class="quiz-description text-custom mb-4"><?php echo nl2br(htmlspecialchars($quiz['description'])); ?></p>
                </div>
                <div class="flex items-center justify-between mt-auto">
                    <span class="text-xs text-muted-custom">Créé le : <?php echo date('d/m/Y H:i', strtotime($quiz['date_creation'])); ?></span>
                    <a href="view.php?id=<?php echo $quiz['id']; ?>" class="inline-block px-4 py-2 bg-primary text-white rounded hover:bg-secondary transition ml-2">
                        <i class="fas fa-play mr-1"></i>Commencer
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Message quand aucun résultat de recherche -->
        <div id="no-results-message" class="text-center text-custom mt-8 hidden">
            <i class="fas fa-search text-gray-400 text-4xl mb-4"></i>
            <p class="text-lg">Aucun quiz ne correspond à votre recherche.</p>
            <p class="text-sm text-muted-custom mt-2">Essayez avec d'autres mots-clés.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const quizCards = document.querySelectorAll('.quiz-card');
    const quizContainer = document.getElementById('quiz-container');
    const resultCount = document.getElementById('result-count');
    const noResultsMessage = document.getElementById('no-results-message');
    const noQuizMessage = document.getElementById('no-quiz-message');
    
    // Fonction de recherche
    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;
        
        quizCards.forEach(card => {
            const title = card.dataset.title;
            const description = card.dataset.description;
            
            // Vérifier si le terme de recherche est présent dans le titre ou la description
            const isVisible = title.includes(searchTerm) || description.includes(searchTerm);
            
            if (isVisible) {
                card.style.display = 'flex';
                visibleCount++;
                
                // Surligner les termes de recherche
                highlightSearchTerms(card, searchTerm);
            } else {
                card.style.display = 'none';
            }
        });
        
        // Mettre à jour le compteur
        updateResultCount(visibleCount, searchTerm);
        
        // Afficher/masquer le message "aucun résultat"
        if (visibleCount === 0 && searchTerm !== '') {
            noResultsMessage.classList.remove('hidden');
            quizContainer.classList.add('hidden');
            if (noQuizMessage) {
                noQuizMessage.classList.add('hidden');
            }
        } else {
            noResultsMessage.classList.add('hidden');
            quizContainer.classList.remove('hidden');
            if (noQuizMessage && visibleCount === 0) {
                noQuizMessage.classList.remove('hidden');
            } else if (noQuizMessage) {
                noQuizMessage.classList.add('hidden');
            }
        }
    }
    
    // Fonction pour surligner les termes de recherche
    function highlightSearchTerms(card, searchTerm) {
        const titleElement = card.querySelector('.quiz-title');
        const descriptionElement = card.querySelector('.quiz-description');
        
        if (searchTerm === '') {
            // Restaurer le texte original si pas de recherche
            titleElement.textContent = titleElement.textContent;
            descriptionElement.textContent = descriptionElement.textContent;
            return;
        }
        
        // Fonction helper pour surligner le texte
        function highlightText(element, term) {
            const originalText = element.textContent;
            if (originalText.toLowerCase().includes(term)) {
                const regex = new RegExp(`(${term})`, 'gi');
                const highlightedText = originalText.replace(regex, '<mark class="bg-yellow-200 px-1 rounded">$1</mark>');
                element.innerHTML = highlightedText;
            }
        }
        
        highlightText(titleElement, searchTerm);
        highlightText(descriptionElement, searchTerm);
    }
    
    // Fonction pour mettre à jour le compteur de résultats
    function updateResultCount(count, searchTerm) {
        if (searchTerm === '') {
            resultCount.textContent = `${quizCards.length} quiz trouvé(s)`;
        } else {
            resultCount.textContent = `${count} quiz trouvé(s) pour "${searchTerm}"`;
        }
    }
    
    // Écouteur d'événement pour la recherche en temps réel
    searchInput.addEventListener('input', performSearch);
    
    // Écouteur pour la touche Échap (pour effacer la recherche)
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            searchInput.value = '';
            performSearch();
            searchInput.blur();
        }
    });
    
    // Focus automatique sur la barre de recherche avec Ctrl+F ou Cmd+F
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            searchInput.focus();
        }
    });
});
</script>

<style>
/* Animations pour les transitions */
.quiz-card {
    transition: all 0.3s ease;
}

.quiz-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

/* Style pour le surlignage */
mark {
    background-color: #eeeacfff !important;
    padding: 1px 2px;
    border-radius: 2px;
}

/* Animation pour le compteur de résultats */
#result-count {
    transition: all 0.2s ease;
}

/* Style pour la barre de recherche */
#search-input:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    border-color: #3b82f6;
}

/* Style responsive pour la barre de recherche */
@media (max-width: 640px) {
    .quiz-card {
        min-height: auto;
    }
}
</style>

<?php include ROOT_PATH . '/includes/footer.php'; ?>