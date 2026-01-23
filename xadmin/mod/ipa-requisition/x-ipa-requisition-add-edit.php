<?php
$id = 0;
$D = array();
$items = array();

if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT r.*, CONCAT(c.firstName, ' ', IFNULL(c.lastName,'')) as coachName
                FROM " . $DB->pre . "ipa_requisition r
                LEFT JOIN " . $DB->pre . "ipa_coach c ON r.coachID = c.coachID
                WHERE r.status=? AND r.requisitionID=?";
    $D = $DB->dbRow();

    // Get items with stock info
    if ($D) {
        $DB->vals = array($id);
        $DB->types = "i";
        $DB->sql = "SELECT ri.*, p.productSKU,
                           COALESCE((SELECT SUM(s.availableQty) FROM " . $DB->pre . "inventory_stock s WHERE s.productID = ri.productID), 0) as stockQty
                    FROM " . $DB->pre . "ipa_requisition_item ri
                    LEFT JOIN " . $DB->pre . "product p ON ri.productID = p.productID
                    WHERE ri.requisitionID=? AND ri.status=1";
        $DB->dbRows();
        $items = $DB->rows ?: array();
    }
}

// Build coach dropdown
$DB->sql = "SELECT coachID, CONCAT(firstName, ' ', IFNULL(lastName,'')) as coachName FROM " . $DB->pre . "ipa_coach WHERE status=1 ORDER BY firstName";
$coachRows = $DB->dbRows() ?: array();
$coachOpt = '<option value="">-- Select Coach --</option>';
$selCoach = $D["coachID"] ?? "";
foreach ($coachRows as $c) {
    $sel = ($selCoach == $c["coachID"]) ? ' selected="selected"' : '';
    $coachOpt .= '<option value="' . $c["coachID"] . '"' . $sel . '>' . htmlspecialchars($c["coachName"]) . '</option>';
}

// Get products from warehouse
$DB->sql = "SELECT p.productID, p.productName, p.productSKU, p.uom,
                   COALESCE((SELECT SUM(s.availableQty) FROM " . $DB->pre . "inventory_stock s WHERE s.productID = p.productID), 0) as stockQty
            FROM " . $DB->pre . "product p
            WHERE p.status=1 AND p.isStockable=1
            ORDER BY p.productName";
$products = $DB->dbRows() ?: array();

$canEdit = !$id || ($D["requisitionStatus"] ?? "") == "Draft";

$arrFormDetails = array(
    array("type" => "select", "name" => "coachID", "value" => $coachOpt, "title" => "Coach", "validate" => "required"),
    array("type" => "date", "name" => "requisitionDate", "value" => $D["requisitionDate"] ?? date("Y-m-d"), "title" => "Requisition Date"),
    array("type" => "date", "name" => "requiredByDate", "value" => $D["requiredByDate"] ?? "", "title" => "Required By Date"),
    array("type" => "textarea", "name" => "notes", "value" => $D["notes"] ?? "", "title" => "Notes", "params" => array("rows" => 2)),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post">
        <input type="hidden" name="requisitionID" value="<?php echo $id; ?>">

        <?php if ($id && !$canEdit): ?>
        <div class="wrap-form">
            <div style="background:#fef3c7; padding:12px 15px; border-left:4px solid #f59e0b; color:#92400e;">
                <i class="fa fa-info-circle"></i> This requisition cannot be edited (Status: <?php echo $D["requisitionStatus"]; ?>)
            </div>
        </div>
        <?php endif; ?>

        <?php if ($id && $D["requisitionStatus"] == "Rejected" && $D["rejectionReason"]): ?>
        <div class="wrap-form">
            <div style="background:#fee2e2; padding:12px 15px; border-left:4px solid #ef4444; color:#991b1b;">
                <strong>Rejection Reason:</strong> <?php echo htmlspecialchars($D["rejectionReason"]); ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="wrap-form">
            <h2 class="form-head"><?php echo $id ? "Edit Requisition: " . htmlspecialchars($D["requisitionNo"]) : "New Requisition"; ?></h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormDetails); ?>
            </ul>
        </div>

        <div class="wrap-form">
            <h2 class="form-head">Requisition Items</h2>
            <div style="padding:15px;">
                <table id="itemsTable" width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                    <thead>
                        <tr>
                            <th align="left">Product</th>
                            <th align="center" width="100">Stock</th>
                            <th align="center" width="80">Qty</th>
                            <th align="center" width="80">Unit</th>
                            <?php if ($canEdit): ?><th align="center" width="50"></th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <!-- Items loaded via JS -->
                    </tbody>
                </table>

                <?php if ($canEdit): ?>
                <div style="margin-top:10px;">
                    <button type="button" class="btn btn-default btn-sm" onclick="addItem()">
                        <i class="fa fa-plus"></i> Add Item
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($canEdit): ?>
        <div class="wrap-form">
            <ul class="tbl-form">
                <li class="frm-btn">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-primary" onclick="saveRequisition(false)">
                        <i class="fa fa-save"></i> Save as Draft
                    </button>
                    <button type="button" class="btn btn-success" onclick="saveRequisition(true)">
                        <i class="fa fa-paper-plane"></i> Save & Submit
                    </button>
                </li>
            </ul>
        </div>
        <?php endif; ?>
    </form>
</div>

<script>
var canEdit = <?php echo $canEdit ? "true" : "false"; ?>;
var existingItems = <?php echo json_encode($items); ?>;
var products = <?php echo json_encode($products); ?>;

$(document).ready(function() {
    if (existingItems.length > 0) {
        existingItems.forEach(function(item) {
            addItem(item);
        });
    } else if (canEdit) {
        addItem();
    }

    <?php if (!$canEdit): ?>
    $('#frmAddEdit select, #frmAddEdit input, #frmAddEdit textarea').prop('disabled', true);
    <?php endif; ?>
});

function getProductOptions(selectedID) {
    var html = '<option value="">-- Select Product --</option>';
    products.forEach(function(p) {
        var sel = (p.productID == selectedID) ? ' selected' : '';
        var stockInfo = ' [Stock: ' + parseFloat(p.stockQty).toFixed(0) + ' ' + p.uom + ']';
        html += '<option value="' + p.productID + '" data-uom="' + p.uom + '" data-stock="' + p.stockQty + '"' + sel + '>' + p.productName + stockInfo + '</option>';
    });
    return html;
}

function addItem(data) {
    data = data || {};
    var rowIndex = $('#itemsBody tr').length;

    var html = '<tr data-index="' + rowIndex + '">';
    html += '<td><select class="inp-fld product-select" name="productID_' + rowIndex + '" onchange="updateUnit(this)" style="width:100%;">' + getProductOptions(data.productID) + '</select></td>';
    html += '<td align="center" class="stock-cell">' + (data.stockQty ? parseFloat(data.stockQty).toFixed(0) : '-') + '</td>';
    html += '<td><input type="number" class="inp-fld qty-input" name="qty_' + rowIndex + '" value="' + (data.requestedQty || 1) + '" min="1" style="width:70px; text-align:center;"' + (canEdit ? '' : ' disabled') + '></td>';
    html += '<td align="center" class="unit-cell">' + (data.unit || 'Pcs') + '</td>';
    if (canEdit) {
        html += '<td align="center"><button type="button" class="btn btn-sm btn-danger" onclick="removeItem(this)"><i class="fa fa-times"></i></button></td>';
    }
    html += '</tr>';

    $('#itemsBody').append(html);

    if (!canEdit) {
        $('#itemsBody tr:last select').prop('disabled', true);
    }
}

function updateUnit(select) {
    var $row = $(select).closest('tr');
    var $option = $(select).find('option:selected');
    var uom = $option.data('uom') || 'Pcs';
    var stock = $option.data('stock') || 0;
    $row.find('.unit-cell').text(uom);
    $row.find('.stock-cell').text(parseFloat(stock).toFixed(0));
}

function removeItem(btn) {
    var rowCount = $('#itemsBody tr').length;
    if (rowCount > 1) {
        $(btn).closest('tr').remove();
    } else {
        alert('At least one item is required');
    }
}

function saveRequisition(submit) {
    var coachID = $('select[name="coachID"]').val();
    if (!coachID) {
        alert('Please select a coach');
        return;
    }

    var items = [];
    var hasItems = false;
    $('#itemsBody tr').each(function() {
        var $row = $(this);
        var productID = $row.find('.product-select').val();
        var qty = $row.find('.qty-input').val();
        if (productID && qty > 0) {
            items.push({productID: productID, requestedQty: qty});
            hasItems = true;
        }
    });

    if (!hasItems) {
        alert('Please add at least one item');
        return;
    }

    var requisitionID = $('input[name="requisitionID"]').val();

    $.ajax({
        url: '<?php echo ADMINURL; ?>/mod/ipa-requisition/x-ipa-requisition.inc.php',
        type: 'POST',
        data: {
            xAction: requisitionID > 0 ? 'UPDATE' : 'ADD',
            requisitionID: requisitionID,
            coachID: coachID,
            requisitionDate: $('input[name="requisitionDate"]').val(),
            requiredByDate: $('input[name="requiredByDate"]').val(),
            notes: $('textarea[name="notes"]').val(),
            items: JSON.stringify(items)
        },
        dataType: 'json',
        success: function(res) {
            if (res.err == 0) {
                if (submit) {
                    $.ajax({
                        url: '<?php echo ADMINURL; ?>/mod/ipa-requisition/x-ipa-requisition.inc.php',
                        type: 'POST',
                        data: {xAction: 'SUBMIT', requisitionID: res.id || requisitionID},
                        dataType: 'json',
                        success: function(res2) {
                            alert('Requisition submitted for approval');
                            window.location.href = '<?php echo ADMINURL; ?>/ipa-requisition-list/';
                        }
                    });
                } else {
                    alert(res.msg);
                    window.location.href = '<?php echo ADMINURL; ?>/ipa-requisition-list/';
                }
            } else {
                alert('Error: ' + res.msg);
            }
        },
        error: function(xhr) {
            console.log('Error:', xhr.responseText);
            alert('Request failed');
        }
    });
}
</script>
