<?php
$arrSearch = array(
    array("type" => "text", "name" => "brandID", "title" => "#ID", "where" => "AND b.brandID=?", "dtype" => "i"),
    array("type" => "text", "name" => "brandName", "title" => "Name", "where" => "AND b.brandName LIKE CONCAT('%',?,'%')", "dtype" => "s")
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT b.brandID FROM `" . $DB->pre . "product_brand` b WHERE b.status=?" . $MXFRM->where;
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
                array("#ID", "brandID", ' width="10%" align="center"', true),
                array("Brand Name", "brandName", ' width="50%" align="left"'),
                array("Description", "description", ' width="30%" align="left"'),
                array("Order", "sortOrder", ' width="10%" align="center"')
            );

            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT b.* FROM `" . $DB->pre . "product_brand` b WHERE b.status=?" . $MXFRM->where . mxOrderBy("b.sortOrder ASC, b.brandName ASC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        $d["description"] = !empty($d["description"]) ? substr($d["description"], 0, 50) . (strlen($d["description"]) > 50 ? '...' : '') : '-';
                    ?>
                        <tr><?php echo getMAction("mid", $d["brandID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["brandID"], strip_tags($d[$v[1]] ?? '')) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records">No brands found</div>
        <?php } ?>
    </div>
</div>
