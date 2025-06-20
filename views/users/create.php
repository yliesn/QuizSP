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

$page_title = 'Créer un utilisateur';
include __DIR__ . '/../../includes/header.php';

// Affichage notification après création utilisateur
$message = '';
$message_type = '';
if (!empty($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'] ?? 'info';
    unset($_SESSION['message'], $_SESSION['message_type']);
}
?>
<div class="container mx-auto max-w-2xl mt-10 p-8 bg-white rounded-lg shadow-lg">
    <h1 class="text-2xl font-bold mb-6 text-primary flex items-center gap-2"><i class="fas fa-user-plus"></i>Créer un utilisateur</h1>
    <form method="POST" action="<?php echo BASE_URL; ?>/controllers/create_user.php" class="space-y-4 max-w-md">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <div>
            <label for="nom" class="block text-custom">Nom</label>
            <input type="text" id="nom" name="nom" required class="mt-1 w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary">
        </div>
        <div>
            <label for="prenom" class="block text-custom">Prénom</label>
            <input type="text" id="prenom" name="prenom" required class="mt-1 w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary">
        </div>
        <div>
            <label for="login" class="block text-custom">Login</label>
            <input type="text" id="login" name="login" required class="mt-1 w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary">
        </div>
        <div>
            <label for="role" class="block text-custom">Rôle</label>
            <select id="role" name="role" required class="mt-1 w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary">
                <option value="USER">Utilisateur</option>
                <option value="MODERATEUR">Modérateur</option>
                <option value="ADMIN">Administrateur</option>
            </select>
        </div>
        <div>
            <label for="password" class="block text-custom">Mot de passe</label>
            <input type="password" id="password" name="password" minlength="4" required class="mt-1 w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary">
            <div class="text-xs text-gray-400">Au moins 4 caractères.</div>
        </div>
        <div>
            <label for="confirm_password" class="block text-custom">Confirmer le mot de passe</label>
            <input type="password" id="confirm_password" name="confirm_password" required class="mt-1 w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary">
        </div>
        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 rounded bg-primary text-white hover:bg-secondary transition"><i class="fas fa-user-plus mr-1"></i>Créer l'utilisateur</button>
        </div>
    </form>
    <div class="mt-8 flex justify-end">
        <a href="<?php echo BASE_URL; ?>/dashboard.php" class="px-4 py-2 rounded bg-primary text-white hover:bg-secondary transition">Retour au tableau de bord</a>
    </div>
</div>
<script>
// Validation côté client du mot de passe
const password = document.getElementById('password');
const confirmPassword = document.getElementById('confirm_password');
const userForm = document.querySelector('form[action$="create_user.php"]');
if (userForm) {
    userForm.addEventListener('submit', function(e) {
        if (password.value !== confirmPassword.value) {
            e.preventDefault();
            notifications.error('Erreur', 'Les mots de passe ne correspondent pas.');
        }
    });
}
</script>
<?php if (!empty($message)): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        notifications.<?php echo $message_type; ?>(
            '<?php echo $message_type === 'success' ? 'Succès' : ucfirst($message_type); ?>',
            '<?php echo addslashes($message); ?>'
        );
    });
</script>
<?php endif; ?>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
