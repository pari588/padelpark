<?php
/*
addCourt = To save Court data.
updateCourt = To update Court data.
*/

function addCourt()
{
    global $DB;

    $locationID = intval($_POST["locationID"]);
    if ($locationID == 0) {
        setResponse(array("err" => 1, "msg" => "Location is required"));
        return;
    }

    // Generate court code if not provided
    if (empty($_POST["courtCode"])) {
        $DB->vals = array($locationID);
        $DB->types = "i";
        $DB->sql = "SELECT COUNT(*) as cnt FROM " . $DB->pre . "pnp_court WHERE locationID=?";
        $row = $DB->dbRow();
        $_POST["courtCode"] = "C" . str_pad(($row["cnt"] + 1), 2, "0", STR_PAD_LEFT);
    }

    $DB->table = $DB->pre . "pnp_court";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        setResponse(array("err" => 0, "param" => "id=" . $DB->insertID));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateCourt()
{
    global $DB;
    $courtID = intval($_POST["courtID"]);

    $DB->table = $DB->pre . "pnp_court";
    $DB->data = $_POST;
    if ($DB->dbUpdate("courtID=?", "i", array($courtID))) {
        setResponse(array("err" => 0, "param" => "id=" . $courtID));
    } else {
        setResponse(array("err" => 1));
    }
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addCourt(); break;
            case "UPDATE": updateCourt(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "pnp_court", "PK" => "courtID"));
}
