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

// Parent category dropdown (exclude self)
$parentOpt = '<option value="">-- No Parent (Top Level) --</option>';
$DB->vals = array(1);
$DB->types = "i";
$excludeClause = $id > 0 ? " AND categoryID != $id" : "";
$DB->sql = "SELECT categoryID, categoryName FROM " . $DB->pre . "product_category WHERE status=?" . $excludeClause . " ORDER BY categoryName";
$categories = $DB->dbRows();
foreach ($categories as $cat) {
    $sel = (($D["parentCategoryID"] ?? 0) == $cat["categoryID"]) ? ' selected="selected"' : '';
    $parentOpt .= '<option value="' . $cat["categoryID"] . '"' . $sel . '>' . htmlspecialchars($cat["categoryName"]) . '</option>';
}

$arrForm = array(
    array("type" => "text", "name" => "categoryName", "value" => $D["categoryName"] ?? "", "title" => "Category Name", "validate" => "required"),
    array("type" => "select", "name" => "parentCategoryID", "value" => $parentOpt, "title" => "Parent Category", "default" => false),
    array("type" => "textarea", "name" => "description", "value" => $D["description"] ?? "", "title" => "Description", "params" => array("rows" => 3)),
    array("type" => "text", "name" => "sortOrder", "value" => $D["sortOrder"] ?? "0", "title" => "Sort Order", "validate" => "number")
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form">
            <ul class="tbl-form"><?php echo $MXFRM->getForm($arrForm); ?></ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>

<script>
// Define required JavaScript variables for form submission
var MODINCURL = '<?php echo ADMINURL; ?>/mod/product-category/x-product-category.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/product-category/';
var ADMINURL = '<?php echo ADMINURL; ?>';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
</script>
