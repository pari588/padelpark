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

// Build category type dropdown
$catTypes = array("Singles", "Doubles", "Mixed");
$catTypeOpt = "";
$currentCatType = $D["categoryType"] ?? "Doubles";
foreach ($catTypes as $ct) {
    $sel = ($currentCatType == $ct) ? ' selected="selected"' : '';
    $catTypeOpt .= '<option value="' . $ct . '"' . $sel . '>' . $ct . '</option>';
}

// Build gender restriction dropdown
$genders = array("Open", "Men", "Women", "Mixed");
$genderOpt = "";
$currentGender = $D["genderRestriction"] ?? "Open";
foreach ($genders as $g) {
    $sel = ($currentGender == $g) ? ' selected="selected"' : '';
    $genderOpt .= '<option value="' . $g . '"' . $sel . '>' . $g . '</option>';
}

// Build age group dropdown
$ageGroups = array("Open", "U-12", "U-14", "U-16", "U-18", "U-21", "Senior", "Veterans");
$ageGroupOpt = "";
$currentAge = $D["ageGroup"] ?? "Open";
foreach ($ageGroups as $ag) {
    $sel = ($currentAge == $ag) ? ' selected="selected"' : '';
    $ageGroupOpt .= '<option value="' . $ag . '"' . $sel . '>' . $ag . '</option>';
}

// Build skill level dropdown
$skillLevels = array("Beginner", "Intermediate", "Advanced", "Pro", "Open");
$skillOpt = "";
$currentSkill = $D["skillLevel"] ?? "Open";
foreach ($skillLevels as $sl) {
    $sel = ($currentSkill == $sl) ? ' selected="selected"' : '';
    $skillOpt .= '<option value="' . $sl . '"' . $sel . '>' . $sl . '</option>';
}

$arrForm = array(
    array("type" => "text", "name" => "categoryCode", "value" => $D["categoryCode"] ?? "", "title" => "Category Code", "validate" => "required", "info" => '<span class="info">e.g., MD-OPEN, WD-U18</span>'),
    array("type" => "text", "name" => "categoryName", "value" => $D["categoryName"] ?? "", "title" => "Category Name", "validate" => "required", "info" => '<span class="info">e.g., Men\'s Doubles Open</span>'),
    array("type" => "select", "name" => "categoryType", "value" => $catTypeOpt, "title" => "Category Type", "validate" => "required"),
    array("type" => "select", "name" => "genderRestriction", "value" => $genderOpt, "title" => "Gender Restriction"),
);

$arrForm1 = array(
    array("type" => "select", "name" => "ageGroup", "value" => $ageGroupOpt, "title" => "Age Group"),
    array("type" => "select", "name" => "skillLevel", "value" => $skillOpt, "title" => "Skill Level"),
    array("type" => "text", "name" => "minPlayers", "value" => $D["minPlayers"] ?? "2", "title" => "Min Players", "validate" => "required,number"),
    array("type" => "text", "name" => "maxPlayers", "value" => $D["maxPlayers"] ?? "2", "title" => "Max Players", "validate" => "required,number"),
);

$arrForm2 = array(
    array("type" => "textarea", "name" => "description", "value" => $D["description"] ?? "", "title" => "Description", "params" => array("rows" => 3)),
    array("type" => "text", "name" => "sortOrder", "value" => $D["sortOrder"] ?? "0", "title" => "Sort Order", "validate" => "number"),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Category Info</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Restrictions</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm1); ?>
            </ul>
        </div>
        <div class="wrap-form f100">
            <h2 class="form-head">Additional Details</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm2); ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>
