<?php
//Start: To add holiday.
function addHoliday()
{
    global $DB;
    $str = "ERR";
    if (count($_POST) > 0) {
        $date  = $_POST['date'];
        $dbdata = array();
        $dbdata['status'] = 1;
        $dbdata['ahDate'] = $date;
        $dbdata['holidayType'] = $_POST['holiType'];
        $dbdata['ahReason'] = $_POST['ahReason'];
        if (!hasRecord($date)) {
            $DB->table = $DB->pre . "attendance_holidays";
            $DB->data  = $dbdata;
            $DB->dbInsert();
            $str = "OK";
        } else {
            $DB->table = $DB->pre . "attendance_holidays";
            $DB->data  = $dbdata;
            $DB->dbUpdate("ahDate='$date'");
            $DB->dbUpdate("ahDate=?", "s", array($date));
            $str = "OK";
        }
    }
    return $str;
}
// End
//Start: To get holiday.
function getHolidays($year = 0)
{
    global $DB;
    $data = array();
    $DB->vals = array($year, 1);
    $DB->types = "si";
    $DB->sql = "SELECT * FROM `" . $DB->pre . "attendance_holidays` WHERE YEAR(ahDate) = ? AND status=?";
    $DB->dbRows();
    if ($DB->numRows > 0) {
        foreach ($DB->rows as $key => $value) {
            $data[$value['ahDate']] = $value;
        }
    }
    return $data;
}
// End
//Start: To check if that date has a holiday record.
function hasRecord($date = "")
{
    global $DB;
    $str = false;
    $DB->vals = array($date);
    $DB->types = "s";
    $DB->sql = "SELECT * FROM `" . $DB->pre . "attendance_holidays` WHERE ahDate =?";
    $DB->dbRows();
    if ($DB->numRows > 0) {
        $str = true;
    }
    return $str;
}
// End
//Start: To delete holiday.
function deleteHoliday()
{
    global $DB;
    $str = "ERR";
    $date = $_POST["date"];
    if ($_SESSION[SITEURL]["MXID"] > 0 || $_SESSION[SITEURL]["MXID"] == "SUPER") {
        $DB->vals = array($date);
        $DB->types = "s";
        $DB->sql = "DELETE FROM `" . $DB->pre . "attendance_holidays` WHERE ahDate =? LIMIT 1";
        if ($DB->dbQuery() == true) {
            $str = "OK";
        }
    }
    return $str;
}
// End
if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest(true, false);
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "addHoliday":
                $MXRES = addHoliday($_POST);
                break;
            case "deleteHoliday":
                $MXRES = deleteHoliday($_POST);
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "attendance_holidays", "PK" => "ahID"));
}
