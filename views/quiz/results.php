<?php
// Page d'affichage des résultats de tous les quiz par personne (admin et modérateur uniquement)
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../auth/auth.php';
require_login();

// Vérifier le rôle admin ou modérateur
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['ADMIN', 'MODERATEUR'])) {
    $_SESSION['error_message'] = "Accès refusé.";
    redirect(BASE_URL . '/dashboard.php');
}

// Connexion à la base de données
require_once __DIR__ . '/../../config/database.php';
$pdo = getDbConnection();
if (!$pdo) {
    die('Erreur de connexion à la base de données.');
}

// Récupérer les filtres
$filter_user = isset($_GET['user']) ? trim($_GET['user']) : '';
$filter_quiz = isset($_GET['quiz']) ? trim($_GET['quiz']) : '';
$filter_date = isset($_GET['date']) ? trim($_GET['date']) : '';

// Construction de la requête avec filtres dynamiques
$where = [];
$params = [];
if ($filter_user !== '') {
    $where[] = '(u.nom LIKE :user OR u.prenom LIKE :user)';
    $params[':user'] = "%$filter_user%";
}
if ($filter_quiz !== '') {
    $where[] = 'q.titre LIKE :quiz';
    $params[':quiz'] = "%$filter_quiz%";
}
if ($filter_date !== '') {
    $where[] = 'DATE(r.date_passage) = :date';
    $params[':date'] = $filter_date;
}
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$sql = "SELECT r.id, r.user_id, r.quizz_id, r.score, r.date_passage, u.nom, u.prenom, q.titre
        FROM resultat_quiz r
        JOIN user u ON r.user_id = u.id
        JOIN quizz q ON r.quizz_id = q.id
        $where_sql
        ORDER BY r.date_passage DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Regrouper les résultats par quiz
global $results;
$quizzes = [];
foreach ($results as $row) {
    $quizzes[$row['titre']][] = $row;
}

// Récupérer le nombre de questions pour chaque quiz
$nb_questions_by_quiz = [];
$stmt = $pdo->query('SELECT quizz.id, COUNT(question.id) as nb_questions FROM quizz LEFT JOIN question ON question.quizz_id = quizz.id GROUP BY quizz.id');
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $nb_questions_by_quiz[$row['id']] = $row['nb_questions'];
}

$page_title = 'Résultats des quiz';
include __DIR__ . '/../../includes/header.php';
?>
<!-- Ajout Chart.js depuis CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<div class="container mx-auto max-w-4xl mt-10 p-8 bg-white rounded-lg shadow-lg">
    <h1 class="text-2xl font-bold mb-6 text-primary flex items-center gap-2"><i class="fas fa-poll"></i> Résultats des quizz</h1>
    <div class="mb-6 flex items-center gap-4">
        <form method="get" class="flex flex-wrap gap-4 items-end">
            <!-- <div>
                <label for="user" class="block text-custom">Nom ou prénom</label>
                <input type="text" id="user" name="user" value="<?php echo htmlspecialchars($filter_user); ?>" class="mt-1 px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary">
            </div> -->
            <div>
                <label for="quiz" class="block text-custom">Quiz</label>
                <input type="text" id="quiz" name="quiz" value="<?php echo htmlspecialchars($filter_quiz); ?>" class="mt-1 px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label for="date" class="block text-custom">Date</label>
                <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($filter_date); ?>" class="mt-1 px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <button type="submit" class="px-4 py-2 rounded bg-primary text-white hover:bg-secondary transition">Filtrer</button>
                <a href="results.php" class="ml-2 px-4 py-2 rounded bg-gray-300 text-gray-700 hover:bg-gray-400 transition">Réinitialiser</a>
            </div>
        </form>
        <div class="flex items-center ml-auto">
            <input type="checkbox" id="toggle-graphs" checked style="width:1.2em;height:1.2em;">
            <label for="toggle-graphs" class="ml-2 text-custom font-semibold cursor-pointer">Afficher les graphiques</label>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('toggle-graphs');
        function updateGraphsDisplay() {
            document.querySelectorAll('canvas[id^="chart-quiz-"]').forEach(c => {
                c.style.display = toggle.checked ? '' : 'none';
            });
        }
        toggle.addEventListener('change', updateGraphsDisplay);
        updateGraphsDisplay();
    });
    </script>
    <?php if (empty($quizzes)): ?>
        <div class="text-center py-4">Aucun résultat trouvé.</div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($quizzes as $quiz_title => $quiz_results): ?>
                <?php 
                    $quiz_id = $quiz_results[0]['quizz_id'];
                    $total_questions = isset($nb_questions_by_quiz[$quiz_id]) ? $nb_questions_by_quiz[$quiz_id] : '?';
                    // Préparer les scores pour le graphique
                    $scores = array_map(function($row) { return (int)$row['score']; }, $quiz_results);
                    $labels = array_map(function($row) { return $row['prenom'] . ' ' . $row['nom']; }, $quiz_results);

                    // Regrouper les scores pour le camembert : nombre d'utilisateurs par score
                    $score_counts = array_count_values($scores);
                    $pie_labels = array_map(function($score) use ($total_questions) {
                        return $score . ' / ' . $total_questions;
                    }, array_keys($score_counts));
                    $pie_data = array_values($score_counts);
                    $pie_colors = [];
                    $nb_colors = max(1, count($pie_labels));
                    foreach ($pie_labels as $i => $lbl) {
                        // Palette dynamique sur tout le cercle chromatique
                        $hue = round(360 * $i / $nb_colors);
                        $pie_colors[] = "hsl($hue, 70%, 60%)";
                    }
                ?>
                <div class="bg-gray-50 rounded-lg shadow p-4">
                    <h2 class="text-lg font-semibold mb-3 text-primary flex items-center gap-2"><i class="fas fa-clipboard-list"></i> <?php echo htmlspecialchars($quiz_title); ?> <span class="ml-2 text-xs text-gray-500">(<?php echo $total_questions; ?> question<?php echo ($total_questions > 1 ? 's' : ''); ?>)</span></h2>
                    <!-- Graphe des scores -->
                    <canvas id="chart-quiz-<?php echo $quiz_id; ?>" height="180"></canvas>
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        if (window.Chart) {
                            const ctx = document.getElementById('chart-quiz-<?php echo $quiz_id; ?>').getContext('2d');
                            new Chart(ctx, {
                                type: 'pie',
                                data: {
                                    labels: <?php echo json_encode($pie_labels); ?>,
                                    datasets: [{
                                        label: 'Répartition des scores',
                                        data: <?php echo json_encode($pie_data); ?>,
                                        backgroundColor: <?php echo json_encode($pie_colors); ?>,
                                        borderColor: '#fff',
                                        borderWidth: 2
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        legend: { display: true, position: 'bottom' },
                                        title: { display: false }
                                    }
                                }
                            });
                        }
                    });
                    </script>
                    <table class="min-w-full border text-xs mb-2">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-2 py-1 border">Nom</th>
                                <th class="px-2 py-1 border">Prénom</th>
                                <th class="px-2 py-1 border">Score</th>
                                <th class="px-2 py-1 border">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quiz_results as $row): ?>
                                <tr>
                                    <td class="border px-2 py-1"><?php echo htmlspecialchars($row['nom']); ?></td>
                                    <td class="border px-2 py-1"><?php echo htmlspecialchars($row['prenom']); ?></td>
                                    <td class="border px-2 py-1 font-bold text-center">
                                        <?php echo htmlspecialchars($row['score']) . ' / ' . $total_questions; ?>
                                    </td>
                                    <td class="border px-2 py-1"><?php echo htmlspecialchars($row['date_passage']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="mt-8 flex justify-end">
        <a href="<?php echo BASE_URL; ?>/dashboard.php" class="px-4 py-2 rounded bg-primary text-white hover:bg-secondary transition">Retour au tableau de bord</a>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
