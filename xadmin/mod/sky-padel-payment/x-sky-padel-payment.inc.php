<?php
function addPayment()
{
    global $DB;
    if (isset($_POST["quotationID"])) $_POST["quotationID"] = intval($_POST["quotationID"]);
    if (isset($_POST["projectID"])) $_POST["projectID"] = intval($_POST["projectID"]);
    if (isset($_POST["amount"])) $_POST["amount"] = floatval($_POST["amount"]);

    $DB->table = $DB->pre . "sky_padel_payment";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $paymentID = $DB->insertID;

        // Update quotation/project payment status
        if (!empty($_POST["quotationID"])) {
            updateQuotationPaymentStatus($_POST["quotationID"]);
        }
        if (!empty($_POST["projectID"])) {
            updateProjectPaymentStatus($_POST["projectID"]);
        }

        setResponse(array("err" => 0, "param" => "id=$paymentID"));
    } else {
        setResponse(array("err" => 1));
    }
}

function updatePayment()
{
    global $DB;
    $paymentID = intval($_POST["paymentID"]);
    if (isset($_POST["quotationID"])) $_POST["quotationID"] = intval($_POST["quotationID"]);
    if (isset($_POST["projectID"])) $_POST["projectID"] = intval($_POST["projectID"]);
    if (isset($_POST["amount"])) $_POST["amount"] = floatval($_POST["amount"]);

    $DB->table = $DB->pre . "sky_padel_payment";
    $DB->data = $_POST;
    if ($DB->dbUpdate("paymentID=?", "i", array($paymentID))) {
        // Update quotation/project payment status
        if (!empty($_POST["quotationID"])) {
            updateQuotationPaymentStatus($_POST["quotationID"]);
        }
        if (!empty($_POST["projectID"])) {
            updateProjectPaymentStatus($_POST["projectID"]);
        }

        setResponse(array("err" => 0, "param" => "id=$paymentID"));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateQuotationPaymentStatus($quotationID)
{
    global $DB;
    // Calculate total payments for this quotation
    $DB->vals = array($quotationID);
    $DB->types = "i";
    $DB->sql = "SELECT SUM(amount) as totalPaid FROM " . $DB->pre . "sky_padel_payment WHERE quotationID=? AND status=1";
    $result = $DB->dbRow();
    $totalPaid = $result["totalPaid"] ?? 0;

    // Get quotation total
    $DB->sql = "SELECT totalAmount FROM " . $DB->pre . "sky_padel_quotation WHERE quotationID=?";
    $quotation = $DB->dbRow();
    $totalAmount = $quotation["totalAmount"] ?? 0;

    // Update payment status
    $paymentStatus = "Pending";
    if ($totalPaid >= $totalAmount) {
        $paymentStatus = "Paid";
    } elseif ($totalPaid > 0) {
        $paymentStatus = "Partial";
    }

    $DB->vals = array($paymentStatus, $quotationID);
    $DB->types = "si";
    $DB->sql = "UPDATE " . $DB->pre . "sky_padel_quotation SET paymentStatus=? WHERE quotationID=?";
    $DB->dbQuery();
}

function updateProjectPaymentStatus($projectID)
{
    global $DB;
    // Calculate total payments for this project
    $DB->vals = array($projectID);
    $DB->types = "i";
    $DB->sql = "SELECT SUM(amount) as totalPaid FROM " . $DB->pre . "sky_padel_payment WHERE projectID=? AND status=1";
    $result = $DB->dbRow();
    $totalPaid = $result["totalPaid"] ?? 0;

    // Get project contract amount
    $DB->sql = "SELECT contractAmount FROM " . $DB->pre . "project WHERE projectID=?";
    $project = $DB->dbRow();
    $contractAmount = $project["contractAmount"] ?? 0;

    // Update payment status
    $paymentStatus = "Pending";
    if ($totalPaid >= $contractAmount) {
        $paymentStatus = "Paid";
    } elseif ($totalPaid > 0) {
        $paymentStatus = "Partial";
    }

    $DB->vals = array($paymentStatus, $projectID);
    $DB->types = "si";
    $DB->sql = "UPDATE " . $DB->pre . "project SET paymentStatus=? WHERE projectID=?";
    $DB->dbQuery();
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    // Bypass JWT validation for admin AJAX requests (use PHP session auth instead)
    $MXRES = mxCheckRequest(true, true);
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addPayment(); break;
            case "UPDATE": updatePayment(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "sky_padel_payment", "PK" => "paymentID"));
}
?>
