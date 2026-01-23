<?php
/*
addPrize = To save Prize data.
updatePrize = To update Prize data.
markPaid = Mark prize as paid.
*/

function addPrize()
{
    global $DB;

    // Calculate TDS if applicable
    if ($_POST["prizeAmount"] > 10000) {
        $_POST["tdsDeducted"] = round($_POST["prizeAmount"] * 0.3124, 2); // TDS @ 31.24% for prize money > 10000
        $_POST["netAmount"] = $_POST["prizeAmount"] - $_POST["tdsDeducted"];
    } else {
        $_POST["tdsDeducted"] = 0;
        $_POST["netAmount"] = $_POST["prizeAmount"];
    }

    $DB->table = $DB->pre . "ipt_prize";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        setResponse(array("err" => 0, "param" => "id=" . $DB->insertID));
    } else {
        setResponse(array("err" => 1));
    }
}

function updatePrize()
{
    global $DB;
    $prizeID = intval($_POST["prizeID"]);

    // Calculate TDS if applicable
    if ($_POST["prizeAmount"] > 10000) {
        $_POST["tdsDeducted"] = round($_POST["prizeAmount"] * 0.3124, 2);
        $_POST["netAmount"] = $_POST["prizeAmount"] - $_POST["tdsDeducted"];
    } else {
        $_POST["tdsDeducted"] = 0;
        $_POST["netAmount"] = $_POST["prizeAmount"];
    }

    $DB->table = $DB->pre . "ipt_prize";
    $DB->data = $_POST;
    if ($DB->dbUpdate("prizeID=?", "i", array($prizeID))) {
        setResponse(array("err" => 0, "param" => "id=" . $prizeID));
    } else {
        setResponse(array("err" => 1));
    }
}

function markPaid()
{
    global $DB;
    $prizeID = intval($_POST["prizeID"]);

    $DB->vals = array("Paid", date("Y-m-d"), $prizeID);
    $DB->types = "ssi";
    $DB->sql = "UPDATE " . $DB->pre . "ipt_prize SET disbursementStatus=?, disbursementDate=? WHERE prizeID=?";
    if ($DB->dbQuery()) {
        setResponse(array("err" => 0, "msg" => "Prize marked as paid"));
    } else {
        setResponse(array("err" => 1));
    }
}

// Handle AJAX actions
if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest(true, true); // Skip JWT token validation, use session auth
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addPrize(); break;
            case "UPDATE": updatePrize(); break;
            case "MARK_PAID": markPaid(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "ipt_prize", "PK" => "prizeID"));
}
