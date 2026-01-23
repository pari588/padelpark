<?php
/*
B2B Payment Module
- Record payments from distributors
- Allocate payments to invoices
- Update invoice status
- Update distributor ledger
*/

function generatePaymentNo()
{
    global $DB;
    $prefix = "PAY-" . date("Ymd") . "-";
    $DB->sql = "SELECT MAX(paymentNo) as lastNo FROM " . $DB->pre . "b2b_payment WHERE paymentNo LIKE '$prefix%'";
    $result = $DB->dbRow();
    $lastNo = $result["lastNo"] ?? "";

    if ($lastNo) {
        $seq = intval(substr($lastNo, -4)) + 1;
    } else {
        $seq = 1;
    }
    return $prefix . str_pad($seq, 4, "0", STR_PAD_LEFT);
}

function addPayment()
{
    global $DB;

    $distributorID = intval($_POST["distributorID"]);
    if ($distributorID < 1) {
        setResponse(array("err" => 1, "msg" => "Please select a distributor"));
        return;
    }

    $amount = floatval($_POST["amount"]);
    if ($amount <= 0) {
        setResponse(array("err" => 1, "msg" => "Payment amount must be greater than zero"));
        return;
    }

    // Parse allocations
    $allocations = json_decode($_POST["allocations"] ?? "[]", true);

    // Validate allocations total
    $allocatedTotal = 0;
    foreach ($allocations as $alloc) {
        $allocatedTotal += floatval($alloc["amount"]);
    }

    if ($allocatedTotal > $amount) {
        setResponse(array("err" => 1, "msg" => "Allocated amount exceeds payment amount"));
        return;
    }

    // Generate payment number
    $paymentNo = generatePaymentNo();

    // Create payment record
    $DB->table = $DB->pre . "b2b_payment";
    $DB->data = array(
        "paymentNo" => $paymentNo,
        "paymentDate" => $_POST["paymentDate"] ?? date("Y-m-d"),
        "distributorID" => $distributorID,
        "paymentMode" => $_POST["paymentMode"] ?? "NEFT",
        "amount" => $amount,
        "allocatedAmount" => $allocatedTotal,
        "unallocatedAmount" => $amount - $allocatedTotal,
        "transactionRef" => $_POST["transactionRef"] ?? "",
        "bankName" => $_POST["bankName"] ?? "",
        "chequeNo" => $_POST["chequeNo"] ?? "",
        "chequeDate" => $_POST["chequeDate"] ?: null,
        "remarks" => $_POST["remarks"] ?? "",
        "paymentStatus" => "Cleared",
        "createdBy" => $_SESSION["ADMINID"] ?? 0,
        "created" => date("Y-m-d H:i:s"),
        "status" => 1
    );

    if (!$DB->dbInsert()) {
        setResponse(array("err" => 1, "msg" => "Failed to create payment"));
        return;
    }

    $paymentID = $DB->insertID;

    // Create allocations
    foreach ($allocations as $alloc) {
        if (floatval($alloc["amount"]) <= 0) continue;

        $DB->table = $DB->pre . "b2b_payment_allocation";
        $DB->data = array(
            "paymentID" => $paymentID,
            "invoiceID" => intval($alloc["invoiceID"]),
            "allocatedAmount" => floatval($alloc["amount"]),
            "created" => date("Y-m-d H:i:s")
        );
        $DB->dbInsert();

        // Update invoice payment status
        updateInvoicePaymentStatus(intval($alloc["invoiceID"]));
    }

    // Update distributor outstanding
    updateDistributorOutstanding($distributorID, $amount, "subtract");

    setResponse(array("err" => 0, "paymentID" => $paymentID, "paymentNo" => $paymentNo, "param" => "id=" . $paymentID, "msg" => "Payment recorded successfully"));
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

    if ($operation == "subtract") {
        $newOutstanding = $currentOutstanding - $amount;
    } else {
        $newOutstanding = $currentOutstanding + $amount;
    }

    if ($newOutstanding < 0) $newOutstanding = 0;

    // Update distributor outstanding
    $DB->vals = array($newOutstanding, date("Y-m-d H:i:s"), $distributorID);
    $DB->types = "dsi";
    $DB->sql = "UPDATE " . $DB->pre . "distributor SET currentOutstanding=?, modified=? WHERE distributorID=?";
    $DB->dbQuery();

    return $newOutstanding;
}

function updateInvoicePaymentStatus($invoiceID)
{
    global $DB;

    $DB->vals = array($invoiceID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM " . $DB->pre . "b2b_invoice WHERE invoiceID=? AND status=?";
    $invoice = $DB->dbRow();

    if (!$invoice) return;

    // Calculate total paid
    $DB->vals = array($invoiceID);
    $DB->types = "i";
    $DB->sql = "SELECT SUM(allocatedAmount) as totalPaid FROM " . $DB->pre . "b2b_payment_allocation WHERE invoiceID=?";
    $paid = $DB->dbRow();
    $paidAmount = floatval($paid["totalPaid"] ?? 0);

    $balanceAmount = $invoice["totalAmount"] - $paidAmount;
    $status = "Generated";

    if ($paidAmount >= $invoice["totalAmount"]) {
        $status = "Paid";
        $balanceAmount = 0;
    } else if ($paidAmount > 0) {
        $status = "Partially Paid";
    }

    // Check if overdue
    if ($status != "Paid" && strtotime($invoice["dueDate"]) < strtotime(date("Y-m-d"))) {
        $status = "Overdue";
    }

    // Update invoice
    $DB->vals = array($paidAmount, $balanceAmount, $status, $invoiceID);
    $DB->types = "ddsi";
    $DB->sql = "UPDATE " . $DB->pre . "b2b_invoice SET paidAmount=?, balanceAmount=?, invoiceStatus=? WHERE invoiceID=?";
    $DB->dbQuery();
}

function getUnpaidInvoices()
{
    global $DB;

    $distributorID = intval($_POST["distributorID"]);
    if ($distributorID < 1) {
        setResponse(array("err" => 1, "msg" => "Invalid distributor ID"));
        return;
    }

    $DB->vals = array($distributorID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT invoiceID, invoiceNo, invoiceDate, dueDate, totalAmount, paidAmount, balanceAmount, invoiceStatus
                FROM " . $DB->pre . "b2b_invoice
                WHERE distributorID=? AND status=? AND invoiceStatus NOT IN ('Paid', 'Cancelled')
                ORDER BY dueDate ASC";
    $invoices = $DB->dbRows();

    setResponse(array("err" => 0, "data" => $invoices));
}

function cancelPayment()
{
    global $DB;

    $paymentID = intval($_POST["paymentID"]);
    $reason = trim($_POST["reason"] ?? "");

    $DB->vals = array($paymentID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM " . $DB->pre . "b2b_payment WHERE paymentID=? AND status=?";
    $payment = $DB->dbRow();

    if (!$payment) {
        setResponse(array("err" => 1, "msg" => "Payment not found"));
        return;
    }

    // Get affected invoices before deleting allocations
    $DB->vals = array($paymentID);
    $DB->types = "i";
    $DB->sql = "SELECT invoiceID FROM " . $DB->pre . "b2b_payment_allocation WHERE paymentID=?";
    $affectedInvoices = $DB->dbRows();

    // Delete allocations
    $DB->vals = array($paymentID);
    $DB->types = "i";
    $DB->sql = "DELETE FROM " . $DB->pre . "b2b_payment_allocation WHERE paymentID=?";
    $DB->dbQuery();

    // Update affected invoices
    foreach ($affectedInvoices as $inv) {
        updateInvoicePaymentStatus($inv["invoiceID"]);
    }

    // Reverse outstanding (add back)
    updateDistributorOutstanding($payment["distributorID"], $payment["amount"], "add");

    // Update payment status
    $DB->vals = array("Cancelled", $reason, date("Y-m-d H:i:s"), $paymentID);
    $DB->types = "sssi";
    $DB->sql = "UPDATE " . $DB->pre . "b2b_payment SET paymentStatus=?, remarks=CONCAT(COALESCE(remarks,''), '\nCancelled: ', ?) WHERE paymentID=?";
    $DB->dbQuery();

    setResponse(array("err" => 0, "msg" => "Payment cancelled"));
}

function getPaymentDetails()
{
    global $DB;

    $paymentID = intval($_POST["paymentID"]);

    $DB->vals = array($paymentID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT p.*, d.companyName as distributorName, d.distributorCode
                FROM " . $DB->pre . "b2b_payment p
                LEFT JOIN " . $DB->pre . "distributor d ON p.distributorID = d.distributorID
                WHERE p.paymentID=? AND p.status=?";
    $payment = $DB->dbRow();

    if (!$payment) {
        setResponse(array("err" => 1, "msg" => "Payment not found"));
        return;
    }

    // Get allocations
    $DB->vals = array($paymentID);
    $DB->types = "i";
    $DB->sql = "SELECT pa.*, i.invoiceNo, i.invoiceDate, i.totalAmount, i.balanceAmount
                FROM " . $DB->pre . "b2b_payment_allocation pa
                LEFT JOIN " . $DB->pre . "b2b_invoice i ON pa.invoiceID = i.invoiceID
                WHERE pa.paymentID=?";
    $allocations = $DB->dbRows();

    setResponse(array("err" => 0, "payment" => $payment, "allocations" => $allocations));
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addPayment(); break;
            case "GET_UNPAID": getUnpaidInvoices(); break;
            case "CANCEL": cancelPayment(); break;
            case "GET_DETAILS": getPaymentDetails(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "b2b_payment", "PK" => "paymentID"));
}
?>
