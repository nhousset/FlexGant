<?php
session_start();
$adminFile = 'db/admin.json';

// Si l'environnement n'est pas initialisé, redirection forcée vers le setup
if (!file_exists($adminFile)) {
    header('Location: setup.php');
    exit;
}

// Si déjà connecté, direction le tableau de bord
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    $config = json_decode(file_get_contents($adminFile), true);
    
    if ($config && password_verify($password, $config['password'])) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Mot de passe administrateur non valide.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gantt Engine</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); width: 100%; max-width: 400px; box-sizing: border-box; }
        h2 { margin-top: 0; color: #1e293b; font-size: 24px; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #475569; font-weight: 500; font-size: 14px; }
        input[type="password"] { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 16px; }
        button { width: 100%; padding: 12px; background: #1e293b; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer; }
        button:hover { background: #0f172a; }
        .error { color: #dc2626; background: #fee2e2; padding: 10px; border-radius: 6px; font-size: 14px; margin-bottom: 20px; text-align: center; }
        .success { color: #16a34a; background: #dcfce7; padding: 10px; border-radius: 6px; font-size: 14px; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Accès Administration</h2>
        
        <?php if (isset($_GET['setup_success'])): ?>
            <div class="success">Environnement créé avec succès ! Connectez-vous.</div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="password">Mot de passe de sécurité</label>
                <input type="password" id="password" name="password" required autofocus>
            </div>
            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>
</html>
