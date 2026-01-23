<?php
/**
 * Vendor RFQ Module - Backend Logic
 */

function getRFQStatusBadge($status) {
    $badges = array(
        "Draft" => "badge-secondary",
        "Published" => "badge-info",
        "Closed" => "badge-warning",
        "Awarded" => "badge-success",
        "Cancelled" => "badge-danger"
    );
    $class = isset($badges[$status]) ? $badges[$status] : "badge-secondary";
    return '<span class="badge ' . $class . '">' . htmlentities($status) . '</span>';
}

function generateRFQNumber() {
    global $DB;
    $prefix = "RFQ-" . date("Ymd") . "-";
    $DB->sql = "SELECT rfqNumber FROM " . $DB->pre . "vendor_rfq WHERE rfqNumber LIKE ? ORDER BY rfqID DESC LIMIT 1";
    $DB->vals = array($prefix . "%");
    $DB->types = "s";
    $row = $DB->dbRow();
    $newNum = $row ? intval(substr($row["rfqNumber"], -4)) + 1 : 1;
    return $prefix . str_pad($newNum, 4, "0", STR_PAD_LEFT);
}

function addRFQ() {
    global $DB;
    $rfqNumber = generateRFQNumber();
    $_POST["rfqNumber"] = $rfqNumber;
    if (!isset($_POST["rfqStatus"])) $_POST["rfqStatus"] = "Draft";

    $DB->table = $DB->pre . "vendor_rfq";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $rfqID = $DB->insertID;
        setResponse(array("err" => 0, "msg" => "RFQ created!", "param" => "rfqID=$rfqID"));
    } else {
        setResponse(array("err" => 1, "msg" => "Failed to create RFQ."));
    }
}

function updateRFQ() {
    global $DB;
    $rfqID = intval($_POST["rfqID"]);
    $DB->table = $DB->pre . "vendor_rfq";
    $DB->data = $_POST;
    if ($DB->dbUpdate("rfqID=?", "i", array($rfqID))) {
        setResponse(array("err" => 0, "msg" => "RFQ updated!", "param" => "rfqID=$rfqID"));
    } else {
        setResponse(array("err" => 1, "msg" => "Failed to update RFQ."));
    }
}

function publishRFQ() {
    global $DB;
    $rfqID = intval($_POST["rfqID"]);
    $DB->table = $DB->pre . "vendor_rfq";
    $DB->data = array("rfqStatus" => "Published", "publishDate" => date("Y-m-d H:i:s"));
    if ($DB->dbUpdate("rfqID=?", "i", array($rfqID))) {
        setResponse(array("err" => 0, "msg" => "RFQ published!"));
    } else {
        setResponse(array("err" => 1, "msg" => "Failed to publish RFQ."));
    }
}

function closeRFQ() {
    global $DB;
    $rfqID = intval($_POST["rfqID"]);
    $DB->table = $DB->pre . "vendor_rfq";
    $DB->data = array("rfqStatus" => "Closed", "closedAt" => date("Y-m-d H:i:s"));
    if ($DB->dbUpdate("rfqID=?", "i", array($rfqID))) {
        setResponse(array("err" => 0, "msg" => "RFQ closed!"));
    } else {
        setResponse(array("err" => 1, "msg" => "Failed to close RFQ."));
    }
}

function addRFQItem() {
    global $DB;
    $rfqID = intval($_POST["rfqID"]);
    $DB->table = $DB->pre . "vendor_rfq_item";
    $DB->data = array(
        "rfqID" => $rfqID,
        "itemDescription" => $_POST["itemDescription"] ?? "",
        "specifications" => $_POST["specifications"] ?? "",
        "unit" => $_POST["unit"] ?? "Nos",
        "quantity" => floatval($_POST["quantity"] ?? 1),
        "estimatedRate" => floatval($_POST["estimatedRate"] ?? 0)
    );
    if ($DB->dbInsert()) {
        setResponse(array("err" => 0, "msg" => "Item added!"));
    } else {
        setResponse(array("err" => 1, "msg" => "Failed to add item."));
    }
}

function deleteRFQItem() {
    global $DB;
    $itemID = intval($_POST["itemID"]);
    $DB->table = $DB->pre . "vendor_rfq_item";
    $DB->data = array("status" => 0);
    if ($DB->dbUpdate("itemID=?", "i", array($itemID))) {
        setResponse(array("err" => 0, "msg" => "Item deleted!"));
    } else {
        setResponse(array("err" => 1, "msg" => "Failed to delete item."));
    }
}

function awardRFQ() {
    global $DB;
    $rfqID = intval($_POST["rfqID"]);
    $quoteID = intval($_POST["quoteID"]);
    $vendorID = intval($_POST["vendorID"]);

    // Update RFQ status
    $DB->table = $DB->pre . "vendor_rfq";
    $DB->data = array("rfqStatus" => "Awarded", "awardedAt" => date("Y-m-d H:i:s"), "awardedVendorID" => $vendorID);
    $DB->dbUpdate("rfqID=?", "i", array($rfqID));

    // Accept the winning quote
    $DB->table = $DB->pre . "vendor_quote";
    $DB->data = array("quoteStatus" => "Accepted");
    $DB->dbUpdate("quoteID=?", "i", array($quoteID));

    // Reject other quotes
    $DB->sql = "UPDATE " . $DB->pre . "vendor_quote SET quoteStatus='Rejected' WHERE rfqID=? AND quoteID!=? AND quoteStatus!='Rejected'";
    $DB->vals = array($rfqID, $quoteID);
    $DB->types = "ii";
    $DB->dbQuery();

    setResponse(array("err" => 0, "msg" => "RFQ awarded successfully!"));
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addRFQ(); break;
            case "UPDATE": updateRFQ(); break;
            case "PUBLISH": publishRFQ(); break;
            case "CLOSE": closeRFQ(); break;
            case "ADD_ITEM": addRFQItem(); break;
            case "DELETE_ITEM": deleteRFQItem(); break;
            case "AWARD": awardRFQ(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "vendor_rfq", "PK" => "rfqID"));
}
?>
