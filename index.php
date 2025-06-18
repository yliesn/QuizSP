<?php
/**
 * Page de connexion à l'application
 * Affiche le formulaire de connexion et gère les redirections et messages d'erreur
 */

// Inclure le fichier de configuration
require_once 'config/config.php';
require_once __DIR__ . '/auth/auth.php';

// Générer un token CSRF pour la sécurité du formulaire
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Rediriger vers le dashboard
    header("Location: dashboard.php");
    exit();
}

// Message d'erreur pour affichage
$error_message = "";
if (isset($_GET['expired']) && $_GET['expired'] == 1) {
    $error_message = "Votre session a expiré. Veuillez vous reconnecter.";
}
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Effacer le message après utilisation
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Suppression du lien CSS Tailwind incorrect -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css" rel="stylesheet"> -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Correction du chemin du CSS personnalisé en relatif pour éviter le mixed content -->
    <link href="<?php echo BASE_URL; ?>/assets/css/custom.css" rel="stylesheet">
    <title>Connexion - Gestion de Stock Pharmacie</title>
</head>
<body class="bg-custom min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md p-8 bg-white rounded-lg shadow-lg">
        <div class="flex flex-col items-center mb-6">
            <img src="assets/img/logo.png" alt="Logo Pharmacie" class="w-32 mb-2">
            <h2 class="text-2xl font-bold mb-2">Connexion</h2>
        </div>
        <form method="POST" action="controllers/login.php" class="space-y-4">
            <!-- Token CSRF caché -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <!-- Champ Identifiant -->
            <div>
                <label for="username" class="block text-custom">Identifiant</label>
                <input type="text" id="username" name="username" required class="mt-1 w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            
            <!-- Champ Mot de passe -->
            <div>
                <label for="password" class="block text-custom">Mot de passe</label>
                <input type="password" id="password" name="password" required class="mt-1 w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            
            <!-- Bouton de connexion -->
            <button type="submit" class="w-full py-2 px-4 bg-primary text-white rounded hover:bg-secondary transition">Se connecter</button>
        </form>
        <?php if (!empty($error_message)): ?>
            <div class="mt-4 p-2 bg-red-100 text-red-700 rounded text-center">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
