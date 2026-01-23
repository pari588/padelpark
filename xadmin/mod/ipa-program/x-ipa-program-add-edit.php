<?php
$id = 0;
$D = array();
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "`=?";
    $D = $DB->dbRow();
}

// Build program type dropdown
$types = array("Clinic", "Private", "Group", "Camp", "Masterclass");
$typeOpt = '';
$currentType = $D["programType"] ?? "Group";
foreach ($types as $t) {
    $sel = ($currentType == $t) ? ' selected="selected"' : '';
    $typeOpt .= '<option value="' . $t . '"' . $sel . '>' . $t . '</option>';
}

// Build skill level dropdown
$levels = array("Beginner", "Intermediate", "Advanced", "All Levels");
$levelOpt = '';
$currentLevel = $D["skillLevel"] ?? "All Levels";
foreach ($levels as $l) {
    $sel = ($currentLevel == $l) ? ' selected="selected"' : '';
    $levelOpt .= '<option value="' . $l . '"' . $sel . '>' . $l . '</option>';
}

$arrFormBasic = array(
    array("type" => "text", "name" => "programCode", "value" => $D["programCode"] ?? "", "title" => "Program Code", "info" => '<span class="info">Auto-generated if blank</span>'),
    array("type" => "text", "name" => "programName", "value" => $D["programName"] ?? "", "title" => "Program Name", "validate" => "required"),
    array("type" => "textarea", "name" => "programDescription", "value" => $D["programDescription"] ?? "", "title" => "Description", "params" => array("rows" => 3)),
    array("type" => "select", "name" => "programType", "value" => $typeOpt, "title" => "Program Type"),
    array("type" => "select", "name" => "skillLevel", "value" => $levelOpt, "title" => "Skill Level"),
    array("type" => "text", "name" => "ageGroup", "value" => $D["ageGroup"] ?? "", "title" => "Age Group", "info" => '<span class="info">e.g., Adults, Kids 8-12, Teens</span>'),
);

$arrFormDetails = array(
    array("type" => "text", "name" => "sessionDuration", "value" => $D["sessionDuration"] ?? "60", "title" => "Session Duration (mins)", "validate" => "required,number"),
    array("type" => "text", "name" => "totalSessions", "value" => $D["totalSessions"] ?? "1", "title" => "Total Sessions", "validate" => "number", "info" => '<span class="info">For multi-session programs</span>'),
    array("type" => "text", "name" => "minParticipants", "value" => $D["minParticipants"] ?? "1", "title" => "Min Participants", "validate" => "number"),
    array("type" => "text", "name" => "maxParticipants", "value" => $D["maxParticipants"] ?? "10", "title" => "Max Participants", "validate" => "number"),
);

$arrFormPricing = array(
    array("type" => "text", "name" => "pricePerSession", "value" => $D["pricePerSession"] ?? "0", "title" => "Price Per Session (Rs.)", "validate" => "number"),
    array("type" => "text", "name" => "packagePrice", "value" => $D["packagePrice"] ?? "0", "title" => "Package Price (Rs.)", "validate" => "number", "info" => '<span class="info">For multi-session package discount</span>'),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Program Information</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormBasic); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Session Details</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormDetails); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Pricing</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormPricing); ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>

<script>
// Define required JavaScript variables for form submission
var MODINCURL = '<?php echo ADMINURL; ?>/mod/ipa-program/x-ipa-program.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/ipa-program/';
var ADMINURL = '<?php echo ADMINURL; ?>';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
</script>
