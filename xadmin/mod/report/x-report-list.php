<script type="text/javascript" src="<?php echo mxGetUrl($TPL->modUrl . '/inc/js/x-report.inc.js'); ?>"></script>
<?php
$id = 0;
$D = array();
$arrDD = array();
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "` =?";
    $D = $DB->dbRow();
}
if (count($arrDD) < 1) {
    $v = array();
    $arrDD[] = $v;
}
$creditDebitAmount = 0;
$fromDate = (isset($_GET['fromDate'])) ? $_GET['fromDate'] : date('Y-m-01');
$toDate = (isset($_GET['toDate'])) ? $_GET['toDate'] :  date('Y-m-t', strtotime('today'));
$dateTypes = $dateWhere = "";
$dateVals = array();
if ($fromDate != "") {
    $dateTypes .= "s";
    $dateWhere .= " AND transactionDate >= ?";
    array_push($dateVals, $fromDate);
}

if ($toDate != "") {
    $dateTypes .= "s";
    $dateWhere .= " AND transactionDate <= ?";
    array_push($dateVals, $toDate);
}
$calcBalanceAmt = calculateBalanceAmount();
$total = $calcBalanceAmt["creditAmount"] - $calcBalanceAmt["debitAmount"];
$creditAmount = $calcBalanceAmt["creditAmount"];
$debitAmount = $calcBalanceAmt["debitAmount"];


// START : search array
$arrSearch = array(
    array("type" => "date", "name" => "fromDate", "value" => $fromDate, "title" => "From Date", "validate" => "required", "where" => " AND DATE(transactionDate) >= ?", "dtype" => "s", "params" => array("yearRange" => "-100:+0", "maxDate" => "0d")),
    array("type" => "date", "name" => "toDate", "value" => $toDate, "title" => "To Date", "validate" => "required", "where" => " AND DATE(transactionDate) <= ?", "dtype" => "s", "params" => array("yearRange" => "-100:+0", "maxDate" => "0d")),
);
// END
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);

$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->vals  = array_merge($DB->vals, $dateVals);
$DB->types = "i" . $dateTypes . $MXFRM->types;
$DB->sql = "SELECT CD.creditDebitID,CD.transactionDate,CD.balanceAmount,CD.amount,CD.transactionType,CD.particulars  FROM `" . $DB->pre . $MXMOD["TBL"] . "` AS CD
            WHERE CD.status=? " . $dateWhere  . $MXFRM->where . mxOrderBy(" CD.transactionDate ASC,CD.creditDebitID ASC ");
$DB->dbQuery();
$MXTOTREC = $DB->numRows;

if (!$MXFRM->where && $MXTOTREC < 1)
    // $strSearch = "";
    $MXFRM->where = "&fromDate='" . $fromDate . "'";


echo $strSearch;
?>
<div class="wrap-right">
    <?php
    ?>
    <?php echo getPageNav('<a href="javascript:void(0)" class="button" id="exportBtnReport">Download Report</a>', '', array('trash', 'add')); ?>
    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("SR NO", "srNo", ' width="1%" nowrap align="center"', false, 'nosort'),
                array("Transaction Date", "transactionDate", ' nowrap align="center"', false, 'nosort'),
                array("Particulars", "particulars", ' align="left"', false, 'nosort'),
                array("Amount Issue (Cr)", "creditAmount", ' nowrap align="right"', false, 'nosort'),
                array("Amount Expense (Dr)", "debitAmount", ' nowrap align="right"', false, 'nosort'),
                array("Balance Amount", "balanceAmount", ' nowrap align="right"', false, 'nosort'),
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->vals  = array_merge($DB->vals, $dateVals);
            $DB->types = "i" . $dateTypes . $MXFRM->types;
            $DB->sql = "SELECT CD.creditDebitID,CD.transactionDate,CD.balanceAmount,CD.amount,CD.transactionType,CD.particulars  FROM `" . $DB->pre . $MXMOD["TBL"] . "` AS CD
                        WHERE CD.status=? " . $dateWhere . $MXFRM->where . mxOrderBy(" CD.transactionDate ASC,CD.creditDebitID ASC ");
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list report">
                <thead>
                    <tr> <?php echo getListTitle($MXCOLS); ?></tr>
                </thead>
                <tbody>
                    <?php
                    $srNo = 1;
                    foreach ($DB->rows as $d) {
                        $d['creditAmount'] = ($d['transactionType'] == 1) ? $d["amount"] : number_format(0, 2);
                        $d['debitAmount'] = ($d['transactionType'] == 2) ? $d["amount"] : number_format(0, 2);
                        if ($d['transactionType'] == 1) {
                            $creditDebitAmount += $d['amount'];
                        } else if ($d['transactionType'] == 2) {
                            $creditDebitAmount -= $d['amount'];
                        }
                        $d['srNo'] = $srNo;
                        $srNo++;
                    ?>
                        <tr> <?php echo getMAction("mid", $d["creditDebitID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td <?php echo $v[2];
                                    ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3]) && $v[3] != "") {
                                        echo getViewEditUrl("id=" . $d["creditDebitID"], $d[$v[1]]);
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
                        <th class='' colspan='3'>&nbsp;Total</th>
                        <th>" . number_format($creditAmount, 2) . "</th>
                        <th>" . number_format($debitAmount, 2) . "</th>
                        <th>" . number_format($total, 2) . "</th>
                    </tr>";
                    ?>
                </tfoot>
            </table>

        <?php } else { ?>
            <div class="no-records">No records found</div>
        <?php } ?>
    </div>
</div>