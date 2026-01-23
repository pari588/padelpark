<?php
// B2B Invoice List - Using standard xAdmin layout

// invoiceStatus dropdown
$invoiceStatusArr = array("" => "All Status", "Draft" => "Draft", "Generated" => "Generated", "Sent" => "Sent", "Partially Paid" => "Partially Paid", "Paid" => "Paid", "Overdue" => "Overdue", "Cancelled" => "Cancelled");
$invoiceStatusOpt = '';
$selInvoiceStatus = $_GET["invoiceStatus"] ?? "";
foreach ($invoiceStatusArr as $k => $v) {
    $sel = ($selInvoiceStatus == $k) ? ' selected="selected"' : '';
    $invoiceStatusOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$arrSearch = array(
    array("type" => "text", "name" => "invoiceID", "title" => "#ID", "where" => "AND i.invoiceID=?", "dtype" => "i"),
    array("type" => "text", "name" => "invoiceNo", "title" => "Invoice No", "where" => "AND i.invoiceNo LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "distributorName", "title" => "Distributor", "where" => "AND i.distributorName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "invoiceStatus", "title" => "Status", "where" => "AND i.invoiceStatus=?", "dtype" => "s", "value" => $invoiceStatusOpt, "default" => false),
    array("type" => "date", "name" => "invoiceDateFrom", "title" => "Date From", "where" => "AND i.invoiceDate>=?", "dtype" => "s"),
    array("type" => "date", "name" => "invoiceDateTo", "title" => "Date To", "where" => "AND i.invoiceDate<=?", "dtype" => "s")
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT i.invoiceID FROM `" . $DB->pre . "b2b_invoice` i WHERE i.status=?" . $MXFRM->where;
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
                array("#ID", "invoiceID", ' width="4%" align="center"', true),
                array("Invoice No", "invoiceNo", ' width="11%" align="left"'),
                array("Date", "invoiceDate", ' width="7%" align="center"'),
                array("Due Date", "dueDate", ' width="7%" align="center"'),
                array("Distributor", "distributorName", ' width="16%" align="left"'),
                array("Total", "totalAmount", ' width="9%" align="right"'),
                array("Paid", "paidAmount", ' width="9%" align="right"'),
                array("Balance", "balanceAmount", ' width="9%" align="right"'),
                array("Status", "invoiceStatus", ' width="8%" align="center"'),
                array("Actions", "actions", ' width="10%" align="center"')
            );

            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT i.* FROM `" . $DB->pre . "b2b_invoice` i
                        WHERE i.status=?" . $MXFRM->where . mxOrderBy("i.invoiceDate DESC, i.invoiceID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Check if overdue
                        $isOverdue = ($d["invoiceStatus"] != "Paid" && $d["invoiceStatus"] != "Cancelled" && strtotime($d["dueDate"]) < strtotime(date("Y-m-d")));

                        // Format dates
                        $d["invoiceDate"] = date("d-M-Y", strtotime($d["invoiceDate"]));
                        $dueDisplay = date("d-M-Y", strtotime($d["dueDate"]));
                        if ($isOverdue) {
                            $daysOverdue = floor((time() - strtotime($d["dueDate"])) / 86400);
                            $dueDisplay .= '<br><small style="color:#dc3545;">' . $daysOverdue . ' days</small>';
                        }
                        $d["dueDate"] = $dueDisplay;

                        // Format amounts
                        $d["totalAmount"] = '<strong>Rs. ' . number_format($d["totalAmount"], 2) . '</strong>';
                        $d["paidAmount"] = '<span style="color:#198754;">Rs. ' . number_format($d["paidAmount"], 2) . '</span>';

                        $balance = floatval($d["balanceAmount"]);
                        $d["balanceAmount"] = $balance > 0
                            ? '<span style="color:#dc3545;font-weight:bold;">Rs. ' . number_format($balance, 2) . '</span>'
                            : '<span style="color:#198754;">Rs. 0</span>';

                        // Store original status for action check
                        $originalStatus = $d["invoiceStatus"];

                        // Format invoice status with badges
                        $statusClasses = array(
                            "Draft" => "badge-secondary",
                            "Generated" => "badge-primary",
                            "Sent" => "badge-info",
                            "Partially Paid" => "badge-warning",
                            "Paid" => "badge-success",
                            "Overdue" => "badge-danger",
                            "Cancelled" => "badge-danger"
                        );
                        $statusClass = $statusClasses[$d["invoiceStatus"]] ?? "badge-secondary";
                        $d["invoiceStatus"] = '<span class="badge ' . $statusClass . '">' . $d["invoiceStatus"] . '</span>';

                        // Actions
                        $actions = '<a href="' . ADMINURL . '/b2b-invoice-add/?id=' . $d["invoiceID"] . '" title="View">View</a> | <a href="' . ADMINURL . '/mod/b2b-invoice/x-b2b-invoice-print.php?id=' . $d["invoiceID"] . '" target="_blank" title="Print">Print</a>';
                        // Add cancel button for non-cancelled invoices
                        if ($originalStatus != "Cancelled") {
                            $actions .= ' | <a href="javascript:void(0)" onclick="cancelInvoice(' . $d["invoiceID"] . ')" title="Cancel" style="color:#dc3545;">Cancel</a>';
                        }
                        $d["actions"] = $actions;
                    ?>
                        <tr<?php echo $isOverdue ? ' style="background:#fff3cd;"' : ''; ?>><?php echo getMAction("mid", $d["invoiceID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["invoiceID"], strip_tags($d[$v[1]] ?? '')) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records">No invoices found.</div>
        <?php } ?>
    </div>
</div>

<script>
function cancelInvoice(invoiceID) {
    if (!confirm('Are you sure you want to cancel this invoice?')) return;

    var reason = prompt('Please enter reason for cancellation:');
    if (!reason) return;

    // Show loading
    var btn = event.target;
    var originalText = btn.innerHTML;
    btn.innerHTML = 'Cancelling...';
    btn.style.pointerEvents = 'none';

    $.mxajax({
        url: '<?php echo ADMINURL; ?>/mod/b2b-invoice/x-b2b-invoice.inc.php',
        data: {
            xAction: 'CANCEL',
            invoiceID: invoiceID,
            reason: reason
        }
    }).then(function(res) {
        btn.innerHTML = originalText;
        btn.style.pointerEvents = 'auto';

        if (res.err == 0) {
            alert(res.msg || 'Invoice cancelled successfully.');
            location.reload();
        } else {
            alert(res.msg || 'Error cancelling invoice');
        }
    }).catch(function(err) {
        btn.innerHTML = originalText;
        btn.style.pointerEvents = 'auto';
        alert('Error: ' + (err.message || 'Failed to cancel invoice'));
    });
}
</script>
