<?php
// Page de tableau de bord protégée
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/auth/auth.php';
require_login();

$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$page_title = 'Tableau de bord';
include __DIR__ . '/includes/header.php';
?>
<div class="min-h-screen bg-gradient-to-br from-primary to-secondary flex flex-col items-center justify-start py-10">
    <div class="w-full max-w-5xl bg-white/90 rounded-xl shadow-2xl p-10">
        <h1 class="text-3xl font-bold text-primary mb-8 flex items-center gap-2"><i class="fas fa-fire"></i> Tableau de bord</h1>
        <?php if (in_array($user_role, ['ADMIN', 'MODERATEUR'])): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mt-6">
            <a href="<?php echo BASE_URL; ?>/views/quiz/list.php" class="dashboard-card">
                <div class="icon-circle bg-primary"><i class="fas fa-list"></i></div>
                <div class="font-bold text-lg">Liste des quiz</div>
                <div class="text-gray-600 text-sm">Voir, éditer ou créer des quiz</div>
            </a>
            <a href="<?php echo BASE_URL; ?>/views/quiz/results.php" class="dashboard-card">
                <div class="icon-circle bg-secondary"><i class="fas fa-poll"></i></div>
                <div class="font-bold text-lg">Résultats quiz</div>
                <div class="text-gray-600 text-sm">Consulter les scores et statistiques</div>
            </a>
            <a href="<?php echo BASE_URL; ?>/views/users/list.php" class="dashboard-card">
                <div class="icon-circle bg-green-600"><i class="fas fa-users"></i></div>
                <div class="font-bold text-lg">Gestion des utilisateurs</div>
                <div class="text-gray-600 text-sm">Ajouter, modifier ou désactiver des comptes</div>
            </a>
            <a href="<?php echo BASE_URL; ?>/views/quiz/create.php" class="dashboard-card">
                <div class="icon-circle bg-yellow-500"><i class="fas fa-plus"></i></div>
                <div class="font-bold text-lg">Créer un quiz</div>
                <div class="text-gray-600 text-sm">Nouveau quiz en quelques clics</div>
            </a>
            <a href="<?php echo BASE_URL; ?>/views/users/create.php" class="dashboard-card">
                <div class="icon-circle bg-indigo-500"><i class="fas fa-user-plus"></i></div>
                <div class="font-bold text-lg">Créer un utilisateur</div>
                <div class="text-gray-600 text-sm">Ajout rapide d'un nouveau compte</div>
            </a>
            <a href="<?php echo BASE_URL; ?>/views/aide.php" class="dashboard-card">
                <div class="icon-circle bg-gray-700"><i class="fas fa-question-circle"></i></div>
                <div class="font-bold text-lg">Aide & Support</div>
                <div class="text-gray-600 text-sm">Documentation, contact, FAQ</div>
            </a>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-6">
            <a href="<?php echo BASE_URL; ?>/views/quiz/list.php" class="dashboard-card">
                <div class="icon-circle bg-primary"><i class="fas fa-list"></i></div>
                <div class="font-bold text-lg">Mes quiz</div>
                <div class="text-gray-600 text-sm">Accéder aux quiz disponibles</div>
            </a>
            <a href="<?php echo BASE_URL; ?>/views/quiz/results.php" class="dashboard-card">
                <div class="icon-circle bg-secondary"><i class="fas fa-poll"></i></div>
                <div class="font-bold text-lg">Mes résultats</div>
                <div class="text-gray-600 text-sm">Voir mes scores et mon historique</div>
            </a>
            <a href="<?php echo BASE_URL; ?>/views/users/profile.php" class="dashboard-card">
                <div class="icon-circle bg-green-600"><i class="fas fa-user"></i></div>
                <div class="font-bold text-lg">Mon profil</div>
                <div class="text-gray-600 text-sm">Gérer mes informations personnelles</div>
            </a>
            <a href="<?php echo BASE_URL; ?>/views/aide.php" class="dashboard-card">
                <div class="icon-circle bg-gray-700"><i class="fas fa-question-circle"></i></div>
                <div class="font-bold text-lg">Aide & Support</div>
                <div class="text-gray-600 text-sm">Documentation, contact, FAQ</div>
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>
<style>
.dashboard-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 2px 8px 0 #0001;
    padding: 2rem 1rem 1.5rem 1rem;
    text-decoration: none;
    transition: box-shadow 0.2s, transform 0.2s;
    border: 2px solid transparent;
}
.dashboard-card:hover {
    box-shadow: 0 6px 24px 0 #0002;
    transform: translateY(-4px) scale(1.03);
    border-color: #2563eb;
}
.icon-circle {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 2rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 8px 0 #0001;
}
</style>
<?php include __DIR__ . '/includes/footer.php'; ?>
