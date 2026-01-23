<?php
//echo "#####".(__DIR__);
require_once((__DIR__) . "/../config.inc.php");
require_once((__DIR__) . "/db.inc.php");
$DB = new mxDb($DBHOST, $DBUSER, $DBPASS, $DBNAME);
require_once((__DIR__) . "/formating.inc.php");
require_once((__DIR__) . "/common.inc.php");
require_once((__DIR__) . "/file.inc.php");
$MXSETTINGSJ = $MXNOTRASHID = $MXLANGS = array();
$MXSET = getSetting();

if (isset($MXSET["TIMEZONE"]) && $MXSET["TIMEZONE"] !== "") {
    date_default_timezone_set($MXSET["TIMEZONE"]);
}

$SKIPMODORG = array("admin-menu", "db-setting", "optimize", "organization", "setting", "template");

// $MXADDCORETBL = array(
//     "language"=>array("x_admin_user","x_menu"),
//     "organization"=>array("x_admin_role","x_admin_user")
// );
