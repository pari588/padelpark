<?php
/*
addParticipant = To save Participant data.
updateParticipant = To update Participant data.
checkInParticipant = Check in participant at venue.
*/

function generateRegistrationNo($tournamentID)
{
    global $DB;
    $DB->vals = array($tournamentID);
    $DB->types = "i";
    $DB->sql = "SELECT tournamentCode FROM " . $DB->pre . "ipt_tournament WHERE tournamentID=?";
    $tournament = $DB->dbRow();
    $prefix = ($tournament["tournamentCode"] ?? "IPT") . "-P";

    $DB->sql = "SELECT registrationNo FROM " . $DB->pre . "ipt_participant
                WHERE tournamentID=" . intval($tournamentID) . " AND registrationNo LIKE '" . $prefix . "%'
                ORDER BY registrationNo DESC LIMIT 1";
    $row = $DB->dbRow();
    $nextNum = 1;
    if ($DB->numRows > 0) {
        $lastNum = intval(substr($row['registrationNo'], -3));
        $nextNum = $lastNum + 1;
    }
    return $prefix . str_pad($nextNum, 3, "0", STR_PAD_LEFT);
}

function addParticipant()
{
    global $DB;

    $tournamentID = intval($_POST["tournamentID"]);

    // Generate registration number
    if (empty($_POST["registrationNo"])) {
        $_POST["registrationNo"] = generateRegistrationNo($tournamentID);
    }

    // Set team name if empty
    if (empty($_POST["teamName"])) {
        $_POST["teamName"] = $_POST["player1Name"];
        if (!empty($_POST["player2Name"])) {
            $_POST["teamName"] .= " / " . $_POST["player2Name"];
        }
    }

    $DB->table = $DB->pre . "ipt_participant";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        // Update participant count
        updateParticipantCount($tournamentID);
        setResponse(array("err" => 0, "param" => "id=" . $DB->insertID));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateParticipant()
{
    global $DB;
    $participantID = intval($_POST["participantID"]);

    // Set team name if empty
    if (empty($_POST["teamName"])) {
        $_POST["teamName"] = $_POST["player1Name"];
        if (!empty($_POST["player2Name"])) {
            $_POST["teamName"] .= " / " . $_POST["player2Name"];
        }
    }

    $DB->table = $DB->pre . "ipt_participant";
    $DB->data = $_POST;
    if ($DB->dbUpdate("participantID=?", "i", array($participantID))) {
        setResponse(array("err" => 0, "param" => "id=" . $participantID));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateParticipantCount($tournamentID)
{
    global $DB;
    $DB->vals = array($tournamentID);
    $DB->types = "i";
    $DB->sql = "SELECT COUNT(*) as cnt FROM " . $DB->pre . "ipt_participant WHERE tournamentID=? AND status=1";
    $row = $DB->dbRow();

    $DB->vals = array($row["cnt"], $tournamentID);
    $DB->types = "ii";
    $DB->sql = "UPDATE " . $DB->pre . "ipt_tournament SET currentParticipants=? WHERE tournamentID=?";
    $DB->dbQuery();
}

function checkInParticipant()
{
    global $DB;
    $participantID = intval($_POST["participantID"]);

    $DB->vals = array(1, date("Y-m-d H:i:s"), "Checked-In", $participantID);
    $DB->types = "issi";
    $DB->sql = "UPDATE " . $DB->pre . "ipt_participant SET checkedIn=?, checkInTime=?, participantStatus=? WHERE participantID=?";
    if ($DB->dbQuery()) {
        setResponse(array("err" => 0, "msg" => "Participant checked in successfully"));
    } else {
        setResponse(array("err" => 1));
    }
}

function confirmParticipant()
{
    global $DB;
    $participantID = intval($_POST["participantID"]);

    $DB->vals = array("Confirmed", $participantID);
    $DB->types = "si";
    $DB->sql = "UPDATE " . $DB->pre . "ipt_participant SET participantStatus=? WHERE participantID=?";
    if ($DB->dbQuery()) {
        setResponse(array("err" => 0, "msg" => "Participant confirmed"));
    } else {
        setResponse(array("err" => 1));
    }
}

function getCategories()
{
    global $DB, $MXRES;
    $tournamentID = intval($_POST["tournamentID"]);

    $DB->vals = array($tournamentID);
    $DB->types = "i";
    $DB->sql = "SELECT tc.tcID, c.categoryName, c.categoryType
                FROM " . $DB->pre . "ipt_tournament_category tc
                JOIN " . $DB->pre . "ipt_category c ON tc.categoryID=c.categoryID
                WHERE tc.tournamentID=? AND tc.status=1
                ORDER BY c.categoryName";
    $categories = $DB->dbRows();

    $MXRES["err"] = 0;
    $MXRES["categories"] = $categories;
}

// Handle AJAX actions
if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");

    header('Content-Type: application/json');

    // For GET_CATEGORIES, skip auth check to simplify
    if ($_POST["xAction"] == "GET_CATEGORIES") {
        global $DB;
        $tournamentID = intval($_POST["tournamentID"]);
        $DB->vals = array($tournamentID);
        $DB->types = "i";
        $DB->sql = "SELECT c.categoryID, c.categoryName, c.categoryType
                    FROM " . $DB->pre . "ipt_tournament_category tc
                    JOIN " . $DB->pre . "ipt_category c ON tc.categoryID=c.categoryID
                    WHERE tc.tournamentID=? AND tc.status=1
                    ORDER BY c.categoryName";
        $DB->dbRows();
        $categories = $DB->rows ?: array();
        echo json_encode(array("err" => 0, "categories" => $categories));
        exit;
    }

    $MXRES = mxCheckRequest(true, true);
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addParticipant(); break;
            case "UPDATE": updateParticipant(); break;
            case "CHECK_IN": checkInParticipant(); break;
            case "CONFIRM": confirmParticipant(); break;
        }
    }
    echo json_encode($MXRES);
    exit;
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "ipt_participant", "PK" => "participantID"));
}
