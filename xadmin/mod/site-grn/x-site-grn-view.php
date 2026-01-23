<?php
require_once("x-site-grn.inc.php");

$id = intval($_GET["id"] ?? 0);
$D = getSiteGrnDetails($id);

if (!$D) {
    echo '<div class="wrap-right">' . getPageNav() . '<div class="wrap-data"><div class="alert alert-danger">GRN not found</div></div></div>';
    return;
}

$items = $D["items"] ?? array();
$statusColors = array(
    "Draft" => "#ffc107",
    "Accepted" => "#28a745",
    "Rejected" => "#dc3545"
);
$statusColor = $statusColors[$D["grnStatus"]] ?? "#6c757d";

$typeColors = array(
    "From-Warehouse" => "#667eea",
    "Direct-Purchase" => "#17a2b8",
    "Return" => "#ffc107"
);
$typeColor = $typeColors[$D["grnType"]] ?? "#6c757d";
?>
<style>
.grn-header { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: #fff; padding: 30px; border-radius: 8px; margin-bottom: 20px; }
.grn-no { font-size: 28px; font-weight: bold; }
.grn-meta { display: flex; gap: 30px; margin-top: 15px; font-size: 14px; opacity: 0.9; }
.status-badge { display: inline-block; padding: 8px 20px; border-radius: 20px; font-weight: bold; text-transform: uppercase; font-size: 12px; }
.type-badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-weight: 600; font-size: 11px; margin-left: 10px; }
.info-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
.info-card h4 { margin: 0 0 15px 0; color: #374151; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
.info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
.info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f3f4f6; }
.info-row:last-child { border-bottom: none; }
.info-label { color: #6b7280; }
.info-value { font-weight: 600; color: #111827; }
.items-table { width: 100%; border-collapse: collapse; }
.items-table th { background: #f8f9fa; padding: 12px; text-align: left; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; border-bottom: 2px solid #e5e7eb; }
.items-table td { padding: 12px; border-bottom: 1px solid #f3f4f6; }
.items-table tfoot td { background: #f8f9fa; font-weight: bold; }
.action-buttons { display: flex; gap: 10px; margin-top: 20px; }
.action-buttons .btn { padding: 12px 24px; border-radius: 6px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
.btn-accept { background: #28a745; color: #fff; border: none; cursor: pointer; }
.btn-edit { background: #667eea; color: #fff; }
.btn-back { background: #f3f4f6; color: #374151; }
.condition-good { color: #28a745; }
.condition-damaged { color: #dc3545; }
.condition-partial { color: #ffc107; }
.shortage { color: #dc3545; font-weight: bold; }
.excess { color: #28a745; font-weight: bold; }
</style>

<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data" style="padding: 20px;">

        <!-- Header -->
        <div class="grn-header">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div class="grn-no">
                        <?php echo htmlspecialchars($D["grnNo"]); ?>
                        <span class="type-badge" style="background: <?php echo $typeColor; ?>;">
                            <?php echo str_replace("-", " ", $D["grnType"]); ?>
                        </span>
                    </div>
                    <div class="grn-meta">
                        <span><i class="fa fa-folder"></i> <?php echo htmlspecialchars($D["projectNo"] . ' - ' . $D["projectName"]); ?></span>
                        <?php if ($D["allocationNo"]): ?>
                        <span><i class="fa fa-box"></i> <?php echo htmlspecialchars($D["allocationNo"]); ?></span>
                        <?php endif; ?>
                        <span><i class="fa fa-calendar"></i> <?php echo date("d M Y", strtotime($D["grnDate"])); ?></span>
                    </div>
                </div>
                <span class="status-badge" style="background: <?php echo $statusColor; ?>;">
                    <?php echo $D["grnStatus"]; ?>
                </span>
            </div>
        </div>

        <!-- Info Cards -->
        <div class="info-grid">
            <div class="info-card">
                <h4>Project Details</h4>
                <div class="info-row">
                    <span class="info-label">Project No</span>
                    <span class="info-value"><?php echo htmlspecialchars($D["projectNo"]); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Project Name</span>
                    <span class="info-value"><?php echo htmlspecialchars($D["projectName"]); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Client</span>
                    <span class="info-value"><?php echo htmlspecialchars($D["clientName"]); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Site Location</span>
                    <span class="info-value"><?php echo htmlspecialchars($D["siteCity"]); ?></span>
                </div>
            </div>

            <div class="info-card">
                <h4>Delivery Details</h4>
                <div class="info-row">
                    <span class="info-label">Vehicle No</span>
                    <span class="info-value"><?php echo htmlspecialchars($D["vehicleNo"] ?: "-"); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Transporter</span>
                    <span class="info-value"><?php echo htmlspecialchars($D["transporterName"] ?: "-"); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">LR/Challan No</span>
                    <span class="info-value"><?php echo htmlspecialchars($D["lrNumber"] ?: "-"); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Received By</span>
                    <span class="info-value"><?php echo htmlspecialchars($D["receiverName"] ?: "-"); ?></span>
                </div>
            </div>
        </div>

        <?php if ($D["allocationNo"]): ?>
        <div class="info-card">
            <h4>Source Allocation</h4>
            <div class="info-row">
                <span class="info-label">Allocation No</span>
                <span class="info-value">
                    <a href="<?php echo ADMINURL; ?>/stock-allocation-view/?id=<?php echo $D["allocationID"]; ?>">
                        <?php echo htmlspecialchars($D["allocationNo"]); ?>
                    </a>
                </span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Items -->
        <div class="info-card">
            <h4>Items Received (<?php echo count($items); ?>)</h4>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th style="text-align: center;">Expected</th>
                        <th style="text-align: center;">Received</th>
                        <th style="text-align: center;">Accepted</th>
                        <th style="text-align: center;">Rejected</th>
                        <th style="text-align: center;">Variance</th>
                        <th style="text-align: center;">Condition</th>
                        <th style="text-align: right;">Unit Cost</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $i => $item):
                        $variance = $item["receivedQty"] - $item["expectedQty"];
                        $varianceClass = $variance < 0 ? "shortage" : ($variance > 0 ? "excess" : "");
                        $conditionClass = "condition-" . strtolower($item["itemCondition"]);
                    ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><strong><?php echo htmlspecialchars($item["productName"]); ?></strong></td>
                        <td><?php echo htmlspecialchars($item["productSKU"]); ?></td>
                        <td style="text-align: center;"><?php echo number_format($item["expectedQty"], 2); ?></td>
                        <td style="text-align: center;"><?php echo number_format($item["receivedQty"], 2); ?></td>
                        <td style="text-align: center;"><?php echo number_format($item["acceptedQty"], 2); ?></td>
                        <td style="text-align: center;"><?php echo number_format($item["rejectedQty"], 2); ?></td>
                        <td style="text-align: center;" class="<?php echo $varianceClass; ?>">
                            <?php echo $variance != 0 ? ($variance > 0 ? "+" : "") . number_format($variance, 2) : "-"; ?>
                        </td>
                        <td style="text-align: center;" class="<?php echo $conditionClass; ?>">
                            <?php echo $item["itemCondition"]; ?>
                        </td>
                        <td style="text-align: right;">₹<?php echo number_format($item["unitCost"], 2); ?></td>
                        <td style="text-align: right;">₹<?php echo number_format($item["totalCost"], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4"><strong>Total</strong></td>
                        <td style="text-align: center;"><strong><?php echo number_format($D["totalQty"], 2); ?></strong></td>
                        <td colspan="5"></td>
                        <td style="text-align: right;"><strong>₹<?php echo number_format($D["totalValue"], 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Notes -->
        <?php if (!empty($D["notes"])): ?>
        <div class="info-card">
            <h4>Notes</h4>
            <p><?php echo nl2br(htmlspecialchars($D["notes"])); ?></p>
        </div>
        <?php endif; ?>

        <!-- Acceptance Info -->
        <?php if ($D["grnStatus"] == "Accepted" && $D["acceptedDate"]): ?>
        <div class="info-card">
            <h4>Acceptance Details</h4>
            <div class="info-row">
                <span class="info-label">Accepted Date</span>
                <span class="info-value"><?php echo date("d M Y H:i", strtotime($D["acceptedDate"])); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="action-buttons">
            <?php if ($D["grnStatus"] == "Draft"): ?>
                <a href="<?php echo ADMINURL; ?>/site-grn-edit/?id=<?php echo $id; ?>" class="btn btn-edit">
                    <i class="fa fa-edit"></i> Edit
                </a>
                <button onclick="acceptGrn(<?php echo $id; ?>)" class="btn btn-accept">
                    <i class="fa fa-check-circle"></i> Accept GRN
                </button>
            <?php endif; ?>

            <a href="<?php echo ADMINURL; ?>/site-grn/" class="btn btn-back">
                <i class="fa fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</div>

<script>
function acceptGrn(grnID) {
    if (confirm('Are you sure you want to accept this GRN? This will confirm receipt of goods at the project site.')) {
        $.mxajax({
            url: MODURL + 'x-site-grn.inc.php',
            data: { xAction: 'ACCEPT', grnID: grnID }
        }).done(function(res) {
            if (res.err == 0) {
                alert('GRN accepted successfully!');
                location.reload();
            } else {
                alert(res.msg || 'Failed to accept GRN');
            }
        });
    }
}
</script>
