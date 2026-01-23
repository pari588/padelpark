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

$arrForm = array(
    array("type" => "text", "name" => "courtName", "value" => $D["courtName"] ?? "", "title" => "Court Name", "validate" => "required"),
    array("type" => "text", "name" => "courtCode", "value" => $D["courtCode"] ?? "", "title" => "Court Code"),
    array("type" => "text", "name" => "centerName", "value" => $D["centerName"] ?? "", "title" => "Center Name", "validate" => "required"),
    array("type" => "textarea", "name" => "centerAddress", "value" => $D["centerAddress"] ?? "", "title" => "Center Address", "params" => array("rows" => 3)),
    array("type" => "select", "name" => "courtType", "value" => array(
        array("val" => "Indoor", "txt" => "Indoor", "sel" => ($D["courtType"] ?? "Indoor") == "Indoor"),
        array("val" => "Outdoor", "txt" => "Outdoor", "sel" => ($D["courtType"] ?? "") == "Outdoor"),
        array("val" => "Semi-Covered", "txt" => "Semi-Covered", "sel" => ($D["courtType"] ?? "") == "Semi-Covered")
    ), "title" => "Court Type", "validate" => "required"),
    array("type" => "text", "name" => "surfaceType", "value" => $D["surfaceType"] ?? "Artificial Grass", "title" => "Surface Type"),
);

$arrForm1 = array(
    array("type" => "text", "name" => "hudleCourtID", "value" => $D["hudleCourtID"] ?? "", "title" => "Hudle Court ID"),
    array("type" => "text", "name" => "pricePerHour", "value" => $D["pricePerHour"] ?? "0", "title" => "Price Per Hour (₹)", "validate" => "number"),
    array("type" => "file", "name" => "courtImage", "value" => array($D["courtImage"] ?? "", $id ?? 0), "title" => "Court Image", "params" => array("EXT" => "jpg|jpeg|png|gif|webp")),
    array("type" => "editor", "name" => "courtDescription", "value" => $D["courtDescription"] ?? "", "title" => "Court Description", "params" => array("toolbar" => "basic", "height" => 150)),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm);
                ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm1);
                ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>
