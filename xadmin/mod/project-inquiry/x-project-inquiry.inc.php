<?php
// ============================================================================
// Pump Inquiry - Admin Module Configuration
// ============================================================================
// This file configures the admin module for pump inquiries
// It sets up the module variables required by the admin framework

// IMPORTANT: This must be called unconditionally on every page load
// so that session variables are available for AJAX requests (trash/delete)
if (function_exists("setModVars")) {
    setModVars(array(
        "TBL" => "bombay_pump_inquiry",
        "PK" => "pumpInquiryID",
        "NO_PREFIX" => true  // Don't add mx_ prefix to this table name
    ));
}

// Process AJAX POST requests
if (isset($_POST["xAction"])) {
    // This is an AJAX POST request from admin form
    require_once("../../../core/core.inc.php");
}
?>
