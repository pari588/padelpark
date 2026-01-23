<?php
// Get locations for filter dropdown
$DB->sql = "SELECT locationID, locationName FROM " . $DB->pre . "pnp_location WHERE status=1 ORDER BY locationName";
$locations = $DB->dbRows();
$locationOpt = '<option value="">All Locations</option>';
$selLoc = $_GET["locationID"] ?? "";
foreach ($locations as $loc) {
    $sel = ($selLoc == $loc["locationID"]) ? ' selected="selected"' : '';
    $locationOpt .= '<option value="' . $loc["locationID"] . '"' . $sel . '>' . htmlspecialchars($loc["locationName"]) . '</option>';
}

// Build invoice type dropdown
$typeArr = array("" => "All", "Booking" => "Booking", "Rental" => "Rental", "Combined" => "Combined", "Other" => "Other");
$typeOpt = '';
$selType = $_GET["invoiceType"] ?? "";
foreach ($typeArr as $k => $v) {
    $sel = ($selType == $k) ? ' selected="selected"' : '';
    $typeOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

// Build payment status dropdown
$payArr = array("" => "All", "Pending" => "Pending", "Paid" => "Paid", "Partial" => "Partial", "Refunded" => "Refunded");
$payOpt = '';
$selPay = $_GET["paymentStatus"] ?? "";
foreach ($payArr as $k => $v) {
    $sel = ($selPay == $k) ? ' selected="selected"' : '';
    $payOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$arrSearch = array(
    array("type" => "text", "name" => "invoiceID", "title" => "#ID", "where" => "AND i.invoiceID=?", "dtype" => "i"),
    array("type" => "text", "name" => "invoiceNo", "title" => "Invoice No", "where" => "AND i.invoiceNo LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "locationID", "title" => "Location", "where" => "AND i.locationID=?", "dtype" => "i", "value" => $locationOpt, "default" => false),
    array("type" => "text", "name" => "customerName", "title" => "Customer", "where" => "AND i.customerName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "date", "name" => "invoiceDate", "title" => "Date", "where" => "AND i.invoiceDate=?", "dtype" => "s"),
    array("type" => "select", "name" => "invoiceType", "title" => "Type", "where" => "AND i.invoiceType=?", "dtype" => "s", "value" => $typeOpt, "default" => false),
    array("type" => "select", "name" => "paymentStatus", "title" => "Payment", "where" => "AND i.paymentStatus=?", "dtype" => "s", "value" => $payOpt, "default" => false)
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT i.invoiceID FROM `" . $DB->pre . "pnp_invoice` i WHERE i.status=?" . $MXFRM->where;
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
                array("Invoice No", "invoiceNo", ' width="12%" align="left"'),
                array("Date", "invoiceDate", ' width="9%" align="center"'),
                array("Location", "locationName", ' width="14%" align="left"'),
                array("Customer", "customerName", ' width="15%" align="left"'),
                array("Type", "invoiceType", ' width="8%" align="center"'),
                array("Amount", "totalAmount", ' width="10%" align="right"'),
                array("Payment", "paymentStatus", ' width="10%" align="center"'),
                array("Status", "invoiceStatus", ' width="10%" align="center"'),
                array("Actions", "actions", ' width="8%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT i.*, l.locationName
                        FROM `" . $DB->pre . "pnp_invoice` i
                        LEFT JOIN `" . $DB->pre . "pnp_location` l ON i.locationID=l.locationID
                        WHERE i.status=? " . $MXFRM->where . mxOrderBy("i.invoiceDate DESC, i.invoiceID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Format date
                        $d["invoiceDate"] = date("d-M-Y", strtotime($d["invoiceDate"]));

                        // Format amount
                        $d["totalAmount"] = "Rs. " . number_format($d["totalAmount"], 0);

                        // Type badge
                        $typeColors = array("Booking" => "badge-primary", "Rental" => "badge-info", "Combined" => "badge-warning", "Other" => "badge-secondary");
                        $d["invoiceType"] = '<span class="badge ' . ($typeColors[$d["invoiceType"]] ?? "badge-secondary") . '">' . $d["invoiceType"] . '</span>';

                        // Payment status badge
                        $payColors = array("Pending" => "badge-warning", "Paid" => "badge-success", "Partial" => "badge-info", "Refunded" => "badge-danger");
                        $d["paymentStatus"] = '<span class="badge ' . ($payColors[$d["paymentStatus"]] ?? "badge-secondary") . '">' . $d["paymentStatus"] . '</span>';

                        // Invoice status badge
                        $statusColors = array("Draft" => "badge-secondary", "Generated" => "badge-primary", "Sent" => "badge-success", "Cancelled" => "badge-danger");
                        $d["invoiceStatus"] = '<span class="badge ' . ($statusColors[$d["invoiceStatus"]] ?? "badge-secondary") . '">' . $d["invoiceStatus"] . '</span>';

                        // Action buttons
                        $d["actions"] = '<a href="' . ADMINURL . '/mod/pnp-invoice/x-pnp-invoice-print.php?id=' . $d["invoiceID"] . '" target="_blank" class="btn btn-sm btn-info" title="View/Print"><i class="fa fa-print"></i></a>';
                    ?>
                        <tr><?php echo getMAction("mid", $d["invoiceID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["invoiceID"], $d[$v[1]]) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records" style="text-align:center; padding:60px 20px;">
                <i class="fa fa-file-invoice" style="font-size:48px;color:#ddd;margin-bottom:15px;display:block;"></i>
                <p style="margin:0; color:#888; font-size:15px;">No invoices found. Invoices are generated from completed bookings or synced from Hudle.</p>
            </div>
        <?php } ?>
    </div>
</div>
