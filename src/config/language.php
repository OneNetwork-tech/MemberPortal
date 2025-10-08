<?php
// Language configuration
define('DEFAULT_LANGUAGE', 'sv');
define('SUPPORTED_LANGUAGES', ['sv', 'en']);

// Get current language
function getCurrentLanguage() {
    if (isset($_SESSION['language']) && in_array($_SESSION['language'], SUPPORTED_LANGUAGES)) {
        return $_SESSION['language'];
    }
    
    // Try to detect browser language
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        if (in_array($browserLang, SUPPORTED_LANGUAGES)) {
            return $browserLang;
        }
    }
    
    return DEFAULT_LANGUAGE;
}

// Set language
function setLanguage($language) {
    if (in_array($language, SUPPORTED_LANGUAGES)) {
        $_SESSION['language'] = $language;
        return true;
    }
    return false;
}

// Language strings
$translations = [
    'sv' => [
        // General
        'app_name' => 'Medlemsportal',
        'welcome' => 'Välkommen',
        'save' => 'Spara',
        'cancel' => 'Avbryt',
        'delete' => 'Radera',
        'edit' => 'Redigera',
        'view' => 'Visa',
        'search' => 'Sök',
        'filter' => 'Filtrera',
        'actions' => 'Åtgärder',
        'status' => 'Status',
        'active' => 'Aktiv',
        'inactive' => 'Inaktiv',
        'pending' => 'Väntar på godkännande',
        
        // Navigation
        'home' => 'Hem',
        'dashboard' => 'Dashboard',
        'members' => 'Medlemmar',
        'reports' => 'Rapporter',
        'settings' => 'Inställningar',
        'profile' => 'Profil',
        'logout' => 'Logga ut',
        'login' => 'Logga in',
        'register' => 'Registrera',
        
        // Auth
        'login_title' => 'Logga in på din medlemsportal',
        'register_title' => 'Gå med i medlemsportalen',
        'email' => 'E-post',
        'password' => 'Lösenord',
        'confirm_password' => 'Bekräfta lösenord',
        'remember_me' => 'Kom ihåg mig',
        'forgot_password' => 'Glömt lösenord?',
        'no_account' => 'Har du inget konto?',
        'has_account' => 'Har du redan ett konto?',
        'create_account' => 'Skapa konto',
        'sign_in' => 'Logga in',
        
        // Registration
        'personal_info' => 'Personlig information',
        'address_info' => 'Adressinformation',
        'review_submit' => 'Granska & Skicka',
        'first_name' => 'Förnamn',
        'last_name' => 'Efternamn',
        'personnummer' => 'Personnummer',
        'telephone' => 'Telefon',
        'address' => 'Adress',
        'postal_code' => 'Postnummer',
        'city' => 'Stad',
        'state' => 'Län',
        'country' => 'Land',
        'terms_agree' => 'Jag godkänner <a href="terms.php">Användarvillkoren</a> och <a href="privacy.php">Integritetspolicyn</a>',
        'newsletter_agree' => 'Jag vill få uppdateringar och nyhetsbrev om medlemsförmåner',
        'registration_success' => 'Registrering Lyckades!',
        'registration_success_message' => 'Tack för att du registrerar dig hos %s. Din medlemsansökan har mottagits och väntar på godkännande.',
        
        // Form helpers
        'required_field' => 'Obligatoriskt fält',
        'personnummer_help' => 'Format: ÅÅÅÅMMDD-XXXX eller ÅÅMMDD-XXXX. Används för identitetsverifiering.',
        'email_help' => 'Vi skickar godkännandemeddelande till denna e-post',
        'telephone_help' => 'Inkludera landsnummer om utanför Sverige',
        'postal_code_help' => '5-siffrigt svenskt postnummer',
        
        // Dashboard
        'admin_dashboard' => 'Admin Dashboard',
        'member_dashboard' => 'Medlems Dashboard',
        'total_members' => 'Totalt antal medlemmar',
        'active_members' => 'Aktiva medlemmar',
        'pending_approval' => 'Väntar på godkännande',
        'member_roles' => 'Medlemsroller',
        'recent_activity' => 'Senaste aktivitet',
        'recent_members' => 'Nya medlemmar',
        
        // Member management
        'approve' => 'Godkänn',
        'suspend' => 'Avstäng',
        'activate' => 'Aktivera',
        'role' => 'Roll',
        'registration_date' => 'Registreringsdatum',
        'last_login' => 'Senaste inloggning',
        
        // Roles
        'super_admin' => 'Super Admin',
        'admin' => 'Admin',
        'board_member' => 'Styrelsemedlem',
        'staff' => 'Personal',
        'moderator' => 'Moderator',
        'member' => 'Medlem',
        
        // Errors
        'error' => 'Fel',
        'validation_error' => 'Valideringsfel',
        'login_failed' => 'Inloggning misslyckades',
        'registration_failed' => 'Registrering misslyckades',
        'invalid_email' => 'Ogiltig e-postadress',
        'invalid_personnummer' => 'Ogiltigt personnummer',
        'email_exists' => 'E-postadressen finns redan registrerad',
        'personnummer_exists' => 'Personnumret finns redan registrerat',
        'required_fields' => 'Vänligen fyll i alla obligatoriska fält',
        
        // Success
        'success' => 'Lyckades',
        'operation_success' => 'Åtgärd lyckades',
        
        // Time
        'just_now' => 'just nu',
        'minutes_ago' => 'minuter sedan',
        'hours_ago' => 'timmar sedan',
        'days_ago' => 'dagar sedan'
    ],
    
    'en' => [
        // General
        'app_name' => 'Membership Portal',
        'welcome' => 'Welcome',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'delete' => 'Delete',
        'edit' => 'Edit',
        'view' => 'View',
        'search' => 'Search',
        'filter' => 'Filter',
        'actions' => 'Actions',
        'status' => 'Status',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'pending' => 'Pending Approval',
        
        // Navigation
        'home' => 'Home',
        'dashboard' => 'Dashboard',
        'members' => 'Members',
        'reports' => 'Reports',
        'settings' => 'Settings',
        'profile' => 'Profile',
        'logout' => 'Logout',
        'login' => 'Login',
        'register' => 'Register',
        
        // Auth
        'login_title' => 'Login to your membership portal',
        'register_title' => 'Join the membership portal',
        'email' => 'Email',
        'password' => 'Password',
        'confirm_password' => 'Confirm Password',
        'remember_me' => 'Remember me',
        'forgot_password' => 'Forgot password?',
        'no_account' => 'Don\'t have an account?',
        'has_account' => 'Already have an account?',
        'create_account' => 'Create account',
        'sign_in' => 'Sign in',
        
        // Registration
        'personal_info' => 'Personal Information',
        'address_info' => 'Address Information',
        'review_submit' => 'Review & Submit',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'personnummer' => 'Personal Identity Number',
        'telephone' => 'Telephone',
        'address' => 'Address',
        'postal_code' => 'Postal Code',
        'city' => 'City',
        'state' => 'State/Region',
        'country' => 'Country',
        'terms_agree' => 'I agree to the <a href="terms.php">Terms of Service</a> and <a href="privacy.php">Privacy Policy</a>',
        'newsletter_agree' => 'I want to receive updates and newsletters about membership benefits',
        'registration_success' => 'Registration Successful!',
        'registration_success_message' => 'Thank you for registering with %s. Your membership application has been received and is pending approval.',
        
        // Form helpers
        'required_field' => 'Required field',
        'personnummer_help' => 'Format: YYYYMMDD-XXXX or YYMMDD-XXXX. Used for identity verification.',
        'email_help' => 'We\'ll send approval notification to this email',
        'telephone_help' => 'Include country code if outside Sweden',
        'postal_code_help' => '5-digit Swedish postal code',
        
        // Dashboard
        'admin_dashboard' => 'Admin Dashboard',
        'member_dashboard' => 'Member Dashboard',
        'total_members' => 'Total Members',
        'active_members' => 'Active Members',
        'pending_approval' => 'Pending Approval',
        'member_roles' => 'Member Roles',
        'recent_activity' => 'Recent Activity',
        'recent_members' => 'Recent Members',
        
        // Member management
        'approve' => 'Approve',
        'suspend' => 'Suspend',
        'activate' => 'Activate',
        'role' => 'Role',
        'registration_date' => 'Registration Date',
        'last_login' => 'Last Login',
        
        // Roles
        'super_admin' => 'Super Admin',
        'admin' => 'Admin',
        'board_member' => 'Board Member',
        'staff' => 'Staff',
        'moderator' => 'Moderator',
        'member' => 'Member',
        
        // Errors
        'error' => 'Error',
        'validation_error' => 'Validation Error',
        'login_failed' => 'Login failed',
        'registration_failed' => 'Registration failed',
        'invalid_email' => 'Invalid email address',
        'invalid_personnummer' => 'Invalid personal identity number',
        'email_exists' => 'Email address already registered',
        'personnummer_exists' => 'Personal identity number already registered',
        'required_fields' => 'Please fill in all required fields',
        
        // Success
        'success' => 'Success',
        'operation_success' => 'Operation successful',
        
        // Time
        'just_now' => 'just now',
        'minutes_ago' => 'minutes ago',
        'hours_ago' => 'hours ago',
        'days_ago' => 'days ago'
    ]
];

// Translation function
function t($key, $params = []) {
    global $translations;
    $language = getCurrentLanguage();
    
    $translation = $translations[$language][$key] ?? $translations[DEFAULT_LANGUAGE][$key] ?? $key;
    
    // Replace parameters
    foreach ($params as $param => $value) {
        $translation = str_replace("%$param%", $value, $translation);
    }
    
    return $translation;
}

// Initialize language
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set default language if not set
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = getCurrentLanguage();
}
?>