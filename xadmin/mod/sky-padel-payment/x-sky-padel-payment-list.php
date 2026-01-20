<?php
$arrSearch = array(
    array("type" => "text", "name" => "paymentID", "title" => "#ID", "where" => "AND p.paymentID=?", "dtype" => "i"),
    array("type" => "text", "name" => "quotationNo", "title" => "Quotation No", "where" => "AND q.quotationNo LIKE CONCAT('%',?,'%')", "dtype" => "s")
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT p.paymentID FROM `" . $DB->pre . "sky_padel_payment` p LEFT JOIN `" . $DB->pre . "sky_padel_quotation` q ON p.quotationID=q.quotationID WHERE p.status=?" . $MXFRM->where;
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
                array("#ID", "paymentID", ' width="5%" align="center"', true),
                array("Quotation No", "quotationNo", ' width="12%" align="left"'),
                array("Date", "paymentDate", ' width="10%" align="center"'),
                array("Type", "paymentType", ' width="12%" align="center"'),
                array("Method", "paymentMethod", ' width="12%" align="center"'),
                array("Amount", "amount", ' width="15%" align="right"'),
                array("Transaction ID", "transactionID", ' width="15%" align="left"'),
                array("Notes", "notes", ' width="19%" align="left"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT p.*, q.quotationNo FROM `" . $DB->pre . "sky_padel_payment` p LEFT JOIN `" . $DB->pre . "sky_padel_quotation` q ON p.quotationID=q.quotationID WHERE p.status=? " . $MXFRM->where . mxOrderBy("p.paymentID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        $d["paymentDate"] = isset($d["paymentDate"]) && $d["paymentDate"] != "0000-00-00" ? date("d-M-Y", strtotime($d["paymentDate"])) : "-";
                        $d["amount"] = "₹" . number_format($d["amount"] ?? 0, 2);
                        $typeClass = $d["paymentType"] == "Advance" ? "badge-primary" : "badge-info";
                        $d["paymentType"] = '<span class="badge ' . $typeClass . '">' . $d["paymentType"] . '</span>';
                    ?>
                        <tr><?php echo getMAction("mid", $d["paymentID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["paymentID"], strip_tags($d[$v[1]])) : ($d[$v[1]] ?? ""); ?></td>
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
