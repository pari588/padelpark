<?php
/*
generateDebitNoteNo = Generate unique debit note number.
addDebitNote = To save Debit Note data.
updateDebitNote = To update Debit Note data.
approveDebitNote = Approve debit note and update outstanding/stock.
cancelDebitNote = Cancel a debit note.
collectDebitNote = Record payment against debit note.
deductDebitNoteStock = Deduct stock from warehouse.
updateEntityOutstanding = Update entity outstanding balance.
*/

function generateDebitNoteNo()
{
    global $DB;
    $prefix = "DN-" . date("Ymd") . "-";
    $DB->sql = "SELECT debitNoteNo FROM " . $DB->pre . "debit_note
                WHERE debitNoteNo LIKE '" . $prefix . "%'
                ORDER BY debitNoteNo DESC LIMIT 1";
    $row = $DB->dbRow();
    $nextNum = 1;
    if ($DB->numRows > 0) {
        $lastNum = intval(substr($row['debitNoteNo'], -4));
        $nextNum = $lastNum + 1;
    }
    return $prefix . str_pad($nextNum, 4, "0", STR_PAD_LEFT);
}

function addDebitNote()
{
    global $DB;

    // Generate debit note number
    if (empty($_POST["debitNoteNo"])) {
        $_POST["debitNoteNo"] = generateDebitNoteNo();
    }

    // Set default invoiceType if empty
    if (empty($_POST["invoiceType"])) {
        $_POST["invoiceType"] = "Other";
    }

    // Convert entity fields to entityID based on entityType
    $entityType = $_POST["entityType"] ?? "Distributor";
    if ($entityType == "Distributor") {
        $_POST["entityID"] = intval($_POST["distributorID"] ?? 0);
        unset($_POST["distributorID"], $_POST["locationID"], $_POST["customerName"]);
    } elseif ($entityType == "Location") {
        $_POST["entityID"] = intval($_POST["locationID"] ?? 0);
        unset($_POST["distributorID"], $_POST["locationID"], $_POST["customerName"]);
    } elseif ($entityType == "Customer") {
        $_POST["entityID"] = 0; // Customer doesn't have ID
        $_POST["entityName"] = trim($_POST["customerName"] ?? "");
        unset($_POST["distributorID"], $_POST["locationID"], $_POST["customerName"]);
    }

    // Validation
    $errors = array();

    // Validate entity selection
    if ($entityType == "Distributor" || $entityType == "Location") {
        if (empty($_POST["entityID"]) || $_POST["entityID"] <= 0) {
            $errors[] = "Please select a valid " . $entityType;
        }
    } elseif ($entityType == "Customer") {
        if (empty($_POST["entityName"]) || trim($_POST["entityName"]) == "") {
            $errors[] = "Please enter Customer Name";
        }
    }

    // Validate entity name and GSTIN
    if (empty($_POST["entityName"]) || trim($_POST["entityName"]) == "") {
        $errors[] = "Entity Name is required";
    }

    if (empty($_POST["entityGSTIN"]) || trim($_POST["entityGSTIN"]) == "") {
        $errors[] = "Entity GSTIN is required";
    }

    // Validate amounts
    $subtotal = floatval($_POST["subtotal"] ?? 0);
    if ($subtotal <= 0) {
        $errors[] = "Subtotal must be greater than 0";
    }

    // Return errors if any
    if (!empty($errors)) {
        setResponse(array("err" => 1, "msg" => implode(". ", $errors)));
        return;
    }

    // Calculate tax amounts
    $subtotal = floatval($_POST["subtotal"] ?? 0);
    $discount = floatval($_POST["discountAmount"] ?? 0);
    $taxable = $subtotal - $discount;
    $cgstRate = floatval($_POST["cgstRate"] ?? 9);
    $sgstRate = floatval($_POST["sgstRate"] ?? 9);
    $igstRate = floatval($_POST["igstRate"] ?? 0);

    $_POST["taxableAmount"] = $taxable;
    $_POST["cgstAmount"] = round($taxable * $cgstRate / 100, 2);
    $_POST["sgstAmount"] = round($taxable * $sgstRate / 100, 2);
    $_POST["igstAmount"] = round($taxable * $igstRate / 100, 2);
    $_POST["totalAmount"] = $taxable + $_POST["cgstAmount"] + $_POST["sgstAmount"] + $_POST["igstAmount"];
    $_POST["balanceAmount"] = $_POST["totalAmount"];
    $_POST["collectedAmount"] = 0;
    $_POST["debitNoteStatus"] = "Draft";
    $_POST["createdBy"] = $_SESSION[SITEURL]["userID"] ?? 0;

    // Get items from POST
    $items = json_decode($_POST["items"] ?? "[]", true);
    unset($_POST["items"]);

    $DB->table = $DB->pre . "debit_note";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $debitNoteID = $DB->insertID;

        // Insert items
        if (!empty($items)) {
            foreach ($items as $item) {
                $itemData = array(
                    "debitNoteID" => $debitNoteID,
                    "productID" => intval($item["productID"] ?? 0),
                    "productSKU" => $item["productSKU"] ?? "",
                    "productName" => $item["productName"] ?? "",
                    "hsnCode" => $item["hsnCode"] ?? "",
                    "quantity" => floatval($item["quantity"] ?? 1),
                    "uom" => $item["uom"] ?? "Pcs",
                    "unitPrice" => floatval($item["unitPrice"] ?? 0),
                    "discountPercent" => floatval($item["discountPercent"] ?? 0),
                    "discountAmount" => floatval($item["discountAmount"] ?? 0),
                    "taxableAmount" => floatval($item["taxableAmount"] ?? 0),
                    "gstRate" => floatval($item["gstRate"] ?? 18),
                    "cgstAmount" => floatval($item["cgstAmount"] ?? 0),
                    "sgstAmount" => floatval($item["sgstAmount"] ?? 0),
                    "igstAmount" => floatval($item["igstAmount"] ?? 0),
                    "totalAmount" => floatval($item["totalAmount"] ?? 0),
                    "stockDeducted" => 0,
                    "status" => 1
                );
                $DB->table = $DB->pre . "debit_note_item";
                $DB->data = $itemData;
                $DB->dbInsert();
            }
        }

        setResponse(array("err" => 0, "param" => "id=" . $debitNoteID, "msg" => "Debit Note created successfully"));
    } else {
        setResponse(array("err" => 1, "msg" => "Failed to create Debit Note"));
    }
}

function updateDebitNote()
{
    global $DB;
    $debitNoteID = intval($_POST["debitNoteID"]);

    // Check if already approved
    $DB->vals = array($debitNoteID);
    $DB->types = "i";
    $DB->sql = "SELECT debitNoteStatus FROM " . $DB->pre . "debit_note WHERE debitNoteID=?";
    $existing = $DB->dbRow();
    if ($existing && $existing["debitNoteStatus"] != "Draft") {
        setResponse(array("err" => 1, "msg" => "Cannot edit approved debit note"));
        return;
    }

    // Set default invoiceType if empty
    if (empty($_POST["invoiceType"])) {
        $_POST["invoiceType"] = "Other";
    }

    // Convert entity fields to entityID based on entityType
    $entityType = $_POST["entityType"] ?? "Distributor";
    if ($entityType == "Distributor") {
        $_POST["entityID"] = intval($_POST["distributorID"] ?? 0);
        unset($_POST["distributorID"], $_POST["locationID"], $_POST["customerName"]);
    } elseif ($entityType == "Location") {
        $_POST["entityID"] = intval($_POST["locationID"] ?? 0);
        unset($_POST["distributorID"], $_POST["locationID"], $_POST["customerName"]);
    } elseif ($entityType == "Customer") {
        $_POST["entityID"] = 0; // Customer doesn't have ID
        $_POST["entityName"] = trim($_POST["customerName"] ?? "");
        unset($_POST["distributorID"], $_POST["locationID"], $_POST["customerName"]);
    }

    // Validation
    $errors = array();

    // Validate entity selection
    if ($entityType == "Distributor" || $entityType == "Location") {
        if (empty($_POST["entityID"]) || $_POST["entityID"] <= 0) {
            $errors[] = "Please select a valid " . $entityType;
        }
    } elseif ($entityType == "Customer") {
        if (empty($_POST["entityName"]) || trim($_POST["entityName"]) == "") {
            $errors[] = "Please enter Customer Name";
        }
    }

    // Validate entity name and GSTIN
    if (empty($_POST["entityName"]) || trim($_POST["entityName"]) == "") {
        $errors[] = "Entity Name is required";
    }

    if (empty($_POST["entityGSTIN"]) || trim($_POST["entityGSTIN"]) == "") {
        $errors[] = "Entity GSTIN is required";
    }

    // Validate amounts
    $subtotal = floatval($_POST["subtotal"] ?? 0);
    if ($subtotal <= 0) {
        $errors[] = "Subtotal must be greater than 0";
    }

    // Return errors if any
    if (!empty($errors)) {
        setResponse(array("err" => 1, "msg" => implode(". ", $errors)));
        return;
    }

    // Calculate tax amounts
    $discount = floatval($_POST["discountAmount"] ?? 0);
    $taxable = $subtotal - $discount;
    $cgstRate = floatval($_POST["cgstRate"] ?? 9);
    $sgstRate = floatval($_POST["sgstRate"] ?? 9);
    $igstRate = floatval($_POST["igstRate"] ?? 0);

    $_POST["taxableAmount"] = $taxable;
    $_POST["cgstAmount"] = round($taxable * $cgstRate / 100, 2);
    $_POST["sgstAmount"] = round($taxable * $sgstRate / 100, 2);
    $_POST["igstAmount"] = round($taxable * $igstRate / 100, 2);
    $_POST["totalAmount"] = $taxable + $_POST["cgstAmount"] + $_POST["sgstAmount"] + $_POST["igstAmount"];
    $_POST["balanceAmount"] = $_POST["totalAmount"];

    // Get items from POST
    $items = json_decode($_POST["items"] ?? "[]", true);
    unset($_POST["items"]);

    $DB->table = $DB->pre . "debit_note";
    $DB->data = $_POST;
    if ($DB->dbUpdate("debitNoteID=?", "i", array($debitNoteID))) {
        // Delete old items and insert new
        $DB->vals = array($debitNoteID);
        $DB->types = "i";
        $DB->sql = "DELETE FROM " . $DB->pre . "debit_note_item WHERE debitNoteID=?";
        $DB->dbQuery();

        // Insert items
        if (!empty($items)) {
            foreach ($items as $item) {
                $itemData = array(
                    "debitNoteID" => $debitNoteID,
                    "productID" => intval($item["productID"] ?? 0),
                    "productSKU" => $item["productSKU"] ?? "",
                    "productName" => $item["productName"] ?? "",
                    "hsnCode" => $item["hsnCode"] ?? "",
                    "quantity" => floatval($item["quantity"] ?? 1),
                    "uom" => $item["uom"] ?? "Pcs",
                    "unitPrice" => floatval($item["unitPrice"] ?? 0),
                    "discountPercent" => floatval($item["discountPercent"] ?? 0),
                    "discountAmount" => floatval($item["discountAmount"] ?? 0),
                    "taxableAmount" => floatval($item["taxableAmount"] ?? 0),
                    "gstRate" => floatval($item["gstRate"] ?? 18),
                    "cgstAmount" => floatval($item["cgstAmount"] ?? 0),
                    "sgstAmount" => floatval($item["sgstAmount"] ?? 0),
                    "igstAmount" => floatval($item["igstAmount"] ?? 0),
                    "totalAmount" => floatval($item["totalAmount"] ?? 0),
                    "stockDeducted" => 0,
                    "status" => 1
                );
                $DB->table = $DB->pre . "debit_note_item";
                $DB->data = $itemData;
                $DB->dbInsert();
            }
        }

        setResponse(array("err" => 0, "param" => "id=" . $debitNoteID, "msg" => "Debit Note updated successfully"));
    } else {
        setResponse(array("err" => 1, "msg" => "Failed to update Debit Note"));
    }
}

function approveDebitNote()
{
    global $DB;
    $debitNoteID = intval($_POST["debitNoteID"]);

    // Get debit note details
    $DB->vals = array($debitNoteID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM " . $DB->pre . "debit_note WHERE debitNoteID=? AND status=?";
    $dn = $DB->dbRow();

    if (!$dn) {
        setResponse(array("err" => 1, "msg" => "Debit Note not found"));
        return;
    }

    if ($dn["debitNoteStatus"] != "Draft") {
        setResponse(array("err" => 1, "msg" => "Debit Note is not in Draft status"));
        return;
    }

    // Deduct stock if warehouse is set
    $stockDeducted = false;
    if (!empty($dn["warehouseID"])) {
        $stockDeducted = deductDebitNoteStock($debitNoteID, $dn["warehouseID"], $dn["debitNoteNo"]);
    }

    // Update entity outstanding (increase by debit note amount)
    updateEntityOutstandingDN($dn["entityType"], $dn["entityID"], $dn["totalAmount"], "add");

    // Update debit note status
    $DB->vals = array("Approved", $_SESSION[SITEURL]["userID"] ?? 0, date("Y-m-d H:i:s"), $debitNoteID);
    $DB->types = "sisi";
    $DB->sql = "UPDATE " . $DB->pre . "debit_note SET debitNoteStatus=?, approvedBy=?, approvedDate=? WHERE debitNoteID=?";
    $DB->dbQuery();

    $msg = "Debit Note approved successfully";
    if ($stockDeducted) {
        $msg .= ". Stock deducted from warehouse.";
    }
    setResponse(array("err" => 0, "msg" => $msg));
}

function cancelDebitNote()
{
    global $DB;
    $debitNoteID = intval($_POST["debitNoteID"]);

    // Get debit note details
    $DB->vals = array($debitNoteID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM " . $DB->pre . "debit_note WHERE debitNoteID=? AND status=?";
    $dn = $DB->dbRow();

    if (!$dn) {
        setResponse(array("err" => 1, "msg" => "Debit Note not found"));
        return;
    }

    // If approved, reverse the changes
    if ($dn["debitNoteStatus"] == "Approved" || $dn["debitNoteStatus"] == "Partially Collected") {
        // Reverse outstanding (subtract the amount)
        updateEntityOutstandingDN($dn["entityType"], $dn["entityID"], $dn["totalAmount"], "subtract");

        // Reverse stock (restore what was deducted)
        if (!empty($dn["warehouseID"])) {
            reverseStockDeduction($debitNoteID, $dn["warehouseID"], $dn["debitNoteNo"]);
        }
    }

    // Update status
    $DB->vals = array("Cancelled", $debitNoteID);
    $DB->types = "si";
    $DB->sql = "UPDATE " . $DB->pre . "debit_note SET debitNoteStatus=? WHERE debitNoteID=?";
    $DB->dbQuery();

    setResponse(array("err" => 0, "msg" => "Debit Note cancelled successfully"));
}

function deductDebitNoteStock($debitNoteID, $warehouseID, $debitNoteNo)
{
    global $DB;

    // Get debit note items
    $DB->vals = array($debitNoteID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM " . $DB->pre . "debit_note_item WHERE debitNoteID=? AND status=?";
    $items = $DB->dbRows();

    if (empty($items)) return false;

    $deducted = false;
    foreach ($items as $item) {
        $productID = intval($item["productID"]);
        $quantity = floatval($item["quantity"]);

        if ($productID < 1 || $quantity <= 0) continue;

        // Get current stock
        $DB->vals = array(1, $warehouseID, $productID);
        $DB->types = "iii";
        $DB->sql = "SELECT stockID, quantity as stockQty, availableQty FROM " . $DB->pre . "inventory_stock WHERE status=? AND warehouseID=? AND productID=?";
        $currentStock = $DB->dbRow();

        if ($currentStock) {
            $currentQty = floatval($currentStock["stockQty"] ?? 0);
            $newQty = max(0, $currentQty - $quantity);

            $DB->vals = array($newQty, $newQty, date("Y-m-d H:i:s"), $currentStock["stockID"]);
            $DB->types = "ddsi";
            $DB->sql = "UPDATE " . $DB->pre . "inventory_stock SET quantity=?, availableQty=?, lastUpdated=? WHERE stockID=?";
            $DB->dbQuery();

            // Record in stock ledger
            $DB->table = $DB->pre . "stock_ledger";
            $DB->data = array(
                "warehouseID" => $warehouseID,
                "productID" => $productID,
                "transactionType" => "Debit-Note",
                "transactionDate" => date("Y-m-d H:i:s"),
                "referenceType" => "Debit-Note",
                "referenceNumber" => $debitNoteNo,
                "qtyIn" => 0,
                "qtyOut" => $quantity,
                "balanceQty" => $newQty,
                "notes" => "Stock deducted via Debit Note " . $debitNoteNo,
                "createdBy" => $_SESSION[SITEURL]["userID"] ?? 0,
                "created" => date("Y-m-d H:i:s")
            );
            $DB->dbInsert();

            // Mark item as stock deducted
            $DB->vals = array(1, $item["itemID"]);
            $DB->types = "ii";
            $DB->sql = "UPDATE " . $DB->pre . "debit_note_item SET stockDeducted=? WHERE itemID=?";
            $DB->dbQuery();

            $deducted = true;
        }
    }

    return $deducted;
}

function reverseStockDeduction($debitNoteID, $warehouseID, $debitNoteNo)
{
    global $DB;

    // Get debit note items that had stock deducted
    $DB->vals = array($debitNoteID, 1, 1);
    $DB->types = "iii";
    $DB->sql = "SELECT * FROM " . $DB->pre . "debit_note_item WHERE debitNoteID=? AND status=? AND stockDeducted=?";
    $items = $DB->dbRows();

    if (empty($items)) return false;

    foreach ($items as $item) {
        $productID = intval($item["productID"]);
        $quantity = floatval($item["quantity"]);

        if ($productID < 1 || $quantity <= 0) continue;

        // Get current stock
        $DB->vals = array(1, $warehouseID, $productID);
        $DB->types = "iii";
        $DB->sql = "SELECT stockID, quantity as stockQty FROM " . $DB->pre . "inventory_stock WHERE status=? AND warehouseID=? AND productID=?";
        $currentStock = $DB->dbRow();

        if ($currentStock) {
            $newQty = floatval($currentStock["stockQty"]) + $quantity;

            $DB->vals = array($newQty, $newQty, date("Y-m-d H:i:s"), $currentStock["stockID"]);
            $DB->types = "ddsi";
            $DB->sql = "UPDATE " . $DB->pre . "inventory_stock SET quantity=?, availableQty=?, lastUpdated=? WHERE stockID=?";
            $DB->dbQuery();

            // Record in stock ledger
            $DB->table = $DB->pre . "stock_ledger";
            $DB->data = array(
                "warehouseID" => $warehouseID,
                "productID" => $productID,
                "transactionType" => "DN-Reversal",
                "transactionDate" => date("Y-m-d H:i:s"),
                "referenceType" => "Debit-Note",
                "referenceNumber" => $debitNoteNo,
                "qtyIn" => $quantity,
                "qtyOut" => 0,
                "balanceQty" => $newQty,
                "notes" => "Stock reversal - Debit Note " . $debitNoteNo . " cancelled",
                "createdBy" => $_SESSION[SITEURL]["userID"] ?? 0,
                "created" => date("Y-m-d H:i:s")
            );
            $DB->dbInsert();
        }
    }

    return true;
}

function collectDebitNote()
{
    global $DB;
    $debitNoteID = intval($_POST["debitNoteID"]);
    $collectAmount = floatval($_POST["collectAmount"]);
    $paymentMethod = trim($_POST["paymentMethod"] ?? "");
    $paymentReference = trim($_POST["paymentReference"] ?? "");
    $notes = trim($_POST["notes"] ?? "");

    // Get debit note
    $DB->vals = array($debitNoteID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM " . $DB->pre . "debit_note WHERE debitNoteID=? AND status=?";
    $dn = $DB->dbRow();

    if (!$dn) {
        setResponse(array("err" => 1, "msg" => "Debit Note not found"));
        return;
    }

    if ($dn["debitNoteStatus"] == "Draft" || $dn["debitNoteStatus"] == "Cancelled") {
        setResponse(array("err" => 1, "msg" => "Debit Note must be approved before collection"));
        return;
    }

    if ($collectAmount > $dn["balanceAmount"]) {
        setResponse(array("err" => 1, "msg" => "Collection amount exceeds balance"));
        return;
    }

    // Record adjustment
    $DB->table = $DB->pre . "note_adjustment";
    $DB->data = array(
        "noteType" => "Debit",
        "noteID" => $debitNoteID,
        "noteNo" => $dn["debitNoteNo"],
        "invoiceType" => "Collection",
        "invoiceID" => 0,
        "invoiceNo" => $paymentMethod . ($paymentReference ? " - " . $paymentReference : ""),
        "adjustedAmount" => $collectAmount,
        "adjustmentDate" => date("Y-m-d"),
        "notes" => $notes,
        "createdBy" => $_SESSION[SITEURL]["userID"] ?? 0
    );
    $DB->dbInsert();

    // Update debit note balance
    $newCollected = $dn["collectedAmount"] + $collectAmount;
    $newBalance = $dn["totalAmount"] - $newCollected;
    $newStatus = $newBalance <= 0 ? "Fully Collected" : "Partially Collected";

    $DB->vals = array($newCollected, $newBalance, $newStatus, $debitNoteID);
    $DB->types = "ddsi";
    $DB->sql = "UPDATE " . $DB->pre . "debit_note SET collectedAmount=?, balanceAmount=?, debitNoteStatus=? WHERE debitNoteID=?";
    $DB->dbQuery();

    // Update entity outstanding (reduce by collected amount)
    updateEntityOutstandingDN($dn["entityType"], $dn["entityID"], $collectAmount, "subtract");

    setResponse(array("err" => 0, "msg" => "Collection recorded successfully"));
}

function updateEntityOutstandingDN($entityType, $entityID, $amount, $operation)
{
    global $DB;

    $table = "";
    $pk = "";
    $field = "currentOutstanding";

    switch ($entityType) {
        case "Distributor":
            $table = $DB->pre . "distributor";
            $pk = "distributorID";
            break;
        case "Customer":
            $table = $DB->pre . "customer";
            $pk = "customerID";
            break;
        case "Location":
            return;
        default:
            return;
    }

    // Check if table exists
    $DB->sql = "SHOW TABLES LIKE '" . $table . "'";
    $DB->dbRow();
    if ($DB->numRows == 0) return;

    $sign = ($operation == "add") ? "+" : "-";
    $DB->vals = array($entityID);
    $DB->types = "i";
    $DB->sql = "UPDATE " . $table . " SET " . $field . " = COALESCE(" . $field . ", 0) " . $sign . " " . floatval($amount) . " WHERE " . $pk . "=?";
    $DB->dbQuery();
}

function getEntityDetails()
{
    global $DB, $MXRES;
    $entityType = trim($_POST["entityType"] ?? "");
    $entityID = intval($_POST["entityID"] ?? 0);

    if (!$entityType || !$entityID) {
        setResponse(array("err" => 1, "msg" => "Invalid parameters"));
        return;
    }

    $data = array();

    if ($entityType == "Distributor") {
        $DB->vals = array($entityID, 1);
        $DB->types = "ii";
        $DB->sql = "SELECT companyName, gstin, billingAddress, billingState, billingStateCode FROM " . $DB->pre . "distributor WHERE distributorID=? AND status=?";
        $result = $DB->dbRow();
        if ($result) {
            $data = array(
                "name" => $result["companyName"],
                "gstin" => $result["gstin"] ?? "",
                "address" => $result["billingAddress"] ?? "",
                "state" => $result["billingState"] ?? "",
                "stateCode" => $result["billingStateCode"] ?? ""
            );
        }
    } elseif ($entityType == "Location") {
        $DB->vals = array($entityID, 1);
        $DB->types = "ii";
        $DB->sql = "SELECT locationName, gstin, address, state FROM " . $DB->pre . "pnp_location WHERE locationID=? AND status=?";
        $result = $DB->dbRow();
        if ($result) {
            $data = array(
                "name" => $result["locationName"],
                "gstin" => $result["gstin"] ?? "",
                "address" => $result["address"] ?? "",
                "state" => $result["state"] ?? "",
                "stateCode" => ""
            );
        }
    }

    if (!empty($data)) {
        $MXRES["err"] = 0;
        $MXRES["data"] = $data;
        $MXRES["msg"] = "Entity details fetched successfully";
    } else {
        setResponse(array("err" => 1, "msg" => "Entity not found"));
    }
}

// Handle AJAX actions
if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest(true, true); // Session-based auth
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addDebitNote(); break;
            case "UPDATE": updateDebitNote(); break;
            case "APPROVE": approveDebitNote(); break;
            case "CANCEL": cancelDebitNote(); break;
            case "COLLECT": collectDebitNote(); break;
            case "GET_ENTITY_DETAILS": getEntityDetails(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "debit_note", "PK" => "debitNoteID"));
}
