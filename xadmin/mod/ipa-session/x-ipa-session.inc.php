<?php
/*
addSession = To save Session data.
updateSession = To update Session data.
generateSessionCode = Generate unique session code.
addParticipant = Add participant to session.
markAttendance = Mark participant attendance.
sendSessionFeedbackEmails = Send feedback request emails to all participants.
*/

/**
 * Send feedback request emails to all session participants
 */
function sendSessionFeedbackEmails($sessionID)
{
    global $DB;

    $sessionID = intval($sessionID);
    $emailsSent = 0;
    $errors = array();

    // Get session details with coach info
    $DB->vals = array($sessionID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT s.*,
                       CONCAT(c.firstName, ' ', IFNULL(c.lastName,'')) as coachName,
                       pr.programName
                FROM " . $DB->pre . "ipa_session s
                LEFT JOIN " . $DB->pre . "ipa_coach c ON s.coachID = c.coachID
                LEFT JOIN " . $DB->pre . "ipa_program pr ON s.programID = pr.programID
                WHERE s.sessionID=? AND s.status=?";
    $session = $DB->dbRow();

    if (!$session) {
        return array('sent' => 0, 'errors' => array('Session not found'));
    }

    // Get all participants with email addresses
    $DB->vals = array($sessionID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT sp.playerID, p.firstName, p.lastName, p.email, p.phone
                FROM " . $DB->pre . "ipa_session_participant sp
                LEFT JOIN " . $DB->pre . "ipa_player p ON sp.playerID = p.playerID
                WHERE sp.sessionID=? AND sp.status=?";
    $DB->dbRows();
    $participants = $DB->rows ?: array();

    if (empty($participants)) {
        return array('sent' => 0, 'errors' => array('No participants found'));
    }

    // Include feedback module for token generation
    require_once(__DIR__ . "/../ipa-student-feedback/x-ipa-student-feedback.inc.php");

    foreach ($participants as $p) {
        $playerName = trim($p['firstName'] . ' ' . ($p['lastName'] ?? ''));
        $playerEmail = $p['email'];

        if (empty($playerEmail) || !filter_var($playerEmail, FILTER_VALIDATE_EMAIL)) {
            continue;
        }

        // Generate feedback token
        $result = generateFeedbackToken($sessionID, $p['playerID'], $session['coachID']);

        if ($result['err'] != 0) {
            $errors[] = "Token failed for $playerName";
            continue;
        }

        // Send email
        $sent = sendFeedbackRequestEmail(
            $playerEmail,
            $playerName,
            $session['coachName'],
            $session['sessionDate'],
            $session['startTime'],
            $session['programName'] ?? 'Training Session',
            $result['url']
        );

        if ($sent) {
            $emailsSent++;
        } else {
            $errors[] = "Email failed for $playerName";
        }
    }

    // Mark session as feedback sent
    if ($emailsSent > 0) {
        $DB->vals = array(1, $sessionID);
        $DB->types = "ii";
        $DB->sql = "UPDATE " . $DB->pre . "ipa_session SET studentFeedbackSent=? WHERE sessionID=?";
        $DB->dbQuery();
    }

    return array('sent' => $emailsSent, 'errors' => $errors);
}

/**
 * Send individual feedback request email
 */
function sendFeedbackRequestEmail($email, $playerName, $coachName, $sessionDate, $sessionTime, $programName, $feedbackUrl)
{
    $formattedDate = date('l, F j, Y', strtotime($sessionDate));
    $formattedTime = date('g:i A', strtotime($sessionTime));

    $subject = "Share your feedback - Indian Padel Academy";

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
                    <tr>
                        <td style="background: linear-gradient(135deg, #1a3a2f, #2d5a47); padding:32px 40px; text-align:center;">
                            <div style="width:56px; height:56px; background:#c9a227; border-radius:14px; display:inline-block; line-height:56px; margin-bottom:12px;">
                                <span style="color:#1a3a2f; font-size:28px; font-weight:bold;">IPA</span>
                            </div>
                            <h1 style="color:#ffffff; margin:0; font-size:24px; font-weight:400;">Indian Padel Academy</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:40px;">
                            <h2 style="color:#1a3a2f; margin:0 0 8px 0; font-size:28px; font-weight:400;">How was your session?</h2>
                            <p style="color:#666666; margin:0 0 32px 0; font-size:16px; line-height:1.6;">
                                Hi ' . htmlspecialchars($playerName) . ', we hope you enjoyed your recent training session. Your feedback helps us improve!
                            </p>
                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#fdfcf7; border-radius:12px; margin-bottom:32px;">
                                <tr>
                                    <td style="padding:24px;">
                                        <p style="margin:0 0 16px 0; color:#888888; font-size:12px; text-transform:uppercase; letter-spacing:1px; border-bottom:1px solid #e8e8e8; padding-bottom:12px;">Session Details</p>
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="50%" style="padding:8px 0;">
                                                    <p style="margin:0; color:#888888; font-size:13px;">Coach</p>
                                                    <p style="margin:4px 0 0 0; color:#1a3a2f; font-size:16px; font-weight:600;">' . htmlspecialchars($coachName) . '</p>
                                                </td>
                                                <td width="50%" style="padding:8px 0;">
                                                    <p style="margin:0; color:#888888; font-size:13px;">Program</p>
                                                    <p style="margin:4px 0 0 0; color:#1a3a2f; font-size:16px; font-weight:600;">' . htmlspecialchars($programName) . '</p>
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
                                This link will expire in 7 days. Your feedback helps us provide better training.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#fdfcf7; padding:24px 40px; border-top:1px solid #e8e8e8;">
                            <p style="margin:0; color:#888888; font-size:13px; text-align:center;">
                                &copy; ' . date('Y') . ' Indian Padel Academy. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';

    $headers = array();
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-type: text/html; charset=UTF-8";
    $headers[] = "From: Indian Padel Academy <noreply@indianpadelacademy.com>";
    $headers[] = "Reply-To: support@indianpadelacademy.com";
    $headers[] = "X-Mailer: PHP/" . phpversion();

    return @mail($email, $subject, $htmlBody, implode("\r\n", $headers));
}

function generateSessionCode()
{
    global $DB;
    $prefix = "IPA-SES-" . date("Ymd") . "-";
    $DB->sql = "SELECT sessionCode FROM " . $DB->pre . "ipa_session
                WHERE sessionCode LIKE '" . $prefix . "%'
                ORDER BY sessionID DESC LIMIT 1";
    $row = $DB->dbRow();
    $nextNum = 1;
    if ($DB->numRows > 0) {
        $lastNum = intval(substr($row['sessionCode'], -3));
        $nextNum = $lastNum + 1;
    }
    return $prefix . str_pad($nextNum, 3, "0", STR_PAD_LEFT);
}

function addSession()
{
    global $DB;

    // Generate session code if empty
    if (empty($_POST["sessionCode"])) {
        $_POST["sessionCode"] = generateSessionCode();
    }

    $DB->table = $DB->pre . "ipa_session";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        setResponse(array("err" => 0, "param" => "id=" . $DB->insertID));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateSession()
{
    global $DB;
    $sessionID = intval($_POST["sessionID"]);

    // Check if status is changing to Completed
    $newStatus = $_POST["sessionStatus"] ?? "";
    $wasNotCompleted = false;

    if ($newStatus === "Completed") {
        $DB->vals = array($sessionID);
        $DB->types = "i";
        $DB->sql = "SELECT sessionStatus, studentFeedbackSent FROM " . $DB->pre . "ipa_session WHERE sessionID=?";
        $current = $DB->dbRow();
        $wasNotCompleted = ($current && $current["sessionStatus"] !== "Completed" && $current["studentFeedbackSent"] == 0);
    }

    $DB->table = $DB->pre . "ipa_session";
    $DB->data = $_POST;
    if ($DB->dbUpdate("sessionID=?", "i", array($sessionID))) {
        // Update enrolled count
        updateEnrolledCount($sessionID);

        // Auto-send feedback emails if session just completed
        if ($wasNotCompleted && $newStatus === "Completed") {
            $emailResult = sendSessionFeedbackEmails($sessionID);
            setResponse(array("err" => 0, "param" => "id=" . $sessionID, "feedbackEmails" => $emailResult));
        } else {
            setResponse(array("err" => 0, "param" => "id=" . $sessionID));
        }
    } else {
        setResponse(array("err" => 1));
    }
}

function updateEnrolledCount($sessionID)
{
    global $DB;
    $DB->vals = array($sessionID);
    $DB->types = "i";
    $DB->sql = "SELECT COUNT(*) as enrolled, SUM(CASE WHEN attendanceStatus='Attended' THEN 1 ELSE 0 END) as attended
                FROM " . $DB->pre . "ipa_session_participant WHERE sessionID=? AND status=1";
    $counts = $DB->dbRow();

    $DB->vals = array($counts["enrolled"] ?? 0, $counts["attended"] ?? 0, $sessionID);
    $DB->types = "iii";
    $DB->sql = "UPDATE " . $DB->pre . "ipa_session SET enrolledCount=?, attendedCount=? WHERE sessionID=?";
    $DB->dbQuery();
}

function addParticipant()
{
    global $DB;
    $sessionID = intval($_POST["sessionID"]);
    $playerID = intval($_POST["playerID"]);

    // Check if already enrolled
    $DB->vals = array($sessionID, $playerID);
    $DB->types = "ii";
    $DB->sql = "SELECT spID FROM " . $DB->pre . "ipa_session_participant WHERE sessionID=? AND playerID=? AND status=1";
    $existing = $DB->dbRow();
    if ($DB->numRows > 0) {
        setResponse(array("err" => 1, "msg" => "Player already enrolled in this session"));
        return;
    }

    $DB->table = $DB->pre . "ipa_session_participant";
    $DB->data = array(
        "sessionID" => $sessionID,
        "playerID" => $playerID,
        "amountPaid" => floatval($_POST["amountPaid"] ?? 0),
        "attendanceStatus" => "Enrolled"
    );
    if ($DB->dbInsert()) {
        updateEnrolledCount($sessionID);
        setResponse(array("err" => 0, "msg" => "Participant added successfully"));
    } else {
        setResponse(array("err" => 1));
    }
}

function markAttendance()
{
    global $DB;
    $spID = intval($_POST["spID"]);
    $status = $_POST["attendanceStatus"] ?? "Attended";

    $DB->vals = array($status, $status == "Attended" ? date("Y-m-d H:i:s") : null, $spID);
    $DB->types = "ssi";
    $DB->sql = "UPDATE " . $DB->pre . "ipa_session_participant SET attendanceStatus=?, checkInTime=? WHERE spID=?";
    if ($DB->dbQuery()) {
        // Get sessionID and update count
        $DB->vals = array($spID);
        $DB->types = "i";
        $DB->sql = "SELECT sessionID FROM " . $DB->pre . "ipa_session_participant WHERE spID=?";
        $row = $DB->dbRow();
        if ($row) {
            updateEnrolledCount($row["sessionID"]);
        }
        setResponse(array("err" => 0, "msg" => "Attendance marked"));
    } else {
        setResponse(array("err" => 1));
    }
}

function getPlayers()
{
    global $DB;
    $search = $_POST["search"] ?? "";

    $DB->vals = array("%" . $search . "%", "%" . $search . "%", "%" . $search . "%");
    $DB->types = "sss";
    $DB->sql = "SELECT playerID, playerCode, CONCAT(firstName, ' ', IFNULL(lastName,'')) as playerName, phone
                FROM " . $DB->pre . "ipa_player
                WHERE status=1 AND (firstName LIKE ? OR lastName LIKE ? OR playerCode LIKE ?)
                ORDER BY firstName LIMIT 20";
    $players = $DB->dbRows();
    echo json_encode(array("err" => 0, "players" => $players));
    exit;
}

// Handle AJAX actions
if (isset($_POST["xAction"])) {
    ob_start();
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    ob_end_clean();

    header('Content-Type: application/json');

    // Public actions (no auth needed)
    if ($_POST["xAction"] == "GET_PLAYERS") {
        getPlayers();
    }

    $MXRES = mxCheckRequest(true, true);
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addSession(); break;
            case "UPDATE": updateSession(); break;
            case "ADD_PARTICIPANT": addParticipant(); break;
            case "MARK_ATTENDANCE": markAttendance(); break;
        }
    }
    echo json_encode($MXRES);
    exit;
} else {
    if (function_exists("setModVars")) {
        setModVars(array("TBL" => "ipa_session", "PK" => "sessionID"));
    }
}
