<?php
$whereS = $types = "";
$arrImage = $vals = array();
$id = 0;
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"]);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "` =?";
    $D = $DB->dbRow();

    $strUri = '<input type="hidden" id="oldUri" name="oldUri" value="' . $D["seoUri"] . '" />';
    $vals = array($id);
    $types = "i";
    $whereS = " AND categoryPID != ?";
}

$DB->types = "i" . $types;
$DB->vals = $vals;
array_unshift($DB->vals, 1);

$DB->sql = "SELECT categoryPID,categoryTitle,parentID FROM `" . $DB->pre . "pump_category` WHERE status= ? $whereS ORDER BY categoryTitle";
$arrCats = $DB->dbRows();
$strOpt = getTreeDD($arrCats, "categoryPID", "categoryTitle", "parentID", $D['parentID'] ?? "");

$arrFrom = array(
    array("type" => "text", "name" => "categoryTitle", "value" => $D["categoryTitle"] ?? "", "title" => "Category Title", "validate" => "required"),
    array("type" => "textarea", "name" => "synopsis", "value" => $D["synopsis"] ?? "", "title" => "Description", "attr" => ' rows="8"'),
    array("type" => "hidden", "name" => "oldCatName", "value" => $D["categoryTitle"] ?? "")
);

$arrFromS = array(
    array("type" => "select", "name" => "parentID", "value" => $strOpt, "title" => "Category Parent"),
    array("type" => "file", "name" => "imageName", "value" => array($D["imageName"] ?? "", $id), "title" => "file", "params" => array("EXT" => "jpg|jpeg|png|gif"), "info" => '<span class="info"> Dimention: 192 x 192</span>'),
    array("type" => "text", "name" => "xOrder", "value" => $D["xOrder"] ?? "", "title" => "Display Order", "attrp" => ' class="displayOrder"')
);

if ($_SESSION[SITEURL]["MXID"] == "SUPER") {
    $arrTemplates = getCatTemplates();
    $templateFile = getArrayDD(["data" => array("data" => $arrTemplates), "selected" => $D["templateFile"] ?? 0]); //getArrayDD($arrTemplates, $D["templateFile"]);
    array_push($arrFromS, array("type" => "select", "name" => "templateFile", "value" => $templateFile, "title" => "Template File"));
}
$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrFrom);
                ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFromS); ?>
                <?php echo $MXFRM->getFormMeta(); ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
        <?php echo $strUri ?? "";  ?>
    </form>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('.displayOrder').keypress(function() {
            return isNumber(event);
        });
    });
</script>