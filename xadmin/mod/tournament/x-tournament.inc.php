<?php
/*
 addLead : To add leads data.
 updateLead: To update leads data.
 addUpdateComment: To add and update commnets.
*/
// Start: To add leads data.
function addLead()
{
    global $DB;
    if (isset($_POST["partyName"]))
        $_POST["partyName"] = cleanTitle($_POST["partyName"]);
    if (isset($_POST["partyAddr"]))
        $_POST["partyAddr"] = $_POST["partyAddr"];
    if (isset($_POST["leadLocation"]))
        $_POST["leadLocation"] = $_POST["leadLocation"];
    if (isset($_POST["contactPerson"]))
        $_POST["contactPerson"] = $_POST["contactPerson"];
    if (isset($_POST["contactNumber"]))
        $_POST["contactNumber"] = $_POST["contactNumber"];
    if (isset($_POST["officeNumber"]))
        $_POST["officeNumber"] = $_POST["officeNumber"];
    if (isset($_POST["remark"]))
        $_POST["remark"] = $_POST["remark"];
    if (isset($_POST["leadDate"]))
        $_POST["leadDate"] = $_POST["leadDate"];
    if (intval($_POST["leadUser"]) != 0) {
        $_POST["addedByLead"] = $_SESSION[SITEURL]["MXID"];
        $_POST["transferByLead"] = $_SESSION[SITEURL]["MXID"];
        $_POST["currentLead"] = $_POST["leadUser"];
    } else {
        $_POST["addedByLead"] = $_SESSION[SITEURL]["MXID"];
        $_POST["transferByLead"] = $_SESSION[SITEURL]["MXID"];
        $_POST["currentLead"] = $_SESSION[SITEURL]["MXID"];
    }
    $_POST["referenceDocument"] = mxGetFileName("referenceDocument");
    $_POST["visitingCard"] = mxGetFileName("visitingCard");
    $DB->table = $DB->pre . "lead";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $leadID = $DB->insertID;
        if ($leadID) {
            addUpdateComment($leadID);
            setResponse(array("err" => 0, "param" => "id=$leadID"));
        }
    } else {
        setResponse(array("err" => 1));
    }
}
// End. 
// Start: To update leads data.
function updateLead()
{
    global $DB;
    if (isset($_POST["partyName"]))
        $_POST["partyName"] = cleanTitle($_POST["partyName"]);
    if (isset($_POST["partyAddr"]))
        $_POST["partyAddr"] = $_POST["partyAddr"];
    if (isset($_POST["leadLocation"]))
        $_POST["leadLocation"] = $_POST["leadLocation"];
    if (isset($_POST["contactPerson"]))
        $_POST["contactPerson"] = $_POST["contactPerson"];
    if (isset($_POST["contactNumber"]))
        $_POST["contactNumber"] = $_POST["contactNumber"];
    if (isset($_POST["officeNumber"]))
        $_POST["officeNumber"] = $_POST["officeNumber"];
    if (isset($_POST["remark"]))
        $_POST["remark"] = $_POST["remark"];
    if (isset($_POST["leadDate"])) {
        $_POST["leadDate"] = $_POST["leadDate"];
    }
    if (isset($_POST["leadUser"])) {
        $_POST["transferByLead"] = $_SESSION[SITEURL]["MXID"];
        $_POST["currentLead"] = $_POST["leadUser"];
    }
    $_POST["referenceDocument"] = mxGetFileName("referenceDocument");
    $_POST["visitingCard"] = mxGetFileName("visitingCard");
    $leadID = intval($_POST["leadID"]);
    $DB->table = $DB->pre . "lead";
    $DB->data = $_POST;

    if ($DB->dbUpdate("leadID=?", "i", array($leadID))) {
        $DB->vals = array($leadID);
        $DB->types = "i";
        $DB->sql = "DELETE FROM " . $DB->pre . "lead_detail WHERE leadID=?";
        $DB->dbQuery();
        addUpdateComment($leadID);
        setResponse(array("err" => 0, "param" => "id=$leadID"));
    } else {
        setResponse(array("err" => 1));
    }
}
// End. 
// Start: To add and update commnets.
function addUpdateComment($leadID = 0)
{
    global $DB;
    if (intval($leadID) > 0) {
        if (isset($_POST["leadDID"]) && count($_POST["leadDID"]) > 0) {
            for ($i = 0; $i < count($_POST["leadDID"]); $i++) {
                $arr = array(
                    "leadID" => $leadID,
                    "comment" => $_POST["comment"][$i],
                    "cmtDate" => date("Y/m/d")
                );
                if (isset($_POST["leadDID"][$i]) && intval($_POST["leadDID"][$i]) > 0) {
                    $arr["leadDID"] = $_POST["leadDID"][$i];
                }
                $DB->table = $DB->pre . "lead_detail";
                $DB->data = $arr;
                $DB->dbInsert();
            }
        }
    }
}
// End. 
if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
                addLead();
                break;
            case "UPDATE":
                updateLead();
                break;
            case "mxDelFile":
                if ($_REQUEST["fld"] == "referenceDocument") {
                    $param = array("dir" => "lead-ref-document", "tbl" => "lead", "pk" => "leadID");
                }
                if ($_REQUEST["fld"] == "visitingCard") {
                    $param = array("dir" => "visiting-card", "tbl" => "lead", "pk" => "leadID");
                }
                mxDelFile(array_merge($_REQUEST, $param));
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(
        array("TBL" => "lead", "PK" => "leadID", "UDIR" => array(
            "referenceDocument" => "lead-ref-document",
            "visitingCard" => "visiting-card",
            "cameraUpload" => "camera-upload"
        ))
    );
}
