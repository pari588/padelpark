<?php
/*
Stock Ledger Module - View stock movement audit trail
*/

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest(true, true); // Session-based auth
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "stock_ledger", "PK" => "ledgerID"));
}
?>
