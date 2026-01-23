<?php
function addSiteReport()
{
    global $DB;
    if (isset($_POST["visitID"])) $_POST["visitID"] = intval($_POST["visitID"]);
    if (isset($_POST["leadID"])) $_POST["leadID"] = intval($_POST["leadID"]);
    if (isset($_POST["reportBy"])) $_POST["reportBy"] = intval($_POST["reportBy"]);
    if (isset($_POST["estimatedCost"])) $_POST["estimatedCost"] = floatval($_POST["estimatedCost"]);
    $_POST["photos"] = mxGetFileName("photos");

    $DB->table = $DB->pre . "sky_padel_site_report";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $reportID = $DB->insertID;
        // Update lead status
        $DB->vals = array("Site Visit Done", $_POST["leadID"]);
        $DB->types = "si";
        $DB->sql = "UPDATE " . $DB->pre . "sky_padel_lead SET leadStatus=? WHERE leadID=?";
        $DB->dbQuery();
        setResponse(array("err" => 0, "param" => "id=$reportID"));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateSiteReport()
{
    global $DB;
    $reportID = intval($_POST["reportID"]);
    if (isset($_POST["visitID"])) $_POST["visitID"] = intval($_POST["visitID"]);
    if (isset($_POST["leadID"])) $_POST["leadID"] = intval($_POST["leadID"]);
    if (isset($_POST["reportBy"])) $_POST["reportBy"] = intval($_POST["reportBy"]);
    if (isset($_POST["estimatedCost"])) $_POST["estimatedCost"] = floatval($_POST["estimatedCost"]);
    $_POST["photos"] = mxGetFileName("photos");

    $DB->table = $DB->pre . "sky_padel_site_report";
    $DB->data = $_POST;
    if ($DB->dbUpdate("reportID=?", "i", array($reportID))) {
        setResponse(array("err" => 0, "param" => "id=$reportID"));
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
            case "ADD": addSiteReport(); break;
            case "UPDATE": updateSiteReport(); break;
            case "mxDelFile":
                $param = array("dir" => "sky-padel-site-report", "tbl" => "sky_padel_site_report", "pk" => "reportID");
                mxDelFile(array_merge($_REQUEST, $param));
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "sky_padel_site_report", "PK" => "reportID", "UDIR" => array("photos" => "sky-padel-site-report")));
}
?>
