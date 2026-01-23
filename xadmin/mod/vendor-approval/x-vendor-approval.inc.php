<?php
/**
 * Vendor Approval Dashboard - Backend Logic
 */

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        // Actions are handled by vendor-onboarding.inc.php
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "vendor_onboarding", "PK" => "vendorID"));
}
?>
