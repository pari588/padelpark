<?php
require_once("x-site-grn.inc.php");

$id = 0;
$D = array();
$items = array();

if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $D = getSiteGrnDetails($id);

    if (!$D) {
        echo '<div class="wrap-right">' . getPageNav() . '<div class="wrap-data"><div class="alert alert-danger">GRN not found</div></div></div>';
        return;
    }
    $items = $D["items"] ?? array();
}

$isAccepted = ($D["grnStatus"] ?? "") === "Accepted";

// Check if creating from allocation
$allocationID = intval($_GET["allocationID"] ?? 0);
$allocationData = null;
$allocationItems = array();
if ($allocationID && !$id) {
    // Load allocation data
    $DB->vals = array($allocationID);
    $DB->types = "i";
    $DB->sql = "SELECT a.*, p.projectID, p.projectNo, p.projectName, p.siteCity
                FROM " . $DB->pre . "stock_allocation a
                LEFT JOIN " . $DB->pre . "sky_padel_project p ON a.projectID = p.projectID
                WHERE a.allocationID=? AND a.allocationType='Dispatched'";
    $allocationData = $DB->dbRow();

    if ($allocationData) {
        $D["projectID"] = $allocationData["projectID"];
        $D["allocationID"] = $allocationID;
        $D["grnType"] = "From-Warehouse";

        // Get allocation items
        $DB->vals = array($allocationID);
        $DB->types = "i";
        $DB->sql = "SELECT * FROM " . $DB->pre . "stock_allocation_item WHERE allocationID=? AND status=1";
        $DB->dbRows();
        $allocationItems = $DB->rows;
    }
}

// Get dropdowns
$projectOpt = getProjectDropdown($D["projectID"] ?? 0);

$arrForm = array(
    array("type" => "text", "name" => "grnNo", "value" => $D["grnNo"] ?? "(Auto-generated)", "title" => "GRN No", "params" => array("readonly" => "readonly")),
    array("type" => "select", "name" => "projectID", "value" => $projectOpt, "title" => "Project *", "validate" => "req", "params" => ($isAccepted || $allocationID) ? array("disabled" => "disabled") : array()),
    array("type" => "date", "name" => "grnDate", "value" => $D["grnDate"] ?? date("Y-m-d"), "title" => "GRN Date", "params" => $isAccepted ? array("disabled" => "disabled") : array()),
    array("type" => "select", "name" => "grnType", "value" => getGrnTypeDropdown($D["grnType"] ?? "From-Warehouse"), "title" => "GRN Type", "params" => ($isAccepted || $allocationID) ? array("disabled" => "disabled") : array()),
    array("type" => "text", "name" => "vehicleNo", "value" => $D["vehicleNo"] ?? ($allocationData["vehicleNo"] ?? ""), "title" => "Vehicle No"),
    array("type" => "text", "name" => "transporterName", "value" => $D["transporterName"] ?? "", "title" => "Transporter Name"),
    array("type" => "text", "name" => "lrNumber", "value" => $D["lrNumber"] ?? "", "title" => "LR/Challan Number"),
    array("type" => "text", "name" => "receiverName", "value" => $D["receiverName"] ?? ($_SESSION["mxAdminName"] ?? ""), "title" => "Received By"),
    array("type" => "textarea", "name" => "notes", "value" => $D["notes"] ?? "", "title" => "Notes", "params" => array("rows" => 2))
);

$MXFRM = new mxForm();

// Helper function for GRN type dropdown
function getGrnTypeDropdown($selected = "From-Warehouse")
{
    $types = array(
        "From-Warehouse" => "From Warehouse",
        "Direct-Purchase" => "Direct Purchase",
        "Return" => "Return"
    );

    $opt = '<option value="">Select GRN Type</option>';
    foreach ($types as $value => $label) {
        $sel = ($selected == $value) ? ' selected="selected"' : '';
        $opt .= '<option value="' . $value . '"' . $sel . '>' . $label . '</option>';
    }

    return $opt;
}

// Helper function for project dropdown
function getProjectDropdown($selectedID = 0)
{
    global $DB;

    $DB->vals = array(1);
    $DB->types = "i";
    $DB->sql = "SELECT projectID, projectNo, projectName, clientName, siteCity
                FROM " . $DB->pre . "sky_padel_project
                WHERE status=?
                ORDER BY projectNo DESC";
    $DB->dbRows();

    $opt = '<option value="">Select Project</option>';
    foreach ($DB->rows as $p) {
        $sel = ($selectedID == $p["projectID"]) ? ' selected="selected"' : '';
        $opt .= '<option value="' . $p["projectID"] . '"' . $sel . '>'
              . htmlspecialchars($p["projectNo"] . ' - ' . $p["projectName"] . ' (' . $p["siteCity"] . ')')
              . '</option>';
    }

    return $opt;
}
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
.condition-select { width: 100px; }
.total-row { background: #f8f9fa; font-weight: bold; }
.allocation-info { background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; margin: 15px; }
</style>

<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post">
        <?php if ($isAccepted): ?>
            <div class="alert alert-success" style="margin: 15px; padding: 15px; background: #d4edda; border-left: 4px solid #28a745;">
                <strong>Accepted</strong> - This GRN was accepted on <?php echo date("d M Y H:i", strtotime($D["acceptedDate"])); ?>. Goods have been confirmed received at site.
            </div>
        <?php endif; ?>

        <?php if ($allocationData): ?>
            <div class="allocation-info">
                <strong>Creating GRN from Allocation:</strong> <?php echo htmlspecialchars($allocationData["allocationNo"]); ?><br>
                <small>Vehicle: <?php echo htmlspecialchars($allocationData["vehicleNo"] ?: "-"); ?> | Driver: <?php echo htmlspecialchars($allocationData["driverName"] ?: "-"); ?></small>
            </div>
        <?php endif; ?>

        <ul class="tbl-form"><?php echo $MXFRM->getForm($arrForm); ?></ul>

        <!-- Items Section -->
        <table width="100%" border="0" cellspacing="0" cellpadding="5" class="tbl-form">
            <tr>
                <td class="fld-label" style="vertical-align: top;">Items Received</td>
                <td class="fld-value">
                    <table class="items-table" id="itemsTable">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="25%">Product</th>
                                <th width="8%">Expected</th>
                                <th width="8%">Received</th>
                                <th width="8%">Accepted</th>
                                <th width="8%">Rejected</th>
                                <th width="8%">Condition</th>
                                <th width="10%">Unit Cost</th>
                                <th width="10%">Total</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <?php
                            // Use allocation items if creating from allocation
                            $displayItems = !empty($items) ? $items : array();
                            if (empty($displayItems) && !empty($allocationItems)) {
                                foreach ($allocationItems as $ai) {
                                    $displayItems[] = array(
                                        "allocationItemID" => $ai["itemID"],
                                        "productID" => $ai["productID"],
                                        "productName" => $ai["productName"],
                                        "productSKU" => $ai["productSKU"],
                                        "unit" => $ai["unit"],
                                        "expectedQty" => $ai["dispatchedQty"],
                                        "receivedQty" => $ai["dispatchedQty"],
                                        "acceptedQty" => $ai["dispatchedQty"],
                                        "rejectedQty" => 0,
                                        "unitCost" => $ai["unitCost"],
                                        "itemCondition" => "Good"
                                    );
                                }
                            }
                            ?>
                            <?php if (!empty($displayItems)): ?>
                                <?php foreach ($displayItems as $i => $item): ?>
                                <tr>
                                    <td style="text-align: center;"><?php echo $i + 1; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item["productName"]); ?></strong><br>
                                        <small><?php echo htmlspecialchars($item["productSKU"]); ?></small>
                                        <input type="hidden" name="items[<?php echo $i; ?>][productID]" value="<?php echo $item["productID"]; ?>">
                                        <input type="hidden" name="items[<?php echo $i; ?>][productName]" value="<?php echo htmlspecialchars($item["productName"]); ?>">
                                        <input type="hidden" name="items[<?php echo $i; ?>][productSKU]" value="<?php echo htmlspecialchars($item["productSKU"]); ?>">
                                        <input type="hidden" name="items[<?php echo $i; ?>][unit]" value="<?php echo htmlspecialchars($item["unit"]); ?>">
                                        <input type="hidden" name="items[<?php echo $i; ?>][allocationItemID]" value="<?php echo $item["allocationItemID"] ?? 0; ?>">
                                    </td>
                                    <td style="text-align: center;"><?php echo number_format($item["expectedQty"], 2); ?>
                                        <input type="hidden" name="items[<?php echo $i; ?>][expectedQty]" value="<?php echo $item["expectedQty"]; ?>">
                                    </td>
                                    <td><input type="number" name="items[<?php echo $i; ?>][receivedQty]" value="<?php echo $item["receivedQty"]; ?>" class="qty-input" step="0.001" onchange="updateItemTotals(this)" <?php echo $isAccepted ? 'disabled' : ''; ?>></td>
                                    <td><input type="number" name="items[<?php echo $i; ?>][acceptedQty]" value="<?php echo $item["acceptedQty"]; ?>" class="qty-input" step="0.001" onchange="updateItemTotals(this)" <?php echo $isAccepted ? 'disabled' : ''; ?>></td>
                                    <td><input type="number" name="items[<?php echo $i; ?>][rejectedQty]" value="<?php echo $item["rejectedQty"] ?? 0; ?>" class="qty-input" step="0.001" <?php echo $isAccepted ? 'disabled' : ''; ?>></td>
                                    <td>
                                        <select name="items[<?php echo $i; ?>][itemCondition]" class="condition-select" <?php echo $isAccepted ? 'disabled' : ''; ?>>
                                            <option value="Good" <?php echo ($item["itemCondition"] ?? "") == "Good" ? "selected" : ""; ?>>Good</option>
                                            <option value="Damaged" <?php echo ($item["itemCondition"] ?? "") == "Damaged" ? "selected" : ""; ?>>Damaged</option>
                                            <option value="Partial" <?php echo ($item["itemCondition"] ?? "") == "Partial" ? "selected" : ""; ?>>Partial</option>
                                        </select>
                                    </td>
                                    <td><input type="number" name="items[<?php echo $i; ?>][unitCost]" value="<?php echo $item["unitCost"]; ?>" class="cost-input" step="0.01" onchange="updateItemTotals(this)" <?php echo $isAccepted ? 'disabled' : ''; ?>></td>
                                    <td style="text-align: right;" class="item-total">₹<?php echo number_format(($item["acceptedQty"] ?? $item["receivedQty"]) * $item["unitCost"], 2); ?></td>
                                    <td>
                                        <?php if (!$isAccepted && !$allocationID): ?>
                                        <button type="button" class="btn-remove" onclick="removeItem(this)">&times;</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="8" style="text-align: right;">Total Value:</td>
                                <td style="text-align: right;" id="grandTotal">₹<?php echo number_format($D["totalValue"] ?? 0, 2); ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>

                    <?php if (!$isAccepted && !$allocationID): ?>
                    <div style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 4px;">
                        <strong>Add Product:</strong>
                        <div style="display: flex; gap: 10px; margin-top: 10px;">
                            <select id="productSelect" class="product-select" style="flex: 2;">
                                <option value="">-- Select product --</option>
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
                    <?php if (!$isAccepted): ?>
                    <button type="button" class="btn btn-primary" onclick="saveGrn()">Save GRN</button>
                    <?php if ($id > 0): ?>
                    <button type="button" class="btn btn-success" onclick="acceptGrn()">Accept GRN</button>
                    <?php endif; ?>
                    <?php endif; ?>
                    <a href="<?php echo ADMINURL; ?>/site-grn/" class="btn btn-secondary">Back</a>
                </td>
            </tr>
        </table>

        <input type="hidden" name="grnID" value="<?php echo $id; ?>">
        <input type="hidden" name="allocationID" value="<?php echo $allocationID ?: ($D["allocationID"] ?? 0); ?>">
    </form>
</div>

<script>
var itemIndex = <?php echo count($displayItems ?? array()); ?>;

// Load products on page load
loadProducts();

function loadProducts() {
    $.mxajax({
        url: '<?php echo ADMINURL; ?>/mod/retail-product/x-retail-product.inc.php',
        data: { xAction: 'GET_ALL_PRODUCTS' }
    }).done(function(res) {
        if (res.products) {
            var html = '<option value="">-- Select product --</option>';
            res.products.forEach(function(p) {
                html += '<option value="' + p.productID + '" data-name="' + p.productName + '" data-sku="' + (p.productSKU || '') + '" data-unit="' + (p.unit || 'Unit') + '" data-cost="' + (p.costPrice || 0) + '">'
                      + p.productName + ' (' + (p.productSKU || 'N/A') + ')</option>';
            });
            document.getElementById('productSelect').innerHTML = html;
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
    var productID = select.value;
    var productName = option.dataset.name;
    var productSKU = option.dataset.sku;
    var unit = option.dataset.unit;
    var unitCost = parseFloat(option.dataset.cost) || 0;
    var total = qty * unitCost;

    var html = '<tr>' +
        '<td style="text-align: center;">' + (document.querySelectorAll('#itemsBody tr').length + 1) + '</td>' +
        '<td><strong>' + productName + '</strong><br><small>' + productSKU + '</small>' +
        '<input type="hidden" name="items[' + itemIndex + '][productID]" value="' + productID + '">' +
        '<input type="hidden" name="items[' + itemIndex + '][productName]" value="' + productName + '">' +
        '<input type="hidden" name="items[' + itemIndex + '][productSKU]" value="' + productSKU + '">' +
        '<input type="hidden" name="items[' + itemIndex + '][unit]" value="' + unit + '">' +
        '<input type="hidden" name="items[' + itemIndex + '][allocationItemID]" value="0"></td>' +
        '<td style="text-align: center;">' + qty.toFixed(2) + '<input type="hidden" name="items[' + itemIndex + '][expectedQty]" value="' + qty + '"></td>' +
        '<td><input type="number" name="items[' + itemIndex + '][receivedQty]" value="' + qty + '" class="qty-input" step="0.001" onchange="updateItemTotals(this)"></td>' +
        '<td><input type="number" name="items[' + itemIndex + '][acceptedQty]" value="' + qty + '" class="qty-input" step="0.001" onchange="updateItemTotals(this)"></td>' +
        '<td><input type="number" name="items[' + itemIndex + '][rejectedQty]" value="0" class="qty-input" step="0.001"></td>' +
        '<td><select name="items[' + itemIndex + '][itemCondition]" class="condition-select">' +
        '<option value="Good">Good</option><option value="Damaged">Damaged</option><option value="Partial">Partial</option></select></td>' +
        '<td><input type="number" name="items[' + itemIndex + '][unitCost]" value="' + unitCost.toFixed(2) + '" class="cost-input" step="0.01" onchange="updateItemTotals(this)"></td>' +
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

function updateItemTotals(input) {
    var row = input.closest('tr');
    var qty = parseFloat(row.querySelector('[name*="[acceptedQty]"]')?.value) || parseFloat(row.querySelector('[name*="[receivedQty]"]')?.value) || 0;
    var cost = parseFloat(row.querySelector('[name*="[unitCost]"]')?.value) || 0;
    var itemTotal = qty * cost;
    row.querySelector('.item-total').textContent = '₹' + itemTotal.toFixed(2);
    updateTotals();
}

function updateTotals() {
    var total = 0;
    document.querySelectorAll('#itemsBody tr').forEach(function(row) {
        var qty = parseFloat(row.querySelector('[name*="[acceptedQty]"]')?.value) || parseFloat(row.querySelector('[name*="[receivedQty]"]')?.value) || 0;
        var cost = parseFloat(row.querySelector('.cost-input')?.value) || 0;
        total += qty * cost;
    });
    document.getElementById('grandTotal').textContent = '₹' + total.toFixed(2);
}

function saveGrn() {
    var formData = new FormData(document.getElementById('frmAddEdit'));
    formData.append('xAction', <?php echo $id > 0 ? "'UPDATE'" : "'ADD'"; ?>);

    $.ajax({
        url: MODURL + 'x-site-grn.inc.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(res) {
            if (res.err == 0) {
                alert('GRN saved successfully!');
                if (res.grnID) {
                    location.href = ADMINURL + '/site-grn-edit/?id=' + res.grnID;
                } else {
                    location.reload();
                }
            } else {
                alert(res.errMsg || res.msg || 'Failed to save GRN');
            }
        },
        error: function() {
            alert('Error saving GRN');
        }
    });
}

function acceptGrn() {
    if (!confirm('Are you sure you want to accept this GRN? This will confirm receipt of goods at the project site.')) return;

    $.mxajax({
        url: MODURL + 'x-site-grn.inc.php',
        data: { xAction: 'ACCEPT', grnID: <?php echo $id; ?> }
    }).done(function(res) {
        if (res.err == 0) {
            alert('GRN accepted successfully!');
            location.reload();
        } else {
            alert(res.errMsg || res.msg || 'Failed to accept GRN');
        }
    });
}
</script>
