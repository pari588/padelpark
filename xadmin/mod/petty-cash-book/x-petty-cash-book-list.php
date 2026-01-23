<script type="text/javascript" src="<?php echo mxGetUrl($TPL->modUrl . '/inc/js/x-petty-cash-book.inc.js'); ?>"></script>
<?php
//Get available amount data.
$AvailableBalanceAmt = balanceAmountcheck(0, 1);



// end
// START : search array
$whrArr = array("sql" => "status=?", "types" => "i", "vals" => array("1"));
$categoryOptDDArr = getTableDD(["table" => $DB->pre . "pettycash_category", "key" => "pettyCashCatID", "val" => "pettyCashCat", "selected" => ($_GET["pettyCashCatID"] ?? ""), "where" =>  $whrArr]);
//getTableDD($DB->pre . "pettycash_category", "pettyCashCatID", "pettyCashCat", $_GET["pettyCashCatID"] ?? 0, $whrArr);
$transactionTypeArr = array("2" => "Debit", "1" => "Credit");
$transactionTypeDD = getArrayDD(["data" => array("data" => $transactionTypeArr), "selected" => $_GET["transactionType"] ?? 0]); //getArrayDD($transactionTypeArr, $_GET["transactionType"]);
$paymentModeArr = array("Cash" => "Cash", "Cheque" => "Cheque");
$paymentModeDD = getArrayDD(["data" => array("data" => $paymentModeArr), "selected" => $_GET["paymentMode"] ?? 0]); // getArrayDD($paymentModeArr, $_GET["paymentMode"]);
//$categoryArr = getDataArray($DB->pre . "pettycash_category", "pettyCashCatID", "pettyCashCat", $whrArr);
$params = ["table" => $DB->pre . "pettycash_category", "key" => "pettyCashCatID", "val" => "pettyCashCat", "where" => $whrArr];
$categoryArr  = getDataArray($params);
$arrSearch = array(
    array("type" => "text", "name" => "pettyCashBookID",  "title" => "#ID", "where" => "AND pettyCashBookID=?", "dtype" => "i"),
    array("type" => "text", "name" => "pettyCashNote", "title" => "Note", "where" => "AND pettyCashNote=?", "dtype" => "s"),
    array("type" => "select", "name" => "pettyCashCatID", "value" => $categoryOptDDArr, "title" => "Category", "where" => "AND pettyCashCatID=?", "dtype" => "i"),
    array("type" => "select", "name" => "transactionType", "value" => $transactionTypeDD, "title" => "Transaction Type", "where" => "AND transactionType=?", "dtype" => "i"),
    array("type" => "select", "name" => "paymentMode", "value" => $paymentModeDD, "title" => "Payment Mode", "where" => "AND paymentMode=?", "dtype" => "s"),
    array("type" => "date", "name" => "fromDate", "title" => "From Date", "where" => "AND DATE(transactionDate) >=?", "dtype" => "s", "attr" => "style='width:160px;'"),
    array("type" => "date", "name" => "toDate", "title" => "To Date", "where" => "AND DATE(transactionDate) <=?", "dtype" => "s", "attr" => "style='width:140px;'")
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
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "pettyCashBookID", ' width="1%" align="center"', true),
                array("Note", "pettyCashNote", ' align="left"'),
                array("Category", "pettyCashCatID", ' align="left"'),
                array("Transaction Type", "transactionType", ' align="center"'),
                array("Credit", "credit", ' align="right"', false, 'nosort'),
                array("Debit", "debit", ' align="right"', false, 'nosort'),
                array("Balance", "balanceAmount", ' align="right"'),
                array("Payment Mode", "paymentMode", ' width="1%" align="center" title="Pay Mode"'),
                array("Date", "transactionDate", ' align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT *  FROM `" . $DB->pre . $MXMOD["TBL"] . "`WHERE status=? " . $MXFRM->where . mxOrderBy(" pettyCashBookID DESC ") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead>
                    <tr> <?php echo getListTitle($MXCOLS); ?></tr>
                </thead>
                <tbody>
                    <?php
                    $creditedAmt = 0;
                    $debitedAmt = 0;
                    foreach ($DB->rows as $d) {
                        $d['credit'] = "0.00";
                        $d['debit'] = "0.00";

                        if ($d['transactionType'] == 1) {
                            $d['credit'] = $d['amount'];
                            $creditedAmt += $d["credit"];
                        } else {
                            $d['debit'] = number_format($d['amount'], 2);
                            $debitedAmt += $d["amount"];
                        }
                        //$balanceAmount+=$d['balanceAmount'];
                        $d["pettyCashCatID"] = $categoryArr['data'][$d["pettyCashCatID"]] ?? "-";
                        $d["transactionType"] = $transactionTypeArr[$d["transactionType"]] ?? "";
                        $d["paymentMode"] = $paymentModeArr[$d["paymentMode"]] ?? "";
                    ?>
                        <tr> <?php echo getMAction("mid", $d["pettyCashBookID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td <?php echo $v[2];
                                    ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3]) && $v[3] != "") {
                                        echo getViewEditUrl("id=" . $d["pettyCashBookID"], $d[$v[1]]);
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
                        <th class='action' colspan='5'>&nbsp;Total</th>
                        <th>" . number_format($creditedAmt, 2) . "</th>
                        <th>" . number_format($debitedAmt, 2)  . "</th>
                        <th>" . number_format($AvailableBalanceAmt['balanceAmount'], 2) . "</th>

                        <th><th>
                    </tr>";
                    ?>
                </tfoot>
            </table>

        <?php } else { ?>
            <div class="no-records">No records found</div>
        <?php } ?>
    </div>
</div>