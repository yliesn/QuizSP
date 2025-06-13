<?php
// Menu de navigation principal, à inclure dans le header ou les pages
// Affiche les liens selon le rôle de l'usager
?>
<ul class="flex flex-col lg:flex-row gap-2">
    <li>
        <a class="px-4 py-2 rounded hover:bg-blue-800 transition" href="<?php echo BASE_URL; ?>/dashboard.php">
            <i class="fas fa-tachometer-alt mr-1"></i> Tableau de bord
        </a>
    </li>
    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN'): ?>
    <li>
        <a class="px-4 py-2 rounded hover:bg-blue-800 transition" href="#">Administration</a>
    </li>
    <?php endif; ?>
</ul>
