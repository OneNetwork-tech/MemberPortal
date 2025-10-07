<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="header">
        <h1><?php echo APP_NAME; ?> - Access Denied</h1>
    </div>
    <div class="container">
        <div class="form-container" style="text-align: center;">
            <h2>Access Denied</h2>
            <p>You don't have permission to access this page.</p>
            <a href="admin_dashboard.php" class="btn">Return to Dashboard</a>
        </div>
    </div>
</body>
</html>