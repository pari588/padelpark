<?php
// B2B Payment List - Using standard xAdmin layout

// paymentMode dropdown
$paymentModeArr = array("" => "All Modes", "Cash" => "Cash", "Cheque" => "Cheque", "NEFT" => "NEFT", "RTGS" => "RTGS", "IMPS" => "IMPS", "UPI" => "UPI");
$paymentModeOpt = '';
$selPaymentMode = $_GET["paymentMode"] ?? "";
foreach ($paymentModeArr as $k => $v) {
    $sel = ($selPaymentMode == $k) ? ' selected="selected"' : '';
    $paymentModeOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

// paymentStatus dropdown
$paymentStatusArr = array("" => "All Status", "Pending" => "Pending", "Cleared" => "Cleared", "Bounced" => "Bounced", "Cancelled" => "Cancelled");
$paymentStatusOpt = '';
$selPaymentStatus = $_GET["paymentStatus"] ?? "";
foreach ($paymentStatusArr as $k => $v) {
    $sel = ($selPaymentStatus == $k) ? ' selected="selected"' : '';
    $paymentStatusOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$arrSearch = array(
    array("type" => "text", "name" => "paymentID", "title" => "#ID", "where" => "AND p.paymentID=?", "dtype" => "i"),
    array("type" => "text", "name" => "paymentNo", "title" => "Payment No", "where" => "AND p.paymentNo LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "paymentMode", "title" => "Mode", "where" => "AND p.paymentMode=?", "dtype" => "s", "value" => $paymentModeOpt, "default" => false),
    array("type" => "select", "name" => "paymentStatus", "title" => "Status", "where" => "AND p.paymentStatus=?", "dtype" => "s", "value" => $paymentStatusOpt, "default" => false),
    array("type" => "date", "name" => "paymentDateFrom", "title" => "Date From", "where" => "AND p.paymentDate>=?", "dtype" => "s"),
    array("type" => "date", "name" => "paymentDateTo", "title" => "Date To", "where" => "AND p.paymentDate<=?", "dtype" => "s")
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT p.paymentID FROM `" . $DB->pre . "b2b_payment` p WHERE p.status=?" . $MXFRM->where;
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
                array("#ID", "paymentID", ' width="4%" align="center"', true),
                array("Payment No", "paymentNo", ' width="12%" align="left"'),
                array("Date", "paymentDate", ' width="10%" align="center"'),
                array("Distributor", "distributorName", ' width="20%" align="left"'),
                array("Mode", "paymentMode", ' width="10%" align="center"'),
                array("Reference", "transactionRef", ' width="12%" align="left"'),
                array("Amount", "amount", ' width="12%" align="right"'),
                array("Allocated", "allocatedAmount", ' width="10%" align="right"'),
                array("Status", "paymentStatus", ' width="10%" align="center"')
            );

            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT p.*, d.companyName as distributorName FROM `" . $DB->pre . "b2b_payment` p
                        LEFT JOIN `" . $DB->pre . "distributor` d ON p.distributorID = d.distributorID
                        WHERE p.status=?" . $MXFRM->where . mxOrderBy("p.paymentDate DESC, p.paymentID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Format date
                        $d["paymentDate"] = date("d-M-Y", strtotime($d["paymentDate"]));

                        // Format amounts
                        $d["amount"] = '<strong style="color:#198754;">Rs. ' . number_format($d["amount"], 2) . '</strong>';

                        $allocated = floatval($d["allocatedAmount"]);
                        $d["allocatedAmount"] = 'Rs. ' . number_format($allocated, 2);

                        // Format reference
                        $ref = $d["transactionRef"];
                        if (empty($ref) && !empty($d["chequeNo"])) {
                            $ref = 'Chq: ' . $d["chequeNo"];
                        }
                        $d["transactionRef"] = $ref ?: '<span style="color:#999;">-</span>';

                        // Format distributor
                        $d["distributorName"] = $d["distributorName"] ?: '<span style="color:#999;">-</span>';

                        // Format payment status with badges
                        $statusClasses = array(
                            "Pending" => "badge-warning",
                            "Cleared" => "badge-success",
                            "Bounced" => "badge-danger",
                            "Cancelled" => "badge-secondary"
                        );
                        $statusClass = $statusClasses[$d["paymentStatus"]] ?? "badge-secondary";
                        $d["paymentStatus"] = '<span class="badge ' . $statusClass . '">' . $d["paymentStatus"] . '</span>';
                    ?>
                        <tr><?php echo getMAction("mid", $d["paymentID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["paymentID"], strip_tags($d[$v[1]] ?? '')) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records">No payments found.</div>
        <?php } ?>
    </div>
</div>
