<?php


function addCustomer()
{
    global $DB;
    $_POST["customerName"] = cleanTitle($_POST["customerName"]);
    $DB->types = "s";
    $DB->vals = array($_POST["customerName"]);
    $DB->sql = "SELECT customerName FROM `" . $DB->pre . "customer` WHERE customerName=?" . mxWhere();
    $DB->dbQuery();
    if ($DB->numRows > 0) {
        setResponse(["err" => 1, "msg" => "Customer already exists", "alert" => "Customer already exists"]);
    } else {
        if (!isset($_POST["isActive"]))
            $_POST["isActive"] = 0;

        $DB->table = $DB->pre . "customer";
        $DB->data = $_POST;
        if ($DB->dbInsert()) {
            $customerID = $DB->insertID;
            setResponse(["err" => 0, "param" => "id=$customerID"]);
        } else {
            setResponse(["err" => 1]);
        }
    }
}

function updateCustomer()
{
    global $DB;
    $customerID = intval($_POST["customerID"]);
    $_POST["customerName"] = cleanTitle($_POST["customerName"]);
    $DB->types = "is";
    $DB->vals = array($customerID, $_POST["customerName"]);
    $DB->sql = "SELECT customerName FROM `" . $DB->pre . "customer` WHERE customerID <> ?   AND customerName=? " . mxWhere();
    $DB->dbQuery();
    if ($DB->numRows > 0) {
        setResponse(["err" => 1, "msg" => "Customer already exists", "alert" => "Customer already exists"]);
    } else {
        if (!isset($_POST["isActive"]))
            $_POST["isActive"] = 0;

        $DB->table = $DB->pre . "customer";
        $DB->data = $_POST;
        if ($DB->dbUpdate("customerID=?", "i", array($customerID))) {
            setResponse(["err" => 0, "param" => "id=$customerID"]);
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
                addCustomer();
                break;
            case "UPDATE":
                updateCustomer();
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    setModVars(array("TBL" => "customer", "PK" => "customerID"));
}
