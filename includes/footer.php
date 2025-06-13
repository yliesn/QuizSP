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
        <p class="mb-0">&copy; <?php echo date('Y'); ?> PharmaStock</p>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.js"></script>
