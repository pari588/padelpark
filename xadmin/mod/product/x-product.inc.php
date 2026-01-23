<?php
/*
addProduct = To save new product.
updateProduct = To update product.
getProductDropdown = Get product dropdown for AJAX
*/

function addProduct()
{
    global $DB;

    // Type casting
    if (isset($_POST["categoryID"])) $_POST["categoryID"] = intval($_POST["categoryID"]) ?: null;
    if (isset($_POST["brandID"])) $_POST["brandID"] = intval($_POST["brandID"]) ?: null;
    if (isset($_POST["hsnID"])) $_POST["hsnID"] = intval($_POST["hsnID"]) ?: null;
    if (isset($_POST["basePrice"])) $_POST["basePrice"] = floatval($_POST["basePrice"]);
    if (isset($_POST["gstRate"])) $_POST["gstRate"] = floatval($_POST["gstRate"]);
    if (isset($_POST["weight"])) $_POST["weight"] = floatval($_POST["weight"]) ?: null;
    if (isset($_POST["length"])) $_POST["length"] = floatval($_POST["length"]) ?: null;
    if (isset($_POST["width"])) $_POST["width"] = floatval($_POST["width"]) ?: null;
    if (isset($_POST["height"])) $_POST["height"] = floatval($_POST["height"]) ?: null;
    if (isset($_POST["reorderLevel"])) $_POST["reorderLevel"] = intval($_POST["reorderLevel"]);
    if (isset($_POST["isActive"])) $_POST["isActive"] = intval($_POST["isActive"]);
    if (isset($_POST["isStockable"])) $_POST["isStockable"] = intval($_POST["isStockable"]);

    // Check for duplicate SKU
    if (!empty($_POST["productSKU"])) {
        $DB->vals = array(1, $_POST["productSKU"]);
        $DB->types = "is";
        $DB->sql = "SELECT productID FROM " . $DB->pre . "product WHERE status=? AND productSKU=?";
        if ($DB->dbRow()) {
            setResponse(array("err" => 1, "msg" => "Product SKU already exists"));
            return;
        }
    }

    $DB->table = $DB->pre . "product";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $productID = $DB->insertID;
        setResponse(array("err" => 0, "param" => "id=$productID"));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateProduct()
{
    global $DB;
    $productID = intval($_POST["productID"]);

    // Type casting
    if (isset($_POST["categoryID"])) $_POST["categoryID"] = intval($_POST["categoryID"]) ?: null;
    if (isset($_POST["brandID"])) $_POST["brandID"] = intval($_POST["brandID"]) ?: null;
    if (isset($_POST["hsnID"])) $_POST["hsnID"] = intval($_POST["hsnID"]) ?: null;
    if (isset($_POST["basePrice"])) $_POST["basePrice"] = floatval($_POST["basePrice"]);
    if (isset($_POST["gstRate"])) $_POST["gstRate"] = floatval($_POST["gstRate"]);
    if (isset($_POST["weight"])) $_POST["weight"] = floatval($_POST["weight"]) ?: null;
    if (isset($_POST["length"])) $_POST["length"] = floatval($_POST["length"]) ?: null;
    if (isset($_POST["width"])) $_POST["width"] = floatval($_POST["width"]) ?: null;
    if (isset($_POST["height"])) $_POST["height"] = floatval($_POST["height"]) ?: null;
    if (isset($_POST["reorderLevel"])) $_POST["reorderLevel"] = intval($_POST["reorderLevel"]);
    if (isset($_POST["isActive"])) $_POST["isActive"] = intval($_POST["isActive"]);
    if (isset($_POST["isStockable"])) $_POST["isStockable"] = intval($_POST["isStockable"]);

    // Check for duplicate SKU (exclude current)
    if (!empty($_POST["productSKU"])) {
        $DB->vals = array(1, $_POST["productSKU"], $productID);
        $DB->types = "isi";
        $DB->sql = "SELECT productID FROM " . $DB->pre . "product WHERE status=? AND productSKU=? AND productID!=?";
        if ($DB->dbRow()) {
            setResponse(array("err" => 1, "msg" => "Product SKU already exists"));
            return;
        }
    }

    $DB->table = $DB->pre . "product";
    $DB->data = $_POST;
    if ($DB->dbUpdate("productID=?", "i", array($productID))) {
        setResponse(array("err" => 0, "param" => "id=$productID"));
    } else {
        setResponse(array("err" => 1));
    }
}

// Get product dropdown (for AJAX use in other modules)
function getProductDropdown()
{
    global $DB;

    $warehouseID = isset($_POST["warehouseID"]) ? intval($_POST["warehouseID"]) : 0;
    $search = isset($_POST["search"]) ? trim($_POST["search"]) : "";

    $options = array();

    $DB->vals = array(1, 1);
    $DB->types = "ii";
    $whereClause = "";

    if (!empty($search)) {
        $DB->vals[] = "%$search%";
        $DB->vals[] = "%$search%";
        $DB->types .= "ss";
        $whereClause = " AND (p.productName LIKE ? OR p.productSKU LIKE ?)";
    }

    $DB->sql = "SELECT p.productID, p.productSKU, p.productName, p.uom, p.basePrice
                FROM " . $DB->pre . "product p
                WHERE p.status=? AND p.isActive=?" . $whereClause . "
                ORDER BY p.productName
                LIMIT 50";
    $products = $DB->dbRows();

    foreach ($products as $p) {
        $options[] = array(
            "id" => $p["productID"],
            "text" => $p["productSKU"] . " - " . $p["productName"],
            "sku" => $p["productSKU"],
            "name" => $p["productName"],
            "uom" => $p["uom"],
            "price" => $p["basePrice"]
        );
    }

    setResponse(array("err" => 0, "data" => $options));
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest(true, true); // Session-based auth
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addProduct(); break;
            case "UPDATE": updateProduct(); break;
            case "GET_PRODUCTS": getProductDropdown(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "product", "PK" => "productID"));
}
?>
