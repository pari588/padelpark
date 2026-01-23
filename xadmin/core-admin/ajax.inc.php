<?php
// for admin code modules please add language related columns

function getValsTypes()
{
}

function changeStatus($status = 0)
{
    global $DB, $MXRES;
    $MXRES["err"] = 1;
    $MXRES["msg"] = "Sorry, Cannot perform this action..";
    $mod = htmlspecialchars(trim($_REQUEST["modName"]));
    $vals = trim($_REQUEST["id"]);

    if (isset($mod) && $mod != "" && isset($vals) && $vals != "") {
        global $MXSET;
        $table = trim($_SESSION[SITEURL][$mod]["TBL"]);
        $pk = trim($_SESSION[SITEURL][$mod]["PK"]);

        // Check if this module uses custom table naming (without mx_ prefix)
        $no_prefix = isset($_SESSION[SITEURL][$mod]["NO_PREFIX"]) && $_SESSION[SITEURL][$mod]["NO_PREFIX"] === true;
        if (!$no_prefix) {
            $table = $DB->pre . $table;  // Add mx_ prefix if not a custom table
        }

        $wLang = '';
        $inWhere = implode(",", array_fill(0, count(explode(",", $vals)), "?"));
        if ($MXSET["MULTILINGUAL"] == 1 && $MXSET["LANGTYPE"] == 0) {
            $wLang = " OR parentLID IN(" . $inWhere . ")";
            $vals = $vals . "," . $vals;
        }

        $DB->vals = explode(",", $vals);
        array_unshift($DB->vals, $status);
        $DB->types = implode("", array_fill(0, count($DB->vals), "i"));

        $valsL = $DB->vals;
        $typeL = $DB->types;
        $DB->sql = "UPDATE `" . $table . "` SET status=? WHERE `$pk` IN(" . $inWhere . ")" . $wLang;
        if ($DB->dbQuery()) {
            $MXRES["err"] = 0;
        }

        if ($MXSET["LANGTYPE"] == 1 && $MXSET["MULTILINGUAL"] == 1) {
            if ($DB->ifTableExists($table . "_l")) {
                $DB->vals = $valsL;
                $DB->types = $typeL;
                $DB->sql = "UPDATE `" . $table . "_l` SET status=? WHERE `parentLID` IN(" . $inWhere . ")";
                if ($DB->dbQuery()) {
                    $MXRES["err"] = 0;
                }
            }
        }
    }
}

function delete()
{
    global $DB, $MXRES;
    $MXRES["err"] = 1;
    //$MXRES["msg"] = "Sorry, Cannot delete this record..";
    $MXRES["msg"] = "Sorry, Delete disabled, Please contact admin..";
    // $mod = htmlspecialchars(trim($_REQUEST["modName"]));
    // $vals = intval($_REQUEST["id"]);
    // if (isset($mod) && $mod != "" && isset($vals) && $vals != "") {
    //     global $MXSET;
    //     $table = trim($_SESSION[SITEURL][$mod]["TBL"]);
    //     $pk = trim($_SESSION[SITEURL][$mod]["PK"]);
    //     $wLang = '';
    //     $inWhere = implode(",", array_fill(0, count(explode(",", $vals)), "?"));
    //     if ($MXSET["MULTILINGUAL"] == 1 && $MXSET["LANGTYPE"] == 0) {
    //         $wLang = " OR parentLID IN(" . $inWhere . ")";
    //         $vals = $vals . "," . $vals;
    //     }
    //     $valsL = $DB->vals = explode(",", $vals);
    //     $typeL = $DB->types = implode("", array_fill(0, count($DB->vals), "i"));

    //     $DB->sql = "DELETE FROM `" . $DB->pre . "$table` WHERE `$pk` IN(" . $inWhere . ")" . $wLang;
    //     if ($DB->dbQuery()) {
    //         $MXRES["err"] = 0;
    //     }

    //     if ($MXSET["LANGTYPE"] == 1 && $MXSET["MULTILINGUAL"] == 1) {
    //         if ($DB->ifTableExists($DB->pre . $table . "_l")) {
    //             $DB->vals = $valsL;
    //             $DB->types = $typeL;
    //             $DB->sql = "DELETE FROM `" . $DB->pre . $table . "_l` WHERE `parentLID` IN(" . $inWhere . ")";
    //             if ($DB->dbQuery()) {
    //                 $MXRES["err"] = 0;
    //             }
    //         }
    //     }
    // }
}

function setTheme($type = "")
{
    if (isset($type) && $type != "") {
        global $DB;
        if ($_SESSION[SITEURL]["MXID"] == "SUPER") {
            $DB->vals = array($type, "THEME");
            $DB->types = "ss";
            $DB->sql = "UPDATE " . $DB->pre . "x_setting SET settingVal=? WHERE settingKey=?";
        } else {
            $DB->vals = array($type, $_SESSION[SITEURL]["MXID"]);
            $DB->types = "si";
            $DB->sql = "UPDATE " . $DB->pre . "x_admin_user SET userTheme=? WHERE userID=?";
        }
        $DB->dbQuery();
        $_SESSION[SITEURL]["THEME"] = $type;
    }
}

function setFontSize($type = "")
{
    if (isset($type) && $type != "") {
        global $DB;
        if ($_SESSION[SITEURL]["MXID"] == "SUPER") {
            $DB->vals = array($type, "FONT");
            $DB->types = "ss";
            $DB->sql = "UPDATE " . $DB->pre . "x_setting SET settingVal=? WHERE settingKey=?";
        } else {
            $DB->vals = array($type, $_SESSION[SITEURL]["MXID"]);
            $DB->types = "si";
            $DB->sql = "UPDATE " . $DB->pre . "x_admin_user SET userFont=? WHERE userID=?";
        }
        $DB->dbQuery();
        $_SESSION[SITEURL]["FONT"] = $type;
    }
}

function getLogData($tblName = "", $pkName = "", $pkValue = 0)
{   
    $data = '';
    if ($tblName != "" && $pkName != "" && $pkValue > 0) {
        global $DB, $MXRES;
        $DB->types = "ssi";
        $DB->vals = array($DB->pre . $tblName, $pkName, $pkValue);

        $arrActType = array(0 => "ADDED", 1 => "UPDATED");
        $DB->sql = "SELECT U.displayName,L.actionBy,L.actionDate,L.actionType FROM `" . $DB->pre . "x_log_action` AS L
                    LEFT JOIN `" . $DB->pre . "x_admin_user` AS U ON U.userID = L.actionBy
                    WHERE L.tblName= ? AND L.pkName = ? AND L.pkValue =? ";
        $DB->dbRows();
        if ($DB->numRows > 0) {
            $data = '<table width="100%" border="0" cellspacing="2" cellpadding="6" class="tbl-list xsmall">
                        <thead>
                            <tr>
                                
                                <th align="left" title="' . "$tblName, $pkName, $pkValue" . '">BY USER</th>
                                <th width="1%">DATE</th>
                                <th width="1%">TYPE</th>
                            </tr>
                        </thead>
                        <tbody>';
            foreach ($DB->rows as $d) {

                if ($d["actionBy"] == 0) {
                    $displayName = "Super Admin";
                } else {
                    $displayName = $d["displayName"];
                }
                $data .= '<tr>
                            <td align="left" nowrap>' . $displayName . '</td>
                            <td align="center" nowrap>' . $d["actionDate"] . '</td>
                            <td align="center" nowrap>' . $arrActType[$d["actionType"]] . '</td>
                         </tr>';
            }
            $data .= '</tbody>
                    </table>';
            $MXRES["err"] = 0;
        }
    }
    return $data;
}

function getModLog()
{
    global $MXRES;
    $MXRES["err"] = 1;
    $MXRES["msg"] = "Sorry, No log found for this record...";
    if (isset($_POST["pkValue"]) && isset($_POST["modName"])) {
        $pkValue = intval($_POST["pkValue"]);
        if ($pkValue > 0) {
            if (isset($_SESSION[SITEURL][$_POST["modName"]]["TBL"])) {
                $tblName = $_SESSION[SITEURL][$_POST["modName"]]["TBL"];
                $pkName = $_SESSION[SITEURL][$_POST["modName"]]["PK"];
                $MXRES["data"] = getLogData($tblName, $pkName, $pkValue);
            }
        }
    }
}

if (isset($_POST["xAction"])) {
    require_once("../../core/core.inc.php");
    require_once("settings.inc.php");
    $chkLogin = true;
    $ignoreToken = false;
    if ($_POST["xAction"] == "xLogin") {
        $chkLogin = false;
        $ignoreToken = true;
    }
    $MXRES = mxCheckRequest($chkLogin, $ignoreToken);

    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "xLogin":

                if (loginAdminUser($_POST["userName"], $_POST["userPass"])) {
                    $setting = getSetting("DEFAULTPAGE");
                    $redirect = "";
                    $_SESSION[SITEURL]["login_attempts"] = 0;
                    if (isset($_POST["redirect"]) && $_POST["redirect"] !== "")
                        $redirect = htmlspecialchars($_POST["redirect"]);
                    else
                        $redirect = "/" . $setting["DEFAULTPAGE"] . "-list/";

                    setResponse(["err" => 0, "msg" => "Login successfull", "rurl" => ADMINURL . "$redirect"]);
                } else {
                    if (!isset($_SESSION[SITEURL]["login_attempts"]))
                        $_SESSION[SITEURL]["login_attempts"] = 0;

                    $_SESSION[SITEURL]["login_attempts"] += 1;
                    $total_attempt = 3;

                    $remaining_attempt = $total_attempt - $_SESSION[SITEURL]["login_attempts"];
                    if ($remaining_attempt == 0) {
                        $_SESSION[SITEURL]["locked"] = time();
                        $str = "Please try again after 30 seconds.";
                    } else {
                        $str = "You have remaining " . $remaining_attempt . " attempt.";
                    }
                    setResponse(["err" => 1, "msg" => "Please login with valid username / password. " . $str, "rurl" => ADMINURL]);
                }
                break;
            case "setTheme":
                setTheme(htmlspecialchars($_REQUEST["type"]));
                break;
            case "setFontSize":
                setFontSize(htmlspecialchars($_REQUEST["type"]));
                break;
            case "trash":
                changeStatus(0);
                break;
            case "restore":
                changeStatus(1);
                break;
            case "delete":
                delete();
                break;
            case "getModLog":
                getModLog();
                break;
            case "getLogData":
                $MXRES["data"] = getLogData($_POST["tblName"], $_POST["pkName"], $_POST["pkValue"]);
                break;
        }
    }
    echo json_encode($MXRES);
}
