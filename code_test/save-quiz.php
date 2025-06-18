<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

try {
    // Récupérer les données JSON envoyées
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['filename']) || !isset($input['content'])) {
        throw new Exception('Données manquantes');
    }
    
    $filename = $input['filename'];
    $content = $input['content'];
    
    // Utiliser le dossier temporaire du système si uploads/ ne fonctionne pas
    $uploadDir = 'uploads/quiz/';
    
    // Vérifier si le dossier existe et est accessible en écriture
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            // Si impossible de créer uploads/, utiliser le dossier courant
            $uploadDir = './';
        }
    }
    
    // Vérifier les permissions d'écriture
    if (!is_writable($uploadDir)) {
        // Tenter de changer les permissions
        chmod($uploadDir, 0777);
        
        // Si toujours pas accessible, utiliser le dossier courant
        if (!is_writable($uploadDir)) {
            $uploadDir = './';
        }
    }
    
    // Sécuriser le nom de fichier
    $filename = basename($filename);
    $filepath = $uploadDir . $filename;
    
    // Sauvegarder le fichier
    $bytesWritten = file_put_contents($filepath, $content);
    
    if ($bytesWritten !== false) {
        echo json_encode([
            'success' => true,
            'message' => 'Quiz sauvegardé avec succès',
            'filename' => $filename,
            'path' => $filepath,
            'size' => $bytesWritten . ' bytes'
        ]);
    } else {
        throw new Exception('Impossible d\'écrire dans le fichier. Vérifiez les permissions du dossier.');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug_info' => [
            'current_dir' => getcwd(),
            'upload_dir_exists' => file_exists('uploads/quiz/'),
            'upload_dir_writable' => is_writable('uploads/quiz/'),
            'current_dir_writable' => is_writable('./')
        ]
    ]);
}
?>