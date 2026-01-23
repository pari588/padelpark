<?php
/*
updateHome = To update home data.
addUpdateHomeSlider = To add and update home slider's detail data.
addUpdateBestPartner = To add and update home slider's detail data.
*/
//Start: To update Product data.
function  updateHome()
{
    global $DB;
    $homeID = intval($_POST["homeID"]);
    if (isset($_POST["homeTitle"]))
        $_POST["homeTitle"] = cleanTitle($_POST["homeTitle"]);
    if (isset($_POST["homeDesc"]))
        $_POST["homeDesc"] = cleanHtml($_POST["homeDesc"]);
    if (isset($_POST["contactUsUrl"]))
        $_POST["contactUsUrl"] = cleanTitle($_POST["contactUsUrl"]);
    if (isset($_POST["aboutUrl"]))
        $_POST["aboutUrl"] = cleanTitle($_POST["aboutUrl"]);
    if (isset($_POST["otherTitleOne"]))
        $_POST["otherTitleOne"] = cleanTitle($_POST["otherTitleOne"]);
    if (isset($_POST["otherTitleTwo"]))
        $_POST["otherTitleTwo"] = trim($_POST["otherTitleTwo"]);
    if (isset($_POST["otherTitleThree"]))
        $_POST["otherTitleThree"] = cleanTitle($_POST["otherTitleThree"]);
    if (isset($_POST["otherTitleFour"]))
        $_POST["otherTitleFour"] = cleanTitle($_POST["otherTitleFour"]);
    if (isset($_POST["otherDescOne"]))
        $_POST["otherDescOne"] = cleanHtml($_POST["otherDescOne"]);
    if (isset($_POST["otherDescTwo"]))
        $_POST["otherDescTwo"] = cleanHtml($_POST["otherDescTwo"]);
    if (isset($_POST["otherDescThree"]))
        $_POST["otherDescThree"] = cleanHtml($_POST["otherDescThree"]);
    if (isset($_POST["otherDescFour"]))
        $_POST["otherDescFour"] = cleanHtml($_POST["otherDescFour"]);
    if (isset($_POST["serviceTitle"]))
        $_POST["serviceTitle"] = cleanTitle($_POST["serviceTitle"]);
    if (isset($_POST["serviceSubTitle"]))
        $_POST["serviceSubTitle"] = cleanTitle($_POST["serviceSubTitle"]);
    if (isset($_POST["effectiveIncrease"]))
        $_POST["effectiveIncrease"] = cleanTitle($_POST["effectiveIncrease"]);
    if (isset($_POST["yearsExperience"]))
        $_POST["yearsExperience"] = cleanTitle($_POST["yearsExperience"]);
    $_POST["serviceImg"] = mxGetFileName("serviceImg");


    $DB->table = $DB->pre . "home";
    $DB->data = $_POST;

    if ($DB->dbUpdate("homeID=?", "i", array($homeID))) {
        if ($homeID) {
            //To delete existing home slider data.
            $DB->vals = array($homeID);
            $DB->types = "i";
            $DB->sql = "DELETE FROM " . $DB->pre . "home_slider WHERE homeID=?";
            $DB->dbQuery();
            // End.
            addUpdateHomeSlider($homeID);
            //To delete existing best partner data.
            $DB->vals = array($homeID);
            $DB->types = "i";
            $DB->sql = "DELETE FROM " . $DB->pre . "home_best_partner WHERE homeID=?";
            $DB->dbQuery();
            // End.
            addUpdateBestPartner($homeID);
            setResponse(array("err" => 0, "param" => "id=$homeID"));
        }
    } else {
        setResponse(array("err" => 1));
    }
}
//End.
//Start: To add and update home slider's detail data.
function addUpdateHomeSlider($homeID = 0)
{
    global $DB;
    if ($homeID) {
        if (isset($_POST["homeSliderID"]) && count($_POST["homeSliderID"]) > 0) {
            for ($i = 0; $i < count($_POST["homeSliderID"]); $i++) {
                $arrIn = array(
                    "homeID" => $homeID,
                    "sliderTitle" => $_POST["sliderTitle"][$i]??"",
                    "sliderImage" => $_POST["sliderImage"][$i]??"",
                );
                $arrIn["sliderImage"] = mxGetFileName("sliderImage", $i);
                $DB->table = $DB->pre . "home_slider";
                $DB->data = $arrIn;
                $DB->dbInsert();
            }
        }
    }
}
//End
//Start: To add and update Best Partner's detail data.
function addUpdateBestPartner($homeID = 0)
{
    global $DB;
    if ($homeID) {
        if (isset($_POST["bestPartnerID"]) && count($_POST["bestPartnerID"]) > 0) {
            for ($i = 0; $i < count($_POST["bestPartnerID"]); $i++) {
                $arrIn = array(
                    "homeID" => $homeID,
                    "bestPartnerTitle" => $_POST["bestPartnerTitle"][$i],
                );
                $arrIn["bestPartnerImg"] = mxGetFileName("bestPartnerImg", $i);
                $DB->table = $DB->pre . "home_best_partner";
                $DB->data = $arrIn;
                $DB->dbInsert();
            }
        }
    }
}
//End
if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "UPDATE":
                updateHome();
                break;
            case "mxDelFile":
                $param = array("dir" => "home", "tbl" => "home", "pk" => "homeID");
                if ($_REQUEST["fld"] == "bestPartnerImg") {
                    $param = array("dir" => "home", "tbl" => "home_best_partner", "pk" => "homeID");
                }
                if ($_REQUEST["fld"] == "sliderImage") {
                    $param = array("dir" => "home", "tbl" => "home_slider", "pk" => "homeID");
                }
                if ($_REQUEST["fld"] == "serviceImg") {
                    $param = array("dir" => "home", "tbl" => "home", "pk" => "homeID");
                }
                mxDelFile(array_merge($_REQUEST, $param));
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "home", "PK" => "homeID", "UDIR" => array("bestPartnerImg" => "home", "sliderImage" => "home", "serviceImg" => "home")));
}
