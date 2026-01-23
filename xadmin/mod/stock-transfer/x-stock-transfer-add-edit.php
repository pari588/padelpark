<?php
// Warehouse dropdown
$warehouseOpt = '<option value="">-- Select Warehouse --</option>';
$DB->vals = array(1, 1);
$DB->types = "ii";
$DB->sql = "SELECT warehouseID, warehouseCode, warehouseName, warehouseType FROM " . $DB->pre . "warehouse WHERE status=? AND isActive=? ORDER BY warehouseType, warehouseName";
$warehouses = $DB->dbRows();
foreach ($warehouses as $w) {
    $warehouseOpt .= '<option value="' . $w["warehouseID"] . '">[' . $w["warehouseType"] . '] ' . $w["warehouseCode"] . ' - ' . htmlspecialchars($w["warehouseName"]) . '</option>';
}

// Product dropdown
$productOpt = '<option value="">-- Select Product --</option>';
$DB->vals = array(1, 1);
$DB->types = "ii";
$DB->sql = "SELECT productID, productSKU, productName, uom FROM " . $DB->pre . "product WHERE status=? AND isActive=? AND isStockable=1 ORDER BY productName";
$products = $DB->dbRows();
foreach ($products as $p) {
    $productOpt .= '<option value="' . $p["productID"] . '" data-uom="' . $p["uom"] . '">' . $p["productSKU"] . ' - ' . htmlspecialchars($p["productName"]) . '</option>';
}

// Form array
$arrForm = array(
    array("type" => "select", "name" => "fromWarehouseID", "value" => $warehouseOpt, "title" => "From Warehouse", "validate" => "required", "default" => false),
    array("type" => "select", "name" => "toWarehouseID", "value" => $warehouseOpt, "title" => "To Warehouse", "validate" => "required", "default" => false),
    array("type" => "select", "name" => "productID", "value" => $productOpt, "title" => "Product", "validate" => "required", "default" => false),
    array("type" => "text", "name" => "quantity", "value" => "", "title" => "Quantity to Transfer", "validate" => "required,number"),
    array("type" => "textarea", "name" => "notes", "value" => "", "title" => "Transfer Notes", "params" => array("rows" => 3))
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form">
            <h2 class="form-head">Stock Transfer Details</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm); ?>
            </ul>
            <h2 class="form-head">Source Warehouse Stock</h2>
            <table width="100%" border="0" cellspacing="0" cellpadding="10" class="tbl-list">
                <tr>
                    <td id="sourceStockInfo" align="center">Select source warehouse and product to view available stock.</td>
                </tr>
            </table>
        </div>
        <input type="hidden" name="xAction" value="TRANSFER" />
        <?php echo getFormButtons("inventory-stock"); ?>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var fromWarehouseSelect = document.querySelector('[name="fromWarehouseID"]');
    var toWarehouseSelect = document.querySelector('[name="toWarehouseID"]');
    var productSelect = document.querySelector('[name="productID"]');
    var quantityInput = document.querySelector('[name="quantity"]');
    var stockInfo = document.getElementById('sourceStockInfo');

    // Prevent selecting same warehouse for from and to
    toWarehouseSelect.addEventListener('change', function() {
        if (this.value && this.value === fromWarehouseSelect.value) {
            alert('Source and destination warehouse cannot be the same');
            this.value = '';
        }
    });

    fromWarehouseSelect.addEventListener('change', function() {
        if (this.value && this.value === toWarehouseSelect.value) {
            toWarehouseSelect.value = '';
        }
        updateSourceStock();
    });

    productSelect.addEventListener('change', updateSourceStock);

    function updateSourceStock() {
        var warehouseID = fromWarehouseSelect.value;
        var productID = productSelect.value;

        if (warehouseID && productID) {
            stockInfo.innerHTML = 'Loading...';

            $.ajax({
                url: '<?php echo ADMINURL; ?>/mod/inventory-stock/x-inventory-stock.inc.php',
                type: 'POST',
                data: {
                    xAction: 'GET_SUMMARY',
                    warehouseID: warehouseID,
                    productID: productID,
                    xToken: '<?php echo $_SESSION[SITEURL]["CSRF_TOKEN"] ?? ""; ?>'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.err === 0 && response.data && response.data.length > 0) {
                        var stock = response.data[0];
                        var qty = parseFloat(stock.quantity || stock.availableQty || 0);
                        stockInfo.innerHTML = '<strong style="color: #198754;">Available Stock: ' + qty.toFixed(2) + ' ' + (stock.uom || 'Pcs') + '</strong>';
                        quantityInput.setAttribute('max', qty);
                    } else {
                        stockInfo.innerHTML = '<strong style="color: #dc3545;">No stock available</strong> at source warehouse for this product.';
                        quantityInput.setAttribute('max', 0);
                    }
                },
                error: function() {
                    stockInfo.innerHTML = '<span style="color: #dc3545;">Error loading stock info</span>';
                }
            });
        } else {
            stockInfo.innerHTML = 'Select source warehouse and product to view available stock.';
        }
    }

    // Form submission
    document.getElementById('frmAddEdit').addEventListener('submit', function(e) {
        e.preventDefault();

        var formData = {
            xAction: 'TRANSFER',
            fromWarehouseID: fromWarehouseSelect.value,
            toWarehouseID: toWarehouseSelect.value,
            productID: productSelect.value,
            quantity: quantityInput.value,
            notes: document.querySelector('[name="notes"]').value,
            xToken: '<?php echo $_SESSION[SITEURL]["CSRF_TOKEN"] ?? ""; ?>'
        };

        $.ajax({
            url: '<?php echo ADMINURL; ?>/mod/inventory-stock/x-inventory-stock.inc.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.err === 0) {
                    alert(response.msg || 'Stock transferred successfully!');
                    window.location.href = '<?php echo ADMINURL; ?>/inventory-stock/';
                } else {
                    alert(response.msg || 'Error transferring stock');
                }
            },
            error: function() {
                alert('Error processing request');
            }
        });
    });
});
</script>


<script>
// Define required JavaScript variables for form submission
var MODINCURL = '<?php echo ADMINURL; ?>/mod/stock-transfer/x-stock-transfer.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/stock-transfer/';
var ADMINURL = '<?php echo ADMINURL; ?>';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
</script>
