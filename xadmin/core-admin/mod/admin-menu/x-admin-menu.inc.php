<?php

function getSubFolders($mPath = "")
{
    $arrFolders = array();
    $arrF = glob($mPath . '/*', GLOB_ONLYDIR);
    if (count($arrF) > 0) {
        foreach ($arrF as $path) {
            $pathArr = explode("/", $path);
            $arrF = array_values(array_filter($pathArr));
            $arrFolders[] = end($arrF);
        }
    }
    return $arrFolders;
}

function recreateAdminMenu()
{
    $delCnt = 0;
    $addCnt = 0;
    $arrFModules = getSubFolders(ADMINPATH . "/mod");
    if (count($arrFModules) > 0) {
        global $DB;
        $seoUri = "'" . implode("','", $arrFModules) . "'";
        $DB->types = implode("", array_fill(0, count($arrFModules), "s"));
        $DB->vals = $arrFModules;
        $DB->sql = "DELETE FROM " . $DB->pre . "x_admin_role_access WHERE adminMenuID IN(SELECT DISTINCT(adminMenuID) FROM " . $DB->pre . "x_admin_menu WHERE seoUri NOT IN(" . implode(",", array_fill(0, count($arrFModules), "?")) . "))";
        $DB->dbQuery();
        $DB->sql = "DELETE FROM " . $DB->pre . "x_admin_menu WHERE seoUri NOT IN($seoUri) AND menuType=0";
        if ($DB->dbQuery()) {
            $delCnt = $delCnt + $DB->affectedRows;
        }

        $DB->types = "i";
        $DB->vals = array(0);
        $DB->sql = "SELECT DISTINCT(parentID) AS parentID FROM " . $DB->pre . "x_admin_menu WHERE menuType=?";
        $DB->dbRows();
        if ($DB->numRows > 0) {
            $arrPID = array();
            foreach ($DB->rows as $d) {
                $arrPID[] = $d["parentID"];
            }
            if ($arrPID) {
                $DB->sql = "DELETE FROM " . $DB->pre . "x_admin_menu WHERE menuType=1 AND adminMenuID NOT IN(" . implode(",", $arrPID) . ")";
                if ($DB->dbQuery()) {
                    $delCnt = $delCnt + $DB->affectedRows;
                }
            }
        }

        $arrAModules = array();
        $DB->sql = "SELECT seoUri FROM " . $DB->pre . "x_admin_menu";
        $DB->dbRows();
        if ($DB->numRows > 0) {
            foreach ($DB->rows as $d)
                $arrAModules[] = $d["seoUri"];
        }

        if ($arrAModules) {
            $arrInsert = array_diff($arrFModules, $arrAModules);
        } else {
            $arrInsert = $arrFModules;
        }

        if ($arrInsert) {
            resetAutoIncreament($DB->pre . "x_admin_menu", "adminMenuID");
            foreach ($arrInsert as $m) {
                $DB->data = array("menuType" => "0", "menuTitle" => ucfirst(str_replace("-", " ", $m)), "parentID" => "0", "seoUri" => "$m", "status" => "1");
                $DB->table = $DB->pre . "x_admin_menu";
                if ($DB->dbInsert()) {
                    $addCnt++;
                }
            }
        }
    }
    global $MXRES;
    $MXRES["msg"] = "MENUS ADDED : $addCnt\n MENUS DELETED $delCnt";
}

function addAdminMenu()
{
    global $DB;
    resetAutoIncreament($DB->pre . "x_admin_menu", "adminMenuID");
    $_POST["status"] = 1;
    $_POST["menuType"] = 1;
    if (!isset($_POST['hideMenu']) || trim($_POST['hideMenu']) == "")
        $_POST['hideMenu'] = 0;

    $DB->table = $DB->pre . "x_admin_menu";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $adminMenuID = $DB->insertID;
        if ($adminMenuID) {
            setResponse(["err"=>0,"param"=>"id=$adminMenuID"]);
        }
    } else {
        setResponse(["err"=>1]);;
    }
}

function updateAdminMenu()
{
    global $DB;
    resetAutoIncreament($DB->pre . "x_admin_menu", "adminMenuID");
    $adminMenuID = intval($_POST["adminMenuID"]);
    if (!isset($_POST['hideMenu']) || trim($_POST['hideMenu']) == "")
        $_POST['hideMenu'] = 0;
    $DB->table = $DB->pre . "x_admin_menu";
    $DB->data = $_POST;
    if ($DB->dbUpdate("adminMenuID=?", "i", array($adminMenuID))) {
        setResponse(["err"=>0,"param"=>"id=$adminMenuID"]);
    } else {
        setResponse(["err"=>1]);
    }
}

if (isset($_POST["xAction"])) {
    require_once("../../../../core/core.inc.php");
    // Use ignoreToken=true since this is a session-based form, not JWT-based
    $MXRES = mxCheckRequest(true, true);
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
                addAdminMenu();
                break;
            case "UPDATE":
                updateAdminMenu();
                break;
            case "recreateAdminMenu":
                recreateAdminMenu();
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "x_admin_menu", "PK" => "adminMenuID"));
}
