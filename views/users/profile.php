<?php
// Profil de l'utilisateur (consultation et modification)
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../auth/auth.php';
require_login();

// Récupérer l'ID utilisateur depuis la session
$user_id = $_SESSION['user_id'];

// Récupérer les infos utilisateur depuis la base
try {
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT id, nom, prenom, login, role, date_derniere_connexion FROM user WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if (!$user) {
        $_SESSION['error_message'] = "Impossible de récupérer vos informations.";
        redirect(BASE_URL . '/dashboard.php');
    }
} catch (Exception $e) {
    error_log('Erreur profil: ' . $e->getMessage());
    $_SESSION['error_message'] = "Erreur lors de la récupération de vos informations.";
    redirect(BASE_URL . '/dashboard.php');
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

$page_title = 'Mon profil';
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
    <h1 class="text-2xl font-bold mb-6 text-primary flex items-center gap-2"><i class="fas fa-user-circle"></i>Mon profil</h1>
    <div class="flex flex-col md:flex-row gap-8 mb-8">
        <div class="flex flex-col items-center md:w-1/3">
            <div class="w-24 h-24 rounded-full bg-primary flex items-center justify-center text-white text-3xl font-bold mb-2">
                <?php echo strtoupper(substr($user['prenom'],0,1).substr($user['nom'],0,1)); ?>
            </div>
            <div class="text-lg font-semibold text-custom mb-1"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></div>
            <div class="text-sm text-gray-500 mb-1"><i class="fas fa-id-badge mr-1"></i><?php echo htmlspecialchars($user['login']); ?></div>
            <div class="text-xs mb-1">
                <?php if ($user['role'] === 'ADMIN'): ?>
                    <span class="uppercase px-2 py-1 rounded bg-red-500 text-white">Administrateur</span>
                <?php else: ?>
                    <span class="uppercase px-2 py-1 rounded bg-blue-400 text-white">Utilisateur</span>
                <?php endif; ?>
            </div>
            <div class="text-xs text-gray-400"><i class="fas fa-clock mr-1"></i>Dernière connexion :
                <?php echo $user['date_derniere_connexion'] ? date('d/m/Y', strtotime($user['date_derniere_connexion'])) : 'Jamais'; ?>
            </div>
        </div>
        <div class="md:w-2/3">
            <div class="space-y-4 bg-gray-50 p-4 rounded">
                <div class="flex gap-4">
                    <div class="w-1/2">
                        <label class="block text-custom">Nom</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['nom']); ?>" disabled class="mt-1 w-full px-3 py-2 border rounded bg-gray-100 text-gray-500 cursor-not-allowed">
                    </div>
                    <div class="w-1/2">
                        <label class="block text-custom">Prénom</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['prenom']); ?>" disabled class="mt-1 w-full px-3 py-2 border rounded bg-gray-100 text-gray-500 cursor-not-allowed">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-10">
        <h2 class="text-xl font-semibold mb-4 text-primary">Changer mon mot de passe</h2>
        <form method="POST" action="<?php echo BASE_URL; ?>/controllers/change_password.php" class="space-y-4 max-w-md">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div>
                <label for="old_password" class="block text-custom">Ancien mot de passe</label>
                <input type="password" id="old_password" name="old_password" required class="mt-1 w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label for="new_password" class="block text-custom">Nouveau mot de passe</label>
                <input type="password" id="new_password" name="new_password" minlength="8" required class="mt-1 w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary">
                <div class="text-xs text-gray-400">Au moins 8 caractères.</div>
            </div>
            <div>
                <label for="confirm_password" class="block text-custom">Confirmer le nouveau mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required class="mt-1 w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 rounded bg-primary text-white hover:bg-secondary transition"><i class="fas fa-key mr-1"></i>Changer le mot de passe</button>
            </div>
        </form>
    </div>
    <div class="mt-8 flex justify-end">
        <a href="<?php echo BASE_URL; ?>/dashboard.php" class="px-4 py-2 rounded bg-primary text-white hover:bg-secondary transition">Retour au tableau de bord</a>
    </div>
</div>
<script>
// Validation côté client du mot de passe
const newPassword = document.getElementById('new_password');
const confirmPassword = document.getElementById('confirm_password');
const pwdForm = document.querySelector('form[action$="change_password.php"]');
if (pwdForm) {
    pwdForm.addEventListener('submit', function(e) {
        if (newPassword.value !== confirmPassword.value) {
            e.preventDefault();
            notifications.error('Erreur', 'Les mots de passe ne correspondent pas.');
        }
    });
}
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
