<?php
$DB->vals = array(1);
$DB->types = "i";
$DB->sql = "SELECT categoryID,categoryTitle,parentCategoryID FROM `" . $DB->pre . "category` where status=? ORDER BY categoryTitle ";
$arrCats = $DB->dbRows();
$strOpt = getTreeDD($arrCats, "categoryID", "categoryTitle", "parentCategoryID", $_GET['categoryID'] ?? 0, array(0));

// START : search array
$arrSearch = array(
    array("type" => "text", "name" => "productSkuID",  "title" => "#ID", "where" => "AND productSkuID=?", "dtype" => "i"),
    array("type" => "text", "name" => "productSku",  "title" => "Product-Sku", "where" => "AND productSku LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "categoryID", "value" => $strOpt, "title" => "Category", "where" => "AND categoryID=?", "dtype" => "i"),
    array("type" => "text", "name" => "currentStock",  "title" => "Current Stock", "where" => "AND currentStock=?", "dtype" => "i"),
);
// END
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);

$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT " . $MXMOD["PK"] . " FROM `" . $DB->pre . $MXMOD["TBL"] . "`  WHERE status=?" . $MXFRM->where;
$DB->dbQuery();
$MXTOTREC = $DB->numRows;

if (!$MXFRM->where && $MXTOTREC < 1)
    $strSearch = "";

echo $strSearch;
$categoryWhr = array("sql" => "status=? ", "types" => "i", "vals" => array(1));
$params = ["table" => $DB->pre . "category", "key" => "categoryID", "val" => "categoryTitle", "where" => $categoryWhr];
$categoryArr  = getDataArray($params);
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "productSkuID", ' width="1%" align="center"', true),
                array("Category Name", "categoryID", '  nowrap align="left"'),
                array("Product-Sku", "productSku", ' nowrap align="left"'),
                array("Current Stock", "currentStock", ' nowrap align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "`WHERE status=? " . $MXFRM->where . mxOrderBy("productSkuID DESC ") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead>
                    <tr> <?php echo getListTitle($MXCOLS); ?></tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($DB->rows as $d) {
                        $d["categoryID"] = $categoryArr['data'][$d["categoryID"]] ?? "";
                    ?>
                        <tr> <?php echo getMAction("mid", $d["productSkuID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td <?php echo $v[2];
                                    ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3]) && $v[3] != "") {
                                        echo getViewEditUrl("id=" . $d["productSkuID"], $d[$v[1]]);
                                    } else {
                                        echo $d[$v[1]] ?? "";
                                    }
                                    ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

        <?php } else { ?>
            <div class="no-records">No records found</div>
        <?php } ?>
    </div>
</div>