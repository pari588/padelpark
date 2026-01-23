<?php
$id = 0; $D = array();
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "`=?";
    $D = $DB->dbRow();
}

$arrForm = array(
    array("type" => "text", "name" => "categoryName", "value" => $D["categoryName"] ?? "", "title" => "Category Name", "validate" => "required"),
    array("type" => "text", "name" => "categoryCode", "value" => $D["categoryCode"] ?? "", "title" => "Category Code", "info" => '<span class="info">Short code for internal use (e.g., STEEL, TURF)</span>'),
    array("type" => "textarea", "name" => "description", "value" => $D["description"] ?? "", "title" => "Description", "params" => array("rows" => 3)),
    array("type" => "text", "name" => "sortOrder", "value" => $D["sortOrder"] ?? "0", "title" => "Sort Order", "validate" => "number", "info" => '<span class="info">Lower numbers appear first</span>')
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
