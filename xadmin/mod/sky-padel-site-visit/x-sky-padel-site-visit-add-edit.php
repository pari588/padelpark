<?php
$id = 0; $D = array();
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "`=?";
    $D = $DB->dbRow();
}

// Check if leadID is passed in URL (coming from lead list)
$preselectedLeadID = isset($_GET['leadID']) ? intval($_GET['leadID']) : ($D['leadID'] ?? 0);

$whrArr = array("sql" => "status=?", "types" => "i", "vals" => array(1));
$leadOpt = getTableDD(["table" => $DB->pre . "sky_padel_lead", "key" => "leadID", "val" => "CONCAT(leadNo, ' - ', clientName)", "selected" => $preselectedLeadID, "where" => $whrArr]);
$userOpt = getTableDD(["table" => $DB->pre . "x_admin_user", "key" => "userID", "val" => "displayName", "selected" => ($D['scheduledBy'] ?? 0), "where" => $whrArr]);

// Build visit type options
$visitTypes = array("Initial", "Follow-up", "Final Inspection");
$visitTypeOpt = "";
$currentType = $D["visitType"] ?? "Initial";
foreach ($visitTypes as $type) {
    $sel = ($currentType == $type) ? ' selected="selected"' : '';
    $visitTypeOpt .= '<option value="' . $type . '"' . $sel . '>' . $type . '</option>';
}

// Build visit status options
$visitStatuses = array("Scheduled", "Completed", "Cancelled", "Rescheduled");
$visitStatusOpt = "";
$currentStatus = $D["visitStatus"] ?? "Scheduled";
foreach ($visitStatuses as $stat) {
    $sel = ($currentStatus == $stat) ? ' selected="selected"' : '';
    $visitStatusOpt .= '<option value="' . $stat . '"' . $sel . '>' . $stat . '</option>';
}

$arrForm = array(
    array("type" => "select", "name" => "leadID", "value" => $leadOpt, "title" => "Lead", "validate" => "required"),
    array("type" => "date", "name" => "visitDate", "value" => $D["visitDate"] ?? date("Y-m-d"), "title" => "Visit Date", "validate" => "required"),
    array("type" => "time", "name" => "visitTime", "value" => $D["visitTime"] ?? "", "title" => "Visit Time", "validate" => "required"),
    array("type" => "select", "name" => "visitType", "value" => $visitTypeOpt, "title" => "Type"),
    array("type" => "text", "name" => "attendees", "value" => $D["attendees"] ?? "", "title" => "Attendees"),
    array("type" => "select", "name" => "visitStatus", "value" => $visitStatusOpt, "title" => "Status"),
    array("type" => "select", "name" => "scheduledBy", "value" => $userOpt, "title" => "Scheduled By"),
    array("type" => "textarea", "name" => "notes", "value" => $D["notes"] ?? "", "title" => "Notes", "params" => array("rows" => 4))
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <ul class="tbl-form"><?php echo $MXFRM->getForm($arrForm); ?></ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>
