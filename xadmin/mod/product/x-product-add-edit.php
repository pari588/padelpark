<?php
$id = 0;
$D = array();

if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "`=?";
    $D = $DB->dbRow();
}

// Generate SKU for new entries
$productSKU = $D["productSKU"] ?? "";
if (empty($productSKU)) {
    $DB->sql = "SELECT COUNT(*) as cnt FROM " . $DB->pre . "product WHERE status=1";
    $cnt = $DB->dbRow();
    $productSKU = "PRD-" . str_pad(($cnt["cnt"] + 1), 5, "0", STR_PAD_LEFT);
}

// Category dropdown
$categoryOpt = '<option value="">-- Select Category --</option>';
$DB->vals = array(1);
$DB->types = "i";
$DB->sql = "SELECT categoryID, categoryName, parentCategoryID FROM " . $DB->pre . "product_category WHERE status=? ORDER BY categoryName";
$cats = $DB->dbRows();
$currentCat = $D["categoryID"] ?? 0;
foreach ($cats as $c) {
    $sel = ($currentCat == $c["categoryID"]) ? ' selected="selected"' : '';
    $prefix = $c["parentCategoryID"] ? '-- ' : '';
    $categoryOpt .= '<option value="' . $c["categoryID"] . '"' . $sel . '>' . $prefix . htmlspecialchars($c["categoryName"]) . '</option>';
}

// Brand dropdown
$brandOpt = '<option value="">-- Select Brand --</option>';
$DB->vals = array(1);
$DB->types = "i";
$DB->sql = "SELECT brandID, brandName FROM " . $DB->pre . "product_brand WHERE status=? ORDER BY brandName";
$brands = $DB->dbRows();
$currentBrand = $D["brandID"] ?? 0;
foreach ($brands as $b) {
    $sel = ($currentBrand == $b["brandID"]) ? ' selected="selected"' : '';
    $brandOpt .= '<option value="' . $b["brandID"] . '"' . $sel . '>' . htmlspecialchars($b["brandName"]) . '</option>';
}

// HSN Code dropdown
$hsnOpt = '<option value="">-- Select HSN Code --</option>';
$DB->sql = "SELECT hsnID, hsnCode, description, gstRate FROM " . $DB->pre . "hsn_code ORDER BY hsnCode";
$hsnCodes = $DB->dbRows();
$currentHsn = $D["hsnID"] ?? 0;
foreach ($hsnCodes as $h) {
    $sel = ($currentHsn == $h["hsnID"]) ? ' selected="selected"' : '';
    $hsnOpt .= '<option value="' . $h["hsnID"] . '" data-gst="' . $h["gstRate"] . '"' . $sel . '>' . $h["hsnCode"] . ' - ' . htmlspecialchars(substr($h["description"], 0, 40)) . ' (' . $h["gstRate"] . '%)</option>';
}

// UOM dropdown
$uomOpt = "";
$uoms = array("Pcs" => "Pieces", "Kg" => "Kilograms", "Ltr" => "Liters", "Mtr" => "Meters", "Sqm" => "Square Meters", "Set" => "Set", "Box" => "Box", "Unit" => "Unit");
$currentUom = $D["uom"] ?? "Pcs";
foreach ($uoms as $k => $v) {
    $sel = ($currentUom == $k) ? ' selected="selected"' : '';
    $uomOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

// Form array
$arrForm = array(
    // Basic Information
    array("type" => "text", "name" => "productSKU", "value" => $productSKU, "title" => "Product SKU", "validate" => "required", "info" => '<span class="info">Unique product code</span>'),
    array("type" => "text", "name" => "productName", "value" => $D["productName"] ?? "", "title" => "Product Name", "validate" => "required"),
    array("type" => "textarea", "name" => "description", "value" => $D["description"] ?? "", "title" => "Description", "params" => array("rows" => 3)),

    // Classification
    array("type" => "select", "name" => "categoryID", "value" => $categoryOpt, "title" => "Category", "default" => false),
    array("type" => "select", "name" => "brandID", "value" => $brandOpt, "title" => "Brand", "default" => false),
    array("type" => "select", "name" => "hsnID", "value" => $hsnOpt, "title" => "HSN Code", "default" => false, "info" => '<span class="info">Required for GST/E-Way Bill</span>'),

    // Pricing
    array("type" => "select", "name" => "uom", "value" => $uomOpt, "title" => "Unit of Measurement", "validate" => "required", "default" => false),
    array("type" => "text", "name" => "basePrice", "value" => $D["basePrice"] ?? "0.00", "title" => "Base Price (₹)", "validate" => "required"),
    array("type" => "text", "name" => "gstRate", "value" => $D["gstRate"] ?? "18", "title" => "GST Rate (%)", "validate" => "required", "info" => '<span class="info">Auto-filled from HSN code</span>'),

    // Dimensions (for shipping/Porter API)
    array("type" => "text", "name" => "weight", "value" => $D["weight"] ?? "", "title" => "Weight (Kg)", "info" => '<span class="info">For shipping calculations</span>'),
    array("type" => "text", "name" => "length", "value" => $D["length"] ?? "", "title" => "Length (cm)"),
    array("type" => "text", "name" => "width", "value" => $D["width"] ?? "", "title" => "Width (cm)"),
    array("type" => "text", "name" => "height", "value" => $D["height"] ?? "", "title" => "Height (cm)"),

    // Inventory Settings
    array("type" => "text", "name" => "reorderLevel", "value" => $D["reorderLevel"] ?? "10", "title" => "Reorder Level", "info" => '<span class="info">Alert when stock falls below this</span>'),
    array("type" => "checkbox", "name" => "isActive", "value" => $D["isActive"] ?? 1, "title" => "Active"),
    array("type" => "checkbox", "name" => "isStockable", "value" => $D["isStockable"] ?? 1, "title" => "Track Inventory", "info" => '<span class="info">Enable stock tracking</span>')
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form">
            <!-- Basic Information Section -->
            <div class="form-section" style="margin: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #0d6efd;">
                <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #333;">Basic Information</h3>
                <ul class="tbl-form">
                    <?php echo $MXFRM->getForm(array_slice($arrForm, 0, 3)); ?>
                </ul>
            </div>

            <!-- Classification Section -->
            <div class="form-section" style="margin: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #198754;">
                <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #333;">Classification</h3>
                <ul class="tbl-form">
                    <?php echo $MXFRM->getForm(array_slice($arrForm, 3, 3)); ?>
                </ul>
            </div>

            <!-- Pricing Section -->
            <div class="form-section" style="margin: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #ffc107;">
                <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #333;">Pricing & Tax</h3>
                <ul class="tbl-form">
                    <?php echo $MXFRM->getForm(array_slice($arrForm, 6, 3)); ?>
                </ul>
            </div>

            <!-- Dimensions Section -->
            <div class="form-section" style="margin: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #6c757d;">
                <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #333;">Dimensions (for Shipping)</h3>
                <ul class="tbl-form">
                    <?php echo $MXFRM->getForm(array_slice($arrForm, 9, 4)); ?>
                </ul>
            </div>

            <!-- Settings Section -->
            <div class="form-section" style="margin: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #dc3545;">
                <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #333;">Inventory Settings</h3>
                <ul class="tbl-form">
                    <?php echo $MXFRM->getForm(array_slice($arrForm, 13, 3)); ?>
                </ul>
            </div>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>

<script>
// Auto-fill GST rate when HSN code is selected
document.addEventListener('DOMContentLoaded', function() {
    var hsnSelect = document.querySelector('[name="hsnID"]');
    var gstInput = document.querySelector('[name="gstRate"]');

    if (hsnSelect && gstInput) {
        hsnSelect.addEventListener('change', function() {
            var selected = this.options[this.selectedIndex];
            var gstRate = selected.getAttribute('data-gst');
            if (gstRate) {
                gstInput.value = gstRate;
            }
        });
    }

    // Format price input
    var priceInput = document.querySelector('[name="basePrice"]');
    if (priceInput) {
        priceInput.addEventListener('blur', function() {
            var val = parseFloat(this.value) || 0;
            this.value = val.toFixed(2);
        });
    }
});
</script>

<style>
.form-section h3 {
    font-weight: 600;
}
.form-section .tbl-form {
    margin: 0;
    padding: 0;
}
.info {
    font-size: 11px;
    color: #666;
    font-style: italic;
}
</style>

<script>
// Define required JavaScript variables for form submission
var MODINCURL = '<?php echo ADMINURL; ?>/mod/product/x-product.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/product/';
var ADMINURL = '<?php echo ADMINURL; ?>';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
</script>
