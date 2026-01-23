<?php
/**
 * Vendor Portal - Core Include File
 * Handles session, authentication, and common functions
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define constants
if (!defined('VENDOR_PORTAL_SESSION')) define('VENDOR_PORTAL_SESSION', 'VENDOR_PORTAL');
if (!defined('VP_CSRF_TOKEN')) define('VP_CSRF_TOKEN', 'VP_CSRF');

// Include core database connection
if (!defined('BES_ROOT')) {
    define('BES_ROOT', dirname(dirname(dirname(__DIR__))));
}
require_once(BES_ROOT . "/core/core.inc.php");

// Define vendor portal base URL (root URL without /xsite)
if (!defined('VP_BASEURL')) {
    define('VP_BASEURL', 'https://pp.paritoshajmera.com');
}

global $DB;

/**
 * Check if vendor is logged in
 */
function vpIsLoggedIn() {
    return isset($_SESSION[VENDOR_PORTAL_SESSION]['vendorID']) &&
           $_SESSION[VENDOR_PORTAL_SESSION]['vendorID'] > 0;
}

/**
 * Get logged in vendor ID
 */
function vpGetVendorID() {
    return $_SESSION[VENDOR_PORTAL_SESSION]['vendorID'] ?? 0;
}

/**
 * Get logged in vendor data
 */
function vpGetVendor() {
    return $_SESSION[VENDOR_PORTAL_SESSION]['vendor'] ?? [];
}

/**
 * Get portal user data
 */
function vpGetUser() {
    return $_SESSION[VENDOR_PORTAL_SESSION]['user'] ?? [];
}

/**
 * Require authentication - redirect to login if not logged in
 */
function vpRequireAuth() {
    if (!vpIsLoggedIn()) {
        header('Location: ' . VP_BASEURL . '/vendorportal/login');
        exit;
    }
}

/**
 * Login vendor
 */
function vpLogin($email, $password) {
    global $DB;

    $DB->sql = "SELECT u.*, v.legalName, v.vendorCode, v.approvalStatus
                FROM mx_vendor_portal_user u
                JOIN mx_vendor_onboarding v ON u.vendorID = v.vendorID
                WHERE u.email = ? AND u.isActive = 1 AND u.status = 1";
    $DB->vals = [$email];
    $DB->types = "s";
    $user = $DB->dbRow();

    if (!$user) {
        return ['err' => 1, 'msg' => 'Invalid email or account not active'];
    }

    if (!password_verify($password, $user['passwordHash'])) {
        return ['err' => 1, 'msg' => 'Invalid password'];
    }

    if ($user['approvalStatus'] !== 'Approved') {
        return ['err' => 1, 'msg' => 'Your vendor account is not yet approved'];
    }

    // Update last login
    $DB->sql = "UPDATE mx_vendor_portal_user SET lastLoginAt = ? WHERE userID = ?";
    $DB->vals = [date('Y-m-d H:i:s'), $user['userID']];
    $DB->types = "si";
    $DB->dbQuery();

    // Set session
    $_SESSION[VENDOR_PORTAL_SESSION] = [
        'vendorID' => $user['vendorID'],
        'userID' => $user['userID'],
        'user' => [
            'userID' => $user['userID'],
            'email' => $user['email'],
            'fullName' => $user['fullName']
        ],
        'vendor' => [
            'vendorID' => $user['vendorID'],
            'legalName' => $user['legalName'],
            'vendorCode' => $user['vendorCode']
        ]
    ];

    // Generate CSRF token
    $_SESSION[VENDOR_PORTAL_SESSION][VP_CSRF_TOKEN] = bin2hex(random_bytes(32));

    return ['err' => 0, 'msg' => 'Login successful'];
}

/**
 * Logout vendor
 */
function vpLogout() {
    unset($_SESSION[VENDOR_PORTAL_SESSION]);
    header('Location: ' . VP_BASEURL . '/vendorportal/login');
    exit;
}

/**
 * Get CSRF token
 */
function vpGetCSRFToken() {
    if (!isset($_SESSION[VENDOR_PORTAL_SESSION][VP_CSRF_TOKEN])) {
        $_SESSION[VENDOR_PORTAL_SESSION][VP_CSRF_TOKEN] = bin2hex(random_bytes(32));
    }
    return $_SESSION[VENDOR_PORTAL_SESSION][VP_CSRF_TOKEN];
}

/**
 * Verify CSRF token
 */
function vpVerifyCSRF($token) {
    return isset($_SESSION[VENDOR_PORTAL_SESSION][VP_CSRF_TOKEN]) &&
           hash_equals($_SESSION[VENDOR_PORTAL_SESSION][VP_CSRF_TOKEN], $token);
}

/**
 * Get vendor dashboard stats
 */
function vpGetDashboardStats() {
    global $DB;
    $vendorID = vpGetVendorID();

    // Available RFQs
    $DB->sql = "SELECT COUNT(*) as cnt FROM mx_vendor_rfq
                WHERE rfqStatus = 'Published'
                AND (isPublic = 1 OR FIND_IN_SET(?, invitedVendors))
                AND status = 1";
    $DB->vals = [$vendorID];
    $DB->types = "i";
    $availableRFQs = $DB->dbRow()['cnt'] ?? 0;

    // Submitted quotes
    $DB->sql = "SELECT COUNT(*) as cnt FROM mx_vendor_quote WHERE vendorID = ? AND quoteStatus != 'Draft' AND status = 1";
    $DB->vals = [$vendorID];
    $DB->types = "i";
    $submittedQuotes = $DB->dbRow()['cnt'] ?? 0;

    // Pending (draft) quotes
    $DB->sql = "SELECT COUNT(*) as cnt FROM mx_vendor_quote WHERE vendorID = ? AND quoteStatus = 'Draft' AND status = 1";
    $DB->vals = [$vendorID];
    $DB->types = "i";
    $pendingQuotes = $DB->dbRow()['cnt'] ?? 0;

    // Awarded orders
    $DB->sql = "SELECT COUNT(*) as cnt FROM mx_vendor_quote WHERE vendorID = ? AND quoteStatus = 'Accepted' AND status = 1";
    $DB->vals = [$vendorID];
    $DB->types = "i";
    $awardedOrders = $DB->dbRow()['cnt'] ?? 0;

    return [
        'availableRFQs' => $availableRFQs,
        'submittedQuotes' => $submittedQuotes,
        'pendingQuotes' => $pendingQuotes,
        'awardedOrders' => $awardedOrders
    ];
}

/**
 * Get available RFQs for vendor
 */
function vpGetAvailableRFQs($status = 'Published', $limit = 10) {
    global $DB;
    $vendorID = vpGetVendorID();

    $statusCondition = $status === 'all' ? "IN ('Published', 'Closed')" : "= '$status'";

    $DB->sql = "SELECT r.*, c.categoryName,
                (SELECT COUNT(*) FROM mx_vendor_rfq_item WHERE rfqID = r.rfqID AND status = 1) as itemCount,
                (SELECT quoteID FROM mx_vendor_quote WHERE rfqID = r.rfqID AND vendorID = ? AND status = 1 LIMIT 1) as myQuoteID
                FROM mx_vendor_rfq r
                LEFT JOIN mx_vendor_category c ON r.category = c.categoryID
                WHERE r.rfqStatus $statusCondition
                AND (r.isPublic = 1 OR FIND_IN_SET(?, r.invitedVendors))
                AND r.status = 1
                ORDER BY r.publishDate DESC
                LIMIT $limit";
    $DB->vals = [$vendorID, $vendorID];
    $DB->types = "ii";

    return $DB->dbRows();
}

/**
 * Get RFQ details
 */
function vpGetRFQ($rfqID) {
    global $DB;
    $vendorID = vpGetVendorID();

    $DB->sql = "SELECT r.*, c.categoryName
                FROM mx_vendor_rfq r
                LEFT JOIN mx_vendor_category c ON r.category = c.categoryID
                WHERE r.rfqID = ?
                AND (r.isPublic = 1 OR FIND_IN_SET(?, r.invitedVendors))
                AND r.status = 1";
    $DB->vals = [$rfqID, $vendorID];
    $DB->types = "ii";

    return $DB->dbRow();
}

/**
 * Get RFQ items
 */
function vpGetRFQItems($rfqID) {
    global $DB;

    $DB->sql = "SELECT * FROM mx_vendor_rfq_item WHERE rfqID = ? AND status = 1 ORDER BY sortOrder, itemID";
    $DB->vals = [$rfqID];
    $DB->types = "i";

    return $DB->dbRows();
}

/**
 * Get vendor's quotes
 */
function vpGetMyQuotes($limit = 50) {
    global $DB;
    $vendorID = vpGetVendorID();

    $DB->sql = "SELECT q.*, r.rfqNumber, r.title as rfqTitle
                FROM mx_vendor_quote q
                JOIN mx_vendor_rfq r ON q.rfqID = r.rfqID
                WHERE q.vendorID = ? AND q.status = 1
                ORDER BY q.quoteID DESC
                LIMIT $limit";
    $DB->vals = [$vendorID];
    $DB->types = "i";

    return $DB->dbRows();
}

/**
 * Generate quote number
 */
function vpGenerateQuoteNumber() {
    global $DB;
    $vendorID = vpGetVendorID();
    $vendor = vpGetVendor();

    $prefix = "Q-" . ($vendor['vendorCode'] ?? 'VND') . "-" . date("Ymd") . "-";
    $DB->sql = "SELECT quoteNumber FROM mx_vendor_quote WHERE quoteNumber LIKE ? ORDER BY quoteID DESC LIMIT 1";
    $DB->vals = [$prefix . "%"];
    $DB->types = "s";
    $row = $DB->dbRow();

    if ($row) {
        $lastNum = intval(substr($row["quoteNumber"], -4));
        $newNum = $lastNum + 1;
    } else {
        $newNum = 1;
    }

    return $prefix . str_pad($newNum, 4, "0", STR_PAD_LEFT);
}

/**
 * Clean input
 */
function vpClean($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency
 */
function vpFormatCurrency($amount) {
    return number_format($amount, 2);
}

/**
 * Format date
 */
function vpFormatDate($date, $format = 'd M Y') {
    return $date ? date($format, strtotime($date)) : '-';
}

/**
 * Get status badge HTML
 */
function vpGetStatusBadge($status) {
    $classes = [
        'Draft' => 'status-draft',
        'Submitted' => 'status-submitted',
        'Under Review' => 'status-review',
        'Shortlisted' => 'status-shortlisted',
        'Accepted' => 'status-accepted',
        'Rejected' => 'status-rejected',
        'Published' => 'status-published',
        'Closed' => 'status-closed',
        'Awarded' => 'status-awarded'
    ];

    $class = $classes[$status] ?? 'status-default';
    return '<span class="status-badge ' . $class . '">' . vpClean($status) . '</span>';
}
