<?php
$id = 0;
$D = array();
$items = array();

if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT o.*, d.companyName, d.distributorCode, d.gstin, d.creditLimit, d.currentOutstanding, d.creditDays, d.billingStateCode
                FROM `" . $DB->pre . $MXMOD["TBL"] . "` o
                LEFT JOIN " . $DB->pre . "distributor d ON o.distributorID = d.distributorID
                WHERE o.status=? AND o.`" . $MXMOD["PK"] . "`=?";
    $D = $DB->dbRow();

    // Get items
    $DB->vals = array($id);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "b2b_sales_order_item WHERE orderID=? AND status=1";
    $items = $DB->dbRows();
}

// Pre-select distributor if provided
$preSelectDistributor = isset($_GET["distributorID"]) ? intval($_GET["distributorID"]) : ($D["distributorID"] ?? 0);

// Check if order is editable
$isEditable = empty($D) || !in_array($D["orderStatus"] ?? "Draft", ["Shipped", "Delivered", "Cancelled"]);

// Get warehouses for dropdown
$whrArr = array("sql" => "status=? AND isActive=?", "types" => "ii", "vals" => array(1, 1));
$warehouseOpt = getTableDD(["table" => $DB->pre . "warehouse", "key" => "warehouseID", "val" => "CONCAT(warehouseCode, ' - ', warehouseName)", "selected" => ($D["warehouseID"] ?? 0), "where" => $whrArr]);

// Get distributors
$DB->vals = array(1, 1);
$DB->types = "ii";
$DB->sql = "SELECT distributorID, companyName, distributorCode, creditLimit, currentOutstanding, creditDays, gstin, billingStateCode, shippingAddress FROM " . $DB->pre . "distributor WHERE status=? AND isActive=? ORDER BY companyName";
$distributors = $DB->dbRows();

// Build distributor dropdown
$distOpt = "";
foreach ($distributors as $dist) {
    $sel = ($preSelectDistributor == $dist["distributorID"]) ? ' selected="selected"' : '';
    $dataAttrs = ' data-name="' . htmlspecialchars($dist["companyName"]) . '"';
    $dataAttrs .= ' data-gstin="' . $dist["gstin"] . '"';
    $dataAttrs .= ' data-credit="' . $dist["creditLimit"] . '"';
    $dataAttrs .= ' data-outstanding="' . $dist["currentOutstanding"] . '"';
    $dataAttrs .= ' data-days="' . $dist["creditDays"] . '"';
    $dataAttrs .= ' data-statecode="' . $dist["billingStateCode"] . '"';
    $dataAttrs .= ' data-address="' . htmlspecialchars($dist["shippingAddress"] ?? "") . '"';
    $distOpt .= '<option value="' . $dist["distributorID"] . '"' . $sel . $dataAttrs . '>' . $dist["distributorCode"] . ' - ' . $dist["companyName"] . '</option>';
}

$arrForm = array(
    array("type" => "select", "name" => "distributorID", "value" => $distOpt, "title" => "Distributor", "validate" => "required", "params" => array("id" => "distributorID", "onchange" => "onDistributorChange()")),
    array("type" => "date", "name" => "orderDate", "value" => $D["orderDate"] ?? date("Y-m-d"), "title" => "Order Date", "validate" => "required"),
    array("type" => "select", "name" => "warehouseID", "value" => $warehouseOpt, "title" => "Warehouse", "validate" => "required"),
    array("type" => "text", "name" => "paymentTerms", "value" => $D["paymentTerms"] ?? "Net 30", "title" => "Payment Terms", "params" => array("id" => "paymentTerms")),
    array("type" => "text", "name" => "poNumber", "value" => $D["poNumber"] ?? "", "title" => "PO Number"),
    array("type" => "date", "name" => "poDate", "value" => $D["poDate"] ?? "", "title" => "PO Date"),
    array("type" => "textarea", "name" => "shippingAddress", "value" => $D["shippingAddress"] ?? "", "title" => "Shipping Address", "params" => array("rows" => 2, "id" => "shippingAddress")),
    array("type" => "textarea", "name" => "remarks", "value" => $D["remarks"] ?? "", "title" => "Remarks", "params" => array("rows" => 2)),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <?php if (!empty($D)): ?>
    <div style="padding: 15px; margin: 0 15px 15px; background: #f8f9fa; border-radius: 8px;">
        <strong style="font-size: 18px;"><?php echo $D["orderNo"]; ?></strong>
        <span style="margin-left: 10px; padding: 3px 10px; border-radius: 3px; background: <?php echo $D["orderStatus"] == "Draft" ? "#6c757d" : ($D["orderStatus"] == "Delivered" ? "#198754" : "#0d6efd"); ?>; color: #fff; font-size: 12px;"><?php echo $D["orderStatus"]; ?></span>
        <span style="margin-left: 5px; padding: 3px 10px; border-radius: 3px; background: <?php echo $D["paymentStatus"] == "Paid" ? "#198754" : ($D["paymentStatus"] == "Pending" ? "#ffc107" : "#dc3545"); ?>; color: <?php echo $D["paymentStatus"] == "Pending" ? "#000" : "#fff"; ?>; font-size: 12px;"><?php echo $D["paymentStatus"]; ?></span>
        <?php if ($D["orderStatus"] == "Draft"): ?>
            <button type="button" onclick="confirmOrder()" style="float: right; background: #0d6efd; color: #fff; border: none; padding: 5px 15px; border-radius: 4px; cursor: pointer;"><i class="fa fa-check"></i> Confirm Order</button>
        <?php endif; ?>
        <?php if (in_array($D["orderStatus"], ["Confirmed", "Processing", "Partially Shipped"])): ?>
            <a href="<?php echo ADMINURL; ?>/b2b-invoice-add/?orderID=<?php echo $id; ?>" style="float: right; background: #198754; color: #fff; padding: 5px 15px; border-radius: 4px; text-decoration: none;"><i class="fa fa-file-text-o"></i> Create Invoice</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (!$isEditable): ?>
    <div style="padding: 15px; margin: 0 15px 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
        <i class="fa fa-exclamation-triangle"></i> This order is <?php echo $D["orderStatus"]; ?> and cannot be edited.
    </div>
    <?php endif; ?>

    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form">
            <h2 class="form-head">Order Details</h2>
            <ul class="tbl-form"><?php echo $MXFRM->getForm($arrForm); ?></ul>

            <!-- Credit Info Display -->
            <div id="creditInfo" style="display: <?php echo !empty($D) ? 'block' : 'none'; ?>; margin: 15px; padding: 15px; background: #e9ecef; border-radius: 8px;">
                <table width="100%">
                    <tr>
                        <td align="center" width="33%">
                            <small>Credit Limit</small><br>
                            <strong id="creditLimit">Rs. <?php echo number_format($D["creditLimit"] ?? 0, 0); ?></strong>
                        </td>
                        <td align="center" width="33%">
                            <small>Outstanding</small><br>
                            <strong id="currentOutstanding" style="color: #dc3545;">Rs. <?php echo number_format($D["currentOutstanding"] ?? 0, 0); ?></strong>
                        </td>
                        <td align="center" width="33%">
                            <small>Available</small><br>
                            <strong id="availableCredit" style="color: #198754;">Rs. <?php echo number_format(($D["creditLimit"] ?? 0) - ($D["currentOutstanding"] ?? 0), 0); ?></strong>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Order Items Section -->
            <div style="margin: 20px 15px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <h3 style="margin-bottom: 15px; border-bottom: 2px solid #0d6efd; padding-bottom: 10px;">
                    Order Items
                    <?php if ($isEditable): ?>
                    <button type="button" onclick="openProductSearch()" style="float: right; background: #0d6efd; color: #fff; border: none; padding: 5px 15px; border-radius: 4px; cursor: pointer;"><i class="fa fa-plus"></i> Add Product</button>
                    <?php endif; ?>
                </h3>
                <table id="itemsTable" class="tbl-list" style="width: 100%;">
                    <thead>
                        <tr style="background: #e9ecef;">
                            <th style="padding: 10px;">#</th>
                            <th style="padding: 10px;">SKU</th>
                            <th style="padding: 10px;">Product</th>
                            <th style="padding: 10px;">Qty</th>
                            <th style="padding: 10px;">UOM</th>
                            <th style="padding: 10px; text-align: right;">Unit Price</th>
                            <th style="padding: 10px;">Disc %</th>
                            <th style="padding: 10px;">GST %</th>
                            <th style="padding: 10px; text-align: right;">Total</th>
                            <?php if ($isEditable): ?><th style="padding: 10px;"></th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <?php if (count($items) > 0):
                            $sn = 0;
                            foreach ($items as $item):
                                $sn++;
                        ?>
                        <tr data-row="<?php echo $sn; ?>">
                            <td style="padding: 8px;"><?php echo $sn; ?></td>
                            <td style="padding: 8px;"><?php echo $item["productSKU"]; ?></td>
                            <td style="padding: 8px;">
                                <?php echo $item["productName"]; ?>
                                <input type="hidden" name="items[<?php echo $sn; ?>][productID]" value="<?php echo $item["productID"]; ?>">
                                <input type="hidden" name="items[<?php echo $sn; ?>][productSKU]" value="<?php echo $item["productSKU"]; ?>">
                                <input type="hidden" name="items[<?php echo $sn; ?>][productName]" value="<?php echo $item["productName"]; ?>">
                                <input type="hidden" name="items[<?php echo $sn; ?>][hsnCode]" value="<?php echo $item["hsnCode"]; ?>">
                            </td>
                            <td style="padding: 8px;"><input type="number" name="items[<?php echo $sn; ?>][quantity]" value="<?php echo $item["quantity"]; ?>" min="0.01" step="0.01" onchange="calculateRow(<?php echo $sn; ?>)" style="width: 70px; padding: 5px; border: 1px solid #ddd; border-radius: 4px;" <?php echo !$isEditable ? 'disabled' : ''; ?>></td>
                            <td style="padding: 8px;">
                                <input type="hidden" name="items[<?php echo $sn; ?>][uom]" value="<?php echo $item["uom"]; ?>">
                                <?php echo $item["uom"]; ?>
                            </td>
                            <td style="padding: 8px;"><input type="number" name="items[<?php echo $sn; ?>][unitPrice]" value="<?php echo $item["unitPrice"]; ?>" min="0" step="0.01" onchange="calculateRow(<?php echo $sn; ?>)" style="width: 100px; padding: 5px; border: 1px solid #ddd; border-radius: 4px; text-align: right;" <?php echo !$isEditable ? 'disabled' : ''; ?>></td>
                            <td style="padding: 8px;"><input type="number" name="items[<?php echo $sn; ?>][discountPercent]" value="<?php echo $item["discountPercent"]; ?>" min="0" max="100" step="0.01" onchange="calculateRow(<?php echo $sn; ?>)" style="width: 60px; padding: 5px; border: 1px solid #ddd; border-radius: 4px;" <?php echo !$isEditable ? 'disabled' : ''; ?>></td>
                            <td style="padding: 8px;">
                                <input type="hidden" name="items[<?php echo $sn; ?>][gstRate]" value="<?php echo $item["gstRate"]; ?>">
                                <?php echo $item["gstRate"]; ?>%
                            </td>
                            <td style="padding: 8px; text-align: right;" class="line-total"><strong>Rs. <?php echo number_format($item["totalAmount"], 2); ?></strong>
                                <input type="hidden" name="items[<?php echo $sn; ?>][totalAmount]" value="<?php echo $item["totalAmount"]; ?>">
                            </td>
                            <?php if ($isEditable): ?>
                            <td style="padding: 8px;"><button type="button" onclick="removeRow(<?php echo $sn; ?>)" style="background: #dc3545; color: #fff; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;"><i class="fa fa-times"></i></button></td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr id="emptyRow"><td colspan="<?php echo $isEditable ? 10 : 9; ?>" style="padding: 20px; text-align: center; color: #999;">No items added</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Order Summary -->
            <div style="margin: 20px 15px; padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 8px;">
                <h3 style="margin-bottom: 15px; border-bottom: 2px solid #198754; padding-bottom: 10px;">Order Summary</h3>
                <table width="50%" style="margin-left: auto;">
                    <tr><td style="padding: 8px;">Subtotal</td><td style="padding: 8px; text-align: right;" id="summarySubtotal">Rs. <?php echo number_format($D["subtotal"] ?? 0, 2); ?></td></tr>
                    <tr><td style="padding: 8px;">Discount</td><td style="padding: 8px; text-align: right; color: #dc3545;" id="summaryDiscount">- Rs. <?php echo number_format($D["discountAmount"] ?? 0, 2); ?></td></tr>
                    <tr><td style="padding: 8px;">Taxable Amount</td><td style="padding: 8px; text-align: right;" id="summaryTaxable">Rs. <?php echo number_format($D["taxableAmount"] ?? 0, 2); ?></td></tr>
                    <tr id="cgstRow"><td style="padding: 8px;">CGST</td><td style="padding: 8px; text-align: right;" id="summaryCgst">Rs. <?php echo number_format($D["cgstAmount"] ?? 0, 2); ?></td></tr>
                    <tr id="sgstRow"><td style="padding: 8px;">SGST</td><td style="padding: 8px; text-align: right;" id="summarySgst">Rs. <?php echo number_format($D["sgstAmount"] ?? 0, 2); ?></td></tr>
                    <tr id="igstRow" style="display: none;"><td style="padding: 8px;">IGST</td><td style="padding: 8px; text-align: right;" id="summaryIgst">Rs. <?php echo number_format($D["igstAmount"] ?? 0, 2); ?></td></tr>
                    <tr style="font-weight: bold; background: #f8f9fa;"><td style="padding: 10px; font-size: 16px;">Total</td><td style="padding: 10px; text-align: right; font-size: 18px;" id="summaryTotal">Rs. <?php echo number_format($D["totalAmount"] ?? 0, 2); ?></td></tr>
                </table>

                <!-- Hidden fields for totals -->
                <input type="hidden" name="subtotal" id="subtotalField" value="<?php echo $D["subtotal"] ?? 0; ?>">
                <input type="hidden" name="discountAmount" id="discountAmountField" value="<?php echo $D["discountAmount"] ?? 0; ?>">
                <input type="hidden" name="taxableAmount" id="taxableAmountField" value="<?php echo $D["taxableAmount"] ?? 0; ?>">
                <input type="hidden" name="cgstAmount" id="cgstAmountField" value="<?php echo $D["cgstAmount"] ?? 0; ?>">
                <input type="hidden" name="sgstAmount" id="sgstAmountField" value="<?php echo $D["sgstAmount"] ?? 0; ?>">
                <input type="hidden" name="igstAmount" id="igstAmountField" value="<?php echo $D["igstAmount"] ?? 0; ?>">
                <input type="hidden" name="totalAmount" id="totalAmountField" value="<?php echo $D["totalAmount"] ?? 0; ?>">
                <input type="hidden" name="distributorName" id="distributorNameField" value="<?php echo $D["distributorName"] ?? ""; ?>">
                <input type="hidden" name="distributorGSTIN" id="distributorGSTINField" value="<?php echo $D["distributorGSTIN"] ?? ""; ?>">
            </div>
        </div>
        <?php if ($isEditable): ?>
        <?php echo $MXFRM->closeForm(); ?>
        <?php endif; ?>
    </form>
</div>

<!-- Product Search Popup -->
<div id="productModal" onclick="if(event.target===this) closeProductModal();" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #fff; border-radius: 8px; width: 800px; max-width: 90%; max-height: 80vh; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
        <div style="padding: 15px 20px; background: #0d6efd; color: #fff; display: flex; justify-content: space-between; align-items: center;">
            <h4 style="margin: 0; font-size: 16px;">Search Products</h4>
            <button type="button" onclick="closeProductModal()" style="background: none; border: none; color: #fff; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
        </div>
        <div style="padding: 20px;">
            <div style="margin-bottom: 15px;">
                <input type="text" id="productSearch" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;" placeholder="Search by SKU or Product Name...">
                <button type="button" onclick="searchProducts()" style="margin-top: 10px; padding: 10px 20px; background: #0d6efd; color: white; border: none; border-radius: 4px; cursor: pointer;">Search</button>
            </div>
            <div id="productResults" style="max-height: 400px; overflow-y: auto;">
                <p style="text-align: center; color: #999; padding: 40px 0;">Type to search and click Search button</p>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo ADMINURL; ?>/mod/b2b-sales-order/inc/js/x-b2b-sales-order.inc.js"></script>
<script>
var ADMINURL = '<?php echo ADMINURL; ?>';
var MODINCURL = '<?php echo ADMINURL; ?>/mod/b2b-sales-order/x-b2b-sales-order.inc.php';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
var orderID = <?php echo $id; ?>;
var rowCount = <?php echo count($items); ?>;
var ourStateCode = '27';
var taxZone = 'Local';
var XTOKEN = '<?php echo $_SESSION[SITEURL]["CSRF_TOKEN"] ?? ""; ?>';

$(document).ready(function() {
    onDistributorChange();
});

function onDistributorChange() {
    var sel = document.getElementById('distributorID');
    var opt = sel.options[sel.selectedIndex];
    var info = document.getElementById('creditInfo');

    if (sel.value) {
        info.style.display = 'block';
        document.getElementById('creditLimit').textContent = 'Rs. ' + parseFloat(opt.dataset.credit || 0).toLocaleString('en-IN');
        document.getElementById('currentOutstanding').textContent = 'Rs. ' + parseFloat(opt.dataset.outstanding || 0).toLocaleString('en-IN');
        var avail = parseFloat(opt.dataset.credit || 0) - parseFloat(opt.dataset.outstanding || 0);
        document.getElementById('availableCredit').textContent = 'Rs. ' + avail.toLocaleString('en-IN');
        document.getElementById('paymentTerms').value = 'Net ' + (opt.dataset.days || 30);
        document.getElementById('shippingAddress').value = opt.dataset.address || '';
        document.getElementById('distributorNameField').value = opt.dataset.name || '';
        document.getElementById('distributorGSTINField').value = opt.dataset.gstin || '';
        updateTaxDisplay(opt.dataset.statecode);
    } else {
        info.style.display = 'none';
    }
}

function updateTaxDisplay(distStateCode) {
    var isInterstate = distStateCode && distStateCode != ourStateCode;
    taxZone = isInterstate ? 'Interstate' : 'Local';
    document.getElementById('cgstRow').style.display = isInterstate ? 'none' : '';
    document.getElementById('sgstRow').style.display = isInterstate ? 'none' : '';
    document.getElementById('igstRow').style.display = isInterstate ? '' : 'none';
}

function confirmOrder() {
    if (!confirm('Are you sure you want to confirm this order?')) return;
    $.ajax({
        url: '<?php echo ADMINURL; ?>/mod/b2b-sales-order/x-b2b-sales-order.inc.php',
        type: 'POST',
        data: {
            xAction: 'CONFIRM',
            orderID: orderID
        },
        dataType: 'json',
        success: function(res) {
            if (res.err == 0) {
                location.reload();
            } else {
                alert(res.msg || 'Error confirming order');
            }
        },
        error: function() {
            alert('Error confirming order. Please try again.');
        }
    });
}
</script>
