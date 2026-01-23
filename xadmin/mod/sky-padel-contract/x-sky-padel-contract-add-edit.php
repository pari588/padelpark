<?php
/**
 * Sky Padel Contract - Add/Edit
 */
require_once("x-sky-padel-contract.inc.php");

$id = 0;
$D = array();
$milestones = array();

// Contracts cannot be added manually
if ($TPL->pageType == "add") {
    echo '<div class="wrap-right">';
    echo getPageNav();
    echo '<div class="wrap-data">';
    echo '<div class="alert alert-info" style="margin: 15px; padding: 20px;">';
    echo '<h4 style="margin-bottom:10px;">Contracts are Auto-Generated</h4>';
    echo '<p>Contracts cannot be created manually. They are automatically generated when a quotation is approved by the client.</p>';
    echo '<p><strong>Workflow:</strong> Lead → Site Visit → Quotation → Client Approves → Contract Generated → Client Signs</p>';
    echo '<p style="margin-top: 15px;">';
    echo '<a href="' . ADMINURL . '/sky-padel-contract/" class="btn btn-primary">View All Contracts</a> ';
    echo '<a href="' . ADMINURL . '/sky-padel-quotation/" class="btn btn-secondary">Manage Quotations</a>';
    echo '</p></div></div></div>';
    return;
}

if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $D = getContractDetails($id);

    if (!$D) {
        echo '<div class="wrap-right">';
        echo getPageNav();
        echo '<div class="wrap-data"><div class="alert alert-danger">Contract not found</div></div></div>';
        return;
    }

    $milestones = $D["milestones"] ?? array();
}

$isSigned = ($D['contractStatus'] ?? '') === 'Signed';

// Status options
$statusOpt = '';
$statuses = array("Pending Signature", "Signed", "Cancelled");
foreach ($statuses as $s) {
    $sel = ($D["contractStatus"] ?? "") == $s ? ' selected="selected"' : '';
    $statusOpt .= '<option value="' . $s . '"' . $sel . '>' . $s . '</option>';
}

$arrForm = array(
    array("type" => "text", "name" => "contractNo", "value" => $D["contractNo"] ?? "", "title" => "Contract No", "params" => array("readonly" => "readonly")),
    array("type" => "text", "name" => "quotationRef", "value" => $D["quotationNo"] ?? "Q-" . ($D["quotationID"] ?? ""), "title" => "Quotation Ref", "params" => array("readonly" => "readonly")),
    array("type" => "select", "name" => "contractStatus", "value" => $statusOpt, "title" => "Status", "params" => $isSigned ? array("disabled" => "disabled") : array()),
    array("type" => "date", "name" => "contractDate", "value" => $D["contractDate"] ?? date("Y-m-d"), "title" => "Contract Date", "params" => $isSigned ? array("disabled" => "disabled") : array()),
    array("type" => "text", "name" => "courtConfiguration", "value" => $D["courtConfiguration"] ?? "", "title" => "Court Configuration", "params" => $isSigned ? array("disabled" => "disabled") : array()),
    array("type" => "text", "name" => "clientName", "value" => $D["clientName"] ?? "", "title" => "Client Name", "params" => $isSigned ? array("disabled" => "disabled") : array()),
    array("type" => "text", "name" => "clientEmail", "value" => $D["clientEmail"] ?? "", "title" => "Client Email", "params" => $isSigned ? array("disabled" => "disabled") : array()),
    array("type" => "text", "name" => "clientPhone", "value" => $D["clientPhone"] ?? "", "title" => "Client Phone", "params" => $isSigned ? array("disabled" => "disabled") : array()),
    array("type" => "textarea", "name" => "clientAddress", "value" => $D["clientAddress"] ?? "", "title" => "Client Address", "params" => array_merge(array("rows" => 2), $isSigned ? array("disabled" => "disabled") : array())),
    array("type" => "text", "name" => "contractAmount", "value" => $D["contractAmount"] ?? "0", "title" => "Contract Amount (₹)", "validate" => "number", "params" => $isSigned ? array("disabled" => "disabled") : array()),
    array("type" => "text", "name" => "advancePercentage", "value" => $D["advancePercentage"] ?? "50", "title" => "Advance %", "validate" => "number", "params" => $isSigned ? array("disabled" => "disabled") : array()),
    array("type" => "text", "name" => "advanceAmount", "value" => $D["advanceAmount"] ?? "0", "title" => "Advance Amount (₹)", "validate" => "number", "params" => $isSigned ? array("disabled" => "disabled") : array()),
    array("type" => "textarea", "name" => "scopeOfWork", "value" => $D["scopeOfWork"] ?? "", "title" => "Scope of Work", "params" => array_merge(array("rows" => 4), $isSigned ? array("disabled" => "disabled") : array())),
    array("type" => "textarea", "name" => "termsAndConditions", "value" => $D["termsAndConditions"] ?? "", "title" => "Terms & Conditions", "params" => array_merge(array("rows" => 6), $isSigned ? array("disabled" => "disabled") : array())),
    array("type" => "textarea", "name" => "paymentTerms", "value" => $D["paymentTerms"] ?? "", "title" => "Payment Terms", "params" => array_merge(array("rows" => 3), $isSigned ? array("disabled" => "disabled") : array())),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <?php
        if ($isSigned) {
            echo '<div class="alert alert-warning" style="margin: 15px; padding: 15px; background: #fff3e0; border-left: 4px solid #ff9800;">';
            echo '<strong>Contract Signed</strong> - Signed by ' . htmlspecialchars($D["signedBy"]) . ' on ' . date("d M Y, h:i A", strtotime($D["signedAt"])) . '. Editing is disabled.';
            echo '</div>';
        }
        ?>
        <ul class="tbl-form"><?php echo $MXFRM->getForm($arrForm); ?></ul>

        <!-- Payment Milestones -->
        <table width="100%" border="0" cellspacing="0" cellpadding="5" class="tbl-form">
            <tr>
                <td class="fld-label" style="vertical-align: top;">Payment Milestones</td>
                <td class="fld-value">
                    <table width="100%" border="0" cellspacing="0" cellpadding="5" id="milestoneTable" style="border: 1px solid #ddd;">
                        <thead>
                            <tr style="background: #f5f5f5;">
                                <th width="5%">#</th>
                                <th width="20%">Milestone Name</th>
                                <th width="25%">Description</th>
                                <th width="12%">Percentage</th>
                                <th width="15%">Amount (₹)</th>
                                <th width="12%">Due After (Days)</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($milestones as $i => $m): ?>
                            <tr>
                                <td style="text-align: center;"><?php echo $i + 1; ?></td>
                                <td><input type="text" name="milestones[<?php echo $i; ?>][name]" value="<?php echo htmlspecialchars($m['milestoneName']); ?>" class="form-control" <?php echo $isSigned ? 'disabled' : ''; ?>></td>
                                <td><input type="text" name="milestones[<?php echo $i; ?>][description]" value="<?php echo htmlspecialchars($m['milestoneDescription'] ?? ''); ?>" class="form-control" <?php echo $isSigned ? 'disabled' : ''; ?>></td>
                                <td><input type="number" name="milestones[<?php echo $i; ?>][percentage]" value="<?php echo $m['paymentPercentage']; ?>" step="0.01" class="form-control" <?php echo $isSigned ? 'disabled' : ''; ?>></td>
                                <td><input type="number" name="milestones[<?php echo $i; ?>][amount]" value="<?php echo $m['paymentAmount']; ?>" step="0.01" class="form-control" <?php echo $isSigned ? 'disabled' : ''; ?>></td>
                                <td><input type="number" name="milestones[<?php echo $i; ?>][dueAfterDays]" value="<?php echo $m['dueAfterDays']; ?>" class="form-control" <?php echo $isSigned ? 'disabled' : ''; ?>></td>
                                <td>
                                    <input type="hidden" name="milestones[<?php echo $i; ?>][id]" value="<?php echo $m['milestoneID']; ?>">
                                    <?php if (!$isSigned): ?>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove();renumberMilestones();">&times;</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (!$isSigned): ?>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addMilestone()" style="margin-top: 10px;">+ Add Milestone</button>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <!-- Signature Info (if signed) -->
        <?php if ($isSigned): ?>
        <table width="100%" border="0" cellspacing="0" cellpadding="5" class="tbl-form">
            <tr>
                <td class="fld-label">Signed By</td>
                <td class="fld-value"><?php echo htmlspecialchars($D["signedBy"]); ?></td>
            </tr>
            <tr>
                <td class="fld-label">Signed At</td>
                <td class="fld-value"><?php echo date("d M Y, h:i A", strtotime($D["signedAt"])); ?></td>
            </tr>
            <tr>
                <td class="fld-label">Signature IP</td>
                <td class="fld-value"><?php echo htmlspecialchars($D["signatureIP"]); ?></td>
            </tr>
        </table>
        <?php endif; ?>

        <!-- Actions -->
        <table width="100%" border="0" cellspacing="0" cellpadding="5" class="tbl-form">
            <tr>
                <td class="fld-label"></td>
                <td class="fld-value">
                    <?php if (!$isSigned): ?>
                    <button type="button" class="btn btn-primary" onclick="saveContract()">Save Contract</button>
                    <?php endif; ?>
                    <a href="<?php echo ADMINURL; ?>/sky-padel-contract-view/?id=<?php echo $id; ?>" class="btn btn-info">View</a>
                    <a href="<?php echo ADMINURL; ?>/mod/sky-padel-contract/x-sky-padel-contract-pdf.php?id=<?php echo $id; ?>" class="btn btn-danger" target="_blank">PDF</a>
                    <a href="<?php echo ADMINURL; ?>/sky-padel-contract/" class="btn btn-secondary">Back</a>
                </td>
            </tr>
        </table>

        <input type="hidden" name="contractID" value="<?php echo $id; ?>">
    </form>
</div>

<script>
var milestoneIndex = <?php echo count($milestones); ?>;

function addMilestone() {
    var tbody = document.querySelector('#milestoneTable tbody');
    var rowCount = tbody.querySelectorAll('tr').length + 1;

    var tr = document.createElement('tr');
    tr.innerHTML = `
        <td style="text-align: center;">${rowCount}</td>
        <td><input type="text" name="milestones[${milestoneIndex}][name]" class="form-control" placeholder="Milestone name"></td>
        <td><input type="text" name="milestones[${milestoneIndex}][description]" class="form-control" placeholder="Description"></td>
        <td><input type="number" name="milestones[${milestoneIndex}][percentage]" class="form-control" step="0.01" value="0"></td>
        <td><input type="number" name="milestones[${milestoneIndex}][amount]" class="form-control" step="0.01" value="0"></td>
        <td><input type="number" name="milestones[${milestoneIndex}][dueAfterDays]" class="form-control" value="0"></td>
        <td>
            <input type="hidden" name="milestones[${milestoneIndex}][id]" value="0">
            <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove();renumberMilestones();">&times;</button>
        </td>
    `;
    tbody.appendChild(tr);
    milestoneIndex++;
}

function renumberMilestones() {
    var rows = document.querySelectorAll('#milestoneTable tbody tr');
    rows.forEach(function(row, idx) {
        row.querySelector('td:first-child').textContent = idx + 1;
    });
}

function saveContract() {
    var formData = new FormData(document.getElementById('frmAddEdit'));
    formData.append('xAction', 'SAVE');

    $.ajax({
        url: MODURL + 'x-sky-padel-contract.inc.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(res) {
            if (res.err == 0) {
                alert('Contract saved successfully!');
                location.reload();
            } else {
                alert(res.msg || 'Failed to save contract');
            }
        },
        error: function() {
            alert('Error saving contract');
        }
    });
}

// Auto-calculate advance amount
document.querySelector('[name="contractAmount"]')?.addEventListener('input', calculateAdvance);
document.querySelector('[name="advancePercentage"]')?.addEventListener('input', calculateAdvance);

function calculateAdvance() {
    var amount = parseFloat(document.querySelector('[name="contractAmount"]').value) || 0;
    var pct = parseFloat(document.querySelector('[name="advancePercentage"]').value) || 0;
    document.querySelector('[name="advanceAmount"]').value = (amount * pct / 100).toFixed(2);
}
</script>
