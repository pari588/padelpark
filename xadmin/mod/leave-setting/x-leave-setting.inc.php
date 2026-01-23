<?php
/*
addleave_setting = To save leave_setting data.
updateleave_setting = To update leave_setting data.
addUpdateMoterDetail = To add and update leave_setting's detail data. 
*/
//Start: To save leave_setting data.
function addleave_setting()
{
    global $DB;
    if (isset($_POST["FYStartDate"]))
    $_POST["FYStartDate"] = cleanTitle($_POST["FYStartDate"]);
    $_POST["dateAdded"] = date("Y-m-d H:i:s");
    $date = $_POST["FYStartDate"];
    $fromdateArr = explode("/", $date);
    foreach ($fromdateArr as $key => $val) {
        $newArr[] = $val;
    }
    $_POST["FYStartDate"] =  $newArr[0];
    $_POST["FYEndDate"] =  $newArr[1];
    $DB->table = $DB->pre . "leave_setting";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $leaveSettingID = $DB->insertID;
        if ($leaveSettingID) {
            setResponse(array("err" => 0, "param" => "id=$leaveSettingID"));
        }
    } else {
        setResponse(array("err" => 1));
    }
}
//End.
//Start: To update leave_setting data.
function  updateleave_setting()
{
    global $DB;
    if (isset($_POST["FYStartDate"]))
        $_POST["FYStartDate"] = cleanTitle($_POST["FYStartDate"]);
    $leaveSettingID = intval($_POST["leaveSettingID"]);
    $_POST["dateAdded"] = date("Y-m-d H:i:s");
    $date = $_POST["FYStartDate"];
    $fromdateArr = explode("/", $date);
    foreach ($fromdateArr as $key => $val) {
        $newArr[] = $val;
    }
    $_POST["FYStartDate"] =  $newArr[0];
    $_POST["FYEndDate"] =  $newArr[1];
    $DB->table = $DB->pre . "leave_setting";

    $DB->data = $_POST;
    if ($DB->dbUpdate("leaveSettingID=?", "i", array($leaveSettingID))) {
        if ($leaveSettingID) {
            setResponse(array("err" => 0, "param" => "id=$leaveSettingID"));
        }
    } else {
        setResponse(array("err" => 1));
    }
}
//End.
if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
                addleave_setting();
                break;
            case "UPDATE":
                updateleave_setting();
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "leave_setting", "PK" => "leaveSettingID"));
}
