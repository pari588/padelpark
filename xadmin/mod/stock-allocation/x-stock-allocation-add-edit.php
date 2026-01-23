<?php
require_once("x-stock-allocation.inc.php");

$id = 0;
$D = array();
$items = array();

if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $D = getAllocationDetails($id);

    if (!$D) {
        echo '<div class="wrap-right">' . getPageNav() . '<div class="wrap-data"><div class="alert alert-danger">Allocation not found</div></div></div>';
        return;
    }
    $items = $D["items"] ?? array();
}

$isDispatched = ($D["allocationType"] ?? "") === "Dispatched";

// Get dropdowns
$projectOpt = getProjectDropdown($D["projectID"] ?? 0);
$warehouseOpt = getWarehouseDropdown($D["warehouseID"] ?? 0);

$arrForm = array(
    array("type" => "text", "name" => "allocationNo", "value" => $D["allocationNo"] ?? "(Auto-generated)", "title" => "Allocation No", "params" => array("readonly" => "readonly")),
    array("type" => "select", "name" => "projectID", "value" => $projectOpt, "title" => "Project *", "validate" => "req", "params" => $isDispatched ? array("disabled" => "disabled") : array()),
    array("type" => "select", "name" => "warehouseID", "value" => $warehouseOpt, "title" => "Source Warehouse *", "validate" => "req", "params" => $isDispatched ? array("disabled" => "disabled") : array()),
    array("type" => "date", "name" => "allocationDate", "value" => $D["allocationDate"] ?? date("Y-m-d"), "title" => "Allocation Date", "params" => $isDispatched ? array("disabled" => "disabled") : array()),
    array("type" => "textarea", "name" => "notes", "value" => $D["notes"] ?? "", "title" => "Notes", "params" => array("rows" => 2))
);

// Dispatch details (for edit/view)
if ($id > 0) {
    $arrForm[] = array("type" => "text", "name" => "vehicleNo", "value" => $D["vehicleNo"] ?? "", "title" => "Vehicle No");
    $arrForm[] = array("type" => "text", "name" => "driverName", "value" => $D["driverName"] ?? "", "title" => "Driver Name");
    $arrForm[] = array("type" => "text", "name" => "driverPhone", "value" => $D["driverPhone"] ?? "", "title" => "Driver Phone");
    $arrForm[] = array("type" => "text", "name" => "ewayBillNo", "value" => $D["ewayBillNo"] ?? "", "title" => "E-Way Bill No");
}

$MXFRM = new mxForm();
?>
<style>
.items-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
.items-table th { background: #f5f5f5; padding: 10px; text-align: left; font-size: 12px; border: 1px solid #ddd; }
.items-table td { padding: 8px; border: 1px solid #ddd; vertical-align: middle; }
.items-table input, .items-table select { width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; }
.items-table .qty-input { width: 80px; text-align: right; }
.items-table .cost-input { width: 100px; text-align: right; }
.btn-add-item { background: #28a745; color: #fff; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; margin-top: 10px; }
.btn-remove { background: #dc3545; color: #fff; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; }
.product-select { min-width: 250px; }
.stock-info { font-size: 11px; color: #666; }
.total-row { background: #f8f9fa; font-weight: bold; }
</style>

<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post">
        <?php if ($isDispatched): ?>
            <div class="alert alert-info" style="margin: 15px; padding: 15px; background: #e3f2fd; border-left: 4px solid #2196f3;">
                <strong>Dispatched</strong> - This allocation was dispatched on <?php echo date("d M Y", strtotime($D["dispatchDate"])); ?>. Items are in transit to the project site.
            </div>
        <?php endif; ?>

        <ul class="tbl-form"><?php echo $MXFRM->getForm($arrForm); ?></ul>

        <!-- Items Section -->
        <table width="100%" border="0" cellspacing="0" cellpadding="5" class="tbl-form">
            <tr>
                <td class="fld-label" style="vertical-align: top;">Items to Allocate</td>
                <td class="fld-value">
                    <table class="items-table" id="itemsTable">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="35%">Product</th>
                                <th width="10%">Available</th>
                                <th width="10%">Qty</th>
                                <th width="8%">Unit</th>
                                <th width="12%">Unit Cost</th>
                                <th width="12%">Total</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <?php if (!empty($items)): ?>
                                <?php foreach ($items as $i => $item): ?>
                                <tr>
                                    <td style="text-align: center;"><?php echo $i + 1; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item["productName"]); ?></strong><br>
                                        <small class="stock-info"><?php echo htmlspecialchars($item["productSKU"]); ?></small>
                                        <input type="hidden" name="items[<?php echo $i; ?>][productID]" value="<?php echo $item["productID"]; ?>">
                                    </td>
                                    <td style="text-align: center;">-</td>
                                    <td><input type="number" name="items[<?php echo $i; ?>][qty]" value="<?php echo $item["allocatedQty"]; ?>" class="qty-input" step="0.001" <?php echo $isDispatched ? 'disabled' : ''; ?>></td>
                                    <td style="text-align: center;"><?php echo htmlspecialchars($item["unit"]); ?></td>
                                    <td><input type="number" name="items[<?php echo $i; ?>][unitCost]" value="<?php echo $item["unitCost"]; ?>" class="cost-input" step="0.01" <?php echo $isDispatched ? 'disabled' : ''; ?>></td>
                                    <td style="text-align: right;">₹<?php echo number_format($item["totalCost"], 2); ?></td>
                                    <td>
                                        <?php if (!$isDispatched): ?>
                                        <button type="button" class="btn-remove" onclick="removeItem(this)">&times;</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="6" style="text-align: right;">Total Value:</td>
                                <td style="text-align: right;" id="grandTotal">₹<?php echo number_format($D["totalValue"] ?? 0, 2); ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>

                    <?php if (!$isDispatched): ?>
                    <div style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 4px;">
                        <strong>Add Product:</strong>
                        <div style="display: flex; gap: 10px; margin-top: 10px;">
                            <select id="productSelect" class="product-select" style="flex: 2;">
                                <option value="">-- Select warehouse first --</option>
                            </select>
                            <input type="number" id="addQty" placeholder="Qty" style="width: 100px;" step="0.001">
                            <button type="button" class="btn-add-item" onclick="addItem()">+ Add</button>
                        </div>
                    </div>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <!-- Actions -->
        <table width="100%" border="0" cellspacing="0" cellpadding="5" class="tbl-form">
            <tr>
                <td class="fld-label"></td>
                <td class="fld-value">
                    <?php if (!$isDispatched): ?>
                    <button type="button" class="btn btn-primary" onclick="saveAllocation()">Save Allocation</button>
                    <?php if ($id > 0): ?>
                    <button type="button" class="btn btn-success" onclick="dispatchAllocation()">Dispatch</button>
                    <?php endif; ?>
                    <?php endif; ?>
                    <a href="<?php echo ADMINURL; ?>/stock-allocation/" class="btn btn-secondary">Back</a>
                </td>
            </tr>
        </table>

        <input type="hidden" name="allocationID" value="<?php echo $id; ?>">
    </form>
</div>

<script>
var ADMINURL = ADMINURL || '<?php echo ADMINURL; ?>';
var MODURL = MODURL || '<?php echo ADMINURL; ?>/mod/stock-allocation/';
var MODINCURL = MODINCURL || '<?php echo ADMINURL; ?>/mod/stock-allocation/x-stock-allocation.inc.php';
var PAGETYPE = PAGETYPE || '<?php echo $TPL->pageType ?? "add"; ?>';
var itemIndex = <?php echo count($items); ?>;
var productsCache = [];

// Load products when warehouse changes
document.querySelector('[name="warehouseID"]')?.addEventListener('change', function() {
    loadWarehouseProducts(this.value);
});

// Load products on page load if warehouse is selected
<?php if (!empty($D["warehouseID"])): ?>
loadWarehouseProducts(<?php echo $D["warehouseID"]; ?>);
<?php endif; ?>

function loadWarehouseProducts(warehouseID) {
    if (!warehouseID) {
        document.getElementById('productSelect').innerHTML = '<option value="">-- Select warehouse first --</option>';
        return;
    }

    document.getElementById('productSelect').innerHTML = '<option value="">Loading products...</option>';

    $.ajax({
        url: MODURL + 'x-stock-allocation.inc.php',
        type: 'POST',
        data: { xAction: 'GET_PRODUCTS', warehouseID: warehouseID },
        dataType: 'json',
        success: function(res) {
            console.log('Products response:', res);
            console.log('Products count:', res.products ? res.products.length : 'no products');

            if (!res.products || res.products.length === 0) {
                document.getElementById('productSelect').innerHTML = '<option value="">No products found (err=' + res.err + ', msg=' + (res.msg || 'none') + ')</option>';
                return;
            }

            productsCache = res.products || [];
            var html = '<option value="">-- Select product --</option>';
            productsCache.forEach(function(p) {
                html += '<option value="' + p.productID + '" data-stock="' + p.availableQty + '" data-unit="' + (p.unit || 'Unit') + '" data-cost="' + (p.costPrice || 0) + '" data-name="' + p.productName + '" data-sku="' + p.productSKU + '">'
                      + p.productName + ' (' + p.productSKU + ') - Avl: ' + p.availableQty + '</option>';
            });
            document.getElementById('productSelect').innerHTML = html;
        },
        error: function(xhr, status, error) {
            console.error('Error loading products:', xhr.status, error);
            console.error('Response:', xhr.responseText);
            document.getElementById('productSelect').innerHTML = '<option value="">Error: ' + error + ' (' + xhr.status + ')</option>';
        }
    });
}

function addItem() {
    var select = document.getElementById('productSelect');
    var qty = parseFloat(document.getElementById('addQty').value) || 0;

    if (!select.value || qty <= 0) {
        alert('Please select a product and enter quantity');
        return;
    }

    var option = select.options[select.selectedIndex];
    var available = parseFloat(option.dataset.stock) || 0;

    if (qty > available) {
        alert('Quantity exceeds available stock (' + available + ')');
        return;
    }

    var productID = select.value;
    var productName = option.dataset.name;
    var productSKU = option.dataset.sku;
    var unit = option.dataset.unit;
    var unitCost = parseFloat(option.dataset.cost) || 0;
    var total = qty * unitCost;

    var html = '<tr>' +
        '<td style="text-align: center;">' + (document.querySelectorAll('#itemsBody tr').length + 1) + '</td>' +
        '<td><strong>' + productName + '</strong><br><small class="stock-info">' + productSKU + '</small>' +
        '<input type="hidden" name="items[' + itemIndex + '][productID]" value="' + productID + '"></td>' +
        '<td style="text-align: center;">' + available + '</td>' +
        '<td><input type="number" name="items[' + itemIndex + '][qty]" value="' + qty + '" class="qty-input" step="0.001" onchange="updateTotals()"></td>' +
        '<td style="text-align: center;">' + unit + '</td>' +
        '<td><input type="number" name="items[' + itemIndex + '][unitCost]" value="' + unitCost.toFixed(2) + '" class="cost-input" step="0.01" onchange="updateTotals()"></td>' +
        '<td style="text-align: right;" class="item-total">₹' + total.toFixed(2) + '</td>' +
        '<td><button type="button" class="btn-remove" onclick="removeItem(this)">&times;</button></td>' +
        '</tr>';

    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', html);
    itemIndex++;

    // Reset
    select.value = '';
    document.getElementById('addQty').value = '';
    updateTotals();
}

function removeItem(btn) {
    btn.closest('tr').remove();
    renumberItems();
    updateTotals();
}

function renumberItems() {
    var rows = document.querySelectorAll('#itemsBody tr');
    rows.forEach(function(row, idx) {
        row.querySelector('td:first-child').textContent = idx + 1;
    });
}

function updateTotals() {
    var total = 0;
    document.querySelectorAll('#itemsBody tr').forEach(function(row) {
        var qty = parseFloat(row.querySelector('.qty-input')?.value) || 0;
        var cost = parseFloat(row.querySelector('.cost-input')?.value) || 0;
        var itemTotal = qty * cost;
        row.querySelector('.item-total').textContent = '₹' + itemTotal.toFixed(2);
        total += itemTotal;
    });
    document.getElementById('grandTotal').textContent = '₹' + total.toFixed(2);
}

function saveAllocation() {
    var formData = new FormData(document.getElementById('frmAddEdit'));
    formData.append('xAction', <?php echo $id > 0 ? "'UPDATE'" : "'ADD'"; ?>);

    $.ajax({
        url: MODURL + 'x-stock-allocation.inc.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(res) {
            if (res.err == 0) {
                alert('Allocation saved successfully!');
                if (res.allocationID) {
                    location.href = ADMINURL + '/stock-allocation-edit/?id=' + res.allocationID;
                } else {
                    location.reload();
                }
            } else {
                alert(res.errMsg || res.msg || 'Failed to save allocation');
            }
        },
        error: function(xhr, status, error) {
            console.error('Save error:', error);
            alert('Error saving allocation');
        }
    });
}

function dispatchAllocation() {
    if (!confirm('Are you sure you want to dispatch this allocation? Items will be marked as in-transit.')) return;

    var formData = new FormData(document.getElementById('frmAddEdit'));
    formData.append('xAction', 'DISPATCH');

    $.ajax({
        url: MODURL + 'x-stock-allocation.inc.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(res) {
            if (res.err == 0) {
                alert('Allocation dispatched successfully!');
                location.reload();
            } else {
                alert(res.errMsg || res.msg || 'Failed to dispatch');
            }
        },
        error: function(xhr, status, error) {
            console.error('Dispatch error:', error);
            alert('Error dispatching allocation');
        }
    });
}
</script>
