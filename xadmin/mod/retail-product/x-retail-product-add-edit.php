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
//showing category of 
$DB->vals = array(1);
$DB->types = "i";
$DB->sql = "SELECT categoryID,categoryTitle,parentCategoryID FROM `" . $DB->pre . "category` where status=? ORDER BY categoryTitle ";
$arrCats = $DB->dbRows();
$strOpt = getTreeDD($arrCats, "categoryID", "categoryTitle", "parentCategoryID", $D['categoryID'] ?? 0, array(0));

$hsnWhr = array("sql" => "status=?", "types" => "i", "vals" => array("1"));
$hsnOpt = getTableDD(["table" => $DB->pre . "hsn", "key" => "hsnID", "val" => "hsnNo", "selected" => ($D['hsnID'] ?? ""), $id, "where" =>  $hsnWhr]);

$arrForm = array(
    array("type" => "select", "name" => "categoryID", "value" => $strOpt, "title" => "Select Category", "validate" => "required"),
    array("type" => "select", "name" => "hsnID", "value" => $hsnOpt, "title" => "Select HSN", "validate" => "required"),
    array("type" => "text", "name" => "productSku", "value" => htmlentities($D["productSku"] ?? ""), "title" => "Product-Sku", "validate" => "required"),

);
// $arrForm1 = array(
//     array("type" => "text", "name" => "currentStock", "value" => $D["currentStock"] ?? "", "title" => "Current Stock"),
// );

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
            <!-- <ul class="tbl-form">
                <?php
                // echo $MXFRM->getForm($arrForm1);
                ?>
            </ul> -->
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>