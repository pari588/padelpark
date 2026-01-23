<?php
/*
Warehouse Dashboard - Overview of inventory and B2B sales
*/

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "warehouse", "PK" => "warehouseID"));
}
?>
