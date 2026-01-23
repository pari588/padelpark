<?php

function addSiteMenu()
{
    global $DB;

    if ($_POST["menuType"] == "exlink")
        $_POST["seoUri"] = $_POST["exlink"];

    if ($_POST["menuType"] == "dynamic")
        $_POST["templateID"] = $_POST["templateIDD"];

    if ($_POST["menuType"] == "static")
        $_POST["templateID"] = $_POST["templateIDS"];

    if (!isset($_POST["menuTarget"]))
        $_POST["menuTarget"] = 0;
    $_POST["menuImage"] = mxGetFileName("menuImage");

    $DB->table = $DB->pre . "x_menu";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $menuID = $DB->insertID;
        if ($menuID) {
            setResponse(["err" => 0, "param" => "id=$menuID"]);
        } else {
            setResponse(["err" => 1]);
        }
    }
}

function updateSiteMenu()
{
    global $DB;
    $menuID = intval($_POST["menuID"]);

    if ($_POST["menuType"] == "exlink")
        $_POST["seoUri"] = $_POST["exlink"];

    if ($_POST["menuType"] == "dynamic")
        $_POST["templateID"] = $_POST["templateIDD"];

    if ($_POST["menuType"] == "static")
        $_POST["templateID"] = $_POST["templateIDS"];

    if (!isset($_POST["menuTarget"]))
        $_POST["menuTarget"] = 0;
    $_POST["menuImage"] = mxGetFileName("menuImage");

    $DB->table = $DB->pre . "x_menu";
    $DB->data = $_POST;
    if ($DB->dbUpdate("menuID=?", "i", array($menuID))) {
        if ($menuID) {
            setResponse(["err" => 0, "param" => "id=$menuID"]);
        } else {
            setResponse(["err" => 1]);
        }
    }
}

function getTemplateMod($modType = 0)
{
    global $DB;
    $arrWhere    = array("sql" => "modType=? ", "types" => "i", "vals" => array($modType));
    $params = ["table" => $DB->pre . "x_template", "key" => "templateID", "val" => "seoUri", "where" => $arrWhere, "lang" => false];
    $arrTplMod  = getDataArray($params);

    return $arrTplMod;
}

function getAutocomplete()
{
    $json = array();
    if (isset($_POST["send"]) && $_POST["send"] != "") {
        $arrFld = json_decode($_POST["send"]);
        if (isset($arrFld)) {
            $templateIDD = intval($arrFld->templateIDD);
            if ($templateIDD > 0) {
                global $DB;
                $DB->vals = array(1, trim($templateIDD));
                $DB->types = "is";
                $DB->sql = "SELECT pkMaster,tblMaster,titleMaster FROM `" . $DB->pre . "x_template` WHERE status=? AND templateID = ?";
                $DB->dbRow();
                if ($DB->numRows > 0) {
                    extract($DB->row);
                    $searchString = trim($_POST['searchString']);
                    if (isset($tblMaster) && $tblMaster != "" && isset($titleMaster) && $titleMaster != "" && isset($searchString) && $searchString != "") {
                        $DB->vals = array(1, $searchString);
                        $DB->types = "is";
                        $DB->sql = "SELECT $pkMaster,$titleMaster,seoUri FROM `" . $DB->pre . "$tblMaster` WHERE status=? AND `$titleMaster` LIKE CONCAT('%',?,'%') ORDER BY `$titleMaster` ASC LIMIT 100";
                        $data = $DB->dbRows();
                        if ($DB->numRows > 0) {
                            $arr = array();
                            foreach ($data as $v) {
                                $json[] = array('value' => $v["seoUri"], 'label' => $v[$titleMaster], 'data' => array("menuTitle" => $v[$titleMaster], "seoUri" => $v["seoUri"]));
                            }
                        }
                    }
                }
            }
        }
    }
    return json_encode($json);
}
//-------------------------------
if (isset($_POST["xAction"])) {
    require_once("../../../../core/core.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
                addSiteMenu();
                break;
            case "UPDATE":
                updateSiteMenu();
                break;
            case 'getAutocomplete':
                echo getAutocomplete();
                exit;
                break;
            case "mxDelFile":
                if ($_REQUEST["fld"] == "menuImage") {
                    $param = array("dir" => "menu", "tbl" => "menu", "pk" => "menuID");
                }
                mxDelFile(array_merge($_REQUEST, $param));
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "x_menu", "PK" => "menuID", "UDIR" => array("menuImage" => "menu")));
}
