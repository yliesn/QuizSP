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

// Récupérer tous les résultats de quiz avec info utilisateur et quiz
$sql = "SELECT r.id, r.user_id, r.quiz_id, r.score, r.date_passage, u.nom, u.prenom, q.titre
        FROM quiz_results r
        JOIN users u ON r.user_id = u.id
        JOIN quiz q ON r.quiz_id = q.id
        ORDER BY r.date_passage DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Résultats des quiz par personne';
include __DIR__ . '/../../includes/header.php';
?>
<div class="container mx-auto max-w-4xl mt-10 p-8 bg-white rounded-lg shadow-lg">
    <h1 class="text-2xl font-bold mb-6 text-primary flex items-center gap-2"><i class="fas fa-poll"></i> Résultats des quiz par personne</h1>
    <div class="overflow-x-auto">
        <table class="min-w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 border">Nom</th>
                    <th class="px-4 py-2 border">Prénom</th>
                    <th class="px-4 py-2 border">Quiz</th>
                    <th class="px-4 py-2 border">Score</th>
                    <th class="px-4 py-2 border">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($results)): ?>
                    <tr><td colspan="5" class="text-center py-4">Aucun résultat trouvé.</td></tr>
                <?php else: ?>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($row['nom']); ?></td>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($row['prenom']); ?></td>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($row['titre']); ?></td>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($row['score']); ?></td>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($row['date_passage']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-8 flex justify-end">
        <a href="<?php echo BASE_URL; ?>/dashboard.php" class="px-4 py-2 rounded bg-primary text-white hover:bg-secondary transition">Retour au tableau de bord</a>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
