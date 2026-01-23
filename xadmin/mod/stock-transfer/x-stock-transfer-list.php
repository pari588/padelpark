<?php
// Stock Transfer List - Shows transfers from stock_ledger

// Warehouse dropdown for filter
$warehouseOptArr = array("" => "All Warehouses");
$DB->vals = array(1);
$DB->types = "i";
$DB->sql = "SELECT warehouseID, warehouseCode, warehouseName FROM " . $DB->pre . "warehouse WHERE status=? ORDER BY warehouseName";
$warehouses = $DB->dbRows();
foreach ($warehouses as $w) {
    $warehouseOptArr[$w["warehouseID"]] = $w["warehouseCode"] . " - " . $w["warehouseName"];
}

// Convert array to HTML options
$warehouseOpt = '';
$selWarehouse = $_GET["warehouseID"] ?? "";
foreach ($warehouseOptArr as $k => $v) {
    $sel = ($selWarehouse == $k) ? ' selected="selected"' : '';
    $warehouseOpt .= '<option value="' . $k . '"' . $sel . '>' . htmlspecialchars($v) . '</option>';
}

// Search filters
$arrSearch = array(
    array("type" => "select", "name" => "warehouseID", "title" => "Warehouse", "where" => "AND sl.warehouseID=?", "dtype" => "i", "value" => $warehouseOpt, "default" => false),
    array("type" => "text", "name" => "productSKU", "title" => "Product SKU", "where" => "AND p.productSKU LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "referenceNumber", "title" => "Reference", "where" => "AND sl.referenceNumber LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "date", "name" => "dateFrom", "title" => "From Date", "where" => "AND DATE(sl.created)>=?", "dtype" => "s"),
    array("type" => "date", "name" => "dateTo", "title" => "To Date", "where" => "AND DATE(sl.created)<=?", "dtype" => "s")
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
$DB->types = $MXFRM->types;
$DB->sql = "SELECT sl.ledgerID FROM `" . $DB->pre . "stock_ledger` sl
            LEFT JOIN `" . $DB->pre . "product` p ON sl.productID = p.productID
            WHERE sl.transactionType IN ('Transfer-In', 'Transfer-Out')" . $MXFRM->where;
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
                array("#ID", "ledgerID", ' width="5%" align="center"'),
                array("Date/Time", "created", ' width="12%" align="center"'),
                array("Warehouse", "warehouseName", ' width="18%" align="left"'),
                array("Product", "productName", ' width="20%" align="left"'),
                array("Type", "transactionType", ' width="10%" align="center"'),
                array("Qty", "qtyDisplay", ' width="10%" align="right"'),
                array("Balance", "balanceQty", ' width="10%" align="right"'),
                array("Reference", "referenceNumber", ' width="15%" align="left"')
            );

            $DB->vals = $MXFRM->vals;
            $DB->types = $MXFRM->types;
            $DB->sql = "SELECT sl.*, w.warehouseName, w.warehouseCode,
                               p.productSKU, p.productName
                        FROM `" . $DB->pre . "stock_ledger` sl
                        LEFT JOIN `" . $DB->pre . "warehouse` w ON sl.warehouseID = w.warehouseID
                        LEFT JOIN `" . $DB->pre . "product` p ON sl.productID = p.productID
                        WHERE sl.transactionType IN ('Transfer-In', 'Transfer-Out')" . $MXFRM->where . mxOrderBy("sl.created DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Format date
                        $d["created"] = date("d-M-Y H:i", strtotime($d["created"]));

                        // Format warehouse
                        $d["warehouseName"] = ($d["warehouseCode"] ?? '') . ' - ' . ($d["warehouseName"] ?? '');

                        // Format product
                        $d["productName"] = ($d["productSKU"] ?? '') . ' - ' . ($d["productName"] ?? '');

                        // Format transfer type
                        if ($d["transactionType"] == "Transfer-In") {
                            $d["transactionType"] = '<span class="badge badge-success">IN</span>';
                            $d["qtyDisplay"] = '<strong style="color:#198754;">+' . number_format($d["qtyIn"], 2) . '</strong>';
                        } else {
                            $d["transactionType"] = '<span class="badge badge-warning">OUT</span>';
                            $d["qtyDisplay"] = '<strong style="color:#dc3545;">-' . number_format($d["qtyOut"], 2) . '</strong>';
                        }

                        // Format balance
                        $d["balanceQty"] = number_format($d["balanceQty"], 2);

                        // Format reference
                        $d["referenceNumber"] = $d["referenceNumber"] ?: '-';
                    ?>
                        <tr><?php echo getMAction("mid", $d["ledgerID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo $d[$v[1]] ?? ""; ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records">No stock transfers found.</div>
        <?php } ?>
    </div>
</div>
