<?php
// Warehouse dropdown for filter
$warehouseOptArr = array("" => "All Warehouses");
$DB->vals = array(1, 1);
$DB->types = "ii";
$DB->sql = "SELECT warehouseID, warehouseCode, warehouseName FROM " . $DB->pre . "warehouse WHERE status=? AND isActive=? ORDER BY warehouseName";
$warehouses = $DB->dbRows();
foreach ($warehouses as $w) {
    $warehouseOptArr[$w["warehouseID"]] = $w["warehouseCode"] . " - " . $w["warehouseName"];
}

// Convert to HTML options
$warehouseOpt = '';
$selWarehouse = $_GET["warehouseID"] ?? "";
foreach ($warehouseOptArr as $k => $v) {
    $sel = ($selWarehouse == $k) ? ' selected="selected"' : '';
    $warehouseOpt .= '<option value="' . $k . '"' . $sel . '>' . htmlspecialchars($v) . '</option>';
}

// Category dropdown for filter
$categoryOptArr = array("" => "All Categories");
$DB->vals = array(1);
$DB->types = "i";
$DB->sql = "SELECT categoryID, categoryName FROM " . $DB->pre . "product_category WHERE status=? ORDER BY categoryName";
$cats = $DB->dbRows();
foreach ($cats as $c) {
    $categoryOptArr[$c["categoryID"]] = $c["categoryName"];
}

// Convert to HTML options
$categoryOpt = '';
$selCategory = $_GET["categoryID"] ?? "";
foreach ($categoryOptArr as $k => $v) {
    $sel = ($selCategory == $k) ? ' selected="selected"' : '';
    $categoryOpt .= '<option value="' . $k . '"' . $sel . '>' . htmlspecialchars($v) . '</option>';
}

// Stock status dropdown
$stockStatusArr = array("" => "All", "low" => "Low Stock", "out" => "Out of Stock", "available" => "In Stock");
$stockStatusOpt = '';
$selStatus = $_GET["stockStatus"] ?? "";
foreach ($stockStatusArr as $k => $v) {
    $sel = ($selStatus == $k) ? ' selected="selected"' : '';
    $stockStatusOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

// Search filters
$arrSearch = array(
    array("type" => "select", "name" => "warehouseID", "title" => "Warehouse", "where" => "AND s.warehouseID=?", "dtype" => "i", "value" => $warehouseOpt, "default" => false),
    array("type" => "text", "name" => "productSKU", "title" => "SKU", "where" => "AND p.productSKU LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "productName", "title" => "Product", "where" => "AND p.productName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "categoryID", "title" => "Category", "where" => "AND p.categoryID=?", "dtype" => "i", "value" => $categoryOpt, "default" => false),
    array("type" => "select", "name" => "stockStatus", "title" => "Stock", "where" => "", "dtype" => "s", "value" => $stockStatusOpt, "default" => false)
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);

// Handle custom stock status filter
$stockStatusFilter = "";
if (isset($_GET["stockStatus"]) && $_GET["stockStatus"] != "") {
    switch ($_GET["stockStatus"]) {
        case "low":
            $stockStatusFilter = " AND s.quantity > 0 AND s.quantity <= p.reorderLevel";
            break;
        case "out":
            $stockStatusFilter = " AND s.quantity <= 0";
            break;
        case "available":
            $stockStatusFilter = " AND s.quantity > 0";
            break;
    }
}

$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT s.stockID FROM `" . $DB->pre . "inventory_stock` s
            LEFT JOIN `" . $DB->pre . "product` p ON s.productID = p.productID
            LEFT JOIN `" . $DB->pre . "warehouse` w ON s.warehouseID = w.warehouseID
            WHERE s.status=?" . $MXFRM->where . $stockStatusFilter;
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
                array("Warehouse", "warehouseName", ' width="18%" align="left"'),
                array("SKU", "productSKU", ' width="10%" align="left"'),
                array("Product", "productName", ' width="22%" align="left"'),
                array("Category", "categoryName", ' width="12%" align="left"'),
                array("Qty", "quantity", ' width="8%" align="right"'),
                array("UOM", "uom", ' width="5%" align="center"'),
                array("Reorder", "reorderLevel", ' width="8%" align="center"'),
                array("Status", "stockStatus", ' width="10%" align="center"'),
                array("Last Updated", "lastUpdated", ' width="12%" align="center"')
            );

            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT s.*, w.warehouseName, w.warehouseCode,
                               p.productSKU, p.productName, p.uom, p.reorderLevel,
                               c.categoryName
                        FROM `" . $DB->pre . "inventory_stock` s
                        LEFT JOIN `" . $DB->pre . "warehouse` w ON s.warehouseID = w.warehouseID
                        LEFT JOIN `" . $DB->pre . "product` p ON s.productID = p.productID
                        LEFT JOIN `" . $DB->pre . "product_category` c ON p.categoryID = c.categoryID
                        WHERE s.status=?" . $MXFRM->where . $stockStatusFilter . mxOrderBy("w.warehouseName ASC, p.productName ASC") . mxQryLimit();
            $DB->dbRows();

            // Calculate totals
            $totalQty = 0;
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        $totalQty += $d["quantity"];

                        // Format warehouse
                        $d["warehouseName"] = '<strong>' . $d["warehouseCode"] . '</strong><br><small>' . $d["warehouseName"] . '</small>';

                        // Format category
                        $d["categoryName"] = $d["categoryName"] ?? '<span style="color:#999;">-</span>';

                        // Determine stock status
                        $qty = floatval($d["quantity"]);
                        $reorder = floatval($d["reorderLevel"]);
                        if ($qty <= 0) {
                            $d["stockStatus"] = '<span class="badge badge-danger">Out of Stock</span>';
                        } elseif ($qty <= $reorder) {
                            $d["stockStatus"] = '<span class="badge badge-warning">Low Stock</span>';
                        } else {
                            $d["stockStatus"] = '<span class="badge badge-success">In Stock</span>';
                        }

                        // Format quantity
                        $d["quantity"] = '<strong>' . number_format($d["quantity"], 2) . '</strong>';

                        // Format last updated
                        $d["lastUpdated"] = $d["lastUpdated"] ? date("d-M-Y H:i", strtotime($d["lastUpdated"])) : '-';
                    ?>
                        <tr><?php echo getMAction("mid", $d["stockID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo $d[$v[1]] ?? ""; ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr style="background: #f8f9fa; font-weight: bold;">
                        <td colspan="4" align="right">Total Stock:</td>
                        <td align="right"><?php echo number_format($totalQty, 2); ?></td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        <?php } else { ?>
            <div class="no-records">No stock records found.</div>
        <?php } ?>
    </div>
</div>
