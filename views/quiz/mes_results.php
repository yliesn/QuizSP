<?php require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../auth/auth.php';
require_login();

$user_id = $_SESSION['user_id'];
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';

// Seuls les utilisateurs non animateurs/modérateurs peuvent accéder à cette page
if (in_array($user_role, ['ADMIN', 'MODERATEUR', 'animateur JSP'])) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$pdo = getDbConnection();
$stmt = $pdo->prepare('SELECT q.titre, r.score, r.date_passage, q.id as quiz_id, (SELECT COUNT(*) FROM question WHERE quizz_id = q.id) as total_questions
    FROM resultat_quiz r
    JOIN quizz q ON r.quizz_id = q.id
    WHERE r.user_id = ?
    ORDER BY r.date_passage DESC');
$stmt->execute([$user_id]);
$results = $stmt->fetchAll();
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes résultats de quiz</title>
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1976d2">
    <style>
        body { background: #f4f6fb; font-family: 'Segoe UI', Arial, sans-serif; }
        .results-container {
            max-width: 700px;
            margin: 3em auto;
            background: #fff;
            border-radius: 1.2em;
            box-shadow: 0 8px 32px rgba(44,62,80,0.13);
            padding: 2.5em 2em 2em 2em;
        }
        h1 { color: #e74c3c; text-align: center; margin-bottom: 1em; }
        table { width: 100%; border-collapse: collapse; margin-top: 2em; }
        th, td { padding: 0.8em 1em; text-align: left; }
        th { background: #ecf0f1; color: #34495e; }
        tr:nth-child(even) { background: #f8fafd; }
        tr:hover { background: #f1cfcf; }
        .score-badge { background: #e74c3c; color: #fff; border-radius: 1em; padding: 0.2em 1em; font-weight: bold; }
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
<div class="results-container">
    <h1>Mes résultats de quiz</h1>
    <?php if (empty($results)): ?>
        <p style="text-align:center;color:#888;">Vous n'avez encore passé aucun quiz.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Quiz</th>
                <th>Score</th>
                <th>Date de passage</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($results as $row): ?>
            <tr>
                <td><a href="<?php echo BASE_URL; ?>/views/quiz/view.php?id=<?php echo (int)$row['quiz_id']; ?>" style="color:#2980b9;text-decoration:underline;">
                    <?php echo htmlspecialchars($row['titre']); ?>
                </a></td>
                <td><span class="score-badge"><?php echo (int)$row['score'] . ' / ' . (int)$row['total_questions']; ?></span></td>
                <td><?php echo date('d/m/Y à H:i', strtotime($row['date_passage'])); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
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
