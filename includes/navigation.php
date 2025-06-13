<?php
// Menu de navigation principal, à inclure dans le header ou les pages
// Affiche les liens selon le rôle de l'usager
?>
<ul class="flex flex-col lg:flex-row gap-2">
    <li>
        <a class="px-4 py-2 rounded hover:bg-secondary transition" href="<?php echo BASE_URL; ?>/dashboard.php">
            <i class="fas fa-tachometer-alt mr-1"></i> Tableau de bord
        </a>
    </li>
    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN'): ?>
    <li class="relative group">
        <button class="px-4 py-2 rounded hover:bg-secondary transition flex items-center gap-2" type="button">
            <i class="fas fa-cog mr-1"></i> Administration
            <span class="fas fa-chevron-down"></span>
        </button>
        <ul class="absolute left-0 mt-2 w-48 bg-white text-gray-800 rounded shadow-lg hidden group-hover:block z-50">
            <li><a class="block px-4 py-2 hover:bg-gray-100" href="<?php echo BASE_URL; ?>/views/users/list.php">Utilisateurs</a></li>
            <li><a class="block px-4 py-2 hover:bg-gray-100" href="<?php echo BASE_URL; ?>/views/users/add.php">Ajouter un utilisateur</a></li>
        </ul>
    </li>
    <?php endif; ?>
</ul>
<script>
// Affichage du sous-menu Administration au survol
if (document.querySelectorAll('.group').length) {
  document.querySelectorAll('.group').forEach(function(el) {
    el.addEventListener('mouseenter', function() {
      this.querySelector('ul').classList.remove('hidden');
    });
    el.addEventListener('mouseleave', function() {
      this.querySelector('ul').classList.add('hidden');
    });
  });
}
</script>
