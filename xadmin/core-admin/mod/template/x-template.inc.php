<?php
function addUpdateTemplate()
{
    global $DB;
    $templateID = 0;
    if (isset($_POST["modDir"]) && trim($_POST["modDir"]) !== "" && isset($_POST["seoUri"]) && trim($_POST["seoUri"]) !== "") {

        if ($_POST["modType"] == 1) {
            unset($_POST["tblMaster"]);
            unset($_POST["pkMaster"]);
            unset($_POST["titleMaster"]);
            unset($_POST["tplFileCol"]);
        }

        $DB->types = "iss";
        $DB->vals = array(1, trim($_POST["modDir"]), trim($_POST["seoUri"]));
        $DB->sql = "SELECT templateID FROM `" . $DB->pre . "x_template` WHERE status=? AND modDir=? AND seoUri=? ORDER BY xOrder ASC";
        $DB->dbRow();
        $totRec = $DB->numRows;
        $DB->table = $DB->pre . "x_template";
        $DB->data = $_POST;

        if ($totRec > 0) {
            $templateID = $DB->row["templateID"];
            $DB->dbUpdate("templateID=?", "i", array($templateID));
        } else {
            if ($DB->dbInsert()) {
                $templateID = $DB->insertID;
            }
        }
    }

    if ($templateID > 0) {
        if ($_POST["modType"] == 1) {
            if (isset($_POST["metaTitleT"]) && isset($_POST["langCodeT"]) && count($_POST["metaTitleT"]) > 0) {
                foreach ($_POST["langCodeT"] as $k => $langCode) {
                    //if (trim($_POST["metaTitleT"][$k] !== "")) {
                    saveMeta(array("langCode" => $langCode, "metaKey" => trim($_POST["metaKey"]), "metaTitle" => trim($_POST["metaTitleT"][$k]), "metaKeyword" => trim($_POST["metaKeywordT"][$k]), "metaDesc" => trim($_POST["metaDescT"][$k])));
                    // }
                }
            }
        }
        setResponse(["err" => 0, "param" => "modDir=" . $_POST["modDir"] . "&seoUri=" . $_POST["seoUri"] . "&fileMod=" . $_POST["fileMod"]]);
    } else {
        setResponse(["err" => 1]);

    }
}
$arrModType = array(0 => "Dynamic", 1 => "Static");
if (isset($_POST["xAction"])) {
    require_once("../../../../core/core.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "UPDATE":
                addUpdateTemplate();
                break;
            case 'mxGetTableFlds':
                $arr = mxGetTableFlds($DB->pre . $_REQUEST["table"]);
                if (count($arr) > 0) {
                    $MXRES["err"] = 0;
                    $MXRES["data"] = getArrayDD(["data" => ["data" => $arr]]);
                }
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "x_template", "PK" => "templateID"));
}
