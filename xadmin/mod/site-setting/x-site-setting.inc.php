<?php
/*
updateSiteSetting = To update sitesetting data.
*/
//Start: To save sitesetting data.

//Start: To update sitesetting data.
function  updateSiteSetting()
{
    global $DB;
    $siteSettingID = intval($_POST["siteSettingID"]);
    if (isset($_POST["contactNo"]))
        $_POST["contactNo"] = cleanTitle($_POST["contactNo"]);
    if (isset($_POST["contactMail"]))
        $_POST["contactMail"] = cleanTitle($_POST["contactMail"]);
    if (isset($_POST["twitterUrl"]))
        $_POST["twitterUrl"] = trim($_POST["twitterUrl"]);
    if (isset($_POST["facebookUrl"]))
        $_POST["facebookUrl"] = trim($_POST["facebookUrl"]);
    if (isset($_POST["pintrestUrl"]))
        $_POST["pintrestUrl"] = cleanTitle($_POST["pintrestUrl"]);
    if (isset($_POST["instaUrl"]))
        $_POST["instaUrl"] = cleanTitle($_POST["instaUrl"]);
    if (isset($_POST["enquiryFormUrl"]))
        $_POST["enquiryFormUrl"] = trim($_POST["enquiryFormUrl"]);
    if (isset($_POST["siteFooterInfo"]))
        $_POST["siteFooterInfo"] = trim($_POST["siteFooterInfo"]);
    if (isset($_POST["communicationEmail"]))
        $_POST["communicationEmail"] = cleanTitle($_POST["communicationEmail"]);
        
    $DB->table = $DB->pre . "site_setting";
    $DB->data = $_POST;

    if ($DB->dbUpdate("siteSettingID=?", "i", array($siteSettingID))) {
        if ($siteSettingID) {
            setResponse(array("err" => 0, "param" => "id=$siteSettingID"));
        }
    } else {
        setResponse(array("err" => 1));
    }
}
//End.


if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "UPDATE":
                updateSiteSetting();
                break;
            case "mxDelFile":
                $param = array("dir" => "site_setting", "tbl" => "site_setting", "pk" => "siteSettingID");
                mxDelFile(array_merge($_REQUEST, $param));
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "site_setting", "PK" => "siteSettingID"));
}
