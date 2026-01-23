<?php
/*
Inventory Stock Module for Warehouse Management
- View stock levels by warehouse and product
- Stock adjustments (manual in/out)
- Stock transfers between warehouses
- Stock ledger for audit trail
*/

function addStockAdjustment()
{
    global $DB;

    $warehouseID = intval($_POST["warehouseID"]);
    $productID = intval($_POST["productID"]);
    $adjustmentType = $_POST["adjustmentType"]; // IN or OUT
    $quantity = floatval($_POST["quantity"]);
    $reason = trim($_POST["reason"] ?? "");
    $referenceNo = trim($_POST["referenceNo"] ?? "");

    if ($warehouseID < 1 || $productID < 1 || $quantity <= 0) {
        setResponse(array("err" => 1, "msg" => "Invalid data provided"));
        return;
    }

    // Get current stock for this product in this warehouse
    $DB->vals = array(1, $warehouseID, $productID);
    $DB->types = "iii";
    $DB->sql = "SELECT stockID, quantity, availableQty FROM " . $DB->pre . "inventory_stock WHERE status=? AND warehouseID=? AND productID=?";
    $currentStock = $DB->dbRow();

    $currentQty = floatval($currentStock["quantity"] ?? $currentStock["availableQty"] ?? 0);
    $stockID = $currentStock["stockID"] ?? 0;

    // Calculate new quantity
    if ($adjustmentType == "OUT") {
        if ($quantity > $currentQty) {
            setResponse(array("err" => 1, "msg" => "Insufficient stock. Current: " . $currentQty));
            return;
        }
        $newQty = $currentQty - $quantity;
        $qtyIn = 0;
        $qtyOut = $quantity;
    } else {
        $newQty = $currentQty + $quantity;
        $qtyIn = $quantity;
        $qtyOut = 0;
    }

    // Update or insert stock record
    if ($stockID > 0) {
        $DB->vals = array($newQty, $newQty, date("Y-m-d H:i:s"), $stockID);
        $DB->types = "ddsi";
        $DB->sql = "UPDATE " . $DB->pre . "inventory_stock SET quantity=?, availableQty=?, lastUpdated=? WHERE stockID=?";
        $DB->dbQuery();
    } else {
        $DB->table = $DB->pre . "inventory_stock";
        $DB->data = array(
            "warehouseID" => $warehouseID,
            "productID" => $productID,
            "quantity" => $newQty,
            "availableQty" => $newQty,
            "lastUpdated" => date("Y-m-d H:i:s"),
            "status" => 1
        );
        $DB->dbInsert();
        $stockID = $DB->insertID;
    }

    // Record in stock ledger (using actual table column names)
    $DB->table = $DB->pre . "stock_ledger";
    $DB->data = array(
        "warehouseID" => $warehouseID,
        "productID" => $productID,
        "transactionType" => "Adjustment",
        "transactionDate" => date("Y-m-d H:i:s"),
        "referenceType" => "Manual",
        "referenceNumber" => $referenceNo,
        "qtyIn" => $qtyIn,
        "qtyOut" => $qtyOut,
        "balanceQty" => $newQty,
        "notes" => $reason,
        "createdBy" => $_SESSION["ADMINID"] ?? 0,
        "created" => date("Y-m-d H:i:s")
    );
    $DB->dbInsert();

    setResponse(array("err" => 0, "msg" => "Stock adjusted successfully", "param" => "warehouseID=$warehouseID"));
}

function transferStock()
{
    global $DB;

    $fromWarehouseID = intval($_POST["fromWarehouseID"]);
    $toWarehouseID = intval($_POST["toWarehouseID"]);
    $productID = intval($_POST["productID"]);
    $quantity = floatval($_POST["quantity"]);
    $notes = trim($_POST["notes"] ?? "");

    if ($fromWarehouseID < 1 || $toWarehouseID < 1 || $productID < 1 || $quantity <= 0) {
        setResponse(array("err" => 1, "msg" => "Invalid data provided"));
        return;
    }

    if ($fromWarehouseID == $toWarehouseID) {
        setResponse(array("err" => 1, "msg" => "Source and destination warehouse cannot be same"));
        return;
    }

    // Check source stock
    $DB->vals = array(1, $fromWarehouseID, $productID);
    $DB->types = "iii";
    $DB->sql = "SELECT stockID, quantity, availableQty FROM " . $DB->pre . "inventory_stock WHERE status=? AND warehouseID=? AND productID=?";
    $sourceStock = $DB->dbRow();

    $sourceQty = floatval($sourceStock["quantity"] ?? $sourceStock["availableQty"] ?? 0);
    if ($quantity > $sourceQty) {
        setResponse(array("err" => 1, "msg" => "Insufficient stock at source warehouse. Available: " . $sourceQty));
        return;
    }

    // Get destination stock
    $DB->vals = array(1, $toWarehouseID, $productID);
    $DB->types = "iii";
    $DB->sql = "SELECT stockID, quantity, availableQty FROM " . $DB->pre . "inventory_stock WHERE status=? AND warehouseID=? AND productID=?";
    $destStock = $DB->dbRow();

    $destQty = floatval($destStock["quantity"] ?? $destStock["availableQty"] ?? 0);
    $destStockID = $destStock["stockID"] ?? 0;

    // Generate transfer reference
    $transferRef = "TRF-" . date("YmdHis") . "-" . rand(100, 999);

    // Update source stock
    $newSourceQty = $sourceQty - $quantity;
    $DB->vals = array($newSourceQty, $newSourceQty, date("Y-m-d H:i:s"), $sourceStock["stockID"]);
    $DB->types = "ddsi";
    $DB->sql = "UPDATE " . $DB->pre . "inventory_stock SET quantity=?, availableQty=?, lastUpdated=? WHERE stockID=?";
    $DB->dbQuery();

    // Update or insert destination stock
    $newDestQty = $destQty + $quantity;
    if ($destStockID > 0) {
        $DB->vals = array($newDestQty, $newDestQty, date("Y-m-d H:i:s"), $destStockID);
        $DB->types = "ddsi";
        $DB->sql = "UPDATE " . $DB->pre . "inventory_stock SET quantity=?, availableQty=?, lastUpdated=? WHERE stockID=?";
        $DB->dbQuery();
    } else {
        $DB->table = $DB->pre . "inventory_stock";
        $DB->data = array(
            "warehouseID" => $toWarehouseID,
            "productID" => $productID,
            "quantity" => $newDestQty,
            "availableQty" => $newDestQty,
            "lastUpdated" => date("Y-m-d H:i:s"),
            "status" => 1
        );
        $DB->dbInsert();
    }

    // Record ledger entry for source (OUT)
    $DB->table = $DB->pre . "stock_ledger";
    $DB->data = array(
        "warehouseID" => $fromWarehouseID,
        "productID" => $productID,
        "transactionType" => "Transfer-Out",
        "transactionDate" => date("Y-m-d H:i:s"),
        "referenceType" => "Transfer",
        "referenceNumber" => $transferRef,
        "qtyIn" => 0,
        "qtyOut" => $quantity,
        "balanceQty" => $newSourceQty,
        "notes" => "Transfer to WH#" . $toWarehouseID . ". " . $notes,
        "createdBy" => $_SESSION["ADMINID"] ?? 0,
        "created" => date("Y-m-d H:i:s")
    );
    $DB->dbInsert();

    // Record ledger entry for destination (IN)
    $DB->table = $DB->pre . "stock_ledger";
    $DB->data = array(
        "warehouseID" => $toWarehouseID,
        "productID" => $productID,
        "transactionType" => "Transfer-In",
        "transactionDate" => date("Y-m-d H:i:s"),
        "referenceType" => "Transfer",
        "referenceNumber" => $transferRef,
        "qtyIn" => $quantity,
        "qtyOut" => 0,
        "balanceQty" => $newDestQty,
        "notes" => "Transfer from WH#" . $fromWarehouseID . ". " . $notes,
        "createdBy" => $_SESSION["ADMINID"] ?? 0,
        "created" => date("Y-m-d H:i:s")
    );
    $DB->dbInsert();

    setResponse(array("err" => 0, "msg" => "Stock transferred successfully. Ref: " . $transferRef));
}

function getStockLedger()
{
    global $DB;

    $warehouseID = intval($_POST["warehouseID"] ?? 0);
    $productID = intval($_POST["productID"] ?? 0);
    $limit = intval($_POST["limit"] ?? 50);

    $whereClause = "WHERE 1=1";
    $DB->vals = array();
    $DB->types = "";

    if ($warehouseID > 0) {
        $whereClause .= " AND sl.warehouseID=?";
        $DB->vals[] = $warehouseID;
        $DB->types .= "i";
    }

    if ($productID > 0) {
        $whereClause .= " AND sl.productID=?";
        $DB->vals[] = $productID;
        $DB->types .= "i";
    }

    $DB->sql = "SELECT sl.*, w.warehouseName, p.productName, p.productSKU
                FROM " . $DB->pre . "stock_ledger sl
                LEFT JOIN " . $DB->pre . "warehouse w ON sl.warehouseID = w.warehouseID
                LEFT JOIN " . $DB->pre . "product p ON sl.productID = p.productID
                $whereClause
                ORDER BY sl.created DESC
                LIMIT $limit";
    $ledger = $DB->dbRows();

    setResponse(array("err" => 0, "data" => $ledger));
}

function getWarehouseStockSummary()
{
    global $DB;

    $warehouseID = intval($_POST["warehouseID"] ?? 0);
    $productID = intval($_POST["productID"] ?? 0);

    $DB->vals = array(1);
    $DB->types = "i";
    $whereClause = "";

    if ($warehouseID > 0) {
        $whereClause .= " AND s.warehouseID=?";
        $DB->vals[] = $warehouseID;
        $DB->types .= "i";
    }

    if ($productID > 0) {
        $whereClause .= " AND s.productID=?";
        $DB->vals[] = $productID;
        $DB->types .= "i";
    }

    $DB->sql = "SELECT s.*, w.warehouseName, w.warehouseCode, p.productName, p.productSKU, p.uom, p.reorderLevel
                FROM " . $DB->pre . "inventory_stock s
                LEFT JOIN " . $DB->pre . "warehouse w ON s.warehouseID = w.warehouseID
                LEFT JOIN " . $DB->pre . "product p ON s.productID = p.productID
                WHERE s.status=? $whereClause
                ORDER BY w.warehouseName, p.productName";
    $stock = $DB->dbRows();

    setResponse(array("err" => 0, "data" => $stock));
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADJUST": addStockAdjustment(); break;
            case "TRANSFER": transferStock(); break;
            case "GET_LEDGER": getStockLedger(); break;
            case "GET_SUMMARY": getWarehouseStockSummary(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "inventory_stock", "PK" => "stockID"));
}
?>
