<?php
/**
 * IPA Student Feedback - Auto Email Cron
 * Runs periodically to send feedback request emails for completed sessions
 *
 * Setup: Add to cron/task scheduler to run every 30 minutes or hourly
 * Example: */30 * * * * php /path/to/cron-send-feedback.php
 */

// Allow CLI or direct access with secret key
$allowedKey = 'ipa_feedback_cron_2024';
$isCLI = (php_sapi_name() === 'cli');
$hasValidKey = isset($_GET['key']) && $_GET['key'] === $allowedKey;

if (!$isCLI && !$hasValidKey) {
    http_response_code(403);
    die('Access denied');
}

require_once(__DIR__ . "/../../../core/core.inc.php");

// Include the feedback functions
require_once(__DIR__ . "/x-ipa-student-feedback.inc.php");

/**
 * Send feedback request email to a student
 */
function sendFeedbackEmail($playerEmail, $playerName, $coachName, $sessionDate, $sessionTime, $programName, $feedbackUrl) {
    global $DB;

    if (empty($playerEmail) || !filter_var($playerEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $formattedDate = date('l, F j, Y', strtotime($sessionDate));
    $formattedTime = date('g:i A', strtotime($sessionTime));

    $subject = "How was your session? Share your feedback - Indian Padel Academy";

    $htmlBody = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#f5f5f5; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f5f5; padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.08);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #1a3a2f, #2d5a47); padding:32px 40px; text-align:center;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <div style="width:56px; height:56px; background:#c9a227; border-radius:14px; display:inline-block; line-height:56px; margin-bottom:12px;">
                                            <span style="color:#1a3a2f; font-size:28px; font-weight:bold;">IPA</span>
                                        </div>
                                        <h1 style="color:#ffffff; margin:0; font-size:24px; font-weight:400; font-family:Georgia, serif;">Indian Padel Academy</h1>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding:40px;">
                            <h2 style="color:#1a3a2f; margin:0 0 8px 0; font-size:28px; font-weight:400; font-family:Georgia, serif;">How was your session?</h2>
                            <p style="color:#666666; margin:0 0 32px 0; font-size:16px; line-height:1.6;">
                                Hi ' . htmlspecialchars($playerName) . ', we hope you enjoyed your recent training session. Your feedback helps us improve!
                            </p>

                            <!-- Session Details Card -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#fdfcf7; border-radius:12px; margin-bottom:32px;">
                                <tr>
                                    <td style="padding:24px;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding-bottom:16px; border-bottom:1px solid #e8e8e8;">
                                                    <p style="margin:0; color:#888888; font-size:12px; text-transform:uppercase; letter-spacing:1px;">Session Details</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top:16px;">
                                                    <table width="100%" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td width="50%" style="padding:8px 0;">
                                                                <p style="margin:0; color:#888888; font-size:13px;">Coach</p>
                                                                <p style="margin:4px 0 0 0; color:#1a3a2f; font-size:16px; font-weight:600;">' . htmlspecialchars($coachName) . '</p>
                                                            </td>
                                                            <td width="50%" style="padding:8px 0;">
                                                                <p style="margin:0; color:#888888; font-size:13px;">Program</p>
                                                                <p style="margin:4px 0 0 0; color:#1a3a2f; font-size:16px; font-weight:600;">' . htmlspecialchars($programName ?: 'Training Session') . '</p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td width="50%" style="padding:8px 0;">
                                                                <p style="margin:0; color:#888888; font-size:13px;">Date</p>
                                                                <p style="margin:4px 0 0 0; color:#1a3a2f; font-size:16px; font-weight:600;">' . $formattedDate . '</p>
                                                            </td>
                                                            <td width="50%" style="padding:8px 0;">
                                                                <p style="margin:0; color:#888888; font-size:13px;">Time</p>
                                                                <p style="margin:4px 0 0 0; color:#1a3a2f; font-size:16px; font-weight:600;">' . $formattedTime . '</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <a href="' . $feedbackUrl . '" style="display:inline-block; background:linear-gradient(135deg, #1a3a2f, #2d5a47); color:#ffffff; text-decoration:none; padding:16px 48px; border-radius:12px; font-size:16px; font-weight:600; box-shadow:0 4px 16px rgba(26,58,47,0.3);">
                                            Share Your Feedback
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="color:#888888; margin:32px 0 0 0; font-size:14px; text-align:center; line-height:1.6;">
                                This link will expire in 7 days. Your feedback is anonymous and helps us provide better training.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#fdfcf7; padding:24px 40px; border-top:1px solid #e8e8e8;">
                            <p style="margin:0; color:#888888; font-size:13px; text-align:center;">
                                &copy; ' . date('Y') . ' Indian Padel Academy. All rights reserved.<br>
                                <a href="#" style="color:#1a3a2f; text-decoration:none;">Unsubscribe</a> from feedback requests
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';

    // Plain text version
    $textBody = "Hi $playerName,\n\n";
    $textBody .= "How was your session at Indian Padel Academy?\n\n";
    $textBody .= "Session Details:\n";
    $textBody .= "- Coach: $coachName\n";
    $textBody .= "- Date: $formattedDate\n";
    $textBody .= "- Time: $formattedTime\n";
    $textBody .= "- Program: " . ($programName ?: 'Training Session') . "\n\n";
    $textBody .= "Share your feedback: $feedbackUrl\n\n";
    $textBody .= "This link expires in 7 days.\n\n";
    $textBody .= "Thank you,\nIndian Padel Academy";

    // Send email using PHP mail or your preferred method
    $headers = array();
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-type: text/html; charset=UTF-8";
    $headers[] = "From: Indian Padel Academy <noreply@indianpadelacademy.com>";
    $headers[] = "Reply-To: support@indianpadelacademy.com";
    $headers[] = "X-Mailer: PHP/" . phpversion();

    $result = @mail($playerEmail, $subject, $htmlBody, implode("\r\n", $headers));

    // Log email attempt
    logFeedbackEmail($playerEmail, $playerName, $result ? 'sent' : 'failed');

    return $result;
}

/**
 * Log email sending attempts
 */
function logFeedbackEmail($email, $name, $status) {
    $logFile = __DIR__ . '/feedback-email.log';
    $logEntry = date('Y-m-d H:i:s') . " | $status | $email | $name\n";
    @file_put_contents($logFile, $logEntry, FILE_APPEND);
}

/**
 * Process completed sessions and send feedback emails
 */
function processCompletedSessions() {
    global $DB;

    $processed = 0;
    $emailsSent = 0;

    // Get completed sessions from the last 24 hours that haven't had feedback sent
    $DB->vals = array('Completed', 0, 1);
    $DB->types = "sii";
    $DB->sql = "SELECT s.sessionID, s.sessionCode, s.sessionDate, s.startTime, s.coachID,
                       CONCAT(c.firstName, ' ', IFNULL(c.lastName,'')) as coachName,
                       pr.programName
                FROM " . $DB->pre . "ipa_session s
                LEFT JOIN " . $DB->pre . "ipa_coach c ON s.coachID = c.coachID
                LEFT JOIN " . $DB->pre . "ipa_program pr ON s.programID = pr.programID
                WHERE s.sessionStatus=?
                AND s.studentFeedbackSent=?
                AND s.status=?
                AND s.sessionDate >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                ORDER BY s.sessionDate DESC";
    $DB->dbRows();
    $sessions = $DB->rows ?: array();

    echo "Found " . count($sessions) . " sessions to process\n";

    foreach ($sessions as $session) {
        echo "Processing session: " . $session['sessionCode'] . "\n";

        // Get participants for this session
        $DB->vals = array($session['sessionID'], 1);
        $DB->types = "ii";
        $DB->sql = "SELECT sp.playerID, p.firstName, p.lastName, p.email, p.phone
                    FROM " . $DB->pre . "ipa_session_participant sp
                    LEFT JOIN " . $DB->pre . "ipa_player p ON sp.playerID = p.playerID
                    WHERE sp.sessionID=? AND sp.status=?";
        $DB->dbRows();
        $participants = $DB->rows ?: array();

        if (empty($participants)) {
            echo "  - No participants found, skipping\n";
            continue;
        }

        $sessionEmailsSent = 0;

        foreach ($participants as $participant) {
            $playerName = trim($participant['firstName'] . ' ' . ($participant['lastName'] ?? ''));
            $playerEmail = $participant['email'];

            if (empty($playerEmail)) {
                echo "  - No email for $playerName, skipping\n";
                continue;
            }

            // Generate feedback token
            $result = generateFeedbackToken(
                $session['sessionID'],
                $participant['playerID'],
                $session['coachID']
            );

            if ($result['err'] != 0) {
                echo "  - Token generation failed for $playerName: " . ($result['msg'] ?? 'Unknown error') . "\n";
                continue;
            }

            // Send email
            $emailSent = sendFeedbackEmail(
                $playerEmail,
                $playerName,
                $session['coachName'],
                $session['sessionDate'],
                $session['startTime'],
                $session['programName'],
                $result['url']
            );

            if ($emailSent) {
                echo "  - Email sent to $playerName ($playerEmail)\n";
                $sessionEmailsSent++;
                $emailsSent++;
            } else {
                echo "  - Failed to send email to $playerName ($playerEmail)\n";
            }
        }

        // Mark session as feedback sent if at least one email was sent
        if ($sessionEmailsSent > 0) {
            $DB->vals = array(1, $session['sessionID']);
            $DB->types = "ii";
            $DB->sql = "UPDATE " . $DB->pre . "ipa_session SET studentFeedbackSent=? WHERE sessionID=?";
            $DB->dbQuery();
            $processed++;
        }
    }

    echo "\nComplete: $processed sessions processed, $emailsSent emails sent\n";

    return array('sessions' => $processed, 'emails' => $emailsSent);
}

// Run the cron
echo "=== IPA Feedback Email Cron ===\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";

$result = processCompletedSessions();

echo "\nFinished at: " . date('Y-m-d H:i:s') . "\n";
