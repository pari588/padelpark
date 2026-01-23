<?php
/*
Stock Allocation Module - Core Functions
Allocate inventory items to projects from warehouse
*/

/**
 * Generate unique allocation number: SA-YYYYMMDD-XXXX
 */
function generateAllocationNo()
{
    global $DB;
    $prefix = "SA-" . date("Ymd") . "-";

    $DB->sql = "SELECT allocationNo FROM " . $DB->pre . "stock_allocation
                WHERE allocationNo LIKE ? ORDER BY allocationID DESC LIMIT 1";
    $DB->vals = array($prefix . "%");
    $DB->types = "s";
    $last = $DB->dbRow();

    if ($last && !empty($last["allocationNo"])) {
        $parts = explode("-", $last["allocationNo"]);
        $seq = intval(end($parts)) + 1;
    } else {
        $seq = 1;
    }

    return $prefix . str_pad($seq, 4, "0", STR_PAD_LEFT);
}

/**
 * Add new stock allocation
 */
function addAllocation()
{
    global $DB;

    $projectID = intval($_POST["projectID"]);
    $warehouseID = intval($_POST["warehouseID"]);

    if (!$projectID || !$warehouseID) {
        setResponse(array("err" => 1, "errMsg" => "Project and Warehouse are required"));
        return;
    }

    $allocationNo = generateAllocationNo();

    // Create allocation header
    $DB->table = $DB->pre . "stock_allocation";
    $DB->data = array(
        "allocationNo" => $allocationNo,
        "projectID" => $projectID,
        "warehouseID" => $warehouseID,
        "allocationDate" => $_POST["allocationDate"] ?? date("Y-m-d"),
        "allocationType" => "Reserved",
        "notes" => $_POST["notes"] ?? "",
        "createdBy" => $_SESSION["mxAdminUserID"] ?? 0
    );

    if (!$DB->dbInsert()) {
        setResponse(array("err" => 1, "errMsg" => "Failed to create allocation"));
        return;
    }

    $allocationID = $DB->insertID;

    // Add items if provided
    if (isset($_POST["items"]) && is_array($_POST["items"])) {
        $totalItems = 0;
        $totalQty = 0;
        $totalValue = 0;

        foreach ($_POST["items"] as $item) {
            $productID = intval($item["productID"]);
            $qty = floatval($item["qty"]);

            if ($productID <= 0 || $qty <= 0) continue;

            // Get product details
            $DB->vals = array($productID);
            $DB->types = "i";
            $DB->sql = "SELECT productName, productSKU, unit, costPrice FROM " . $DB->pre . "retail_product WHERE productID=?";
            $product = $DB->dbRow();

            if (!$product) continue;

            $unitCost = floatval($item["unitCost"] ?? $product["costPrice"] ?? 0);
            $totalCost = $qty * $unitCost;

            // Check available stock
            $DB->vals = array($warehouseID, $productID);
            $DB->types = "ii";
            $DB->sql = "SELECT availableQty FROM " . $DB->pre . "inventory_stock WHERE warehouseID=? AND productID=?";
            $stock = $DB->dbRow();

            if (!$stock || $stock["availableQty"] < $qty) {
                // Skip if not enough stock, but log it
                continue;
            }

            // Insert allocation item
            $DB->table = $DB->pre . "stock_allocation_item";
            $DB->data = array(
                "allocationID" => $allocationID,
                "productID" => $productID,
                "productName" => $product["productName"],
                "productSKU" => $product["productSKU"],
                "unit" => $product["unit"],
                "allocatedQty" => $qty,
                "unitCost" => $unitCost,
                "totalCost" => $totalCost,
                "lotNumber" => $item["lotNumber"] ?? "",
                "notes" => $item["notes"] ?? ""
            );
            $DB->dbInsert();

            // Reserve stock in inventory
            $DB->vals = array($qty, $qty, $warehouseID, $productID);
            $DB->types = "ddii";
            $DB->sql = "UPDATE " . $DB->pre . "inventory_stock
                        SET reservedQty = reservedQty + ?, availableQty = availableQty - ?
                        WHERE warehouseID=? AND productID=?";
            $DB->dbQuery();

            // Add ledger entry
            addStockLedgerEntry($productID, $warehouseID, "Reserved", $allocationID, $allocationNo, 0, $qty, $unitCost, "Stock reserved for allocation");

            $totalItems++;
            $totalQty += $qty;
            $totalValue += $totalCost;
        }

        // Update allocation totals
        $DB->vals = array($totalItems, $totalQty, $totalValue, $allocationID);
        $DB->types = "iddi";
        $DB->sql = "UPDATE " . $DB->pre . "stock_allocation SET totalItems=?, totalQty=?, totalValue=? WHERE allocationID=?";
        $DB->dbQuery();
    }

    setResponse(array("err" => 0, "allocationID" => $allocationID, "allocationNo" => $allocationNo, "param" => "id=$allocationID"));
}

/**
 * Update stock allocation
 */
function updateAllocation()
{
    global $DB;
    $allocationID = intval($_POST["allocationID"]);

    // Get existing allocation
    $DB->vals = array($allocationID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "stock_allocation WHERE allocationID=?";
    $allocation = $DB->dbRow();

    if (!$allocation) {
        setResponse(array("err" => 1, "errMsg" => "Allocation not found"));
        return;
    }

    if ($allocation["allocationType"] == "Dispatched") {
        setResponse(array("err" => 1, "errMsg" => "Cannot modify dispatched allocation"));
        return;
    }

    // Update header
    $DB->vals = array(
        $_POST["allocationDate"] ?? $allocation["allocationDate"],
        $_POST["notes"] ?? $allocation["notes"],
        $_POST["vehicleNo"] ?? "",
        $_POST["driverName"] ?? "",
        $_POST["driverPhone"] ?? "",
        $_POST["ewayBillNo"] ?? "",
        $allocationID
    );
    $DB->types = "ssssssi";
    $DB->sql = "UPDATE " . $DB->pre . "stock_allocation SET
                allocationDate=?, notes=?, vehicleNo=?, driverName=?, driverPhone=?, ewayBillNo=?
                WHERE allocationID=?";
    $DB->dbQuery();

    setResponse(array("err" => 0, "param" => "id=$allocationID"));
}

/**
 * Dispatch allocation (send items to project site)
 */
function dispatchAllocation()
{
    global $DB;
    $allocationID = intval($_POST["allocationID"]);

    // Get allocation
    $DB->vals = array($allocationID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "stock_allocation WHERE allocationID=? AND status=1";
    $allocation = $DB->dbRow();

    if (!$allocation) {
        setResponse(array("err" => 1, "errMsg" => "Allocation not found"));
        return;
    }

    if ($allocation["allocationType"] == "Dispatched") {
        setResponse(array("err" => 1, "errMsg" => "Already dispatched"));
        return;
    }

    // Get allocation items
    $DB->vals = array($allocationID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "stock_allocation_item WHERE allocationID=? AND status=1";
    $DB->dbRows();
    $items = $DB->rows;

    // Update each item's dispatched qty and inventory
    foreach ($items as $item) {
        $dispatchQty = $item["allocatedQty"];

        // Update item dispatched qty
        $DB->vals = array($dispatchQty, $item["itemID"]);
        $DB->types = "di";
        $DB->sql = "UPDATE " . $DB->pre . "stock_allocation_item SET dispatchedQty=? WHERE itemID=?";
        $DB->dbQuery();

        // Move from reserved to in-transit in inventory
        $DB->vals = array($dispatchQty, $dispatchQty, $allocation["warehouseID"], $item["productID"]);
        $DB->types = "ddii";
        $DB->sql = "UPDATE " . $DB->pre . "inventory_stock
                    SET reservedQty = reservedQty - ?, inTransitQty = inTransitQty + ?
                    WHERE warehouseID=? AND productID=?";
        $DB->dbQuery();

        // Add ledger entry for dispatch
        addStockLedgerEntry(
            $item["productID"],
            $allocation["warehouseID"],
            "Transfer-Out",
            $allocationID,
            $allocation["allocationNo"],
            0,
            $dispatchQty,
            $item["unitCost"],
            "Dispatched to project site"
        );
    }

    // Update allocation status
    $DB->vals = array(
        "Dispatched",
        date("Y-m-d"),
        $_SESSION["mxAdminUserID"] ?? 0,
        $_POST["vehicleNo"] ?? "",
        $_POST["driverName"] ?? "",
        $_POST["driverPhone"] ?? "",
        $_POST["ewayBillNo"] ?? "",
        $allocationID
    );
    $DB->types = "ssissssi";
    $DB->sql = "UPDATE " . $DB->pre . "stock_allocation SET
                allocationType='Dispatched', dispatchDate=?, dispatchedBy=?,
                vehicleNo=?, driverName=?, driverPhone=?, ewayBillNo=?
                WHERE allocationID=?";
    $DB->dbQuery();

    setResponse(array("err" => 0, "msg" => "Allocation dispatched successfully"));
}

/**
 * Add stock ledger entry
 */
function addStockLedgerEntry($productID, $warehouseID, $transactionType, $refID, $refNo, $qtyIn, $qtyOut, $unitCost, $notes = "")
{
    global $DB;

    // Get current balance
    $DB->vals = array($warehouseID, $productID);
    $DB->types = "ii";
    $DB->sql = "SELECT availableQty FROM " . $DB->pre . "inventory_stock WHERE warehouseID=? AND productID=?";
    $stock = $DB->dbRow();
    $balance = $stock ? $stock["availableQty"] : 0;

    $DB->table = $DB->pre . "stock_ledger";
    $DB->data = array(
        "productID" => $productID,
        "warehouseID" => $warehouseID,
        "transactionType" => $transactionType,
        "transactionDate" => date("Y-m-d H:i:s"),
        "referenceType" => "StockAllocation",
        "referenceID" => $refID,
        "referenceNumber" => $refNo,
        "qtyIn" => $qtyIn,
        "qtyOut" => $qtyOut,
        "balanceQty" => $balance,
        "unitCost" => $unitCost,
        "transactionValue" => ($qtyIn - $qtyOut) * $unitCost,
        "createdBy" => $_SESSION["mxAdminUserID"] ?? 0,
        "notes" => $notes
    );
    $DB->dbInsert();
}

/**
 * Get allocation details
 */
function getAllocationDetails($allocationID)
{
    global $DB;

    $DB->vals = array($allocationID);
    $DB->types = "i";
    $DB->sql = "SELECT a.*, p.projectNo, p.projectName, p.clientName, p.siteCity,
                       w.warehouseCode, w.warehouseName
                FROM " . $DB->pre . "stock_allocation a
                LEFT JOIN " . $DB->pre . "sky_padel_project p ON a.projectID = p.projectID
                LEFT JOIN " . $DB->pre . "warehouse w ON a.warehouseID = w.warehouseID
                WHERE a.allocationID = ?";
    $allocation = $DB->dbRow();

    if ($allocation) {
        // Get items
        $DB->vals = array($allocationID);
        $DB->types = "i";
        $DB->sql = "SELECT * FROM " . $DB->pre . "stock_allocation_item WHERE allocationID=? AND status=1 ORDER BY itemID";
        $DB->dbRows();
        $allocation["items"] = $DB->rows;
    }

    return $allocation;
}

/**
 * Get project dropdown
 */
function getProjectDropdown($selectedID = 0)
{
    global $DB;

    $DB->vals = array(1);
    $DB->types = "i";
    $DB->sql = "SELECT projectID, projectNo, projectName, clientName, siteCity
                FROM " . $DB->pre . "sky_padel_project
                WHERE status=? AND projectStatus IN ('Active','Quoted')
                ORDER BY projectNo DESC";
    $DB->dbRows();

    $opt = '<option value="">Select Project</option>';
    foreach ($DB->rows as $p) {
        $sel = ($selectedID == $p["projectID"]) ? ' selected="selected"' : '';
        $opt .= '<option value="' . $p["projectID"] . '"' . $sel . '>'
              . htmlspecialchars($p["projectNo"] . ' - ' . $p["projectName"] . ' (' . $p["siteCity"] . ')')
              . '</option>';
    }

    return $opt;
}

/**
 * Get warehouse dropdown
 */
function getWarehouseDropdown($selectedID = 0)
{
    global $DB;

    $DB->vals = array(1);
    $DB->types = "i";
    $DB->sql = "SELECT warehouseID, warehouseCode, warehouseName, city
                FROM " . $DB->pre . "warehouse
                WHERE status=?
                ORDER BY warehouseName";
    $DB->dbRows();

    $opt = '<option value="">Select Warehouse</option>';
    foreach ($DB->rows as $w) {
        $sel = ($selectedID == $w["warehouseID"]) ? ' selected="selected"' : '';
        $opt .= '<option value="' . $w["warehouseID"] . '"' . $sel . '>'
              . htmlspecialchars($w["warehouseCode"] . ' - ' . $w["warehouseName"])
              . '</option>';
    }

    return $opt;
}

/**
 * Get warehouse products with stock
 */
function getWarehouseProducts()
{
    global $DB, $MXRES;
    $warehouseID = intval($_POST["warehouseID"] ?? $_GET["warehouseID"] ?? 0);

    if (!$warehouseID) {
        $MXRES["err"] = 1;
        $MXRES["msg"] = "Warehouse not specified";
        return;
    }

    $DB->vals = array($warehouseID);
    $DB->types = "i";
    $DB->sql = "SELECT s.*, p.productName, p.productSKU, p.uom as unit, p.basePrice as costPrice
                FROM " . $DB->pre . "inventory_stock s
                LEFT JOIN " . $DB->pre . "product p ON s.productID = p.productID
                WHERE s.warehouseID = ? AND s.availableQty > 0 AND s.status = 1
                ORDER BY p.productName";
    $DB->dbRows();

    $MXRES["err"] = 0;
    $MXRES["products"] = $DB->rows;
    $MXRES["msg"] = count($DB->rows) . " products found";
}

// Handle AJAX requests
if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");

    // For admin users, bypass JWT token validation and use session authentication
    $MXRES = mxCheckRequest(true, true); // login=true, ignoreToken=true

    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addAllocation(); break;
            case "UPDATE": updateAllocation(); break;
            case "DISPATCH": dispatchAllocation(); break;
            case "GET_PRODUCTS": getWarehouseProducts(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) {
        setModVars(array("TBL" => "stock_allocation", "PK" => "allocationID"));
    }
}
?>
