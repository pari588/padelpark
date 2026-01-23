<?php
$id = 0;
$D = array();
$items = array();

if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT cn.*, w.warehouseName
                FROM `" . $DB->pre . $MXMOD["TBL"] . "` cn
                LEFT JOIN " . $DB->pre . "warehouse w ON cn.warehouseID = w.warehouseID
                WHERE cn.status=? AND cn.`" . $MXMOD["PK"] . "`=?";
    $D = $DB->dbRow();

    // Get items
    if (!empty($D)) {
        $DB->vals = array($id, 1);
        $DB->types = "ii";
        $DB->sql = "SELECT * FROM " . $DB->pre . "credit_note_item WHERE creditNoteID=? AND status=?";
        $items = $DB->dbRows();
    }
}

// Check if creating from invoice
$fromInvoice = false;
$invoiceData = null;
if (empty($D) && !empty($_GET["invoiceType"]) && !empty($_GET["invoiceID"])) {
    $fromInvoice = true;
}

$isEdit = !empty($D);
$isDraft = empty($D) || $D["creditNoteStatus"] == "Draft";
$MXFRM = new mxForm();

// Get warehouses for dropdown
$whrArr = array("sql" => "status=?", "types" => "i", "vals" => array(1));
$warehouseOpt = '<option value="">-- Select Warehouse --</option>' . getTableDD([
    "table" => $DB->pre . "warehouse",
    "key" => "warehouseID",
    "val" => "warehouseName",
    "selected" => ($D['warehouseID'] ?? 0),
    "where" => $whrArr
]);

// Get distributors for dropdown
$distributorOpt = '<option value="">-- Select Distributor --</option>' . getTableDD([
    "table" => $DB->pre . "distributor",
    "key" => "distributorID",
    "val" => "companyName",
    "selected" => ($D['entityType'] == "Distributor" ? $D['entityID'] : 0),
    "where" => $whrArr
]);

// Get PNP locations for dropdown
$locationOpt = '<option value="">-- Select Location --</option>';
$DB->vals = array(1);
$DB->types = "i";
$DB->sql = "SELECT locationID, locationName FROM " . $DB->pre . "pnp_location WHERE status=? ORDER BY locationName";
$locations = $DB->dbRows();
foreach ($locations as $loc) {
    $sel = ($D['entityType'] == "Location" && $D['entityID'] == $loc['locationID']) ? ' selected' : '';
    $locationOpt .= '<option value="' . $loc['locationID'] . '"' . $sel . '>' . htmlspecialchars($loc['locationName']) . '</option>';
}

// Build select options
$entityTypes = array("Distributor" => "Distributor", "Customer" => "Customer", "Location" => "Location");
$entityTypeOpt = "";
foreach ($entityTypes as $k => $v) {
    $sel = (($D["entityType"] ?? "Distributor") == $k) ? ' selected="selected"' : '';
    $entityTypeOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$invoiceTypes = array("B2B" => "B2B Invoice", "PNP" => "PNP Invoice", "Other" => "Other");
$invoiceTypeOpt = '<option value="">-- None --</option>';
foreach ($invoiceTypes as $k => $v) {
    $sel = (($D["invoiceType"] ?? "") == $k) ? ' selected="selected"' : '';
    $invoiceTypeOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$reasons = array("Sales Return" => "Sales Return", "Discount" => "Discount", "Rate Difference" => "Rate Difference", "Quality Issue" => "Quality Issue", "Shortage" => "Shortage", "Invoice Cancellation" => "Invoice Cancellation", "Other" => "Other");
$reasonOpt = "";
foreach ($reasons as $k => $v) {
    $sel = (($D["reason"] ?? "Other") == $k) ? ' selected="selected"' : '';
    $reasonOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$statuses = array("Draft" => "Draft", "Approved" => "Approved", "Partially Adjusted" => "Partially Adjusted", "Fully Adjusted" => "Fully Adjusted", "Cancelled" => "Cancelled");
$statusOpt = "";
foreach ($statuses as $k => $v) {
    $sel = (($D["creditNoteStatus"] ?? "Draft") == $k) ? ' selected="selected"' : '';
    $statusOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <?php if ($isEdit): ?>
        <!-- Edit Mode Header -->
        <h2 class="form-head">
            <?php echo htmlspecialchars($D["creditNoteNo"]); ?> -
            <span class="badge badge-<?php
                echo $D["creditNoteStatus"] == "Approved" ? "success" :
                    ($D["creditNoteStatus"] == "Draft" ? "secondary" :
                    ($D["creditNoteStatus"] == "Cancelled" ? "danger" : "info"));
            ?>"><?php echo $D["creditNoteStatus"]; ?></span>
        </h2>

        <p>
            <a href="<?php echo ADMINURL; ?>/mod/credit-note/x-credit-note-print.php?id=<?php echo $id; ?>" target="_blank" class="btn">Print</a>
            <a href="<?php echo ADMINURL; ?>/mod/credit-note/x-credit-note-print.php?id=<?php echo $id; ?>&download=1" target="_blank" class="btn">Download PDF</a>
            <?php if ($D["creditNoteStatus"] == "Draft"): ?>
            <button type="button" onclick="approveCreditNote()" class="btn btn-success">Approve Credit Note</button>
            <?php endif; ?>
            <?php if ($D["creditNoteStatus"] == "Approved" || $D["creditNoteStatus"] == "Partially Adjusted"): ?>
            <button type="button" onclick="showAdjustModal()" class="btn btn-primary">Adjust Against Invoice</button>
            <?php endif; ?>
            <?php if ($D["creditNoteStatus"] != "Cancelled" && $D["creditNoteStatus"] != "Fully Adjusted"): ?>
            <button type="button" onclick="cancelCreditNote()" class="btn btn-danger">Cancel</button>
            <?php endif; ?>
        </p>
        <?php endif; ?>

        <!-- Form -->
        <form name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="creditNoteID" value="<?php echo $id; ?>">
            <input type="hidden" name="items" id="itemsJson" value="">

            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="50%" valign="top">
                        <h2 class="form-head">Credit Note Details</h2>
                        <ul class="tbl-form">
                            <?php
                            $arrForm = array(
                                array("type" => "text", "name" => "creditNoteNo", "value" => $D["creditNoteNo"] ?? "", "title" => "Credit Note No", "info" => '<span class="info">Leave blank for auto-generation</span>', "params" => array("readonly" => $isEdit)),
                                array("type" => "date", "name" => "creditNoteDate", "value" => $D["creditNoteDate"] ?? date("Y-m-d"), "title" => "Date", "validate" => "required"),
                                array("type" => "select", "name" => "entityType", "value" => $entityTypeOpt, "title" => "Entity Type", "validate" => "required"),
                            );
                            echo $MXFRM->getForm($arrForm);
                            ?>
                            <li class="entity-distributor" style="<?php echo ($D["entityType"] ?? "Distributor") == "Distributor" ? "" : "display:none"; ?>">
                                <label>Distributor <span class="req">*</span></label>
                                <div class="frm-field">
                                    <select name="distributorID" id="distributorID" class="entity-select">
                                        <?php echo $distributorOpt; ?>
                                    </select>
                                </div>
                            </li>
                            <li class="entity-location" style="<?php echo ($D["entityType"] ?? "") == "Location" ? "" : "display:none"; ?>">
                                <label>Location <span class="req">*</span></label>
                                <div class="frm-field">
                                    <select name="locationID" id="locationID" class="entity-select">
                                        <?php echo $locationOpt; ?>
                                    </select>
                                </div>
                            </li>
                            <li class="entity-customer" style="<?php echo ($D["entityType"] ?? "") == "Customer" ? "" : "display:none"; ?>">
                                <label>Customer Name</label>
                                <div class="frm-field">
                                    <input type="text" name="customerName" id="customerName" value="<?php echo htmlspecialchars($D["entityName"] ?? ""); ?>">
                                </div>
                            </li>
                            <?php
                            $arrForm2 = array(
                                array("type" => "text", "name" => "entityName", "value" => $D["entityName"] ?? "", "title" => "Entity Name", "validate" => "required", "params" => array("id" => "entityNameDisplay", "readonly" => true)),
                                array("type" => "text", "name" => "entityGSTIN", "value" => $D["entityGSTIN"] ?? "", "title" => "GSTIN", "validate" => "required"),
                                array("type" => "select", "name" => "warehouseID", "value" => $warehouseOpt, "title" => "Warehouse (for stock)", "info" => '<span class="info">Stock will be restored to this warehouse</span>'),
                            );
                            echo $MXFRM->getForm($arrForm2);
                            ?>
                        </ul>

                        <h2 class="form-head">Invoice Reference</h2>
                        <ul class="tbl-form">
                            <?php
                            $arrForm3 = array(
                                array("type" => "select", "name" => "invoiceType", "value" => $invoiceTypeOpt, "title" => "Invoice Type"),
                                array("type" => "text", "name" => "invoiceNo", "value" => $D["invoiceNo"] ?? "", "title" => "Invoice No", "params" => array("id" => "invoiceNo")),
                                array("type" => "hidden", "name" => "invoiceID", "value" => $D["invoiceID"] ?? ""),
                                array("type" => "select", "name" => "reason", "value" => $reasonOpt, "title" => "Reason", "validate" => "required"),
                                array("type" => "textarea", "name" => "reasonDetails", "value" => $D["reasonDetails"] ?? "", "title" => "Reason Details", "params" => array("rows" => 2)),
                            );
                            echo $MXFRM->getForm($arrForm3);
                            ?>
                        </ul>
                    </td>
                    <td width="50%" valign="top">
                        <h2 class="form-head">Amount Details</h2>
                        <ul class="tbl-form">
                            <?php
                            $arrForm4 = array(
                                array("type" => "text", "name" => "subtotal", "value" => $D["subtotal"] ?? "0", "title" => "Subtotal (Rs.)", "validate" => "required,number"),
                                array("type" => "text", "name" => "discountAmount", "value" => $D["discountAmount"] ?? "0", "title" => "Discount (Rs.)", "validate" => "number"),
                                array("type" => "text", "name" => "cgstRate", "value" => $D["cgstRate"] ?? "9", "title" => "CGST Rate (%)", "validate" => "number"),
                                array("type" => "text", "name" => "sgstRate", "value" => $D["sgstRate"] ?? "9", "title" => "SGST Rate (%)", "validate" => "number"),
                                array("type" => "text", "name" => "igstRate", "value" => $D["igstRate"] ?? "0", "title" => "IGST Rate (%)", "validate" => "number"),
                                array("type" => "text", "name" => "totalAmount", "value" => $D["totalAmount"] ?? "0", "title" => "Total Amount (Rs.)", "validate" => "required,number", "params" => array("readonly" => true)),
                            );
                            echo $MXFRM->getForm($arrForm4);
                            ?>
                        </ul>

                        <?php if ($isEdit): ?>
                        <h2 class="form-head">Adjustment Info</h2>
                        <ul class="tbl-form">
                            <li>
                                <label>Status</label>
                                <div class="frm-field"><?php echo $D["creditNoteStatus"]; ?></div>
                            </li>
                            <li>
                                <label>Adjusted Amount</label>
                                <div class="frm-field">Rs. <?php echo number_format($D["adjustedAmount"], 2); ?></div>
                            </li>
                            <li>
                                <label>Balance Amount</label>
                                <div class="frm-field"><strong>Rs. <?php echo number_format($D["balanceAmount"], 2); ?></strong></div>
                            </li>
                            <?php if ($D["approvedDate"]): ?>
                            <li>
                                <label>Approved Date</label>
                                <div class="frm-field"><?php echo date("d-M-Y H:i", strtotime($D["approvedDate"])); ?></div>
                            </li>
                            <?php endif; ?>
                        </ul>
                        <?php endif; ?>

                        <h2 class="form-head">Notes</h2>
                        <ul class="tbl-form">
                            <?php
                            $arrForm5 = array(
                                array("type" => "textarea", "name" => "notes", "value" => $D["notes"] ?? "", "title" => "Notes", "params" => array("rows" => 3)),
                            );
                            echo $MXFRM->getForm($arrForm5);
                            ?>
                        </ul>
                    </td>
                </tr>
            </table>

            <!-- Items Section -->
            <h2 class="form-head">Line Items</h2>
            <?php if ($isDraft): ?>
            <p><button type="button" onclick="addItemRow()" class="btn btn-sm">+ Add Item</button></p>
            <?php endif; ?>

            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list" id="itemsTable">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="25%">Product</th>
                        <th width="10%">SKU</th>
                        <th width="8%">Qty</th>
                        <th width="10%">Unit Price</th>
                        <th width="8%">GST %</th>
                        <th width="10%">Tax</th>
                        <th width="12%">Total</th>
                        <?php if ($isDraft): ?><th width="5%">-</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    <?php if (empty($items)): ?>
                    <tr id="noItemsRow">
                        <td colspan="<?php echo $isDraft ? 9 : 8; ?>" align="center">No items added</td>
                    </tr>
                    <?php else: ?>
                    <?php $sn = 0; foreach ($items as $item): $sn++; ?>
                    <tr class="item-row" data-item='<?php echo htmlspecialchars(json_encode($item)); ?>'>
                        <td><?php echo $sn; ?></td>
                        <td><?php echo htmlspecialchars($item["productName"]); ?></td>
                        <td><?php echo htmlspecialchars($item["productSKU"]); ?></td>
                        <td align="center"><?php echo number_format($item["quantity"], 0); ?></td>
                        <td align="right">Rs. <?php echo number_format($item["unitPrice"], 2); ?></td>
                        <td align="center"><?php echo $item["gstRate"]; ?>%</td>
                        <td align="right">Rs. <?php echo number_format($item["cgstAmount"] + $item["sgstAmount"] + $item["igstAmount"], 2); ?></td>
                        <td align="right"><strong>Rs. <?php echo number_format($item["totalAmount"], 2); ?></strong></td>
                        <?php if ($isDraft): ?>
                        <td><button type="button" onclick="removeItemRow(this)" class="btn btn-sm btn-danger">X</button></td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php echo $MXFRM->closeForm(); ?>
        </form>
    </div>
</div>

<!-- Adjust Modal -->
<div id="adjustModal" class="modal" style="display:none;">
    <div class="modal-content">
        <h3>Adjust Credit Note Against Invoice</h3>
        <div class="modal-body">
            <p>Balance Available: <strong>Rs. <?php echo number_format($D["balanceAmount"] ?? 0, 2); ?></strong></p>
            <ul class="tbl-form">
                <li>
                    <label>Invoice Type</label>
                    <div class="frm-field">
                        <select id="targetInvoiceType" onchange="loadInvoiceList()">
                            <option value="">-- Select --</option>
                            <option value="B2B">B2B Invoice</option>
                            <option value="PNP">PNP Invoice</option>
                        </select>
                    </div>
                </li>
                <li>
                    <label>Invoice</label>
                    <div class="frm-field">
                        <select id="targetInvoiceID">
                            <option value="">-- Select Invoice --</option>
                        </select>
                    </div>
                </li>
                <li>
                    <label>Adjustment Amount</label>
                    <div class="frm-field">
                        <input type="number" id="adjustAmount" step="0.01" max="<?php echo $D["balanceAmount"] ?? 0; ?>">
                    </div>
                </li>
                <li>
                    <label>Notes</label>
                    <div class="frm-field">
                        <textarea id="adjustNotes" rows="2"></textarea>
                    </div>
                </li>
            </ul>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeAdjustModal()" class="btn">Cancel</button>
            <button type="button" onclick="submitAdjustment()" class="btn btn-primary">Apply Adjustment</button>
        </div>
    </div>
</div>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal-content {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    width: 500px;
    max-width: 90%;
}
.modal-footer {
    margin-top: 20px;
    text-align: right;
}
.modal-footer .btn {
    margin-left: 10px;
}
</style>

<script>
// Define required JavaScript variables
var MODINCURL = '<?php echo ADMINURL; ?>/mod/credit-note/x-credit-note.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/credit-note/';
var ADMINURL = '<?php echo ADMINURL; ?>';
var creditNoteID = <?php echo $id; ?>;
var isDraft = <?php echo $isDraft ? 'true' : 'false'; ?>;
var entityType = '<?php echo $D["entityType"] ?? "Distributor"; ?>';
var balanceAmount = <?php echo $D["balanceAmount"] ?? 0; ?>;

// Initialize items from existing data
var items = [];
<?php if (!empty($items)): ?>
items = <?php echo json_encode($items); ?>;
<?php endif; ?>

// From invoice initialization
<?php if ($fromInvoice): ?>
$(document).ready(function() {
    loadFromInvoice('<?php echo $_GET["invoiceType"]; ?>', <?php echo intval($_GET["invoiceID"]); ?>);
});
<?php endif; ?>
</script>
<script src="<?php echo ADMINURL; ?>/mod/credit-note/inc/js/x-credit-note.inc.js"></script>
