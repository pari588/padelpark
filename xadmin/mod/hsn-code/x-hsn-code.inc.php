<?php
/*
HSN Code Module - Manage HSN codes for GST compliance
*/

function addHSNCode()
{
    global $DB;

    if (isset($_POST["gstRate"])) $_POST["gstRate"] = floatval($_POST["gstRate"]);

    // Check for duplicate HSN code
    if (!empty($_POST["hsnCode"])) {
        $DB->vals = array($_POST["hsnCode"]);
        $DB->types = "s";
        $DB->sql = "SELECT hsnID FROM " . $DB->pre . "hsn_code WHERE hsnCode=?";
        if ($DB->dbRow()) {
            setResponse(array("err" => 1, "msg" => "HSN Code already exists"));
            return;
        }
    }

    $DB->table = $DB->pre . "hsn_code";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $hsnID = $DB->insertID;
        setResponse(array("err" => 0, "param" => "id=$hsnID"));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateHSNCode()
{
    global $DB;
    $hsnID = intval($_POST["hsnID"]);

    if (isset($_POST["gstRate"])) $_POST["gstRate"] = floatval($_POST["gstRate"]);

    // Check for duplicate HSN code (exclude current)
    if (!empty($_POST["hsnCode"])) {
        $DB->vals = array($_POST["hsnCode"], $hsnID);
        $DB->types = "si";
        $DB->sql = "SELECT hsnID FROM " . $DB->pre . "hsn_code WHERE hsnCode=? AND hsnID!=?";
        if ($DB->dbRow()) {
            setResponse(array("err" => 1, "msg" => "HSN Code already exists"));
            return;
        }
    }

    $DB->table = $DB->pre . "hsn_code";
    $DB->data = $_POST;
    if ($DB->dbUpdate("hsnID=?", "i", array($hsnID))) {
        setResponse(array("err" => 0, "param" => "id=$hsnID"));
    } else {
        setResponse(array("err" => 1));
    }
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest(true, true); // Session-based auth
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addHSNCode(); break;
            case "UPDATE": updateHSNCode(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "hsn_code", "PK" => "hsnID"));
}
?>
