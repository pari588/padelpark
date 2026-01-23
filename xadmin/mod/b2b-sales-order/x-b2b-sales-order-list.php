<?php
// B2B Sales Order List - Using standard xAdmin layout

// Order Status dropdown
$statusArr = array("" => "All Status", "Draft" => "Draft", "Confirmed" => "Confirmed", "Processing" => "Processing", "Shipped" => "Shipped", "Delivered" => "Delivered", "Cancelled" => "Cancelled");
$statusOpt = '';
$selStatus = $_GET["orderStatus"] ?? "";
foreach ($statusArr as $k => $v) {
    $sel = ($selStatus == $k) ? ' selected="selected"' : '';
    $statusOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$arrSearch = array(
    array("type" => "text", "name" => "orderID", "title" => "#ID", "where" => "AND o.orderID=?", "dtype" => "i"),
    array("type" => "text", "name" => "orderNo", "title" => "Order No", "where" => "AND o.orderNo LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "distributorName", "title" => "Distributor", "where" => "AND o.distributorName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "orderStatus", "title" => "Status", "where" => "AND o.orderStatus=?", "dtype" => "s", "value" => $statusOpt, "default" => false),
    array("type" => "date", "name" => "orderDateFrom", "title" => "Date From", "where" => "AND o.orderDate>=?", "dtype" => "s"),
    array("type" => "date", "name" => "orderDateTo", "title" => "Date To", "where" => "AND o.orderDate<=?", "dtype" => "s")
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT o.orderID FROM `" . $DB->pre . "b2b_sales_order` o WHERE o.status=?" . $MXFRM->where;
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
                array("#ID", "orderID", ' width="4%" align="center"', true),
                array("Order No", "orderNo", ' width="12%" align="left"'),
                array("Date", "orderDate", ' width="10%" align="center"'),
                array("Distributor", "distributorName", ' width="20%" align="left"'),
                array("Warehouse", "warehouseName", ' width="12%" align="left"'),
                array("Amount", "totalAmount", ' width="12%" align="right"'),
                array("Status", "orderStatus", ' width="10%" align="center"'),
                array("Payment", "paymentStatus", ' width="10%" align="center"')
            );

            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT o.*, w.warehouseName FROM `" . $DB->pre . "b2b_sales_order` o
                        LEFT JOIN `" . $DB->pre . "warehouse` w ON o.warehouseID = w.warehouseID
                        WHERE o.status=?" . $MXFRM->where . mxOrderBy("o.orderDate DESC, o.orderID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Format date
                        $d["orderDate"] = date("d-M-Y", strtotime($d["orderDate"]));

                        // Format amount
                        $d["totalAmount"] = '<strong>Rs. ' . number_format($d["totalAmount"], 2) . '</strong>';

                        // Format warehouse
                        $d["warehouseName"] = $d["warehouseName"] ?: '<span style="color:#999;">-</span>';

                        // Format order status with badges
                        $statusClasses = array(
                            "Draft" => "badge-secondary",
                            "Confirmed" => "badge-primary",
                            "Processing" => "badge-warning",
                            "Partially Shipped" => "badge-info",
                            "Shipped" => "badge-info",
                            "Delivered" => "badge-success",
                            "Cancelled" => "badge-danger"
                        );
                        $statusClass = $statusClasses[$d["orderStatus"]] ?? "badge-secondary";
                        $d["orderStatus"] = '<span class="badge ' . $statusClass . '">' . $d["orderStatus"] . '</span>';

                        // Format payment status
                        $paymentClasses = array(
                            "Pending" => "badge-secondary",
                            "Partial" => "badge-warning",
                            "Paid" => "badge-success",
                            "Overdue" => "badge-danger"
                        );
                        $paymentClass = $paymentClasses[$d["paymentStatus"]] ?? "badge-secondary";
                        $d["paymentStatus"] = '<span class="badge ' . $paymentClass . '">' . $d["paymentStatus"] . '</span>';
                    ?>
                        <tr><?php echo getMAction("mid", $d["orderID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["orderID"], strip_tags($d[$v[1]] ?? '')) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records">No sales orders found.</div>
        <?php } ?>
    </div>
</div>
