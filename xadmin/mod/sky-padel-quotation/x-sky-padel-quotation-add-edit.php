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

    // Get existing milestones
    $DB->vals = array($id);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM `" . $DB->pre . "sky_padel_quotation_milestone` WHERE quotationID=? ORDER BY sortOrder";
    $milestones = $DB->dbRows();
}

// Check if this is creating a revision
$isRevision = isset($_GET['revisionOf']) ? intval($_GET['revisionOf']) : 0;
if ($isRevision > 0 && $TPL->pageType == "add") {
    // Load original quotation data for revision
    $DB->vals = array(1, $isRevision);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . "sky_padel_quotation` WHERE status=? AND quotationID=?";
    $origQuotation = $DB->dbRow();
    if ($origQuotation) {
        // Copy data but reset status and dates
        $D = $origQuotation;
        $D["quotationStatus"] = "Draft";
        $D["quotationDate"] = date("Y-m-d");
        $D["validUntil"] = date("Y-m-d", strtotime("+30 days"));

        // Generate new quotation number with revision suffix
        $parentID = $origQuotation["parentQuotationID"] ?: $isRevision;
        $DB->vals = array($parentID, $parentID);
        $DB->types = "ii";
        $DB->sql = "SELECT MAX(revisionNumber) as maxRev FROM " . $DB->pre . "sky_padel_quotation WHERE parentQuotationID=? OR quotationID=?";
        $revData = $DB->dbRow();
        $newRevNum = ($revData["maxRev"] ?? 0) + 1;
        $baseQuotationNo = preg_replace('/-R\d+$/', '', $origQuotation["quotationNo"]);
        $D["quotationNo"] = $baseQuotationNo . "-R" . $newRevNum;

        // Load original milestones
        $DB->vals = array($isRevision);
        $DB->types = "i";
        $DB->sql = "SELECT * FROM `" . $DB->pre . "sky_padel_quotation_milestone` WHERE quotationID=? ORDER BY sortOrder";
        $milestones = $DB->dbRows();
    }
}

// Check if leadID is passed in URL (coming from lead list)
$preselectedLeadID = isset($_GET['leadID']) ? intval($_GET['leadID']) : ($D['leadID'] ?? 0);

$whrArr = array("sql" => "status=?", "types" => "i", "vals" => array(1));
$leadOpt = getTableDD(["table" => $DB->pre . "sky_padel_lead", "key" => "leadID", "val" => "CONCAT(leadNo, ' - ', clientName)", "selected" => $preselectedLeadID, "where" => $whrArr]);
$reportOpt = getTableDD(["table" => $DB->pre . "sky_padel_site_report", "key" => "reportID", "val" => "CONCAT('Report #', reportID, ' - ', DATE_FORMAT(reportDate, '%d-%b-%Y'))", "selected" => ($D['reportID'] ?? 0), "where" => $whrArr]);

$arrForm = array(
    array("type" => "text", "name" => "quotationNo", "value" => $D["quotationNo"] ?? "QT-" . date("Ymd") . "-" . rand(100, 999), "title" => "Quotation No", "validate" => "required"),
    array("type" => "select", "name" => "leadID", "value" => $leadOpt, "title" => "Lead", "validate" => "required"),
    array("type" => "select", "name" => "reportID", "value" => $reportOpt, "title" => "Site Report"),
    array("type" => "date", "name" => "quotationDate", "value" => $D["quotationDate"] ?? date("Y-m-d"), "title" => "Quotation Date", "validate" => "required"),
    array("type" => "date", "name" => "validUntil", "value" => $D["validUntil"] ?? date("Y-m-d", strtotime("+30 days")), "title" => "Valid Until", "validate" => "required"),
    array("type" => "text", "name" => "courtConfiguration", "value" => $D["courtConfiguration"] ?? "", "title" => "Court Configuration", "info" => '<span class="info">e.g., Single Court, Double Court</span>'),
    array("type" => "textarea", "name" => "scopeOfWork", "value" => $D["scopeOfWork"] ?? "", "title" => "Scope of Work", "params" => array("rows" => 4)),
    array("type" => "text", "name" => "subtotal", "value" => $D["subtotal"] ?? "0", "title" => "Subtotal (₹)", "validate" => "number"),
    array("type" => "text", "name" => "taxAmount", "value" => $D["taxAmount"] ?? "0", "title" => "Tax Amount (₹)", "validate" => "number"),
    array("type" => "text", "name" => "totalAmount", "value" => $D["totalAmount"] ?? "0", "title" => "Total Amount (₹)", "validate" => "required,number"),
    array("type" => "textarea", "name" => "terms", "value" => $D["terms"] ?? "Payment: 50% advance, 50% on completion\nWarranty: 1 year\nDelivery: 4-6 weeks", "title" => "Terms & Conditions", "params" => array("rows" => 3)),
    array("type" => "select", "name" => "quotationStatus", "value" => (function() use ($D) {
        $statuses = array("Draft", "Sent", "Client Reviewing", "Approved", "Rejected", "Expired");
        $opt = "";
        $current = $D["quotationStatus"] ?? "Draft";
        foreach ($statuses as $s) {
            $sel = ($current == $s) ? ' selected="selected"' : '';
            $opt .= '<option value="' . $s . '"' . $sel . '>' . $s . '</option>';
        }
        return $opt;
    })(), "title" => "Status"),
    array("type" => "textarea", "name" => "notes", "value" => $D["notes"] ?? "", "title" => "Notes", "params" => array("rows" => 2))
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <?php
        // Show revision banner if this is a revision
        if (($D["revisionNumber"] ?? 0) > 0) {
            echo '<div class="alert alert-info" style="margin: 15px; padding: 15px; background: #e3f2fd; border-left: 4px solid #2196f3; border-radius: 4px;">';
            echo '<strong>Revision #' . $D["revisionNumber"] . '</strong> - ';
            echo 'This is a revision of quotation <a href="?m=sky-padel-quotation-edit&id=' . $D["parentQuotationID"] . '">' . preg_replace('/-R\d+$/', '', $D["quotationNo"]) . '</a>';
            echo '</div>';
        }
        if ($isRevision > 0) {
            echo '<div class="alert alert-warning" style="margin: 15px; padding: 15px; background: #fff3e0; border-left: 4px solid #ff9800; border-radius: 4px;">';
            echo '<strong>Creating Revision</strong> - You are creating a new revision based on the rejected quotation.';
            echo '<input type="hidden" name="parentQuotationID" value="' . ($origQuotation["parentQuotationID"] ?: $isRevision) . '" />';
            echo '<input type="hidden" name="revisionNumber" value="' . $newRevNum . '" />';
            echo '</div>';
        }
        ?>
        <div class="wrap-form">
            <ul class="tbl-form"><?php echo $MXFRM->getForm($arrForm); ?></ul>

            <!-- Payment Milestones Section -->
            <div class="form-section" style="margin: 20px 15px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <h3 style="margin-bottom: 15px; color: #333; border-bottom: 2px solid #f59e0b; padding-bottom: 10px;">Payment Milestones</h3>
                <p style="color: #666; margin-bottom: 15px;">Define payment schedule milestones. These will be copied to the proforma invoice when the quotation is approved.</p>
                <table id="milestonesTable" class="tbl-list" style="width: 100%;">
                    <thead>
                        <tr style="background: #e9ecef;">
                            <th width="25%" style="padding: 10px; text-align: left;">Milestone Name</th>
                            <th width="25%" style="padding: 10px; text-align: left;">Description</th>
                            <th width="12%" style="padding: 10px; text-align: left;">Percentage (%)</th>
                            <th width="15%" style="padding: 10px; text-align: left;">Amount (₹)</th>
                            <th width="13%" style="padding: 10px; text-align: left;">Due After (Days)</th>
                            <th width="10%" style="padding: 10px; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="milestoneRows">
                        <?php
                        if (count($milestones) > 0) {
                            foreach ($milestones as $idx => $m) { ?>
                                <tr class="milestone-row">
                                    <td style="padding: 8px;"><input type="text" name="milestoneName[]" value="<?php echo htmlspecialchars($m["milestoneName"]); ?>" class="form-control" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
                                    <td style="padding: 8px;"><input type="text" name="milestoneDescription[]" value="<?php echo htmlspecialchars($m["milestoneDescription"] ?? ""); ?>" class="form-control" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
                                    <td style="padding: 8px;"><input type="number" name="paymentPercentage[]" value="<?php echo $m["paymentPercentage"]; ?>" class="form-control milestone-pct" step="0.01" onchange="calculateMilestoneAmount(this)" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
                                    <td style="padding: 8px;"><input type="number" name="paymentAmount[]" value="<?php echo $m["paymentAmount"]; ?>" class="form-control milestone-amt" step="0.01" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
                                    <td style="padding: 8px;"><input type="number" name="dueAfterDays[]" value="<?php echo $m["dueAfterDays"]; ?>" class="form-control" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
                                    <td style="padding: 8px; text-align: center;"><button type="button" onclick="removeMilestoneRow(this)" style="background:#dc3545; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;"><i class="fa fa-trash"></i></button></td>
                                </tr>
                            <?php }
                        } else { ?>
                            <!-- Default milestones for new quotation -->
                            <tr class="milestone-row">
                                <td style="padding: 8px;"><input type="text" name="milestoneName[]" value="Advance Payment" class="form-control" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
                                <td style="padding: 8px;"><input type="text" name="milestoneDescription[]" value="On order confirmation" class="form-control" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
                                <td style="padding: 8px;"><input type="number" name="paymentPercentage[]" value="50" class="form-control milestone-pct" step="0.01" onchange="calculateMilestoneAmount(this)" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
                                <td style="padding: 8px;"><input type="number" name="paymentAmount[]" value="0" class="form-control milestone-amt" step="0.01" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
                                <td style="padding: 8px;"><input type="number" name="dueAfterDays[]" value="0" class="form-control" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
                                <td style="padding: 8px; text-align: center;"><button type="button" onclick="removeMilestoneRow(this)" style="background:#dc3545; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;"><i class="fa fa-trash"></i></button></td>
                            </tr>
                            <tr class="milestone-row">
                                <td style="padding: 8px;"><input type="text" name="milestoneName[]" value="On Delivery" class="form-control" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
                                <td style="padding: 8px;"><input type="text" name="milestoneDescription[]" value="Materials delivered to site" class="form-control" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
                                <td style="padding: 8px;"><input type="number" name="paymentPercentage[]" value="25" class="form-control milestone-pct" step="0.01" onchange="calculateMilestoneAmount(this)" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
                                <td style="padding: 8px;"><input type="number" name="paymentAmount[]" value="0" class="form-control milestone-amt" step="0.01" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
                                <td style="padding: 8px;"><input type="number" name="dueAfterDays[]" value="30" class="form-control" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
                                <td style="padding: 8px; text-align: center;"><button type="button" onclick="removeMilestoneRow(this)" style="background:#dc3545; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;"><i class="fa fa-trash"></i></button></td>
                            </tr>
                            <tr class="milestone-row">
                                <td style="padding: 8px;"><input type="text" name="milestoneName[]" value="Final Payment" class="form-control" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
                                <td style="padding: 8px;"><input type="text" name="milestoneDescription[]" value="On installation completion" class="form-control" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
                                <td style="padding: 8px;"><input type="number" name="paymentPercentage[]" value="25" class="form-control milestone-pct" step="0.01" onchange="calculateMilestoneAmount(this)" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
                                <td style="padding: 8px;"><input type="number" name="paymentAmount[]" value="0" class="form-control milestone-amt" step="0.01" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
                                <td style="padding: 8px;"><input type="number" name="dueAfterDays[]" value="60" class="form-control" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
                                <td style="padding: 8px; text-align: center;"><button type="button" onclick="removeMilestoneRow(this)" style="background:#dc3545; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;"><i class="fa fa-trash"></i></button></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <div style="margin-top: 15px;">
                    <button type="button" onclick="addMilestoneRow()" style="background:#f59e0b; color:white; border:none; padding:8px 15px; border-radius:4px; cursor:pointer;"><i class="fa fa-plus"></i> Add Milestone</button>
                    <button type="button" onclick="recalculateAllMilestones()" style="background:#6c757d; color:white; border:none; padding:8px 15px; border-radius:4px; cursor:pointer; margin-left: 10px;"><i class="fa fa-calculator"></i> Recalculate</button>
                    <span id="totalPercentage" style="margin-left: 20px; font-weight: bold;"></span>
                </div>
            </div>

            <?php if ($isRevision > 0) { ?>
            <div style="margin: 20px 15px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Revision Notes</label>
                <textarea name="revisionNotes" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" placeholder="Explain what changed in this revision..."><?php echo htmlspecialchars($D["revisionNotes"] ?? ""); ?></textarea>
            </div>
            <?php } ?>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>

<script>
function addMilestoneRow() {
    var tbody = document.getElementById('milestoneRows');
    var row = document.createElement('tr');
    row.className = 'milestone-row';
    row.innerHTML = `
        <td style="padding: 8px;"><input type="text" name="milestoneName[]" value="" class="form-control" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
        <td style="padding: 8px;"><input type="text" name="milestoneDescription[]" value="" class="form-control" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
        <td style="padding: 8px;"><input type="number" name="paymentPercentage[]" value="0" class="form-control milestone-pct" step="0.01" onchange="calculateMilestoneAmount(this)" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
        <td style="padding: 8px;"><input type="number" name="paymentAmount[]" value="0" class="form-control milestone-amt" step="0.01" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
        <td style="padding: 8px;"><input type="number" name="dueAfterDays[]" value="0" class="form-control" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" /></td>
        <td style="padding: 8px; text-align: center;"><button type="button" onclick="removeMilestoneRow(this)" style="background:#dc3545; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;"><i class="fa fa-trash"></i></button></td>
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

// Recalculate when total amount changes
document.addEventListener('DOMContentLoaded', function() {
    var totalAmountInput = document.querySelector('[name="totalAmount"]');
    if (totalAmountInput) {
        totalAmountInput.addEventListener('change', recalculateAllMilestones);
        totalAmountInput.addEventListener('blur', recalculateAllMilestones);
    }
    updateTotalPercentage();
    recalculateAllMilestones();
});
</script>
