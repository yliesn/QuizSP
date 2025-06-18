<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../auth/auth.php';
require_login();

// Vérifier le rôle admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'ADMIN') {
    $_SESSION['error_message'] = "Accès refusé.";
    redirect(BASE_URL . '/dashboard.php');
}

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
    <div class="w-full max-w-3xl bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-2xl font-bold text-secondary font-oswald mb-8 text-center">Liste des Quiz</h1>
        <a href="create.php" class="inline-block mb-6 px-6 py-2 bg-primary text-white rounded hover:bg-secondary transition">+ Nouveau quiz</a>
        <?php if (empty($quizz)): ?>
            <div class="text-center text-custom">Aucun quiz trouvé.</div>
        <?php else: ?>
        <table class="w-full table-auto border-collapse">
            <thead>
                <tr class="bg-light-gray text-custom">
                    <th class="px-4 py-2 text-left">Titre</th>
                    <th class="px-4 py-2 text-left">Description</th>
                    <th class="px-4 py-2 text-left">Créé le</th>
                    <th class="px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quizz as $quiz): ?>
                <tr class="border-b hover:bg-gray-100">
                    <td class="px-4 py-2 font-semibold text-custom"><?php echo htmlspecialchars($quiz['titre']); ?></td>
                    <td class="px-4 py-2 text-custom"><?php echo htmlspecialchars($quiz['description']); ?></td>
                    <td class="px-4 py-2 text-custom"><?php echo htmlspecialchars($quiz['date_creation']); ?></td>
                    <td class="px-4 py-2">
                        <a href="view.php?id=<?php echo $quiz['id']; ?>" class="text-primary hover:underline mr-2">Voir</a>
                        <!-- <a href="edit.php?id=<?php echo $quiz['id']; ?>" class="text-secondary hover:underline mr-2">Modifier</a> -->
                        <!-- <a href="delete.php?id=<?php echo $quiz['id']; ?>" class="text-danger hover:underline">Supprimer</a> -->
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
