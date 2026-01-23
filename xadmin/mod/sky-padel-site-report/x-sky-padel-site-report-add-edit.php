<?php
$id = 0; $D = array();
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "`=?";
    $D = $DB->dbRow();
}

$whrArr = array("sql" => "status=?", "types" => "i", "vals" => array(1));
$visitOpt = getTableDD(["table" => $DB->pre . "sky_padel_site_visit", "key" => "visitID", "val" => "CONCAT('Visit #', visitID, ' - ', DATE_FORMAT(visitDate, '%d-%b-%Y'))", "selected" => ($D['visitID'] ?? 0), "where" => $whrArr]);
$leadOpt = getTableDD(["table" => $DB->pre . "sky_padel_lead", "key" => "leadID", "val" => "CONCAT(leadNo, ' - ', clientName)", "selected" => ($D['leadID'] ?? 0), "where" => $whrArr]);
$userOpt = getTableDD(["table" => $DB->pre . "x_admin_user", "key" => "userID", "val" => "displayName", "selected" => ($D['reportBy'] ?? 0), "where" => $whrArr]);

// Build suitability rating options
$suitabilityRatings = array("Excellent" => "Excellent", "Good" => "Good", "Average" => "Average", "Poor" => "Poor");
$suitabilityOpt = "";
$currentSuitability = $D["suitabilityRating"] ?? "Good";
foreach ($suitabilityRatings as $val => $txt) {
    $sel = ($currentSuitability == $val) ? ' selected="selected"' : '';
    $suitabilityOpt .= '<option value="' . $val . '"' . $sel . '>' . $txt . '</option>';
}

// Build electricity options
$electricityOptions = array("Yes" => "Available", "No" => "Not Available", "Needs Upgrade" => "Needs Upgrade");
$electricityOpt = "";
$currentElectricity = $D["electricityAvailability"] ?? "Yes";
foreach ($electricityOptions as $val => $txt) {
    $sel = ($currentElectricity == $val) ? ' selected="selected"' : '';
    $electricityOpt .= '<option value="' . $val . '"' . $sel . '>' . $txt . '</option>';
}

// Build water options
$waterOptions = array("Yes" => "Available", "No" => "Not Available");
$waterOpt = "";
$currentWater = $D["waterAvailability"] ?? "Yes";
foreach ($waterOptions as $val => $txt) {
    $sel = ($currentWater == $val) ? ' selected="selected"' : '';
    $waterOpt .= '<option value="' . $val . '"' . $sel . '>' . $txt . '</option>';
}

// Build access road options
$accessRoadOptions = array("Excellent" => "Excellent", "Good" => "Good", "Poor" => "Poor");
$accessRoadOpt = "";
$currentAccess = $D["accessRoad"] ?? "Good";
foreach ($accessRoadOptions as $val => $txt) {
    $sel = ($currentAccess == $val) ? ' selected="selected"' : '';
    $accessRoadOpt .= '<option value="' . $val . '"' . $sel . '>' . $txt . '</option>';
}

$arrForm = array(
    array("type" => "select", "name" => "visitID", "value" => $visitOpt, "title" => "Site Visit", "validate" => "required"),
    array("type" => "select", "name" => "leadID", "value" => $leadOpt, "title" => "Lead", "validate" => "required"),
    array("type" => "date", "name" => "reportDate", "value" => $D["reportDate"] ?? date("Y-m-d"), "title" => "Report Date", "validate" => "required"),
    array("type" => "textarea", "name" => "siteCondition", "value" => $D["siteCondition"] ?? "", "title" => "Site Condition", "params" => array("rows" => 3)),
    array("type" => "text", "name" => "spaceMeasurements", "value" => $D["spaceMeasurements"] ?? "", "title" => "Measurements", "info" => '<span class="info">e.g., 40m x 20m</span>'),
    array("type" => "select", "name" => "suitabilityRating", "value" => $suitabilityOpt, "title" => "Suitability"),
    array("type" => "select", "name" => "electricityAvailability", "value" => $electricityOpt, "title" => "Electricity"),
    array("type" => "select", "name" => "waterAvailability", "value" => $waterOpt, "title" => "Water"),
    array("type" => "select", "name" => "accessRoad", "value" => $accessRoadOpt, "title" => "Access Road"),
    array("type" => "textarea", "name" => "recommendations", "value" => $D["recommendations"] ?? "", "title" => "Recommendations", "params" => array("rows" => 3)),
    array("type" => "textarea", "name" => "challenges", "value" => $D["challenges"] ?? "", "title" => "Challenges", "params" => array("rows" => 3)),
    array("type" => "text", "name" => "estimatedCost", "value" => $D["estimatedCost"] ?? "0", "title" => "Estimated Cost (₹)", "validate" => "number"),
    array("type" => "file", "name" => "photos", "value" => array($D["photos"] ?? "", $id ?? 0), "title" => "Photos", "params" => array("EXT" => "jpg|jpeg|png|gif|webp", "multiple" => true)),
    array("type" => "select", "name" => "reportBy", "value" => $userOpt, "title" => "Report By")
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form">
            <ul class="tbl-form"><?php echo $MXFRM->getForm($arrForm); ?></ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>

<script>
// Define required JavaScript variables for form submission
var MODINCURL = '<?php echo ADMINURL; ?>/mod/sky-padel-site-report/x-sky-padel-site-report.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/sky-padel-site-report/';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
var ADMINURL = '<?php echo ADMINURL; ?>';
</script>
