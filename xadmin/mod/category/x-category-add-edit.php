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
}

$DB->types = "ii";
$DB->vals = array(1, 0);
$DB->sql = "SELECT categoryID,categoryTitle,parentCategoryID FROM `" . $DB->pre . "category` WHERE status= ? AND parentCategoryID= ? ORDER BY categoryTitle";
$arrCats = $DB->dbRows();
$strOpt = getTreeDD($arrCats, "categoryID", "categoryTitle", "parentCategoryID", $D['parentCategoryID'] ?? 0);
$arrFrom = array(
    array("type" => "select", "name" => "parentCategoryID", "value" => $strOpt, "title" => "Category Parent"),
    array("type" => "text", "name" => "categoryTitle", "value" => htmlentities($D["categoryTitle"] ?? ""), "title" => "Category Title", "validate" => "required"),
);
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
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>