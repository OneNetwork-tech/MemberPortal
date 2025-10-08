<?php
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = sanitizeInput($_POST['password']);
    $remember = isset($_POST['remember']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Check if account is active
            if (!$user['is_active']) {
                $error = "Your account has been suspended. Please contact administration.";
            } else {
                // Verify password (using the temporary password for demo)
                // In production, you should use: password_verify($password, $user['password'])
                $validPassword = true; // For demo purposes
                
                if ($validPassword) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_name'] = $user['firstname'] . ' ' . $user['lastname'];
                    $_SESSION['login_time'] = time();

                    // Update last login
                    $updateStmt = $pdo->prepare("UPDATE members SET last_login = NOW() WHERE id = ?");
                    $updateStmt->execute([$user['id']]);

                    // Log the login activity
                    logActivity($pdo, $user['id'], 'user_login', 'User logged in successfully');

                    // Redirect based on role
                    if (hasPermission($user['role'], 'view_admin_dashboard')) {
                        header('Location: admin_dashboard.php');
                    } else {
                        header('Location: member_dashboard.php');
                    }
                    exit;
                } else {
                    $error = "Invalid email or password";
                    logActivity($pdo, $user['id'], 'login_failed', 'Failed login attempt');
                }
            }
        } else {
            $error = "Invalid email or password";
        }
    } catch (PDOException $e) {
        $error = "Login failed. Please try again.";
        error_log("Login error: " . $e->getMessage());
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
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<!-- Navigation -->
<nav class="navbar">
    <div class="nav-container">
        <div class="nav-logo">
            <i class="fas fa-users"></i>
            <span><?php echo t('app_name'); ?></span>
        </div>
        <div class="nav-links">
            <a href="index.php" class="nav-link"><?php echo t('home'); ?></a>
            <a href="index.php#features" class="nav-link"><?php echo t('features'); ?></a>
            <a href="index.php#about" class="nav-link"><?php echo t('about'); ?></a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="admin_dashboard.php" class="nav-link"><?php echo t('dashboard'); ?></a>
                <?php if (hasPermission($_SESSION['user_role'], 'view_members')): ?>
                    <a href="members_list.php" class="nav-link"><?php echo t('members'); ?></a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <div class="nav-actions">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="btn btn-outline"><?php echo t('login'); ?></a>
                <a href="register.php" class="btn btn-primary"><?php echo t('register'); ?></a>
            <?php else: ?>
                <div class="user-menu">
                    <span class="user-greeting"><?php echo t('welcome'); ?>, <?php echo $_SESSION['user_name']; ?></span>
                    <div class="user-dropdown">
                        <div class="user-info">
                            <strong><?php echo $_SESSION['user_name']; ?></strong>
                            <span><?php echo t($_SESSION['user_role']); ?></span>
                        </div>
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i> <?php echo t('profile'); ?>
                        </a>
                        <a href="logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i> <?php echo t('logout'); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Language Switcher -->
            <?php include 'includes/language_switcher.php'; ?>
        </div>
        <div class="nav-toggle">
            <i class="fas fa-bars"></i>
        </div>
    </div>
</nav>

    <div class="auth-container" style="margin-top: 70px;">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-sign-in-alt"></i>
                    <h1>Welcome Back</h1>
                </div>
                <p>Sign in to your membership account</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="alert-content">
                        <h3>Login Failed</h3>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($email); ?>" 
                               required
                               placeholder="your.email@example.com">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" 
                               required
                               placeholder="Enter your password">
                        <button type="button" class="password-toggle" id="passwordToggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <div class="checkbox-group">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="forgot-password.php" class="forgot-password">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-large btn-full">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>

                <div class="auth-divider">
                    <span>or</span>
                </div>

                <a href="register.php" class="btn btn-outline btn-large btn-full">
                    <i class="fas fa-user-plus"></i>
                    Create New Account
                </a>
            </form>

            <div class="demo-accounts">
                <h4><i class="fas fa-vial"></i> Demo Accounts</h4>
                <div class="demo-account">
                    <strong>Super Admin:</strong> admin@memberportal.se / anypassword
                </div>
                <div class="demo-account">
                    <strong>Board Member:</strong> board@memberportal.se / anypassword
                </div>
                <div class="demo-account">
                    <strong>Staff:</strong> staff@memberportal.se / anypassword
                </div>
                <div class="demo-account">
                    <strong>Member:</strong> member@memberportal.se / anypassword
                </div>
                <div class="demo-help">
                    <i class="fas fa-info-circle"></i>
                    Use any password for demo accounts
                </div>
            </div>

            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Join now</a></p>
                <p><a href="pending-approval.php">Check approval status</a></p>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html>