<?php
/*
addProductCategory = To save new product category.
updateProductCategory = To update product category.
*/

function addProductCategory()
{
    global $DB;

    if (isset($_POST["parentCategoryID"])) $_POST["parentCategoryID"] = intval($_POST["parentCategoryID"]) ?: null;
    if (isset($_POST["sortOrder"])) $_POST["sortOrder"] = intval($_POST["sortOrder"]);

    $DB->table = $DB->pre . "product_category";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $categoryID = $DB->insertID;
        setResponse(array("err" => 0, "param" => "id=$categoryID"));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateProductCategory()
{
    global $DB;
    $categoryID = intval($_POST["categoryID"]);

    if (isset($_POST["parentCategoryID"])) $_POST["parentCategoryID"] = intval($_POST["parentCategoryID"]) ?: null;
    if (isset($_POST["sortOrder"])) $_POST["sortOrder"] = intval($_POST["sortOrder"]);

    // Prevent self-reference
    if ($_POST["parentCategoryID"] == $categoryID) {
        $_POST["parentCategoryID"] = null;
    }

    $DB->table = $DB->pre . "product_category";
    $DB->data = $_POST;
    if ($DB->dbUpdate("categoryID=?", "i", array($categoryID))) {
        setResponse(array("err" => 0, "param" => "id=$categoryID"));
    } else {
        setResponse(array("err" => 1));
    }
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest(true, true); // Session-based auth
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addProductCategory(); break;
            case "UPDATE": updateProductCategory(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "product_category", "PK" => "categoryID"));
}
?>
