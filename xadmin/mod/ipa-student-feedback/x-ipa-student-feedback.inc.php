<?php
/*
IPA Student Feedback Module
Students rate coaches after sessions
*/

/**
 * Generate a secure feedback token
 */
function generateFeedbackToken($sessionID, $playerID, $coachID, $expiryDays = 7) {
    global $DB;

    $sessionID = intval($sessionID);
    $playerID = intval($playerID);
    $coachID = intval($coachID);

    // Generate unique token
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime("+{$expiryDays} days"));

    // Check if feedback entry already exists
    $DB->vals = array($sessionID, $playerID, 1);
    $DB->types = "iii";
    $DB->sql = "SELECT feedbackID, feedbackToken, submittedAt FROM " . $DB->pre . "ipa_student_feedback
                WHERE sessionID=? AND playerID=? AND status=?";
    $existing = $DB->dbRow();

    if ($existing && $existing['submittedAt']) {
        return array('err' => 1, 'msg' => 'Feedback already submitted for this session');
    }

    if ($existing) {
        // Update existing token
        $DB->vals = array($token, $expiry, $existing['feedbackID']);
        $DB->types = "ssi";
        $DB->sql = "UPDATE " . $DB->pre . "ipa_student_feedback
                    SET feedbackToken=?, tokenExpiry=? WHERE feedbackID=?";
        $DB->dbQuery();
        $feedbackID = $existing['feedbackID'];
    } else {
        // Create new feedback entry with token
        $DB->vals = array($token, $expiry, $sessionID, $playerID, $coachID, date('Y-m-d'));
        $DB->types = "ssiiis";
        $DB->sql = "INSERT INTO " . $DB->pre . "ipa_student_feedback
                    (feedbackToken, tokenExpiry, sessionID, playerID, coachID, feedbackDate)
                    VALUES (?, ?, ?, ?, ?, ?)";
        $DB->dbQuery();
        $feedbackID = $DB->insertID;
    }

    // Build feedback URL
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    $baseUrl .= "://" . $_SERVER['HTTP_HOST'];
    $feedbackUrl = $baseUrl . "/bes/studentfeedback/?token=" . $token;

    return array('err' => 0, 'token' => $token, 'url' => $feedbackUrl, 'feedbackID' => $feedbackID);
}

if (isset($_POST["xAction"])) {
    ob_start();
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    ob_end_clean();

    mxCheckRequest(true, true);

    $xAction = $_POST["xAction"];

    if ($xAction == "ADD") {
        $sessionID = intval($_POST["sessionID"] ?? 0);
        $playerID = intval($_POST["playerID"] ?? 0);
        $coachID = intval($_POST["coachID"] ?? 0);
        $overallRating = floatval($_POST["overallRating"] ?? 0);
        $technicalSkillsRating = floatval($_POST["technicalSkillsRating"] ?? 0);
        $communicationRating = floatval($_POST["communicationRating"] ?? 0);
        $punctualityRating = floatval($_POST["punctualityRating"] ?? 0);
        $engagementRating = floatval($_POST["engagementRating"] ?? 0);
        $feedbackComments = trim($_POST["feedbackComments"] ?? "");
        $improvementSuggestions = trim($_POST["improvementSuggestions"] ?? "");
        $feedbackDate = $_POST["feedbackDate"] ?? date("Y-m-d");

        // Check if feedback already exists
        $DB->vals = array($sessionID, $playerID);
        $DB->types = "ii";
        $DB->sql = "SELECT feedbackID FROM " . $DB->pre . "ipa_student_feedback WHERE sessionID=? AND playerID=? AND status=1";
        $existing = $DB->dbRow();

        if ($existing) {
            header('Content-Type: application/json');
            echo json_encode(array("err" => 1, "msg" => "Feedback already submitted for this session"));
            exit;
        }

        $DB->vals = array($sessionID, $playerID, $coachID, $overallRating, $technicalSkillsRating, $communicationRating, $punctualityRating, $engagementRating, $feedbackComments, $improvementSuggestions, $feedbackDate);
        $DB->types = "iiidddddss" . "s";
        $DB->sql = "INSERT INTO " . $DB->pre . "ipa_student_feedback
                    (sessionID, playerID, coachID, overallRating, technicalSkillsRating, communicationRating, punctualityRating, engagementRating, feedbackComments, improvementSuggestions, feedbackDate)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $DB->dbQuery();
        $feedbackID = $DB->insertID;

        // Update coach's average rating
        updateCoachAverageRating($coachID);

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "msg" => "Feedback submitted successfully", "id" => $feedbackID));
        exit;
    }

    if ($xAction == "UPDATE") {
        $feedbackID = intval($_POST["feedbackID"] ?? 0);
        $overallRating = floatval($_POST["overallRating"] ?? 0);
        $technicalSkillsRating = floatval($_POST["technicalSkillsRating"] ?? 0);
        $communicationRating = floatval($_POST["communicationRating"] ?? 0);
        $punctualityRating = floatval($_POST["punctualityRating"] ?? 0);
        $engagementRating = floatval($_POST["engagementRating"] ?? 0);
        $feedbackComments = trim($_POST["feedbackComments"] ?? "");
        $improvementSuggestions = trim($_POST["improvementSuggestions"] ?? "");

        // Get coachID for later update
        $DB->vals = array($feedbackID);
        $DB->types = "i";
        $DB->sql = "SELECT coachID FROM " . $DB->pre . "ipa_student_feedback WHERE feedbackID=?";
        $row = $DB->dbRow();
        $coachID = $row["coachID"] ?? 0;

        $DB->vals = array($overallRating, $technicalSkillsRating, $communicationRating, $punctualityRating, $engagementRating, $feedbackComments, $improvementSuggestions, $feedbackID);
        $DB->types = "dddddss" . "i";
        $DB->sql = "UPDATE " . $DB->pre . "ipa_student_feedback
                    SET overallRating=?, technicalSkillsRating=?, communicationRating=?, punctualityRating=?, engagementRating=?, feedbackComments=?, improvementSuggestions=?
                    WHERE feedbackID=?";
        $DB->dbQuery();

        if ($coachID > 0) {
            updateCoachAverageRating($coachID);
        }

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "msg" => "Feedback updated successfully"));
        exit;
    }

    if ($xAction == "DELETE") {
        $feedbackID = intval($_POST["feedbackID"] ?? 0);

        // Get coachID for later update
        $DB->vals = array($feedbackID);
        $DB->types = "i";
        $DB->sql = "SELECT coachID FROM " . $DB->pre . "ipa_student_feedback WHERE feedbackID=?";
        $row = $DB->dbRow();
        $coachID = $row["coachID"] ?? 0;

        $DB->vals = array(0, $feedbackID);
        $DB->types = "ii";
        $DB->sql = "UPDATE " . $DB->pre . "ipa_student_feedback SET status=? WHERE feedbackID=?";
        $DB->dbQuery();

        if ($coachID > 0) {
            updateCoachAverageRating($coachID);
        }

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "msg" => "Feedback deleted successfully"));
        exit;
    }

    if ($xAction == "GENERATE_LINK") {
        $sessionID = intval($_POST["sessionID"] ?? 0);
        $playerID = intval($_POST["playerID"] ?? 0);
        $coachID = intval($_POST["coachID"] ?? 0);

        if ($sessionID <= 0 || $playerID <= 0 || $coachID <= 0) {
            header('Content-Type: application/json');
            echo json_encode(array("err" => 1, "msg" => "Missing required parameters"));
            exit;
        }

        $result = generateFeedbackToken($sessionID, $playerID, $coachID);

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    if ($xAction == "GENERATE_BULK_LINKS") {
        $sessionID = intval($_POST["sessionID"] ?? 0);

        if ($sessionID <= 0) {
            header('Content-Type: application/json');
            echo json_encode(array("err" => 1, "msg" => "Session ID required"));
            exit;
        }

        // Get session details
        $DB->vals = array($sessionID, 1);
        $DB->types = "ii";
        $DB->sql = "SELECT s.*, c.firstName as coachFirstName, c.lastName as coachLastName
                    FROM " . $DB->pre . "ipa_session s
                    LEFT JOIN " . $DB->pre . "ipa_coach c ON s.coachID = c.coachID
                    WHERE s.sessionID=? AND s.status=?";
        $session = $DB->dbRow();

        if (!$session) {
            header('Content-Type: application/json');
            echo json_encode(array("err" => 1, "msg" => "Session not found"));
            exit;
        }

        // Get all participants for this session
        $DB->vals = array($sessionID, 1);
        $DB->types = "ii";
        $DB->sql = "SELECT sp.playerID, p.firstName, p.lastName, p.phone, p.email
                    FROM " . $DB->pre . "ipa_session_participant sp
                    LEFT JOIN " . $DB->pre . "ipa_player p ON sp.playerID = p.playerID
                    WHERE sp.sessionID=? AND sp.status=?";
        $DB->dbRows();
        $participants = $DB->rows ?: array();

        if (empty($participants)) {
            header('Content-Type: application/json');
            echo json_encode(array("err" => 1, "msg" => "No participants found for this session"));
            exit;
        }

        $links = array();
        foreach ($participants as $p) {
            $result = generateFeedbackToken($sessionID, $p['playerID'], $session['coachID']);
            if ($result['err'] == 0) {
                $links[] = array(
                    'playerID' => $p['playerID'],
                    'playerName' => trim($p['firstName'] . ' ' . ($p['lastName'] ?? '')),
                    'phone' => $p['phone'],
                    'email' => $p['email'],
                    'url' => $result['url']
                );
            }
        }

        // Mark session as feedback sent
        $DB->vals = array(1, $sessionID);
        $DB->types = "ii";
        $DB->sql = "UPDATE " . $DB->pre . "ipa_session SET studentFeedbackSent=? WHERE sessionID=?";
        $DB->dbQuery();

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "links" => $links, "count" => count($links)));
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode(array("err" => 1, "msg" => "Invalid action"));
    exit;
} else {
    if (function_exists("setModVars")) {
        setModVars(array("TBL" => "ipa_student_feedback", "PK" => "feedbackID"));
    }
}

function updateCoachAverageRating($coachID) {
    global $DB;

    $DB->vals = array($coachID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT AVG(overallRating) as avgRating, COUNT(*) as totalFeedback
                FROM " . $DB->pre . "ipa_student_feedback
                WHERE coachID=? AND status=?";
    $stats = $DB->dbRow();

    $avgRating = round($stats["avgRating"] ?? 0, 2);

    $DB->vals = array($avgRating, $coachID);
    $DB->types = "di";
    $DB->sql = "UPDATE " . $DB->pre . "ipa_coach SET avgStudentRating=? WHERE coachID=?";
    $DB->dbQuery();
}
