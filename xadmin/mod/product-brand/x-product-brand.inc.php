<?php
/*
addProductBrand = To save new product brand.
updateProductBrand = To update product brand.
*/

function addProductBrand()
{
    global $DB;

    if (isset($_POST["sortOrder"])) $_POST["sortOrder"] = intval($_POST["sortOrder"]);

    $DB->table = $DB->pre . "product_brand";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $brandID = $DB->insertID;
        setResponse(array("err" => 0, "param" => "id=$brandID"));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateProductBrand()
{
    global $DB;
    $brandID = intval($_POST["brandID"]);

    if (isset($_POST["sortOrder"])) $_POST["sortOrder"] = intval($_POST["sortOrder"]);

    $DB->table = $DB->pre . "product_brand";
    $DB->data = $_POST;
    if ($DB->dbUpdate("brandID=?", "i", array($brandID))) {
        setResponse(array("err" => 0, "param" => "id=$brandID"));
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
            case "ADD": addProductBrand(); break;
            case "UPDATE": updateProductBrand(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "product_brand", "PK" => "brandID"));
}
?>
