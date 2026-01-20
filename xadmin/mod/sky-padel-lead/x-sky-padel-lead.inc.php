<?php
/*
addLead = To save lead data.
updateLead = To update lead data.
*/

//Start: To save lead data.
function addLead()
{
    global $DB;

    if (isset($_POST["clientName"]))
        $_POST["clientName"] = cleanTitle($_POST["clientName"]);
    if (isset($_POST["clientEmail"]))
        $_POST["clientEmail"] = cleanTitle($_POST["clientEmail"]);
    if (isset($_POST["clientPhone"]))
        $_POST["clientPhone"] = cleanTitle($_POST["clientPhone"]);
    if (isset($_POST["clientCompany"]))
        $_POST["clientCompany"] = cleanTitle($_POST["clientCompany"]);
    if (isset($_POST["siteAddress"]))
        $_POST["siteAddress"] = cleanTitle($_POST["siteAddress"]);
    if (isset($_POST["estimatedBudget"]))
        $_POST["estimatedBudget"] = floatval($_POST["estimatedBudget"]);
    if (isset($_POST["assignedTo"]))
        $_POST["assignedTo"] = intval($_POST["assignedTo"]);

    // Generate leadNo if not provided
    if (empty($_POST["leadNo"])) {
        $_POST["leadNo"] = "LEAD-" . date("Ymd") . "-" . rand(1000, 9999);
    }

    $DB->table = $DB->pre . "sky_padel_lead";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $leadID = $DB->insertID;
        if ($leadID) {
            setResponse(array("err" => 0, "param" => "id=$leadID"));
        }
    } else {
        setResponse(array("err" => 1));
    }
}
//End.

//Start: To update lead data.
function updateLead()
{
    global $DB;
    $leadID = intval($_POST["leadID"]);

    if (isset($_POST["clientName"]))
        $_POST["clientName"] = cleanTitle($_POST["clientName"]);
    if (isset($_POST["clientEmail"]))
        $_POST["clientEmail"] = cleanTitle($_POST["clientEmail"]);
    if (isset($_POST["clientPhone"]))
        $_POST["clientPhone"] = cleanTitle($_POST["clientPhone"]);
    if (isset($_POST["clientCompany"]))
        $_POST["clientCompany"] = cleanTitle($_POST["clientCompany"]);
    if (isset($_POST["siteAddress"]))
        $_POST["siteAddress"] = cleanTitle($_POST["siteAddress"]);
    if (isset($_POST["estimatedBudget"]))
        $_POST["estimatedBudget"] = floatval($_POST["estimatedBudget"]);
    if (isset($_POST["assignedTo"]))
        $_POST["assignedTo"] = intval($_POST["assignedTo"]);

    $DB->table = $DB->pre . "sky_padel_lead";
    $DB->data = $_POST;
    if ($DB->dbUpdate("leadID=?", "i", array($leadID))) {
        setResponse(array("err" => 0, "param" => "id=$leadID"));
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
                addLead();
                break;
            case "UPDATE":
                updateLead();
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "sky_padel_lead", "PK" => "leadID"));
}
?>
