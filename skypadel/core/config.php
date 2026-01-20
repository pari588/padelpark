<?php
/**
 * Sky Padel Client Portal - Configuration
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bombayengg');
define('DB_PREFIX', 'mx_');

// Site Configuration
define('SITE_NAME', 'Sky Padel India');
define('SITE_URL', 'http://localhost/bes/skypadel');
define('ADMIN_URL', 'http://localhost/bes/xadmin');

// OTP Settings
define('OTP_EXPIRY_MINUTES', 10);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_MINUTES', 30);

// Session timeout (in seconds)
define('SESSION_TIMEOUT', 3600); // 1 hour

// Database Connection
function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
    }
    return $conn;
}

// Helper Functions
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: " . $url);
    exit;
}

function jsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['client_email']) && !empty($_SESSION['client_email']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect(SITE_URL . '/index.php');
    }
    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_destroy();
        redirect(SITE_URL . '/index.php?msg=timeout');
    }
    $_SESSION['last_activity'] = time();
}

function getClientEmail() {
    return $_SESSION['client_email'] ?? '';
}

function formatDate($date) {
    if (empty($date) || $date == '0000-00-00') return '-';
    return date('d M Y', strtotime($date));
}

function formatMoney($amount) {
    return '₹' . number_format((float)$amount, 2);
}

function getClientProjects($email) {
    $db = getDB();
    $prefix = DB_PREFIX;

    $stmt = $db->prepare("
        SELECT p.*,
               l.clientName, l.clientEmail, l.clientPhone,
               CONCAT_WS(', ', l.siteCity, l.siteState) as projectLocation,
               (SELECT COUNT(*) FROM {$prefix}sky_padel_milestone m WHERE m.projectID = p.projectID AND m.status = 1) as totalMilestones,
               (SELECT COUNT(*) FROM {$prefix}sky_padel_milestone m WHERE m.projectID = p.projectID AND m.milestoneStatus = 'Completed' AND m.status = 1) as completedMilestones
        FROM {$prefix}sky_padel_project p
        LEFT JOIN {$prefix}sky_padel_lead l ON p.leadID = l.leadID
        WHERE l.clientEmail = ? AND p.status = 1
        ORDER BY p.created DESC
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function logActivity($email, $type, $entityType = null, $entityID = null) {
    $db = getDB();
    $prefix = DB_PREFIX;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $stmt = $db->prepare("INSERT INTO {$prefix}sky_padel_client_activity
                          (clientEmail, activityType, entityType, entityID, ipAddress, userAgent)
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiss", $email, $type, $entityType, $entityID, $ip, $ua);
    $stmt->execute();
}

/**
 * Get contracts for a client by email
 */
function getClientContracts($email) {
    $db = getDB();
    $prefix = DB_PREFIX;

    $stmt = $db->prepare("
        SELECT c.*, q.quotationNo, q.courtConfiguration,
               l.clientName, l.clientEmail, l.clientPhone,
               CONCAT_WS(', ', l.siteCity, l.siteState) as projectLocation
        FROM {$prefix}sky_padel_contract c
        INNER JOIN {$prefix}sky_padel_quotation q ON c.quotationID = q.quotationID
        INNER JOIN {$prefix}sky_padel_lead l ON c.leadID = l.leadID
        WHERE l.clientEmail = ? AND c.status = 1
        ORDER BY c.created DESC
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get a single contract with verification
 */
function getClientContract($contractID, $email) {
    $db = getDB();
    $prefix = DB_PREFIX;

    $stmt = $db->prepare("
        SELECT c.*, q.quotationNo, q.quotationDate, q.courtConfiguration, q.validUntil,
               l.clientName, l.clientEmail, l.clientPhone, l.siteAddress, l.siteCity, l.siteState
        FROM {$prefix}sky_padel_contract c
        INNER JOIN {$prefix}sky_padel_quotation q ON c.quotationID = q.quotationID
        INNER JOIN {$prefix}sky_padel_lead l ON c.leadID = l.leadID
        WHERE c.contractID = ? AND l.clientEmail = ? AND c.status = 1
    ");
    $stmt->bind_param("is", $contractID, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Get contract milestones
 */
function getContractMilestones($contractID) {
    $db = getDB();
    $prefix = DB_PREFIX;

    $stmt = $db->prepare("
        SELECT * FROM {$prefix}sky_padel_contract_milestone
        WHERE contractID = ?
        ORDER BY sortOrder ASC
    ");
    $stmt->bind_param("i", $contractID);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}
