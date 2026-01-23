<script type="text/javascript" src="<?php echo mxGetUrl($TPL->modUrl . '/inc/js/x-voucher.inc.js'); ?>"></script>
<?php
$whrArr = array("sql" => "status=?", "types" => "i", "vals" => array("1"));
$categoryOptDDArr =getTableDD(["table" => $DB->pre . "pettycash_category", "key" => "pettyCashCatID", "val" => "pettyCashCat", "selected" => (  $_GET["pettyCashCatID"]?? ""), "where" =>  $whrArr]);
// getTableDD($DB->pre . "pettycash_category", "pettyCashCatID", "pettyCashCat", $_GET["pettyCashCatID"] ?? 0, $whrArr);
//$categoryArr = getDataArray($DB->pre . "pettycash_category", "pettyCashCatID", "pettyCashCat", $whrArr);

$params = ["table" => $DB->pre . "pettycash_category", "key" => "pettyCashCatID", "val" => "pettyCashCat", "where" => $whrArr];
$categoryArr  = getDataArray($params);
// START : search array
$arrSearch = array(
    array("type" => "text", "name" => "voucherID",  "title" => "#ID", "where" => "AND voucherID=?", "dtype" => "i"),
    array("type" => "text", "name" => "voucherTitle",  "title" => "Title", "where" => "AND voucherTitle LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "pettyCashCatID", "value" => $categoryOptDDArr, "title" => "Category", "where" => "AND pettyCashCatID=?", "dtype" => "i"),
    array("type" => "text", "name" => "voucherNo",  "title" => "Voucher No", "where" => "AND voucherNo LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "voucherDebitTo",  "title" => "Debit To", "where" => "AND voucherDebitTo LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "voucherAmt",  "title" => "Amount", "where" => "AND voucherAmt LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "date", "name" => "fromDate", "title" => "From Date", "where" => "AND DATE(voucherDate) >=?", "dtype" => "s", "attr" => "style='width:160px;'"),
    array("type" => "date", "name" => "toDate", "title" => "To Date", "where" => "AND DATE(voucherDate) <=?", "dtype" => "s", "attr" => "style='width:140px;'")
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
?>
<div class="wrap-right">
    <?php echo getPageNav('<a href="javascript:void(0)" class="button download-zip">Download Zip</a>'); ?>
    <div class="wrap-data">
        <?php
        // <i class="fa-sharp fa-light fa-file-pdf"></i>
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "voucherID", ' width="1%" align="center"', true),
                array("Preview", "preview", ' width="2%" align="center"', false, 'nosort'),
                array("Print", "print", ' width="2%" align="center"', false, 'nosort'),
                array("Title", "voucherTitle", ' align="left"'),
                array("Category", "pettyCashCatID", ' align="left"'),
                array("Voucher No", "voucherNo", ' nowrap align="center"'),
                array("Debit To", "voucherDebitTo", ' align="center"'),
                array("Amount", "voucherAmt", ' align="right"'),
                array("Voucher Date", "voucherDate", ' align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT *  FROM `" . $DB->pre . $MXMOD["TBL"] . "`WHERE status=? " . $MXFRM->where . mxOrderBy(" voucherID DESC ") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead>
                    <tr> <?php echo getListTitle($MXCOLS); ?></tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($DB->rows as $d) {
                        $d["preview"] = '<a href="#" class="fa-print btn ico preview-voucher" title="Print" voucherID="' . $d["voucherID"] . '" rel="0"></a>';
                        $d["print"] = '<a href="#" class="fa-print btn ico print-voucher" title="Print" voucherID="' . $d["voucherID"] . '" rel="pdf"></a>';
                        $d["pettyCashCatID"] = $categoryArr['data'][$d["pettyCashCatID"]]??0;
                    ?>
                        <tr> <?php echo getMAction("mid", $d["voucherID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td <?php echo $v[2];
                                    ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3]) && $v[3] != "") {
                                        echo getViewEditUrl("id=" . $d["voucherID"], $d[$v[1]]);
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