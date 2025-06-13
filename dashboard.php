<?php
// Page de tableau de bord protégée
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/auth/auth.php';
require_login();

$page_title = 'Tableau de bord';
include __DIR__ . '/includes/header.php';
?>
<div class="container mx-auto mt-10 p-8 bg-white rounded-lg shadow-lg max-w-2xl">
    <h1 class="text-2xl font-bold mb-4 text-primary">Bienvenue sur le tableau de bord</h1>
    <p class="text-custom">Vous êtes connecté.</p>
</div>
<?php
include __DIR__ . '/includes/footer.php';
