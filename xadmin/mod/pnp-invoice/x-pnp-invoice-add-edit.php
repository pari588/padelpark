<?php
$id = 0;
$D = array();
$saleItems = array();

if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT i.*, l.locationName
                FROM `" . $DB->pre . $MXMOD["TBL"] . "` i
                LEFT JOIN " . $DB->pre . "pnp_location l ON i.locationID = l.locationID
                WHERE i.status=? AND i.`" . $MXMOD["PK"] . "`=?";
    $D = $DB->dbRow();

    // Get sale items if linked
    if (!empty($D['saleID'])) {
        $DB->vals = array($D['saleID']);
        $DB->types = "i";
        $DB->sql = "SELECT * FROM " . $DB->pre . "pnp_retail_sale_item WHERE saleID = ? AND status = 1";
        $saleItems = $DB->dbRows();
    }
}

$isEdit = !empty($D);
$MXFRM = new mxForm();

// Get locations for dropdown
$whrArr = array("sql" => "status=?", "types" => "i", "vals" => array(1));
$locationOpt = getTableDD([
    "table" => $DB->pre . "pnp_location",
    "key" => "locationID",
    "val" => "locationName",
    "selected" => ($D['locationID'] ?? 0),
    "where" => $whrArr
]);

// Build select options
$invTypes = array("Booking", "Rental", "Combined", "Other");
$invTypeOpt = "";
foreach ($invTypes as $it) {
    $sel = (($D["invoiceType"] ?? "Booking") == $it) ? ' selected="selected"' : '';
    $invTypeOpt .= '<option value="' . $it . '"' . $sel . '>' . $it . '</option>';
}

$payMethods = array("Cash", "Card", "UPI", "Hudle", "Wallet", "Bank Transfer");
$payMethodOpt = "";
foreach ($payMethods as $pm) {
    $sel = (($D["paymentMethod"] ?? "Cash") == $pm) ? ' selected="selected"' : '';
    $payMethodOpt .= '<option value="' . $pm . '"' . $sel . '>' . $pm . '</option>';
}

$payStatuses = array("Pending", "Paid", "Partial", "Refunded");
$payStatusOpt = "";
foreach ($payStatuses as $ps) {
    $sel = (($D["paymentStatus"] ?? "Paid") == $ps) ? ' selected="selected"' : '';
    $payStatusOpt .= '<option value="' . $ps . '"' . $sel . '>' . $ps . '</option>';
}

$invStatuses = array("Draft", "Generated", "Sent", "Cancelled");
$invStatusOpt = "";
foreach ($invStatuses as $is) {
    $sel = (($D["invoiceStatus"] ?? "Generated") == $is) ? ' selected="selected"' : '';
    $invStatusOpt .= '<option value="' . $is . '"' . $sel . '>' . $is . '</option>';
}
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <?php if ($isEdit): ?>
        <!-- Edit Mode Header -->
        <h2 class="form-head"><?php echo $D["invoiceNo"]; ?> - <span class="badge badge-<?php echo $D["paymentStatus"] == "Paid" ? "success" : ($D["paymentStatus"] == "Pending" ? "warning" : "info"); ?>"><?php echo $D["paymentStatus"]; ?></span></h2>

        <p>
            <a href="<?php echo ADMINURL; ?>/mod/pnp-invoice/x-pnp-invoice-print.php?id=<?php echo $id; ?>" target="_blank" class="btn">Print</a>
            <a href="<?php echo ADMINURL; ?>/mod/pnp-invoice/x-pnp-invoice-print.php?id=<?php echo $id; ?>&download=1" target="_blank" class="btn">Download PDF</a>
            <?php if ($D["invoiceStatus"] != "Cancelled"): ?>
            <button type="button" onclick="cancelInvoice()" class="btn" style="background:#dc3545; color:#fff;">Cancel Invoice</button>
            <?php endif; ?>
        </p>
        <?php endif; ?>

        <!-- Editable Form -->
        <form name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="50%" valign="top">
                        <h2 class="form-head">Invoice Details</h2>
                        <ul class="tbl-form">
                            <?php
                            $arrForm = array(
                                array("type" => "text", "name" => "invoiceNo", "value" => $D["invoiceNo"] ?? "", "title" => "Invoice No", "info" => '<span class="info">Leave blank for auto-generation</span>'),
                                array("type" => "date", "name" => "invoiceDate", "value" => $D["invoiceDate"] ?? date("Y-m-d"), "title" => "Invoice Date", "validate" => "required"),
                                array("type" => "select", "name" => "locationID", "value" => $locationOpt, "title" => "Location", "validate" => "required"),
                                array("type" => "select", "name" => "invoiceType", "value" => $invTypeOpt, "title" => "Invoice Type"),
                            );
                            echo $MXFRM->getForm($arrForm);
                            ?>
                        </ul>

                        <h2 class="form-head">Customer Details</h2>
                        <ul class="tbl-form">
                            <?php
                            $arrForm2 = array(
                                array("type" => "text", "name" => "customerName", "value" => $D["customerName"] ?? "", "title" => "Customer Name", "validate" => "required"),
                                array("type" => "text", "name" => "customerPhone", "value" => $D["customerPhone"] ?? "", "title" => "Phone"),
                                array("type" => "text", "name" => "customerEmail", "value" => $D["customerEmail"] ?? "", "title" => "Email"),
                                array("type" => "text", "name" => "customerGSTIN", "value" => $D["customerGSTIN"] ?? "", "title" => "Customer GSTIN"),
                            );
                            echo $MXFRM->getForm($arrForm2);
                            ?>
                        </ul>
                    </td>
                    <td width="50%" valign="top">
                        <h2 class="form-head">Amount Details</h2>
                        <ul class="tbl-form">
                            <?php
                            $arrForm3 = array(
                                array("type" => "text", "name" => "subtotal", "value" => $D["subtotal"] ?? "0", "title" => "Subtotal (Rs.)", "validate" => "required,number"),
                                array("type" => "text", "name" => "discountAmount", "value" => $D["discountAmount"] ?? "0", "title" => "Discount (Rs.)", "validate" => "number"),
                                array("type" => "text", "name" => "cgstRate", "value" => $D["cgstRate"] ?? "9", "title" => "CGST Rate (%)", "validate" => "number"),
                                array("type" => "text", "name" => "sgstRate", "value" => $D["sgstRate"] ?? "9", "title" => "SGST Rate (%)", "validate" => "number"),
                                array("type" => "text", "name" => "totalAmount", "value" => $D["totalAmount"] ?? "0", "title" => "Total Amount (Rs.)", "validate" => "required,number"),
                            );
                            echo $MXFRM->getForm($arrForm3);
                            ?>
                        </ul>

                        <h2 class="form-head">Payment Details</h2>
                        <ul class="tbl-form">
                            <?php
                            $arrForm4 = array(
                                array("type" => "select", "name" => "paymentMethod", "value" => $payMethodOpt, "title" => "Payment Method"),
                                array("type" => "text", "name" => "paymentReference", "value" => $D["paymentReference"] ?? "", "title" => "Payment Reference"),
                                array("type" => "select", "name" => "paymentStatus", "value" => $payStatusOpt, "title" => "Payment Status"),
                                array("type" => "text", "name" => "paidAmount", "value" => $D["paidAmount"] ?? "0", "title" => "Paid Amount (Rs.)", "validate" => "number"),
                                array("type" => "select", "name" => "invoiceStatus", "value" => $invStatusOpt, "title" => "Invoice Status"),
                                array("type" => "textarea", "name" => "notes", "value" => $D["notes"] ?? "", "title" => "Notes", "params" => array("rows" => 2)),
                            );
                            echo $MXFRM->getForm($arrForm4);
                            ?>
                        </ul>
                    </td>
                </tr>
            </table>

            <?php if (count($saleItems) > 0): ?>
            <h2 class="form-head">Sale Items (Read Only)</h2>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead>
                    <tr><th>#</th><th>Product</th><th>SKU</th><th align="center">Qty</th><th align="right">Rate</th><th align="right">Tax</th><th align="right">Amount</th></tr>
                </thead>
                <tbody>
                    <?php $sn = 0; foreach ($saleItems as $item): $sn++; ?>
                    <tr>
                        <td><?php echo $sn; ?></td>
                        <td><?php echo $item["productName"]; ?></td>
                        <td><?php echo $item["productSKU"]; ?></td>
                        <td align="center"><?php echo number_format($item["quantity"], 0); ?></td>
                        <td align="right">Rs. <?php echo number_format($item["unitPrice"], 2); ?></td>
                        <td align="right">Rs. <?php echo number_format($item["taxAmount"], 2); ?></td>
                        <td align="right"><strong>Rs. <?php echo number_format($item["totalAmount"], 2); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <?php echo $MXFRM->closeForm(); ?>
        </form>
    </div>
</div>

<script>
var invoiceID = <?php echo $id; ?>;

function cancelInvoice() {
    var reason = prompt('Please enter reason for cancellation:');
    if (!reason) return;

    $.mxajax({
        url: '<?php echo ADMINURL; ?>/mod/pnp-invoice/x-pnp-invoice.inc.php',
        data: {
            xAction: 'CANCEL',
            invoiceID: invoiceID,
            reason: reason
        }
    }).then(function(res) {
        if (res.err == 0) {
            alert(res.msg || 'Invoice cancelled successfully.');
            location.reload();
        } else {
            alert(res.msg || 'Error cancelling invoice');
        }
    });
}

// Auto-calculate total
function calculateTotal() {
    var subtotal = parseFloat(document.querySelector('[name="subtotal"]')?.value) || 0;
    var discount = parseFloat(document.querySelector('[name="discountAmount"]')?.value) || 0;
    var cgstRate = parseFloat(document.querySelector('[name="cgstRate"]')?.value) || 0;
    var sgstRate = parseFloat(document.querySelector('[name="sgstRate"]')?.value) || 0;

    var taxable = subtotal - discount;
    var cgst = Math.round(taxable * cgstRate / 100 * 100) / 100;
    var sgst = Math.round(taxable * sgstRate / 100 * 100) / 100;
    var total = taxable + cgst + sgst;

    if (document.querySelector('[name="totalAmount"]')) {
        document.querySelector('[name="totalAmount"]').value = total.toFixed(2);
    }
}

document.querySelector('[name="subtotal"]')?.addEventListener('input', calculateTotal);
document.querySelector('[name="discountAmount"]')?.addEventListener('input', calculateTotal);
document.querySelector('[name="cgstRate"]')?.addEventListener('input', calculateTotal);
document.querySelector('[name="sgstRate"]')?.addEventListener('input', calculateTotal);
</script>
