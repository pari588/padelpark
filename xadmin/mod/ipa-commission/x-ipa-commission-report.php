<?php
// Build coach dropdown
$DB->sql = "SELECT coachID, CONCAT(firstName, ' ', IFNULL(lastName,'')) as coachName FROM " . $DB->pre . "ipa_coach WHERE status=1 ORDER BY firstName";
$coaches = $DB->dbRows() ?: array();

$startDate = $_GET["startDate"] ?? date("Y-m-01");
$endDate = $_GET["endDate"] ?? date("Y-m-t");
$selCoach = $_GET["coachID"] ?? "";

// Get summary totals
$whereCoach = $selCoach ? " AND c.coachID=" . intval($selCoach) : "";
$DB->vals = array($startDate, $endDate);
$DB->types = "ss";
$DB->sql = "SELECT
                COUNT(*) as totalRecords,
                SUM(c.saleAmount) as totalSales,
                SUM(c.commissionAmount) as totalCommission,
                SUM(CASE WHEN c.commissionStatus='Paid' THEN c.commissionAmount ELSE 0 END) as paidAmount,
                SUM(CASE WHEN c.commissionStatus IN ('Pending','Approved') THEN c.commissionAmount ELSE 0 END) as pendingAmount
            FROM " . $DB->pre . "ipa_coach_commission c
            WHERE c.status=1 AND c.saleDate BETWEEN ? AND ?" . $whereCoach;
$DB->dbRows();
$totals = !empty($DB->rows) ? $DB->rows[0] : array("totalRecords" => 0, "totalSales" => 0, "totalCommission" => 0, "paidAmount" => 0, "pendingAmount" => 0);

// Get coach-wise breakdown
$DB->vals = array($startDate, $endDate);
$DB->types = "ss";
$DB->sql = "SELECT c.coachID, CONCAT(co.firstName, ' ', IFNULL(co.lastName,'')) as coachName,
                   co.commissionRate,
                   SUM(c.saleAmount) as totalSales,
                   SUM(c.commissionAmount) as totalCommission,
                   COUNT(*) as recordCount,
                   SUM(CASE WHEN c.commissionStatus='Paid' THEN c.commissionAmount ELSE 0 END) as paidAmount,
                   SUM(CASE WHEN c.commissionStatus IN ('Pending','Approved') THEN c.commissionAmount ELSE 0 END) as pendingAmount
            FROM " . $DB->pre . "ipa_coach_commission c
            LEFT JOIN " . $DB->pre . "ipa_coach co ON c.coachID = co.coachID
            WHERE c.status=1 AND c.saleDate BETWEEN ? AND ?" . $whereCoach . "
            GROUP BY c.coachID
            ORDER BY totalCommission DESC";
$DB->dbRows();
$coachSummary = $DB->rows ?: array();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <div style="padding:10px 15px; border-bottom:1px solid #eee;">
            <a href="<?php echo ADMINURL; ?>/ipa-commission-list/" class="btn btn-default btn-sm"><i class="fa fa-list"></i> Commission List</a>
            <a href="<?php echo ADMINURL; ?>/ipa-commission-add/" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Add Commission</a>
            <a href="<?php echo ADMINURL; ?>/ipa-commission-report/" class="btn btn-primary btn-sm"><i class="fa fa-chart-bar"></i> Summary Report</a>
        </div>

        <div class="wrap-form">
            <h2 class="form-head"><i class="fa fa-chart-bar"></i> Commission Summary Report</h2>

            <!-- Filter Form -->
            <form method="get" action="" style="padding:15px; background:#f9fafb; margin:15px; border-radius:8px; display:flex; gap:15px; flex-wrap:wrap; align-items:end;">
                <div>
                    <label style="display:block; font-size:12px; color:#666; margin-bottom:4px;">Start Date</label>
                    <input type="date" name="startDate" class="inp-fld" value="<?php echo $startDate; ?>">
                </div>
                <div>
                    <label style="display:block; font-size:12px; color:#666; margin-bottom:4px;">End Date</label>
                    <input type="date" name="endDate" class="inp-fld" value="<?php echo $endDate; ?>">
                </div>
                <div>
                    <label style="display:block; font-size:12px; color:#666; margin-bottom:4px;">Coach</label>
                    <select name="coachID" class="inp-fld">
                        <option value="">All Coaches</option>
                        <?php foreach ($coaches as $c): ?>
                        <option value="<?php echo $c["coachID"]; ?>" <?php echo $selCoach == $c["coachID"] ? "selected" : ""; ?>><?php echo htmlspecialchars($c["coachName"]); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Apply Filter</button>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;color:#3b82f6;"><i class="fa fa-shopping-cart"></i></div>
                <div class="stat-info">
                    <span class="stat-value">&#8377; <?php echo number_format($totals["totalSales"] ?? 0, 0); ?></span>
                    <span class="stat-label">Total Sales</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#d1fae5;color:#10b981;"><i class="fa fa-percentage"></i></div>
                <div class="stat-info">
                    <span class="stat-value">&#8377; <?php echo number_format($totals["totalCommission"] ?? 0, 0); ?></span>
                    <span class="stat-label">Total Commission</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#22c55e;"><i class="fa fa-check-circle"></i></div>
                <div class="stat-info">
                    <span class="stat-value">&#8377; <?php echo number_format($totals["paidAmount"] ?? 0, 0); ?></span>
                    <span class="stat-label">Paid Out</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#f59e0b;"><i class="fa fa-clock"></i></div>
                <div class="stat-info">
                    <span class="stat-value">&#8377; <?php echo number_format($totals["pendingAmount"] ?? 0, 0); ?></span>
                    <span class="stat-label">Pending</span>
                </div>
            </div>
        </div>

        <!-- Coach Breakdown -->
        <?php if (!empty($coachSummary)): ?>
        <div style="margin:20px;">
            <h3 style="margin:0 0 15px; font-size:16px; color:#374151;"><i class="fa fa-users"></i> Coach-wise Breakdown</h3>
            <table width="100%" border="0" cellspacing="0" cellpadding="10" class="tbl-list">
                <thead>
                    <tr>
                        <th align="left">Coach</th>
                        <th align="center" width="10%">Rate</th>
                        <th align="center" width="10%">Records</th>
                        <th align="right" width="15%">Total Sales</th>
                        <th align="right" width="15%">Commission</th>
                        <th align="right" width="12%">Paid</th>
                        <th align="right" width="12%">Pending</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coachSummary as $cs): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($cs["coachName"]); ?></strong></td>
                        <td align="center"><?php echo $cs["commissionRate"]; ?>%</td>
                        <td align="center"><?php echo $cs["recordCount"]; ?></td>
                        <td align="right" style="font-family:monospace;">&#8377; <?php echo number_format($cs["totalSales"], 2); ?></td>
                        <td align="right" style="font-family:monospace; font-weight:700; color:#10b981;">&#8377; <?php echo number_format($cs["totalCommission"], 2); ?></td>
                        <td align="right" style="font-family:monospace; color:#22c55e;">&#8377; <?php echo number_format($cs["paidAmount"], 2); ?></td>
                        <td align="right" style="font-family:monospace; color:#f59e0b;">&#8377; <?php echo number_format($cs["pendingAmount"], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot style="background:#f9fafb; font-weight:700;">
                    <tr>
                        <td colspan="3" align="right">Totals:</td>
                        <td align="right" style="font-family:monospace;">&#8377; <?php echo number_format($totals["totalSales"], 2); ?></td>
                        <td align="right" style="font-family:monospace; color:#10b981;">&#8377; <?php echo number_format($totals["totalCommission"], 2); ?></td>
                        <td align="right" style="font-family:monospace; color:#22c55e;">&#8377; <?php echo number_format($totals["paidAmount"], 2); ?></td>
                        <td align="right" style="font-family:monospace; color:#f59e0b;">&#8377; <?php echo number_format($totals["pendingAmount"], 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php else: ?>
        <div class="no-records" style="text-align:center; padding:60px 20px;">
            <i class="fa fa-chart-bar" style="font-size:48px;color:#ddd;margin-bottom:15px;display:block;"></i>
            <p style="margin:0; color:#888;">No commission data for selected period</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    padding: 20px;
}
.stat-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 18px;
    display: flex;
    align-items: center;
    gap: 15px;
}
.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
.stat-info .stat-value {
    display: block;
    font-size: 20px;
    font-weight: 700;
    color: #111827;
}
.stat-info .stat-label {
    font-size: 12px;
    color: #6b7280;
}
</style>
