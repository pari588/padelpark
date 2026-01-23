<?php
/**
 * Vendor Portal - Main Entry Point
 * Redirects to login if not authenticated, otherwise to dashboard
 */

require_once(__DIR__ . "/x-vendorportal.inc.php");

// If logged in, redirect to dashboard
if (vpIsLoggedIn()) {
    header("Location: " . VP_BASEURL . "/vendorportal/dashboard");
    exit;
}

// Otherwise, redirect to login
header("Location: " . VP_BASEURL . "/vendorportal/login");
exit;
