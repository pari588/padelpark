<?php
/*
addTournament = To save Tournament data.
updateTournament = To update Tournament data.
generateTournamentCode = Generate unique tournament code.
generateFixtures = Generate tournament fixtures/draw.
*/

function generateTournamentCode()
{
    global $DB;
    $prefix = "IPT-" . date("Y") . "-";
    $DB->sql = "SELECT tournamentCode FROM " . $DB->pre . "ipt_tournament
                WHERE tournamentCode LIKE '" . $prefix . "%'
                ORDER BY tournamentCode DESC LIMIT 1";
    $row = $DB->dbRow();
    $nextNum = 1;
    if ($DB->numRows > 0) {
        $lastNum = intval(substr($row['tournamentCode'], -3));
        $nextNum = $lastNum + 1;
    }
    return $prefix . str_pad($nextNum, 3, "0", STR_PAD_LEFT);
}

function addTournament()
{
    global $DB;

    // Generate tournament code
    if (empty($_POST["tournamentCode"])) {
        $_POST["tournamentCode"] = generateTournamentCode();
    }

    $_POST["createdBy"] = $_SESSION[SITEURL]["userID"] ?? 0;

    // Extract categories before saving
    $categories = $_POST["categories"] ?? array();
    unset($_POST["categories"]);

    $DB->table = $DB->pre . "ipt_tournament";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $tournamentID = $DB->insertID;

        // Save categories
        saveCategories($tournamentID, $categories);

        setResponse(array("err" => 0, "param" => "id=" . $tournamentID));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateTournament()
{
    global $DB;
    $tournamentID = intval($_POST["tournamentID"]);

    // Extract categories before saving
    $categories = $_POST["categories"] ?? array();
    unset($_POST["categories"]);

    $DB->table = $DB->pre . "ipt_tournament";
    $DB->data = $_POST;
    if ($DB->dbUpdate("tournamentID=?", "i", array($tournamentID))) {
        // Save categories
        saveCategories($tournamentID, $categories);

        setResponse(array("err" => 0, "param" => "id=" . $tournamentID));
    } else {
        setResponse(array("err" => 1));
    }
}

function saveCategories($tournamentID, $categories)
{
    global $DB;

    // Get entry fees, prize pools, and max teams from POST
    $entryFees = $_POST["categoryEntryFee"] ?? array();
    $prizePools = $_POST["categoryPrizePool"] ?? array();
    $maxTeams = $_POST["categoryMaxTeams"] ?? array();

    // Delete existing categories for this tournament
    $DB->vals = array($tournamentID);
    $DB->types = "i";
    $DB->sql = "DELETE FROM " . $DB->pre . "ipt_tournament_category WHERE tournamentID=?";
    $DB->dbQuery();

    // Insert new categories with entry fee and prize pool
    if (!empty($categories) && is_array($categories)) {
        foreach ($categories as $categoryID) {
            $categoryID = intval($categoryID);
            if ($categoryID > 0) {
                $DB->table = $DB->pre . "ipt_tournament_category";
                $DB->data = array(
                    "tournamentID" => $tournamentID,
                    "categoryID" => $categoryID,
                    "entryFee" => floatval($entryFees[$categoryID] ?? 0),
                    "prizePool" => floatval($prizePools[$categoryID] ?? 0),
                    "maxTeams" => intval($maxTeams[$categoryID] ?? 16),
                    "status" => 1
                );
                $DB->dbInsert();
            }
        }
    }
}

function updateTournamentStatus()
{
    global $DB;
    $tournamentID = intval($_POST["tournamentID"]);
    $newStatus = $_POST["tournamentStatus"] ?? "";

    $validStatuses = array("Draft", "Open", "Registration-Closed", "In-Progress", "Completed", "Cancelled");
    if (!in_array($newStatus, $validStatuses)) {
        setResponse(array("err" => 1, "msg" => "Invalid status"));
        return;
    }

    $DB->vals = array($newStatus, $tournamentID);
    $DB->types = "si";
    $DB->sql = "UPDATE " . $DB->pre . "ipt_tournament SET tournamentStatus=? WHERE tournamentID=?";
    if ($DB->dbQuery()) {
        setResponse(array("err" => 0, "msg" => "Status updated to " . $newStatus));
    } else {
        setResponse(array("err" => 1));
    }
}

function generateFixtures()
{
    global $DB;
    $tournamentID = intval($_POST["tournamentID"]);
    $tcID = intval($_POST["tcID"]);

    // Get all confirmed participants for this category
    $DB->vals = array($tournamentID, $tcID, "Confirmed");
    $DB->types = "iis";
    $DB->sql = "SELECT * FROM " . $DB->pre . "ipt_participant
                WHERE tournamentID=? AND tcID=? AND participantStatus=? AND status=1
                ORDER BY seedNumber DESC, participantID ASC";
    $participants = $DB->dbRows();

    $count = count($participants);
    if ($count < 2) {
        setResponse(array("err" => 1, "msg" => "Need at least 2 participants to generate fixtures"));
        return;
    }

    // Get tournament details
    $DB->vals = array($tournamentID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "ipt_tournament WHERE tournamentID=?";
    $tournament = $DB->dbRow();

    // Simple knockout bracket generation
    $rounds = ceil(log($count, 2));
    $bracketSize = pow(2, $rounds);

    // Delete existing fixtures for this category
    $DB->vals = array($tournamentID, $tcID);
    $DB->types = "ii";
    $DB->sql = "DELETE FROM " . $DB->pre . "ipt_fixture WHERE tournamentID=? AND tcID=?";
    $DB->dbQuery();

    // Round names
    $roundNames = array(
        1 => "Final",
        2 => "Semi-Final",
        3 => "Quarter-Final",
        4 => "Round of 16",
        5 => "Round of 32",
        6 => "Round of 64"
    );

    // Generate first round matches
    $matchNo = 1;
    $matchDate = $tournament["startDate"];

    for ($i = 0; $i < $bracketSize / 2; $i++) {
        $team1 = $participants[$i * 2] ?? null;
        $team2 = $participants[$i * 2 + 1] ?? null;

        $matchStatus = "Scheduled";
        if (!$team1 && !$team2) continue;
        if (!$team2) $matchStatus = "Bye";

        $DB->table = $DB->pre . "ipt_fixture";
        $DB->data = array(
            "tournamentID" => $tournamentID,
            "tcID" => $tcID,
            "matchNo" => $matchNo,
            "roundName" => $roundNames[$rounds] ?? "Round " . $rounds,
            "roundNumber" => $rounds,
            "bracketPosition" => $i + 1,
            "matchDate" => $matchDate,
            "team1ID" => $team1["participantID"] ?? null,
            "team1Name" => $team1 ? ($team1["teamName"] ?: $team1["player1Name"]) : "BYE",
            "team1Seed" => $team1["seedNumber"] ?? 0,
            "team2ID" => $team2["participantID"] ?? null,
            "team2Name" => $team2 ? ($team2["teamName"] ?: $team2["player1Name"]) : "BYE",
            "team2Seed" => $team2["seedNumber"] ?? 0,
            "matchStatus" => $matchStatus
        );
        $DB->dbInsert();
        $matchNo++;
    }

    // Mark draw as generated
    $DB->vals = array(1, $tcID);
    $DB->types = "ii";
    $DB->sql = "UPDATE " . $DB->pre . "ipt_tournament_category SET drawGenerated=1 WHERE tcID=?";
    $DB->dbQuery();

    setResponse(array("err" => 0, "msg" => "Fixtures generated for " . ($matchNo - 1) . " matches"));
}

// Handle AJAX actions
if (isset($_POST["xAction"])) {
    ob_start();
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    ob_end_clean();

    header('Content-Type: application/json');
    $MXRES = mxCheckRequest(true, true); // Skip JWT token validation, use session auth
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addTournament(); break;
            case "UPDATE": updateTournament(); break;
            case "UPDATE_STATUS": updateTournamentStatus(); break;
            case "GENERATE_FIXTURES": generateFixtures(); break;
        }
    }
    echo json_encode($MXRES);
    exit;
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "ipt_tournament", "PK" => "tournamentID"));
}
