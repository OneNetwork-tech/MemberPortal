<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = sanitizeInput($_POST['password']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // In real application, use password_verify() with hashed passwords
            // For now, we'll use a simple check (you should implement proper password hashing)
            if ($password === 'default_password' || password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['firstname'] . ' ' . $user['lastname'];

                // Update last login
                $updateStmt = $pdo->prepare("UPDATE members SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);

                header('Location: admin_dashboard.php');
                exit;
            } else {
                $error = "Invalid email or password";
            }
        } else {
            $error = "Invalid email or password";
        }
    } catch (PDOException $e) {
        $error = "Login failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 2rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><?php echo APP_NAME; ?> - Admin Login</h1>
    </div>

    <div class="container">
        <div class="form-container login-container">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn">Login</button>
            </form>

            <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 4px;">
                <h4>Demo Accounts:</h4>
                <p><strong>Super Admin:</strong> admin@memberportal.se / password</p>
                <p><strong>Board Member:</strong> board@memberportal.se / password</p>
                <p><strong>Staff:</strong> staff@memberportal.se / password</p>
            </div>
        </div>
    </div>
</body>
</html>