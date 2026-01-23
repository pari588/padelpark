<?php
/*
addPlayer = To save Player data.
updatePlayer = To update Player data.
generatePlayerCode = Generate unique player code.
*/

function generatePlayerCode()
{
    global $DB;
    $prefix = "IPA-P";
    $DB->sql = "SELECT playerCode FROM " . $DB->pre . "ipa_player
                WHERE playerCode LIKE '" . $prefix . "%'
                ORDER BY playerID DESC LIMIT 1";
    $row = $DB->dbRow();
    $nextNum = 1;
    if ($DB->numRows > 0) {
        $lastNum = intval(substr($row['playerCode'], -4));
        $nextNum = $lastNum + 1;
    }
    return $prefix . str_pad($nextNum, 4, "0", STR_PAD_LEFT);
}

function addPlayer()
{
    global $DB;

    // Generate player code if empty
    if (empty($_POST["playerCode"])) {
        $_POST["playerCode"] = generatePlayerCode();
    }

    $DB->table = $DB->pre . "ipa_player";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        setResponse(array("err" => 0, "param" => "id=" . $DB->insertID));
    } else {
        setResponse(array("err" => 1));
    }
}

function updatePlayer()
{
    global $DB;
    $playerID = intval($_POST["playerID"]);

    $DB->table = $DB->pre . "ipa_player";
    $DB->data = $_POST;
    if ($DB->dbUpdate("playerID=?", "i", array($playerID))) {
        setResponse(array("err" => 0, "param" => "id=" . $playerID));
    } else {
        setResponse(array("err" => 1));
    }
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
            case "ADD": addPlayer(); break;
            case "UPDATE": updatePlayer(); break;
        }
    }
    echo json_encode($MXRES);
    exit;
} else {
    if (function_exists("setModVars")) {
        setModVars(array("TBL" => "ipa_player", "PK" => "playerID"));
    }
}
