<?php
/*
Sky Padel Project Expenses Module
Track all expenses per project for accurate P&L
*/

if (function_exists("setModVars")) {
    setModVars(array("TBL" => "sky_padel_project_expense", "PK" => "expenseID"));
}

$GLOBALS["EXPENSE_CATEGORIES"] = array(
    "Material" => "Material & Supplies",
    "Labor" => "Labor & Wages",
    "Transport" => "Transport & Logistics",
    "Equipment" => "Equipment Rental",
    "Subcontractor" => "Subcontractor",
    "Permits" => "Permits & Fees",
    "Utilities" => "Utilities",
    "Other" => "Other"
);

/**
 * Get project dropdown for expense form
 */
function getProjectDropdownForExpense($selectedID = 0)
{
    global $DB;

    $DB->sql = "SELECT projectID, projectNo, projectName, clientName
                FROM " . $DB->pre . "sky_padel_project
                WHERE status = 1
                ORDER BY projectID DESC";
    $DB->dbRows();

    $opt = '<option value="">Select Project</option>';
    foreach ($DB->rows as $p) {
        $sel = ($selectedID == $p["projectID"]) ? ' selected="selected"' : '';
        $opt .= '<option value="' . $p["projectID"] . '"' . $sel . '>'
              . htmlspecialchars($p["projectNo"] . ' - ' . $p["projectName"])
              . '</option>';
    }

    return $opt;
}

/**
 * Get expenses for a project
 */
function getProjectExpenses($projectID)
{
    global $DB;

    $DB->vals = array($projectID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM " . $DB->pre . "sky_padel_project_expense
                WHERE projectID = ? AND status = ?
                ORDER BY expenseDate DESC, expenseID DESC";
    $DB->dbRows();

    return $DB->rows;
}

/**
 * Get expense summary by category for a project
 */
function getProjectExpenseSummary($projectID)
{
    global $DB;

    $DB->vals = array($projectID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT
                    expenseCategory,
                    COUNT(*) as count,
                    SUM(totalAmount) as total,
                    SUM(paidAmount) as paid
                FROM " . $DB->pre . "sky_padel_project_expense
                WHERE projectID = ? AND status = ?
                GROUP BY expenseCategory";
    $DB->dbRows();

    return $DB->rows;
}

/**
 * Get total expenses for a project
 */
function getProjectTotalExpenses($projectID)
{
    global $DB;

    $DB->vals = array($projectID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT
                    SUM(totalAmount) as totalExpenses,
                    SUM(paidAmount) as paidAmount,
                    SUM(totalAmount - paidAmount) as pendingAmount
                FROM " . $DB->pre . "sky_padel_project_expense
                WHERE projectID = ? AND status = ?";
    $DB->dbRow();

    return $DB->row ?: array("totalExpenses" => 0, "paidAmount" => 0, "pendingAmount" => 0);
}

/**
 * Update project's totalCost based on expenses
 */
function updateProjectTotalCost($projectID)
{
    global $DB;

    $expenses = getProjectTotalExpenses($projectID);
    $totalCost = floatval($expenses["totalExpenses"]);

    // Also add material cost from stock allocations
    $DB->vals = array($projectID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT SUM(totalValue) as materialCost
                FROM " . $DB->pre . "stock_allocation
                WHERE projectID = ? AND status = ?";
    $DB->dbRow();
    $materialCost = floatval($DB->row["materialCost"] ?? 0);

    $totalCost += $materialCost;

    // Update project
    $DB->vals = array($totalCost, $projectID);
    $DB->types = "di";
    $DB->sql = "UPDATE " . $DB->pre . "sky_padel_project SET totalCost = ? WHERE projectID = ?";
    $DB->dbQuery();

    return $totalCost;
}

// Handle AJAX requests
if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();

    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "GET_PROJECT_EXPENSES":
                $projectID = intval($_POST["projectID"]);
                $expenses = getProjectExpenses($projectID);
                $summary = getProjectExpenseSummary($projectID);
                $totals = getProjectTotalExpenses($projectID);
                setResponse(array("err" => 0, "expenses" => $expenses, "summary" => $summary, "totals" => $totals));
                break;

            case "SAVE_EXPENSE":
                $data = array(
                    "projectID" => intval($_POST["projectID"]),
                    "expenseDate" => $_POST["expenseDate"],
                    "expenseCategory" => $_POST["expenseCategory"],
                    "description" => $_POST["description"] ?? "",
                    "vendorName" => $_POST["vendorName"] ?? "",
                    "invoiceNo" => $_POST["invoiceNo"] ?? "",
                    "amount" => floatval($_POST["amount"]),
                    "taxAmount" => floatval($_POST["taxAmount"] ?? 0),
                    "totalAmount" => floatval($_POST["totalAmount"]),
                    "paymentStatus" => $_POST["paymentStatus"] ?? "Pending",
                    "paidAmount" => floatval($_POST["paidAmount"] ?? 0),
                    "notes" => $_POST["notes"] ?? ""
                );

                $expenseID = intval($_POST["expenseID"] ?? 0);

                if ($expenseID > 0) {
                    // Update
                    $DB->vals = array_values($data);
                    $DB->vals[] = $expenseID;
                    $DB->types = "isssssdddsds" . "i";
                    $DB->sql = "UPDATE " . $DB->pre . "sky_padel_project_expense SET
                                projectID=?, expenseDate=?, expenseCategory=?, description=?,
                                vendorName=?, invoiceNo=?, amount=?, taxAmount=?, totalAmount=?,
                                paymentStatus=?, paidAmount=?, notes=?
                                WHERE expenseID=?";
                    $DB->dbQuery();
                } else {
                    // Insert
                    $data["createdBy"] = $_SESSION[SITEURL]["MXUID"];
                    $DB->vals = array_values($data);
                    $DB->types = "isssssdddsds" . "i";
                    $DB->sql = "INSERT INTO " . $DB->pre . "sky_padel_project_expense
                                (projectID, expenseDate, expenseCategory, description,
                                vendorName, invoiceNo, amount, taxAmount, totalAmount,
                                paymentStatus, paidAmount, notes, createdBy)
                                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
                    $DB->dbQuery();
                    $expenseID = $DB->insertID;
                }

                // Update project total cost
                updateProjectTotalCost($data["projectID"]);

                setResponse(array("err" => 0, "msg" => "Expense saved", "expenseID" => $expenseID));
                break;

            case "DELETE_EXPENSE":
                $expenseID = intval($_POST["expenseID"]);

                // Get projectID first
                $DB->vals = array($expenseID);
                $DB->types = "i";
                $DB->sql = "SELECT projectID FROM " . $DB->pre . "sky_padel_project_expense WHERE expenseID = ?";
                $DB->dbRow();
                $projectID = intval($DB->row["projectID"] ?? 0);

                // Soft delete
                $DB->vals = array(0, $expenseID);
                $DB->types = "ii";
                $DB->sql = "UPDATE " . $DB->pre . "sky_padel_project_expense SET status = ? WHERE expenseID = ?";
                $DB->dbQuery();

                // Update project total cost
                if ($projectID > 0) {
                    updateProjectTotalCost($projectID);
                }

                setResponse(array("err" => 0, "msg" => "Expense deleted"));
                break;
        }
    }
    echo json_encode($MXRES);
    exit;
}
?>
