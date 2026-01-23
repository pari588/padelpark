<?php
/*
IPA Coach Session Feedback Module
Quick per-session skill feedback from coach to students
*/

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

        // Skill ratings (1-5 scale)
        $forehandRating = intval($_POST["forehandRating"] ?? 0);
        $backhandRating = intval($_POST["backhandRating"] ?? 0);
        $serveRating = intval($_POST["serveRating"] ?? 0);
        $volleyRating = intval($_POST["volleyRating"] ?? 0);
        $footworkRating = intval($_POST["footworkRating"] ?? 0);
        $gameAwarenessRating = intval($_POST["gameAwarenessRating"] ?? 0);

        // Calculate overall (average of all ratings)
        $ratingCount = 0;
        $ratingSum = 0;
        foreach ([$forehandRating, $backhandRating, $serveRating, $volleyRating, $footworkRating, $gameAwarenessRating] as $r) {
            if ($r > 0) {
                $ratingSum += $r;
                $ratingCount++;
            }
        }
        $overallRating = $ratingCount > 0 ? round($ratingSum / $ratingCount, 2) : 0;

        $sessionNotes = trim($_POST["sessionNotes"] ?? "");
        $areasToWork = trim($_POST["areasToWork"] ?? "");
        $progressStatus = $_POST["progressStatus"] ?? "Good";

        // Check if feedback already exists for this session/player
        $DB->vals = array($sessionID, $playerID);
        $DB->types = "ii";
        $DB->sql = "SELECT feedbackID FROM " . $DB->pre . "ipa_coach_session_feedback WHERE sessionID=? AND playerID=?";
        $DB->dbQuery();

        if ($DB->numRows > 0) {
            // Update existing
            $existing = $DB->dbFetch();
            $feedbackID = $existing["feedbackID"];

            $DB->vals = array($forehandRating, $backhandRating, $serveRating, $volleyRating, $footworkRating, $gameAwarenessRating, $overallRating, $sessionNotes, $areasToWork, $progressStatus, $feedbackID);
            $DB->types = "iiiiiidsss" . "i";
            $DB->sql = "UPDATE " . $DB->pre . "ipa_coach_session_feedback
                        SET forehandRating=?, backhandRating=?, serveRating=?, volleyRating=?, footworkRating=?, gameAwarenessRating=?, overallRating=?, sessionNotes=?, areasToWork=?, progressStatus=?, feedbackDate=NOW()
                        WHERE feedbackID=?";
            $DB->dbQuery();

            header('Content-Type: application/json');
            echo json_encode(array("err" => 0, "msg" => "Feedback updated successfully", "id" => $feedbackID));
            exit;
        }

        // Insert new
        $DB->vals = array($sessionID, $playerID, $coachID, $forehandRating, $backhandRating, $serveRating, $volleyRating, $footworkRating, $gameAwarenessRating, $overallRating, $sessionNotes, $areasToWork, $progressStatus);
        $DB->types = "iiiiiiiiidss" . "s";
        $DB->sql = "INSERT INTO " . $DB->pre . "ipa_coach_session_feedback
                    (sessionID, playerID, coachID, forehandRating, backhandRating, serveRating, volleyRating, footworkRating, gameAwarenessRating, overallRating, sessionNotes, areasToWork, progressStatus)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $DB->dbQuery();
        $feedbackID = $DB->insertID;

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "msg" => "Feedback saved successfully", "id" => $feedbackID));
        exit;
    }

    if ($xAction == "BULK_ADD") {
        $sessionID = intval($_POST["sessionID"] ?? 0);
        $coachID = intval($_POST["coachID"] ?? 0);
        $feedbacks = json_decode($_POST["feedbacks"] ?? "[]", true);

        if (!is_array($feedbacks) || empty($feedbacks)) {
            header('Content-Type: application/json');
            echo json_encode(array("err" => 1, "msg" => "No feedback data provided"));
            exit;
        }

        $savedCount = 0;
        foreach ($feedbacks as $fb) {
            $playerID = intval($fb["playerID"] ?? 0);
            if ($playerID < 1) continue;

            $forehandRating = intval($fb["forehandRating"] ?? 0);
            $backhandRating = intval($fb["backhandRating"] ?? 0);
            $serveRating = intval($fb["serveRating"] ?? 0);
            $volleyRating = intval($fb["volleyRating"] ?? 0);
            $footworkRating = intval($fb["footworkRating"] ?? 0);
            $gameAwarenessRating = intval($fb["gameAwarenessRating"] ?? 0);

            $ratingCount = 0;
            $ratingSum = 0;
            foreach ([$forehandRating, $backhandRating, $serveRating, $volleyRating, $footworkRating, $gameAwarenessRating] as $r) {
                if ($r > 0) {
                    $ratingSum += $r;
                    $ratingCount++;
                }
            }
            $overallRating = $ratingCount > 0 ? round($ratingSum / $ratingCount, 2) : 0;

            $sessionNotes = trim($fb["sessionNotes"] ?? "");
            $areasToWork = trim($fb["areasToWork"] ?? "");
            $progressStatus = $fb["progressStatus"] ?? "Good";

            // Upsert: INSERT ... ON DUPLICATE KEY UPDATE
            $DB->vals = array($sessionID, $playerID, $coachID, $forehandRating, $backhandRating, $serveRating, $volleyRating, $footworkRating, $gameAwarenessRating, $overallRating, $sessionNotes, $areasToWork, $progressStatus, $forehandRating, $backhandRating, $serveRating, $volleyRating, $footworkRating, $gameAwarenessRating, $overallRating, $sessionNotes, $areasToWork, $progressStatus);
            $DB->types = "iiiiiiiiidsss" . "iiiiiidss" . "s";
            $DB->sql = "INSERT INTO " . $DB->pre . "ipa_coach_session_feedback
                        (sessionID, playerID, coachID, forehandRating, backhandRating, serveRating, volleyRating, footworkRating, gameAwarenessRating, overallRating, sessionNotes, areasToWork, progressStatus)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                        forehandRating=?, backhandRating=?, serveRating=?, volleyRating=?, footworkRating=?, gameAwarenessRating=?, overallRating=?, sessionNotes=?, areasToWork=?, progressStatus=?, feedbackDate=NOW()";
            $DB->dbQuery();
            $savedCount++;
        }

        // Mark session as feedback completed
        $DB->vals = array(1, $sessionID);
        $DB->types = "ii";
        $DB->sql = "UPDATE " . $DB->pre . "ipa_session SET coachFeedbackCompleted=? WHERE sessionID=?";
        $DB->dbQuery();

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "msg" => "Saved feedback for " . $savedCount . " students"));
        exit;
    }

    if ($xAction == "UPDATE") {
        $feedbackID = intval($_POST["feedbackID"] ?? 0);

        $forehandRating = intval($_POST["forehandRating"] ?? 0);
        $backhandRating = intval($_POST["backhandRating"] ?? 0);
        $serveRating = intval($_POST["serveRating"] ?? 0);
        $volleyRating = intval($_POST["volleyRating"] ?? 0);
        $footworkRating = intval($_POST["footworkRating"] ?? 0);
        $gameAwarenessRating = intval($_POST["gameAwarenessRating"] ?? 0);

        $ratingCount = 0;
        $ratingSum = 0;
        foreach ([$forehandRating, $backhandRating, $serveRating, $volleyRating, $footworkRating, $gameAwarenessRating] as $r) {
            if ($r > 0) {
                $ratingSum += $r;
                $ratingCount++;
            }
        }
        $overallRating = $ratingCount > 0 ? round($ratingSum / $ratingCount, 2) : 0;

        $sessionNotes = trim($_POST["sessionNotes"] ?? "");
        $areasToWork = trim($_POST["areasToWork"] ?? "");
        $progressStatus = $_POST["progressStatus"] ?? "Good";

        $DB->vals = array($forehandRating, $backhandRating, $serveRating, $volleyRating, $footworkRating, $gameAwarenessRating, $overallRating, $sessionNotes, $areasToWork, $progressStatus, $feedbackID);
        $DB->types = "iiiiiidsss" . "i";
        $DB->sql = "UPDATE " . $DB->pre . "ipa_coach_session_feedback
                    SET forehandRating=?, backhandRating=?, serveRating=?, volleyRating=?, footworkRating=?, gameAwarenessRating=?, overallRating=?, sessionNotes=?, areasToWork=?, progressStatus=?
                    WHERE feedbackID=?";
        $DB->dbQuery();

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "msg" => "Feedback updated successfully"));
        exit;
    }

    if ($xAction == "DELETE") {
        $feedbackID = intval($_POST["feedbackID"] ?? 0);

        $DB->vals = array(0, $feedbackID);
        $DB->types = "ii";
        $DB->sql = "UPDATE " . $DB->pre . "ipa_coach_session_feedback SET status=? WHERE feedbackID=?";
        $DB->dbQuery();

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "msg" => "Feedback deleted successfully"));
        exit;
    }

    if ($xAction == "GET_SESSION_PARTICIPANTS") {
        $sessionID = intval($_POST["sessionID"] ?? 0);

        // Get session details
        $DB->vals = array($sessionID);
        $DB->types = "i";
        $DB->sql = "SELECT s.*, CONCAT(c.firstName, ' ', IFNULL(c.lastName,'')) as coachName, pr.programName
                    FROM " . $DB->pre . "ipa_session s
                    LEFT JOIN " . $DB->pre . "ipa_coach c ON s.coachID = c.coachID
                    LEFT JOIN " . $DB->pre . "ipa_program pr ON s.programID = pr.programID
                    WHERE s.sessionID=?";
        $DB->dbRows();
        $session = !empty($DB->rows) ? $DB->rows[0] : null;

        if (!$session) {
            header('Content-Type: application/json');
            echo json_encode(array("err" => 1, "msg" => "Session not found"));
            exit;
        }

        // Get participants with existing feedback if any
        $DB->vals = array($sessionID, $sessionID);
        $DB->types = "ii";
        $DB->sql = "SELECT sp.participantID, sp.playerID,
                           CONCAT(p.firstName, ' ', IFNULL(p.lastName,'')) as playerName,
                           p.skillLevel,
                           f.feedbackID, f.forehandRating, f.backhandRating, f.serveRating,
                           f.volleyRating, f.footworkRating, f.gameAwarenessRating,
                           f.overallRating, f.sessionNotes, f.areasToWork, f.progressStatus
                    FROM " . $DB->pre . "ipa_session_participant sp
                    LEFT JOIN " . $DB->pre . "ipa_player p ON sp.playerID = p.playerID
                    LEFT JOIN " . $DB->pre . "ipa_coach_session_feedback f ON f.sessionID=? AND f.playerID = sp.playerID AND f.status=1
                    WHERE sp.sessionID=? AND sp.status=1
                    ORDER BY p.firstName";
        $DB->dbRows();
        $participants = $DB->rows ?: array();

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "session" => $session, "participants" => $participants));
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode(array("err" => 1, "msg" => "Invalid action"));
    exit;
} else {
    if (function_exists("setModVars")) {
        setModVars(array("TBL" => "ipa_coach_session_feedback", "PK" => "feedbackID"));
    }
}
