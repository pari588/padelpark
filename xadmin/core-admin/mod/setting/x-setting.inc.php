<?php
function mxDelFileS($params = array())
{
    global $MXRES;
    $MXRES = array("err" => 1, "msg" => "Cannot delete file something went wrong.", "data" => array(), "rurl" => "");
    if (count($params) > 0) {
        global $DB;
        extract($params);
        if (mxDeleteFile(UPLOADPATH . "/setting/", $fl)) {
            $DB->vals = array("NULL", $fld);
            $DB->types = "ss";
            $DB->sql = "UPDATE " . $DB->pre . "x_setting SET settingVal=? WHERE settingKey=?";
            if ($DB->dbQuery()) {
                $MXRES["err"] = 0;
                $MXRES["msg"] = "File deleted successfully";
            }
        }
    }
    return $MXRES;
}

function setImagesName($k, $v)
{
    $f = mxGetFileName($k);
    if (isset($f) && $f != "") {
        rename(UPLOADPATH . "/setting/" . $f, UPLOADPATH . "/setting/$v");
    }
    return $v;
}

function updateSetting()
{
    global $DB, $MXRES;
    $MXRES["err"] = 1;
    $MXRES["msg"] = "Sorry! Settings cannot be updated...";
    $D = getSetting();
    $_POST["LOGOLIGHT"] = setImagesName("LOGOLIGHT", "lightlogo.png");
    $_POST["LOGODARK"] = setImagesName("LOGODARK", "darklogo.png");
    $_POST["LOGOMODERATE"] = setImagesName("LOGOMODERATE", "moderatelogo.png");
    $_POST["FAVICON"] = setImagesName("FAVICON", "favicon.png");
    $_POST["LOADERIMAGE"] = setImagesName("LOADERIMAGE", "logo-m.png");

    if (!isset($_POST["MULTILINGUAL"]))
        $_POST["MULTILINGUAL"] = 0;

    if (!isset($_POST["LANGTYPE"]))
        $_POST["LANGTYPE"] = 0;

    if (!isset($_POST["MULTIORG"]))
        $_POST["MULTIORG"] = 0;

    if (isset($_POST["LOGIGNORETBL"]) && count($_POST["LOGIGNORETBL"]) > 0)
        $_POST["LOGIGNORETBL"] = implode(",", $_POST["LOGIGNORETBL"]);
    else
        $_POST["LOGIGNORETBL"] = "";

    $DB->table = $DB->pre . "x_setting";
    foreach ($D as $settingKey => $v) {
        if (isset($_POST[$settingKey])) {
            $DB->data = array("settingVal" => $_POST[$settingKey]);
            if ($DB->dbUpdate("settingKey=?", "s", array($settingKey)))
                $MXRES["err"] = 0;
        }
    }
    if ($MXRES["err"] == 0)
        $MXRES["msg"] = "Settings updated successfully...";
}

function restoreSettings()
{
    global $DB;
    $DB->sql = "UPDATE " . $DB->pre . "x_setting SET settingVal=settingDefault";
    $DB->dbQuery();
    $files = glob(UPLOADPATH . '/setting/*');
    foreach ($files as $file) {
        if (is_file($file))
            unlink($file);
    }

    $files = glob(UPLOADPATH . '/setting/tmp/*');
    foreach ($files as $file) {
        if (is_file($file))
            unlink($file);
    }
    if (file_exists(UPLOADPATH . '/setting/tmp'))
        rmdir(UPLOADPATH . '/setting/tmp');

    copy(ADMINPATH . "/images/lightlogo.png", UPLOADPATH . "/setting/lightlogo.png");
    copy(ADMINPATH . "/images/darklogo.png", UPLOADPATH . "/setting/darklogo.png");
    copy(ADMINPATH . "/images/moderatelogo.png", UPLOADPATH . "/setting/moderatelogo.png");
    copy(ADMINPATH . "/images/favicon.png", UPLOADPATH . "/setting/favicon.png");
    copy(ADMINPATH . "/images/logo-m.png", UPLOADPATH . "/setting/logo-m.png");
}

function resetJs()
{
    global $DB, $MXRES;
    $key = generateKey();
    $DB->vals = array($key, "JSKEY");
    $DB->types = "ss";
    $DB->sql = "UPDATE " . $DB->pre . "x_setting SET settingVal=? WHERE settingKey=?";
    $DB->dbQuery();
    $MXRES["data"]["jskey"] = $key;
    $MXRES["msg"] = "OK";
}

function optimizeLog()
{
    global $MXSET, $DB, $MXRES;
    $flg = 0;
    if (isset($MXSET["LOGIGNORETBL"]) && trim($MXSET["LOGIGNORETBL"]) !== "") {
        $arrTblSkip = explode(",", $MXSET["LOGIGNORETBL"]);
        if (count($arrTblSkip) > 0) {
            $strSkip = "'" . implode("','", $arrTblSkip) . "'";
            $DB->sql = "DELETE FROM " . $DB->pre . "x_log_action WHERE tblName IN($strSkip)";
            if ($DB->dbQuery()) {
                $flg++;
            }
        }
    }
    if (isset($MXSET["LOGACTIONDAYS"]) && $MXSET["LOGACTIONDAYS"] > 0) {
        $logDays = intval($MXSET["LOGACTIONDAYS"]);
        $DB->vals = array($logDays);
        $DB->types = "i";
        $DB->sql = "DELETE FROM " . $DB->pre . "x_log_action WHERE actionDate < (NOW() - INTERVAL ? DAY)";
        if ($DB->dbQuery()) {
            $flg++;
        }
    }

    if (isset($MXSET["LOGREQUESTDAYS"]) && $MXSET["LOGREQUESTDAYS"] > 0) {
        $logDays = intval($MXSET["LOGREQUESTDAYS"]);
        $DB->vals = array($logDays);
        $DB->types = "i";
        $DB->sql = "DELETE FROM " . $DB->pre . "x_log_request WHERE logDate < (NOW() - INTERVAL ? DAY)";
        if ($DB->dbQuery()) {
            $flg++;
        }
    }

    $MXRES["msg"] = "Log optimized successfully...";
    if ($flg == 0) {
        $MXRES["msg"] = "Error while optimizing log...";
    }
}

function deleteLog()
{
    global $DB, $MXRES;
    $flg = 0;
    $DB->sql = "TRUNCATE `" . $DB->pre . "x_log_action`";
    if ($DB->dbQuery()) {
        $flg++;
    }
    $DB->sql = "TRUNCATE `" . $DB->pre . "x_log_request`";
    if ($DB->dbQuery()) {
        $flg++;
    }

    $MXRES["msg"] = "All logs deleted successfully...";
    if ($flg == 0) {
        $MXRES["msg"] = "Error while deleting log...";
    }
}

if (isset($_POST["xAction"])) {
    require_once("../../../../core/core.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "UPDATE":
                updateSetting();
                break;
            case "restoreSettings":
                restoreSettings();
                break;
            case "resetJs":
                resetJs();
                break;
            case "mxDelFile":
                mxDelFileS($_REQUEST);
                break;
            case "deleteLog":
                deleteLog();
                break;
            case "optimizeLog":
                optimizeLog();
                break;
        }
    }
    echo json_encode($MXRES, true);
} else { // Following all required
    if (function_exists("setModVars")) setModVars(array("TBL" => "x_setting", "PK" => "settingID", "UDIR" => array("LOGOLIGHT" => "setting", "LOGODARK" => "setting", "LOGOMODERATE" => "setting", "FAVICON" => "setting", "LOADERIMAGE" => "setting")));
}
