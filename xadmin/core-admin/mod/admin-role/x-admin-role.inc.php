<?php

function getRoleKey($roleID = 0)
{
    $roleKey = "";
    if (isset($roleID) && $roleID  > 0) {
        global $DB;
        $DB->vals = array($roleID);
        $DB->types = "i";
        $DB->sql = "SELECT roleKey FROM `" . $DB->pre . "x_admin_role` WHERE roleID=?";
        $DB->dbRow();
        if ($DB->numRows > 0) {
            $roleKey = $DB->row["roleKey"];
        }
    }
    return $roleKey;
}

function getAccess($roleID = 0)
{
    $arr = array();
    global $DB;
    if ($roleID) {
        $DB->types = "i";
        $DB->vals = array($roleID);
        $DB->sql = "SELECT * FROM `" . $DB->pre . "x_admin_role_access` WHERE roleID=?";
        $S = $DB->dbRows();
        foreach ($S as $v)
            $arr[$v["adminMenuID"]] = json_decode($v["accessType"]);
    }
    return $arr;
}

function addUserAccess($roleID = 0)
{
    global $DB;
    if (isset($_POST["access"])) {
        $DB->table = $DB->pre . "x_admin_role_access";
        foreach ($_POST["access"] as $adminMenuID => $v) {
            if ($v) {
                $DB->data = array("roleID" => $roleID, "adminMenuID" => $adminMenuID, "accessType" => json_encode($v));
                $DB->dbInsert();
            }
        }
    }
}

function addAdminRole()
{
    global $DB;

    if (!isset($_POST['parentID']) || $_POST['parentID'] == '')
        $_POST['parentID'] = 0;

    resetAutoIncreament($DB->pre . "x_admin_role", "roleID");
    $DB->table = $DB->pre . "x_admin_role";
    $DB->data = array("roleKey" => $_POST["roleKey"], "roleName" => $_POST["roleName"], "roleEmail" => $_POST["roleEmail"], "rolePage" => $_POST["rolePage"], "parentID" => ($_POST['parentID'] ?? 0), "orgID" => $_POST["orgID"] ?? 0);
    if ($DB->dbInsert()) {
        $roleID = $DB->insertID;
        if ($roleID) {
            addUserAccess($roleID);
            updateRoleParentKey($roleID, $_POST["parentID"]);
            setResponse(["err" => 0, "param" => "id=$roleID"]);
        }
    } else {
        setResponse(["err" => 1]);
    }
}

function updateAdminRole()
{
    global $DB;

    if (!isset($_POST['parentID']) || $_POST['parentID'] == '')
        $_POST['parentID'] = 0;

    resetAutoIncreament($DB->pre . "x_admin_role", "roleID");
    $roleID = intval($_POST["roleID"]);
    $DB->table = $DB->pre . "x_admin_role";
    $DB->data = array("roleKey" => $_POST["roleKey"], "roleName" => $_POST["roleName"], "roleEmail" => $_POST["roleEmail"], "rolePage" => $_POST["rolePage"], "parentID" => ($_POST['parentID'] ?? 0), "orgID" => $_POST["orgID"] ?? 0);
    if ($DB->dbUpdate("roleID=?", 'i', array($roleID))) {
        if ($roleID) {
            $whereR = '';
            $DB->vals = array($roleID);
            $DB->types = "i";
            if ($_SESSION[SITEURL]['MXID'] != "SUPER") {
                $whereR = ' AND adminMenuID < ?';
                array_push($DB->vals, "100000");
                $DB->types .= "i";
            }

            $DB->sql = "DELETE FROM `" . $DB->pre . "x_admin_role_access` WHERE roleID=? $whereR";
            $DB->dbQuery();
            addUserAccess($roleID);
            updateRoleParentKey($roleID, $_POST["parentID"]);
            setResponse(["err" => 0, "param" => "id=$roleID"]);
        } else {
            setResponse(["err" => 1]);
        }
    }
}

//roleKeyP

function updateRoleParentKey($roleID = 0, $parentID = 0)
{
    if (!isset($_POST['parentID']) || $_POST['parentID'] == '')
        $_POST['parentID'] = 0;
    if (isset($roleID) && $roleID > 0) {
        global $DB;
        $roleKeyP = "";
        if (isset($roleID) && intval($parentID)) {
            $DB->types = "i";
            $DB->vals = array($parentID);
            $DB->sql = "SELECT roleKey FROM `" . $DB->pre . "x_admin_role` WHERE roleID=?";
            $DB->dbRow();
            if ($DB->numRows > 0) {
                $roleKeyP = $DB->row["roleKey"];
            }
        }
        $DB->sql = "UPDATE `" . $DB->pre . "x_admin_role` SET roleKeyP = '" . $roleKeyP . "' WHERE roleID=$roleID";
        $DB->dbQuery();
    }
}

if (isset($_POST["xAction"])) {

    require_once("../../../../core/core.inc.php");
    // Bypass JWT validation for admin AJAX requests (use PHP session auth instead)
    $MXRES = mxCheckRequest(true, true);
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
                addAdminRole();
                break;
            case "UPDATE":
                updateAdminRole();
                break;
            case "updateRoleParentKey":
                updateRoleParentKey(($_POST["roleID"] ?? 0), ($_POST["parentID"] ?? 0));
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "x_admin_role", "PK" => "roleID"));
}
