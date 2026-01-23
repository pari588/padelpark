<?php
/*
Sky Padel Reports Module - Core Functions
Profitability and analytics reports
*/

/**
 * Get project profitability data
 */
function getProjectProfitability($filters = array())
{
    global $DB;

    $where = "WHERE p.status = 1";
    $vals = array();
    $types = "";

    // Apply filters
    if (!empty($filters["projectStatus"])) {
        $where .= " AND p.projectStatus = ?";
        $vals[] = $filters["projectStatus"];
        $types .= "s";
    }

    if (!empty($filters["dateFrom"])) {
        $where .= " AND p.startDate >= ?";
        $vals[] = $filters["dateFrom"];
        $types .= "s";
    }

    if (!empty($filters["dateTo"])) {
        $where .= " AND (p.expectedEndDate <= ? OR p.actualEndDate <= ?)";
        $vals[] = $filters["dateTo"];
        $vals[] = $filters["dateTo"];
        $types .= "ss";
    }

    if (!empty($filters["siteCity"])) {
        $where .= " AND p.siteCity LIKE CONCAT('%', ?, '%')";
        $vals[] = $filters["siteCity"];
        $types .= "s";
    }

    $DB->vals = $vals;
    $DB->types = $types;
    $DB->sql = "SELECT
                    p.projectID,
                    p.projectNo,
                    p.projectName,
                    p.clientName,
                    p.siteCity,
                    p.contractAmount,
                    p.totalCost,
                    p.profitAmount,
                    p.projectStatus,
                    p.startDate,
                    p.expectedEndDate,
                    p.actualEndDate,
                    COALESCE((SELECT SUM(paidAmount) FROM " . $DB->pre . "sky_padel_payment WHERE projectID = p.projectID AND status = 1), 0) as totalReceived,
                    COALESCE((SELECT SUM(totalValue) FROM " . $DB->pre . "stock_allocation WHERE projectID = p.projectID AND status = 1), 0) as materialCost,
                    COALESCE((SELECT SUM(totalAmount) FROM " . $DB->pre . "sky_padel_project_expense WHERE projectID = p.projectID AND status = 1), 0) as expenseCost,
                    COALESCE((SELECT SUM(totalAmount) FROM " . $DB->pre . "sky_padel_project_expense WHERE projectID = p.projectID AND status = 1 AND expenseCategory = 'Labor'), 0) as laborCost,
                    COALESCE((SELECT SUM(totalAmount) FROM " . $DB->pre . "sky_padel_project_expense WHERE projectID = p.projectID AND status = 1 AND expenseCategory = 'Transport'), 0) as transportCost,
                    COALESCE((SELECT SUM(totalAmount) FROM " . $DB->pre . "sky_padel_project_expense WHERE projectID = p.projectID AND status = 1 AND expenseCategory NOT IN ('Labor','Transport')), 0) as otherExpenses
                FROM " . $DB->pre . "sky_padel_project p
                " . $where . "
                ORDER BY p.projectID DESC";

    $DB->dbRows();
    $projects = $DB->rows;

    // Calculate totals and additional metrics
    foreach ($projects as &$p) {
        // Calculate actual profit including all expenses
        $revenue = floatval($p["contractAmount"]);

        // Material costs from stock allocations
        $materialCost = floatval($p["materialCost"]);

        // Expense tracking costs
        $expenseCost = floatval($p["expenseCost"] ?? 0);
        $laborCost = floatval($p["laborCost"] ?? 0);
        $transportCost = floatval($p["transportCost"] ?? 0);
        $otherExpenses = floatval($p["otherExpenses"] ?? 0);

        // Total cost = material allocation + all tracked expenses
        $totalCost = $materialCost + $expenseCost;

        $p["revenue"] = $revenue;
        $p["materialCost"] = $materialCost;
        $p["expenseCost"] = $expenseCost;
        $p["laborCost"] = $laborCost;
        $p["transportCost"] = $transportCost;
        $p["otherCost"] = $otherExpenses;
        $p["totalCost"] = $totalCost;
        $p["grossProfit"] = $revenue - $totalCost;
        $p["profitMargin"] = $revenue > 0 ? (($revenue - $totalCost) / $revenue) * 100 : 0;
        $p["collectionRate"] = $revenue > 0 ? (floatval($p["totalReceived"]) / $revenue) * 100 : 0;
        $p["outstanding"] = $revenue - floatval($p["totalReceived"]);
    }

    return $projects;
}

/**
 * Get summary statistics
 */
function getProfitabilitySummary($projects)
{
    $summary = array(
        "totalProjects" => count($projects),
        "activeProjects" => 0,
        "completedProjects" => 0,
        "totalRevenue" => 0,
        "totalReceived" => 0,
        "totalOutstanding" => 0,
        "totalMaterialCost" => 0,
        "totalExpenseCost" => 0,
        "totalLaborCost" => 0,
        "totalTransportCost" => 0,
        "totalOtherCost" => 0,
        "totalCost" => 0,
        "totalProfit" => 0,
        "avgProfitMargin" => 0,
        "avgCollectionRate" => 0
    );

    $profitMargins = array();
    $collectionRates = array();

    foreach ($projects as $p) {
        $summary["totalRevenue"] += $p["revenue"];
        $summary["totalReceived"] += floatval($p["totalReceived"]);
        $summary["totalOutstanding"] += $p["outstanding"];
        $summary["totalMaterialCost"] += $p["materialCost"];
        $summary["totalExpenseCost"] += floatval($p["expenseCost"] ?? 0);
        $summary["totalLaborCost"] += floatval($p["laborCost"] ?? 0);
        $summary["totalTransportCost"] += floatval($p["transportCost"] ?? 0);
        $summary["totalOtherCost"] += floatval($p["otherCost"] ?? 0);
        $summary["totalCost"] += $p["totalCost"];
        $summary["totalProfit"] += $p["grossProfit"];

        if ($p["revenue"] > 0) {
            $profitMargins[] = $p["profitMargin"];
            $collectionRates[] = $p["collectionRate"];
        }

        if ($p["projectStatus"] == "Active") $summary["activeProjects"]++;
        if ($p["projectStatus"] == "Completed") $summary["completedProjects"]++;
    }

    if (count($profitMargins) > 0) {
        $summary["avgProfitMargin"] = array_sum($profitMargins) / count($profitMargins);
    }
    if (count($collectionRates) > 0) {
        $summary["avgCollectionRate"] = array_sum($collectionRates) / count($collectionRates);
    }

    return $summary;
}

/**
 * Get monthly revenue trend
 */
function getMonthlyRevenueTrend($months = 12)
{
    global $DB;

    $DB->sql = "SELECT
                    DATE_FORMAT(startDate, '%Y-%m') as month,
                    COUNT(*) as projectCount,
                    SUM(contractAmount) as revenue,
                    SUM(totalCost) as cost,
                    SUM(contractAmount - COALESCE(totalCost, 0)) as profit
                FROM " . $DB->pre . "sky_padel_project
                WHERE status = 1 AND startDate >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                GROUP BY DATE_FORMAT(startDate, '%Y-%m')
                ORDER BY month ASC";
    $DB->vals = array($months);
    $DB->types = "i";
    $DB->dbRows();

    return $DB->rows;
}

/**
 * Get city-wise performance
 */
function getCityWisePerformance()
{
    global $DB;

    $DB->sql = "SELECT
                    siteCity as city,
                    COUNT(*) as projectCount,
                    SUM(contractAmount) as totalRevenue,
                    SUM(COALESCE(totalCost, 0)) as totalCost,
                    SUM(contractAmount - COALESCE(totalCost, 0)) as totalProfit,
                    AVG((contractAmount - COALESCE(totalCost, 0)) / NULLIF(contractAmount, 0) * 100) as avgMargin
                FROM " . $DB->pre . "sky_padel_project
                WHERE status = 1 AND siteCity IS NOT NULL AND siteCity != ''
                GROUP BY siteCity
                ORDER BY totalRevenue DESC
                LIMIT 10";
    $DB->dbRows();

    return $DB->rows;
}

// Only run module setup when loaded as main module (not when included from other files)
if (basename($_SERVER["SCRIPT_FILENAME"]) !== "index.php" && isset($_POST["xAction"])) {
    // Handle AJAX requests
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "GET_PROFITABILITY":
                $filters = array(
                    "projectStatus" => $_POST["projectStatus"] ?? "",
                    "dateFrom" => $_POST["dateFrom"] ?? "",
                    "dateTo" => $_POST["dateTo"] ?? "",
                    "siteCity" => $_POST["siteCity"] ?? ""
                );
                $projects = getProjectProfitability($filters);
                $summary = getProfitabilitySummary($projects);
                setResponse(array("err" => 0, "projects" => $projects, "summary" => $summary));
                break;
        }
    }
    echo json_encode($MXRES);
} elseif (!defined("SKY_PADEL_REPORT_INCLUDED")) {
    define("SKY_PADEL_REPORT_INCLUDED", true);
    if (function_exists("setModVars") && strpos($_SERVER["REQUEST_URI"], "sky-padel-report") !== false) {
        setModVars(array("TBL" => "sky_padel_project", "PK" => "projectID"));
    }
}
?>
