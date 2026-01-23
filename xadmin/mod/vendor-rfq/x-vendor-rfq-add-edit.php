<?php
/**
 * Vendor RFQ - Add/Edit View (xadmin standard layout)
 */

// Note: x-vendor-rfq.inc.php is already included by xadmin framework

// Define getQuoteStatusBadge if not already defined
if (!function_exists('getQuoteStatusBadge')) {
    function getQuoteStatusBadge($status) {
        $badges = array("Draft" => "badge-secondary", "Submitted" => "badge-info", "Under Review" => "badge-warning", "Shortlisted" => "badge-primary", "Accepted" => "badge-success", "Rejected" => "badge-danger");
        $class = isset($badges[$status]) ? $badges[$status] : "badge-secondary";
        return '<span class="badge ' . $class . '">' . htmlentities($status) . '</span>';
    }
}

$id = 0;
$D = array();
$items = array();

if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["rfqID"] ?? $_GET["id"] ?? 0);
    $DB->sql = "SELECT r.*, c.categoryName
                FROM mx_vendor_rfq r
                LEFT JOIN mx_vendor_category c ON r.category = c.categoryID
                WHERE r.rfqID = ?";
    $DB->vals = array($id);
    $DB->types = "i";
    $D = $DB->dbRow();

    if ($D) {
        // Get items
        $DB->sql = "SELECT * FROM mx_vendor_rfq_item WHERE rfqID = ? AND status = 1 ORDER BY sortOrder, itemID";
        $DB->vals = array($id);
        $DB->types = "i";
        $items = $DB->dbRows();
    }
}

$isEdit = $id > 0 && !empty($D);
$canEdit = !$isEdit || ($D["rfqStatus"] ?? "") == "Draft";

// Get vendor categories for dropdown
$whrArr = array("sql" => "status=?", "types" => "i", "vals" => array(1));
$categoryOpt = getTableDD(["table" => $DB->pre . "vendor_category", "key" => "categoryID", "val" => "categoryName", "selected" => ($D["category"] ?? ""), "where" => $whrArr, "order" => "categoryName ASC"]);

// RFQ Type options
$rfqTypes = array("Goods" => "Goods", "Services" => "Services", "Both" => "Both (Goods & Services)");
$rfqTypeOpt = "";
foreach ($rfqTypes as $val => $txt) {
    $sel = (($D["rfqType"] ?? "Goods") == $val) ? ' selected="selected"' : '';
    $rfqTypeOpt .= '<option value="' . $val . '"' . $sel . '>' . $txt . '</option>';
}

// Visibility options
$visibilityOpt = "";
$visibilityOpt .= '<option value="1"' . (($D["isPublic"] ?? 1) == 1 ? ' selected' : '') . '>Public - All approved vendors</option>';
$visibilityOpt .= '<option value="0"' . (($D["isPublic"] ?? 1) == 0 ? ' selected' : '') . '>Private - Invite specific vendors</option>';

// Payment terms options
$paymentTerms = array("" => "-- Select --", "Advance" => "100% Advance", "50-50" => "50% Advance, 50% on Delivery", "30-70" => "30% Advance, 70% on Delivery", "Net15" => "Net 15 Days", "Net30" => "Net 30 Days", "Net45" => "Net 45 Days", "Net60" => "Net 60 Days", "COD" => "Cash on Delivery");
$paymentOpt = "";
foreach ($paymentTerms as $val => $txt) {
    $sel = (($D["paymentTerms"] ?? "") == $val) ? ' selected="selected"' : '';
    $paymentOpt .= '<option value="' . $val . '"' . $sel . '>' . $txt . '</option>';
}

// Get approved vendors for invitation
$DB->sql = "SELECT vendorID, legalName, vendorCode FROM mx_vendor_onboarding WHERE approvalStatus = 'Approved' AND status = 1 ORDER BY legalName";
$DB->vals = array();
$DB->types = "";
$approvedVendors = $DB->dbRows();
$invitedList = explode(",", $D["invitedVendors"] ?? "");
$invitedVendorsOpt = "";
foreach ($approvedVendors as $vendor) {
    $sel = in_array($vendor["vendorID"], $invitedList) ? ' selected' : '';
    $invitedVendorsOpt .= '<option value="' . $vendor["vendorID"] . '"' . $sel . '>' . htmlentities($vendor["legalName"]) . ' (' . $vendor["vendorCode"] . ')</option>';
}

// Build form array
$arrForm = array(
    array("type" => "text", "name" => "rfqNumber", "value" => $D["rfqNumber"] ?? "Auto-generated", "title" => "RFQ Number", "params" => array("readonly" => "readonly")),
    array("type" => "select", "name" => "rfqType", "value" => $rfqTypeOpt, "title" => "RFQ Type *", "validate" => "required"),
    array("type" => "text", "name" => "title", "value" => $D["title"] ?? "", "title" => "Title *", "validate" => "required"),
    array("type" => "textarea", "name" => "description", "value" => $D["description"] ?? "", "title" => "Description", "params" => array("rows" => 3)),
    array("type" => "select", "name" => "category", "value" => $categoryOpt, "title" => "Category"),
    array("type" => "text", "name" => "submissionDeadline", "value" => $D["submissionDeadline"] ? date("Y-m-d\TH:i", strtotime($D["submissionDeadline"])) : "", "title" => "Submission Deadline *", "params" => array("type" => "datetime-local"), "validate" => "required"),
    array("type" => "text", "name" => "expectedDeliveryDate", "value" => $D["expectedDeliveryDate"] ?? "", "title" => "Expected Delivery Date", "params" => array("type" => "date")),
    array("type" => "select", "name" => "isPublic", "value" => $visibilityOpt, "title" => "Visibility"),
    array("type" => "select", "name" => "invitedVendors[]", "value" => $invitedVendorsOpt, "title" => "Invite Vendors", "params" => array("multiple" => "multiple", "class" => "select2-multiple")),
    array("type" => "textarea", "name" => "deliveryLocation", "value" => $D["deliveryLocation"] ?? "", "title" => "Delivery Location", "params" => array("rows" => 2)),
    array("type" => "select", "name" => "paymentTerms", "value" => $paymentOpt, "title" => "Payment Terms"),
    array("type" => "textarea", "name" => "specialRequirements", "value" => $D["specialRequirements"] ?? "", "title" => "Special Requirements", "params" => array("rows" => 3)),
    array("type" => "textarea", "name" => "termsAndConditions", "value" => $D["termsAndConditions"] ?? "", "title" => "Terms & Conditions", "params" => array("rows" => 3)),
    array("type" => "file", "name" => "attachment", "value" => $D["attachmentPath"] ?? "", "title" => "Attachment")
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>

    <?php if ($isEdit): ?>
    <div class="wrap-data" style="margin-bottom:10px; padding:10px; background:#f8f9fa; border-radius:4px;">
        <strong>Status:</strong> <?php echo getRFQStatusBadge($D["rfqStatus"]); ?>
        <?php if ($D["publishDate"]): ?>
            &nbsp;&nbsp;<strong>Published:</strong> <?php echo date("d-M-Y H:i", strtotime($D["publishDate"])); ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="rfqID" value="<?php echo $id; ?>">
        <div class="wrap-form f50">
            <ul class="tbl-form"><?php echo $MXFRM->getForm($arrForm); ?></ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>

    <?php if ($isEdit): ?>
    <!-- RFQ Line Items Section -->
    <div class="wrap-data" style="margin-top:20px;">
        <div class="wrap-hdr" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h4 style="margin:0;"><i class="fa fa-list"></i> RFQ Line Items (<?php echo count($items); ?>)</h4>
            <?php if ($canEdit): ?>
            <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#addItemModal">
                <i class="fa fa-plus"></i> Add Item
            </button>
            <?php endif; ?>
        </div>

        <table class="table table-bordered table-striped">
            <thead class="thead-light">
                <tr>
                    <th style="width:50px">#</th>
                    <th>Description</th>
                    <th style="width:80px">Unit</th>
                    <th style="width:80px">Qty</th>
                    <th style="width:100px">Est. Rate</th>
                    <th style="width:100px">Est. Amount</th>
                    <?php if ($canEdit): ?><th style="width:60px">Action</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (count($items) > 0): ?>
                    <?php
                    $totalEstAmount = 0;
                    foreach ($items as $idx => $item):
                        $estAmount = $item["quantity"] * ($item["estimatedRate"] ?? 0);
                        $totalEstAmount += $estAmount;
                    ?>
                    <tr>
                        <td><?php echo $idx + 1; ?></td>
                        <td>
                            <strong><?php echo htmlentities($item["itemDescription"]); ?></strong>
                            <?php if ($item["specifications"]): ?>
                                <br><small class="text-muted"><?php echo nl2br(htmlentities($item["specifications"])); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlentities($item["unit"]); ?></td>
                        <td class="text-right"><?php echo number_format($item["quantity"], 2); ?></td>
                        <td class="text-right"><?php echo $item["estimatedRate"] ? number_format($item["estimatedRate"], 2) : "-"; ?></td>
                        <td class="text-right"><?php echo $estAmount > 0 ? number_format($estAmount, 2) : "-"; ?></td>
                        <?php if ($canEdit): ?>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteItem(<?php echo $item['itemID']; ?>)">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="table-info">
                        <td colspan="5" class="text-right"><strong>Total Estimated:</strong></td>
                        <td class="text-right"><strong><?php echo number_format($totalEstAmount, 2); ?></strong></td>
                        <?php if ($canEdit): ?><td></td><?php endif; ?>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="<?php echo $canEdit ? 7 : 6; ?>" class="text-center text-muted">
                            No items added yet. Click "Add Item" to add line items.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (in_array($D["rfqStatus"], array("Published", "Closed", "Awarded"))): ?>
    <!-- Vendor Quotes Section -->
    <div class="wrap-data" style="margin-top:20px;">
        <div class="wrap-hdr" style="margin-bottom:15px;">
            <h4 style="margin:0;"><i class="fa fa-file-text-o"></i> Vendor Quotes</h4>
        </div>
        <?php
        $DB->sql = "SELECT q.*, v.legalName, v.vendorCode
                    FROM mx_vendor_quote q
                    JOIN mx_vendor_onboarding v ON q.vendorID = v.vendorID
                    WHERE q.rfqID = ? AND q.status = 1
                    ORDER BY q.totalAmount ASC";
        $DB->vals = array($id);
        $DB->types = "i";
        $quotes = $DB->dbRows();

        if (count($quotes) > 0):
        ?>
        <table class="table table-bordered table-striped">
            <thead class="thead-light">
                <tr>
                    <th>Vendor</th>
                    <th>Quote #</th>
                    <th>Submitted</th>
                    <th class="text-right">Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quotes as $quote): ?>
                <tr class="<?php echo $quote['quoteStatus'] == 'Accepted' ? 'table-success' : ''; ?>">
                    <td>
                        <strong><?php echo htmlentities($quote["legalName"]); ?></strong>
                        <br><small><?php echo $quote["vendorCode"]; ?></small>
                    </td>
                    <td><?php echo htmlentities($quote["quoteNumber"]); ?></td>
                    <td><?php echo $quote["submittedAt"] ? date("d-M-Y H:i", strtotime($quote["submittedAt"])) : "-"; ?></td>
                    <td class="text-right"><strong><?php echo number_format($quote["totalAmount"], 2); ?></strong></td>
                    <td><?php echo getQuoteStatusBadge($quote["quoteStatus"]); ?></td>
                    <td>
                        <a href="<?php echo ADMINURL; ?>vendor-quote-edit?quoteID=<?php echo $quote['quoteID']; ?>" class="btn btn-sm btn-info">
                            <i class="fa fa-eye"></i>
                        </a>
                        <?php if ($D["rfqStatus"] == "Closed" && $quote["quoteStatus"] != "Accepted"): ?>
                        <button class="btn btn-sm btn-success" onclick="awardToVendor(<?php echo $quote['quoteID']; ?>, <?php echo $quote['vendorID']; ?>)">
                            <i class="fa fa-trophy"></i>
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-muted">No quotes received yet.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Add Item Modal -->
    <?php if ($canEdit): ?>
    <div class="modal fade" id="addItemModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="frmAddItem">
                    <input type="hidden" name="xAction" value="ADD_ITEM">
                    <input type="hidden" name="rfqID" value="<?php echo $id; ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Add RFQ Item</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Item Description <span class="text-danger">*</span></label>
                            <input type="text" name="itemDescription" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Specifications</label>
                            <textarea name="specifications" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Unit *</label>
                                    <select name="unit" class="form-control" required>
                                        <option value="Nos">Nos</option>
                                        <option value="Kg">Kg</option>
                                        <option value="Ltr">Ltr</option>
                                        <option value="Mtr">Mtr</option>
                                        <option value="Sqm">Sqm</option>
                                        <option value="Set">Set</option>
                                        <option value="Box">Box</option>
                                        <option value="Pack">Pack</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Quantity *</label>
                                    <input type="number" name="quantity" class="form-control" step="0.01" min="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Est. Rate</label>
                                    <input type="number" name="estimatedRate" class="form-control" step="0.01" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success"><i class="fa fa-plus"></i> Add Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    if ($.fn.select2) {
        $('.select2-multiple').select2({ width: '100%', placeholder: 'Select vendors' });
    }

    $('select[name="isPublic"]').change(function() {
        var inviteRow = $('select[name="invitedVendors[]"]').closest('li');
        if ($(this).val() == '0') {
            inviteRow.show();
        } else {
            inviteRow.hide();
        }
    }).trigger('change');

    $('#frmAddItem').submit(function(e) {
        e.preventDefault();
        $.post('<?php echo ADMINURL; ?>vendor-rfq-edit', $(this).serialize() + '&xToken=<?php echo $_SESSION[SITEURL][CSRF_TOKEN]; ?>', function(response) {
            alert(response.msg);
            if (response.err == 0) location.reload();
        }, 'json');
    });
});

function deleteItem(itemID) {
    if (confirm('Delete this item?')) {
        $.post('<?php echo ADMINURL; ?>vendor-rfq-edit', {
            xAction: 'DELETE_ITEM',
            itemID: itemID,
            xToken: '<?php echo $_SESSION[SITEURL][CSRF_TOKEN]; ?>'
        }, function(response) {
            alert(response.msg);
            if (response.err == 0) location.reload();
        }, 'json');
    }
}

function awardToVendor(quoteID, vendorID) {
    if (confirm('Award this RFQ to the selected vendor?')) {
        $.post('<?php echo ADMINURL; ?>vendor-rfq-edit', {
            xAction: 'AWARD',
            rfqID: <?php echo $id; ?>,
            quoteID: quoteID,
            vendorID: vendorID,
            xToken: '<?php echo $_SESSION[SITEURL][CSRF_TOKEN]; ?>'
        }, function(response) {
            alert(response.msg);
            if (response.err == 0) location.reload();
        }, 'json');
    }
}
</script>
