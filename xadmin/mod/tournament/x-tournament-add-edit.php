<script type="text/javascript" src="<?php echo mxGetUrl($TPL->modUrl . '/inc/js/x-lead.inc.js'); ?>"></script>
<?php
$D = "";
$readonlyAttr = "";
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"]);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "` =?";
    $D = $DB->dbRow();

    //Getting details data
    $DB->vals = array($id);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "lead_detail  WHERE " . $MXMOD["PK"] . "=?";
    $data = $DB->dbRows();
    foreach ($data as $k => $v) {
        $arrDD[$k] = $v;
    }
    //End.
}
if (isset($arrDD) < 1) {
    $v = array();
    $arrDD[] = $v;
}
$leadID[0] = 0;
// if ($_SESSION[SITEURL]["MXID"] != "SUPER") {
//     $leadID = array($_SESSION[SITEURL]["MXID"]);
// }
if ($_SESSION[SITEURL]["MXROLE"] != "SUPER") {
    $leadID = array($_SESSION[SITEURL]["MXROLE"]);
    $whrArr = array("sql" => "status=? AND roleID=? ", "types" => "ii", "vals" => array(1, $leadID[0]));

}else{
    $whrArr = array("sql" => "status=? ", "types" => "i", "vals" => array(1));

}

$adminUserArrDD = getTableDD(["table" => $DB->pre . "x_admin_user", "key" => "userID", "val" => "displayName", "selected" => ( $D['leadUser'] ?? ""), "where" =>  $whrArr]);

//getTableDD($DB->pre . "x_admin_user", "userID", "displayName", $D["leadUser"] ?? 0, $whrArr);
$arrForm = array(
    array("type" => "date", "name" => "leadDate", "validate" => "required", "value" => $D["leadDate"] ?? "", "title" => "Date", "attrp" => " class='c2'"),
    array("type" => "text", "name" => "partyName", "value" => $D["partyName"] ?? "", "title" => "Party Name", "validate" => "required", "attrp" => " class='c2'"),
    array("type" => "textarea", "name" => "partyAddr", "validate" => "required", "value" => $D["partyAddr"] ?? "", "title" => "Party Address"),
    array("type" => "text", "name" => "leadLocation", "validate" => "", "value" => $D["leadLocation"] ?? "", "title" => "Location", "attrp" => " class='c2'", "validate" => "required",),
    array("type" => "text", "name" => "contactPerson", "value" => $D["contactPerson"] ?? "", "title" => "Contact Person", "attrp" => " class='c2'", "validate" => "required",),
    array("type" => "text", "name" => "contactNumber", "value" => $D["contactNumber"] ?? "", "title" => "Contact Number", "validate" => "required,number", "attrp" => " class='c2'"),
    array("type" => "text", "name" => "officeNumber", "value" => $D["officeNumber"] ?? "", "title" => "Office Number", "attrp" => " class='c2'"),
    array("type" => "textarea", "name" => "remark", "value" => $D["remark"] ?? "", "title" => "Remark", "attrp" => " class='c1'"),
    array("type" => "file", "name" => "referenceDocument", "value" => array($D["referenceDocument"] ?? "", $id ?? 0), "title" => "Reference Document", "params" => array("MAXFILES" => 5, "EXT" => "jpg|jpeg|png|gif|xlsx|xls|csv|pdf|doc|docx"), "attrp" => " class='c1'")
);
$arrForm2 = array(
    array("type" => "select", "name" => "leadUser", "value" => $adminUserArrDD, "title" => "Lead Transfer To", "attrp" => " class='c1'"),
);

$arrFrmCmt = array(
    array("type" => "hidden", "name" => "leadDID"),
    array("type" => "textarea", "name" => "comment", "title" => "Comment", "attr" => ' style="height:67px;" ' . $readonlyAttr . ''),
    array("type" => "text", "name" => "cmtDate", "title" => "Comment Date", "attrp" => ' class="lead-detail-date"  style="display:none"', "attr" => "readonly"),
);
$arrFrm3 = array(
    array("type" => "textarea", "name" => "geolocation", "value" => $D["geolocation"] ?? "", "title" => "Geolocation", "attrp" => " class='c2'", "attr" => " readonly"),
    array("type" => "file", "name" => "visitingCard", "value" => array($D["visitingCard"] ?? "", $id ?? 0), "title" => "Visiting Card", "params" => array("MAXFILES" => 1, "EXT" => "jpg|jpeg|png|gif|xlsx|xls|csv|pdf|doc|docx"), "attrp" => " class='c2'", "attr" => " readonly"),
    array("type" => "file", "name" => "cameraUpload", "value" => array($D["cameraUpload"] ?? "", $id ?? 0), "title" => "Camera Upload", "attrp" => " class='c2 view-only' style='display:none'", "attr" => " readonly")
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Basic Lead Info</h2>
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm);
                ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Transfer Lead</h2>
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm2);
                ?>
            </ul>
            <h2 class="form-head">Comment Section</h2>
            <?php
            echo $MXFRM->getFormG(array("flds" => $arrFrmCmt, "vals" => $arrDD, "type" => 0, "addDel" => true, "class" => " small"));
            ?>
            <h2 class="form-head">Location Details</h2>
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrFrm3);
                ?>
            </ul>
        </div>
        <input type="hidden" name="contactType" value="0" />
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>