<?php
$id = 0;
$D = array();
$milestones = array();

if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "`=?";
    $D = $DB->dbRow();

    // Get milestones
    $DB->vals = array($id);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM `" . $DB->pre . "sky_padel_proforma_milestone` WHERE proformaID=? ORDER BY sortOrder";
    $milestones = $DB->dbRows();
}

// Get quotation dropdown
$whrArr = array("sql" => "status=?", "types" => "i", "vals" => array(1));
$quotationOpt = getTableDD([
    "table" => $DB->pre . "sky_padel_quotation",
    "key" => "quotationID",
    "val" => "CONCAT(quotationNo, ' - ₹', FORMAT(totalAmount, 0))",
    "selected" => ($D['quotationID'] ?? 0),
    "where" => $whrArr
]);

// Get lead dropdown
$leadOpt = getTableDD([
    "table" => $DB->pre . "sky_padel_lead",
    "key" => "leadID",
    "val" => "CONCAT(leadNo, ' - ', clientName)",
    "selected" => ($D['leadID'] ?? 0),
    "where" => $whrArr
]);

// Status dropdown
$statusOptions = array("Generated", "Sent", "Acknowledged", "Partial Payment", "Paid", "Cancelled");
$statusOpt = "";
$currentStatus = $D["invoiceStatus"] ?? "Generated";
foreach ($statusOptions as $s) {
    $sel = ($currentStatus == $s) ? ' selected="selected"' : '';
    $statusOpt .= '<option value="' . $s . '"' . $sel . '>' . $s . '</option>';
}

$arrForm = array(
    array("type" => "text", "name" => "proformaNo", "value" => $D["proformaNo"] ?? "PI-" . date("Ymd") . "-" . str_pad(rand(1, 999), 3, "0", STR_PAD_LEFT), "title" => "Proforma No", "validate" => "required"),
    array("type" => "select", "name" => "quotationID", "value" => $quotationOpt, "title" => "Quotation", "validate" => "required"),
    array("type" => "select", "name" => "leadID", "value" => $leadOpt, "title" => "Lead"),
    array("type" => "date", "name" => "invoiceDate", "value" => $D["invoiceDate"] ?? date("Y-m-d"), "title" => "Invoice Date", "validate" => "required"),
    array("type" => "date", "name" => "validUntil", "value" => $D["validUntil"] ?? date("Y-m-d", strtotime("+30 days")), "title" => "Valid Until"),
    array("type" => "text", "name" => "clientName", "value" => $D["clientName"] ?? "", "title" => "Client Name", "validate" => "required"),
    array("type" => "text", "name" => "clientEmail", "value" => $D["clientEmail"] ?? "", "title" => "Email"),
    array("type" => "text", "name" => "clientPhone", "value" => $D["clientPhone"] ?? "", "title" => "Phone"),
    array("type" => "text", "name" => "clientCompany", "value" => $D["clientCompany"] ?? "", "title" => "Company"),
    array("type" => "textarea", "name" => "clientAddress", "value" => $D["clientAddress"] ?? "", "title" => "Address", "params" => array("rows" => 2)),
    array("type" => "text", "name" => "clientCity", "value" => $D["clientCity"] ?? "", "title" => "City"),
    array("type" => "text", "name" => "clientState", "value" => $D["clientState"] ?? "", "title" => "State"),
    array("type" => "text", "name" => "clientPincode", "value" => $D["clientPincode"] ?? "", "title" => "Pincode"),
    array("type" => "text", "name" => "clientGSTIN", "value" => $D["clientGSTIN"] ?? "", "title" => "GSTIN"),
    array("type" => "text", "name" => "courtConfiguration", "value" => $D["courtConfiguration"] ?? "", "title" => "Court Configuration"),
    array("type" => "textarea", "name" => "scopeOfWork", "value" => $D["scopeOfWork"] ?? "", "title" => "Scope of Work", "params" => array("rows" => 3)),
    array("type" => "text", "name" => "subtotal", "value" => $D["subtotal"] ?? "0", "title" => "Subtotal (₹)", "validate" => "number"),
    array("type" => "text", "name" => "discountAmount", "value" => $D["discountAmount"] ?? "0", "title" => "Discount (₹)", "validate" => "number"),
    array("type" => "text", "name" => "cgstRate", "value" => $D["cgstRate"] ?? "9", "title" => "CGST Rate (%)", "validate" => "number"),
    array("type" => "text", "name" => "cgstAmount", "value" => $D["cgstAmount"] ?? "0", "title" => "CGST Amount (₹)", "validate" => "number"),
    array("type" => "text", "name" => "sgstRate", "value" => $D["sgstRate"] ?? "9", "title" => "SGST Rate (%)", "validate" => "number"),
    array("type" => "text", "name" => "sgstAmount", "value" => $D["sgstAmount"] ?? "0", "title" => "SGST Amount (₹)", "validate" => "number"),
    array("type" => "text", "name" => "totalAmount", "value" => $D["totalAmount"] ?? "0", "title" => "Total Amount (₹)", "validate" => "required,number"),
    array("type" => "text", "name" => "amountInWords", "value" => $D["amountInWords"] ?? "", "title" => "Amount in Words"),
    array("type" => "textarea", "name" => "paymentTerms", "value" => $D["paymentTerms"] ?? "", "title" => "Payment Terms", "params" => array("rows" => 3)),
    array("type" => "textarea", "name" => "bankDetails", "value" => $D["bankDetails"] ?? "Bank: HDFC Bank\nAccount Name: Sky Padel\nAccount No: XXXXXXXXXXXX\nIFSC: HDFC0001234", "title" => "Bank Details", "params" => array("rows" => 4)),
    array("type" => "textarea", "name" => "termsAndConditions", "value" => $D["termsAndConditions"] ?? "", "title" => "Terms & Conditions", "params" => array("rows" => 3)),
    array("type" => "textarea", "name" => "notes", "value" => $D["notes"] ?? "", "title" => "Notes", "params" => array("rows" => 2)),
    array("type" => "select", "name" => "invoiceStatus", "value" => $statusOpt, "title" => "Status")
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form">
            <ul class="tbl-form"><?php echo $MXFRM->getForm($arrForm); ?></ul>

            <!-- Payment Milestones Section -->
            <div class="form-section" style="margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <h3 style="margin-bottom: 15px; color: #333;">Payment Milestones</h3>
                <table id="milestonesTable" class="tbl-list" style="width: 100%;">
                    <thead>
                        <tr>
                            <th width="25%">Milestone Name</th>
                            <th width="25%">Description</th>
                            <th width="12%">Percentage (%)</th>
                            <th width="15%">Amount (₹)</th>
                            <th width="13%">Due After (Days)</th>
                            <th width="10%">Action</th>
                        </tr>
                    </thead>
                    <tbody id="milestoneRows">
                        <?php
                        if (count($milestones) > 0) {
                            foreach ($milestones as $idx => $m) { ?>
                                <tr class="milestone-row">
                                    <td><input type="text" name="milestoneName[]" value="<?php echo htmlspecialchars($m["milestoneName"]); ?>" class="form-control" /></td>
                                    <td><input type="text" name="milestoneDescription[]" value="<?php echo htmlspecialchars($m["milestoneDescription"] ?? ""); ?>" class="form-control" /></td>
                                    <td><input type="number" name="paymentPercentage[]" value="<?php echo $m["paymentPercentage"]; ?>" class="form-control milestone-pct" step="0.01" onchange="calculateMilestoneAmount(this)" /></td>
                                    <td><input type="number" name="paymentAmount[]" value="<?php echo $m["paymentAmount"]; ?>" class="form-control milestone-amt" step="0.01" /></td>
                                    <td><input type="number" name="dueAfterDays[]" value="<?php echo $m["dueAfterDays"]; ?>" class="form-control" /></td>
                                    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeMilestoneRow(this)"><i class="fa fa-trash"></i></button></td>
                                </tr>
                            <?php }
                        } else { ?>
                            <!-- Default milestones -->
                            <tr class="milestone-row">
                                <td><input type="text" name="milestoneName[]" value="Advance Payment" class="form-control" /></td>
                                <td><input type="text" name="milestoneDescription[]" value="On order confirmation" class="form-control" /></td>
                                <td><input type="number" name="paymentPercentage[]" value="50" class="form-control milestone-pct" step="0.01" onchange="calculateMilestoneAmount(this)" /></td>
                                <td><input type="number" name="paymentAmount[]" value="0" class="form-control milestone-amt" step="0.01" /></td>
                                <td><input type="number" name="dueAfterDays[]" value="0" class="form-control" /></td>
                                <td><button type="button" class="btn btn-danger btn-sm" onclick="removeMilestoneRow(this)"><i class="fa fa-trash"></i></button></td>
                            </tr>
                            <tr class="milestone-row">
                                <td><input type="text" name="milestoneName[]" value="On Delivery" class="form-control" /></td>
                                <td><input type="text" name="milestoneDescription[]" value="Materials delivered to site" class="form-control" /></td>
                                <td><input type="number" name="paymentPercentage[]" value="25" class="form-control milestone-pct" step="0.01" onchange="calculateMilestoneAmount(this)" /></td>
                                <td><input type="number" name="paymentAmount[]" value="0" class="form-control milestone-amt" step="0.01" /></td>
                                <td><input type="number" name="dueAfterDays[]" value="30" class="form-control" /></td>
                                <td><button type="button" class="btn btn-danger btn-sm" onclick="removeMilestoneRow(this)"><i class="fa fa-trash"></i></button></td>
                            </tr>
                            <tr class="milestone-row">
                                <td><input type="text" name="milestoneName[]" value="Final Payment" class="form-control" /></td>
                                <td><input type="text" name="milestoneDescription[]" value="On installation completion" class="form-control" /></td>
                                <td><input type="number" name="paymentPercentage[]" value="25" class="form-control milestone-pct" step="0.01" onchange="calculateMilestoneAmount(this)" /></td>
                                <td><input type="number" name="paymentAmount[]" value="0" class="form-control milestone-amt" step="0.01" /></td>
                                <td><input type="number" name="dueAfterDays[]" value="60" class="form-control" /></td>
                                <td><button type="button" class="btn btn-danger btn-sm" onclick="removeMilestoneRow(this)"><i class="fa fa-trash"></i></button></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <div style="margin-top: 10px;">
                    <button type="button" class="btn btn-primary" onclick="addMilestoneRow()"><i class="fa fa-plus"></i> Add Milestone</button>
                    <button type="button" class="btn btn-secondary" onclick="recalculateAllMilestones()"><i class="fa fa-calculator"></i> Recalculate Amounts</button>
                    <span id="totalPercentage" style="margin-left: 20px; font-weight: bold;"></span>
                </div>
            </div>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>

<script>
function calculateTotals() {
    var subtotal = parseFloat(document.querySelector('[name="subtotal"]').value) || 0;
    var discount = parseFloat(document.querySelector('[name="discountAmount"]').value) || 0;
    var cgstRate = parseFloat(document.querySelector('[name="cgstRate"]').value) || 0;
    var sgstRate = parseFloat(document.querySelector('[name="sgstRate"]').value) || 0;

    var taxableAmount = subtotal - discount;
    var cgstAmount = (taxableAmount * cgstRate) / 100;
    var sgstAmount = (taxableAmount * sgstRate) / 100;
    var totalAmount = taxableAmount + cgstAmount + sgstAmount;

    document.querySelector('[name="cgstAmount"]').value = cgstAmount.toFixed(2);
    document.querySelector('[name="sgstAmount"]').value = sgstAmount.toFixed(2);
    document.querySelector('[name="totalAmount"]').value = totalAmount.toFixed(2);

    // Update amount in words
    document.querySelector('[name="amountInWords"]').value = convertToWords(totalAmount);

    // Recalculate milestone amounts
    recalculateAllMilestones();
}

function convertToWords(amount) {
    // Simple conversion - server will do the accurate one
    return "Rupees " + Math.round(amount).toLocaleString('en-IN') + " Only";
}

function addMilestoneRow() {
    var tbody = document.getElementById('milestoneRows');
    var row = document.createElement('tr');
    row.className = 'milestone-row';
    row.innerHTML = `
        <td><input type="text" name="milestoneName[]" value="" class="form-control" /></td>
        <td><input type="text" name="milestoneDescription[]" value="" class="form-control" /></td>
        <td><input type="number" name="paymentPercentage[]" value="0" class="form-control milestone-pct" step="0.01" onchange="calculateMilestoneAmount(this)" /></td>
        <td><input type="number" name="paymentAmount[]" value="0" class="form-control milestone-amt" step="0.01" /></td>
        <td><input type="number" name="dueAfterDays[]" value="0" class="form-control" /></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeMilestoneRow(this)"><i class="fa fa-trash"></i></button></td>
    `;
    tbody.appendChild(row);
}

function removeMilestoneRow(btn) {
    var row = btn.closest('tr');
    row.remove();
    updateTotalPercentage();
}

function calculateMilestoneAmount(input) {
    var row = input.closest('tr');
    var percentage = parseFloat(input.value) || 0;
    var totalAmount = parseFloat(document.querySelector('[name="totalAmount"]').value) || 0;
    var amount = (totalAmount * percentage) / 100;
    row.querySelector('[name="paymentAmount[]"]').value = amount.toFixed(2);
    updateTotalPercentage();
}

function recalculateAllMilestones() {
    var totalAmount = parseFloat(document.querySelector('[name="totalAmount"]').value) || 0;
    document.querySelectorAll('.milestone-pct').forEach(function(input) {
        var row = input.closest('tr');
        var percentage = parseFloat(input.value) || 0;
        var amount = (totalAmount * percentage) / 100;
        row.querySelector('[name="paymentAmount[]"]').value = amount.toFixed(2);
    });
    updateTotalPercentage();
}

function updateTotalPercentage() {
    var total = 0;
    document.querySelectorAll('.milestone-pct').forEach(function(input) {
        total += parseFloat(input.value) || 0;
    });
    var span = document.getElementById('totalPercentage');
    span.textContent = 'Total: ' + total.toFixed(2) + '%';
    span.style.color = (total === 100) ? 'green' : 'red';
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateTotalPercentage();
});
</script>

<style>
.form-section h3 {
    border-bottom: 2px solid #f59e0b;
    padding-bottom: 10px;
}
#milestonesTable input.form-control {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
#milestonesTable th {
    background: #e9ecef;
    padding: 10px;
    text-align: left;
}
#milestonesTable td {
    padding: 8px;
}
.btn-danger {
    background: #dc3545;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
}
.btn-primary {
    background: #f59e0b;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 4px;
    cursor: pointer;
}
.btn-secondary {
    background: #6c757d;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 4px;
    cursor: pointer;
}
</style>

<script>
// Define required JavaScript variables for form submission
var MODINCURL = '<?php echo ADMINURL; ?>/mod/sky-padel-proforma/x-sky-padel-proforma.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/sky-padel-proforma/';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
var ADMINURL = '<?php echo ADMINURL; ?>';
</script>
