<script type="text/javascript" src="<?php echo mxGetUrl($TPL->modUrl . 'inc/js/x-payment-issue.inc.js'); ?>"></script>
<?php
$arrSearch = array(
    array("type" => "text", "name" => "paymentIssueID",  "title" => "#ID", "where" => "AND paymentIssueID=?", "dtype" => "i"),
    array("type" => "date", "name" => "paymentDate", "title" => "Payment Date", "where" => "AND DATE(paymentDate)=?", "dtype" => "s", "attr" => "style='width:160px;'"),
    array("type" => "text", "name" => "amount",  "title" => "Amount", "where" => "AND amount=?", "dtype" => "i"),
);

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
    <?php echo getPageNav();  ?>
    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "paymentIssueID", ' width="2%" align="center"', true),
                array("Payment Date", "paymentDate", ' width="16%" nowrap align="left"'),
                array("Particulars", "particulars", ' width="16%" nowrap align="left"'),
                array("Amount", "amount", ' width="16%" nowrap align="right"'),
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "`WHERE status=? " . $MXFRM->where . mxOrderBy("paymentIssueID DESC ") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead>
                    <tr> <?php echo getListTitle($MXCOLS); ?></tr>
                </thead>
                <tbody>
                    <?php

                    $amountTotal = 0;
                    $balanceAmountTotal = 0;
                    foreach ($DB->rows as $d) {
                        $d['amountTotal'] = $d['amount'];
                        $amountTotal += $d["amountTotal"];

                        $d['balanceAmountTotal'] = $d['balanceAmount'] ?? 0;
                        $balanceAmountTotal += $d["balanceAmountTotal"];
                    ?>
                        <tr> <?php echo getMAction("mid", $d["paymentIssueID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td <?php echo $v[2];
                                    ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3])) {
                                        echo getViewEditUrl("id=" . $d["paymentIssueID"], $d[$v[1]]);
                                    } else {
                                        echo $d[$v[1]] ?? "";
                                    }
                                    ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <?php
                    echo "<tr style='text-align:right;' class='trcolspan'>
                        <th></th>
                        <th class='action' colspan='3'>&nbsp;Total</th>               
                        <th>" . number_format($amountTotal, 2) . "</th>
                    </tr>";
                    ?>
                </tfoot>
            </table>
        <?php } else { ?>
            <div class="no-records">No records found</div>
        <?php } ?>
    </div>
</div>