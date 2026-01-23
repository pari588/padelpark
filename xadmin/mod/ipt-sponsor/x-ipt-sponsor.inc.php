<?php
/*
addSponsor = To save Sponsor data.
updateSponsor = To update Sponsor data.
*/

function addSponsor()
{
    global $DB;

    $DB->table = $DB->pre . "ipt_sponsor";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        setResponse(array("err" => 0, "param" => "id=" . $DB->insertID));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateSponsor()
{
    global $DB;
    $sponsorID = intval($_POST["sponsorID"]);

    $DB->table = $DB->pre . "ipt_sponsor";
    $DB->data = $_POST;
    if ($DB->dbUpdate("sponsorID=?", "i", array($sponsorID))) {
        setResponse(array("err" => 0, "param" => "id=" . $sponsorID));
    } else {
        setResponse(array("err" => 1));
    }
}

// Handle AJAX actions
if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest(true, true); // Skip JWT token validation, use session auth
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addSponsor(); break;
            case "UPDATE": updateSponsor(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "ipt_sponsor", "PK" => "sponsorID"));
}
