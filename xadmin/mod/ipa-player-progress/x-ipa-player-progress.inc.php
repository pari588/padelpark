<?php
/*
IPA Player Progress Module
Dashboard showing player skill progress over time
*/

if (isset($_POST["xAction"])) {
    ob_start();
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    ob_end_clean();

    mxCheckRequest(true, true);

    $xAction = $_POST["xAction"];

    if ($xAction == "GET_PLAYER_PROGRESS") {
        $playerID = intval($_POST["playerID"] ?? 0);

        if ($playerID < 1) {
            header('Content-Type: application/json');
            echo json_encode(array("err" => 1, "msg" => "Invalid player"));
            exit;
        }

        // Get player info
        $DB->vals = array($playerID);
        $DB->types = "i";
        $DB->sql = "SELECT p.*, CONCAT(p.firstName, ' ', IFNULL(p.lastName,'')) as playerName
                    FROM " . $DB->pre . "ipa_player p WHERE p.playerID=?";
        $DB->dbRows();
        $player = !empty($DB->rows) ? $DB->rows[0] : null;

        if (!$player) {
            header('Content-Type: application/json');
            echo json_encode(array("err" => 1, "msg" => "Player not found"));
            exit;
        }

        // Get session feedback history (last 20 sessions)
        $DB->vals = array($playerID);
        $DB->types = "i";
        $DB->sql = "SELECT f.*, s.sessionCode, s.sessionDate,
                           CONCAT(c.firstName, ' ', IFNULL(c.lastName,'')) as coachName
                    FROM " . $DB->pre . "ipa_coach_session_feedback f
                    LEFT JOIN " . $DB->pre . "ipa_session s ON f.sessionID = s.sessionID
                    LEFT JOIN " . $DB->pre . "ipa_coach c ON f.coachID = c.coachID
                    WHERE f.playerID=? AND f.status=1
                    ORDER BY f.feedbackDate DESC
                    LIMIT 20";
        $DB->dbRows();
        $sessionFeedback = $DB->rows ?: array();

        // Get formal assessments
        $DB->vals = array($playerID);
        $DB->types = "i";
        $DB->sql = "SELECT a.*, CONCAT(c.firstName, ' ', IFNULL(c.lastName,'')) as coachName,
                           pr.programName
                    FROM " . $DB->pre . "ipa_coach_assessment a
                    LEFT JOIN " . $DB->pre . "ipa_coach c ON a.coachID = c.coachID
                    LEFT JOIN " . $DB->pre . "ipa_program pr ON a.programID = pr.programID
                    WHERE a.playerID=? AND a.status=1
                    ORDER BY a.assessmentDate DESC
                    LIMIT 10";
        $DB->dbRows();
        $assessments = $DB->rows ?: array();

        // Calculate averages for radar chart (from recent session feedback)
        $skillAverages = array(
            "forehand" => 0,
            "backhand" => 0,
            "serve" => 0,
            "volley" => 0,
            "footwork" => 0,
            "gameAwareness" => 0
        );

        if (!empty($sessionFeedback)) {
            $counts = array("forehand" => 0, "backhand" => 0, "serve" => 0, "volley" => 0, "footwork" => 0, "gameAwareness" => 0);
            foreach ($sessionFeedback as $fb) {
                if ($fb["forehandRating"] > 0) { $skillAverages["forehand"] += $fb["forehandRating"]; $counts["forehand"]++; }
                if ($fb["backhandRating"] > 0) { $skillAverages["backhand"] += $fb["backhandRating"]; $counts["backhand"]++; }
                if ($fb["serveRating"] > 0) { $skillAverages["serve"] += $fb["serveRating"]; $counts["serve"]++; }
                if ($fb["volleyRating"] > 0) { $skillAverages["volley"] += $fb["volleyRating"]; $counts["volley"]++; }
                if ($fb["footworkRating"] > 0) { $skillAverages["footwork"] += $fb["footworkRating"]; $counts["footwork"]++; }
                if ($fb["gameAwarenessRating"] > 0) { $skillAverages["gameAwareness"] += $fb["gameAwarenessRating"]; $counts["gameAwareness"]++; }
            }
            foreach ($skillAverages as $k => $v) {
                $skillAverages[$k] = $counts[$k] > 0 ? round($v / $counts[$k], 2) : 0;
            }
        }

        // Progress trend data (chronological order for charts)
        $progressTrend = array_reverse($sessionFeedback);

        // Get session count and attendance stats
        $DB->vals = array($playerID);
        $DB->types = "i";
        $DB->sql = "SELECT COUNT(*) as totalSessions,
                           SUM(CASE WHEN attendanceStatus='Present' THEN 1 ELSE 0 END) as presentCount,
                           SUM(CASE WHEN attendanceStatus='Absent' THEN 1 ELSE 0 END) as absentCount
                    FROM " . $DB->pre . "ipa_session_participant
                    WHERE playerID=? AND status=1";
        $DB->dbRows();
        $attendance = !empty($DB->rows) ? $DB->rows[0] : array("totalSessions" => 0, "presentCount" => 0, "absentCount" => 0);

        header('Content-Type: application/json');
        echo json_encode(array(
            "err" => 0,
            "player" => $player,
            "skillAverages" => $skillAverages,
            "progressTrend" => $progressTrend,
            "sessionFeedback" => $sessionFeedback,
            "assessments" => $assessments,
            "attendance" => $attendance
        ));
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode(array("err" => 1, "msg" => "Invalid action"));
    exit;
} else {
    if (function_exists("setModVars")) {
        setModVars(array("TBL" => "ipa_player", "PK" => "playerID"));
    }
}
