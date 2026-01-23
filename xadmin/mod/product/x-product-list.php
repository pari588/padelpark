<?php
// Category dropdown for filter
$categoryOptArr = array("" => "All Categories");
$DB->vals = array(1);
$DB->types = "i";
$DB->sql = "SELECT categoryID, categoryName FROM " . $DB->pre . "product_category WHERE status=? ORDER BY categoryName";
$cats = $DB->dbRows();
foreach ($cats as $c) {
    $categoryOptArr[$c["categoryID"]] = $c["categoryName"];
}
$categoryOpt = '';
$selCategory = $_GET["categoryID"] ?? "";
foreach ($categoryOptArr as $k => $v) {
    $sel = ($selCategory == $k) ? ' selected="selected"' : '';
    $categoryOpt .= '<option value="' . $k . '"' . $sel . '>' . htmlspecialchars($v) . '</option>';
}

// Brand dropdown for filter
$brandOptArr = array("" => "All Brands");
$DB->vals = array(1);
$DB->types = "i";
$DB->sql = "SELECT brandID, brandName FROM " . $DB->pre . "product_brand WHERE status=? ORDER BY brandName";
$brands = $DB->dbRows();
foreach ($brands as $b) {
    $brandOptArr[$b["brandID"]] = $b["brandName"];
}
$brandOpt = '';
$selBrand = $_GET["brandID"] ?? "";
foreach ($brandOptArr as $k => $v) {
    $sel = ($selBrand == $k) ? ' selected="selected"' : '';
    $brandOpt .= '<option value="' . $k . '"' . $sel . '>' . htmlspecialchars($v) . '</option>';
}

// Status dropdown
$statusArr = array("" => "All", "1" => "Active", "0" => "Inactive");
$statusOpt = '';
$selStatus = $_GET["isActive"] ?? "";
foreach ($statusArr as $k => $v) {
    $sel = ($selStatus === (string)$k) ? ' selected="selected"' : '';
    $statusOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

// Search filters
$arrSearch = array(
    array("type" => "text", "name" => "productID", "title" => "#ID", "where" => "AND p.productID=?", "dtype" => "i"),
    array("type" => "text", "name" => "productSKU", "title" => "SKU", "where" => "AND p.productSKU LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "productName", "title" => "Name", "where" => "AND p.productName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "categoryID", "title" => "Category", "where" => "AND p.categoryID=?", "dtype" => "i", "value" => $categoryOpt, "default" => false),
    array("type" => "select", "name" => "brandID", "title" => "Brand", "where" => "AND p.brandID=?", "dtype" => "i", "value" => $brandOpt, "default" => false),
    array("type" => "select", "name" => "isActive", "title" => "Status", "where" => "AND p.isActive=?", "dtype" => "i", "value" => $statusOpt, "default" => false)
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT p.productID FROM `" . $DB->pre . "product` p WHERE p.status=?" . $MXFRM->where;
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
                array("#ID", "productID", ' width="5%" align="center"', true),
                array("SKU", "productSKU", ' width="10%" align="left"'),
                array("Product Name", "productName", ' width="22%" align="left"'),
                array("Category", "categoryName", ' width="12%" align="left"'),
                array("Brand", "brandName", ' width="10%" align="left"'),
                array("HSN", "hsnCode", ' width="8%" align="center"'),
                array("UOM", "uom", ' width="5%" align="center"'),
                array("Price", "basePrice", ' width="10%" align="right"'),
                array("GST", "gstRate", ' width="6%" align="center"'),
                array("Status", "isActive", ' width="7%" align="center"')
            );

            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT p.*,
                               c.categoryName,
                               b.brandName,
                               h.hsnCode
                        FROM `" . $DB->pre . "product` p
                        LEFT JOIN `" . $DB->pre . "product_category` c ON p.categoryID = c.categoryID
                        LEFT JOIN `" . $DB->pre . "product_brand` b ON p.brandID = b.brandID
                        LEFT JOIN `" . $DB->pre . "hsn_code` h ON p.hsnID = h.hsnID
                        WHERE p.status=?" . $MXFRM->where . mxOrderBy("p.productName ASC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Format category
                        $d["categoryName"] = $d["categoryName"] ?? '<span style="color:#999;">-</span>';

                        // Format brand
                        $d["brandName"] = $d["brandName"] ?? '<span style="color:#999;">-</span>';

                        // Format HSN
                        $d["hsnCode"] = $d["hsnCode"] ?? '<span style="color:#999;">-</span>';

                        // Format price
                        $d["basePrice"] = '₹' . number_format($d["basePrice"], 2);

                        // Format GST rate
                        $d["gstRate"] = $d["gstRate"] . '%';

                        // Format status
                        if ($d["isActive"] == 1) {
                            $d["isActive"] = '<span class="badge badge-success">Active</span>';
                        } else {
                            $d["isActive"] = '<span class="badge badge-danger">Inactive</span>';
                        }
                    ?>
                        <tr><?php echo getMAction("mid", $d["productID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["productID"], strip_tags($d[$v[1]] ?? '')) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records">
                <p>No products found.</p>
                <p><a href="<?php echo ADMINURL; ?>/product-add/" class="btn">+ Add Product</a></p>
            </div>
        <?php } ?>
    </div>
</div>

<style>
.badge-success { background: #198754; color: #fff; }
.badge-danger { background: #dc3545; color: #fff; }
.badge {
    display: inline-block;
    padding: 4px 8px;
    font-size: 11px;
    font-weight: 500;
    border-radius: 4px;
    text-transform: uppercase;
}
</style>
