<?php
/*
B2B Invoice Module
- Generate invoice from sales order
- Update distributor outstanding
- Track payment status
- Auto-generate invoice number
*/

function generateInvoiceNo()
{
    global $DB;
    $prefix = "INV-" . date("Ymd") . "-";
    $DB->sql = "SELECT MAX(invoiceNo) as lastNo FROM " . $DB->pre . "b2b_invoice WHERE invoiceNo LIKE '$prefix%'";
    $result = $DB->dbRow();
    $lastNo = $result["lastNo"] ?? "";

    if ($lastNo) {
        $seq = intval(substr($lastNo, -4)) + 1;
    } else {
        $seq = 1;
    }
    return $prefix . str_pad($seq, 4, "0", STR_PAD_LEFT);
}

function createFromOrder()
{
    global $DB;

    $orderID = intval($_POST["orderID"]);
    if ($orderID < 1) {
        setResponse(array("err" => 1, "msg" => "Invalid order ID"));
        return;
    }

    // Get order details
    $DB->vals = array($orderID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT o.*, d.companyName, d.gstin, d.creditDays, d.billingAddress as distBillAddr, d.billingState, d.billingStateCode, d.shippingAddress as distShipAddr
                FROM " . $DB->pre . "b2b_sales_order o
                LEFT JOIN " . $DB->pre . "distributor d ON o.distributorID = d.distributorID
                WHERE o.orderID=? AND o.status=?";
    $order = $DB->dbRow();

    if (!$order) {
        setResponse(array("err" => 1, "msg" => "Order not found"));
        return;
    }

    if (in_array($order["orderStatus"], ["Draft", "Cancelled"])) {
        setResponse(array("err" => 1, "msg" => "Order must be confirmed before invoicing"));
        return;
    }

    // Check if invoice already exists for this order
    $DB->vals = array($orderID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT invoiceID FROM " . $DB->pre . "b2b_invoice WHERE orderID=? AND status=? AND invoiceStatus != 'Cancelled'";
    if ($DB->dbRow()) {
        setResponse(array("err" => 1, "msg" => "Invoice already exists for this order"));
        return;
    }

    // Get order items
    $DB->vals = array($orderID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "b2b_sales_order_item WHERE orderID=? AND status=1";
    $items = $DB->dbRows();

    // Generate invoice number
    $invoiceNo = generateInvoiceNo();
    $invoiceDate = $_POST["invoiceDate"] ?? date("Y-m-d");

    // Calculate due date
    $creditDays = intval($order["creditDays"] ?? 30);
    $dueDate = date("Y-m-d", strtotime($invoiceDate . " + $creditDays days"));

    // Create invoice
    $DB->table = $DB->pre . "b2b_invoice";
    $DB->data = array(
        "invoiceNo" => $invoiceNo,
        "invoiceDate" => $invoiceDate,
        "dueDate" => $dueDate,
        "orderID" => $orderID,
        "distributorID" => $order["distributorID"],
        "distributorName" => $order["distributorName"] ?? $order["companyName"],
        "distributorGSTIN" => $order["distributorGSTIN"] ?? $order["gstin"],
        "distributorAddress" => $order["billingAddress"] ?? $order["distBillAddr"],
        "distributorState" => $order["billingState"] ?? "",
        "distributorStateCode" => $order["billingStateCode"] ?? "",
        "warehouseID" => $order["warehouseID"],
        "shippingAddress" => $order["shippingAddress"] ?? $order["distShipAddr"],
        "subtotal" => $order["subtotal"],
        "discountAmount" => $order["discountAmount"],
        "taxableAmount" => $order["taxableAmount"],
        "cgstAmount" => $order["cgstAmount"],
        "sgstAmount" => $order["sgstAmount"],
        "igstAmount" => $order["igstAmount"],
        "totalAmount" => $order["totalAmount"],
        "paidAmount" => 0,
        "balanceAmount" => $order["totalAmount"],
        "paymentTerms" => $order["paymentTerms"] ?? "Net " . $creditDays,
        "invoiceStatus" => "Generated",
        "remarks" => $order["remarks"] ?? "",
        "createdBy" => $_SESSION["ADMINID"] ?? 0,
        "created" => date("Y-m-d H:i:s"),
        "status" => 1
    );

    if (!$DB->dbInsert()) {
        setResponse(array("err" => 1, "msg" => "Failed to create invoice"));
        return;
    }

    $invoiceID = $DB->insertID;

    // Copy items to invoice
    foreach ($items as $item) {
        $DB->table = $DB->pre . "b2b_invoice_item";
        $DB->data = array(
            "invoiceID" => $invoiceID,
            "productID" => $item["productID"],
            "productSKU" => $item["productSKU"],
            "productName" => $item["productName"],
            "hsnCode" => $item["hsnCode"],
            "quantity" => $item["quantity"],
            "uom" => $item["uom"],
            "unitPrice" => $item["unitPrice"],
            "discountPercent" => $item["discountPercent"],
            "discountAmount" => $item["discountAmount"],
            "taxableAmount" => $item["taxableAmount"],
            "gstRate" => $item["gstRate"],
            "cgstAmount" => $item["cgstAmount"],
            "sgstAmount" => $item["sgstAmount"],
            "igstAmount" => $item["igstAmount"],
            "totalAmount" => $item["totalAmount"]
        );
        $DB->dbInsert();
    }

    // Update distributor outstanding
    updateDistributorOutstanding($order["distributorID"], $order["totalAmount"], "add");

    setResponse(array("err" => 0, "invoiceID" => $invoiceID, "invoiceNo" => $invoiceNo, "param" => "id=" . $invoiceID, "msg" => "Invoice created successfully"));
}

function updateDistributorOutstanding($distributorID, $amount, $operation)
{
    global $DB;

    // Get current outstanding
    $DB->vals = array($distributorID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT currentOutstanding FROM " . $DB->pre . "distributor WHERE distributorID=? AND status=?";
    $dist = $DB->dbRow();
    $currentOutstanding = floatval($dist["currentOutstanding"] ?? 0);

    if ($operation == "add") {
        $newOutstanding = $currentOutstanding + $amount;
    } else {
        $newOutstanding = $currentOutstanding - $amount;
    }

    if ($newOutstanding < 0) $newOutstanding = 0;

    // Update distributor outstanding
    $DB->vals = array($newOutstanding, date("Y-m-d H:i:s"), $distributorID);
    $DB->types = "dsi";
    $DB->sql = "UPDATE " . $DB->pre . "distributor SET currentOutstanding=?, modified=? WHERE distributorID=?";
    $DB->dbQuery();

    return $newOutstanding;
}

function addInvoice()
{
    global $DB;

    $distributorID = intval($_POST["distributorID"]);
    if ($distributorID < 1) {
        setResponse(array("err" => 1, "msg" => "Please select a distributor"));
        return;
    }

    // Get distributor info
    $DB->vals = array($distributorID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM " . $DB->pre . "distributor WHERE distributorID=? AND status=?";
    $distributor = $DB->dbRow();

    if (!$distributor) {
        setResponse(array("err" => 1, "msg" => "Distributor not found"));
        return;
    }

    // Parse items
    $items = json_decode($_POST["items"] ?? "[]", true);
    if (empty($items)) {
        setResponse(array("err" => 1, "msg" => "Please add at least one item"));
        return;
    }

    // Generate invoice number
    $invoiceNo = generateInvoiceNo();
    $invoiceDate = $_POST["invoiceDate"] ?? date("Y-m-d");
    $creditDays = intval($distributor["creditDays"] ?? 30);
    $dueDate = date("Y-m-d", strtotime($invoiceDate . " + $creditDays days"));

    // Calculate totals
    $subtotal = floatval($_POST["subtotal"] ?? 0);
    $discountAmount = floatval($_POST["discountAmount"] ?? 0);
    $taxableAmount = floatval($_POST["taxableAmount"] ?? 0);
    $cgstAmount = floatval($_POST["cgstAmount"] ?? 0);
    $sgstAmount = floatval($_POST["sgstAmount"] ?? 0);
    $igstAmount = floatval($_POST["igstAmount"] ?? 0);
    $totalAmount = floatval($_POST["totalAmount"] ?? 0);

    // Create invoice
    $DB->table = $DB->pre . "b2b_invoice";
    $DB->data = array(
        "invoiceNo" => $invoiceNo,
        "invoiceDate" => $invoiceDate,
        "dueDate" => $dueDate,
        "orderID" => intval($_POST["orderID"] ?? 0),
        "distributorID" => $distributorID,
        "distributorName" => $_POST["distributorName"] ?? $distributor["companyName"],
        "distributorGSTIN" => $_POST["distributorGSTIN"] ?? $distributor["gstin"],
        "distributorAddress" => $distributor["billingAddress"],
        "distributorState" => $distributor["billingState"],
        "distributorStateCode" => $distributor["billingStateCode"],
        "warehouseID" => intval($_POST["warehouseID"] ?? 0),
        "shippingAddress" => $_POST["shippingAddress"] ?? "",
        "subtotal" => $subtotal,
        "discountAmount" => $discountAmount,
        "taxableAmount" => $taxableAmount,
        "cgstAmount" => $cgstAmount,
        "sgstAmount" => $sgstAmount,
        "igstAmount" => $igstAmount,
        "totalAmount" => $totalAmount,
        "paidAmount" => 0,
        "balanceAmount" => $totalAmount,
        "paymentTerms" => $_POST["paymentTerms"] ?? "Net " . $creditDays,
        "invoiceStatus" => "Generated",
        "remarks" => $_POST["remarks"] ?? "",
        "createdBy" => $_SESSION["ADMINID"] ?? 0,
        "created" => date("Y-m-d H:i:s"),
        "status" => 1
    );

    if (!$DB->dbInsert()) {
        setResponse(array("err" => 1, "msg" => "Failed to create invoice"));
        return;
    }

    $invoiceID = $DB->insertID;

    // Insert items
    foreach ($items as $item) {
        $DB->table = $DB->pre . "b2b_invoice_item";
        $DB->data = array(
            "invoiceID" => $invoiceID,
            "productID" => intval($item["productID"]),
            "productSKU" => $item["productSKU"] ?? "",
            "productName" => $item["productName"] ?? "",
            "hsnCode" => $item["hsnCode"] ?? "",
            "quantity" => floatval($item["quantity"]),
            "uom" => $item["uom"] ?? "Pcs",
            "unitPrice" => floatval($item["unitPrice"]),
            "discountPercent" => floatval($item["discountPercent"] ?? 0),
            "discountAmount" => floatval($item["discountAmount"] ?? 0),
            "taxableAmount" => floatval($item["taxableAmount"] ?? $item["totalAmount"]),
            "gstRate" => floatval($item["gstRate"] ?? 0),
            "cgstAmount" => floatval($item["cgstAmount"] ?? 0),
            "sgstAmount" => floatval($item["sgstAmount"] ?? 0),
            "igstAmount" => floatval($item["igstAmount"] ?? 0),
            "totalAmount" => floatval($item["totalAmount"])
        );
        $DB->dbInsert();
    }

    // Update distributor outstanding
    updateDistributorOutstanding($distributorID, $totalAmount, "add");

    setResponse(array("err" => 0, "invoiceID" => $invoiceID, "invoiceNo" => $invoiceNo, "param" => "id=" . $invoiceID, "msg" => "Invoice created successfully"));
}

function updateInvoice()
{
    global $DB;

    $invoiceID = intval($_POST["invoiceID"]);
    if ($invoiceID < 1) {
        setResponse(array("err" => 1, "msg" => "Invalid invoice ID"));
        return;
    }

    // Get current invoice
    $DB->vals = array($invoiceID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM " . $DB->pre . "b2b_invoice WHERE invoiceID=? AND status=?";
    $invoice = $DB->dbRow();

    if (!$invoice) {
        setResponse(array("err" => 1, "msg" => "Invoice not found"));
        return;
    }

    // Update invoice
    $DB->table = $DB->pre . "b2b_invoice";
    $DB->data = array(
        "invoiceDate" => $_POST["invoiceDate"] ?? $invoice["invoiceDate"],
        "dueDate" => $_POST["dueDate"] ?? $invoice["dueDate"],
        "distributorName" => $_POST["distributorName"] ?? $invoice["distributorName"],
        "distributorGSTIN" => $_POST["distributorGSTIN"] ?? $invoice["distributorGSTIN"],
        "distributorAddress" => $_POST["distributorAddress"] ?? $invoice["distributorAddress"],
        "shippingAddress" => $_POST["shippingAddress"] ?? $invoice["shippingAddress"],
        "subtotal" => floatval($_POST["subtotal"] ?? $invoice["subtotal"]),
        "discountAmount" => floatval($_POST["discountAmount"] ?? $invoice["discountAmount"]),
        "taxableAmount" => floatval($_POST["taxableAmount"] ?? $invoice["taxableAmount"]),
        "cgstAmount" => floatval($_POST["cgstAmount"] ?? $invoice["cgstAmount"]),
        "sgstAmount" => floatval($_POST["sgstAmount"] ?? $invoice["sgstAmount"]),
        "igstAmount" => floatval($_POST["igstAmount"] ?? $invoice["igstAmount"]),
        "totalAmount" => floatval($_POST["totalAmount"] ?? $invoice["totalAmount"]),
        "paymentTerms" => $_POST["paymentTerms"] ?? $invoice["paymentTerms"],
        "invoiceStatus" => $_POST["invoiceStatus"] ?? $invoice["invoiceStatus"],
        "remarks" => $_POST["remarks"] ?? $invoice["remarks"],
        "modified" => date("Y-m-d H:i:s")
    );

    // Recalculate balance
    $newTotal = floatval($_POST["totalAmount"] ?? $invoice["totalAmount"]);
    $DB->data["balanceAmount"] = $newTotal - $invoice["paidAmount"];

    // Handle outstanding change if total changed
    $oldTotal = floatval($invoice["totalAmount"]);
    if ($newTotal != $oldTotal) {
        $diff = $newTotal - $oldTotal;
        updateDistributorOutstanding($invoice["distributorID"], abs($diff), $diff > 0 ? "add" : "subtract");
    }

    if ($DB->dbUpdate("invoiceID=?", "i", array($invoiceID))) {
        setResponse(array("err" => 0, "param" => "id=" . $invoiceID, "msg" => "Invoice updated successfully"));
    } else {
        setResponse(array("err" => 1, "msg" => "Failed to update invoice"));
    }
}

function cancelInvoice()
{
    global $DB;

    $invoiceID = intval($_POST["invoiceID"]);
    $reason = trim($_POST["reason"] ?? "");

    $DB->vals = array($invoiceID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM " . $DB->pre . "b2b_invoice WHERE invoiceID=? AND status=?";
    $invoice = $DB->dbRow();

    if (!$invoice) {
        setResponse(array("err" => 1, "msg" => "Invoice not found"));
        return;
    }

    if ($invoice["paidAmount"] > 0) {
        // Auto-generate credit note for invoice with payments
        $creditNoteID = autoGenerateCreditNote($invoice, "B2B", $reason);
        if ($creditNoteID) {
            // Get the credit note number
            $DB->vals = array($creditNoteID);
            $DB->types = "i";
            $DB->sql = "SELECT creditNoteNo FROM " . $DB->pre . "credit_note WHERE creditNoteID=?";
            $cnRow = $DB->dbRow();
            $creditNoteNo = $cnRow["creditNoteNo"] ?? "";

            // Mark invoice as cancelled with credit note reference
            $DB->vals = array("Cancelled", date("Y-m-d H:i:s"), $invoiceID);
            $DB->types = "ssi";
            $DB->sql = "UPDATE " . $DB->pre . "b2b_invoice SET invoiceStatus=?, modified=? WHERE invoiceID=?";
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

    // Reverse outstanding
    updateDistributorOutstanding($invoice["distributorID"], $invoice["totalAmount"], "subtract");

    // Restore stock to warehouse
    $warehouseID = intval($invoice["warehouseID"] ?? 0);
    if ($warehouseID > 0) {
        restoreInvoiceStock($invoiceID, $warehouseID, $invoice["invoiceNo"], $reason);
    }

    // Update invoice status
    $DB->vals = array("Cancelled", $reason, date("Y-m-d H:i:s"), $invoiceID);
    $DB->types = "sssi";
    $DB->sql = "UPDATE " . $DB->pre . "b2b_invoice SET invoiceStatus=?, remarks=CONCAT(COALESCE(remarks,''), '\nCancelled: ', ?) WHERE invoiceID=?";
    $DB->dbQuery();

    setResponse(array("err" => 0, "msg" => "Invoice cancelled. Stock restored to warehouse."));
}

/**
 * Restore stock to warehouse when invoice is cancelled
 */
function restoreInvoiceStock($invoiceID, $warehouseID, $invoiceNo, $reason = "")
{
    global $DB;

    // Get invoice items
    $DB->vals = array($invoiceID);
    $DB->types = "i";
    $DB->sql = "SELECT productID, productName, productSKU, quantity FROM " . $DB->pre . "b2b_invoice_item WHERE invoiceID=?";
    $items = $DB->dbRows();

    if (empty($items)) return;

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
            "referenceType" => "Invoice",
            "referenceNumber" => $invoiceNo,
            "qtyIn" => $quantity,
            "qtyOut" => 0,
            "balanceQty" => $newQty,
            "notes" => "Stock restored - Invoice cancelled. " . ($reason ? "Reason: " . $reason : ""),
            "createdBy" => $_SESSION["ADMINID"] ?? 0,
            "created" => date("Y-m-d H:i:s")
        );
        $DB->dbInsert();
    }
}

function getInvoiceDetails()
{
    global $DB;

    $invoiceID = intval($_POST["invoiceID"]);

    $DB->vals = array($invoiceID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT i.*, d.companyName, d.distributorCode, d.gstin, w.warehouseName
                FROM " . $DB->pre . "b2b_invoice i
                LEFT JOIN " . $DB->pre . "distributor d ON i.distributorID = d.distributorID
                LEFT JOIN " . $DB->pre . "warehouse w ON i.warehouseID = w.warehouseID
                WHERE i.invoiceID=? AND i.status=?";
    $invoice = $DB->dbRow();

    if (!$invoice) {
        setResponse(array("err" => 1, "msg" => "Invoice not found"));
        return;
    }

    // Get items
    $DB->vals = array($invoiceID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "b2b_invoice_item WHERE invoiceID=?";
    $items = $DB->dbRows();

    // Get payments
    $DB->vals = array($invoiceID);
    $DB->types = "i";
    $DB->sql = "SELECT pa.*, p.paymentNo, p.paymentDate, p.paymentMode
                FROM " . $DB->pre . "b2b_payment_allocation pa
                LEFT JOIN " . $DB->pre . "b2b_payment p ON pa.paymentID = p.paymentID
                WHERE pa.invoiceID=?";
    $payments = $DB->dbRows();

    setResponse(array("err" => 0, "invoice" => $invoice, "items" => $items, "payments" => $payments));
}

/**
 * Auto-generate credit note when cancelling invoice with payments
 */
function autoGenerateCreditNote($invoice, $invoiceType, $reason = "")
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

    // Determine entity details based on invoice type
    $entityType = "Distributor";
    $entityID = $invoice["distributorID"] ?? 0;
    $entityName = $invoice["distributorName"] ?? "";
    $entityGSTIN = $invoice["distributorGSTIN"] ?? "";
    $warehouseID = $invoice["warehouseID"] ?? 0;

    if ($invoiceType == "PNP") {
        $entityType = "Location";
        $entityID = $invoice["locationID"] ?? 0;
        $entityName = $invoice["customerName"] ?? "";
        $entityGSTIN = $invoice["customerGSTIN"] ?? "";
        // Get warehouse from location
        if ($entityID > 0) {
            $DB->vals = array($entityID);
            $DB->types = "i";
            $DB->sql = "SELECT warehouseID FROM " . $DB->pre . "pnp_location WHERE locationID=?";
            $loc = $DB->dbRow();
            $warehouseID = $loc["warehouseID"] ?? 0;
        }
    }

    // Create credit note
    $DB->table = $DB->pre . "credit_note";
    $DB->data = array(
        "creditNoteNo" => $creditNoteNo,
        "creditNoteDate" => date("Y-m-d"),
        "entityType" => $entityType,
        "entityID" => $entityID,
        "entityName" => $entityName,
        "entityGSTIN" => $entityGSTIN,
        "invoiceType" => $invoiceType,
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
        "notes" => "Auto-generated on invoice cancellation",
        "createdBy" => $_SESSION[SITEURL]["userID"] ?? 0,
        "status" => 1
    );

    if (!$DB->dbInsert()) {
        return false;
    }

    $creditNoteID = $DB->insertID;

    // Get and copy invoice items
    if ($invoiceType == "B2B") {
        $DB->vals = array($invoice["invoiceID"]);
        $DB->types = "i";
        $DB->sql = "SELECT * FROM " . $DB->pre . "b2b_invoice_item WHERE invoiceID=?";
    } else {
        // PNP - get from retail sale items
        $DB->vals = array($invoice["saleID"] ?? 0, 1);
        $DB->types = "ii";
        $DB->sql = "SELECT * FROM " . $DB->pre . "pnp_retail_sale_item WHERE saleID=? AND status=?";
    }
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

    // Restore stock if warehouse is set
    if ($warehouseID > 0) {
        restoreCreditNoteStockAuto($creditNoteID, $warehouseID, $creditNoteNo);
    }

    // Update entity outstanding (reduce by credit note amount)
    if ($entityType == "Distributor" && $entityID > 0) {
        updateDistributorOutstanding($entityID, $invoice["totalAmount"], "subtract");
    }

    return $creditNoteID;
}

/**
 * Restore stock for auto-generated credit note
 */
function restoreCreditNoteStockAuto($creditNoteID, $warehouseID, $creditNoteNo)
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
            "notes" => "Stock restored via Credit Note (Invoice Cancellation)",
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

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest(true, true); // Session-based auth
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "CREATE_FROM_ORDER": createFromOrder(); break;
            case "ADD": addInvoice(); break;
            case "UPDATE": updateInvoice(); break;
            case "CANCEL": cancelInvoice(); break;
            case "GET_DETAILS": getInvoiceDetails(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "b2b_invoice", "PK" => "invoiceID"));
}
?>
