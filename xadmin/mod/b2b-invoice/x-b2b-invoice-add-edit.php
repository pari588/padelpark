<?php
$id = 0;
$D = array();
$items = array();
$payments = array();

if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT i.*, d.companyName, d.distributorCode, d.gstin, w.warehouseName
                FROM `" . $DB->pre . $MXMOD["TBL"] . "` i
                LEFT JOIN " . $DB->pre . "distributor d ON i.distributorID = d.distributorID
                LEFT JOIN " . $DB->pre . "warehouse w ON i.warehouseID = w.warehouseID
                WHERE i.status=? AND i.`" . $MXMOD["PK"] . "`=?";
    $D = $DB->dbRow();

    // Get items
    $DB->vals = array($id);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "b2b_invoice_item WHERE invoiceID=?";
    $items = $DB->dbRows();

    // Get payments
    $DB->vals = array($id);
    $DB->types = "i";
    $DB->sql = "SELECT pa.*, p.paymentNo, p.paymentDate, p.paymentMode
                FROM " . $DB->pre . "b2b_payment_allocation pa
                LEFT JOIN " . $DB->pre . "b2b_payment p ON pa.paymentID = p.paymentID
                WHERE pa.invoiceID=?";
    $payments = $DB->dbRows();
}

// Check if creating from order
$orderID = isset($_GET["orderID"]) ? intval($_GET["orderID"]) : 0;
$order = null;
$orderItems = array();
if ($orderID > 0 && empty($D)) {
    $DB->vals = array($orderID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT o.*, d.companyName, d.gstin, d.creditDays, d.billingAddress as distBillAddr, d.billingState, d.billingStateCode, d.shippingAddress as distShipAddr
                FROM " . $DB->pre . "b2b_sales_order o
                LEFT JOIN " . $DB->pre . "distributor d ON o.distributorID = d.distributorID
                WHERE o.orderID=? AND o.status=?";
    $order = $DB->dbRow();

    if ($order) {
        $DB->vals = array($orderID);
        $DB->types = "i";
        $DB->sql = "SELECT * FROM " . $DB->pre . "b2b_sales_order_item WHERE orderID=? AND status=1";
        $orderItems = $DB->dbRows();
    }
}

$isEdit = !empty($D);
$MXFRM = new mxForm();

// Build status options
$statusArr = array("Generated", "Sent", "Overdue", "Paid", "Partial", "Cancelled");
$statusOpt = "";
foreach ($statusArr as $s) {
    $sel = (($D["invoiceStatus"] ?? "Generated") == $s) ? ' selected="selected"' : '';
    $statusOpt .= '<option value="' . $s . '"' . $sel . '>' . $s . '</option>';
}
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <?php if ($isEdit): ?>
        <!-- Edit Mode -->
        <h2 class="form-head"><?php echo $D["invoiceNo"]; ?> - <span class="badge badge-<?php echo $D["invoiceStatus"] == "Paid" ? "success" : ($D["invoiceStatus"] == "Overdue" || $D["invoiceStatus"] == "Cancelled" ? "danger" : "warning"); ?>"><?php echo $D["invoiceStatus"]; ?></span></h2>

        <p>
            <a href="<?php echo ADMINURL; ?>/mod/b2b-invoice/x-b2b-invoice-print.php?id=<?php echo $id; ?>" target="_blank" class="btn">Print</a>
            <a href="<?php echo ADMINURL; ?>/mod/b2b-invoice/x-b2b-invoice-print.php?id=<?php echo $id; ?>&download=1" target="_blank" class="btn">Download PDF</a>
            <?php if (!in_array($D["invoiceStatus"], ["Paid", "Cancelled"])): ?>
            <a href="<?php echo ADMINURL; ?>/b2b-payment-add/?invoiceID=<?php echo $id; ?>&distributorID=<?php echo $D["distributorID"]; ?>" class="btn">Record Payment</a>
            <button type="button" onclick="cancelInvoice()" class="btn" style="background:#dc3545; color:#fff;">Cancel Invoice</button>
            <?php endif; ?>
        </p>

        <form name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="50%" valign="top">
                        <h2 class="form-head">Invoice Details</h2>
                        <ul class="tbl-form">
                            <?php
                            $arrForm = array(
                                array("type" => "text", "name" => "invoiceNo", "value" => $D["invoiceNo"] ?? "", "title" => "Invoice No", "attr" => "readonly"),
                                array("type" => "date", "name" => "invoiceDate", "value" => $D["invoiceDate"] ?? date("Y-m-d"), "title" => "Invoice Date", "validate" => "required"),
                                array("type" => "date", "name" => "dueDate", "value" => $D["dueDate"] ?? "", "title" => "Due Date", "validate" => "required"),
                                array("type" => "select", "name" => "invoiceStatus", "value" => $statusOpt, "title" => "Status"),
                            );
                            echo $MXFRM->getForm($arrForm);
                            ?>
                        </ul>

                        <h2 class="form-head">Distributor Details</h2>
                        <ul class="tbl-form">
                            <?php
                            $arrForm2 = array(
                                array("type" => "text", "name" => "distributorName", "value" => $D["distributorName"] ?? "", "title" => "Distributor Name", "validate" => "required"),
                                array("type" => "text", "name" => "distributorGSTIN", "value" => $D["distributorGSTIN"] ?? "", "title" => "GSTIN"),
                                array("type" => "textarea", "name" => "distributorAddress", "value" => $D["distributorAddress"] ?? "", "title" => "Billing Address", "params" => array("rows" => 2)),
                                array("type" => "textarea", "name" => "shippingAddress", "value" => $D["shippingAddress"] ?? "", "title" => "Shipping Address", "params" => array("rows" => 2)),
                            );
                            echo $MXFRM->getForm($arrForm2);
                            ?>
                        </ul>

                        <h2 class="form-head">Payment Summary</h2>
                        <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                            <tr><td width="40%">Invoice Amount</td><td align="right"><strong>Rs. <?php echo number_format($D["totalAmount"], 2); ?></strong></td></tr>
                            <tr><td>Paid Amount</td><td align="right">Rs. <?php echo number_format($D["paidAmount"], 2); ?></td></tr>
                            <tr><td>Balance</td><td align="right"><strong>Rs. <?php echo number_format($D["balanceAmount"], 2); ?></strong></td></tr>
                        </table>

                        <?php if (count($payments) > 0): ?>
                        <h2 class="form-head">Payment History</h2>
                        <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                            <thead>
                                <tr><th>Payment No</th><th>Date</th><th>Mode</th><th align="right">Amount</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $pay): ?>
                                <tr>
                                    <td><a href="<?php echo ADMINURL; ?>/b2b-payment-add/?id=<?php echo $pay["paymentID"]; ?>"><?php echo $pay["paymentNo"]; ?></a></td>
                                    <td><?php echo date("d-M-Y", strtotime($pay["paymentDate"])); ?></td>
                                    <td><?php echo $pay["paymentMode"]; ?></td>
                                    <td align="right">Rs. <?php echo number_format($pay["allocatedAmount"], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </td>
                    <td width="50%" valign="top">
                        <h2 class="form-head">Amount Details</h2>
                        <ul class="tbl-form">
                            <?php
                            $arrForm3 = array(
                                array("type" => "text", "name" => "subtotal", "value" => $D["subtotal"] ?? "0", "title" => "Subtotal (Rs.)", "validate" => "required,number"),
                                array("type" => "text", "name" => "discountAmount", "value" => $D["discountAmount"] ?? "0", "title" => "Discount (Rs.)", "validate" => "number"),
                                array("type" => "text", "name" => "taxableAmount", "value" => $D["taxableAmount"] ?? "0", "title" => "Taxable Amount (Rs.)", "validate" => "number"),
                                array("type" => "text", "name" => "cgstAmount", "value" => $D["cgstAmount"] ?? "0", "title" => "CGST (Rs.)", "validate" => "number"),
                                array("type" => "text", "name" => "sgstAmount", "value" => $D["sgstAmount"] ?? "0", "title" => "SGST (Rs.)", "validate" => "number"),
                                array("type" => "text", "name" => "igstAmount", "value" => $D["igstAmount"] ?? "0", "title" => "IGST (Rs.)", "validate" => "number"),
                                array("type" => "text", "name" => "totalAmount", "value" => $D["totalAmount"] ?? "0", "title" => "Total Amount (Rs.)", "validate" => "required,number"),
                            );
                            echo $MXFRM->getForm($arrForm3);
                            ?>
                        </ul>

                        <h2 class="form-head">Other Details</h2>
                        <ul class="tbl-form">
                            <?php
                            $arrForm4 = array(
                                array("type" => "text", "name" => "paymentTerms", "value" => $D["paymentTerms"] ?? "", "title" => "Payment Terms"),
                                array("type" => "textarea", "name" => "remarks", "value" => $D["remarks"] ?? "", "title" => "Remarks", "params" => array("rows" => 2)),
                            );
                            echo $MXFRM->getForm($arrForm4);
                            ?>
                        </ul>

                        <?php if (count($items) > 0): ?>
                        <h2 class="form-head">Invoice Items (<?php echo count($items); ?> items)</h2>
                        <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                            <thead>
                                <tr><th>#</th><th>Product</th><th align="center">Qty</th><th align="right">Rate</th><th align="right">Amount</th></tr>
                            </thead>
                            <tbody>
                                <?php
                                $sn = 0;
                                foreach ($items as $item):
                                    $sn++;
                                ?>
                                <tr>
                                    <td><?php echo $sn; ?></td>
                                    <td><strong><?php echo $item["productSKU"]; ?></strong><br><small><?php echo $item["productName"]; ?></small></td>
                                    <td align="center"><?php echo number_format($item["quantity"], 0); ?> <?php echo $item["uom"]; ?></td>
                                    <td align="right">Rs. <?php echo number_format($item["unitPrice"], 2); ?></td>
                                    <td align="right">Rs. <?php echo number_format($item["totalAmount"], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            <?php echo $MXFRM->closeForm(); ?>
        </form>

        <?php elseif ($order): ?>
        <!-- Create from Order -->
        <form name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="xAction" value="CREATE_FROM_ORDER">
            <input type="hidden" name="orderID" value="<?php echo $orderID; ?>">

            <h2 class="form-head">Order Details</h2>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <tr><td width="20%">Order No</td><td><strong><?php echo $order["orderNo"]; ?></strong></td></tr>
                <tr><td>Order Date</td><td><?php echo date("d-M-Y", strtotime($order["orderDate"])); ?></td></tr>
                <tr><td>Distributor</td><td><?php echo $order["distributorName"] ?? $order["companyName"]; ?></td></tr>
                <tr><td>GSTIN</td><td><?php echo $order["gstin"]; ?></td></tr>
                <tr><td>Total Amount</td><td><strong>Rs. <?php echo number_format($order["totalAmount"], 2); ?></strong></td></tr>
            </table>

            <h2 class="form-head">Invoice Settings</h2>
            <ul class="tbl-form">
                <?php
                $arrForm = array(
                    array("type" => "date", "name" => "invoiceDate", "value" => date("Y-m-d"), "title" => "Invoice Date", "validate" => "required"),
                );
                echo $MXFRM->getForm($arrForm);
                ?>
            </ul>
            <p><strong>Note:</strong> Due date will be calculated based on distributor's credit days (<?php echo $order["creditDays"] ?? 30; ?> days).</p>

            <h2 class="form-head">Items Preview (<?php echo count($orderItems); ?> items)</h2>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead>
                    <tr><th>#</th><th>SKU</th><th>Product</th><th align="center">Qty</th><th align="right">Unit Price</th><th align="right">Total</th></tr>
                </thead>
                <tbody>
                    <?php
                    $sn = 0;
                    foreach ($orderItems as $item):
                        $sn++;
                    ?>
                    <tr>
                        <td><?php echo $sn; ?></td>
                        <td><?php echo $item["productSKU"]; ?></td>
                        <td><?php echo $item["productName"]; ?></td>
                        <td align="center"><?php echo number_format($item["quantity"], 0); ?> <?php echo $item["uom"]; ?></td>
                        <td align="right">Rs. <?php echo number_format($item["unitPrice"], 2); ?></td>
                        <td align="right"><strong>Rs. <?php echo number_format($item["totalAmount"], 2); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr><td colspan="5" align="right"><strong>Grand Total:</strong></td><td align="right"><strong>Rs. <?php echo number_format($order["totalAmount"], 2); ?></strong></td></tr>
                </tfoot>
            </table>

            <p>
                <button type="submit" class="btn" onclick="return submitInvoice(event)">Generate Invoice</button>
                <a href="<?php echo ADMINURL; ?>/b2b-sales-order-add/?id=<?php echo $orderID; ?>" class="btn">Back to Order</a>
            </p>
        </form>

        <?php else: ?>
        <!-- No invoice and no order - show guidance -->
        <h2 class="form-head">Create Invoice</h2>
        <table width="100%" border="0" cellspacing="0" cellpadding="20" class="tbl-list">
            <tr><td align="center">
                <p><strong>Please create invoices from sales orders</strong></p>
                <p>Invoices are generated based on confirmed sales orders.</p>
                <p><a href="<?php echo ADMINURL; ?>/b2b-sales-order/" class="btn">Go to Sales Orders</a></p>
            </td></tr>
        </table>
        <?php endif; ?>
    </div>
</div>

<script>
var invoiceID = <?php echo $id; ?>;

function cancelInvoice() {
    var reason = prompt('Please enter reason for cancellation:');
    if (!reason) return;

    $.mxajax({
        url: '<?php echo ADMINURL; ?>/mod/b2b-invoice/x-b2b-invoice.inc.php',
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

function submitInvoice(e) {
    e.preventDefault();
    var formData = $('#frmAddEdit').serialize();

    $.post('<?php echo ADMINURL; ?>/mod/b2b-invoice/x-b2b-invoice.inc.php', formData, function(res) {
        if (res.err == 0) {
            window.location.href = '<?php echo ADMINURL; ?>/b2b-invoice-add/?id=' + res.invoiceID;
        } else {
            alert(res.msg || 'Error creating invoice');
        }
    }, 'json');
    return false;
}
</script>

<script>
// Define required JavaScript variables for form submission
var MODINCURL = '<?php echo ADMINURL; ?>/mod/b2b-invoice/x-b2b-invoice.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/b2b-invoice/';
var ADMINURL = '<?php echo ADMINURL; ?>';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
</script>
