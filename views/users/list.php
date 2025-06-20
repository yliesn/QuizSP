<?php
// Liste des utilisateurs (admin)
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../auth/auth.php';
require_login();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'ADMIN') {
    $_SESSION['error_message'] = "Accès refusé.";
    redirect(BASE_URL . '/dashboard.php');
}

// Récupérer les utilisateurs
try {
    $db = getDbConnection();
    $stmt = $db->query('SELECT id, nom, prenom, login, role, actif, date_derniere_connexion FROM user ORDER BY nom, prenom');
    $users = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Erreur liste utilisateurs: ' . $e->getMessage());
    $_SESSION['error_message'] = "Erreur lors de la récupération des utilisateurs.";
    redirect(BASE_URL . '/dashboard.php');
}

$page_title = 'Liste des utilisateurs';
include __DIR__ . '/../../includes/header.php';
?>
<div class="container mx-auto max-w-4xl mt-10 p-8 bg-white rounded-lg shadow-lg">
    <h1 class="text-2xl font-bold mb-6 text-primary flex items-center gap-2"><i class="fas fa-users"></i>Liste des utilisateurs</h1>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border rounded" id="users-table">
            <thead>
                <tr class="bg-gray-100 text-left cursor-pointer">
                    <th class="py-2 px-4 border-b" data-sort="nom">Nom</th>
                    <th class="py-2 px-4 border-b" data-sort="prenom">Prénom</th>
                    <th class="py-2 px-4 border-b" data-sort="login">Login</th>
                    <th class="py-2 px-4 border-b" data-sort="role">Rôle</th>
                    <th class="py-2 px-4 border-b" data-sort="actif">Actif</th>
                    <th class="py-2 px-4 border-b" data-sort="date">Dernière connexion</th>
                    <th class="py-2 px-4 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-2 px-4"><?php echo htmlspecialchars($user['nom']); ?></td>
                    <td class="py-2 px-4"><?php echo htmlspecialchars($user['prenom']); ?></td>
                    <td class="py-2 px-4"><?php echo htmlspecialchars($user['login']); ?></td>
                    <td class="py-2 px-4">
                        <?php if ($user['role'] === 'ADMIN'): ?>
                            <span class="uppercase px-2 py-1 rounded bg-red-500 text-white">animateur</span>
                        <?php elseif ($user['role'] === 'MODERATEUR'): ?>
                            <span class="uppercase px-2 py-1 rounded bg-yellow-500 text-white">aide-anim</span>
                        <?php elseif (in_array($user['role'], ['JSP1', 'JSP2', 'JSP3', 'JSP4'])): ?>
                            <span class="uppercase px-2 py-1 rounded bg-indigo-500 text-white"><?php echo htmlspecialchars($user['role']); ?></span>
                        <?php else: ?>
                            <span class="uppercase px-2 py-1 rounded bg-blue-400 text-white">Utilisateur</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-2 px-4">
                        <?php if ($user['actif']): ?>
                            <span class="text-green-600 font-bold">Oui</span>
                        <?php else: ?>
                            <span class="text-red-600 font-bold">Non</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-2 px-4 text-xs text-gray-500">
                        <?php
                        if ($user['date_derniere_connexion']) {
                            $dt = new DateTime($user['date_derniere_connexion']);
                            echo $dt->format('d/m/Y H:i');
                        } else {
                            echo 'Jamais';
                        }
                        ?>
                    </td>
                    <td class="py-2 px-4 text-center">
                        <form method="POST" action="<?php echo BASE_URL; ?>/controllers/toggle_user.php" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <input type="hidden" name="actif" value="<?php echo $user['actif'] ? 0 : 1; ?>">
                            <button type="submit" class="p-2 rounded-full focus:outline-none focus:ring-2 focus:ring-primary transition <?php echo $user['actif'] ? 'bg-green-100 hover:bg-green-300 text-green-700' : 'bg-gray-200 hover:bg-gray-400 text-gray-600'; ?>" title="<?php echo $user['actif'] ? 'Désactiver' : 'Activer'; ?>">
                                <?php if ($user['actif']): ?>
                                    <i class="fas fa-toggle-on fa-lg"></i>
                                <?php else: ?>
                                    <i class="fas fa-toggle-off fa-lg"></i>
                                <?php endif; ?>
                            </button>
                        </form>
                        <a href="<?php echo BASE_URL; ?>/views/users/edit.php?id=<?php echo $user['id']; ?>" class="px-2 py-1 rounded bg-yellow-500 hover:bg-yellow-600 text-white text-xs mr-2"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-8 flex justify-end">
        <a href="<?php echo BASE_URL; ?>/views/users/create.php" class="px-4 py-2 rounded bg-primary text-white hover:bg-secondary transition mr-2"><i class="fas fa-user-plus mr-1"></i>Créer un utilisateur</a>
        <a href="<?php echo BASE_URL; ?>/dashboard.php" class="px-4 py-2 rounded bg-primary text-white hover:bg-secondary transition">Retour au tableau de bord</a>
    </div>
</div>
<script>
    // Tri côté client du tableau des utilisateurs
    document.addEventListener('DOMContentLoaded', function() {
        const table = document.getElementById('users-table');
        const headers = table.querySelectorAll('th[data-sort]');
        let sortDirection = 1;
        let lastSorted = null;
        headers.forEach(function(header, idx) {
            header.addEventListener('click', function() {
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                const sortKey = header.getAttribute('data-sort');
                if (lastSorted === idx) sortDirection *= -1; else sortDirection = 1;
                lastSorted = idx;
                rows.sort(function(a, b) {
                    let aText = a.children[idx].innerText.trim();
                    let bText = b.children[idx].innerText.trim();
                    if (sortKey === 'date') {
                        aText = aText === 'Jamais' ? '' : aText.split('/').reverse().join('-');
                        bText = bText === 'Jamais' ? '' : bText.split('/').reverse().join('-');
                    }
                    if (!isNaN(aText) && !isNaN(bText)) {
                        return (parseFloat(aText) - parseFloat(bText)) * sortDirection;
                    }
                    return aText.localeCompare(bText, 'fr', {numeric: true}) * sortDirection;
                });
                rows.forEach(row => tbody.appendChild(row));
            });
        });
    });
    </script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
