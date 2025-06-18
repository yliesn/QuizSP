<?php
// Menu de navigation principal, à inclure dans le header ou les pages
// Affiche les liens selon le rôle de l'usager
?>
<ul class="flex flex-col lg:flex-row gap-2 ml-auto">
    <li class="relative dropdown-parent" id="user-menu-parent">
        <button id="user-menu-btn" class="flex items-center gap-2 px-4 py-2 rounded hover:bg-secondary transition cursor-pointer w-full text-left dropdown-toggle">
            <i class="fas fa-user"></i><?php echo $prenom . ' ' . $nom; ?>
            <span class="ml-1 fas fa-chevron-down"></span>
        </button>
        <ul id="user-menu-dropdown" class="absolute right-0 mt-2 w-40 bg-white text-gray-800 rounded shadow-lg hidden z-50 dropdown-menu">
            <li><a class="block px-4 py-2 hover:bg-gray-100" href="<?php echo BASE_URL; ?>/views/users/profile.php">Mon profil</a></li>
            <li><hr class="my-1 border-gray-200"></li>
            <li><a class="block px-4 py-2 hover:bg-gray-100" href="<?php echo BASE_URL; ?>/controllers/logout.php">Déconnexion</a></li>
        </ul>
    </li>
</ul>
<script>
// Menu déroulant générique au clic
const dropdownParents = document.querySelectorAll('.dropdown-parent');
dropdownParents.forEach(function(parent) {
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
</script>
