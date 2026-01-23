<?php
/*
B2B Sales Order Module
- Create sales orders for distributors
- Credit limit check
- Auto-generate order number
*/

function generateOrderNo()
{
    global $DB;
    $prefix = "SO-" . date("Ymd") . "-";
    $DB->sql = "SELECT MAX(orderNo) as lastNo FROM " . $DB->pre . "b2b_sales_order WHERE orderNo LIKE '$prefix%'";
    $result = $DB->dbRow();
    $lastNo = $result["lastNo"] ?? "";

    if ($lastNo) {
        $seq = intval(substr($lastNo, -4)) + 1;
    } else {
        $seq = 1;
    }
    return $prefix . str_pad($seq, 4, "0", STR_PAD_LEFT);
}

function addOrder()
{
    global $DB;

    $distributorID = intval($_POST["distributorID"]);
    if ($distributorID < 1) {
        setResponse(array("err" => 1, "msg" => "Please select a distributor"));
        return;
    }

    $warehouseID = intval($_POST["warehouseID"]);
    if ($warehouseID < 1) {
        setResponse(array("err" => 1, "msg" => "Please select a warehouse"));
        return;
    }

    // Get distributor details for credit check
    $DB->vals = array($distributorID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM " . $DB->pre . "distributor WHERE distributorID=? AND status=?";
    $distributor = $DB->dbRow();

    if (!$distributor) {
        setResponse(array("err" => 1, "msg" => "Distributor not found"));
        return;
    }

    if ($distributor["creditStatus"] == "Blocked") {
        setResponse(array("err" => 1, "msg" => "This distributor's credit is blocked"));
        return;
    }

    // Parse order items
    $items = json_decode($_POST["items"] ?? "[]", true);
    if (empty($items)) {
        setResponse(array("err" => 1, "msg" => "Please add at least one item"));
        return;
    }

    // Calculate totals
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += floatval($item["totalAmount"] ?? 0);
    }

    $discountAmount = floatval($_POST["discountAmount"] ?? 0);
    $taxableAmount = floatval($_POST["taxableAmount"] ?? ($subtotal - $discountAmount));

    $cgstAmount = floatval($_POST["cgstAmount"] ?? 0);
    $sgstAmount = floatval($_POST["sgstAmount"] ?? 0);
    $igstAmount = floatval($_POST["igstAmount"] ?? 0);

    $totalAmount = $taxableAmount + $cgstAmount + $sgstAmount + $igstAmount;

    // Credit limit check
    $newBalance = floatval($distributor["currentOutstanding"]) + $totalAmount;
    if ($distributor["creditLimit"] > 0 && $newBalance > $distributor["creditLimit"]) {
        $availableCredit = $distributor["creditLimit"] - $distributor["currentOutstanding"];
        setResponse(array("err" => 1, "msg" => "Order exceeds credit limit. Available credit: Rs. " . number_format($availableCredit, 2)));
        return;
    }

    // Generate order number
    $orderNo = generateOrderNo();

    // Calculate due date
    $creditDays = intval($distributor["creditDays"] ?? 30);
    $orderDate = $_POST["orderDate"] ?? date("Y-m-d");
    $dueDate = date("Y-m-d", strtotime($orderDate . " + $creditDays days"));

    // Insert order
    $DB->table = $DB->pre . "b2b_sales_order";
    $DB->data = array(
        "orderNo" => $orderNo,
        "orderDate" => $orderDate,
        "distributorID" => $distributorID,
        "distributorName" => $_POST["distributorName"] ?? $distributor["companyName"],
        "distributorGSTIN" => $_POST["distributorGSTIN"] ?? $distributor["gstin"],
        "billingAddress" => $distributor["billingAddress"],
        "shippingAddress" => $_POST["shippingAddress"] ?? $distributor["shippingAddress"],
        "warehouseID" => $warehouseID,
        "subtotal" => $subtotal,
        "discountAmount" => $discountAmount,
        "taxableAmount" => $taxableAmount,
        "cgstAmount" => $cgstAmount,
        "sgstAmount" => $sgstAmount,
        "igstAmount" => $igstAmount,
        "totalAmount" => $totalAmount,
        "paymentTerms" => $_POST["paymentTerms"] ?? "Net " . $creditDays,
        "dueDate" => $dueDate,
        "orderStatus" => "Draft",
        "paymentStatus" => "Pending",
        "poNumber" => $_POST["poNumber"] ?? "",
        "poDate" => $_POST["poDate"] ?: null,
        "remarks" => $_POST["remarks"] ?? "",
        "createdBy" => $_SESSION["ADMINID"] ?? 0,
        "created" => date("Y-m-d H:i:s"),
        "status" => 1
    );

    if (!$DB->dbInsert()) {
        setResponse(array("err" => 1, "msg" => "Failed to create order"));
        return;
    }

    $orderID = $DB->insertID;

    // Insert order items
    foreach ($items as $item) {
        $DB->table = $DB->pre . "b2b_sales_order_item";
        $DB->data = array(
            "orderID" => $orderID,
            "productID" => intval($item["productID"]),
            "productSKU" => $item["productSKU"] ?? "",
            "productName" => $item["productName"] ?? "",
            "hsnCode" => $item["hsnCode"] ?? "",
            "quantity" => floatval($item["quantity"]),
            "uom" => $item["uom"] ?? "Pcs",
            "unitPrice" => floatval($item["unitPrice"]),
            "discountPercent" => floatval($item["discountPercent"] ?? 0),
            "discountAmount" => floatval($item["discountAmount"] ?? 0),
            "taxableAmount" => floatval($item["taxableAmount"] ?? $item["totalAmount"]),
            "gstRate" => floatval($item["gstRate"] ?? 18),
            "cgstAmount" => floatval($item["cgstAmount"] ?? 0),
            "sgstAmount" => floatval($item["sgstAmount"] ?? 0),
            "igstAmount" => floatval($item["igstAmount"] ?? 0),
            "totalAmount" => floatval($item["totalAmount"]),
            "status" => 1
        );
        $DB->dbInsert();
    }

    setResponse(array("err" => 0, "param" => "id=" . $orderID, "orderNo" => $orderNo, "msg" => "Order created successfully"));
}

function updateOrder()
{
    global $DB;

    $orderID = intval($_POST["orderID"]);
    if ($orderID < 1) {
        setResponse(array("err" => 1, "msg" => "Invalid order ID"));
        return;
    }

    // Check if order can be edited
    $DB->vals = array($orderID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT orderStatus FROM " . $DB->pre . "b2b_sales_order WHERE orderID=? AND status=?";
    $order = $DB->dbRow();

    if (!$order) {
        setResponse(array("err" => 1, "msg" => "Order not found"));
        return;
    }

    if (in_array($order["orderStatus"], ["Shipped", "Delivered", "Cancelled"])) {
        setResponse(array("err" => 1, "msg" => "Cannot edit " . $order["orderStatus"] . " order"));
        return;
    }

    // Parse and validate items
    $items = json_decode($_POST["items"] ?? "[]", true);
    if (empty($items)) {
        setResponse(array("err" => 1, "msg" => "Please add at least one item"));
        return;
    }

    // Calculate totals
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += floatval($item["totalAmount"] ?? 0);
    }

    $discountAmount = floatval($_POST["discountAmount"] ?? 0);
    $taxableAmount = floatval($_POST["taxableAmount"] ?? ($subtotal - $discountAmount));

    $cgstAmount = floatval($_POST["cgstAmount"] ?? 0);
    $sgstAmount = floatval($_POST["sgstAmount"] ?? 0);
    $igstAmount = floatval($_POST["igstAmount"] ?? 0);

    $totalAmount = $taxableAmount + $cgstAmount + $sgstAmount + $igstAmount;

    // Update order
    $DB->vals = array(
        $_POST["orderDate"] ?? date("Y-m-d"),
        intval($_POST["warehouseID"]),
        $_POST["shippingAddress"] ?? "",
        $subtotal,
        $discountAmount,
        $taxableAmount,
        $cgstAmount,
        $sgstAmount,
        $igstAmount,
        $totalAmount,
        $_POST["paymentTerms"] ?? "",
        $_POST["poNumber"] ?? "",
        $_POST["poDate"] ?: null,
        $_POST["remarks"] ?? "",
        date("Y-m-d H:i:s"),
        $orderID
    );
    $DB->types = "sisdddddddsssssi";
    $DB->sql = "UPDATE " . $DB->pre . "b2b_sales_order SET
                orderDate=?, warehouseID=?, shippingAddress=?,
                subtotal=?, discountAmount=?, taxableAmount=?,
                cgstAmount=?, sgstAmount=?, igstAmount=?, totalAmount=?,
                paymentTerms=?, poNumber=?, poDate=?, remarks=?, modified=?
                WHERE orderID=?";
    $DB->dbQuery();

    // Delete existing items and re-insert
    $DB->vals = array($orderID);
    $DB->types = "i";
    $DB->sql = "DELETE FROM " . $DB->pre . "b2b_sales_order_item WHERE orderID=?";
    $DB->dbQuery();

    // Insert updated items
    foreach ($items as $item) {
        $DB->table = $DB->pre . "b2b_sales_order_item";
        $DB->data = array(
            "orderID" => $orderID,
            "productID" => intval($item["productID"]),
            "productSKU" => $item["productSKU"] ?? "",
            "productName" => $item["productName"] ?? "",
            "hsnCode" => $item["hsnCode"] ?? "",
            "quantity" => floatval($item["quantity"]),
            "uom" => $item["uom"] ?? "Pcs",
            "unitPrice" => floatval($item["unitPrice"]),
            "discountPercent" => floatval($item["discountPercent"] ?? 0),
            "discountAmount" => floatval($item["discountAmount"] ?? 0),
            "taxableAmount" => floatval($item["taxableAmount"] ?? $item["totalAmount"]),
            "gstRate" => floatval($item["gstRate"] ?? 18),
            "cgstAmount" => floatval($item["cgstAmount"] ?? 0),
            "sgstAmount" => floatval($item["sgstAmount"] ?? 0),
            "igstAmount" => floatval($item["igstAmount"] ?? 0),
            "totalAmount" => floatval($item["totalAmount"]),
            "status" => 1
        );
        $DB->dbInsert();
    }

    setResponse(array("err" => 0, "param" => "id=" . $orderID, "msg" => "Order updated successfully"));
}

function confirmOrder()
{
    global $DB;

    $orderID = intval($_POST["orderID"]);

    $DB->vals = array($orderID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM " . $DB->pre . "b2b_sales_order WHERE orderID=? AND status=?";
    $order = $DB->dbRow();

    if (!$order) {
        setResponse(array("err" => 1, "msg" => "Order not found"));
        return;
    }

    if ($order["orderStatus"] != "Draft") {
        setResponse(array("err" => 1, "msg" => "Only draft orders can be confirmed"));
        return;
    }

    // Update order status
    $DB->vals = array("Confirmed", date("Y-m-d H:i:s"), $_SESSION["ADMINID"] ?? 0, date("Y-m-d H:i:s"), $orderID);
    $DB->types = "ssisi";
    $DB->sql = "UPDATE " . $DB->pre . "b2b_sales_order SET orderStatus=?, modified=?, approvedBy=?, approvedDate=? WHERE orderID=?";
    $DB->dbQuery();

    setResponse(array("err" => 0, "msg" => "Order confirmed successfully"));
}

function cancelOrder()
{
    global $DB;

    $orderID = intval($_POST["orderID"]);
    $reason = trim($_POST["reason"] ?? "");

    $DB->vals = array($orderID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM " . $DB->pre . "b2b_sales_order WHERE orderID=? AND status=?";
    $order = $DB->dbRow();

    if (!$order) {
        setResponse(array("err" => 1, "msg" => "Order not found"));
        return;
    }

    if (in_array($order["orderStatus"], ["Shipped", "Delivered"])) {
        setResponse(array("err" => 1, "msg" => "Cannot cancel " . $order["orderStatus"] . " order"));
        return;
    }

    // Update order status
    $DB->vals = array("Cancelled", $reason, date("Y-m-d H:i:s"), $orderID);
    $DB->types = "sssi";
    $DB->sql = "UPDATE " . $DB->pre . "b2b_sales_order SET orderStatus=?, remarks=CONCAT(COALESCE(remarks,''), '\nCancelled: ', ?), modified=? WHERE orderID=?";
    $DB->dbQuery();

    setResponse(array("err" => 0, "msg" => "Order cancelled"));
}

function getProducts()
{
    global $DB, $MXRES;

    $warehouseID = intval($_POST["warehouseID"] ?? 0);
    $search = trim($_POST["search"] ?? "");

    $whereClause = "WHERE p.status=1 AND p.isActive=1";
    $DB->vals = array();
    $DB->types = "";

    if (!empty($search)) {
        $whereClause .= " AND (p.productSKU LIKE ? OR p.productName LIKE ?)";
        $DB->vals[] = "%$search%";
        $DB->vals[] = "%$search%";
        $DB->types .= "ss";
    }

    $stockJoin = "";
    $stockSelect = ", 0 as availableQty";
    if ($warehouseID > 0) {
        $stockJoin = "LEFT JOIN " . $DB->pre . "inventory_stock s ON p.productID = s.productID AND s.warehouseID = $warehouseID AND s.status=1";
        $stockSelect = ", COALESCE(s.quantity, 0) as availableQty";
    }

    $DB->sql = "SELECT p.productID, p.productSKU, p.productName, p.hsnCode, p.basePrice, p.gstRate, p.uom $stockSelect
                FROM " . $DB->pre . "product p
                $stockJoin
                $whereClause
                ORDER BY p.productName
                LIMIT 50";
    $products = $DB->dbRows();

    $MXRES["err"] = 0;
    $MXRES["data"] = $products;
    $MXRES["msg"] = count($products) . " products found";
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");

    // For admin users, bypass JWT token validation and use session authentication
    $MXRES = mxCheckRequest(true, true); // login=true, ignoreToken=true

    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addOrder(); break;
            case "UPDATE": updateOrder(); break;
            case "CONFIRM": confirmOrder(); break;
            case "CANCEL": cancelOrder(); break;
            case "GET_PRODUCTS": getProducts(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "b2b_sales_order", "PK" => "orderID"));
}
?>
