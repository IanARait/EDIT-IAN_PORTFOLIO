<?php
/**
 * Portfolio CMS - Setup Script
 * Run this once to initialize the database and create tables.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Portfolio CMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg: #090909; --surface: #111; --border: #222; --primary: #00E676; --text: #fff; --secondary: #777; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .setup-card { background: var(--surface); border: 1px solid var(--border); border-radius: 20px; padding: 48px; max-width: 600px; width: 100%; }
        h1 { font-size: 28px; margin-bottom: 8px; }
        h1 span { color: var(--primary); }
        .subtitle { color: var(--secondary); margin-bottom: 32px; }
        .step { margin-bottom: 24px; padding: 20px; background: var(--bg); border: 1px solid var(--border); border-radius: 12px; }
        .step h3 { font-size: 16px; margin-bottom: 8px; display: flex; align-items: center; gap: 10px; }
        .step p { font-size: 14px; color: var(--secondary); line-height: 1.6; }
        .success { color: var(--primary); }
        .error { color: #ff4444; }
        .info { color: #4488ff; }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 50px; font-size: 11px; font-weight: 600; }
        .badge-success { background: rgba(0,230,118,0.1); color: var(--primary); }
        .badge-error { background: rgba(255,68,68,0.1); color: #ff4444; }
        .badge-info { background: rgba(68,136,255,0.1); color: #4488ff; }
        .credentials { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 20px; margin-top: 16px; }
        .credentials p { font-size: 14px; color: var(--secondary); margin-bottom: 4px; }
        .credentials strong { color: var(--primary); }
        .btn { display: inline-block; padding: 12px 24px; border-radius: 12px; font-size: 14px; font-weight: 600; text-decoration: none; transition: all 0.3s; border: none; cursor: pointer; font-family: 'Inter', sans-serif; margin-top: 16px; }
        .btn-primary { background: var(--primary); color: #000; }
        .btn-primary:hover { background: #00cc66; }
        .btn-secondary { background: var(--surface); color: var(--text); border: 1px solid var(--border); }
        a.btn-secondary:hover { border-color: var(--primary); color: var(--primary); }
    </style>
</head>
<body>
    <div class="setup-card">
        <h1><span>&lt;</span>Portfolio<span>/&gt;</span> Setup</h1>
        <p class="subtitle">Database initialization and setup wizard</p>
        
        <?php
        $host = 'localhost';
        $user = 'root';
        $pass = '';
        $dbName = 'portfolio_db';
        $errors = [];
        $success = true;

        // Step 1: Create database
        echo '<div class="step"><h3>1. Creating Database <span class="badge badge-info">MySQL</span></h3>';
        try {
            $pdo = new PDO("mysql:host={$host}", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$dbName}`");
            echo '<p class="success">Database created/verified successfully.</p>';
        } catch (PDOException $e) {
            $errors[] = "Database connection failed: " . $e->getMessage();
            $success = false;
            echo '<p class="error">Failed: ' . $e->getMessage() . '</p>';
        }
        echo '</div>';

        // Step 2: Import schema
        if ($success) {
            echo '<div class="step"><h3>2. Importing Schema <span class="badge badge-info">Tables</span></h3>';
            $schemaFile = __DIR__ . '/database/schema.sql';
            if (file_exists($schemaFile)) {
                $sql = file_get_contents($schemaFile);
                // Remove the CREATE DATABASE and USE statements (already done)
                $sql = preg_replace('/CREATE DATABASE.*?;/', '', $sql);
                $sql = preg_replace('/USE\s+`portfolio_db`;/', '', $sql);
                
                // Split by semicolons but be careful with triggers
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                
                $imported = 0;
                $skipped = 0;
                foreach ($statements as $statement) {
                    if (empty($statement) || $statement === '--') continue;
                    try {
                        $pdo->exec($statement);
                        $imported++;
                    } catch (PDOException $e) {
                        // Skip duplicate table errors
                        if (str_contains($e->getMessage(), 'already exists')) {
                            $skipped++;
                            continue;
                        }
                        $errors[] = "Import error: " . $e->getMessage();
                    }
                }
                echo '<p class="success">Schema imported. ' . $imported . ' statements executed, ' . $skipped . ' skipped.</p>';
            } else {
                echo '<p class="error">Schema file not found!</p>';
                $success = false;
            }
            echo '</div>';
        }

        // Step 3: Verify tables
        if ($success) {
            echo '<div class="step"><h3>3. Verifying Tables <span class="badge badge-info">Check</span></h3>';
            try {
                $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                $expected = ['admins', 'categories', 'projects', 'services', 'testimonials', 'messages', 'settings'];
                $missing = array_diff($expected, $tables);
                
                if (empty($missing)) {
                    echo '<p class="success">All ' . count($expected) . ' tables verified.</p>';
                } else {
                    echo '<p class="error">Missing tables: ' . implode(', ', $missing) . '</p>';
                    $success = false;
                }
                
                // Count records
                foreach ($expected as $table) {
                    $count = $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
                    echo '<p style="font-size:13px; color:var(--secondary);">  ' . $table . ': ' . $count . ' records</p>';
                }
            } catch (PDOException $e) {
                echo '<p class="error">Verification failed: ' . $e->getMessage() . '</p>';
            }
            echo '</div>';
        }

        // Step 3b: Ensure admin password is correct
        if ($success) {
            echo '<div class="step"><h3>4. Resetting Admin Password <span class="badge badge-info">Auth</span></h3>';
            try {
                $hash = password_hash('Admin@123', PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE email = ?");
                $stmt->execute([$hash, 'admin@portfolio.com']);
                echo '<p class="success">Admin password reset to: <strong>Admin@123</strong></p>';
            } catch (PDOException $e) {
                echo '<p class="error">Password reset failed: ' . $e->getMessage() . '</p>';
            }
            echo '</div>';
        }

        // Step 5: Create upload directories
        echo '<div class="step"><h3>5. Upload Directories <span class="badge badge-info">Filesystem</span></h3>';
        $dirs = ['uploads', 'uploads/thumbnails', 'uploads/profiles', 'uploads/logos'];
        foreach ($dirs as $dir) {
            $path = __DIR__ . '/' . $dir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
                echo '<p style="font-size:13px; color:var(--secondary);">  Created: ' . $dir . '/</p>';
            } else {
                echo '<p style="font-size:13px; color:var(--secondary);">  Exists: ' . $dir . '/</p>';
            }
        }
        echo '<p class="success">Upload directories ready.</p>';
        echo '</div>';

        // Step 6: Summary
        echo '<div class="step"><h3>6. Setup Complete <span class="badge badge-success">Done</span></h3>';
        if ($success) {
            echo '<p class="success">Your portfolio CMS is ready!</p>';
            echo '<div class="credentials">';
            echo '<p><strong>Admin Login:</strong></p>';
            echo '<p>Email: <strong>admin@portfolio.com</strong></p>';
            echo '<p>Password: <strong>Admin@123</strong></p>';
            echo '<p>Admin URL: <strong><a href="/my-portfolio/admin/" style="color:var(--primary);">/my-portfolio/admin/</a></strong></p>';
            echo '<p>Site URL: <strong><a href="/my-portfolio/public/" style="color:var(--primary);">/my-portfolio/public/</a></strong></p>';
            echo '</div>';
            echo '<div style="margin-top:20px;">';
            echo '<a href="/my-portfolio/admin/login.php" class="btn btn-primary">Go to Admin Panel</a> ';
            echo '<a href="/my-portfolio/public/" class="btn btn-secondary">View Portfolio</a>';
            echo '</div>';
        } else {
            echo '<p class="error">Setup encountered errors. Please check the details above.</p>';
            echo '<button onclick="location.reload()" class="btn btn-primary">Retry Setup</button>';
        }
        echo '</div>';
        ?>
    </div>
</body>
</html>
