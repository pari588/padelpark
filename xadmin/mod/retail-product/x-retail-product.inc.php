<?php
/*
addproduct_sku = To save product_sku data.
updateproduct_sku = To update product_sku data.
*/

//Start: To save product_sku data.
function addProductSku()
{
    global $DB;
    $DB->table = $DB->pre . "product_sku";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $productSkuID = $DB->insertID;
        if ($productSkuID) {
            setResponse(array("err" => 0, "param" => "id=$productSkuID"));
        }
    } else {
        setResponse(array("err" => 1));
    }
}
//End.

//Start: To update product_sku data.
function  updateProductSku()
{
    global $DB;
    $productSkuID = intval($_POST["productSkuID"]);
    $DB->table = $DB->pre . "product_sku";
    $DB->data = $_POST;
    if ($DB->dbUpdate("productSkuID=?", "i", array($productSkuID))) {
        if ($productSkuID) {
            setResponse(array("err" => 0, "param" => "id=$productSkuID"));
        }
    } else {
        setResponse(array("err" => 1));
    }
}
//End.

/**
 * Get all products for dropdown/selection
 */
function getAllProducts()
{
    global $DB;

    // Try retail_product first, then fall back to product table
    $tables = array("retail_product", "product");
    $products = array();

    foreach ($tables as $tbl) {
        $DB->sql = "SHOW TABLES LIKE '" . $DB->pre . $tbl . "'";
        $result = $DB->dbQuery();
        if ($result && $result->num_rows > 0) {
            $DB->vals = array(1);
            $DB->types = "i";
            $DB->sql = "SELECT productID, productName, productSKU, unit, costPrice, sellingPrice
                        FROM " . $DB->pre . $tbl . "
                        WHERE status=?
                        ORDER BY productName";
            $DB->dbRows();
            $products = $DB->rows;
            break;
        }
    }

    echo json_encode(array("err" => 0, "products" => $products));
    exit;
}

//End
if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
                addProductSku();
                break;
            case "UPDATE":
                updateProductSku();
                break;
            case "mxDelFile":
                $param = array("dir" => "product-sku", "tbl" => "product_sku", "pk" => "productSkuID");
                mxDelFile(array_merge($_REQUEST, $param));
                break;
            case "GET_ALL_PRODUCTS":
                getAllProducts();
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "product_sku", "PK" => "productSkuID"));
}
