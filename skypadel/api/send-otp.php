<?php
/**
 * Sky Padel Client Portal - Send OTP API
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

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Please enter a valid email address']);
}

$db = getDB();
$prefix = DB_PREFIX;

// Check if this email has any projects (is a valid client)
$stmt = $db->prepare("
    SELECT l.leadID, l.clientName, l.clientEmail
    FROM {$prefix}sky_padel_lead l
    INNER JOIN {$prefix}sky_padel_project p ON p.leadID = l.leadID
    WHERE l.clientEmail = ? AND l.status = 1 AND p.status = 1
    LIMIT 1
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    jsonResponse(['success' => false, 'message' => 'No active projects found for this email']);
}

$client = $result->fetch_assoc();

// Check if account is locked
$stmt = $db->prepare("SELECT * FROM {$prefix}sky_padel_client_auth WHERE clientEmail = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$authResult = $stmt->get_result();
$auth = $authResult->fetch_assoc();

if ($auth && $auth['isLocked'] == 1) {
    // Check if lockout period has passed
    $lockedUntil = strtotime($auth['modified']) + (LOCKOUT_MINUTES * 60);
    if (time() < $lockedUntil) {
        $remainingMins = ceil(($lockedUntil - time()) / 60);
        jsonResponse(['success' => false, 'message' => "Account is locked. Try again in {$remainingMins} minutes."]);
    } else {
        // Unlock the account
        $stmt = $db->prepare("UPDATE {$prefix}sky_padel_client_auth SET isLocked = 0, loginAttempts = 0 WHERE clientEmail = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
    }
}

// Generate 6-digit OTP
$otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$otpExpiry = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));

// Save or update auth record
if ($auth) {
    $stmt = $db->prepare("UPDATE {$prefix}sky_padel_client_auth
                          SET otpCode = ?, otpExpiry = ?, modified = NOW()
                          WHERE clientEmail = ?");
    $stmt->bind_param("sss", $otp, $otpExpiry, $email);
} else {
    $stmt = $db->prepare("INSERT INTO {$prefix}sky_padel_client_auth
                          (clientEmail, otpCode, otpExpiry)
                          VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $otp, $otpExpiry);
}
$stmt->execute();

// In production, send OTP via email/SMS
// For now, we'll just log it and return success
// TODO: Integrate with email service

// Log the OTP for development (remove in production)
error_log("Sky Padel OTP for {$email}: {$otp}");

// For development, also store in a temp file
file_put_contents(__DIR__ . '/../temp_otp.txt', "Email: {$email}\nOTP: {$otp}\nExpires: {$otpExpiry}\n");

jsonResponse([
    'success' => true,
    'message' => 'OTP sent to your email',
    'debug_otp' => $otp // Remove in production!
]);
