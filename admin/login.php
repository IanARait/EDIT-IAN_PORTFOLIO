<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/classes/Database.php';
require_once dirname(__DIR__) . '/classes/Auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(ADMIN_URL . '/');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $auth = new Auth();
        if ($auth->login($email, $password)) {
            setFlash('success', 'Welcome back, ' . $_SESSION['admin_name'] . '!');
            redirect(ADMIN_URL . '/');
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Portfolio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #090909;
            --bg-surface: #111111;
            --bg-elevated: #1a1a1a;
            --border: #222222;
            --primary: #00E676;
            --primary-dim: rgba(0,230,118,0.1);
            --text: #FFFFFF;
            --text-secondary: #777777;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-primary);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .login-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
        }
        .login-bg::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 40%, rgba(0,230,118,0.05) 0%, transparent 50%),
                        radial-gradient(circle at 70% 60%, rgba(0,230,118,0.03) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-30px, -30px); }
        }
        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        .login-card {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 48px 40px;
            backdrop-filter: blur(20px);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-logo a {
            font-family: 'Poppins', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--text);
            text-decoration: none;
        }
        .login-logo .highlight { color: var(--primary); }
        .login-title {
            font-family: 'Poppins', sans-serif;
            font-size: 22px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 8px;
        }
        .login-subtitle {
            text-align: center;
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 32px;
        }
        .form-floating {
            margin-bottom: 16px;
        }
        .form-floating .form-control {
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text);
            font-size: 15px;
            padding: 16px 16px;
            height: 56px;
            transition: border-color 0.3s;
        }
        .form-floating .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0,230,118,0.1);
            outline: none;
        }
        .form-floating label {
            color: var(--text-secondary);
            font-size: 14px;
        }
        .form-check {
            margin-bottom: 24px;
        }
        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        .form-check-label {
            font-size: 13px;
            color: var(--text-secondary);
        }
        .forgot-link {
            font-size: 13px;
            color: var(--primary);
            text-decoration: none;
        }
        .forgot-link:hover { text-decoration: underline; }
        .login-btn {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: #000;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
        }
        .login-btn:hover {
            background: #00cc66;
            box-shadow: 0 4px 20px rgba(0,230,118,0.3);
            transform: translateY(-1px);
        }
        .login-btn:active {
            transform: translateY(0);
        }
        .error-alert {
            background: rgba(255,68,68,0.1);
            border: 1px solid rgba(255,68,68,0.2);
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #ff6b6b;
            font-size: 14px;
        }
        .back-link {
            text-align: center;
            margin-top: 24px;
        }
        .back-link a {
            color: var(--text-secondary);
            font-size: 13px;
            text-decoration: none;
        }
        .back-link a:hover { color: var(--primary); }
    </style>
</head>
<body>
    <div class="login-bg"></div>
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <a href="<?= ADMIN_URL ?>/">
                    <span class="highlight">&lt;</span>Admin<span class="highlight">/&gt;</span>
                </a>
            </div>
            <h1 class="login-title">Welcome Back</h1>
            <p class="login-subtitle">Sign in to manage your portfolio</p>
            
            <?php if ($error): ?>
            <div class="error-alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                <?= sanitize($error) ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required value="<?= sanitize($_POST['email'] ?? '') ?>">
                    <label for="email">Email Address</label>
                </div>
                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">Password</label>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>
                <button type="submit" class="login-btn">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </button>
            </form>
            
            <div class="back-link">
                <a href="<?= BASE_URL ?>/public/">
                    <i class="bi bi-arrow-left"></i> Back to Portfolio
                </a>
            </div>
        </div>
    </div>
</body>
</html>
