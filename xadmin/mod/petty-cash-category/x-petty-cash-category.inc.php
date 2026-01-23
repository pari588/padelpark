<?php
function addPettyCashCat()
{
    global $DB;
    if (isset($_POST["pettyCashCat"]))
        $_POST["pettyCashCat"] = cleanTitle($_POST["pettyCashCat"]);
    $DB->table = $DB->pre . "pettycash_category";
    $DB->data = $_POST;
    $result = checkDuplicate($_POST["pettyCashCat"]);;
    $respMsg = $result["msg"];
    if ($result["count"] == 0) {
        if ($DB->dbInsert()) {
            $pettyCashCatID = $DB->insertID;
            if ($pettyCashCatID) {
                setResponse(array("err" => 0, "param" => "id=$pettyCashCatID"));
            }
        } else {
            setResponse(array("err" => 1));
        }
    } else {
        setResponse(array("err" => 1, "param" => "", "msg" => "$respMsg"));
    }
}

function updatePettyCashCat()
{
    global $DB;
    $pettyCashCatID = intval($_POST["pettyCashCatID"]);
    if (isset($_POST["pettyCashCat"]))
        $_POST["pettyCashCat"] = cleanTitle($_POST["pettyCashCat"]);
    $DB->table = $DB->pre . "pettycash_category";
    $DB->data = $_POST;
    $result = checkDuplicate($_POST["pettyCashCat"]);;
    $respMsg = $result["msg"];
    if ($result["count"] == 0) {
        if ($DB->dbUpdate("pettyCashCatID=?", "i", array($pettyCashCatID))) {
            if ($pettyCashCatID) {
                setResponse(array("err" => 0, "param" => "id=$pettyCashCatID"));
            }
        } else {
            setResponse(array("err" => 1));
        }
    } else {
        setResponse(array("err" => 1, "param" => "", "msg" => "$respMsg"));
    }
}
//Start: Check category duplication.
function checkDuplicate($pettyCashCat = "")
{
    global $DB;
    $response['count'] = 0;
    $response['msg'] = "";
    if (isset($pettyCashCat)) {
        $DB->vals = array(1, $pettyCashCat);
        $DB->types = "is";
        $DB->sql = "SELECT pettyCashCat FROM `" . $DB->pre . "pettycash_category` WHERE status=? AND pettyCashCat=?";
        $result = $DB->dbRow();
    if($DB->numRows > 0){
        if ($pettyCashCat == $result['pettyCashCat']) {
            $response['count'] = 1;
            $response['msg'] = "This category is already existing";
        }
    }
}
    return $response;
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
                addPettyCashCat();
                break;
            case "UPDATE":
                updatePettyCashCat();
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "pettycash_category", "PK" => "pettyCashCatID"));
}
