<?php
/**
 * Sky Padel India - xSite Module Include
 * Public-facing website for padel court manufacturing
 */

// Prevent direct access
if (!defined('SITEURL')) {
    // Try to find the main include
    $basePath = dirname(dirname(dirname(__FILE__)));
    if (file_exists($basePath . '/inc/common.inc.php')) {
        include_once($basePath . '/inc/common.inc.php');
    } else {
        // Fallback for standalone access
        define('SITEURL', '/bes/');
        define('DB_PREFIX', 'mx_');
    }
}

// Module constants
define('SP_MODULE_PATH', __DIR__);
define('SP_ASSETS_URL', SITEURL . 'xsite/mod/skypadel/inc/');

/**
 * Clean output for display
 */
function spClean($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Format phone number for display
 */
function spFormatPhone($phone) {
    return preg_replace('/(\d{2})(\d{5})(\d{5})/', '+$1 $2 $3', $phone);
}

/**
 * Get database connection (if needed)
 */
function spGetDB() {
    global $xdb;
    if (isset($xdb) && $xdb) {
        return $xdb;
    }
    return null;
}

/**
 * Submit inquiry form
 */
function spSubmitInquiry($data) {
    $db = spGetDB();
    if (!$db) {
        return ['err' => 1, 'msg' => 'Database not available'];
    }

    $prefix = DB_PREFIX;
    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $city = trim($data['city'] ?? '');
    $courtType = trim($data['court_type'] ?? '');
    $message = trim($data['message'] ?? '');

    // Validation
    if (empty($name) || empty($email) || empty($phone)) {
        return ['err' => 1, 'msg' => 'Please fill in all required fields'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['err' => 1, 'msg' => 'Please enter a valid email address'];
    }

    // Insert into leads table
    $sql = "INSERT INTO {$prefix}sky_padel_lead
            (clientName, clientEmail, clientPhone, clientCity, courtType, clientMessage, leadSource, created)
            VALUES (?, ?, ?, ?, ?, ?, 'Website', NOW())";

    $stmt = $db->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssssss", $name, $email, $phone, $city, $courtType, $message);
        if ($stmt->execute()) {
            return ['err' => 0, 'msg' => 'Thank you! Your inquiry has been submitted. We will contact you shortly.'];
        }
    }

    return ['err' => 1, 'msg' => 'Unable to submit inquiry. Please try again.'];
}

/**
 * Site Configuration
 */
$spConfig = [
    'company_name' => 'Sky Padel India',
    'tagline' => 'The Ball is in Your Court',
    'description' => 'We Design, Manufacture and Install the Safest and Highest-Quality Padel Courts Worldwide',
    'phone' => '+919819713115',
    'email' => 'info@skypadel.in',
    'address' => 'Mumbai, India',
    'stats' => [
        'courts' => '1200+',
        'countries' => '50+',
        'partners' => '40+',
        'warranty' => '5 Years'
    ],
    'social' => [
        'facebook' => '#',
        'instagram' => '#',
        'linkedin' => '#',
        'youtube' => '#'
    ]
];
