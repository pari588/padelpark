<?php
/*
Distributor Module for B2B Sales
- Manage B2B customers/distributors
- Credit terms and limits
*/

function addDistributor()
{
    global $DB;

    // Validate required fields
    $companyName = trim($_POST["companyName"] ?? "");
    if (empty($companyName)) {
        setResponse(array("err" => 1, "msg" => "Company name is required"));
        return;
    }

    // Check for duplicate code if provided
    $distributorCode = trim($_POST["distributorCode"] ?? "");
    if (!empty($distributorCode)) {
        $DB->vals = array($distributorCode, 1);
        $DB->types = "si";
        $DB->sql = "SELECT distributorID FROM " . $DB->pre . "distributor WHERE distributorCode=? AND status=?";
        if ($DB->dbRow()) {
            setResponse(array("err" => 1, "msg" => "Distributor code already exists"));
            return;
        }
    } else {
        // Auto-generate code
        $DB->sql = "SELECT MAX(distributorID) as maxID FROM " . $DB->pre . "distributor";
        $maxID = ($DB->dbRow()["maxID"] ?? 0) + 1;
        $distributorCode = "DIST-" . str_pad($maxID, 5, "0", STR_PAD_LEFT);
        $_POST["distributorCode"] = $distributorCode;
    }

    // Set defaults
    if (!isset($_POST["creditLimit"])) $_POST["creditLimit"] = 0;
    if (!isset($_POST["creditDays"])) $_POST["creditDays"] = 30;
    if (!isset($_POST["isActive"])) $_POST["isActive"] = 1;
    if (!isset($_POST["creditStatus"])) $_POST["creditStatus"] = "Active";
    if (!isset($_POST["sameAsBilling"])) $_POST["sameAsBilling"] = isset($_POST["sameAsBilling"]) ? 1 : 0;

    $_POST["created"] = date("Y-m-d H:i:s");
    $_POST["createdBy"] = $_SESSION["ADMINID"] ?? 0;

    $DB->table = $DB->pre . "distributor";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        setResponse(array("err" => 0, "param" => "id=" . $DB->insertID, "msg" => "Distributor added successfully"));
    }
}

function updateDistributor()
{
    global $DB;

    $distributorID = intval($_POST["distributorID"]);
    if ($distributorID < 1) {
        setResponse(array("err" => 1, "msg" => "Invalid distributor ID"));
        return;
    }

    // Validate required fields
    $companyName = trim($_POST["companyName"] ?? "");
    if (empty($companyName)) {
        setResponse(array("err" => 1, "msg" => "Company name is required"));
        return;
    }

    // Check for duplicate code (excluding self)
    $distributorCode = trim($_POST["distributorCode"] ?? "");
    if (!empty($distributorCode)) {
        $DB->vals = array($distributorCode, 1, $distributorID);
        $DB->types = "sii";
        $DB->sql = "SELECT distributorID FROM " . $DB->pre . "distributor WHERE distributorCode=? AND status=? AND distributorID!=?";
        if ($DB->dbRow()) {
            setResponse(array("err" => 1, "msg" => "Distributor code already exists"));
            return;
        }
    }

    // Handle checkbox
    $_POST["sameAsBilling"] = isset($_POST["sameAsBilling"]) ? 1 : 0;

    $_POST["modified"] = date("Y-m-d H:i:s");

    unset($_POST["xAction"]);
    unset($_POST["created"]);
    unset($_POST["createdBy"]);
    unset($_POST["currentOutstanding"]); // Don't allow manual update of outstanding

    $DB->table = $DB->pre . "distributor";
    $DB->data = $_POST;
    $DB->where = "distributorID = $distributorID";
    if ($DB->dbUpdate()) {
        setResponse(array("err" => 0, "param" => "id=" . $distributorID, "msg" => "Distributor updated successfully"));
    }
}

function getDistributorDropdown()
{
    global $DB;

    $DB->vals = array(1, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT distributorID, distributorCode, companyName, creditLimit, currentOutstanding, creditDays
                FROM " . $DB->pre . "distributor
                WHERE status=? AND isActive=?
                ORDER BY companyName";
    $distributors = $DB->dbRows();

    setResponse(array("err" => 0, "data" => $distributors));
}

function getDistributorDetails()
{
    global $DB;

    $distributorID = intval($_POST["distributorID"]);
    if ($distributorID < 1) {
        setResponse(array("err" => 1, "msg" => "Invalid distributor ID"));
        return;
    }

    $DB->vals = array($distributorID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM " . $DB->pre . "distributor WHERE distributorID=? AND status=?";
    $distributor = $DB->dbRow();

    if (!$distributor) {
        setResponse(array("err" => 1, "msg" => "Distributor not found"));
        return;
    }

    // Get recent orders
    $DB->vals = array($distributorID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT orderID, orderNo, orderDate, totalAmount, orderStatus FROM " . $DB->pre . "b2b_sales_order WHERE distributorID=? AND status=? ORDER BY orderDate DESC LIMIT 5";
    $recentOrders = $DB->dbRows();

    // Get recent payments
    $DB->vals = array($distributorID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT paymentID, paymentNo, paymentDate, amount, paymentMode FROM " . $DB->pre . "b2b_payment WHERE distributorID=? AND status=? ORDER BY paymentDate DESC LIMIT 5";
    $recentPayments = $DB->dbRows();

    setResponse(array(
        "err" => 0,
        "distributor" => $distributor,
        "recentOrders" => $recentOrders,
        "recentPayments" => $recentPayments
    ));
}

function updateCreditLimit()
{
    global $DB;

    $distributorID = intval($_POST["distributorID"]);
    $newCreditLimit = floatval($_POST["creditLimit"]);
    $reason = trim($_POST["reason"] ?? "");

    if ($distributorID < 1) {
        setResponse(array("err" => 1, "msg" => "Invalid distributor ID"));
        return;
    }

    // Get current credit limit
    $DB->vals = array($distributorID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT creditLimit FROM " . $DB->pre . "distributor WHERE distributorID=? AND status=?";
    $current = $DB->dbRow();
    $oldLimit = $current["creditLimit"] ?? 0;

    // Update credit limit
    $DB->vals = array($newCreditLimit, date("Y-m-d H:i:s"), $distributorID);
    $DB->types = "dsi";
    $DB->sql = "UPDATE " . $DB->pre . "distributor SET creditLimit=?, modified=? WHERE distributorID=?";
    $DB->dbQuery();

    setResponse(array("err" => 0, "msg" => "Credit limit updated successfully"));
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest(true, true); // Session-based auth
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addDistributor(); break;
            case "UPDATE": updateDistributor(); break;
            case "GET_DROPDOWN": getDistributorDropdown(); break;
            case "GET_DETAILS": getDistributorDetails(); break;
            case "UPDATE_CREDIT": updateCreditLimit(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "distributor", "PK" => "distributorID"));
}
?>
