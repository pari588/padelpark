<?php
// Warehouse dropdown for filter
$warehouseOpt = array("" => "All Warehouses");
$DB->vals = array(1);
$DB->types = "i";
$DB->sql = "SELECT warehouseID, warehouseCode, warehouseName FROM " . $DB->pre . "warehouse WHERE status=? ORDER BY warehouseName";
$warehouses = $DB->dbRows();
foreach ($warehouses as $w) {
    $warehouseOpt[$w["warehouseID"]] = $w["warehouseCode"] . " - " . $w["warehouseName"];
}

// Transaction type filter (matching actual ENUM values)
$typeOpt = array(
    "" => "All Types",
    "Adjustment" => "Adjustment",
    "Transfer-In" => "Transfer In",
    "Transfer-Out" => "Transfer Out",
    "GRN" => "Purchase/GRN",
    "Sale" => "Sale",
    "Return" => "Return",
    "Damage" => "Damage"
);

// Search filters
$arrSearch = array(
    array("type" => "select", "name" => "warehouseID", "title" => "Warehouse", "where" => "AND sl.warehouseID=?", "dtype" => "i", "value" => $warehouseOpt, "default" => false),
    array("type" => "text", "name" => "productSKU", "title" => "Product SKU", "where" => "AND p.productSKU LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "transactionType", "title" => "Type", "where" => "AND sl.transactionType=?", "dtype" => "s", "value" => $typeOpt, "default" => false),
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
            WHERE 1=1" . $MXFRM->where;
$DB->dbQuery();
$MXTOTREC = $DB->numRows;
if (!$MXFRM->where && $MXTOTREC < 1) $strSearch = "";
echo $strSearch;
?>
<div class="wrap-right">
    <?php
    // Read-only ledger - hide add, trash, restore actions
    echo getPageNav('', '', array("add", "trash", "restore"));
    ?>
    <div class="wrap-data">
        <?php if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "ledgerID", ' width="5%" align="center"'),
                array("Date/Time", "created", ' width="12%" align="center"'),
                array("Warehouse", "warehouseName", ' width="15%" align="left"'),
                array("Product", "productName", ' width="18%" align="left"'),
                array("Type", "transactionType", ' width="10%" align="center"'),
                array("In", "qtyIn", ' width="7%" align="right"'),
                array("Out", "qtyOut", ' width="7%" align="right"'),
                array("Balance", "balanceQty", ' width="8%" align="right"'),
                array("Reference", "referenceNumber", ' width="10%" align="left"'),
                array("Notes", "notes", ' width="10%" align="left"')
            );

            $DB->vals = $MXFRM->vals;
            $DB->types = $MXFRM->types;
            $DB->sql = "SELECT sl.*, w.warehouseName, w.warehouseCode,
                               p.productSKU, p.productName
                        FROM `" . $DB->pre . "stock_ledger` sl
                        LEFT JOIN `" . $DB->pre . "warehouse` w ON sl.warehouseID = w.warehouseID
                        LEFT JOIN `" . $DB->pre . "product` p ON sl.productID = p.productID
                        WHERE 1=1" . $MXFRM->where . mxOrderBy("sl.created DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <!-- Read-only audit log - no checkboxes needed -->
                <thead><tr><?php echo getListTitle($MXCOLS, false); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Format date
                        $d["created"] = date("d-M-Y H:i", strtotime($d["created"]));

                        // Format warehouse
                        $d["warehouseName"] = '<strong>' . ($d["warehouseCode"] ?? '') . '</strong><br><small>' . ($d["warehouseName"] ?? '') . '</small>';

                        // Format product
                        $d["productName"] = '<strong>' . ($d["productSKU"] ?? '') . '</strong><br><small>' . ($d["productName"] ?? '') . '</small>';

                        // Format transaction type with color
                        $typeColors = array(
                            "Adjustment" => "badge-info",
                            "Transfer-In" => "badge-success",
                            "Transfer-Out" => "badge-warning",
                            "GRN" => "badge-primary",
                            "Sale" => "badge-danger",
                            "Return" => "badge-secondary",
                            "Damage" => "badge-danger"
                        );
                        $typeClass = $typeColors[$d["transactionType"]] ?? "badge-secondary";
                        $d["transactionType"] = '<span class="badge ' . $typeClass . '">' . $d["transactionType"] . '</span>';

                        // Format qty in/out with color
                        $qtyIn = floatval($d["qtyIn"]);
                        $qtyOut = floatval($d["qtyOut"]);
                        $d["qtyIn"] = $qtyIn > 0 ? '<strong style="color: #198754;">+' . number_format($qtyIn, 2) . '</strong>' : '-';
                        $d["qtyOut"] = $qtyOut > 0 ? '<strong style="color: #dc3545;">-' . number_format($qtyOut, 2) . '</strong>' : '-';

                        // Format balance
                        $d["balanceQty"] = number_format($d["balanceQty"], 2);

                        // Format reference
                        $d["referenceNumber"] = $d["referenceNumber"] ?: '-';

                        // Truncate notes
                        $d["notes"] = strlen($d["notes"] ?? '') > 25 ? substr($d["notes"], 0, 25) . '...' : ($d["notes"] ?: '-');
                    ?>
                        <tr>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo $d[$v[1]] ?? ""; ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records">No ledger entries found.</div>
        <?php } ?>
    </div>
</div>
