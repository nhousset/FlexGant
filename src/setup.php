<?php
session_start();
$adminFile = 'db/admin.json';

// Si le fichier existe déjà, on redirige vers le login
if (file_exists($adminFile)) {
    header('Location: login.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if (strlen($password) >= 6) {
        if (!is_dir('db')) {
            mkdir('db', 0755, true);
        }
        
        $config = [
            'username' => 'admin',
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ];
        
        file_put_contents($adminFile, json_encode($config, JSON_PRETTY_PRINT), LOCK_EX);
        header('Location: login.php?setup_success=1');
        exit;
    } else {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration Initiale - Gantt Engine</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); width: 100%; max-width: 400px; box-sizing: border-box; }
        h2 { margin-top: 0; color: #1e293b; font-size: 24px; text-align: center; }
        p { color: #64748b; font-size: 14px; margin-bottom: 24px; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #475569; font-weight: 500; font-size: 14px; }
        input[type="password"] { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 16px; }
        input[type="password"]:focus { outline: 2px solid #3b82f6; border-color: transparent; }
        button { width: 100%; padding: 12px; background: #2563eb; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        button:hover { background: #1d4ed8; }
        .error { color: #dc2626; background: #fee2e2; padding: 10px; border-radius: 6px; font-size: 14px; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Première Utilisation</h2>
        <p>Création du compte administrateur. Définissez votre mot de passe pour sécuriser vos diagrammes.</p>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="password">Mot de passe Administrateur</label>
                <input type="password" id="password" name="password" required autofocus autocomplete="new-password">
            </div>
            <button type="submit">Générer l'environnement</button>
        </form>
    </div>
</body>
</html>
