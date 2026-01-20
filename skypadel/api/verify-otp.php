<?php
/**
 * Sky Padel Client Portal - Verify OTP API
 */
require_once __DIR__ . '/../core/config.php';

header('Content-Type: application/json');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method']);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');
$otp = trim($input['otp'] ?? '');

if (empty($email) || empty($otp)) {
    jsonResponse(['success' => false, 'message' => 'Email and OTP are required']);
}

if (strlen($otp) !== 6 || !ctype_digit($otp)) {
    jsonResponse(['success' => false, 'message' => 'Invalid OTP format']);
}

$db = getDB();
$prefix = DB_PREFIX;

// Get auth record
$stmt = $db->prepare("SELECT * FROM {$prefix}sky_padel_client_auth WHERE clientEmail = ? AND status = 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$auth = $result->fetch_assoc();

if (!$auth) {
    jsonResponse(['success' => false, 'message' => 'Invalid email or session expired']);
}

// Check if locked
if ($auth['isLocked'] == 1) {
    jsonResponse(['success' => false, 'message' => 'Account is locked. Please try again later.']);
}

// Check OTP expiry
if (strtotime($auth['otpExpiry']) < time()) {
    jsonResponse(['success' => false, 'message' => 'OTP has expired. Please request a new one.']);
}

// Verify OTP
if ($auth['otpCode'] !== $otp) {
    // Increment failed attempts
    $attempts = intval($auth['loginAttempts']) + 1;

    if ($attempts >= MAX_LOGIN_ATTEMPTS) {
        $stmt = $db->prepare("UPDATE {$prefix}sky_padel_client_auth
                              SET loginAttempts = ?, isLocked = 1
                              WHERE clientEmail = ?");
        $stmt->bind_param("is", $attempts, $email);
        $stmt->execute();
        jsonResponse(['success' => false, 'message' => 'Too many failed attempts. Account locked for ' . LOCKOUT_MINUTES . ' minutes.']);
    } else {
        $stmt = $db->prepare("UPDATE {$prefix}sky_padel_client_auth
                              SET loginAttempts = ?
                              WHERE clientEmail = ?");
        $stmt->bind_param("is", $attempts, $email);
        $stmt->execute();
        $remaining = MAX_LOGIN_ATTEMPTS - $attempts;
        jsonResponse(['success' => false, 'message' => "Invalid OTP. {$remaining} attempts remaining."]);
    }
}

// OTP is valid - create session
$token = bin2hex(random_bytes(32));
$tokenExpiry = date('Y-m-d H:i:s', strtotime('+1 day'));

$stmt = $db->prepare("UPDATE {$prefix}sky_padel_client_auth
                      SET otpCode = NULL, otpExpiry = NULL, loginAttempts = 0,
                          authToken = ?, tokenExpiry = ?, lastLogin = NOW()
                      WHERE clientEmail = ?");
$stmt->bind_param("sss", $token, $tokenExpiry, $email);
$stmt->execute();

// Set session
$_SESSION['client_email'] = $email;
$_SESSION['client_token'] = $token;
$_SESSION['last_activity'] = time();

// Log activity
logActivity($email, 'Login');

jsonResponse([
    'success' => true,
    'message' => 'Login successful',
    'redirect' => SITE_URL . '/dashboard.php'
]);
