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

// Build certification level dropdown
$certLevels = array("Level 1", "Level 2", "Level 3", "Head Coach");
$certOpt = '';
$currentCert = $D["certificationLevel"] ?? "Level 1";
foreach ($certLevels as $cl) {
    $sel = ($currentCert == $cl) ? ' selected="selected"' : '';
    $certOpt .= '<option value="' . $cl . '"' . $sel . '>' . $cl . '</option>';
}

// Build employment type dropdown
$empTypes = array("Full-Time", "Part-Time", "Freelance");
$empOpt = '';
$currentEmp = $D["employmentType"] ?? "Full-Time";
foreach ($empTypes as $et) {
    $sel = ($currentEmp == $et) ? ' selected="selected"' : '';
    $empOpt .= '<option value="' . $et . '"' . $sel . '>' . $et . '</option>';
}

// Build status dropdown
$statuses = array("Active", "Inactive", "On Leave", "Terminated");
$statusOpt = '';
$currentStatus = $D["coachStatus"] ?? "Active";
foreach ($statuses as $st) {
    $sel = ($currentStatus == $st) ? ' selected="selected"' : '';
    $statusOpt .= '<option value="' . $st . '"' . $sel . '>' . $st . '</option>';
}

$arrFormBasic = array(
    array("type" => "text", "name" => "coachCode", "value" => $D["coachCode"] ?? "", "title" => "Coach Code", "info" => '<span class="info">Auto-generated if blank</span>'),
    array("type" => "text", "name" => "firstName", "value" => $D["firstName"] ?? "", "title" => "First Name", "validate" => "required"),
    array("type" => "text", "name" => "lastName", "value" => $D["lastName"] ?? "", "title" => "Last Name"),
    array("type" => "text", "name" => "email", "value" => $D["email"] ?? "", "title" => "Email", "validate" => "email"),
    array("type" => "text", "name" => "phone", "value" => $D["phone"] ?? "", "title" => "Phone"),
);

$arrFormCertification = array(
    array("type" => "select", "name" => "certificationLevel", "value" => $certOpt, "title" => "Certification Level"),
    array("type" => "date", "name" => "certificationDate", "value" => $D["certificationDate"] ?? "", "title" => "Certification Date"),
    array("type" => "date", "name" => "certificationExpiry", "value" => $D["certificationExpiry"] ?? "", "title" => "Certification Expiry"),
    array("type" => "textarea", "name" => "specializations", "value" => $D["specializations"] ?? "", "title" => "Specializations", "params" => array("rows" => 2), "info" => '<span class="info">e.g., Beginners, Kids, Advanced Tactics</span>'),
);

$arrFormEmployment = array(
    array("type" => "select", "name" => "employmentType", "value" => $empOpt, "title" => "Employment Type"),
    array("type" => "date", "name" => "joiningDate", "value" => $D["joiningDate"] ?? "", "title" => "Joining Date"),
    array("type" => "text", "name" => "hourlyRate", "value" => $D["hourlyRate"] ?? "0", "title" => "Hourly Rate (Rs.)", "validate" => "number"),
    array("type" => "select", "name" => "coachStatus", "value" => $statusOpt, "title" => "Status"),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Personal Information</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormBasic); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Certification</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormCertification); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Employment Details</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormEmployment); ?>
            </ul>
        </div>
        <?php if ($TPL->pageType == "edit" || $TPL->pageType == "view") { ?>
        <div class="wrap-form f50">
            <h2 class="form-head">Performance Stats</h2>
            <ul class="tbl-form">
                <li>
                    <label>Average Rating</label>
                    <div class="field-val">
                        <?php
                        $rating = floatval($D["avgStudentRating"] ?? 0);
                        echo $rating > 0 ? number_format($rating, 2) . ' / 5.00' : 'No ratings yet';
                        ?>
                    </div>
                </li>
                <li>
                    <label>Total Sessions</label>
                    <div class="field-val"><?php echo intval($D["totalSessionsConducted"] ?? 0); ?></div>
                </li>
                <li>
                    <label>Students Trained</label>
                    <div class="field-val"><?php echo intval($D["totalStudentsTrained"] ?? 0); ?></div>
                </li>
            </ul>
        </div>
        <?php } ?>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>
