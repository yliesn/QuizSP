<?php
/**
 * Pied de page commun de l'application
 * Contient les scripts JS communs et le copyright
 */
// Vérification de sécurité pour éviter l'accès direct au fichier
if (!defined('ROOT_PATH')) {
    header("Location: /");
    exit;
}
?>
<footer class="bg-custom py-6 mt-10">
    <div class="container mx-auto text-center text-custom">
        <p class="mb-0">&copy; <?php echo date('Y'); ?> QuizSP</p>
        <p class="text-xs mt-2">Designé & développé par Ylies Nejara</p>
    </div>
</footer>
<!-- Ajout du système de notifications JS dans le footer pour garantir son chargement sur toutes les pages -->
<script src="<?php echo BASE_URL; ?>/assets/js/notifications.js"></script>
<script>
    if (typeof notifications === 'undefined' && typeof NotificationSystem !== 'undefined') {
        window.notifications = new NotificationSystem({ position: 'top-right', duration: 5000 });
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.js"></script>
