<?php
/*
addleave_type = To save leave_type data.
updateleave_type = To update leave_type data.
addUpdateMoterDetail = To add and update leave_type's detail data. 
*/
//Start: To save leave_type data.
function addleave_type()
{
    global $DB;
    if (isset($_POST["leaveTypeName"]))
        $_POST["leaveTypeName"] = cleanTitle($_POST["leaveTypeName"]);
    $DB->table = $DB->pre . "leave_type";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $leaveTypeID = $DB->insertID;
        if ($leaveTypeID) {
            setResponse(array("err" => 0, "param" => "id=$leaveTypeID"));
        }
    } else {
        setResponse(array("err" => 1));
    }
}
//End.
//Start: To update leave_type data.
function  updateleave_type()
{
    global $DB;
    $leaveTypeID = intval($_POST["leaveTypeID"]);
    if (isset($_POST["leaveTypeName"]))
        $_POST["leaveTypeName"] = cleanTitle($_POST["leaveTypeName"]);
    $DB->table = $DB->pre . "leave_type";
    $DB->data = $_POST;
    if ($DB->dbUpdate("leaveTypeID=?", "i", array($leaveTypeID))) {
        if ($leaveTypeID) {
            setResponse(array("err" => 0, "param" => "id=$leaveTypeID"));
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
                addleave_type();
                break;
            case "UPDATE":
                updateleave_type();
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "leave_type", "PK" => "leaveTypeID"));
}
