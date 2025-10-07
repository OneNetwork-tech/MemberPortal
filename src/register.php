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
        'country' => sanitizeInput($_POST['country'] ?? 'Sweden'),
        'terms' => isset($_POST['terms']),
        'newsletter' => isset($_POST['newsletter'])
    ];

    // Validate required fields
    $requiredFields = ['firstname', 'lastname', 'personnummer', 'email', 'address', 'postal_code', 'city'];
    foreach ($requiredFields as $field) {
        if (empty($formData[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
        }
    }

    // Validate terms acceptance
    if (!$formData['terms']) {
        $errors[] = "You must accept the Terms of Service and Privacy Policy";
    }

    // Validate personnummer
    if (!empty($formData['personnummer']) && !validatePersonnummer($formData['personnummer'])) {
        $errors[] = "Invalid Swedish personnummer format";
    }

    // Validate email
    if (!empty($formData['email']) && !isValidEmail($formData['email'])) {
        $errors[] = "Invalid email format";
    }

    // Validate telephone (if provided)
    if (!empty($formData['telephone']) && !isValidPhone($formData['telephone'])) {
        $errors[] = "Invalid phone number format";
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
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .registration-progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .registration-progress::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--gray-300);
            z-index: 1;
        }
        
        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--white);
            border: 2px solid var(--gray-300);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--gray-500);
            margin-bottom: 0.5rem;
            transition: var(--transition);
        }
        
        .step-label {
            font-size: 0.75rem;
            color: var(--gray-500);
            text-align: center;
            transition: var(--transition);
        }
        
        .progress-step.active .step-number {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--white);
        }
        
        .progress-step.active .step-label {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .progress-step.completed .step-number {
            background: #10b981;
            border-color: #10b981;
            color: var(--white);
        }
        
        .form-section {
            display: none;
        }
        
        .form-section.active {
            display: block;
        }
        
        .form-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--gray-200);
        }
        
        .password-strength {
            margin-top: 0.5rem;
        }
        
        .strength-meter {
            height: 4px;
            background: var(--gray-300);
            border-radius: 2px;
            margin-bottom: 0.5rem;
            overflow: hidden;
        }
        
        .strength-fill {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
            border-radius: 2px;
        }
        
        .strength-weak .strength-fill {
            background: #ef4444;
            width: 33%;
        }
        
        .strength-medium .strength-fill {
            background: #f59e0b;
            width: 66%;
        }
        
        .strength-strong .strength-fill {
            background: #10b981;
            width: 100%;
        }
        
        .strength-text {
            font-size: 0.75rem;
            color: var(--gray-600);
        }
        
        .benefits-list {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }
        
        .benefits-list li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0;
            color: var(--gray-700);
        }
        
        .benefits-list li i {
            color: #10b981;
        }
        
        .privacy-note {
            background: var(--gray-50);
            padding: 1rem;
            border-radius: var(--border-radius);
            margin: 1rem 0;
            font-size: 0.875rem;
            color: var(--gray-600);
        }
        
        .privacy-note i {
            color: var(--primary-color);
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-users"></i>
                <span><?php echo APP_NAME; ?></span>
            </div>
            <div class="nav-links">
                <a href="index.php" class="nav-link">Home</a>
                <a href="index.php#features" class="nav-link">Features</a>
                <a href="index.php#about" class="nav-link">About</a>
                <a href="login.php" class="nav-link">Login</a>
            </div>
            <div class="nav-actions">
                <a href="login.php" class="btn btn-outline">Login</a>
                <a href="register.php" class="btn btn-primary">Register</a>
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
                    <i class="fas fa-user-plus"></i>
                    <h1>Join <?php echo APP_NAME; ?></h1>
                </div>
                <p>Create your membership account in just a few steps</p>
            </div>

            <!-- Registration Progress -->
            <div class="registration-progress">
                <div class="progress-step active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-label">Personal Info</div>
                </div>
                <div class="progress-step" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-label">Address</div>
                </div>
                <div class="progress-step" data-step="3">
                    <div class="step-number">3</div>
                    <div class="step-label">Review & Submit</div>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <div class="alert-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="alert-content">
                        <h3>Registration Successful!</h3>
                        <p>Thank you for registering with <?php echo APP_NAME; ?>. Your membership application has been received and is pending approval.</p>
                        <p><strong>What happens next?</strong></p>
                        <ul>
                            <li>You will receive a confirmation email shortly</li>
                            <li>Our team will review your application</li>
                            <li>You'll be notified once your account is approved</li>
                            <li>After approval, you can login to access member benefits</li>
                        </ul>
                    </div>
                    <div class="alert-actions">
                        <a href="index.php" class="btn btn-outline">Back to Home</a>
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
                <!-- Step 1: Personal Information -->
                <div class="form-section active" data-step="1">
                    <h3><i class="fas fa-user"></i> Personal Information</h3>
                    <p class="form-help">Tell us about yourself. All fields marked with <span class="required">*</span> are required.</p>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstname">First Name <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="firstname" name="firstname" 
                                       value="<?php echo htmlspecialchars($formData['firstname'] ?? ''); ?>" 
                                       required
                                       placeholder="Enter your first name"
                                       data-validate="required">
                            </div>
                            <div class="form-help">Your legal first name as in official documents</div>
                        </div>
                        <div class="form-group">
                            <label for="lastname">Last Name <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="lastname" name="lastname" 
                                       value="<?php echo htmlspecialchars($formData['lastname'] ?? ''); ?>" 
                                       required
                                       placeholder="Enter your last name"
                                       data-validate="required">
                            </div>
                            <div class="form-help">Your legal last name as in official documents</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="personnummer">Personnummer <span class="required">*</span></label>
                        <div class="input-with-icon">
                            <i class="fas fa-id-card"></i>
                            <input type="text" id="personnummer" name="personnummer" 
                                   value="<?php echo htmlspecialchars($formData['personnummer'] ?? ''); ?>" 
                                   required
                                   placeholder="YYYYMMDD-XXXX"
                                   data-validate="personnummer"
                                   maxlength="13">
                        </div>
                        <div class="form-help">Format: YYYYMMDD-XXXX or YYMMDD-XXXX. Used for identity verification.</div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="telephone">Telephone</label>
                            <div class="input-with-icon">
                                <i class="fas fa-phone"></i>
                                <input type="tel" id="telephone" name="telephone" 
                                       value="<?php echo htmlspecialchars($formData['telephone'] ?? ''); ?>" 
                                       placeholder="+46 70 123 4567"
                                       data-validate="phone">
                            </div>
                            <div class="form-help">Include country code if outside Sweden</div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" 
                                       required
                                       placeholder="your.email@example.com"
                                       data-validate="email">
                            </div>
                            <div class="form-help">We'll send approval notification to this email</div>
                        </div>
                    </div>

                    <div class="form-navigation">
                        <div></div> <!-- Spacer -->
                        <button type="button" class="btn btn-primary next-step" data-next="2">
                            Next: Address Information <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Address Information -->
                <div class="form-section" data-step="2">
                    <h3><i class="fas fa-home"></i> Address Information</h3>
                    <p class="form-help">Your address information helps us verify your membership eligibility.</p>
                    
                    <div class="form-group">
                        <label for="address">Street Address <span class="required">*</span></label>
                        <div class="input-with-icon">
                            <i class="fas fa-map-marker-alt"></i>
                            <input type="text" id="address" name="address" 
                                   value="<?php echo htmlspecialchars($formData['address'] ?? ''); ?>" 
                                   required
                                   placeholder="Street name and number"
                                   data-validate="required">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="postal_code">Postal Code <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="fas fa-mail-bulk"></i>
                                <input type="text" id="postal_code" name="postal_code" 
                                       value="<?php echo htmlspecialchars($formData['postal_code'] ?? ''); ?>" 
                                       required
                                       placeholder="12345"
                                       maxlength="5"
                                       data-validate="postal"
                                       data-auto-fill="true">
                            </div>
                            <div class="form-help">5-digit Swedish postal code</div>
                        </div>
                        <div class="form-group">
                            <label for="city">City <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="fas fa-city"></i>
                                <input type="text" id="city" name="city" 
                                       value="<?php echo htmlspecialchars($formData['city'] ?? ''); ?>" 
                                       required
                                       placeholder="City name"
                                       data-validate="required">
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="state">State/Region</label>
                            <div class="input-with-icon">
                                <i class="fas fa-map"></i>
                                <input type="text" id="state" name="state" 
                                       value="<?php echo htmlspecialchars($formData['state'] ?? ''); ?>" 
                                       placeholder="Stockholm">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="country">Country</label>
                            <div class="input-with-icon">
                                <i class="fas fa-globe"></i>
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

                    <div class="form-navigation">
                        <button type="button" class="btn btn-outline prev-step" data-prev="1">
                            <i class="fas fa-arrow-left"></i> Previous
                        </button>
                        <button type="button" class="btn btn-primary next-step" data-next="3">
                            Next: Review & Submit <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 3: Review & Terms -->
                <div class="form-section" data-step="3">
                    <h3><i class="fas fa-clipboard-check"></i> Review & Submit</h3>
                    <p class="form-help">Please review your information and accept the terms to complete your registration.</p>
                    
                    <div class="review-section">
                        <h4>Personal Information</h4>
                        <div class="review-grid">
                            <div class="review-item">
                                <strong>Name:</strong>
                                <span id="review-name"><?php echo htmlspecialchars(($formData['firstname'] ?? '') . ' ' . ($formData['lastname'] ?? '')); ?></span>
                            </div>
                            <div class="review-item">
                                <strong>Personnummer:</strong>
                                <span id="review-personnummer"><?php echo htmlspecialchars($formData['personnummer'] ?? ''); ?></span>
                            </div>
                            <div class="review-item">
                                <strong>Email:</strong>
                                <span id="review-email"><?php echo htmlspecialchars($formData['email'] ?? ''); ?></span>
                            </div>
                            <div class="review-item">
                                <strong>Telephone:</strong>
                                <span id="review-telephone"><?php echo htmlspecialchars($formData['telephone'] ?? ''); ?></span>
                            </div>
                        </div>
                        
                        <h4>Address Information</h4>
                        <div class="review-grid">
                            <div class="review-item">
                                <strong>Address:</strong>
                                <span id="review-address"><?php echo htmlspecialchars($formData['address'] ?? ''); ?></span>
                            </div>
                            <div class="review-item">
                                <strong>Postal Code:</strong>
                                <span id="review-postal_code"><?php echo htmlspecialchars($formData['postal_code'] ?? ''); ?></span>
                            </div>
                            <div class="review-item">
                                <strong>City:</strong>
                                <span id="review-city"><?php echo htmlspecialchars($formData['city'] ?? ''); ?></span>
                            </div>
                            <div class="review-item">
                                <strong>Country:</strong>
                                <span id="review-country"><?php echo htmlspecialchars($formData['country'] ?? ''); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="privacy-note">
                        <i class="fas fa-shield-alt"></i>
                        <strong>Your privacy is important to us:</strong> We only use your personal information for membership management and verification purposes. We never share your data with third parties without your consent.
                    </div>

                    <div class="form-section">
                        <h4><i class="fas fa-file-contract"></i> Terms & Conditions</h4>
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="terms" name="terms" required <?php echo ($formData['terms'] ?? false) ? 'checked' : ''; ?>>
                                <label for="terms">
                                    I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and 
                                    <a href="privacy.php" target="_blank">Privacy Policy</a> <span class="required">*</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="newsletter" name="newsletter" <?php echo ($formData['newsletter'] ?? false) ? 'checked' : ''; ?>>
                                <label for="newsletter">
                                    I want to receive updates, newsletters, and information about membership benefits
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="benefits-list">
                        <h4>Membership Benefits:</h4>
                        <ul>
                            <li><i class="fas fa-check"></i> Access to exclusive member events</li>
                            <li><i class="fas fa-check"></i> Member discounts and offers</li>
                            <li><i class="fas fa-check"></i> Community networking opportunities</li>
                            <li><i class="fas fa-check"></i> Regular updates and newsletters</li>
                            <li><i class="fas fa-check"></i> Voting rights in organization matters</li>
                        </ul>
                    </div>

                    <div class="form-navigation">
                        <button type="button" class="btn btn-outline prev-step" data-prev="2">
                            <i class="fas fa-arrow-left"></i> Previous
                        </button>
                        <button type="submit" class="btn btn-primary btn-large">
                            <i class="fas fa-user-plus"></i>
                            Complete Registration
                        </button>
                    </div>
                </div>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Sign in here</a></p>
                <p>By registering, you agree to our membership guidelines and verification process.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/auth.js"></script>
    <script>
        // Multi-step form functionality
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registrationForm');
            const steps = document.querySelectorAll('.form-section');
            const progressSteps = document.querySelectorAll('.progress-step');
            const nextButtons = document.querySelectorAll('.next-step');
            const prevButtons = document.querySelectorAll('.prev-step');
            let currentStep = 1;

            // Initialize review fields
            updateReviewFields();

            // Next button functionality
            nextButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const nextStep = parseInt(this.getAttribute('data-next'));
                    if (validateStep(currentStep)) {
                        showStep(nextStep);
                    }
                });
            });

            // Previous button functionality
            prevButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const prevStep = parseInt(this.getAttribute('data-prev'));
                    showStep(prevStep);
                });
            });

            function showStep(step) {
                // Hide all steps
                steps.forEach(s => s.classList.remove('active'));
                progressSteps.forEach(s => s.classList.remove('active', 'completed'));
                
                // Show current step
                document.querySelector(`.form-section[data-step="${step}"]`).classList.add('active');
                
                // Update progress steps
                progressSteps.forEach((progressStep, index) => {
                    const stepNumber = parseInt(progressStep.getAttribute('data-step'));
                    if (stepNumber < step) {
                        progressStep.classList.add('completed');
                    } else if (stepNumber === step) {
                        progressStep.classList.add('active');
                    }
                });
                
                currentStep = step;
                updateReviewFields();
            }

            function validateStep(step) {
                const currentSection = document.querySelector(`.form-section[data-step="${step}"]`);
                const inputs = currentSection.querySelectorAll('input[required], select[required]');
                let isValid = true;

                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        showFieldError(input, 'This field is required');
                        isValid = false;
                    } else {
                        clearFieldError(input);
                    }

                    // Additional validation based on input type
                    if (input.type === 'email' && input.value) {
                        if (!isValidEmail(input.value)) {
                            showFieldError(input, 'Please enter a valid email address');
                            isValid = false;
                        }
                    }

                    if (input.id === 'personnummer' && input.value) {
                        if (!isValidPersonnummer(input.value)) {
                            showFieldError(input, 'Please enter a valid Swedish personnummer');
                            isValid = false;
                        }
                    }
                });

                return isValid;
            }

            function updateReviewFields() {
                // Update review section with current form values
                const fields = ['firstname', 'lastname', 'personnummer', 'email', 'telephone', 'address', 'postal_code', 'city', 'country'];
                fields.forEach(field => {
                    const input = document.getElementById(field);
                    const reviewElement = document.getElementById(`review-${field}`);
                    if (input && reviewElement) {
                        if (field === 'firstname' || field === 'lastname') {
                            // Handle name fields specially
                            if (field === 'firstname') {
                                const firstname = input.value;
                                const lastname = document.getElementById('lastname').value;
                                document.getElementById('review-name').textContent = `${firstname} ${lastname}`;
                            }
                        } else {
                            reviewElement.textContent = input.value || 'Not provided';
                        }
                    }
                });
            }

            // Real-time validation
            document.querySelectorAll('input[data-validate]').forEach(input => {
                input.addEventListener('blur', function() {
                    validateField(this);
                });

                input.addEventListener('input', function() {
                    clearFieldError(this);
                    if (this.id === 'postal_code' && this.value.length === 5) {
                        autoFillAddress(this.value);
                    }
                });
            });

            function autoFillAddress(postalCode) {
                if (/^\d{5}$/.test(postalCode)) {
                    const cityInput = document.getElementById('city');
                    const stateInput = document.getElementById('state');
                    
                    if (cityInput && !cityInput.value) {
                        // Simulate API call - in production, use actual Swedish postal service API
                        const addressInfo = getAddressFromPostalCode(postalCode);
                        if (addressInfo) {
                            cityInput.value = addressInfo.city;
                            if (stateInput && !stateInput.value) {
                                stateInput.value = addressInfo.state;
                            }
                            showAutoFillMessage('Address information filled automatically from postal code');
                        }
                    }
                }
            }

            function getAddressFromPostalCode(postalCode) {
                const postalCodeMap = {
                    '11359': { city: 'Stockholm', state: 'Stockholm' },
                    '11129': { city: 'Stockholm', state: 'Stockholm' },
                    '10005': { city: 'Stockholm', state: 'Stockholm' },
                    '10044': { city: 'Stockholm', state: 'Stockholm' },
                    '21119': { city: 'Malmö', state: 'Skåne' },
                    '41319': { city: 'Göteborg', state: 'Västra Götaland' },
                    '58102': { city: 'Linköping', state: 'Östergötland' },
                    '75229': { city: 'Uppsala', state: 'Uppsala' },
                    '85178': { city: 'Sundsvall', state: 'Västernorrland' },
                    '97234': { city: 'Luleå', state: 'Norrbotten' },
                    '90325': { city: 'Umeå', state: 'Västerbotten' },
                    '65225': { city: 'Karlstad', state: 'Värmland' },
                    '79171': { city: 'Falun', state: 'Dalarna' },
                    '55111': { city: 'Jönköping', state: 'Jönköping' },
                    '39234': { city: 'Kalmar', state: 'Kalmar' },
                    '37104': { city: 'Karlskrona', state: 'Blekinge' },
                    '83145': { city: 'Östersund', state: 'Jämtland' },
                    '96133': { city: 'Boden', state: 'Norrbotten' },
                    '94185': { city: 'Piteå', state: 'Norrbotten' },
                    '93134': { city: 'Skellefteå', state: 'Västerbotten' }
                };
                
                return postalCodeMap[postalCode] || null;
            }

            function showAutoFillMessage(message) {
                // Remove existing message
                const existingMessage = document.querySelector('.auto-fill-message');
                if (existingMessage) {
                    existingMessage.remove();
                }
                
                // Create new message
                const messageDiv = document.createElement('div');
                messageDiv.className = 'auto-fill-message';
                messageDiv.style.cssText = `
                    background: #d1fae5;
                    color: #065f46;
                    padding: 0.5rem 1rem;
                    border-radius: 4px;
                    font-size: 0.875rem;
                    margin-top: 0.5rem;
                    border: 1px solid #a7f3d0;
                `;
                messageDiv.textContent = message;
                
                const postalCodeGroup = document.getElementById('postal_code').closest('.form-group');
                postalCodeGroup.appendChild(messageDiv);
                
                // Auto-remove after 5 seconds
                setTimeout(() => {
                    messageDiv.remove();
                }, 5000);
            }

            // Form submission
            form.addEventListener('submit', function(e) {
                if (!validateStep(3)) {
                    e.preventDefault();
                    // Scroll to first error
                    const firstError = form.querySelector('.field-error');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
        });
    </script>
</body>
</html>