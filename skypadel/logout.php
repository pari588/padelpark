<?php
/**
 * Sky Padel Client Portal - Logout
 */
require_once __DIR__ . '/core/config.php';

if (isLoggedIn()) {
    logActivity(getClientEmail(), 'Logout');
}

// Clear session
session_destroy();

// Redirect to login
header("Location: " . SITE_URL . "/login.php?msg=logout");
exit;
