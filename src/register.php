<?php
// register.php
require_once 'config.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $firstname = sanitizeInput($_POST['firstname']);
    $lastname = sanitizeInput($_POST['lastname']);
    $personnummer = sanitizeInput($_POST['personnummer']);
    $telephone = sanitizeInput($_POST['telephone']);
    $email = sanitizeInput($_POST['email']);
    $address = sanitizeInput($_POST['address']);
    $postal_code = sanitizeInput($_POST['postal_code']);
    $city = sanitizeInput($_POST['city']);
    $state = sanitizeInput($_POST['state']);
    $country = sanitizeInput($_POST['country'] ?? 'Sweden');

    // Validate required fields
    if (empty($firstname)) $errors[] = "First name is required";
    if (empty($lastname)) $errors[] = "Last name is required";
    if (empty($personnummer)) $errors[] = "Personnummer is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($address)) $errors[] = "Address is required";
    if (empty($postal_code)) $errors[] = "Postal code is required";
    if (empty($city)) $errors[] = "City is required";

    // Validate personnummer
    if (!empty($personnummer) && !validatePersonnummer($personnummer)) {
        $errors[] = "Invalid Swedish personnummer";
    }

    // Validate email
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // Check for duplicates
    if (emailExists($pdo, $email)) {
        $errors[] = "Email already registered";
    }

    if (personnummerExists($pdo, $personnummer)) {
        $errors[] = "Personnummer already registered";
    }

    // If no errors, insert into database
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO members (firstname, lastname, personnummer, telephone, email, address, postal_code, city, state, country) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $firstname,
                $lastname,
                $personnummer,
                $telephone,
                $email,
                $address,
                $postal_code,
                $city,
                $state,
                $country
            ]);

            $success = true;
            $_POST = []; // Clear form

        } catch (PDOException $e) {
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Registration - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="header">
        <h1><?php echo APP_NAME; ?> - Member Registration</h1>
    </div>

    <div class="container">
        <?php if ($success): ?>
            <div class="alert alert-success">
                <h3>Registration Successful!</h3>
                <p>Thank you for registering. Your membership application has been received and is pending approval.</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <h3>Error(s) occurred:</h3>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form id="registrationForm" method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstname">First Name <span class="required">*</span></label>
                        <input type="text" id="firstname" name="firstname" value="<?php echo $_POST['firstname'] ?? ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="lastname">Last Name <span class="required">*</span></label>
                        <input type="text" id="lastname" name="lastname" value="<?php echo $_POST['lastname'] ?? ''; ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="personnummer">Personnummer <span class="required">*</span></label>
                    <input type="text" id="personnummer" name="personnummer" value="<?php echo $_POST['personnummer'] ?? ''; ?>" required>
                    <div class="form-help">Format: YYMMDD-XXXX or YYYYMMDD-XXXX</div>
                </div>

                <div class="form-group">
                    <label for="telephone">Telephone</label>
                    <input type="tel" id="telephone" name="telephone" value="<?php echo $_POST['telephone'] ?? ''; ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="<?php echo $_POST['email'] ?? ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="address">Address <span class="required">*</span></label>
                    <input type="text" id="address" name="address" value="<?php echo $_POST['address'] ?? ''; ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="postal_code">Postal Code <span class="required">*</span></label>
                        <input type="text" id="postal_code" name="postal_code" value="<?php echo $_POST['postal_code'] ?? ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="city">City <span class="required">*</span></label>
                        <input type="text" id="city" name="city" value="<?php echo $_POST['city'] ?? ''; ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="state">State/Region</label>
                        <input type="text" id="state" name="state" value="<?php echo $_POST['state'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="country">Country</label>
                        <input type="text" id="country" name="country" value="<?php echo $_POST['country'] ?? 'Sweden'; ?>">
                    </div>
                </div>

                <button type="submit" class="btn">Register</button>
            </form>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>