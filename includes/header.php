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
    <script src="<?php echo BASE_URL; ?>/assets/js/notifications.js"></script>
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>PharmaStock</title>
</head>
<body>
    <nav class="bg-blue-700 text-white px-4 py-2">
        <div class="container mx-auto flex flex-wrap items-center justify-between">
            <a class="flex items-center gap-2 font-bold text-xl" href="<?php echo BASE_URL; ?>/dashboard.php">
                <i class="fas fa-pills"></i>
                PharmaStock
            </a>
            <button class="block lg:hidden p-2" id="navbar-toggle">
                <span class="fas fa-bars"></span>
            </button>
            <div class="w-full lg:flex lg:items-center lg:w-auto hidden" id="navbarNav">
                <?php include __DIR__ . '/navigation.php'; ?>
                <ul class="flex flex-col lg:flex-row lg:ml-auto gap-2 mt-4 lg:mt-0">
                    <li class="relative group">
                        <a class="flex items-center gap-2 px-4 py-2 rounded hover:bg-blue-800 transition cursor-pointer" href="#">
                            <i class="fas fa-user"></i><?php echo $prenom . ' ' . $nom; ?>
                            <span class="ml-1 fas fa-chevron-down"></span>
                        </a>
                        <ul class="absolute right-0 mt-2 w-40 bg-white text-gray-800 rounded shadow-lg hidden group-hover:block z-50">
                            <li><a class="block px-4 py-2 hover:bg-gray-100" href="#">Mon profil</a></li>
                            <li><hr class="my-1 border-gray-200"></li>
                            <li><a class="block px-4 py-2 hover:bg-gray-100" href="<?php echo BASE_URL; ?>/controllers/logout.php">Déconnexion</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <script>
        // Menu mobile toggle
        document.getElementById('navbar-toggle').addEventListener('click', function() {
            const nav = document.getElementById('navbarNav');
            nav.classList.toggle('hidden');
        });
        // Dropdown menu (simple version)
        document.querySelectorAll('.group').forEach(function(el) {
            el.addEventListener('mouseenter', function() {
                this.querySelector('ul').classList.remove('hidden');
            });
            el.addEventListener('mouseleave', function() {
                this.querySelector('ul').classList.add('hidden');
            });
        });
        const notifications = new NotificationSystem({
            position: 'top-right',
            duration: 5000
        });
    </script>
