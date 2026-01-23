<?php
$id = 0;
$D = array();
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "` =?";
    $D = $DB->dbRow();
}

// Get locations for dropdown
$whrArr = array("sql" => "status=?", "types" => "i", "vals" => array(1));
$locationOpt = getTableDD([
    "table" => $DB->pre . "pnp_location",
    "key" => "locationID",
    "val" => "locationName",
    "selected" => ($D['locationID'] ?? ($_GET['locationID'] ?? 0)),
    "where" => $whrArr
]);

// Build court type options
$courtTypes = array("Indoor", "Outdoor", "Covered");
$courtTypeOpt = "";
$currentCourtType = $D["courtType"] ?? "Indoor";
foreach ($courtTypes as $ct) {
    $sel = ($currentCourtType == $ct) ? ' selected="selected"' : '';
    $courtTypeOpt .= '<option value="' . $ct . '"' . $sel . '>' . $ct . '</option>';
}

// Build maintenance status options
$maintStatuses = array("Active", "Under Maintenance", "Closed");
$maintStatusOpt = "";
$currentMaintStatus = $D["maintenanceStatus"] ?? "Active";
foreach ($maintStatuses as $ms) {
    $sel = ($currentMaintStatus == $ms) ? ' selected="selected"' : '';
    $maintStatusOpt .= '<option value="' . $ms . '"' . $sel . '>' . $ms . '</option>';
}

$arrForm = array(
    array("type" => "select", "name" => "locationID", "value" => $locationOpt, "title" => "Location", "validate" => "required"),
    array("type" => "text", "name" => "courtCode", "value" => $D["courtCode"] ?? "", "title" => "Court Code", "info" => '<span class="info">Leave blank for auto-generation</span>'),
    array("type" => "text", "name" => "courtName", "value" => $D["courtName"] ?? "", "title" => "Court Name", "validate" => "required"),
    array("type" => "select", "name" => "courtType", "value" => $courtTypeOpt, "title" => "Court Type"),
    array("type" => "text", "name" => "surfaceType", "value" => $D["surfaceType"] ?? "Artificial Turf", "title" => "Surface Type"),
    array("type" => "text", "name" => "courtSize", "value" => $D["courtSize"] ?? "Standard (10m x 20m)", "title" => "Court Size"),
    array("type" => "text", "name" => "maxPlayers", "value" => $D["maxPlayers"] ?? "4", "title" => "Max Players", "validate" => "number"),
);

$arrForm1 = array(
    array("type" => "text", "name" => "hourlyRate", "value" => $D["hourlyRate"] ?? "0", "title" => "Hourly Rate (Rs.)", "validate" => "required,number"),
    array("type" => "text", "name" => "peakHourlyRate", "value" => $D["peakHourlyRate"] ?? "0", "title" => "Peak Hour Rate (Rs.)", "validate" => "number"),
    array("type" => "text", "name" => "weekendRate", "value" => $D["weekendRate"] ?? "0", "title" => "Weekend Rate (Rs.)", "validate" => "number"),
    array("type" => "text", "name" => "memberRate", "value" => $D["memberRate"] ?? "0", "title" => "Member Rate (Rs.)", "validate" => "number"),
    array("type" => "select", "name" => "maintenanceStatus", "value" => $maintStatusOpt, "title" => "Status"),
    array("type" => "text", "name" => "sortOrder", "value" => $D["sortOrder"] ?? "0", "title" => "Sort Order", "validate" => "number"),
    array("type" => "text", "name" => "hudelCourtID", "value" => $D["hudelCourtID"] ?? "", "title" => "Hudle Court ID", "info" => '<span class="info">For API integration</span>'),
    array("type" => "textarea", "name" => "amenities", "value" => $D["amenities"] ?? "", "title" => "Amenities", "params" => array("rows" => 2)),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Court Details</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Pricing & Status</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm1); ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>
