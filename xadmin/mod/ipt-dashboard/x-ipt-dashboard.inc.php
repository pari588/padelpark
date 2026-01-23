<?php
/*
IPT Dashboard - No CRUD operations needed
*/

// Handle AJAX actions if needed
if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "ipt_tournament", "PK" => "tournamentID"));
}
