<?php
function checkReportList()
{
    global $DB;
    $response['err'] = 1;
    $response['str'] = '';
    $creditDebitAmount = 0;
    $fromDate = (isset($_POST['fromDate'])) ? $_POST['fromDate'] : date('Y-m-d', strtotime('-15 days'));
    $toDate = (isset($_POST['toDate'])) ? $_POST['toDate'] : date('Y-m-d');
    $DB->vals = array(1, $fromDate, $toDate);
    $DB->types = "iss";
    $DB->sql = "SELECT CD.creditDebitID,CD.transactionDate,CD.balanceAmount,CD.amount,CD.transactionType,CD.particulars FROM `" . $DB->pre . "credit_debit` AS CD
    WHERE CD.status = ? AND CD.transactionDate >= ? AND CD.transactionDate <= ? ORDER BY CD.transactionDate ASC,CD.creditDebitID ASC ";
    $paymentData = $DB->dbRows();

    if ($DB->numRows > 0) {
        $response['str'] = ' <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
            <thead>
            <tr>
            <th width="1%" align="left">#ID</th>
            <th width="15%" align="left">Transaction Date</th>
            <th align="left">Particulars</th>
            <th align="right">Amount Issue (Cr)</th>
            <th align="right">Amount Expense (Dr)</th>
            <th align="right">Balance</th>
            </tr>
            </thead> <tbody>';
        $srNo = 1;
        foreach ($paymentData as $val) {

            $creditAmount = ($val['transactionType'] == 1) ? $val["amount"] : "";
            $debitAmount = ($val['transactionType'] == 2) ? $val["amount"] : "";

            if ($val['transactionType'] == 1) {
                $creditDebitAmount += $val['amount'];
            } else if ($val['transactionType'] == 2) {
                $creditDebitAmount -= $val['amount'];
            }

            $response['str'] .= '
                <tr class="appoint-row">
                <td>' . $srNo  . '</td>
                <td>' . $val["transactionDate"] . '</td>
                <td align="left">' . $val["particulars"] . '</td>
                <td align="right">' . $creditAmount . '</td>
                <td align="right">' . $debitAmount . '</td>           
                <td align="right">' . $creditDebitAmount . '</td>
                </tr>';
            $srNo++;
        }
        $calcBalanceAmt = calculateBalanceAmount($fromDate, $toDate);
        $total = $calcBalanceAmt["creditAmount"] - $calcBalanceAmt["debitAmount"];
        $response['err'] = 0;
        $response['str'] .= '</tody><tfoot><tr style="text-align:right;" class="trcolspan">
        <th class="action" colspan="3">&nbsp;Total</th>
        <th>' . number_format($calcBalanceAmt["creditAmount"], 2) . '</th>
        <th>' . number_format($calcBalanceAmt["debitAmount"], 2) . '</th>
        <th>' . number_format($total, 2) . '</th>
    </tr></tfoot></table>';
    } else {
        $response['err'] = 1;
        $response['str'] = "<div class='no-records'> No Record Found </div>";
    }

    return $response;
}


//End.
if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {

            case "checkReportList":
                $MXRES = checkReportList();
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "credit_debit", "PK" => "creditDebitID"));
}
