<?php
$id = 0;
$D = array();

if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array($id);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE `" . $MXMOD["PK"] . "`=?";
    $D = $DB->dbRow();
}

// GST Rate options
$gstOpt = "";
$rates = array("0" => "0% (Exempt)", "5" => "5%", "12" => "12%", "18" => "18%", "28" => "28%");
$currentRate = $D["gstRate"] ?? "18";
foreach ($rates as $k => $v) {
    $sel = ($currentRate == $k) ? ' selected="selected"' : '';
    $gstOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$arrForm = array(
    array("type" => "text", "name" => "hsnCode", "value" => $D["hsnCode"] ?? "", "title" => "HSN Code", "validate" => "required", "info" => '<span class="info">4-8 digit HSN/SAC code</span>'),
    array("type" => "textarea", "name" => "description", "value" => $D["description"] ?? "", "title" => "Description", "validate" => "required", "params" => array("rows" => 3)),
    array("type" => "select", "name" => "gstRate", "value" => $gstOpt, "title" => "GST Rate", "validate" => "required", "default" => false)
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form">
            <ul class="tbl-form"><?php echo $MXFRM->getForm($arrForm); ?></ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>

<style>
.info {
    font-size: 11px;
    color: #666;
    font-style: italic;
}
</style>

<script>
// Define required JavaScript variables for form submission
var MODINCURL = '<?php echo ADMINURL; ?>/mod/hsn-code/x-hsn-code.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/hsn-code/';
var ADMINURL = '<?php echo ADMINURL; ?>';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
</script>
