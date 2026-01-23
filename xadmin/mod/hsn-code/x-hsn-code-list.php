<?php
$arrSearch = array(
    array("type" => "text", "name" => "hsnID", "title" => "#ID", "where" => "AND h.hsnID=?", "dtype" => "i"),
    array("type" => "text", "name" => "hsnCode", "title" => "HSN Code", "where" => "AND h.hsnCode LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "description", "title" => "Description", "where" => "AND h.description LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "gstRate", "title" => "GST %", "where" => "AND h.gstRate=?", "dtype" => "d")
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
$DB->types = $MXFRM->types;
$DB->sql = "SELECT h.hsnID FROM `" . $DB->pre . "hsn_code` h WHERE 1=1" . $MXFRM->where;
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
                array("#ID", "hsnID", ' width="8%" align="center"', true),
                array("HSN Code", "hsnCode", ' width="15%" align="left"'),
                array("Description", "description", ' width="55%" align="left"'),
                array("GST Rate", "gstRate", ' width="12%" align="center"'),
                array("Products", "productCount", ' width="10%" align="center"')
            );

            $DB->vals = $MXFRM->vals;
            $DB->types = $MXFRM->types;
            $DB->sql = "SELECT h.*,
                               (SELECT COUNT(*) FROM " . $DB->pre . "product WHERE hsnID = h.hsnID AND status=1) as productCount
                        FROM `" . $DB->pre . "hsn_code` h
                        WHERE 1=1" . $MXFRM->where . mxOrderBy("h.hsnCode ASC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Format GST rate
                        $d["gstRate"] = '<span class="badge badge-info">' . $d["gstRate"] . '%</span>';

                        // Format product count
                        $d["productCount"] = $d["productCount"] > 0 ? '<strong>' . $d["productCount"] . '</strong>' : '<span style="color:#999;">0</span>';
                    ?>
                        <tr><?php echo getMAction("mid", $d["hsnID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["hsnID"], strip_tags($d[$v[1]] ?? '')) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records">
                <p>No HSN codes found.</p>
                <p><a href="<?php echo ADMINURL; ?>/hsn-code-add/" class="btn">+ Add HSN Code</a></p>
            </div>
        <?php } ?>
    </div>
</div>

<style>
.badge-info { background: #0dcaf0; color: #000; }
.badge {
    display: inline-block;
    padding: 4px 8px;
    font-size: 11px;
    font-weight: 500;
    border-radius: 4px;
}
</style>
