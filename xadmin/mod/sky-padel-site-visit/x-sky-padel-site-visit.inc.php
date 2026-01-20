<?php
function addSiteVisit()
{
    global $DB;
    if (isset($_POST["leadID"])) $_POST["leadID"] = intval($_POST["leadID"]);
    if (isset($_POST["scheduledBy"])) $_POST["scheduledBy"] = intval($_POST["scheduledBy"]);

    $DB->table = $DB->pre . "sky_padel_site_visit";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $visitID = $DB->insertID;
        // Update lead status to "Site Visit Scheduled"
        $DB->vals = array("Site Visit Scheduled", $visitID);
        $DB->types = "si";
        $DB->sql = "UPDATE " . $DB->pre . "sky_padel_lead SET leadStatus=? WHERE leadID=(SELECT leadID FROM " . $DB->pre . "sky_padel_site_visit WHERE visitID=?)";
        $DB->dbQuery();
        setResponse(array("err" => 0, "param" => "id=$visitID"));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateSiteVisit()
{
    global $DB;
    $visitID = intval($_POST["visitID"]);
    if (isset($_POST["leadID"])) $_POST["leadID"] = intval($_POST["leadID"]);
    if (isset($_POST["scheduledBy"])) $_POST["scheduledBy"] = intval($_POST["scheduledBy"]);

    $DB->table = $DB->pre . "sky_padel_site_visit";
    $DB->data = $_POST;
    if ($DB->dbUpdate("visitID=?", "i", array($visitID))) {
        setResponse(array("err" => 0, "param" => "id=$visitID"));
    } else {
        setResponse(array("err" => 1));
    }
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addSiteVisit(); break;
            case "UPDATE": updateSiteVisit(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "sky_padel_site_visit", "PK" => "visitID"));
}
?>
