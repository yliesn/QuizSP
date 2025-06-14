<?php
// Page d'édition d'un utilisateur (admin)
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../auth/auth.php';
require_login();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'ADMIN') {
    $_SESSION['error_message'] = "Accès refusé.";
    redirect(BASE_URL . '/dashboard.php');
}

// Récupérer l'ID utilisateur à éditer
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($user_id <= 0) {
    $_SESSION['error_message'] = "Utilisateur invalide.";
    redirect(BASE_URL . '/views/users/list.php');
}

// Récupérer les infos utilisateur depuis la base
try {
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT id, nom, prenom, login, role, actif, date_derniere_connexion FROM user WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if (!$user) {
        $_SESSION['error_message'] = "Utilisateur introuvable.";
        redirect(BASE_URL . '/views/users/list.php');
    }
} catch (Exception $e) {
    error_log('Erreur édition utilisateur: ' . $e->getMessage());
    $_SESSION['error_message'] = "Erreur lors de la récupération de l'utilisateur.";
    redirect(BASE_URL . '/views/users/list.php');
}

// Gestion des messages (succès/erreur)
$message = '';
$message_type = '';
if (!empty($_SESSION['error_message'])) {
    $message = $_SESSION['error_message'];
    $message_type = 'error';
    unset($_SESSION['error_message']);
} elseif (!empty($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    $message_type = 'success';
    unset($_SESSION['success_message']);
}

// Générer un token CSRF
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$page_title = 'Modifier un utilisateur';
include __DIR__ . '/../../includes/header.php';
?>
<div class="container mx-auto max-w-2xl mt-10 p-8 bg-white rounded-lg shadow-lg">
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
    <h1 class="text-2xl font-bold mb-6 text-primary flex items-center gap-2"><i class="fas fa-user-edit"></i>Modifier l'utilisateur</h1>
    <form method="POST" action="<?php echo BASE_URL; ?>/controllers/edit_user.php" class="space-y-4 max-w-md">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
        <div class="flex gap-4">
            <div class="w-1/2">
                <label class="block text-custom">Nom</label>
                <input type="text" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required class="mt-1 w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div class="w-1/2">
                <label class="block text-custom">Prénom</label>
                <input type="text" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" required class="mt-1 w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
        </div>
        <div>
            <label class="block text-custom">Login</label>
            <input type="text" name="login" value="<?php echo htmlspecialchars($user['login']); ?>" required class="mt-1 w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary">
        </div>
        <div>
            <label class="block text-custom">Mot de passe</label>
            <input type="text" name="new_password" id="new_password" class="mt-1 w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary">
        </div>
        <div>
            <label class="block text-custom">Rôle</label>
            <select name="role" required class="mt-1 w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary">
                <option value="USER" <?php if ($user['role']==='USER') echo 'selected'; ?>>Utilisateur</option>
                <option value="MODERATEUR" <?php if ($user['role']==='MODERATEUR') echo 'selected'; ?>>Modérateur</option>
                <option value="ADMIN" <?php if ($user['role']==='ADMIN') echo 'selected'; ?>>Administrateur</option>
            </select>
        </div>
        <div>
            <label class="block text-custom">Statut</label>
            <select name="actif" required class="mt-1 w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary">
                <option value="1" <?php if ($user['actif']) echo 'selected'; ?>>Actif</option>
                <option value="0" <?php if (!$user['actif']) echo 'selected'; ?>>Inactif</option>
            </select>
        </div>
        <div>
            <label class="block text-custom">Dernière connexion</label>
            <input type="text" value="<?php echo $user['date_derniere_connexion'] ? (new DateTime($user['date_derniere_connexion']))->format('d/m/Y H:i') : 'Jamais'; ?>" disabled class="mt-1 w-full px-3 py-2 border rounded bg-gray-100 text-gray-500 cursor-not-allowed">
        </div>
        <div class="flex justify-end gap-2">
            <button type="submit" class="px-4 py-2 rounded bg-primary text-white hover:bg-secondary transition"><i class="fas fa-save mr-1"></i>Enregistrer</button>
            <a href="<?php echo BASE_URL; ?>/views/users/list.php" class="px-4 py-2 rounded bg-primary text-white hover:bg-secondary transition">Retour</a>
        </div>
    </form>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
