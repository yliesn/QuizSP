<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../auth/auth.php';
require_login();

// Vérifier le rôle admin
// if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'ADMIN') {
//     $_SESSION['error_message'] = "Accès refusé.";
//     redirect(BASE_URL . '/dashboard.php');
// }

define('ROOT_PATH', realpath(__DIR__ . '/../../'));
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
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN'): ?>
            <a href="create.php" class="inline-block mb-6 px-6 py-2 bg-primary text-white rounded hover:bg-secondary transition">+ Nouveau quiz</a>
        <?php endif; ?>

        <?php if (empty($quizz)): ?>
            <div class="text-center text-custom">Aucun quiz trouvé.</div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($quizz as $quiz): ?>
            <div class="bg-light-gray rounded-lg shadow p-6 flex flex-col justify-between h-full border border-gray-200">
                <div>
                    <h2 class="text-xl font-bold text-custom mb-2"><?php echo htmlspecialchars($quiz['titre']); ?></h2>
                    <p class="text-custom mb-4"><?php echo nl2br(htmlspecialchars($quiz['description'])); ?></p>
                </div>
                <div class="flex items-center justify-between mt-auto">
                    <span class="text-xs text-muted-custom">Créé le : <?php echo date('d/m/Y H:i', strtotime($quiz['date_creation'])); ?></span>
                    <a href="view.php?id=<?php echo $quiz['id']; ?>" class="inline-block px-4 py-2 bg-primary text-white rounded hover:bg-secondary transition ml-2">Voir</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
