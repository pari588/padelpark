<?php
/*
addCoach = To save Coach data.
updateCoach = To update Coach data.
generateCoachCode = Generate unique coach code.
*/

function generateCoachCode()
{
    global $DB;
    $prefix = "IPA-C";
    $DB->sql = "SELECT coachCode FROM " . $DB->pre . "ipa_coach
                WHERE coachCode LIKE '" . $prefix . "%'
                ORDER BY coachID DESC LIMIT 1";
    $row = $DB->dbRow();
    $nextNum = 1;
    if ($DB->numRows > 0) {
        $lastNum = intval(substr($row['coachCode'], -3));
        $nextNum = $lastNum + 1;
    }
    return $prefix . str_pad($nextNum, 3, "0", STR_PAD_LEFT);
}

function addCoach()
{
    global $DB;

    // Generate coach code if empty
    if (empty($_POST["coachCode"])) {
        $_POST["coachCode"] = generateCoachCode();
    }

    $DB->table = $DB->pre . "ipa_coach";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        setResponse(array("err" => 0, "param" => "id=" . $DB->insertID));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateCoach()
{
    global $DB;
    $coachID = intval($_POST["coachID"]);

    $DB->table = $DB->pre . "ipa_coach";
    $DB->data = $_POST;
    if ($DB->dbUpdate("coachID=?", "i", array($coachID))) {
        setResponse(array("err" => 0, "param" => "id=" . $coachID));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateCoachStats($coachID)
{
    global $DB;

    // Update average rating
    $DB->vals = array($coachID);
    $DB->types = "i";
    $DB->sql = "SELECT AVG(sp.studentRating) as avgRating, COUNT(DISTINCT s.sessionID) as totalSessions, COUNT(DISTINCT sp.playerID) as totalStudents
                FROM " . $DB->pre . "ipa_session s
                LEFT JOIN " . $DB->pre . "ipa_session_participant sp ON s.sessionID = sp.sessionID
                WHERE s.coachID = ? AND s.sessionStatus = 'Completed' AND s.status = 1";
    $stats = $DB->dbRow();

    $DB->vals = array($stats["avgRating"] ?? 0, $stats["totalSessions"] ?? 0, $stats["totalStudents"] ?? 0, $coachID);
    $DB->types = "diii";
    $DB->sql = "UPDATE " . $DB->pre . "ipa_coach SET avgStudentRating=?, totalSessionsConducted=?, totalStudentsTrained=? WHERE coachID=?";
    $DB->dbQuery();
}

// Handle AJAX actions
if (isset($_POST["xAction"])) {
    ob_start();
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    ob_end_clean();

    header('Content-Type: application/json');
    $MXRES = mxCheckRequest(true, true);
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addCoach(); break;
            case "UPDATE": updateCoach(); break;
        }
    }
    echo json_encode($MXRES);
    exit;
} else {
    if (function_exists("setModVars")) {
        setModVars(array("TBL" => "ipa_coach", "PK" => "coachID"));
    }
}
