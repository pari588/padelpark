<?php
require_once("x-stock-allocation.inc.php");

$id = intval($_GET["id"] ?? 0);
$D = getAllocationDetails($id);

if (!$D) {
    echo '<div class="wrap-right">' . getPageNav() . '<div class="wrap-data"><div class="alert alert-danger">Allocation not found</div></div></div>';
    return;
}

$items = $D["items"] ?? array();
$statusColors = array(
    "Reserved" => "#ffc107",
    "Dispatched" => "#28a745",
    "Partial" => "#17a2b8",
    "Returned" => "#6c757d"
);
$statusColor = $statusColors[$D["allocationType"]] ?? "#6c757d";
?>
<style>
.allocation-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 30px; border-radius: 8px; margin-bottom: 20px; }
.allocation-no { font-size: 28px; font-weight: bold; }
.allocation-meta { display: flex; gap: 30px; margin-top: 15px; font-size: 14px; opacity: 0.9; }
.status-badge { display: inline-block; padding: 8px 20px; border-radius: 20px; font-weight: bold; text-transform: uppercase; font-size: 12px; }
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
.btn-dispatch { background: #28a745; color: #fff; border: none; cursor: pointer; }
.btn-edit { background: #667eea; color: #fff; }
.btn-grn { background: #17a2b8; color: #fff; }
.btn-back { background: #f3f4f6; color: #374151; }
</style>

<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data" style="padding: 20px;">

        <!-- Header -->
        <div class="allocation-header">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div class="allocation-no"><?php echo htmlspecialchars($D["allocationNo"]); ?></div>
                    <div class="allocation-meta">
                        <span><i class="fa fa-folder"></i> <?php echo htmlspecialchars($D["projectNo"] . ' - ' . $D["projectName"]); ?></span>
                        <span><i class="fa fa-warehouse"></i> <?php echo htmlspecialchars($D["warehouseName"]); ?></span>
                        <span><i class="fa fa-calendar"></i> <?php echo date("d M Y", strtotime($D["allocationDate"])); ?></span>
                    </div>
                </div>
                <span class="status-badge" style="background: <?php echo $statusColor; ?>;">
                    <?php echo $D["allocationType"]; ?>
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
                <h4>Dispatch Details</h4>
                <div class="info-row">
                    <span class="info-label">Vehicle No</span>
                    <span class="info-value"><?php echo htmlspecialchars($D["vehicleNo"] ?: "-"); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Driver Name</span>
                    <span class="info-value"><?php echo htmlspecialchars($D["driverName"] ?: "-"); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Driver Phone</span>
                    <span class="info-value"><?php echo htmlspecialchars($D["driverPhone"] ?: "-"); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">E-Way Bill</span>
                    <span class="info-value"><?php echo htmlspecialchars($D["ewayBillNo"] ?: "-"); ?></span>
                </div>
                <?php if ($D["dispatchDate"]): ?>
                <div class="info-row">
                    <span class="info-label">Dispatch Date</span>
                    <span class="info-value"><?php echo date("d M Y", strtotime($D["dispatchDate"])); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Items -->
        <div class="info-card">
            <h4>Allocated Items (<?php echo count($items); ?>)</h4>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th style="text-align: center;">Allocated</th>
                        <th style="text-align: center;">Dispatched</th>
                        <th style="text-align: center;">Received</th>
                        <th style="text-align: center;">Unit</th>
                        <th style="text-align: right;">Unit Cost</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $i => $item): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><strong><?php echo htmlspecialchars($item["productName"]); ?></strong></td>
                        <td><?php echo htmlspecialchars($item["productSKU"]); ?></td>
                        <td style="text-align: center;"><?php echo number_format($item["allocatedQty"], 2); ?></td>
                        <td style="text-align: center;"><?php echo number_format($item["dispatchedQty"], 2); ?></td>
                        <td style="text-align: center;"><?php echo number_format($item["receivedQty"], 2); ?></td>
                        <td style="text-align: center;"><?php echo htmlspecialchars($item["unit"]); ?></td>
                        <td style="text-align: right;">₹<?php echo number_format($item["unitCost"], 2); ?></td>
                        <td style="text-align: right;">₹<?php echo number_format($item["totalCost"], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"><strong>Total</strong></td>
                        <td style="text-align: center;"><strong><?php echo number_format($D["totalQty"], 2); ?></strong></td>
                        <td colspan="4"></td>
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

        <!-- Actions -->
        <div class="action-buttons">
            <?php if ($D["allocationType"] == "Reserved"): ?>
                <a href="<?php echo ADMINURL; ?>/stock-allocation-edit/?id=<?php echo $id; ?>" class="btn btn-edit">
                    <i class="fa fa-edit"></i> Edit
                </a>
                <button onclick="dispatchAllocation(<?php echo $id; ?>)" class="btn btn-dispatch">
                    <i class="fa fa-truck"></i> Dispatch
                </button>
            <?php endif; ?>

            <?php if ($D["allocationType"] == "Dispatched"): ?>
                <a href="<?php echo ADMINURL; ?>/site-grn-add/?allocationID=<?php echo $id; ?>" class="btn btn-grn">
                    <i class="fa fa-clipboard-check"></i> Create Site GRN
                </a>
            <?php endif; ?>

            <a href="<?php echo ADMINURL; ?>/stock-allocation/" class="btn btn-back">
                <i class="fa fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</div>

<script>
function dispatchAllocation(allocationID) {
    if (confirm('Are you sure you want to dispatch this allocation?')) {
        $.mxajax({
            url: MODURL + 'x-stock-allocation.inc.php',
            data: { xAction: 'DISPATCH', allocationID: allocationID }
        }).done(function(res) {
            if (res.err == 0) {
                alert('Dispatched successfully!');
                location.reload();
            } else {
                alert(res.msg || 'Failed to dispatch');
            }
        });
    }
}
</script>
