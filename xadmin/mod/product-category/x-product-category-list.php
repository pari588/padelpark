<?php
$arrSearch = array(
    array("type" => "text", "name" => "categoryID", "title" => "#ID", "where" => "AND c.categoryID=?", "dtype" => "i"),
    array("type" => "text", "name" => "categoryName", "title" => "Name", "where" => "AND c.categoryName LIKE CONCAT('%',?,'%')", "dtype" => "s")
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT c.categoryID FROM `" . $DB->pre . "product_category` c WHERE c.status=?" . $MXFRM->where;
$DB->dbQuery();
$MXTOTREC = $DB->numRows;
if (!$MXFRM->where && $MXTOTREC < 1) $strSearch = "";
echo $strSearch;
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <?php if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "categoryID", ' width="8%" align="center"', true),
                array("Category Name", "categoryName", ' width="35%" align="left"'),
                array("Parent Category", "parentName", ' width="25%" align="left"'),
                array("Description", "description", ' width="25%" align="left"'),
                array("Order", "sortOrder", ' width="7%" align="center"')
            );

            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT c.*, p.categoryName as parentName
                        FROM `" . $DB->pre . "product_category` c
                        LEFT JOIN `" . $DB->pre . "product_category` p ON c.parentCategoryID = p.categoryID
                        WHERE c.status=?" . $MXFRM->where . mxOrderBy("c.sortOrder ASC, c.categoryName ASC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        $d["parentName"] = $d["parentName"] ?? '<span style="color:#999;">-</span>';
                        $d["description"] = !empty($d["description"]) ? substr($d["description"], 0, 50) . (strlen($d["description"]) > 50 ? '...' : '') : '-';
                    ?>
                        <tr><?php echo getMAction("mid", $d["categoryID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["categoryID"], strip_tags($d[$v[1]] ?? '')) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records">No categories found</div>
        <?php } ?>
    </div>
</div>
