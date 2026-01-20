<?php
/**
 * Sky Padel Profitability Report
 * Dashboard view with key metrics and project-level details
 */

// Get filter values
$filters = array(
    "projectStatus" => $_GET["status"] ?? "",
    "dateFrom" => $_GET["dateFrom"] ?? "",
    "dateTo" => $_GET["dateTo"] ?? "",
    "siteCity" => $_GET["city"] ?? ""
);

// Get data
$projects = getProjectProfitability($filters);
$summary = getProfitabilitySummary($projects);
$monthlyTrend = getMonthlyRevenueTrend(12);
$cityPerformance = getCityWisePerformance();

// Format numbers for display
function formatMoney($amount) {
    if ($amount >= 10000000) {
        return "₹" . number_format($amount / 10000000, 2) . " Cr";
    } elseif ($amount >= 100000) {
        return "₹" . number_format($amount / 100000, 2) . " L";
    }
    return "₹" . number_format($amount, 0);
}
?>
<style>
.report-container { padding: 20px; }

/* Summary Cards */
.summary-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.summary-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.summary-card.highlight {
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    color: #fff;
}

.summary-card.profit {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #fff;
}

.summary-label {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    opacity: 0.8;
    margin-bottom: 8px;
}

.summary-value {
    font-size: 26px;
    font-weight: 700;
}

.summary-meta {
    font-size: 12px;
    margin-top: 8px;
    opacity: 0.8;
}

.summary-change {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 10px;
    margin-top: 8px;
}

.summary-change.up { background: #d1fae5; color: #065f46; }
.summary-change.down { background: #fee2e2; color: #991b1b; }

/* Filters */
.filters-bar {
    background: #fff;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 24px;
    display: flex;
    gap: 16px;
    align-items: center;
    flex-wrap: wrap;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.filter-group label {
    font-size: 11px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
}

.filter-group select,
.filter-group input {
    padding: 8px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 13px;
    min-width: 150px;
}

.btn-filter {
    background: #0d9488;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    align-self: flex-end;
}

.btn-filter:hover { background: #0f766e; }

.btn-reset {
    background: #f3f4f6;
    color: #374151;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    align-self: flex-end;
}

/* Charts Section */
.charts-row {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-bottom: 24px;
}

.chart-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.chart-title {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 20px;
}

.chart-container {
    height: 250px;
    position: relative;
}

/* Bar Chart (Simple CSS) */
.bar-chart {
    display: flex;
    align-items: flex-end;
    justify-content: space-around;
    height: 200px;
    padding: 20px 10px 30px;
    border-bottom: 1px solid #e5e7eb;
}

.bar-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
}

.bar {
    width: 30px;
    background: linear-gradient(180deg, #0d9488 0%, #14b8a6 100%);
    border-radius: 4px 4px 0 0;
    transition: height 0.3s ease;
    position: relative;
}

.bar:hover {
    background: linear-gradient(180deg, #0f766e 0%, #0d9488 100%);
}

.bar-value {
    position: absolute;
    top: -20px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 10px;
    font-weight: 600;
    color: #374151;
    white-space: nowrap;
}

.bar-label {
    margin-top: 8px;
    font-size: 11px;
    color: #6b7280;
}

/* City List */
.city-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.city-item {
    display: flex;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f3f4f6;
}

.city-item:last-child { border-bottom: none; }

.city-rank {
    width: 24px;
    height: 24px;
    background: #f3f4f6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    color: #6b7280;
    margin-right: 12px;
}

.city-rank.top { background: #fef3c7; color: #92400e; }

.city-name {
    flex: 1;
    font-weight: 600;
    color: #1f2937;
}

.city-stats {
    text-align: right;
}

.city-revenue {
    font-weight: 700;
    color: #0d9488;
}

.city-projects {
    font-size: 12px;
    color: #6b7280;
}

/* Project Table */
.projects-table-container {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.table-title {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
}

.export-btn {
    background: #f3f4f6;
    color: #374151;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
}

.export-btn:hover { background: #e5e7eb; }

.projects-table {
    width: 100%;
    border-collapse: collapse;
}

.projects-table th {
    padding: 12px 8px;
    font-size: 11px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-align: left;
    border-bottom: 2px solid #e5e7eb;
    background: #fafafa;
}

.projects-table td {
    padding: 14px 8px;
    font-size: 13px;
    border-bottom: 1px solid #f3f4f6;
}

.projects-table tr:hover td {
    background: #f9fafb;
}

.project-name {
    font-weight: 600;
    color: #1f2937;
}

.project-client {
    font-size: 12px;
    color: #6b7280;
}

.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.status-badge.active { background: #d1fae5; color: #065f46; }
.status-badge.completed { background: #dbeafe; color: #1e40af; }
.status-badge.quoted { background: #fef3c7; color: #92400e; }
.status-badge.lead { background: #e0e7ff; color: #3730a3; }
.status-badge.cancelled { background: #fee2e2; color: #991b1b; }

.profit-positive { color: #059669; font-weight: 700; }
.profit-negative { color: #dc2626; font-weight: 700; }

.margin-bar {
    width: 60px;
    height: 8px;
    background: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
    display: inline-block;
    vertical-align: middle;
    margin-right: 8px;
}

.margin-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.3s ease;
}

.margin-fill.good { background: #10b981; }
.margin-fill.medium { background: #f59e0b; }
.margin-fill.low { background: #ef4444; }

@media (max-width: 1200px) {
    .summary-grid { grid-template-columns: repeat(3, 1fr); }
    .charts-row { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
    .summary-grid { grid-template-columns: repeat(2, 1fr); }
    .filters-bar { flex-direction: column; align-items: stretch; }
}
</style>

<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data report-container">

        <!-- Summary Cards -->
        <div class="summary-grid">
            <div class="summary-card highlight">
                <div class="summary-label">Total Revenue</div>
                <div class="summary-value"><?php echo formatMoney($summary["totalRevenue"]); ?></div>
                <div class="summary-meta"><?php echo $summary["totalProjects"]; ?> Projects</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Received</div>
                <div class="summary-value"><?php echo formatMoney($summary["totalReceived"]); ?></div>
                <div class="summary-change <?php echo $summary["avgCollectionRate"] >= 50 ? 'up' : 'down'; ?>">
                    <?php echo number_format($summary["avgCollectionRate"], 1); ?>% collected
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Outstanding</div>
                <div class="summary-value"><?php echo formatMoney($summary["totalOutstanding"]); ?></div>
                <div class="summary-meta">Pending collection</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Total Costs</div>
                <div class="summary-value"><?php echo formatMoney($summary["totalMaterialCost"] + $summary["totalOtherCost"]); ?></div>
                <div class="summary-meta">Materials: <?php echo formatMoney($summary["totalMaterialCost"]); ?></div>
            </div>
            <div class="summary-card profit">
                <div class="summary-label">Gross Profit</div>
                <div class="summary-value"><?php echo formatMoney($summary["totalProfit"]); ?></div>
                <div class="summary-change up" style="background: rgba(255,255,255,0.2); color: #fff;">
                    <?php echo number_format($summary["avgProfitMargin"], 1); ?>% margin
                </div>
            </div>
        </div>

        <!-- Filters -->
        <form class="filters-bar" method="GET">
            <div class="filter-group">
                <label>Status</label>
                <select name="status">
                    <option value="">All Status</option>
                    <option value="Lead" <?php echo $filters["projectStatus"] == "Lead" ? "selected" : ""; ?>>Lead</option>
                    <option value="Quoted" <?php echo $filters["projectStatus"] == "Quoted" ? "selected" : ""; ?>>Quoted</option>
                    <option value="Active" <?php echo $filters["projectStatus"] == "Active" ? "selected" : ""; ?>>Active</option>
                    <option value="Completed" <?php echo $filters["projectStatus"] == "Completed" ? "selected" : ""; ?>>Completed</option>
                    <option value="Cancelled" <?php echo $filters["projectStatus"] == "Cancelled" ? "selected" : ""; ?>>Cancelled</option>
                </select>
            </div>
            <div class="filter-group">
                <label>From Date</label>
                <input type="date" name="dateFrom" value="<?php echo htmlspecialchars($filters["dateFrom"]); ?>">
            </div>
            <div class="filter-group">
                <label>To Date</label>
                <input type="date" name="dateTo" value="<?php echo htmlspecialchars($filters["dateTo"]); ?>">
            </div>
            <div class="filter-group">
                <label>City</label>
                <input type="text" name="city" placeholder="Filter by city" value="<?php echo htmlspecialchars($filters["siteCity"]); ?>">
            </div>
            <button type="submit" class="btn-filter">Apply Filters</button>
            <a href="<?php echo ADMINURL; ?>/sky-padel-report/" class="btn-reset">Reset</a>
        </form>

        <!-- Charts -->
        <div class="charts-row">
            <div class="chart-card">
                <div class="chart-title">Monthly Revenue Trend</div>
                <div class="chart-container">
                    <?php if (!empty($monthlyTrend)):
                        $maxRevenue = max(array_column($monthlyTrend, 'revenue')) ?: 1;
                    ?>
                    <div class="bar-chart">
                        <?php foreach ($monthlyTrend as $m):
                            $height = ($m["revenue"] / $maxRevenue) * 180;
                        ?>
                        <div class="bar-item">
                            <div class="bar" style="height: <?php echo max(5, $height); ?>px;">
                                <span class="bar-value"><?php echo formatMoney($m["revenue"]); ?></span>
                            </div>
                            <div class="bar-label"><?php echo date("M", strtotime($m["month"] . "-01")); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div style="text-align: center; padding: 60px; color: #9ca3af;">
                        No data available for the selected period
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-title">Top Cities by Revenue</div>
                <ul class="city-list">
                    <?php foreach ($cityPerformance as $i => $city): ?>
                    <li class="city-item">
                        <span class="city-rank <?php echo $i < 3 ? 'top' : ''; ?>"><?php echo $i + 1; ?></span>
                        <span class="city-name"><?php echo htmlspecialchars($city["city"]); ?></span>
                        <div class="city-stats">
                            <div class="city-revenue"><?php echo formatMoney($city["totalRevenue"]); ?></div>
                            <div class="city-projects"><?php echo $city["projectCount"]; ?> projects</div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                    <?php if (empty($cityPerformance)): ?>
                    <li style="text-align: center; padding: 40px; color: #9ca3af;">No city data</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Projects Table -->
        <div class="projects-table-container">
            <div class="table-header">
                <div class="table-title">Project Profitability Details (<?php echo count($projects); ?> projects)</div>
                <button class="export-btn" onclick="exportToCSV()">
                    <i class="fa fa-download"></i> Export CSV
                </button>
            </div>

            <table class="projects-table" id="projectsTable">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>City</th>
                        <th>Status</th>
                        <th style="text-align: right;">Revenue</th>
                        <th style="text-align: right;">Received</th>
                        <th style="text-align: right;">Cost</th>
                        <th style="text-align: right;">Profit</th>
                        <th>Margin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $p):
                        $statusClass = strtolower($p["projectStatus"]);
                        $profitClass = $p["grossProfit"] >= 0 ? "profit-positive" : "profit-negative";
                        $marginClass = $p["profitMargin"] >= 30 ? "good" : ($p["profitMargin"] >= 15 ? "medium" : "low");
                    ?>
                    <tr>
                        <td>
                            <div class="project-name">
                                <a href="<?php echo ADMINURL; ?>/sky-padel-project-edit/?id=<?php echo $p["projectID"]; ?>">
                                    <?php echo htmlspecialchars($p["projectNo"]); ?>
                                </a>
                            </div>
                            <div class="project-client"><?php echo htmlspecialchars($p["clientName"]); ?></div>
                        </td>
                        <td><?php echo htmlspecialchars($p["siteCity"]); ?></td>
                        <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo $p["projectStatus"]; ?></span></td>
                        <td style="text-align: right; font-weight: 600;"><?php echo formatMoney($p["revenue"]); ?></td>
                        <td style="text-align: right;">
                            <?php echo formatMoney($p["totalReceived"]); ?>
                            <div style="font-size: 11px; color: #6b7280;">
                                <?php echo number_format($p["collectionRate"], 0); ?>%
                            </div>
                        </td>
                        <td style="text-align: right;"><?php echo formatMoney($p["totalCost"]); ?></td>
                        <td style="text-align: right;" class="<?php echo $profitClass; ?>">
                            <?php echo ($p["grossProfit"] >= 0 ? "+" : "") . formatMoney($p["grossProfit"]); ?>
                        </td>
                        <td>
                            <div class="margin-bar">
                                <div class="margin-fill <?php echo $marginClass; ?>" style="width: <?php echo min(100, max(0, $p["profitMargin"])); ?>%;"></div>
                            </div>
                            <span style="font-size: 12px; font-weight: 600;">
                                <?php echo number_format($p["profitMargin"], 1); ?>%
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($projects)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #9ca3af;">
                            No projects found matching the criteria
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
function exportToCSV() {
    var table = document.getElementById('projectsTable');
    var csv = [];

    // Headers
    var headers = [];
    table.querySelectorAll('thead th').forEach(function(th) {
        headers.push('"' + th.textContent.trim() + '"');
    });
    csv.push(headers.join(','));

    // Data rows
    table.querySelectorAll('tbody tr').forEach(function(row) {
        var rowData = [];
        row.querySelectorAll('td').forEach(function(td) {
            var text = td.textContent.trim().replace(/\s+/g, ' ');
            rowData.push('"' + text.replace(/"/g, '""') + '"');
        });
        if (rowData.length > 0) {
            csv.push(rowData.join(','));
        }
    });

    // Download
    var blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'profitability-report-' + new Date().toISOString().slice(0,10) + '.csv';
    link.click();
}
</script>
