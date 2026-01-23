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

// Build gender dropdown
$genders = array("Male", "Female", "Other");
$genderOpt = '<option value="">Select Gender</option>';
$currentGender = $D["gender"] ?? "";
foreach ($genders as $g) {
    $sel = ($currentGender == $g) ? ' selected="selected"' : '';
    $genderOpt .= '<option value="' . $g . '"' . $sel . '>' . $g . '</option>';
}

// Build skill level dropdown
$levels = array("Beginner", "Intermediate", "Advanced", "Pro");
$levelOpt = '';
$currentLevel = $D["currentLevel"] ?? "Beginner";
foreach ($levels as $l) {
    $sel = ($currentLevel == $l) ? ' selected="selected"' : '';
    $levelOpt .= '<option value="' . $l . '"' . $sel . '>' . $l . '</option>';
}

// Build membership type dropdown
$memTypes = array("Standard", "Premium", "Annual", "Lifetime");
$memOpt = '';
$currentMem = $D["membershipType"] ?? "Standard";
foreach ($memTypes as $m) {
    $sel = ($currentMem == $m) ? ' selected="selected"' : '';
    $memOpt .= '<option value="' . $m . '"' . $sel . '>' . $m . '</option>';
}

// Get coaches for preferred coach dropdown
$DB->sql = "SELECT coachID, CONCAT(firstName, ' ', IFNULL(lastName,'')) as coachName FROM " . $DB->pre . "ipa_coach WHERE status=1 AND coachStatus='Active' ORDER BY firstName";
$coaches = $DB->dbRows();
$coachOpt = '<option value="">Select Coach</option>';
$currentCoach = $D["preferredCoach"] ?? "";
foreach ($coaches as $c) {
    $sel = ($currentCoach == $c["coachID"]) ? ' selected="selected"' : '';
    $coachOpt .= '<option value="' . $c["coachID"] . '"' . $sel . '>' . htmlspecialchars($c["coachName"]) . '</option>';
}

$arrFormBasic = array(
    array("type" => "text", "name" => "playerCode", "value" => $D["playerCode"] ?? "", "title" => "Player Code", "info" => '<span class="info">Auto-generated if blank</span>'),
    array("type" => "text", "name" => "firstName", "value" => $D["firstName"] ?? "", "title" => "First Name", "validate" => "required"),
    array("type" => "text", "name" => "lastName", "value" => $D["lastName"] ?? "", "title" => "Last Name"),
    array("type" => "text", "name" => "email", "value" => $D["email"] ?? "", "title" => "Email", "validate" => "email"),
    array("type" => "text", "name" => "phone", "value" => $D["phone"] ?? "", "title" => "Phone"),
    array("type" => "date", "name" => "dateOfBirth", "value" => $D["dateOfBirth"] ?? "", "title" => "Date of Birth"),
    array("type" => "select", "name" => "gender", "value" => $genderOpt, "title" => "Gender"),
);

$arrFormAddress = array(
    array("type" => "textarea", "name" => "address", "value" => $D["address"] ?? "", "title" => "Address", "params" => array("rows" => 2)),
    array("type" => "text", "name" => "city", "value" => $D["city"] ?? "", "title" => "City"),
    array("type" => "text", "name" => "state", "value" => $D["state"] ?? "", "title" => "State"),
    array("type" => "text", "name" => "pincode", "value" => $D["pincode"] ?? "", "title" => "Pincode"),
);

$arrFormMembership = array(
    array("type" => "select", "name" => "membershipType", "value" => $memOpt, "title" => "Membership Type"),
    array("type" => "date", "name" => "membershipStart", "value" => $D["membershipStart"] ?? "", "title" => "Membership Start"),
    array("type" => "date", "name" => "membershipExpiry", "value" => $D["membershipExpiry"] ?? "", "title" => "Membership Expiry"),
    array("type" => "text", "name" => "ipaRanking", "value" => $D["ipaRanking"] ?? "0", "title" => "IPA Ranking", "validate" => "number"),
);

$arrFormSkill = array(
    array("type" => "select", "name" => "currentLevel", "value" => $levelOpt, "title" => "Current Level"),
    array("type" => "select", "name" => "preferredCoach", "value" => $coachOpt, "title" => "Preferred Coach"),
    array("type" => "text", "name" => "preferredTimeSlot", "value" => $D["preferredTimeSlot"] ?? "", "title" => "Preferred Time", "info" => '<span class="info">e.g., Morning, Evening, Weekends</span>'),
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
            <h2 class="form-head">Address</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormAddress); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Membership</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormMembership); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Skill & Preferences</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormSkill); ?>
            </ul>
        </div>
        <?php if ($TPL->pageType == "edit" || $TPL->pageType == "view") { ?>
        <div class="wrap-form f100">
            <h2 class="form-head">Skill Ratings (Updated by Coaches)</h2>
            <ul class="tbl-form" style="display:flex; flex-wrap:wrap;">
                <li style="width:20%;">
                    <label>Forehand</label>
                    <div class="field-val"><?php echo intval($D["forehandRating"] ?? 0); ?>/10</div>
                </li>
                <li style="width:20%;">
                    <label>Backhand</label>
                    <div class="field-val"><?php echo intval($D["backhandRating"] ?? 0); ?>/10</div>
                </li>
                <li style="width:20%;">
                    <label>Serve</label>
                    <div class="field-val"><?php echo intval($D["serveRating"] ?? 0); ?>/10</div>
                </li>
                <li style="width:20%;">
                    <label>Volley</label>
                    <div class="field-val"><?php echo intval($D["volleyRating"] ?? 0); ?>/10</div>
                </li>
                <li style="width:20%;">
                    <label>Strategy</label>
                    <div class="field-val"><?php echo intval($D["gameStrategyRating"] ?? 0); ?>/10</div>
                </li>
            </ul>
        </div>
        <?php } ?>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>
