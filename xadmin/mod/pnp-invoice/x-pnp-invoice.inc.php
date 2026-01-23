<?php
/*
addInvoice = To save Invoice data.
updateInvoice = To update Invoice data.
generateInvoiceNo = Generate unique invoice number.
generateFromBooking = Create invoice from booking.
generateFromRental = Create invoice from rental.
*/

function generateInvoiceNo()
{
    global $DB;
    $prefix = "INV-" . date("Ymd") . "-";
    $DB->sql = "SELECT invoiceNo FROM " . $DB->pre . "pnp_invoice
                WHERE invoiceNo LIKE '" . $prefix . "%'
                ORDER BY invoiceNo DESC LIMIT 1";
    $row = $DB->dbRow();
    $nextNum = 1;
    if ($DB->numRows > 0) {
        $lastNum = intval(substr($row['invoiceNo'], -4));
        $nextNum = $lastNum + 1;
    }
    return $prefix . str_pad($nextNum, 4, "0", STR_PAD_LEFT);
}

function addInvoice()
{
    global $DB;

    // Generate invoice number
    if (empty($_POST["invoiceNo"])) {
        $_POST["invoiceNo"] = generateInvoiceNo();
    }

    // Calculate tax amounts
    $subtotal = floatval($_POST["subtotal"] ?? 0);
    $discount = floatval($_POST["discountAmount"] ?? 0);
    $taxable = $subtotal - $discount;
    $cgstRate = floatval($_POST["cgstRate"] ?? 9);
    $sgstRate = floatval($_POST["sgstRate"] ?? 9);

    $_POST["taxableAmount"] = $taxable;
    $_POST["cgstAmount"] = round($taxable * $cgstRate / 100, 2);
    $_POST["sgstAmount"] = round($taxable * $sgstRate / 100, 2);
    $_POST["totalAmount"] = $taxable + $_POST["cgstAmount"] + $_POST["sgstAmount"];

    $_POST["createdBy"] = $_SESSION[SITEURL]["userID"] ?? 0;

    $DB->table = $DB->pre . "pnp_invoice";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        setResponse(array("err" => 0, "param" => "id=" . $DB->insertID));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateInvoice()
{
    global $DB;
    $invoiceID = intval($_POST["invoiceID"]);

    // Calculate tax amounts
    $subtotal = floatval($_POST["subtotal"] ?? 0);
    $discount = floatval($_POST["discountAmount"] ?? 0);
    $taxable = $subtotal - $discount;
    $cgstRate = floatval($_POST["cgstRate"] ?? 9);
    $sgstRate = floatval($_POST["sgstRate"] ?? 9);

    $_POST["taxableAmount"] = $taxable;
    $_POST["cgstAmount"] = round($taxable * $cgstRate / 100, 2);
    $_POST["sgstAmount"] = round($taxable * $sgstRate / 100, 2);
    $_POST["totalAmount"] = $taxable + $_POST["cgstAmount"] + $_POST["sgstAmount"];

    $DB->table = $DB->pre . "pnp_invoice";
    $DB->data = $_POST;
    if ($DB->dbUpdate("invoiceID=?", "i", array($invoiceID))) {
        setResponse(array("err" => 0, "param" => "id=" . $invoiceID));
    } else {
        setResponse(array("err" => 1));
    }
}

function cancelInvoice()
{
    global $DB;

    $invoiceID = intval($_POST["invoiceID"]);
    $reason = trim($_POST["reason"] ?? "");

    $DB->vals = array($invoiceID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT i.*, l.warehouseID
                FROM " . $DB->pre . "pnp_invoice i
                LEFT JOIN " . $DB->pre . "pnp_location l ON i.locationID = l.locationID
                WHERE i.invoiceID=? AND i.status=?";
    $invoice = $DB->dbRow();

    if (!$invoice) {
        setResponse(array("err" => 1, "msg" => "Invoice not found"));
        return;
    }

    // Check if invoice has payments - auto generate credit note
    if (floatval($invoice["paidAmount"] ?? 0) > 0) {
        $creditNoteID = autoGeneratePnpCreditNote($invoice, $reason);
        if ($creditNoteID) {
            // Get the credit note number
            $DB->vals = array($creditNoteID);
            $DB->types = "i";
            $DB->sql = "SELECT creditNoteNo FROM " . $DB->pre . "credit_note WHERE creditNoteID=?";
            $cnRow = $DB->dbRow();
            $creditNoteNo = $cnRow["creditNoteNo"] ?? "";

            // Mark invoice as cancelled
            $DB->vals = array("Cancelled", date("Y-m-d H:i:s"), $invoiceID);
            $DB->types = "ssi";
            $DB->sql = "UPDATE " . $DB->pre . "pnp_invoice SET invoiceStatus=?, modified=? WHERE invoiceID=?";
            $DB->dbQuery();

            setResponse(array(
                "err" => 0,
                "msg" => "Invoice cancelled successfully. Credit Note " . $creditNoteNo . " has been auto-generated.",
                "creditNoteID" => $creditNoteID,
                "creditNoteNo" => $creditNoteNo
            ));
        } else {
            setResponse(array("err" => 1, "msg" => "Failed to generate credit note"));
        }
        return;
    }

    // Restore stock if invoice has linked retail sale items
    $stockRestored = false;
    if (!empty($invoice["saleID"]) && !empty($invoice["warehouseID"])) {
        $stockRestored = restorePnpInvoiceStock($invoice["saleID"], $invoice["warehouseID"], $invoice["invoiceNo"], $reason);
    }

    // Update invoice status to Cancelled
    $notes = $invoice["notes"] ?? "";
    if ($reason) {
        $notes .= ($notes ? "\n" : "") . "Cancelled: " . $reason . " (" . date("d-M-Y H:i") . ")";
    }

    $DB->vals = array("Cancelled", $notes, date("Y-m-d H:i:s"), $invoiceID);
    $DB->types = "sssi";
    $DB->sql = "UPDATE " . $DB->pre . "pnp_invoice SET invoiceStatus=?, notes=?, modified=? WHERE invoiceID=?";
    $DB->dbQuery();

    $msg = "Invoice cancelled successfully";
    if ($stockRestored) {
        $msg .= ". Stock has been restored to warehouse.";
    }
    setResponse(array("err" => 0, "msg" => $msg));
}

/**
 * Restore stock to warehouse when PNP invoice is cancelled
 */
function restorePnpInvoiceStock($saleID, $warehouseID, $invoiceNo, $reason = "")
{
    global $DB;

    // Get sale items
    $DB->vals = array($saleID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT productID, productName, productSKU, quantity FROM " . $DB->pre . "pnp_retail_sale_item WHERE saleID=? AND status=?";
    $items = $DB->dbRows();

    if (empty($items)) return false;

    $restored = false;
    foreach ($items as $item) {
        $productID = intval($item["productID"]);
        $quantity = floatval($item["quantity"]);

        if ($productID < 1 || $quantity <= 0) continue;

        // Get current stock for this product in this warehouse
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
            "transactionType" => "Invoice-Cancel",
            "transactionDate" => date("Y-m-d H:i:s"),
            "referenceType" => "PNP-Invoice",
            "referenceNumber" => $invoiceNo,
            "qtyIn" => $quantity,
            "qtyOut" => 0,
            "balanceQty" => $newQty,
            "notes" => "Stock restored - PNP Invoice cancelled. " . ($reason ? "Reason: " . $reason : ""),
            "createdBy" => $_SESSION[SITEURL]["userID"] ?? 0,
            "created" => date("Y-m-d H:i:s")
        );
        $DB->dbInsert();
        $restored = true;
    }

    return $restored;
}

function generateFromBooking($bookingID)
{
    global $DB;

    $DB->vals = array($bookingID);
    $DB->types = "i";
    $DB->sql = "SELECT b.*, l.locationName FROM " . $DB->pre . "pnp_booking b
                LEFT JOIN " . $DB->pre . "pnp_location l ON b.locationID=l.locationID
                WHERE b.bookingID=?";
    $booking = $DB->dbRow();

    if (!$booking) return false;

    // Check if invoice already exists
    $DB->vals = array($bookingID);
    $DB->types = "i";
    $DB->sql = "SELECT invoiceID FROM " . $DB->pre . "pnp_invoice WHERE bookingID=? AND status=1";
    $existing = $DB->dbRow();
    if ($existing) return $existing["invoiceID"];

    // Calculate tax
    $subtotal = floatval($booking["baseAmount"]);
    $discount = floatval($booking["discountAmount"]);
    $taxable = $subtotal - $discount;
    $cgstAmount = round($taxable * 9 / 100, 2);
    $sgstAmount = round($taxable * 9 / 100, 2);
    $total = $taxable + $cgstAmount + $sgstAmount;

    $DB->table = $DB->pre . "pnp_invoice";
    $DB->data = array(
        "invoiceNo" => generateInvoiceNo(),
        "bookingID" => $bookingID,
        "locationID" => $booking["locationID"],
        "hudelBookingID" => $booking["hudelBookingID"],
        "customerName" => $booking["customerName"],
        "customerPhone" => $booking["customerPhone"],
        "customerEmail" => $booking["customerEmail"],
        "invoiceDate" => date("Y-m-d"),
        "invoiceType" => "Booking",
        "subtotal" => $subtotal,
        "discountAmount" => $discount,
        "taxableAmount" => $taxable,
        "cgstRate" => 9,
        "cgstAmount" => $cgstAmount,
        "sgstRate" => 9,
        "sgstAmount" => $sgstAmount,
        "totalAmount" => $total,
        "paymentMethod" => $booking["paymentMethod"],
        "paymentReference" => $booking["paymentReference"],
        "paymentStatus" => $booking["paymentStatus"],
        "paidAmount" => $booking["paidAmount"] ?? $total,
        "invoiceStatus" => "Generated",
        "createdBy" => $_SESSION[SITEURL]["userID"] ?? 0
    );

    if ($DB->dbInsert()) {
        // Update booking with invoice reference
        $DB->vals = array($DB->insertID, generateInvoiceNo(), $bookingID);
        $DB->types = "isi";
        $DB->sql = "UPDATE " . $DB->pre . "pnp_booking SET invoiceGenerated=1 WHERE bookingID=?";
        $DB->dbQuery();
        return $DB->insertID;
    }
    return false;
}

/**
 * Auto-generate credit note for PNP invoice cancellation
 */
function autoGeneratePnpCreditNote($invoice, $reason = "")
{
    global $DB;

    // Generate credit note number
    $prefix = "CN-" . date("Ymd") . "-";
    $DB->sql = "SELECT creditNoteNo FROM " . $DB->pre . "credit_note WHERE creditNoteNo LIKE '" . $prefix . "%' ORDER BY creditNoteNo DESC LIMIT 1";
    $row = $DB->dbRow();
    $nextNum = 1;
    if ($DB->numRows > 0) {
        $lastNum = intval(substr($row['creditNoteNo'], -4));
        $nextNum = $lastNum + 1;
    }
    $creditNoteNo = $prefix . str_pad($nextNum, 4, "0", STR_PAD_LEFT);

    // Get warehouse from location
    $warehouseID = 0;
    if (!empty($invoice["locationID"])) {
        $DB->vals = array($invoice["locationID"]);
        $DB->types = "i";
        $DB->sql = "SELECT warehouseID FROM " . $DB->pre . "pnp_location WHERE locationID=?";
        $loc = $DB->dbRow();
        $warehouseID = intval($loc["warehouseID"] ?? 0);
    }

    // Create credit note
    $DB->table = $DB->pre . "credit_note";
    $DB->data = array(
        "creditNoteNo" => $creditNoteNo,
        "creditNoteDate" => date("Y-m-d"),
        "entityType" => "Location",
        "entityID" => $invoice["locationID"] ?? 0,
        "entityName" => $invoice["customerName"] ?? "",
        "entityGSTIN" => $invoice["customerGSTIN"] ?? "",
        "invoiceType" => "PNP",
        "invoiceID" => $invoice["invoiceID"],
        "invoiceNo" => $invoice["invoiceNo"],
        "warehouseID" => $warehouseID,
        "reason" => "Invoice Cancellation",
        "reasonDetails" => $reason,
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
        "adjustedAmount" => 0,
        "balanceAmount" => $invoice["totalAmount"],
        "creditNoteStatus" => "Approved",
        "approvedBy" => $_SESSION[SITEURL]["userID"] ?? 0,
        "approvedDate" => date("Y-m-d H:i:s"),
        "notes" => "Auto-generated on PNP invoice cancellation",
        "createdBy" => $_SESSION[SITEURL]["userID"] ?? 0,
        "status" => 1
    );

    if (!$DB->dbInsert()) {
        return false;
    }

    $creditNoteID = $DB->insertID;

    // Get sale items if linked
    if (!empty($invoice["saleID"])) {
        $DB->vals = array($invoice["saleID"], 1);
        $DB->types = "ii";
        $DB->sql = "SELECT * FROM " . $DB->pre . "pnp_retail_sale_item WHERE saleID=? AND status=?";
        $items = $DB->dbRows();

        foreach ($items as $item) {
            $gstRate = floatval($item["gstRate"] ?? 18);
            $cgst = floatval($item["cgstAmount"] ?? 0);
            $sgst = floatval($item["sgstAmount"] ?? 0);
            $igst = floatval($item["igstAmount"] ?? 0);

            $DB->table = $DB->pre . "credit_note_item";
            $DB->data = array(
                "creditNoteID" => $creditNoteID,
                "productID" => $item["productID"] ?? 0,
                "productSKU" => $item["productSKU"] ?? "",
                "productName" => $item["productName"] ?? "",
                "hsnCode" => $item["hsnCode"] ?? "",
                "quantity" => $item["quantity"] ?? 1,
                "uom" => $item["uom"] ?? "Pcs",
                "unitPrice" => $item["unitPrice"] ?? 0,
                "discountPercent" => $item["discountPercent"] ?? 0,
                "discountAmount" => $item["discountAmount"] ?? 0,
                "taxableAmount" => $item["taxableAmount"] ?? 0,
                "gstRate" => $gstRate,
                "cgstAmount" => $cgst,
                "sgstAmount" => $sgst,
                "igstAmount" => $igst,
                "totalAmount" => $item["totalAmount"] ?? 0,
                "stockRestored" => 0,
                "status" => 1
            );
            $DB->dbInsert();
        }
    }

    // Restore stock if warehouse is set
    if ($warehouseID > 0) {
        restorePnpCreditNoteStock($creditNoteID, $warehouseID, $creditNoteNo);
    }

    return $creditNoteID;
}

/**
 * Restore stock for PNP credit note
 */
function restorePnpCreditNoteStock($creditNoteID, $warehouseID, $creditNoteNo)
{
    global $DB;

    $DB->vals = array($creditNoteID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM " . $DB->pre . "credit_note_item WHERE creditNoteID=? AND status=?";
    $items = $DB->dbRows();

    foreach ($items as $item) {
        $productID = intval($item["productID"]);
        $quantity = floatval($item["quantity"]);

        if ($productID < 1 || $quantity <= 0) continue;

        // Get current stock
        $DB->vals = array(1, $warehouseID, $productID);
        $DB->types = "iii";
        $DB->sql = "SELECT stockID, quantity as stockQty FROM " . $DB->pre . "inventory_stock WHERE status=? AND warehouseID=? AND productID=?";
        $currentStock = $DB->dbRow();

        $currentQty = floatval($currentStock["stockQty"] ?? 0);
        $stockID = $currentStock["stockID"] ?? 0;
        $newQty = $currentQty + $quantity;

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
            "notes" => "Stock restored via Credit Note (PNP Invoice Cancellation)",
            "createdBy" => $_SESSION[SITEURL]["userID"] ?? 0,
            "created" => date("Y-m-d H:i:s")
        );
        $DB->dbInsert();

        // Mark item stock restored
        $DB->vals = array(1, $item["itemID"]);
        $DB->types = "ii";
        $DB->sql = "UPDATE " . $DB->pre . "credit_note_item SET stockRestored=? WHERE itemID=?";
        $DB->dbQuery();
    }
}

// Handle AJAX actions
if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addInvoice(); break;
            case "UPDATE": updateInvoice(); break;
            case "CANCEL": cancelInvoice(); break;
            case "GENERATE_FROM_BOOKING":
                $invoiceID = generateFromBooking(intval($_POST["bookingID"]));
                if ($invoiceID) {
                    setResponse(array("err" => 0, "msg" => "Invoice generated", "invoiceID" => $invoiceID));
                } else {
                    setResponse(array("err" => 1, "msg" => "Failed to generate invoice"));
                }
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "pnp_invoice", "PK" => "invoiceID"));
}
