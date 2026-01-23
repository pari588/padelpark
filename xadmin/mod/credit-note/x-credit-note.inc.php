<?php
/*
generateCreditNoteNo = Generate unique credit note number.
addCreditNote = To save Credit Note data.
updateCreditNote = To update Credit Note data.
approveCreditNote = Approve credit note and update outstanding/stock.
cancelCreditNote = Cancel a credit note.
adjustCreditNote = Adjust credit note against another invoice.
restoreCreditNoteStock = Restore stock to warehouse.
createFromInvoice = Pre-populate credit note from invoice.
getEntityOutstanding = Get current outstanding for entity.
updateEntityOutstanding = Update entity outstanding balance.
*/

function generateCreditNoteNo()
{
    global $DB;
    $prefix = "CN-" . date("Ymd") . "-";
    $DB->sql = "SELECT creditNoteNo FROM " . $DB->pre . "credit_note
                WHERE creditNoteNo LIKE '" . $prefix . "%'
                ORDER BY creditNoteNo DESC LIMIT 1";
    $row = $DB->dbRow();
    $nextNum = 1;
    if ($DB->numRows > 0) {
        $lastNum = intval(substr($row['creditNoteNo'], -4));
        $nextNum = $lastNum + 1;
    }
    return $prefix . str_pad($nextNum, 4, "0", STR_PAD_LEFT);
}

function addCreditNote()
{
    global $DB;

    // Generate credit note number
    if (empty($_POST["creditNoteNo"])) {
        $_POST["creditNoteNo"] = generateCreditNoteNo();
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
    $_POST["adjustedAmount"] = 0;
    $_POST["creditNoteStatus"] = "Draft";
    $_POST["createdBy"] = $_SESSION[SITEURL]["userID"] ?? 0;

    // Get items from POST
    $items = json_decode($_POST["items"] ?? "[]", true);
    unset($_POST["items"]);

    $DB->table = $DB->pre . "credit_note";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $creditNoteID = $DB->insertID;

        // Insert items
        if (!empty($items)) {
            foreach ($items as $item) {
                $itemData = array(
                    "creditNoteID" => $creditNoteID,
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
                    "stockRestored" => 0,
                    "status" => 1
                );
                $DB->table = $DB->pre . "credit_note_item";
                $DB->data = $itemData;
                $DB->dbInsert();
            }
        }

        setResponse(array("err" => 0, "param" => "id=" . $creditNoteID, "msg" => "Credit Note created successfully"));
    } else {
        setResponse(array("err" => 1, "msg" => "Failed to create Credit Note"));
    }
}

function updateCreditNote()
{
    global $DB;
    $creditNoteID = intval($_POST["creditNoteID"]);

    // Check if already approved
    $DB->vals = array($creditNoteID);
    $DB->types = "i";
    $DB->sql = "SELECT creditNoteStatus FROM " . $DB->pre . "credit_note WHERE creditNoteID=?";
    $existing = $DB->dbRow();
    if ($existing && $existing["creditNoteStatus"] != "Draft") {
        setResponse(array("err" => 1, "msg" => "Cannot edit approved credit note"));
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

    // Get items from POST
    $items = json_decode($_POST["items"] ?? "[]", true);
    unset($_POST["items"]);

    $DB->table = $DB->pre . "credit_note";
    $DB->data = $_POST;
    if ($DB->dbUpdate("creditNoteID=?", "i", array($creditNoteID))) {
        // Delete old items and insert new
        $DB->vals = array($creditNoteID);
        $DB->types = "i";
        $DB->sql = "DELETE FROM " . $DB->pre . "credit_note_item WHERE creditNoteID=?";
        $DB->dbQuery();

        // Insert items
        if (!empty($items)) {
            foreach ($items as $item) {
                $itemData = array(
                    "creditNoteID" => $creditNoteID,
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
                    "stockRestored" => 0,
                    "status" => 1
                );
                $DB->table = $DB->pre . "credit_note_item";
                $DB->data = $itemData;
                $DB->dbInsert();
            }
        }

        setResponse(array("err" => 0, "param" => "id=" . $creditNoteID, "msg" => "Credit Note updated successfully"));
    } else {
        setResponse(array("err" => 1, "msg" => "Failed to update Credit Note"));
    }
}

function approveCreditNote()
{
    global $DB;
    $creditNoteID = intval($_POST["creditNoteID"]);

    // Get credit note details
    $DB->vals = array($creditNoteID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM " . $DB->pre . "credit_note WHERE creditNoteID=? AND status=?";
    $cn = $DB->dbRow();

    if (!$cn) {
        setResponse(array("err" => 1, "msg" => "Credit Note not found"));
        return;
    }

    if ($cn["creditNoteStatus"] != "Draft") {
        setResponse(array("err" => 1, "msg" => "Credit Note is not in Draft status"));
        return;
    }

    // Restore stock if warehouse is set
    $stockRestored = false;
    if (!empty($cn["warehouseID"])) {
        $stockRestored = restoreCreditNoteStock($creditNoteID, $cn["warehouseID"], $cn["creditNoteNo"]);
    }

    // Update entity outstanding (reduce by credit note amount)
    updateEntityOutstanding($cn["entityType"], $cn["entityID"], $cn["totalAmount"], "subtract");

    // Update credit note status
    $DB->vals = array("Approved", $_SESSION[SITEURL]["userID"] ?? 0, date("Y-m-d H:i:s"), $creditNoteID);
    $DB->types = "sisi";
    $DB->sql = "UPDATE " . $DB->pre . "credit_note SET creditNoteStatus=?, approvedBy=?, approvedDate=? WHERE creditNoteID=?";
    $DB->dbQuery();

    $msg = "Credit Note approved successfully";
    if ($stockRestored) {
        $msg .= ". Stock restored to warehouse.";
    }
    setResponse(array("err" => 0, "msg" => $msg));
}

function cancelCreditNote()
{
    global $DB;
    $creditNoteID = intval($_POST["creditNoteID"]);

    // Get credit note details
    $DB->vals = array($creditNoteID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM " . $DB->pre . "credit_note WHERE creditNoteID=? AND status=?";
    $cn = $DB->dbRow();

    if (!$cn) {
        setResponse(array("err" => 1, "msg" => "Credit Note not found"));
        return;
    }

    // If approved, reverse the changes
    if ($cn["creditNoteStatus"] == "Approved" || $cn["creditNoteStatus"] == "Partially Adjusted") {
        // Reverse outstanding (add back the amount)
        updateEntityOutstanding($cn["entityType"], $cn["entityID"], $cn["totalAmount"], "add");

        // Reverse stock (deduct what was restored)
        if (!empty($cn["warehouseID"])) {
            reverseStockRestoration($creditNoteID, $cn["warehouseID"], $cn["creditNoteNo"]);
        }
    }

    // Update status
    $DB->vals = array("Cancelled", $creditNoteID);
    $DB->types = "si";
    $DB->sql = "UPDATE " . $DB->pre . "credit_note SET creditNoteStatus=? WHERE creditNoteID=?";
    $DB->dbQuery();

    setResponse(array("err" => 0, "msg" => "Credit Note cancelled successfully"));
}

function restoreCreditNoteStock($creditNoteID, $warehouseID, $creditNoteNo)
{
    global $DB;

    // Get credit note items
    $DB->vals = array($creditNoteID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM " . $DB->pre . "credit_note_item WHERE creditNoteID=? AND status=?";
    $items = $DB->dbRows();

    if (empty($items)) return false;

    $restored = false;
    foreach ($items as $item) {
        $productID = intval($item["productID"]);
        $quantity = floatval($item["quantity"]);

        if ($productID < 1 || $quantity <= 0) continue;

        // Get current stock
        $DB->vals = array(1, $warehouseID, $productID);
        $DB->types = "iii";
        $DB->sql = "SELECT stockID, quantity as stockQty, availableQty FROM " . $DB->pre . "inventory_stock WHERE status=? AND warehouseID=? AND productID=?";
        $currentStock = $DB->dbRow();

        $currentQty = floatval($currentStock["stockQty"] ?? $currentStock["availableQty"] ?? 0);
        $stockID = $currentStock["stockID"] ?? 0;
        $newQty = $currentQty + $quantity;

        // Update or insert stock record
        if ($stockID > 0) {
            $DB->vals = array($newQty, $newQty, date("Y-m-d H:i:s"), $stockID);
            $DB->types = "ddsi";
            $DB->sql = "UPDATE " . $DB->pre . "inventory_stock SET quantity=?, availableQty=?, lastUpdated=? WHERE stockID=?";
            $DB->dbQuery();
        } else {
            $DB->table = $DB->pre . "inventory_stock";
            $DB->data = array(
                "warehouseID" => $warehouseID,
                "productID" => $productID,
                "quantity" => $newQty,
                "availableQty" => $newQty,
                "lastUpdated" => date("Y-m-d H:i:s"),
                "status" => 1
            );
            $DB->dbInsert();
        }

        // Record in stock ledger
        $DB->table = $DB->pre . "stock_ledger";
        $DB->data = array(
            "warehouseID" => $warehouseID,
            "productID" => $productID,
            "transactionType" => "Credit-Note",
            "transactionDate" => date("Y-m-d H:i:s"),
            "referenceType" => "Credit-Note",
            "referenceNumber" => $creditNoteNo,
            "qtyIn" => $quantity,
            "qtyOut" => 0,
            "balanceQty" => $newQty,
            "notes" => "Stock restored via Credit Note " . $creditNoteNo,
            "createdBy" => $_SESSION[SITEURL]["userID"] ?? 0,
            "created" => date("Y-m-d H:i:s")
        );
        $DB->dbInsert();

        // Mark item as stock restored
        $DB->vals = array(1, $item["itemID"]);
        $DB->types = "ii";
        $DB->sql = "UPDATE " . $DB->pre . "credit_note_item SET stockRestored=? WHERE itemID=?";
        $DB->dbQuery();

        $restored = true;
    }

    return $restored;
}

function reverseStockRestoration($creditNoteID, $warehouseID, $creditNoteNo)
{
    global $DB;

    // Get credit note items that had stock restored
    $DB->vals = array($creditNoteID, 1, 1);
    $DB->types = "iii";
    $DB->sql = "SELECT * FROM " . $DB->pre . "credit_note_item WHERE creditNoteID=? AND status=? AND stockRestored=?";
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
            $newQty = max(0, floatval($currentStock["stockQty"]) - $quantity);

            $DB->vals = array($newQty, $newQty, date("Y-m-d H:i:s"), $currentStock["stockID"]);
            $DB->types = "ddsi";
            $DB->sql = "UPDATE " . $DB->pre . "inventory_stock SET quantity=?, availableQty=?, lastUpdated=? WHERE stockID=?";
            $DB->dbQuery();

            // Record in stock ledger
            $DB->table = $DB->pre . "stock_ledger";
            $DB->data = array(
                "warehouseID" => $warehouseID,
                "productID" => $productID,
                "transactionType" => "CN-Reversal",
                "transactionDate" => date("Y-m-d H:i:s"),
                "referenceType" => "Credit-Note",
                "referenceNumber" => $creditNoteNo,
                "qtyIn" => 0,
                "qtyOut" => $quantity,
                "balanceQty" => $newQty,
                "notes" => "Stock reversal - Credit Note " . $creditNoteNo . " cancelled",
                "createdBy" => $_SESSION[SITEURL]["userID"] ?? 0,
                "created" => date("Y-m-d H:i:s")
            );
            $DB->dbInsert();
        }
    }

    return true;
}

function adjustCreditNote()
{
    global $DB;
    $creditNoteID = intval($_POST["creditNoteID"]);
    $targetInvoiceType = $_POST["targetInvoiceType"] ?? "";
    $targetInvoiceID = intval($_POST["targetInvoiceID"]);
    $adjustAmount = floatval($_POST["adjustAmount"]);

    // Get credit note
    $DB->vals = array($creditNoteID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM " . $DB->pre . "credit_note WHERE creditNoteID=? AND status=?";
    $cn = $DB->dbRow();

    if (!$cn) {
        setResponse(array("err" => 1, "msg" => "Credit Note not found"));
        return;
    }

    if ($cn["creditNoteStatus"] == "Draft" || $cn["creditNoteStatus"] == "Cancelled") {
        setResponse(array("err" => 1, "msg" => "Credit Note must be approved before adjustment"));
        return;
    }

    if ($adjustAmount > $cn["balanceAmount"]) {
        setResponse(array("err" => 1, "msg" => "Adjustment amount exceeds balance"));
        return;
    }

    // Get target invoice
    $invoiceTable = "";
    $invoicePK = "";
    $invoiceNoField = "invoiceNo";
    switch ($targetInvoiceType) {
        case "B2B":
            $invoiceTable = $DB->pre . "b2b_invoice";
            $invoicePK = "invoiceID";
            break;
        case "PNP":
            $invoiceTable = $DB->pre . "pnp_invoice";
            $invoicePK = "invoiceID";
            break;
        default:
            setResponse(array("err" => 1, "msg" => "Invalid invoice type"));
            return;
    }

    $DB->vals = array($targetInvoiceID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $invoiceTable . " WHERE " . $invoicePK . "=?";
    $invoice = $DB->dbRow();

    if (!$invoice) {
        setResponse(array("err" => 1, "msg" => "Target invoice not found"));
        return;
    }

    // Record adjustment
    $DB->table = $DB->pre . "note_adjustment";
    $DB->data = array(
        "noteType" => "Credit",
        "noteID" => $creditNoteID,
        "noteNo" => $cn["creditNoteNo"],
        "invoiceType" => $targetInvoiceType,
        "invoiceID" => $targetInvoiceID,
        "invoiceNo" => $invoice[$invoiceNoField],
        "adjustedAmount" => $adjustAmount,
        "adjustmentDate" => date("Y-m-d"),
        "notes" => $_POST["notes"] ?? "",
        "createdBy" => $_SESSION[SITEURL]["userID"] ?? 0
    );
    $DB->dbInsert();

    // Update credit note balance
    $newAdjusted = $cn["adjustedAmount"] + $adjustAmount;
    $newBalance = $cn["totalAmount"] - $newAdjusted;
    $newStatus = $newBalance <= 0 ? "Fully Adjusted" : "Partially Adjusted";

    $DB->vals = array($newAdjusted, $newBalance, $newStatus, $creditNoteID);
    $DB->types = "ddsi";
    $DB->sql = "UPDATE " . $DB->pre . "credit_note SET adjustedAmount=?, balanceAmount=?, creditNoteStatus=? WHERE creditNoteID=?";
    $DB->dbQuery();

    // Update target invoice (add to paid amount, reduce balance)
    $newPaid = floatval($invoice["paidAmount"]) + $adjustAmount;
    $newInvoiceBalance = floatval($invoice["totalAmount"]) - $newPaid;
    $paymentStatus = $newInvoiceBalance <= 0 ? "Paid" : "Partial";

    $DB->vals = array($newPaid, $paymentStatus, $targetInvoiceID);
    $DB->types = "dsi";
    $DB->sql = "UPDATE " . $invoiceTable . " SET paidAmount=?, paymentStatus=? WHERE " . $invoicePK . "=?";
    $DB->dbQuery();

    setResponse(array("err" => 0, "msg" => "Credit Note adjusted successfully"));
}

function createFromInvoice()
{
    global $DB;
    $invoiceType = $_POST["invoiceType"] ?? "";
    $invoiceID = intval($_POST["invoiceID"]);

    $invoiceTable = "";
    $itemTable = "";
    $invoicePK = "";
    $entityType = "";
    $entityIDField = "";
    $entityNameField = "";
    $entityGSTINField = "";

    switch ($invoiceType) {
        case "B2B":
            $invoiceTable = $DB->pre . "b2b_invoice";
            $itemTable = $DB->pre . "b2b_invoice_item";
            $invoicePK = "invoiceID";
            $entityType = "Distributor";
            $entityIDField = "distributorID";
            $entityNameField = "distributorName";
            $entityGSTINField = "distributorGSTIN";
            break;
        case "PNP":
            $invoiceTable = $DB->pre . "pnp_invoice";
            $itemTable = $DB->pre . "pnp_retail_sale_item";
            $invoicePK = "invoiceID";
            $entityType = "Customer";
            $entityIDField = "customerID";
            $entityNameField = "customerName";
            $entityGSTINField = "customerGSTIN";
            break;
        default:
            setResponse(array("err" => 1, "msg" => "Invalid invoice type"));
            return;
    }

    // Get invoice
    $DB->vals = array($invoiceID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $invoiceTable . " WHERE " . $invoicePK . "=?";
    $invoice = $DB->dbRow();

    if (!$invoice) {
        setResponse(array("err" => 1, "msg" => "Invoice not found"));
        return;
    }

    // Get warehouse ID for stock restoration
    $warehouseID = 0;
    if ($invoiceType == "B2B") {
        $warehouseID = intval($invoice["warehouseID"] ?? 0);
    } else if ($invoiceType == "PNP" && !empty($invoice["locationID"])) {
        $DB->vals = array($invoice["locationID"]);
        $DB->types = "i";
        $DB->sql = "SELECT warehouseID FROM " . $DB->pre . "pnp_location WHERE locationID=?";
        $loc = $DB->dbRow();
        $warehouseID = intval($loc["warehouseID"] ?? 0);
    }

    // Get items
    $items = array();
    if ($invoiceType == "B2B") {
        $DB->vals = array($invoiceID, 1);
        $DB->types = "ii";
        $DB->sql = "SELECT * FROM " . $itemTable . " WHERE invoiceID=? AND status=?";
        $items = $DB->dbRows();
    } else if ($invoiceType == "PNP" && !empty($invoice["saleID"])) {
        $DB->vals = array($invoice["saleID"], 1);
        $DB->types = "ii";
        $DB->sql = "SELECT * FROM " . $itemTable . " WHERE saleID=? AND status=?";
        $items = $DB->dbRows();
    }

    // Build response
    $cnData = array(
        "entityType" => $entityType,
        "entityID" => $invoice[$entityIDField] ?? 0,
        "entityName" => $invoice[$entityNameField] ?? "",
        "entityGSTIN" => $invoice[$entityGSTINField] ?? "",
        "invoiceType" => $invoiceType,
        "invoiceID" => $invoiceID,
        "invoiceNo" => $invoice["invoiceNo"],
        "warehouseID" => $warehouseID,
        "reason" => "Invoice Cancellation",
        "subtotal" => $invoice["subtotal"],
        "discountAmount" => $invoice["discountAmount"] ?? 0,
        "taxableAmount" => $invoice["taxableAmount"] ?? $invoice["subtotal"],
        "cgstRate" => $invoice["cgstRate"] ?? 9,
        "cgstAmount" => $invoice["cgstAmount"] ?? 0,
        "sgstRate" => $invoice["sgstRate"] ?? 9,
        "sgstAmount" => $invoice["sgstAmount"] ?? 0,
        "igstRate" => $invoice["igstRate"] ?? 0,
        "igstAmount" => $invoice["igstAmount"] ?? 0,
        "totalAmount" => $invoice["totalAmount"],
        "items" => $items
    );

    setResponse(array("err" => 0, "data" => $cnData));
}

function updateEntityOutstanding($entityType, $entityID, $amount, $operation)
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
            // Check if customer table exists, otherwise skip
            $table = $DB->pre . "customer";
            $pk = "customerID";
            break;
        case "Location":
            // PNP locations may not have outstanding tracking
            return;
        default:
            return;
    }

    // Check if table and column exist
    $DB->sql = "SHOW TABLES LIKE '" . $table . "'";
    $DB->dbRow();
    if ($DB->numRows == 0) return;

    $sign = ($operation == "add") ? "+" : "-";
    $DB->vals = array($entityID);
    $DB->types = "i";
    $DB->sql = "UPDATE " . $table . " SET " . $field . " = COALESCE(" . $field . ", 0) " . $sign . " " . floatval($amount) . " WHERE " . $pk . "=?";
    $DB->dbQuery();
}

function getEntityOutstanding($entityType, $entityID)
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
        default:
            return 0;
    }

    $DB->vals = array($entityID);
    $DB->types = "i";
    $DB->sql = "SELECT " . $field . " FROM " . $table . " WHERE " . $pk . "=?";
    $row = $DB->dbRow();
    return floatval($row[$field] ?? 0);
}

function getInvoiceList()
{
    global $DB;
    $entityType = $_POST["entityType"] ?? "";
    $entityID = intval($_POST["entityID"]);

    $invoices = array();

    if ($entityType == "Distributor") {
        $DB->vals = array($entityID, 1);
        $DB->types = "ii";
        $DB->sql = "SELECT invoiceID, invoiceNo, invoiceDate, totalAmount, paidAmount, (totalAmount - paidAmount) as balanceDue
                    FROM " . $DB->pre . "b2b_invoice
                    WHERE distributorID=? AND status=? AND (totalAmount - paidAmount) > 0
                    ORDER BY invoiceDate DESC";
        $invoices = $DB->dbRows();
    } else if ($entityType == "Customer" || $entityType == "Location") {
        // PNP invoices
        $DB->vals = array(1);
        $DB->types = "i";
        $whereClause = "status=?";

        if ($entityType == "Location" && $entityID > 0) {
            $DB->vals[] = $entityID;
            $DB->types .= "i";
            $whereClause .= " AND locationID=?";
        }

        $DB->sql = "SELECT invoiceID, invoiceNo, invoiceDate, totalAmount, paidAmount, (totalAmount - paidAmount) as balanceDue
                    FROM " . $DB->pre . "pnp_invoice
                    WHERE " . $whereClause . " AND (totalAmount - paidAmount) > 0
                    ORDER BY invoiceDate DESC";
        $invoices = $DB->dbRows();
    }

    setResponse(array("err" => 0, "invoices" => $invoices));
}

// Handle AJAX actions
if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addCreditNote(); break;
            case "UPDATE": updateCreditNote(); break;
            case "APPROVE": approveCreditNote(); break;
            case "CANCEL": cancelCreditNote(); break;
            case "ADJUST": adjustCreditNote(); break;
            case "CREATE_FROM_INVOICE": createFromInvoice(); break;
            case "GET_INVOICE_LIST": getInvoiceList(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "credit_note", "PK" => "creditNoteID"));
}
