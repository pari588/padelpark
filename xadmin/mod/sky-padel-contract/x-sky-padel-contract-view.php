<?php
require_once("x-sky-padel-contract.inc.php");

$contractID = intval($_GET["id"] ?? 0);
$contract = getContractDetails($contractID);

if (!$contract) {
    echo '<div class="alert alert-danger">Contract not found</div>';
    return;
}

// Status badge class
$statusClasses = array(
    "Pending Signature" => "badge-warning",
    "Signed" => "badge-success",
    "Cancelled" => "badge-danger"
);
$statusClass = $statusClasses[$contract["contractStatus"]] ?? "badge-secondary";
?>

<style>
.contract-header {
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    color: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
}
.contract-header h1 {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
}
.contract-no {
    font-size: 14px;
    opacity: 0.8;
    margin-bottom: 5px;
}
.contract-status {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}
.contract-status.pending { background: #fef3c7; color: #92400e; }
.contract-status.signed { background: #dcfce7; color: #166534; }
.contract-status.cancelled { background: #fee2e2; color: #991b1b; }

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 30px;
    margin-bottom: 30px;
}
.info-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 24px;
}
.info-card h3 {
    font-size: 14px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e5e7eb;
}
.info-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f3f4f6;
}
.info-row:last-child { border-bottom: none; }
.info-label { color: #6b7280; font-size: 14px; }
.info-value { color: #111827; font-weight: 500; font-size: 14px; }

.milestone-table {
    width: 100%;
    border-collapse: collapse;
}
.milestone-table th {
    background: #f9fafb;
    padding: 12px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    border-bottom: 2px solid #e5e7eb;
}
.milestone-table td {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 14px;
}
.signature-box {
    background: #f0fdf4;
    border: 2px solid #22c55e;
    border-radius: 12px;
    padding: 24px;
    text-align: center;
}
.signature-box.pending {
    background: #fefce8;
    border-color: #eab308;
}
.signature-name {
    font-size: 24px;
    font-family: 'Georgia', serif;
    font-style: italic;
    color: #166534;
    margin: 10px 0;
}
.signature-meta {
    font-size: 12px;
    color: #6b7280;
}
.action-bar {
    margin-top: 30px;
    display: flex;
    gap: 15px;
}
.btn-pdf {
    background: #dc2626;
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-secondary {
    background: #f3f4f6;
    color: #374151;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
}
</style>

<div class="contract-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <div class="contract-no"><?php echo htmlspecialchars($contract["contractNo"]); ?></div>
            <h1>Contract Agreement</h1>
            <div style="margin-top: 10px; opacity: 0.9;">
                Client: <?php echo htmlspecialchars($contract["clientName"]); ?>
            </div>
        </div>
        <div>
            <span class="contract-status <?php echo strtolower(str_replace(' ', '', $contract["contractStatus"])); ?>">
                <?php echo $contract["contractStatus"]; ?>
            </span>
        </div>
    </div>
</div>

<div class="info-grid">
    <!-- Contract Details -->
    <div class="info-card">
        <h3>Contract Details</h3>
        <div class="info-row">
            <span class="info-label">Contract Number</span>
            <span class="info-value"><?php echo htmlspecialchars($contract["contractNo"]); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Quotation Reference</span>
            <span class="info-value">
                <a href="<?php echo ADMINURL; ?>/sky-padel-quotation-edit/?id=<?php echo $contract["quotationID"]; ?>">
                    <?php echo htmlspecialchars($contract["quotationNo"] ?? "Q-" . $contract["quotationID"]); ?>
                </a>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Contract Date</span>
            <span class="info-value"><?php echo date("d M Y", strtotime($contract["contractDate"])); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Court Configuration</span>
            <span class="info-value"><?php echo htmlspecialchars($contract["courtConfiguration"] ?: "-"); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Contract Amount</span>
            <span class="info-value" style="font-size: 18px; color: #0d9488; font-weight: 600;">₹<?php echo number_format($contract["contractAmount"], 2); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Advance Amount (<?php echo $contract["advancePercentage"]; ?>%)</span>
            <span class="info-value">₹<?php echo number_format($contract["advanceAmount"], 2); ?></span>
        </div>
    </div>

    <!-- Client Details -->
    <div class="info-card">
        <h3>Client Information</h3>
        <div class="info-row">
            <span class="info-label">Name</span>
            <span class="info-value"><?php echo htmlspecialchars($contract["clientName"]); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Email</span>
            <span class="info-value"><?php echo htmlspecialchars($contract["clientEmail"]); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Phone</span>
            <span class="info-value"><?php echo htmlspecialchars($contract["clientPhone"] ?: "-"); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Address</span>
            <span class="info-value"><?php echo htmlspecialchars($contract["clientAddress"] ?: "-"); ?></span>
        </div>
    </div>
</div>

<!-- Payment Milestones -->
<?php if (!empty($contract["milestones"])): ?>
<div class="info-card" style="margin-bottom: 30px;">
    <h3>Payment Milestones</h3>
    <table class="milestone-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Milestone</th>
                <th>Description</th>
                <th>Percentage</th>
                <th>Amount</th>
                <th>Due After</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($contract["milestones"] as $i => $m): ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars($m["milestoneName"]); ?></td>
                <td><?php echo htmlspecialchars($m["milestoneDescription"] ?: "-"); ?></td>
                <td><?php echo $m["paymentPercentage"]; ?>%</td>
                <td>₹<?php echo number_format($m["paymentAmount"], 2); ?></td>
                <td><?php echo $m["dueAfterDays"] > 0 ? $m["dueAfterDays"] . " days" : "On Order"; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Signature Section -->
<div class="info-card" style="margin-bottom: 30px;">
    <h3>Signature</h3>
    <?php if ($contract["contractStatus"] == "Signed"): ?>
    <div class="signature-box">
        <div style="font-size: 12px; color: #6b7280; text-transform: uppercase;">Digitally Signed By</div>
        <div class="signature-name"><?php echo htmlspecialchars($contract["signedBy"]); ?></div>
        <div class="signature-meta">
            <div>Signed on: <?php echo date("d M Y, h:i A", strtotime($contract["signedAt"])); ?></div>
            <div>IP Address: <?php echo htmlspecialchars($contract["signatureIP"]); ?></div>
        </div>
    </div>
    <?php else: ?>
    <div class="signature-box pending">
        <div style="font-size: 18px; color: #92400e; margin-bottom: 10px;">
            <i class="fa fa-clock"></i> Awaiting Client Signature
        </div>
        <div style="font-size: 14px; color: #a16207;">
            The client has not yet signed this contract.
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Terms & Conditions -->
<?php if (!empty($contract["termsAndConditions"])): ?>
<div class="info-card" style="margin-bottom: 30px;">
    <h3>Terms & Conditions</h3>
    <div style="font-size: 14px; line-height: 1.6; color: #374151; white-space: pre-line;">
        <?php echo htmlspecialchars($contract["termsAndConditions"]); ?>
    </div>
</div>
<?php endif; ?>

<!-- Action Bar -->
<div class="action-bar">
    <a href="<?php echo ADMINURL; ?>/mod/sky-padel-contract/x-sky-padel-contract-pdf.php?id=<?php echo $contractID; ?>" class="btn-pdf" target="_blank">
        <i class="fa fa-file-pdf"></i> Download PDF
    </a>
    <a href="<?php echo ADMINURL; ?>/sky-padel-contract/" class="btn-secondary">
        <i class="fa fa-arrow-left"></i> Back to List
    </a>
</div>
