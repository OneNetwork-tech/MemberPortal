<?php
require_once 'config.php';

$errors = [];
$success = false;
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $formData = [
        'firstname' => sanitizeInput($_POST['firstname'] ?? ''),
        'lastname' => sanitizeInput($_POST['lastname'] ?? ''),
        'personnummer' => sanitizeInput($_POST['personnummer'] ?? ''),
        'telephone' => sanitizeInput($_POST['telephone'] ?? ''),
        'email' => sanitizeInput($_POST['email'] ?? ''),
        'address' => sanitizeInput($_POST['address'] ?? ''),
        'postal_code' => sanitizeInput($_POST['postal_code'] ?? ''),
        'city' => sanitizeInput($_POST['city'] ?? ''),
        'state' => sanitizeInput($_POST['state'] ?? ''),
        'country' => sanitizeInput($_POST['country'] ?? 'Sweden')
    ];

    // Validate required fields
    $requiredFields = ['firstname', 'lastname', 'personnummer', 'email', 'address', 'postal_code', 'city'];
    foreach ($requiredFields as $field) {
        if (empty($formData[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
        }
    }

    // Validate personnummer
    if (!empty($formData['personnummer']) && !validatePersonnummer($formData['personnummer'])) {
        $errors[] = "Invalid Swedish personnummer format";
    }

    // Validate email
    if (!empty($formData['email']) && !isValidEmail($formData['email'])) {
        $errors[] = "Invalid email format";
    }

    // Check for duplicates
    if (!empty($formData['email']) && emailExists($pdo, $formData['email'])) {
        $errors[] = "Email already registered";
    }

    if (!empty($formData['personnummer']) && personnummerExists($pdo, $formData['personnummer'])) {
        $errors[] = "Personnummer already registered";
    }

    // Auto-fill address if postal code is provided
    if (empty($formData['city']) && !empty($formData['postal_code'])) {
        $addressInfo = getAddressFromPostalCode($formData['postal_code']);
        if ($addressInfo) {
            $formData['city'] = $addressInfo['city'];
            $formData['state'] = $addressInfo['state'];
        }
    }

    // If no errors, insert into database
    if (empty($errors)) {
        try {
            // Generate a temporary password (user will reset it later)
            $temp_password = hashPassword(generateRandomPassword());
            
            $stmt = $pdo->prepare("INSERT INTO members (firstname, lastname, personnummer, telephone, email, address, postal_code, city, state, country, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'member', 'pending')");
            
            $stmt->execute([
                $formData['firstname'],
                $formData['lastname'],
                $formData['personnummer'],
                $formData['telephone'],
                $formData['email'],
                $formData['address'],
                $formData['postal_code'],
                $formData['city'],
                $formData['state'],
                $formData['country'],
                $temp_password
            ]);

            // Log the registration
            $member_id = $pdo->lastInsertId();
            logActivity($pdo, $member_id, 'member_registered', 'New member registration: ' . $formData['email']);

            // Send welcome email
            $login_url = BASE_URL . 'login.php';
            sendWelcomeEmail($formData['email'], $formData['firstname'] . ' ' . $formData['lastname'], $login_url);

            $success = true;
            $formData = []; // Clear form

        } catch (PDOException $e) {
            $errors[] = "Registration failed. Please try again.";
            error_log("Registration error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <a href="index.php" class="back-home">
                    <i class="fas fa-arrow-left"></i>
                    Back to Home
                </a>
                <div class="auth-logo">
                    <i class="fas fa-users"></i>
                    <h1>Join <?php echo APP_NAME; ?></h1>
                </div>
                <p>Create your membership account</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <div class="alert-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="alert-content">
                        <h3>Registration Successful!</h3>
                        <p>Thank you for registering. Your membership application has been received and is pending approval. You will receive an email once your account is approved.</p>
                    </div>
                    <div class="alert-actions">
                        <a href="login.php" class="btn btn-primary">Go to Login</a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="alert-content">
                        <h3>Please fix the following errors:</h3>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form id="registrationForm" method="POST" action="" class="auth-form">
                <div class="form-section">
                    <h3><i class="fas fa-user"></i> Personal Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstname">First Name <span class="required">*</span></label>
                            <input type="text" id="firstname" name="firstname" 
                                   value="<?php echo htmlspecialchars($formData['firstname'] ?? ''); ?>" 
                                   required
                                   placeholder="Enter your first name">
                            <div class="form-help">Your legal first name as in official documents</div>
                        </div>
                        <div class="form-group">
                            <label for="lastname">Last Name <span class="required">*</span></label>
                            <input type="text" id="lastname" name="lastname" 
                                   value="<?php echo htmlspecialchars($formData['lastname'] ?? ''); ?>" 
                                   required
                                   placeholder="Enter your last name">
                            <div class="form-help">Your legal last name as in official documents</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="personnummer">Personnummer <span class="required">*</span></label>
                        <input type="text" id="personnummer" name="personnummer" 
                               value="<?php echo htmlspecialchars($formData['personnummer'] ?? ''); ?>" 
                               required
                               placeholder="YYYYMMDD-XXXX">
                        <div class="form-help">Format: YYYYMMDD-XXXX or YYMMDD-XXXX</div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="telephone">Telephone</label>
                            <input type="tel" id="telephone" name="telephone" 
                                   value="<?php echo htmlspecialchars($formData['telephone'] ?? ''); ?>" 
                                   placeholder="+46 70 123 4567">
                            <div class="form-help">Include country code if outside Sweden</div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" 
                                   required
                                   placeholder="your.email@example.com">
                            <div class="form-help">We'll send approval notification to this email</div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-home"></i> Address Information</h3>
                    <div class="form-group">
                        <label for="address">Address <span class="required">*</span></label>
                        <input type="text" id="address" name="address" 
                               value="<?php echo htmlspecialchars($formData['address'] ?? ''); ?>" 
                               required
                               placeholder="Street name and number">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="postal_code">Postal Code <span class="required">*</span></label>
                            <input type="text" id="postal_code" name="postal_code" 
                                   value="<?php echo htmlspecialchars($formData['postal_code'] ?? ''); ?>" 
                                   required
                                   placeholder="12345"
                                   maxlength="5">
                            <div class="form-help">5-digit Swedish postal code</div>
                        </div>
                        <div class="form-group">
                            <label for="city">City <span class="required">*</span></label>
                            <input type="text" id="city" name="city" 
                                   value="<?php echo htmlspecialchars($formData['city'] ?? ''); ?>" 
                                   required
                                   placeholder="City name">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="state">State/Region</label>
                            <input type="text" id="state" name="state" 
                                   value="<?php echo htmlspecialchars($formData['state'] ?? ''); ?>" 
                                   placeholder="Stockholm">
                        </div>
                        <div class="form-group">
                            <label for="country">Country</label>
                            <select id="country" name="country">
                                <option value="Sweden" <?php echo ($formData['country'] ?? 'Sweden') === 'Sweden' ? 'selected' : ''; ?>>Sweden</option>
                                <option value="Norway" <?php echo ($formData['country'] ?? '') === 'Norway' ? 'selected' : ''; ?>>Norway</option>
                                <option value="Denmark" <?php echo ($formData['country'] ?? '') === 'Denmark' ? 'selected' : ''; ?>>Denmark</option>
                                <option value="Finland" <?php echo ($formData['country'] ?? '') === 'Finland' ? 'selected' : ''; ?>>Finland</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-shield-alt"></i> Terms & Conditions</h3>
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="terms" name="terms" required>
                            <label for="terms">
                                I agree to the <a href="#" target="_blank">Terms of Service</a> and 
                                <a href="#" target="_blank">Privacy Policy</a>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="newsletter" name="newsletter">
                            <label for="newsletter">
                                I want to receive updates and newsletters about membership benefits
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-large btn-full">
                    <i class="fas fa-user-plus"></i>
                    Create Membership Account
                </button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Sign in here</a></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="assets/js/auth.js"></script>
</body>
</html>