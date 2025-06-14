<?php
/**
 * En-tête commun de l'application
 * Contient la barre de navigation principale
 */

if (!defined('ROOT_PATH')) {
    header("Location: /");
    exit;
}

require_once __DIR__ . '/../auth/auth.php';

$nom = isset($_SESSION['user_nom']) ? htmlspecialchars($_SESSION['user_nom']) : '';
$prenom = isset($_SESSION['user_prenom']) ? htmlspecialchars($_SESSION['user_prenom']) : '';
$role = isset($_SESSION['user_role']) ? htmlspecialchars($_SESSION['user_role']) : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="<?php echo BASE_URL; ?>/assets/css/custom.css" rel="stylesheet">
    <script src="<?php echo BASE_URL; ?>/assets/js/notifications.js"></script>
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>JSP</title>
</head>
<body>
    <nav class="bg-primary text-white px-4 py-2">
        <div class="container mx-auto flex items-center">
            <a class="flex items-center gap-2 font-bold text-xl mr-4" href="<?php echo BASE_URL; ?>/dashboard.php">
                <i class="fas fa-pills"></i>
                JSP
            </a>
            <?php include __DIR__ . '/navigation.php'; ?>
        </div>
    </nav>
    <script>
        // Menu mobile toggle
        document.getElementById('navbar-toggle').addEventListener('click', function() {
            const nav = document.getElementById('navbarNav');
            nav.classList.toggle('hidden');
        });
        // Gestion générique des dropdowns (admin + utilisateur)
        document.querySelectorAll('.dropdown-parent').forEach(function(parent) {
          const toggle = parent.querySelector('.dropdown-toggle');
          const menu = parent.querySelector('.dropdown-menu');
          if (toggle && menu) {
            toggle.addEventListener('click', function(e) {
              e.stopPropagation();
              menu.classList.toggle('hidden');
            });
            document.addEventListener('click', function(e) {
              if (!parent.contains(e.target)) {
                menu.classList.add('hidden');
              }
            });
          }
        });
        const notifications = new NotificationSystem({
            position: 'top-right',
            duration: 5000
        });
    </script>
