<?php
require_once 'config.php';

// Handle language change
if (isset($_GET['lang'])) {
    $newLang = $_GET['lang'];
    if (setLanguage($newLang)) {
        // Redirect back to the same page without the lang parameter
        $currentUrl = str_replace(['?lang=' . $newLang, '&lang=' . $newLang], '', $_SERVER['REQUEST_URI']);
        header('Location: ' . $currentUrl);
        exit;
    }
}
?>