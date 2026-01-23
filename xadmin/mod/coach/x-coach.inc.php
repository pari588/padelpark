<?php
function addVendor()
{
    global $DB;
    $_POST["vendorName"] = cleanTitle($_POST["vendorName"]);
    $DB->types = "s";
    $DB->vals = array($_POST["vendorName"]);
    $DB->sql = "SELECT vendorName FROM `" . $DB->pre . "vendor` WHERE vendorName=?" . mxWhere();
    $DB->dbQuery();
    if ($DB->numRows > 0) {
        setResponse(["err" => 1, "msg" => "Vendor already exists", "alert" => "Vendor already exists"]);
    } else {
        if (!isset($_POST["isActive"]))
            $_POST["isActive"] = 0;

        $DB->table = $DB->pre . "vendor";
        $DB->data = $_POST;
        if ($DB->dbInsert()) {
            $vendorID = $DB->insertID;
            setResponse(["err" => 0, "param" => "id=$vendorID"]);
        } else {
            setResponse(["err" => 1]);
        }
    }
}

function updateVendor()
{
    global $DB;
    $vendorID = intval($_POST["vendorID"]);
    $_POST["vendorName"] = cleanTitle($_POST["vendorName"]);
    $DB->types = "is";
    $DB->vals = array($vendorID, $_POST["vendorName"]);
    $DB->sql = "SELECT vendorName FROM `" . $DB->pre . "vendor` WHERE vendorID != ?   AND vendorName=? " . mxWhere();
    $DB->dbQuery();
    if ($DB->numRows > 0) {
        setResponse(["err" => 1, "msg" => "Vendor already exists", "alert" => "Vendor already exists"]);
    } else {
        if (!isset($_POST["isActive"]))
            $_POST["isActive"] = 0;

        $DB->table = $DB->pre . "vendor";
        $DB->data = $_POST;
        if ($DB->dbUpdate("vendorID=?", "i", array($vendorID))) {
            setResponse(["err" => 0, "param" => "id=$vendorID"]);
        } else {
            setResponse(["err" => 1]);
        }
    }
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
                addVendor();
                break;
            case "UPDATE":
                updateVendor();
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    setModVars(array("TBL" => "vendor", "PK" => "vendorID"));
}
