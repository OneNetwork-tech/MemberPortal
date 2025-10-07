<?php
// functions.php

/**
 * Validate Swedish personnummer
 */
function validatePersonnummer($personnummer) {
    // Remove any dashes or spaces
    $personnummer = preg_replace('/[-\s]/', '', $personnummer);
    
    // Check if it's 10 or 12 digits
    if (!preg_match('/^(\d{10}|\d{12})$/', $personnummer)) {
        return false;
    }
    
    // If 12 digits, remove first two (century)
    if (strlen($personnummer) == 12) {
        $personnummer = substr($personnummer, 2);
    }
    
    // Luhn algorithm validation
    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $digit = intval($personnummer[$i]);
        if ($i % 2 == 0) {
            $digit *= 2;
            if ($digit > 9) {
                $digit -= 9;
            }
        }
        $sum += $digit;
    }
    
    $checksum = (10 - ($sum % 10)) % 10;
    return $checksum == intval($personnummer[9]);
}

/**
 * Get address information from Swedish postal code
 */
function getAddressFromPostalCode($postal_code) {
    // Use Postnord or similar API - this is a simplified version
    $postal_codes = [
        '11359' => ['city' => 'Stockholm', 'state' => 'Stockholm'],
        '11129' => ['city' => 'Stockholm', 'state' => 'Stockholm'],
        '21119' => ['city' => 'Malmö', 'state' => 'Skåne'],
        '41319' => ['city' => 'Göteborg', 'state' => 'Västra Götaland'],
        // Add more postal codes as needed
    ];
    
    return isset($postal_codes[$postal_code]) ? $postal_codes[$postal_code] : null;
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Check if email already exists
 */
function emailExists($pdo, $email) {
    $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch() !== false;
}

/**
 * Check if personnummer already exists
 */
function personnummerExists($pdo, $personnummer) {
    $stmt = $pdo->prepare("SELECT id FROM members WHERE personnummer = ?");
    $stmt->execute([$personnummer]);
    return $stmt->fetch() !== false;
}
?>