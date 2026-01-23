<?php
$id = 0;
$D = array();
$allocations = array();

if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT p.*, d.companyName as distributorName, d.distributorCode
                FROM `" . $DB->pre . $MXMOD["TBL"] . "` p
                LEFT JOIN " . $DB->pre . "distributor d ON p.distributorID = d.distributorID
                WHERE p.status=? AND p.`" . $MXMOD["PK"] . "`=?";
    $D = $DB->dbRow();

    // Get allocations
    $DB->vals = array($id);
    $DB->types = "i";
    $DB->sql = "SELECT pa.*, i.invoiceNo, i.invoiceDate, i.totalAmount
                FROM " . $DB->pre . "b2b_payment_allocation pa
                LEFT JOIN " . $DB->pre . "b2b_invoice i ON pa.invoiceID = i.invoiceID
                WHERE pa.paymentID=?";
    $allocations = $DB->dbRows();
}

// Pre-select distributor if provided
$preSelectDistributor = isset($_GET["distributorID"]) ? intval($_GET["distributorID"]) : ($D["distributorID"] ?? 0);
$preSelectInvoice = isset($_GET["invoiceID"]) ? intval($_GET["invoiceID"]) : 0;

// Get invoice details if pre-selected
if ($preSelectInvoice > 0 && empty($D)) {
    $DB->vals = array($preSelectInvoice, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT i.*, d.distributorID FROM " . $DB->pre . "b2b_invoice i
                LEFT JOIN " . $DB->pre . "distributor d ON i.distributorID = d.distributorID
                WHERE i.invoiceID=? AND i.status=?";
    $invoiceDetails = $DB->dbRow();
    if ($invoiceDetails) {
        $preSelectDistributor = $invoiceDetails["distributorID"];
    }
}

$isView = !empty($D); // Payments are view-only after creation

// Get distributors
$DB->vals = array(1, 1);
$DB->types = "ii";
$DB->sql = "SELECT distributorID, companyName, distributorCode, currentOutstanding FROM " . $DB->pre . "distributor WHERE status=? AND isActive=? ORDER BY companyName";
$distributors = $DB->dbRows();

// Build distributor dropdown
$distOpt = "";
foreach ($distributors as $dist) {
    $sel = ($preSelectDistributor == $dist["distributorID"]) ? ' selected="selected"' : '';
    $dataAttrs = ' data-outstanding="' . $dist["currentOutstanding"] . '"';
    $distOpt .= '<option value="' . $dist["distributorID"] . '"' . $sel . $dataAttrs . '>' . $dist["distributorCode"] . ' - ' . $dist["companyName"] . ' (Outstanding: Rs. ' . number_format($dist["currentOutstanding"], 0) . ')</option>';
}

// Payment mode options
$paymentModes = array("NEFT", "RTGS", "IMPS", "UPI", "Cheque", "Cash");
$modeOpt = "";
$currentMode = $D["paymentMode"] ?? "NEFT";
foreach ($paymentModes as $pm) {
    $sel = ($currentMode == $pm) ? ' selected="selected"' : '';
    $modeOpt .= '<option value="' . $pm . '"' . $sel . '>' . $pm . '</option>';
}

$arrForm = array(
    array("type" => "select", "name" => "distributorID", "value" => $distOpt, "title" => "Distributor", "validate" => "required", "params" => array("id" => "distributorID", "onchange" => "loadUnpaidInvoices()")),
    array("type" => "date", "name" => "paymentDate", "value" => $D["paymentDate"] ?? date("Y-m-d"), "title" => "Payment Date", "validate" => "required"),
    array("type" => "select", "name" => "paymentMode", "value" => $modeOpt, "title" => "Payment Mode", "params" => array("id" => "paymentMode", "onchange" => "togglePaymentFields()")),
    array("type" => "text", "name" => "amount", "value" => $D["amount"] ?? "", "title" => "Payment Amount", "validate" => "required,number", "params" => array("id" => "amount", "onchange" => "updateAllocations()")),
    array("type" => "text", "name" => "transactionRef", "value" => $D["transactionRef"] ?? "", "title" => "Transaction Reference", "info" => '<span class="info">UTR/Transaction ID</span>'),
    array("type" => "text", "name" => "bankName", "value" => $D["bankName"] ?? "", "title" => "Bank Name", "params" => array("id" => "bankName")),
    array("type" => "text", "name" => "chequeNo", "value" => $D["chequeNo"] ?? "", "title" => "Cheque No", "params" => array("id" => "chequeNo")),
    array("type" => "date", "name" => "chequeDate", "value" => $D["chequeDate"] ?? "", "title" => "Cheque Date", "params" => array("id" => "chequeDate")),
    array("type" => "textarea", "name" => "remarks", "value" => $D["remarks"] ?? "", "title" => "Remarks", "params" => array("rows" => 2)),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <?php if ($isView): ?>
    <!-- View Mode -->
    <div style="padding: 15px; margin: 0 15px 15px; background: #f8f9fa; border-radius: 8px;">
        <strong style="font-size: 18px;"><?php echo $D["paymentNo"]; ?></strong>
        <span style="margin-left: 10px; padding: 3px 10px; border-radius: 3px; background: <?php echo $D["paymentStatus"] == "Cancelled" ? "#dc3545" : ($D["paymentStatus"] == "Cleared" ? "#198754" : "#ffc107"); ?>; color: <?php echo $D["paymentStatus"] == "Pending" ? "#000" : "#fff"; ?>; font-size: 12px;"><?php echo $D["paymentStatus"]; ?></span>
        <button type="button" onclick="printReceipt()" style="float: right; background: #6c757d; color: #fff; border: none; padding: 5px 15px; border-radius: 4px; cursor: pointer;"><i class="fa fa-print"></i> Print Receipt</button>
    </div>

    <form class="wrap-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Payment Details</h2>
            <table class="tbl-list" style="width: 100%;">
                <tr><td style="padding: 10px; width: 40%;">Payment No</td><td style="padding: 10px;"><strong><?php echo $D["paymentNo"]; ?></strong></td></tr>
                <tr><td style="padding: 10px;">Payment Date</td><td style="padding: 10px;"><?php echo date("d-M-Y", strtotime($D["paymentDate"])); ?></td></tr>
                <tr><td style="padding: 10px;">Distributor</td><td style="padding: 10px;"><?php echo $D["distributorName"]; ?> (<?php echo $D["distributorCode"]; ?>)</td></tr>
                <tr><td style="padding: 10px;">Payment Mode</td><td style="padding: 10px;"><?php echo $D["paymentMode"]; ?></td></tr>
                <?php if (!empty($D["transactionRef"])): ?>
                <tr><td style="padding: 10px;">Transaction Ref</td><td style="padding: 10px;"><?php echo $D["transactionRef"]; ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($D["chequeNo"])): ?>
                <tr><td style="padding: 10px;">Cheque No</td><td style="padding: 10px;"><?php echo $D["chequeNo"]; ?> (<?php echo date("d-M-Y", strtotime($D["chequeDate"])); ?>)</td></tr>
                <?php endif; ?>
                <tr><td style="padding: 10px;">Payment Amount</td><td style="padding: 10px;"><strong style="font-size: 18px; color: #198754;">Rs. <?php echo number_format($D["amount"], 2); ?></strong></td></tr>
                <tr><td style="padding: 10px;">Allocated</td><td style="padding: 10px;">Rs. <?php echo number_format($D["allocatedAmount"], 2); ?></td></tr>
                <tr><td style="padding: 10px;">Unallocated</td><td style="padding: 10px; color: <?php echo $D["unallocatedAmount"] > 0 ? '#ffc107' : '#198754'; ?>;">Rs. <?php echo number_format($D["unallocatedAmount"], 2); ?></td></tr>
            </table>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Invoice Allocations</h2>
            <?php if (count($allocations) > 0): ?>
            <table class="tbl-list" style="width: 100%;">
                <thead>
                    <tr style="background: #e9ecef;">
                        <th style="padding: 10px;">Invoice</th>
                        <th style="padding: 10px;">Date</th>
                        <th style="padding: 10px; text-align: right;">Invoice Amt</th>
                        <th style="padding: 10px; text-align: right;">Allocated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allocations as $alloc): ?>
                    <tr>
                        <td style="padding: 8px;"><a href="<?php echo ADMINURL; ?>/b2b-invoice-add/?id=<?php echo $alloc["invoiceID"]; ?>"><?php echo $alloc["invoiceNo"]; ?></a></td>
                        <td style="padding: 8px;"><?php echo date("d-M-Y", strtotime($alloc["invoiceDate"])); ?></td>
                        <td style="padding: 8px; text-align: right;">Rs. <?php echo number_format($alloc["totalAmount"], 2); ?></td>
                        <td style="padding: 8px; text-align: right; color: #198754;">Rs. <?php echo number_format($alloc["allocatedAmount"], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p style="padding: 20px; text-align: center; color: #999;">No invoice allocations</p>
            <?php endif; ?>

            <?php if (!empty($D["remarks"])): ?>
            <h2 class="form-head" style="margin-top: 20px;">Remarks</h2>
            <div style="padding: 15px; background: #f8f9fa; border-radius: 4px;">
                <?php echo nl2br(htmlspecialchars($D["remarks"])); ?>
            </div>
            <?php endif; ?>
        </div>
        <div style="clear: both; padding: 15px;">
            <a href="<?php echo ADMINURL; ?>/b2b-payment-list/" class="btn btn-default">Back to List</a>
        </div>
    </form>
    <?php else: ?>
    <!-- Add Mode -->
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form">
            <h2 class="form-head">Payment Details</h2>
            <ul class="tbl-form"><?php echo $MXFRM->getForm($arrForm); ?></ul>

            <!-- Invoice Allocation Section -->
            <div style="margin: 20px 15px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <h3 style="margin-bottom: 15px; border-bottom: 2px solid #198754; padding-bottom: 10px;">Allocate to Invoices</h3>
                <div id="invoiceList">
                    <p style="text-align: center; color: #999;">Select a distributor to see unpaid invoices</p>
                </div>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                    <table width="100%">
                        <tr>
                            <td><strong>Total Allocated:</strong></td>
                            <td style="text-align: right;"><strong id="totalAllocated">Rs. 0.00</strong></td>
                        </tr>
                        <tr>
                            <td><strong>Unallocated Amount:</strong></td>
                            <td style="text-align: right;"><strong id="unallocatedAmount" style="color: #ffc107;">Rs. 0.00</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
    <?php endif; ?>
</div>

<script src="<?php echo ADMINURL; ?>/mod/b2b-payment/inc/js/x-b2b-payment.inc.js"></script>
<script>
var paymentID = <?php echo $id; ?>;
var preSelectInvoice = <?php echo $preSelectInvoice; ?>;

$(document).ready(function() {
    <?php if (!$isView && $preSelectDistributor > 0): ?>
    loadUnpaidInvoices();
    <?php endif; ?>
    togglePaymentFields();
});

function togglePaymentFields() {
    var mode = document.getElementById('paymentMode').value;
    var chequeNo = document.getElementById('chequeNo');
    var chequeDate = document.getElementById('chequeDate');
    var bankName = document.getElementById('bankName');

    // Get parent li elements
    var chequeNoLi = chequeNo ? chequeNo.closest('li') : null;
    var chequeDateLi = chequeDate ? chequeDate.closest('li') : null;
    var bankNameLi = bankName ? bankName.closest('li') : null;

    if (chequeNoLi) chequeNoLi.style.display = mode == 'Cheque' ? '' : 'none';
    if (chequeDateLi) chequeDateLi.style.display = mode == 'Cheque' ? '' : 'none';
    if (bankNameLi) bankNameLi.style.display = mode != 'Cash' ? '' : 'none';
}

function printReceipt() {
    window.open('<?php echo ADMINURL; ?>/b2b-payment-print/?id=<?php echo $id; ?>', '_blank');
}
</script>

<script>
// Define required JavaScript variables for form submission
var MODINCURL = '<?php echo ADMINURL; ?>/mod/b2b-payment/x-b2b-payment.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/b2b-payment/';
var ADMINURL = '<?php echo ADMINURL; ?>';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
</script>
