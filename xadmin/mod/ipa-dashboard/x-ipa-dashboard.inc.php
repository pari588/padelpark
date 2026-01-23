<?php
/*
IPA Dashboard - No CRUD operations, just data display
*/

// Handle AJAX actions
if (isset($_POST["xAction"])) {
    ob_start();
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    ob_end_clean();

    header('Content-Type: application/json');
    echo json_encode(array("err" => 0));
    exit;
} else {
    if (function_exists("setModVars")) {
        setModVars(array("TBL" => "ipa_coach", "PK" => "coachID"));
    }
}
