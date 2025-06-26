<?php require_once __DIR__ . '/../config/config.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aide - Plateforme Quiz Pompier</title>
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1976d2">
    <style>
        body { background: #f4f6fb; font-family: 'Segoe UI', Arial, sans-serif; }
        .help-container {
            max-width: 700px;
            margin: 3em auto;
            background: #fff;
            border-radius: 1.2em;
            box-shadow: 0 8px 32px rgba(44,62,80,0.13);
            padding: 2.5em 2em 2em 2em;
        }
        h1 { color: #e74c3c; text-align: center; margin-bottom: 1em; }
        h2 { color: #34495e; margin-top: 2em; }
        ul, ol { margin-left: 1.5em; }
        .btn-dashboard {
            display: inline-block;
            margin-top: 2em;
            padding: 0.7em 2.2em;
            background: #e74c3c;
            color: #fff;
            font-weight: 600;
            border-radius: 2em;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(231,76,60,0.08);
            transition: background 0.2s;
        }
        .btn-dashboard:hover { background: #c0392b; }
    </style>
</head>
<body>
<div class="help-container">
    <h1>Aide & FAQ</h1>
    <p>Bienvenue sur la page d'aide de la plateforme de quiz pour pompiers. Retrouvez ici les réponses aux questions fréquentes et des conseils pour utiliser le site.</p>

    <h2>Comment passer un quiz ?</h2>
    <ol>
        <li>Accédez à la liste des quiz depuis le tableau de bord.</li>
        <li>Cliquez sur le quiz de votre choix.</li>
        <li>Répondez à chaque question puis cliquez sur "Suivant".</li>
        <li>À la dernière question, cliquez sur "Voir les résultats" pour obtenir votre score.</li>
        <li>Vous pouvez consulter la correction complète après avoir terminé le quiz.</li>
    </ol>

    <h2>Comment sont calculés les scores ?</h2>
    <ul>
        <li>Chaque bonne réponse rapporte 1 point.</li>
        <li>Le score final correspond au nombre de bonnes réponses sur le total de questions.</li>
        <li>Vous ne pouvez passer chaque quiz qu'une seule fois (hors animateurs JSP/modérateurs).</li>
        <li>Les animateurs JSP peuvent repasser les quiz à volonté.</li>
    </ul>

    <h2>Je rencontre un problème, que faire ?</h2>
    <ul>
        <li>Vérifiez votre connexion internet.</li>
        <li>Essayez de recharger la page.</li>
        <li>Si le problème persiste, contactez un animateur JSP.</li>
    </ul>

    <h2>Gestion du compte utilisateur</h2>
    <ul>
        <li>Pour modifier vos informations personnelles, rendez-vous sur la page "Mon profil".</li>
        <li>Vous pouvez changer votre mot de passe depuis votre profil.</li>
        <li>En cas d’oubli de mot de passe, contactez un animateur JSP.</li>
    </ul>

    <h2>Questions fréquentes</h2>
    <ul>
        <li><strong>Q : Puis-je revenir en arrière pendant un quiz ?</strong><br>A : Non, il n'est pas possible de revenir à une question précédente une fois validée.</li>
        <li><strong>Q : Puis-je voir mes anciens scores ?</strong><br>A : Oui, vos résultats sont affichés si vous avez déjà passé un quiz.</li>
        <li><strong>Q : Comment savoir si ma réponse est correcte ?</strong><br>A : La correction s'affiche après chaque question et à la fin du quiz.</li>
        <li><strong>Q : Que faire si une question comporte une erreur ?</strong><br>A : Signalez-la auprès d’un animateur JSP.</li>
        <li><strong>Q : Les quiz sont-ils chronométrés ?</strong><br>A : Non, il n’y a pas de limite de temps pour répondre aux questions.</li>
        <li><strong>Q : Puis-je créer un quiz ?</strong><br>A : Seuls les animateurs JSP et modérateurs peuvent créer ou modifier des quiz.</li>
        <li><strong>Q : Comment accéder à la liste des quiz ?</strong><br>A : Depuis le tableau de bord, cliquez sur "Liste des quiz".</li>
        <li><strong>Q : Que faire si je suis déconnecté automatiquement ?</strong><br>A : Pour des raisons de sécurité, la session expire après un certain temps d’inactivité. Connectez-vous à nouveau.</li>
        <li><strong>Q : Comment contacter l’équipe support ?</strong><br>A : Adressez-vous à un animateur JSP.</li>
    </ul>

    <h2>Autres questions</h2>
    <ul>
        <li>Pour toute suggestion ou question, utilisez le formulaire de contact disponible sur le tableau de bord.</li>
    </ul>

    <a href="<?php echo BASE_URL; ?>/dashboard.php" class="btn-dashboard">← Retour au tableau de bord</a>
</div>
    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
          navigator.serviceWorker.register('/service-worker.js')
            .then(function(reg) {
              console.log('Service Worker enregistré avec succès:', reg.scope);
            })
            .catch(function(err) {
              console.warn('Erreur lors de l’enregistrement du Service Worker:', err);
            });
        });
      }
    </script>
</body>
</html>
