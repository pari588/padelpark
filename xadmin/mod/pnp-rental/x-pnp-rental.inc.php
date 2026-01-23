<?php
/*
addRental = To save Rental transaction.
updateRental = To update Rental transaction.
issueEquipment = Mark equipment as issued.
returnEquipment = Mark equipment as returned.
generateRentalNo = Generate unique rental number.
*/

function generateRentalNo()
{
    global $DB;
    $prefix = "RN-" . date("Ymd") . "-";
    $DB->sql = "SELECT rentalNo FROM " . $DB->pre . "pnp_rental
                WHERE rentalNo LIKE '" . $prefix . "%'
                ORDER BY rentalNo DESC LIMIT 1";
    $row = $DB->dbRow();
    $nextNum = 1;
    if ($DB->numRows > 0) {
        $lastNum = intval(substr($row['rentalNo'], -4));
        $nextNum = $lastNum + 1;
    }
    return $prefix . str_pad($nextNum, 4, "0", STR_PAD_LEFT);
}

function addRental()
{
    global $DB;

    // Generate rental number
    if (empty($_POST["rentalNo"])) {
        $_POST["rentalNo"] = generateRentalNo();
    }

    $_POST["createdBy"] = $_SESSION[SITEURL]["userID"] ?? 0;
    $_POST["rentalDate"] = $_POST["rentalDate"] ?? date("Y-m-d");

    // Calculate totals from items
    $totalRental = 0;
    $totalDeposit = 0;
    if (isset($_POST["equipmentID"]) && is_array($_POST["equipmentID"])) {
        for ($i = 0; $i < count($_POST["equipmentID"]); $i++) {
            if (!empty($_POST["equipmentID"][$i])) {
                $qty = intval($_POST["quantity"][$i] ?? 1);
                $rate = floatval($_POST["rentalRateItem"][$i] ?? 0);
                $totalRental += $qty * $rate;
            }
        }
    }
    $_POST["rentalAmount"] = $totalRental;
    $_POST["totalAmount"] = $totalRental + floatval($_POST["depositAmount"] ?? 0);

    $DB->table = $DB->pre . "pnp_rental";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $rentalID = $DB->insertID;

        // Save rental items
        if (isset($_POST["equipmentID"]) && is_array($_POST["equipmentID"])) {
            for ($i = 0; $i < count($_POST["equipmentID"]); $i++) {
                if (!empty($_POST["equipmentID"][$i])) {
                    $equipmentID = intval($_POST["equipmentID"][$i]);
                    $qty = intval($_POST["quantity"][$i] ?? 1);
                    $rate = floatval($_POST["rentalRateItem"][$i] ?? 0);

                    $DB->table = $DB->pre . "pnp_rental_item";
                    $DB->data = array(
                        "rentalID" => $rentalID,
                        "equipmentID" => $equipmentID,
                        "quantity" => $qty,
                        "rentalRate" => $rate,
                        "totalAmount" => $qty * $rate,
                        "itemStatus" => "Reserved"
                    );
                    $DB->dbInsert();
                }
            }
        }

        setResponse(array("err" => 0, "param" => "id=" . $rentalID));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateRental()
{
    global $DB;
    $rentalID = intval($_POST["rentalID"]);

    $DB->table = $DB->pre . "pnp_rental";
    $DB->data = $_POST;
    if ($DB->dbUpdate("rentalID=?", "i", array($rentalID))) {
        // Update items
        $DB->vals = array($rentalID);
        $DB->types = "i";
        $DB->sql = "DELETE FROM " . $DB->pre . "pnp_rental_item WHERE rentalID=?";
        $DB->dbQuery();

        if (isset($_POST["equipmentID"]) && is_array($_POST["equipmentID"])) {
            for ($i = 0; $i < count($_POST["equipmentID"]); $i++) {
                if (!empty($_POST["equipmentID"][$i])) {
                    $equipmentID = intval($_POST["equipmentID"][$i]);
                    $qty = intval($_POST["quantity"][$i] ?? 1);
                    $rate = floatval($_POST["rentalRateItem"][$i] ?? 0);

                    $DB->table = $DB->pre . "pnp_rental_item";
                    $DB->data = array(
                        "rentalID" => $rentalID,
                        "equipmentID" => $equipmentID,
                        "quantity" => $qty,
                        "rentalRate" => $rate,
                        "totalAmount" => $qty * $rate,
                        "itemStatus" => $_POST["rentalStatus"] ?? "Reserved"
                    );
                    $DB->dbInsert();
                }
            }
        }

        setResponse(array("err" => 0, "param" => "id=" . $rentalID));
    } else {
        setResponse(array("err" => 1));
    }
}

function issueEquipment()
{
    global $DB;
    $rentalID = intval($_POST["rentalID"]);

    // Update rental status
    $DB->vals = array("Issued", date("Y-m-d H:i:s"), $_SESSION[SITEURL]["userID"] ?? 0, $rentalID);
    $DB->types = "ssii";
    $DB->sql = "UPDATE " . $DB->pre . "pnp_rental SET rentalStatus=?, issueTime=?, issuedBy=? WHERE rentalID=?";
    $DB->dbQuery();

    // Update item status
    $DB->vals = array("Issued", $rentalID);
    $DB->types = "si";
    $DB->sql = "UPDATE " . $DB->pre . "pnp_rental_item SET itemStatus=? WHERE rentalID=?";
    $DB->dbQuery();

    // Decrease available quantity
    $DB->vals = array($rentalID);
    $DB->types = "i";
    $DB->sql = "SELECT ri.equipmentID, ri.quantity FROM " . $DB->pre . "pnp_rental_item ri WHERE ri.rentalID=?";
    $items = $DB->dbRows();

    foreach ($items as $item) {
        $DB->vals = array($item["quantity"], $item["equipmentID"]);
        $DB->types = "ii";
        $DB->sql = "UPDATE " . $DB->pre . "pnp_equipment SET availableQuantity = availableQuantity - ? WHERE equipmentID=?";
        $DB->dbQuery();
    }

    setResponse(array("err" => 0, "msg" => "Equipment issued successfully"));
}

function returnEquipment()
{
    global $DB;
    $rentalID = intval($_POST["rentalID"]);
    $condition = $_POST["returnCondition"] ?? "Good";
    $damageCharge = floatval($_POST["damageCharge"] ?? 0);
    $damageNotes = $_POST["damageNotes"] ?? "";

    $returnStatus = $damageCharge > 0 ? "Returned-Damaged" : "Returned";

    // Update rental status
    $DB->vals = array($returnStatus, date("Y-m-d H:i:s"), $_SESSION[SITEURL]["userID"] ?? 0, $damageCharge, $damageNotes, $rentalID);
    $DB->types = "ssidsi";
    $DB->sql = "UPDATE " . $DB->pre . "pnp_rental SET rentalStatus=?, actualReturnTime=?, returnedTo=?, damageCharge=?, damageNotes=? WHERE rentalID=?";
    $DB->dbQuery();

    // Update item status
    $DB->vals = array($returnStatus, $rentalID);
    $DB->types = "si";
    $DB->sql = "UPDATE " . $DB->pre . "pnp_rental_item SET itemStatus=? WHERE rentalID=?";
    $DB->dbQuery();

    // Increase available quantity
    $DB->vals = array($rentalID);
    $DB->types = "i";
    $DB->sql = "SELECT ri.equipmentID, ri.quantity FROM " . $DB->pre . "pnp_rental_item ri WHERE ri.rentalID=?";
    $items = $DB->dbRows();

    foreach ($items as $item) {
        $DB->vals = array($item["quantity"], $item["equipmentID"]);
        $DB->types = "ii";
        $DB->sql = "UPDATE " . $DB->pre . "pnp_equipment SET availableQuantity = availableQuantity + ? WHERE equipmentID=?";
        $DB->dbQuery();
    }

    setResponse(array("err" => 0, "msg" => "Equipment returned successfully"));
}

// Handle AJAX actions
$isRentalAction = isset($_POST["xAction"]) &&
                  isset($_POST["modName"]) &&
                  $_POST["modName"] === "pnp-rental";

if ($isRentalAction) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest(true, true); // Session-based auth
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addRental(); break;
            case "UPDATE": updateRental(); break;
            case "ISSUE": issueEquipment(); break;
            case "RETURN": returnEquipment(); break;
        }
    }
    echo json_encode($MXRES);
} else if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest(true, true); // Session-based auth
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addRental(); break;
            case "UPDATE": updateRental(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "pnp_rental", "PK" => "rentalID"));
}
