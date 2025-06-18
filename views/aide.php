<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aide - Plateforme Quiz Pompier</title>
    <link rel="stylesheet" href="/assets/css/custom.css">
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
    </ol>

    <h2>Comment sont calculés les scores ?</h2>
    <ul>
        <li>Chaque bonne réponse rapporte 1 point.</li>
        <li>Le score final correspond au nombre de bonnes réponses sur le total de questions.</li>
        <li>Vous ne pouvez passer chaque quiz qu'une seule fois (hors administrateurs/modérateurs).</li>
    </ul>

    <h2>Je rencontre un problème, que faire ?</h2>
    <ul>
        <li>Vérifiez votre connexion internet.</li>
        <li>Essayez de recharger la page.</li>
        <li>Si le problème persiste, contactez un administrateur ou envoyez un mail à l'équipe support.</li>
    </ul>

    <h2>Autres questions</h2>
    <ul>
        <li>Pour toute suggestion ou question, utilisez le formulaire de contact disponible sur le tableau de bord.</li>
    </ul>

    <a href=" <?php echo BASE_URL; ?>/dashboard.php" class="btn-dashboard">← Retour au tableau de bord</a>
</div>
</body>
</html>
