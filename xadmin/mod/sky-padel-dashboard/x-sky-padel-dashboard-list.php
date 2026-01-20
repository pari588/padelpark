<?php
// Get profitability summary directly (avoiding external include issues)
$profitSummary = array(
    "totalRevenue" => 0, "totalReceived" => 0, "totalOutstanding" => 0,
    "totalMaterialCost" => 0, "totalExpenseCost" => 0, "totalCost" => 0,
    "totalProfit" => 0, "avgProfitMargin" => 0, "avgCollectionRate" => 0, "totalProjects" => 0
);
$monthlyTrend = array();

// Fetch profitability data including expenses
$DB->sql = "SELECT
    COUNT(*) as totalProjects,
    SUM(contractAmount) as totalRevenue,
    SUM(COALESCE(totalCost, 0)) as totalCost
FROM " . $DB->pre . "sky_padel_project WHERE status = 1";
$DB->dbRow();
if ($DB->row) {
    $profitSummary["totalProjects"] = intval($DB->row["totalProjects"]);
    $profitSummary["totalRevenue"] = floatval($DB->row["totalRevenue"]);
}

// Get material costs from stock allocations
$DB->sql = "SELECT SUM(totalValue) as materialCost FROM " . $DB->pre . "stock_allocation WHERE status = 1";
$DB->dbRow();
$profitSummary["totalMaterialCost"] = floatval($DB->row["materialCost"] ?? 0);

// Get expense costs from project expenses
$DB->sql = "SELECT SUM(totalAmount) as expenseCost FROM " . $DB->pre . "sky_padel_project_expense WHERE status = 1";
$DB->dbRow();
$profitSummary["totalExpenseCost"] = floatval($DB->row["expenseCost"] ?? 0);

// Calculate totals
$profitSummary["totalCost"] = $profitSummary["totalMaterialCost"] + $profitSummary["totalExpenseCost"];
$profitSummary["totalProfit"] = $profitSummary["totalRevenue"] - $profitSummary["totalCost"];
if ($profitSummary["totalRevenue"] > 0) {
    $profitSummary["avgProfitMargin"] = ($profitSummary["totalProfit"] / $profitSummary["totalRevenue"]) * 100;
}

// Get payment totals
$DB->sql = "SELECT SUM(paidAmount) as totalReceived FROM " . $DB->pre . "sky_padel_payment WHERE status = 1";
$DB->dbRow();
if ($DB->row) {
    $profitSummary["totalReceived"] = floatval($DB->row["totalReceived"]);
    $profitSummary["totalOutstanding"] = $profitSummary["totalRevenue"] - $profitSummary["totalReceived"];
    if ($profitSummary["totalRevenue"] > 0) {
        $profitSummary["avgCollectionRate"] = ($profitSummary["totalReceived"] / $profitSummary["totalRevenue"]) * 100;
    }
}

// Get monthly trend (last 6 months)
$DB->sql = "SELECT DATE_FORMAT(startDate, '%Y-%m') as month, SUM(contractAmount) as revenue
    FROM " . $DB->pre . "sky_padel_project
    WHERE status = 1 AND startDate >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(startDate, '%Y-%m') ORDER BY month ASC";
$DB->dbRows();
$monthlyTrend = $DB->rows ?: array();

// Get lead statistics
$DB->vals = array(1);
$DB->types = "i";
$DB->sql = "SELECT
    COUNT(*) as totalLeads,
    SUM(CASE WHEN leadStatus='New' THEN 1 ELSE 0 END) as newLeads,
    SUM(CASE WHEN leadStatus='Contacted' THEN 1 ELSE 0 END) as contactedLeads,
    SUM(CASE WHEN leadStatus='Site Visit Scheduled' THEN 1 ELSE 0 END) as scheduledVisits,
    SUM(CASE WHEN leadStatus='Site Visit Done' THEN 1 ELSE 0 END) as completedVisits,
    SUM(CASE WHEN leadStatus='Quotation Sent' THEN 1 ELSE 0 END) as quotationsSent,
    SUM(CASE WHEN leadStatus='Converted' THEN 1 ELSE 0 END) as quotationsApproved,
    SUM(CASE WHEN leadStatus='Lost' THEN 1 ELSE 0 END) as quotationsRejected,
    SUM(CASE WHEN leadStatus='Lost' THEN 1 ELSE 0 END) as lostLeads
FROM " . $DB->pre . "sky_padel_lead WHERE status=?";
$leadStats = $DB->dbRow();

// Get quotation statistics for sent/approved/rejected counts
$DB->vals = array(1);
$DB->types = "i";
$DB->sql = "SELECT
    SUM(CASE WHEN quotationStatus='Sent' THEN 1 ELSE 0 END) as sentCount,
    SUM(CASE WHEN quotationStatus='Approved' THEN 1 ELSE 0 END) as approvedCount,
    SUM(CASE WHEN quotationStatus='Rejected' THEN 1 ELSE 0 END) as rejectedCount
FROM " . $DB->pre . "sky_padel_quotation WHERE status=?";
$quotStats = $DB->dbRow();
$leadStats["quotationsSent"] = $quotStats["sentCount"] ?? 0;
$leadStats["quotationsApproved"] = $quotStats["approvedCount"] ?? 0;
$leadStats["quotationsRejected"] = $quotStats["rejectedCount"] ?? 0;

// Get project statistics
$DB->vals = array(1);
$DB->types = "i";
$DB->sql = "SELECT
    COUNT(*) as totalProjects,
    SUM(CASE WHEN projectStatus='Active' THEN 1 ELSE 0 END) as activeProjects,
    SUM(CASE WHEN projectStatus='Completed' THEN 1 ELSE 0 END) as completedProjects,
    SUM(CASE WHEN projectStatus='On Hold' THEN 1 ELSE 0 END) as onHoldProjects,
    SUM(contractAmount) as totalContractValue,
    SUM(CASE WHEN projectStatus='Active' THEN contractAmount ELSE 0 END) as activeContractValue
FROM " . $DB->pre . "sky_padel_project WHERE status=?";
$projectStats = $DB->dbRow();

// Get payment statistics
$DB->vals = array(1);
$DB->types = "i";
$DB->sql = "SELECT
    SUM(paidAmount) as totalPayments,
    SUM(CASE WHEN paymentType='Advance' THEN paidAmount ELSE 0 END) as totalAdvance,
    SUM(CASE WHEN paymentType='Milestone' THEN paidAmount ELSE 0 END) as totalMilestone,
    SUM(CASE WHEN paymentType='Final' THEN paidAmount ELSE 0 END) as totalFinal,
    SUM(CASE WHEN MONTH(paymentDate)=MONTH(CURDATE()) AND YEAR(paymentDate)=YEAR(CURDATE()) THEN paidAmount ELSE 0 END) as monthlyRevenue
FROM " . $DB->pre . "sky_padel_payment WHERE status=?";
$paymentStats = $DB->dbRow();

// Get quotation statistics
$DB->vals = array(1);
$DB->types = "i";
$DB->sql = "SELECT
    COUNT(*) as totalQuotations,
    SUM(totalAmount) as totalQuotationValue,
    SUM(CASE WHEN quotationStatus='Draft' THEN totalAmount ELSE 0 END) as draftValue,
    SUM(CASE WHEN quotationStatus='Sent' THEN totalAmount ELSE 0 END) as sentValue,
    SUM(CASE WHEN quotationStatus='Approved' THEN totalAmount ELSE 0 END) as approvedValue,
    SUM(CASE WHEN quotationStatus='Rejected' THEN totalAmount ELSE 0 END) as rejectedValue
FROM " . $DB->pre . "sky_padel_quotation WHERE status=?";
$quotationStats = $DB->dbRow();

// Get upcoming site visits
$DB->vals = array(1);
$DB->types = "i";
$DB->sql = "SELECT v.*, l.leadNo, l.clientName
FROM " . $DB->pre . "sky_padel_site_visit v
LEFT JOIN " . $DB->pre . "sky_padel_lead l ON v.leadID=l.leadID
WHERE v.status=? AND v.visitStatus='Scheduled' AND v.visitDate >= CURDATE()
ORDER BY v.visitDate ASC, v.visitTime ASC LIMIT 5";
$upcomingVisits = $DB->dbRows();

// Get recent payments
$DB->vals = array(1);
$DB->types = "i";
$DB->sql = "SELECT p.*, q.quotationNo
FROM " . $DB->pre . "sky_padel_payment p
LEFT JOIN " . $DB->pre . "sky_padel_quotation q ON p.quotationID=q.quotationID
WHERE p.status=?
ORDER BY p.paymentDate DESC LIMIT 5";
$recentPayments = $DB->dbRows();

// Get active projects
$DB->vals = array(1);
$DB->types = "i";
$DB->sql = "SELECT * FROM " . $DB->pre . "sky_padel_project
WHERE status=? AND projectStatus='Active'
ORDER BY startDate DESC LIMIT 5";
$activeProjects = $DB->dbRows();

// Calculate conversion rate
$conversionRate = $leadStats["totalLeads"] > 0 ? round(($leadStats["quotationsApproved"] / $leadStats["totalLeads"]) * 100, 1) : 0;
?>

<style>
/*=============================================================================
  SKY PADEL DASHBOARD - "STADIUM COMMAND CENTER"
  Bold, authoritative, high-contrast scoreboard aesthetic
  Massive typography for effortless readability
=============================================================================*/

@import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Manrope:wght@400;500;600;700;800&display=swap');

/* ========== DASHBOARD CONTAINER ========== */
.spd-dashboard {
    font-family: 'Manrope', system-ui, sans-serif;
    background: transparent;
    min-height: auto;
    padding: 24px;
    color: #1a1f26;
    font-size: 16px;
    line-height: 1.5;
}

/* ========== HEADER ========== */
.spd-header {
    margin-bottom: 32px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 20px;
    padding-bottom: 24px;
    border-bottom: 1px solid rgba(0,0,0,0.08);
}

.spd-title {
    font-family: 'Bebas Neue', Impact, sans-serif !important;
    font-size: 56px;
    font-weight: 400;
    color: #1a1f26;
    margin: 0;
    letter-spacing: 3px;
    text-transform: uppercase;
    line-height: 1;
}

.spd-subtitle {
    font-size: 16px;
    color: #8b95a5;
    margin-top: 10px;
    font-weight: 500;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.spd-date {
    font-size: 15px;
    font-weight: 700;
    color: #b45309;
    background: rgba(245, 158, 11, 0.1);
    padding: 14px 24px;
    border-radius: 8px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* ========== HERO METRICS - MASSIVE NUMBERS ========== */
.spd-hero-metrics {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
    margin-bottom: 40px;
}

.spd-hero-card {
    background: #fff;
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 16px;
    padding: 36px 32px;
    position: relative;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

/* Glowing top accent bar */
.spd-hero-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--card-accent, #f59e0b);
}

.spd-hero-card:hover {
    transform: translateY(-4px);
    border-color: var(--card-accent, #f59e0b);
    box-shadow: 0 12px 24px rgba(0,0,0,0.1);
}

.spd-hero-card.blue {
    --card-accent: #3b82f6;
}
.spd-hero-card.green {
    --card-accent: #22c55e;
}
.spd-hero-card.orange {
    --card-accent: #f59e0b;
}
.spd-hero-card.red {
    --card-accent: #ef4444;
}

.spd-hero-label {
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: #5c6878;
    margin-bottom: 16px;
}

.spd-hero-value {
    font-family: 'Bebas Neue', Impact, sans-serif !important;
    font-size: 72px;
    font-weight: 400;
    color: #1a1f26;
    line-height: 1;
    margin-bottom: 12px;
    letter-spacing: 2px;
}

.spd-hero-value a {
    color: inherit;
    text-decoration: none;
    transition: all 0.3s ease;
}

.spd-hero-value a:hover {
    color: var(--card-accent, #f59e0b);
}

.spd-hero-sub {
    font-size: 15px;
    color: #8b95a5;
    font-weight: 600;
}

.spd-hero-sub .trend-up {
    color: #22c55e;
    font-weight: 800;
}

/* ========== CONVERSION RING ========== */
.spd-conversion-ring {
    position: absolute;
    top: 50%;
    right: 32px;
    transform: translateY(-50%);
    width: 100px;
    height: 100px;
}

.spd-conversion-ring svg {
    transform: rotate(-90deg);
    width: 100px;
    height: 100px;
}

.spd-conversion-ring circle {
    fill: none;
    stroke-width: 8;
}

.spd-conversion-ring .bg {
    stroke: rgba(0,0,0,0.08);
}

.spd-conversion-ring .progress {
    stroke: #22c55e;
    stroke-linecap: round;
    stroke-dasharray: 251;
    stroke-dashoffset: calc(251 - (251 * var(--percent, 0)) / 100);
    transition: stroke-dashoffset 1.5s cubic-bezier(0.16, 1, 0.3, 1);
}

.spd-conversion-value {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-family: 'Bebas Neue', Impact, sans-serif !important;
    font-size: 24px;
    font-weight: 400;
    color: #22c55e;
    letter-spacing: 1px;
}

/* ========== STATS GRID ========== */
.spd-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 40px;
}

.spd-stat-card {
    background: #fff;
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 12px;
    padding: 28px 24px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.spd-stat-card:hover {
    background: #fff;
    border-color: #f59e0b;
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}

.spd-stat-label {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #8b95a5;
    margin-bottom: 12px;
}

.spd-stat-value {
    font-family: 'Bebas Neue', Impact, sans-serif !important;
    font-size: 42px;
    font-weight: 400;
    color: #1a1f26;
    letter-spacing: 1px;
}

.spd-stat-value.green {
    color: #22c55e;
}

.spd-stat-sub {
    font-size: 14px;
    color: #8b95a5;
    margin-top: 8px;
    font-weight: 500;
}

/* ========== PROFITABILITY SECTION ========== */
.spd-profit-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 16px;
    padding: 24px;
}

.spd-profit-card {
    background: #fff;
    border: 1px solid rgba(0,0,0,0.06);
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.3s ease;
}

.spd-profit-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.08);
}

.spd-profit-card.highlight {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #fff;
    border: none;
}

.spd-profit-card.highlight .spd-profit-label {
    color: rgba(255,255,255,0.8);
}

.spd-profit-card.highlight .spd-profit-value {
    color: #fff;
}

.spd-profit-card.highlight .spd-profit-sub {
    color: rgba(255,255,255,0.7);
}

.spd-profit-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.spd-profit-data {
    flex: 1;
    min-width: 0;
}

.spd-profit-label {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #8b95a5;
    margin-bottom: 4px;
}

.spd-profit-value {
    font-family: 'Bebas Neue', Impact, sans-serif !important;
    font-size: 28px;
    font-weight: 400;
    color: #1a1f26;
    letter-spacing: 1px;
    line-height: 1;
}

.spd-profit-sub {
    font-size: 12px;
    color: #8b95a5;
    margin-top: 4px;
    font-weight: 500;
}

/* Mini Revenue Chart */
.spd-mini-chart {
    padding: 0 24px 24px;
}

.spd-mini-chart-title {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #8b95a5;
    margin-bottom: 16px;
    padding-top: 8px;
    border-top: 1px solid rgba(0,0,0,0.06);
}

.spd-mini-bars {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    height: 80px;
    gap: 8px;
}

.spd-mini-bar-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
}

.spd-mini-bar {
    width: 100%;
    max-width: 40px;
    background: linear-gradient(180deg, #0d9488 0%, #14b8a6 100%);
    border-radius: 4px 4px 0 0;
    margin-top: auto;
    transition: all 0.3s ease;
}

.spd-mini-bar:hover {
    background: linear-gradient(180deg, #0f766e 0%, #0d9488 100%);
    transform: scaleY(1.05);
}

.spd-mini-bar-label {
    font-size: 11px;
    font-weight: 600;
    color: #8b95a5;
    margin-top: 8px;
    text-transform: uppercase;
}

/* Gantt Action Button */
.spd-gantt-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    font-size: 14px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.spd-gantt-btn:hover {
    background: #3b82f6;
    color: #fff;
    transform: scale(1.1);
}

@media (max-width: 1400px) {
    .spd-profit-grid { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 1000px) {
    .spd-profit-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 600px) {
    .spd-profit-grid { grid-template-columns: 1fr; }
}

/* ========== TWO COLUMN LAYOUT ========== */
.spd-two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 28px;
    margin-bottom: 28px;
}

/* Projects section spacing */
.spd-projects {
    margin-bottom: 28px;
}

/* ========== SECTION CARDS ========== */
.spd-section {
    background: #fff;
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    margin-bottom: 28px;
}

.spd-section:last-child {
    margin-bottom: 0;
}

.spd-section-header {
    padding: 20px 28px;
    border-bottom: 1px solid rgba(0,0,0,0.06);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #faf8f5;
}

.spd-section-title {
    font-family: 'Bebas Neue', Impact, sans-serif !important;
    font-size: 24px;
    font-weight: 400;
    color: #1a1f26;
    display: flex;
    align-items: center;
    gap: 14px;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.spd-section-title::before {
    content: '';
    width: 5px;
    height: 28px;
    background: linear-gradient(180deg, #f59e0b 0%, #c87941 100%);
    border-radius: 3px;
}

.spd-section-badge {
    font-size: 14px;
    font-weight: 700;
    background: rgba(245, 158, 11, 0.12);
    color: #b45309;
    padding: 8px 16px;
    border-radius: 100px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* ========== DATA TABLES - LARGE & READABLE ========== */
.spd-table {
    width: 100%;
    border-collapse: collapse;
}

.spd-table th {
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #8b95a5;
    text-align: left;
    padding: 16px 24px;
    background: #faf8f5;
    border-bottom: 1px solid rgba(0,0,0,0.06);
}

.spd-table td {
    font-size: 16px;
    font-weight: 500;
    padding: 18px 24px;
    border-bottom: 1px solid rgba(0,0,0,0.04);
    color: #1a1f26;
}

.spd-table tr:hover td {
    background: rgba(245, 158, 11, 0.06);
}

.spd-table tr:last-child td {
    border-bottom: none;
}

.spd-table a {
    color: #b45309;
    text-decoration: none;
    font-weight: 700;
    transition: all 0.3s ease;
}

.spd-table a:hover {
    color: #f59e0b;
}

.spd-table .mono {
    font-family: 'JetBrains Mono', 'Fira Code', monospace !important;
    font-size: 15px;
    color: #5c6878;
    font-weight: 500;
}

.spd-table .amount {
    font-family: 'Bebas Neue', Impact, sans-serif !important;
    font-weight: 400;
    color: #22c55e;
    font-size: 22px;
    letter-spacing: 1px;
}

/* ========== EMPTY STATE ========== */
.spd-empty {
    padding: 60px 32px;
    text-align: center;
    color: #8b95a5;
    font-size: 18px;
    font-weight: 500;
}

.spd-empty-icon {
    font-size: 56px;
    margin-bottom: 16px;
    opacity: 0.4;
}

/* ========== LEAD FUNNEL - SCOREBOARD STYLE ========== */
.spd-funnel {
    margin-bottom: 0;
    margin-top: 28px;
}

.spd-funnel-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 16px;
    padding: 32px;
}

.spd-funnel-item {
    background: #fff;
    border-radius: 12px;
    padding: 32px 16px;
    text-align: center;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(0,0,0,0.08);
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

/* Colored bottom glow */
.spd-funnel-item::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 100%;
    background: linear-gradient(to top, var(--funnel-color, #448aff) 0%, transparent 60%);
    opacity: 0.08;
    transition: opacity 0.3s ease;
}

.spd-funnel-item:hover {
    transform: translateY(-6px);
    border-color: var(--funnel-color, #448aff);
    box-shadow: 0 12px 24px rgba(0,0,0,0.1);
}

.spd-funnel-item:hover::before {
    opacity: 0.15;
}

.spd-funnel-item:nth-child(1) { --funnel-color: #448aff; --funnel-rgb: 68, 138, 255; }
.spd-funnel-item:nth-child(2) { --funnel-color: #7c4dff; --funnel-rgb: 124, 77, 255; }
.spd-funnel-item:nth-child(3) { --funnel-color: #e040fb; --funnel-rgb: 224, 64, 251; }
.spd-funnel-item:nth-child(4) { --funnel-color: #ffab00; --funnel-rgb: 255, 171, 0; }
.spd-funnel-item:nth-child(5) { --funnel-color: #00bfa5; --funnel-rgb: 0, 191, 165; }
.spd-funnel-item:nth-child(6) { --funnel-color: #00e676; --funnel-rgb: 0, 230, 118; }

.spd-funnel-num {
    font-family: 'Bebas Neue', Impact, sans-serif !important;
    font-size: 52px;
    font-weight: 400;
    color: #1a1f26;
    position: relative;
    z-index: 1;
    margin-bottom: 10px;
    letter-spacing: 2px;
}

.spd-funnel-label {
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #8b95a5;
    position: relative;
    z-index: 1;
}

.spd-funnel-arrow {
    position: absolute;
    right: -10px;
    top: 50%;
    transform: translateY(-50%);
    color: #b8c0cc;
    font-size: 20px;
    z-index: 2;
    opacity: 0.6;
}

/* ========== STATUS BADGES ========== */
.spd-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 100px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.spd-badge.active {
    background: rgba(34, 197, 94, 0.12);
    color: #22c55e;
}

.spd-badge.active::before {
    content: '';
    width: 8px;
    height: 8px;
    background: #22c55e;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.4); }
}

/* ========== PAYMENT TYPE BADGES ========== */
.spd-payment-type {
    font-size: 12px;
    font-weight: 700;
    padding: 6px 12px;
    border-radius: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.spd-payment-type.advance {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.spd-payment-type.milestone {
    background: rgba(139, 92, 246, 0.1);
    color: #8b5cf6;
}

.spd-payment-type.final {
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
}

/* ========== ACTION BUTTONS ========== */
.spd-quick-actions {
    display: flex;
    gap: 12px;
}

.spd-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-radius: 8px;
    border: 2px solid #f59e0b;
    background: #fff;
    color: #b45309 !important;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none !important;
}

.spd-action-btn:hover {
    background: #f59e0b;
    border-color: #f59e0b;
    color: #fff !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(245, 158, 11, 0.25);
}

.spd-action-btn.primary {
    background: #f59e0b;
    border: 2px solid #f59e0b;
    color: #fff !important;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
}

.spd-action-btn.primary:hover {
    background: #d97706;
    border-color: #d97706;
    box-shadow: 0 8px 20px rgba(245, 158, 11, 0.35);
    transform: translateY(-3px);
}

/* ========== RESPONSIVE ========== */
@media (max-width: 1600px) {
    .spd-hero-value { font-size: 72px; }
    .spd-funnel-num { font-size: 56px; }
}

@media (max-width: 1400px) {
    .spd-hero-metrics { grid-template-columns: repeat(2, 1fr); }
    .spd-stats-grid { grid-template-columns: repeat(2, 1fr); }
    .spd-funnel-grid { grid-template-columns: repeat(3, 1fr); }
    .spd-hero-value { font-size: 64px; }
}

@media (max-width: 1000px) {
    .spd-two-col { grid-template-columns: 1fr; }
    .spd-funnel-grid { grid-template-columns: repeat(2, 1fr); }
    .spd-hero-value { font-size: 56px; }
    .spd-dashboard { padding: 32px; }
    .spd-title { font-size: 56px; }
}

@media (max-width: 600px) {
    .spd-hero-metrics { grid-template-columns: 1fr; }
    .spd-stats-grid { grid-template-columns: 1fr; }
    .spd-funnel-grid { grid-template-columns: 1fr; }
    .spd-title { font-size: 42px; }
    .spd-hero-value { font-size: 48px; }
    .spd-funnel-num { font-size: 48px; }
    .spd-dashboard { padding: 24px; }
}

/* ========== ANIMATIONS ========== */
@keyframes fadeSlideUp {
    from {
        opacity: 0;
        transform: translateY(40px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}


.spd-hero-card,
.spd-stat-card,
.spd-section,
.spd-funnel-item {
    animation: fadeSlideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.spd-hero-card:nth-child(1) { animation-delay: 0.1s; }
.spd-hero-card:nth-child(2) { animation-delay: 0.15s; }
.spd-hero-card:nth-child(3) { animation-delay: 0.2s; }
.spd-hero-card:nth-child(4) { animation-delay: 0.25s; }

.spd-stat-card:nth-child(1) { animation-delay: 0.3s; }
.spd-stat-card:nth-child(2) { animation-delay: 0.35s; }
.spd-stat-card:nth-child(3) { animation-delay: 0.4s; }
.spd-stat-card:nth-child(4) { animation-delay: 0.45s; }

.spd-funnel-item:nth-child(1) { animation-delay: 0.5s; }
.spd-funnel-item:nth-child(2) { animation-delay: 0.55s; }
.spd-funnel-item:nth-child(3) { animation-delay: 0.6s; }
.spd-funnel-item:nth-child(4) { animation-delay: 0.65s; }
.spd-funnel-item:nth-child(5) { animation-delay: 0.7s; }
.spd-funnel-item:nth-child(6) { animation-delay: 0.75s; }

.spd-title {
    animation: fadeSlideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
}
</style>

<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data" style="background: transparent; overflow: visible;">

        <div class="spd-dashboard">
            <!-- Header -->
            <div class="spd-header">
                <div>
                    <h1 class="spd-title">Sky Padel</h1>
                    <p class="spd-subtitle">Business Intelligence Dashboard</p>
                </div>
                <div class="spd-date"><?php echo date("l, d M Y"); ?></div>
            </div>

            <!-- Hero Metrics -->
            <div class="spd-hero-metrics">
                <div class="spd-hero-card blue">
                    <div class="spd-hero-label">Total Leads</div>
                    <div class="spd-hero-value">
                        <a href="<?php echo ADMINURL; ?>/sky-padel-lead-list/"><?php echo $leadStats["totalLeads"] ?? 0; ?></a>
                    </div>
                    <div class="spd-hero-sub">All time acquisition</div>
                </div>

                <div class="spd-hero-card green" style="position: relative;">
                    <div class="spd-hero-label">Quotations Approved</div>
                    <div class="spd-hero-value">
                        <a href="<?php echo ADMINURL; ?>/sky-padel-quotation-list/"><?php echo $leadStats["quotationsApproved"] ?? 0; ?></a>
                    </div>
                    <div class="spd-hero-sub">
                        <span class="trend-up"><?php echo $conversionRate; ?>%</span> conversion rate
                    </div>
                    <div class="spd-conversion-ring" style="--percent: <?php echo $conversionRate; ?>">
                        <svg viewBox="0 0 100 100">
                            <circle class="bg" cx="50" cy="50" r="40"/>
                            <circle class="progress" cx="50" cy="50" r="40"/>
                        </svg>
                        <div class="spd-conversion-value"><?php echo $conversionRate; ?>%</div>
                    </div>
                </div>

                <div class="spd-hero-card orange">
                    <div class="spd-hero-label">Quotations Sent</div>
                    <div class="spd-hero-value">
                        <a href="<?php echo ADMINURL; ?>/sky-padel-quotation-list/"><?php echo $leadStats["quotationsSent"] ?? 0; ?></a>
                    </div>
                    <div class="spd-hero-sub">Awaiting response</div>
                </div>

                <div class="spd-hero-card red">
                    <div class="spd-hero-label">Quotations Rejected</div>
                    <div class="spd-hero-value">
                        <a href="<?php echo ADMINURL; ?>/sky-padel-quotation-list/"><?php echo $leadStats["quotationsRejected"] ?? 0; ?></a>
                    </div>
                    <div class="spd-hero-sub">Lost opportunities</div>
                </div>
            </div>

            <!-- Project & Revenue Stats -->
            <div class="spd-stats-grid">
                <div class="spd-stat-card">
                    <div class="spd-stat-label">Active Projects</div>
                    <div class="spd-stat-value"><?php echo $projectStats["activeProjects"] ?? 0; ?></div>
                    <div class="spd-stat-sub">In progress</div>
                </div>

                <div class="spd-stat-card">
                    <div class="spd-stat-label">Completed Projects</div>
                    <div class="spd-stat-value"><?php echo $projectStats["completedProjects"] ?? 0; ?></div>
                    <div class="spd-stat-sub">Successfully delivered</div>
                </div>

                <div class="spd-stat-card">
                    <div class="spd-stat-label">Total Contract Value</div>
                    <div class="spd-stat-value currency"><?php echo number_format($projectStats["totalContractValue"] ?? 0, 0); ?></div>
                    <div class="spd-stat-sub">All projects</div>
                </div>

                <div class="spd-stat-card">
                    <div class="spd-stat-label">Monthly Revenue</div>
                    <div class="spd-stat-value" style="color: #22c55e;"><?php echo number_format($paymentStats["monthlyRevenue"] ?? 0, 0); ?></div>
                    <div class="spd-stat-sub"><?php echo date("F Y"); ?></div>
                </div>
            </div>

            <!-- Payment Stats -->
            <div class="spd-stats-grid">
                <div class="spd-stat-card">
                    <div class="spd-stat-label">Total Payments</div>
                    <div class="spd-stat-value"><?php echo number_format($paymentStats["totalPayments"] ?? 0, 0); ?></div>
                    <div class="spd-stat-sub">All time revenue</div>
                </div>

                <div class="spd-stat-card">
                    <div class="spd-stat-label">Advance Payments</div>
                    <div class="spd-stat-value"><?php echo number_format($paymentStats["totalAdvance"] ?? 0, 0); ?></div>
                    <div class="spd-stat-sub">Upfront collected</div>
                </div>

                <div class="spd-stat-card">
                    <div class="spd-stat-label">Milestone Payments</div>
                    <div class="spd-stat-value"><?php echo number_format($paymentStats["totalMilestone"] ?? 0, 0); ?></div>
                    <div class="spd-stat-sub">Progress payments</div>
                </div>

                <div class="spd-stat-card">
                    <div class="spd-stat-label">Final Payments</div>
                    <div class="spd-stat-value"><?php echo number_format($paymentStats["totalFinal"] ?? 0, 0); ?></div>
                    <div class="spd-stat-sub">Project completion</div>
                </div>
            </div>

            <!-- Profitability Overview -->
            <div class="spd-section" style="margin-bottom: 28px;">
                <div class="spd-section-header">
                    <div class="spd-section-title">Profitability Overview</div>
                    <a href="<?php echo ADMINURL; ?>/sky-padel-report-list/" class="spd-action-btn primary">View Full Report</a>
                </div>
                <div class="spd-profit-grid">
                    <div class="spd-profit-card">
                        <div class="spd-profit-icon" style="background: rgba(13, 148, 136, 0.1); color: #0d9488;">
                            <i class="fa fa-chart-line"></i>
                        </div>
                        <div class="spd-profit-data">
                            <div class="spd-profit-label">Total Revenue</div>
                            <div class="spd-profit-value"><?php echo number_format($profitSummary["totalRevenue"], 0); ?></div>
                        </div>
                    </div>
                    <div class="spd-profit-card">
                        <div class="spd-profit-icon" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;">
                            <i class="fa fa-check-circle"></i>
                        </div>
                        <div class="spd-profit-data">
                            <div class="spd-profit-label">Received</div>
                            <div class="spd-profit-value" style="color: #22c55e;"><?php echo number_format($profitSummary["totalReceived"], 0); ?></div>
                            <div class="spd-profit-sub"><?php echo number_format($profitSummary["avgCollectionRate"], 1); ?>% collected</div>
                        </div>
                    </div>
                    <div class="spd-profit-card">
                        <div class="spd-profit-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                            <i class="fa fa-clock"></i>
                        </div>
                        <div class="spd-profit-data">
                            <div class="spd-profit-label">Outstanding</div>
                            <div class="spd-profit-value" style="color: #ef4444;"><?php echo number_format($profitSummary["totalOutstanding"], 0); ?></div>
                            <div class="spd-profit-sub">Pending collection</div>
                        </div>
                    </div>
                    <div class="spd-profit-card">
                        <div class="spd-profit-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                            <i class="fa fa-boxes"></i>
                        </div>
                        <div class="spd-profit-data">
                            <div class="spd-profit-label">Total Costs</div>
                            <div class="spd-profit-value"><?php echo number_format($profitSummary["totalCost"], 0); ?></div>
                            <div class="spd-profit-sub">Material: <?php echo number_format($profitSummary["totalMaterialCost"], 0); ?> | Expenses: <?php echo number_format($profitSummary["totalExpenseCost"], 0); ?></div>
                        </div>
                    </div>
                    <div class="spd-profit-card highlight">
                        <div class="spd-profit-icon" style="background: rgba(255, 255, 255, 0.2); color: #fff;">
                            <i class="fa fa-rupee-sign"></i>
                        </div>
                        <div class="spd-profit-data">
                            <div class="spd-profit-label">Gross Profit</div>
                            <div class="spd-profit-value"><?php echo number_format($profitSummary["totalProfit"], 0); ?></div>
                            <div class="spd-profit-sub"><?php echo number_format($profitSummary["avgProfitMargin"], 1); ?>% avg margin</div>
                        </div>
                    </div>
                </div>
                <?php if (!empty($monthlyTrend)):
                    $maxRevenue = max(array_column($monthlyTrend, 'revenue')) ?: 1;
                ?>
                <div class="spd-mini-chart">
                    <div class="spd-mini-chart-title">Revenue Trend (Last 6 Months)</div>
                    <div class="spd-mini-bars">
                        <?php foreach ($monthlyTrend as $m):
                            $height = ($m["revenue"] / $maxRevenue) * 100;
                        ?>
                        <div class="spd-mini-bar-item">
                            <div class="spd-mini-bar" style="height: <?php echo max(5, $height); ?>%;"></div>
                            <div class="spd-mini-bar-label"><?php echo date("M", strtotime($m["month"] . "-01")); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Two Column: Visits & Payments -->
            <div class="spd-two-col">
                <!-- Upcoming Site Visits -->
                <div class="spd-section">
                    <div class="spd-section-header">
                        <div class="spd-section-title">Upcoming Site Visits</div>
                        <span class="spd-section-badge"><?php echo count($upcomingVisits); ?> scheduled</span>
                    </div>
                    <div class="spd-section-body">
                        <?php if (count($upcomingVisits) > 0) { ?>
                            <table class="spd-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Lead</th>
                                        <th>Client</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcomingVisits as $visit) { ?>
                                        <tr>
                                            <td class="mono"><?php echo date("d M", strtotime($visit["visitDate"])); ?></td>
                                            <td class="mono"><?php echo date("h:i A", strtotime($visit["visitTime"])); ?></td>
                                            <td><a href="#"><?php echo $visit["leadNo"]; ?></a></td>
                                            <td><?php echo $visit["clientName"]; ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        <?php } else { ?>
                            <div class="spd-empty">
                                <div class="spd-empty-icon">&#128197;</div>
                                No upcoming site visits scheduled
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- Recent Payments -->
                <div class="spd-section">
                    <div class="spd-section-header">
                        <div class="spd-section-title">Recent Payments</div>
                        <a href="<?php echo ADMINURL; ?>/sky-padel-payment-list/" class="spd-action-btn">View All</a>
                    </div>
                    <div class="spd-section-body">
                        <?php if (count($recentPayments) > 0) { ?>
                            <table class="spd-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Quotation</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentPayments as $payment) { ?>
                                        <tr>
                                            <td class="mono"><?php echo date("d M", strtotime($payment["paymentDate"])); ?></td>
                                            <td><?php echo $payment["quotationNo"] ?? "-"; ?></td>
                                            <td>
                                                <span class="spd-payment-type <?php echo strtolower($payment["paymentType"]); ?>">
                                                    <?php echo $payment["paymentType"]; ?>
                                                </span>
                                            </td>
                                            <td class="amount"><?php echo number_format($payment["paidAmount"], 0); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        <?php } else { ?>
                            <div class="spd-empty">
                                <div class="spd-empty-icon">&#128176;</div>
                                No recent payments recorded
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <!-- Active Projects -->
            <div class="spd-section spd-projects">
                <div class="spd-section-header">
                    <div class="spd-section-title">Active Projects</div>
                    <div class="spd-quick-actions">
                        <a href="<?php echo ADMINURL; ?>/sky-padel-project-list/" class="spd-action-btn">View All</a>
                        <a href="<?php echo ADMINURL; ?>/sky-padel-project-add/" class="spd-action-btn primary">+ New Project</a>
                    </div>
                </div>
                <div class="spd-section-body">
                    <?php if (count($activeProjects) > 0) { ?>
                        <table class="spd-table">
                            <thead>
                                <tr>
                                    <th>Project</th>
                                    <th>Client</th>
                                    <th>Start Date</th>
                                    <th>Expected End</th>
                                    <th>Contract Value</th>
                                    <th>Status</th>
                                    <th style="text-align: center;">Timeline</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activeProjects as $project) { ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo ADMINURL; ?>/sky-padel-project-edit/?id=<?php echo $project["projectID"]; ?>">
                                                <?php echo $project["projectName"]; ?>
                                            </a>
                                        </td>
                                        <td><?php echo $project["clientName"]; ?></td>
                                        <td class="mono"><?php echo date("d M Y", strtotime($project["startDate"])); ?></td>
                                        <td class="mono">
                                            <?php echo $project["expectedEndDate"] != "0000-00-00" ? date("d M Y", strtotime($project["expectedEndDate"])) : "-"; ?>
                                        </td>
                                        <td class="amount"><?php echo number_format($project["contractAmount"], 0); ?></td>
                                        <td>
                                            <span class="spd-badge active">Active</span>
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="<?php echo ADMINURL; ?>/sky-padel-project-gantt/?id=<?php echo $project["projectID"]; ?>" class="spd-gantt-btn" title="View Gantt Chart">
                                                <i class="fa fa-project-diagram"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    <?php } else { ?>
                        <div class="spd-empty">
                            <div class="spd-empty-icon">&#128679;</div>
                            No active projects at the moment
                        </div>
                    <?php } ?>
                </div>
            </div>

            <!-- Lead Funnel -->
            <div class="spd-section spd-funnel">
                <div class="spd-section-header">
                    <div class="spd-section-title">Lead Funnel</div>
                    <a href="<?php echo ADMINURL; ?>/sky-padel-lead-list/" class="spd-action-btn">Manage Leads</a>
                </div>
                <div class="spd-funnel-grid">
                    <div class="spd-funnel-item">
                        <div class="spd-funnel-num"><?php echo $leadStats["newLeads"] ?? 0; ?></div>
                        <div class="spd-funnel-label">New Leads</div>
                        <span class="spd-funnel-arrow">&#8594;</span>
                    </div>
                    <div class="spd-funnel-item">
                        <div class="spd-funnel-num"><?php echo $leadStats["contactedLeads"] ?? 0; ?></div>
                        <div class="spd-funnel-label">Contacted</div>
                        <span class="spd-funnel-arrow">&#8594;</span>
                    </div>
                    <div class="spd-funnel-item">
                        <div class="spd-funnel-num"><?php echo $leadStats["scheduledVisits"] ?? 0; ?></div>
                        <div class="spd-funnel-label">Visits Scheduled</div>
                        <span class="spd-funnel-arrow">&#8594;</span>
                    </div>
                    <div class="spd-funnel-item">
                        <div class="spd-funnel-num"><?php echo $leadStats["completedVisits"] ?? 0; ?></div>
                        <div class="spd-funnel-label">Visits Done</div>
                        <span class="spd-funnel-arrow">&#8594;</span>
                    </div>
                    <div class="spd-funnel-item">
                        <div class="spd-funnel-num"><?php echo $leadStats["quotationsSent"] ?? 0; ?></div>
                        <div class="spd-funnel-label">Quotations Sent</div>
                        <span class="spd-funnel-arrow">&#8594;</span>
                    </div>
                    <div class="spd-funnel-item">
                        <div class="spd-funnel-num"><?php echo $leadStats["quotationsApproved"] ?? 0; ?></div>
                        <div class="spd-funnel-label">Converted</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
