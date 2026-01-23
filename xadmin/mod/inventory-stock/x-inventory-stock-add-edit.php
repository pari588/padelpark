<?php
$D = array();

// Warehouse dropdown
$warehouseOpt = '<option value="">-- Select Warehouse --</option>';
$DB->vals = array(1, 1);
$DB->types = "ii";
$DB->sql = "SELECT warehouseID, warehouseCode, warehouseName FROM " . $DB->pre . "warehouse WHERE status=? AND isActive=? ORDER BY warehouseName";
$warehouses = $DB->dbRows();
foreach ($warehouses as $w) {
    $warehouseOpt .= '<option value="' . $w["warehouseID"] . '">' . $w["warehouseCode"] . ' - ' . htmlspecialchars($w["warehouseName"]) . '</option>';
}

// Product dropdown
$productOpt = '<option value="">-- Select Product --</option>';
$DB->vals = array(1, 1);
$DB->types = "ii";
$DB->sql = "SELECT productID, productSKU, productName, uom FROM " . $DB->pre . "product WHERE status=? AND isActive=? ORDER BY productName";
$products = $DB->dbRows();
foreach ($products as $p) {
    $productOpt .= '<option value="' . $p["productID"] . '" data-uom="' . $p["uom"] . '">' . $p["productSKU"] . ' - ' . htmlspecialchars($p["productName"]) . '</option>';
}

// Adjustment type options
$adjustmentTypeOpt = '<option value="IN">Stock IN (Add)</option><option value="OUT">Stock OUT (Remove)</option>';

// Form array
$arrForm = array(
    array("type" => "select", "name" => "warehouseID", "value" => $warehouseOpt, "title" => "Warehouse", "validate" => "required", "default" => false),
    array("type" => "select", "name" => "productID", "value" => $productOpt, "title" => "Product", "validate" => "required", "default" => false),
    array("type" => "select", "name" => "adjustmentType", "value" => $adjustmentTypeOpt, "title" => "Adjustment Type", "validate" => "required", "default" => false),
    array("type" => "text", "name" => "quantity", "value" => "", "title" => "Quantity", "validate" => "required,number"),
    array("type" => "text", "name" => "referenceNo", "value" => "", "title" => "Reference No.", "info" => '<span class="info">Invoice, PO, or internal reference</span>'),
    array("type" => "textarea", "name" => "reason", "value" => "", "title" => "Reason/Notes", "params" => array("rows" => 3))
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form">
            <div class="form-section" style="margin: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #0d6efd;">
                <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #333;">Stock Adjustment</h3>
                <ul class="tbl-form">
                    <?php echo $MXFRM->getForm($arrForm); ?>
                </ul>
            </div>

            <!-- Current Stock Info -->
            <div class="form-section" style="margin: 15px; padding: 15px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
                <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #333;">Current Stock Information</h3>
                <div id="currentStockInfo" style="padding: 10px;">
                    <p style="color: #666;">Select a warehouse and product to view current stock level.</p>
                </div>
            </div>
        </div>

        <input type="hidden" name="xAction" value="ADJUST" />
        <div class="wrap-btn">
            <button type="submit" class="btn" id="btnSubmit">Submit Adjustment</button>
            <a href="<?php echo ADMINURL; ?>/inventory-stock/" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
// Fetch current stock when warehouse and product are selected
document.addEventListener('DOMContentLoaded', function() {
    var warehouseSelect = document.querySelector('[name="warehouseID"]');
    var productSelect = document.querySelector('[name="productID"]');
    var stockInfo = document.getElementById('currentStockInfo');

    function updateStockInfo() {
        var warehouseID = warehouseSelect.value;
        var productID = productSelect.value;

        if (warehouseID && productID) {
            stockInfo.innerHTML = '<p style="color: #666;">Loading...</p>';

            // Make AJAX call to get current stock
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo ADMINURL; ?>/mod/inventory-stock/x-inventory-stock.inc.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.err === 0 && response.data && response.data.length > 0) {
                            var stock = response.data[0];
                            stockInfo.innerHTML = '<table style="width: 100%;">' +
                                '<tr><td style="width: 40%; color: #666;">Current Stock:</td><td><strong style="font-size: 18px;">' + parseFloat(stock.quantity).toFixed(2) + ' ' + (stock.uom || '') + '</strong></td></tr>' +
                                '<tr><td style="color: #666;">Reorder Level:</td><td>' + (stock.reorderLevel || 0) + '</td></tr>' +
                                '<tr><td style="color: #666;">Last Updated:</td><td>' + (stock.lastUpdated || 'N/A') + '</td></tr>' +
                                '</table>';
                        } else {
                            stockInfo.innerHTML = '<p style="color: #28a745;"><strong>No stock record exists.</strong><br>A new stock record will be created.</p>';
                        }
                    } catch (e) {
                        stockInfo.innerHTML = '<p style="color: #dc3545;">Error loading stock info</p>';
                    }
                }
            };
            xhr.send('xAction=GET_SUMMARY&warehouseID=' + warehouseID + '&productID=' + productID);
        } else {
            stockInfo.innerHTML = '<p style="color: #666;">Select a warehouse and product to view current stock level.</p>';
        }
    }

    warehouseSelect.addEventListener('change', updateStockInfo);
    productSelect.addEventListener('change', updateStockInfo);

    // Form submission handling
    document.getElementById('frmAddEdit').addEventListener('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '<?php echo ADMINURL; ?>/mod/inventory-stock/x-inventory-stock.inc.php', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.err === 0) {
                        alert(response.msg || 'Stock adjusted successfully!');
                        window.location.href = '<?php echo ADMINURL; ?>/inventory-stock/';
                    } else {
                        alert(response.msg || 'Error adjusting stock');
                    }
                } catch (e) {
                    alert('Error processing request');
                }
            }
        };
        xhr.send(new URLSearchParams(formData).toString());
    });
});
</script>

<style>
.form-section h3 {
    font-weight: 600;
}
.form-section .tbl-form {
    margin: 0;
    padding: 0;
}
.info {
    font-size: 11px;
    color: #666;
    font-style: italic;
}
.wrap-btn {
    margin: 15px;
    padding: 15px;
    text-align: center;
}
.btn-secondary {
    background: #6c757d;
    color: #fff;
    margin-left: 10px;
}
</style>

<script>
// Define required JavaScript variables for form submission
var MODINCURL = '<?php echo ADMINURL; ?>/mod/inventory-stock/x-inventory-stock.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/inventory-stock/';
var ADMINURL = '<?php echo ADMINURL; ?>';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
</script>
