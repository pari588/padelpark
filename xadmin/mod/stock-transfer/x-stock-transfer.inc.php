<?php
/*
Stock Transfer Module - for transferring stock between warehouses
Uses transferStock() function from inventory-stock module
*/

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    require_once("../inventory-stock/x-inventory-stock.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
            case "TRANSFER":
                transferStock();
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "stock_ledger", "PK" => "ledgerID"));
}
?>
