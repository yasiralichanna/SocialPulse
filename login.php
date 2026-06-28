<?php
/**
 * Login Page — SocialPulse
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// Already logged in? Go to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (authenticate($email, $password)) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialPulse — Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: var(--bg-primary); }
        .login-container { width: 100%; max-width: 420px; padding: 2rem; }
        .login-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); padding: 2.5rem; backdrop-filter: blur(20px); }
        .login-brand { text-align: center; margin-bottom: 2rem; }
        .login-brand .brand-icon { width: 60px; height: 60px; margin: 0 auto 1rem; font-size: 1.6rem; }
        .login-brand h1 { font-size: 1.8rem; font-weight: 800; letter-spacing: -0.5px; }
        .login-brand p { color: var(--text-secondary); margin-top: 0.5rem; font-size: 0.9rem; }
        .login-error { background: rgba(255,107,107,0.1); border: 1px solid rgba(255,107,107,0.2); color: var(--danger); padding: 0.8rem 1rem; border-radius: var(--radius-sm); margin-bottom: 1rem; font-size: 0.85rem; }
        .login-form .form-group { margin-bottom: 1.2rem; }
        .login-form .input-styled { padding: 0.8rem 1rem; }
        .login-form .btn { width: 100%; padding: 0.8rem; font-size: 1rem; margin-top: 0.5rem; }
        .login-footer { text-align: center; margin-top: 1.5rem; font-size: 0.8rem; color: var(--text-muted); }
        .login-footer code { background: var(--surface); padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-brand">
                <div class="brand-icon"><i class="fas fa-bolt"></i></div>
                <h1>SocialPulse</h1>
                <p>Sign in to manage your social media</p>
            </div>

            <?php if ($error): ?>
                <div class="login-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="input-styled" placeholder="admin@socialpulse.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="input-styled" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Sign In</button>
            </form>

            <div class="login-footer">
                Demo credentials: <code>admin@socialpulse.com</code> / <code>admin123</code>
            </div>
        </div>
    </div>
</body>
</html>
