<script type="text/javascript" src="<?php echo mxGetUrl($TPL->modUrl . '/inc/js/x-payment-issue.inc.js'); ?>"></script>
<?php

$id = 0;
$D = array();
$arrDD = array();
$res = calculateBalanceAmount();
$res['balanceAmount'] = $res['creditAmount'] - $res['debitAmount'];
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "` =?";
    $D = $DB->dbRow();
}
$arrForm = array(
    array("type" => "date", "name" => "paymentDate", "value" => $D["paymentDate"] ?? "", "title" => "Payment Date", "validate" => "required", "attrp" => " class='c2'", "attr" => " readonly=readonly", "params" => array("changeMonth" => true, "changeYear" => true, "yearRange" => "-100y:+1", "maxDate" => "0d")),
    array("type" => "text", "name" => "amount", "value" => $D["amount"] ?? "", "title" => "Amount", "validate" => "required", "attrp" => " class='c2'"),
    // array("type" => "text", "name" => "balanceAmount", "value" => $D["balanceAmount"] ?? calculateBalanceAmount(), "title" => "Balance Amount", "attr" => " readonly=readonly", "attrp" => " class='c3'"),
    array("type" => "hidden", "name" => "balanceAmount", "value" => $res["balanceAmount"] ?? 0, "title" => "Balance Amount", "attr" => " readonly=readonly", "attrp" => " class='c3'"),
    array("type" => "textarea", "name" => "particulars", "value" => $D["particulars"] ?? "", "title" => "Particulars", "attrp" => " class='c1'", "validate" => "required"),
    array("type" => "hidden", "name" => "userID", "value" => 14, "title" => "Leave Type Name", "validate" => "required"),
    array("type" => "hidden", "name" => "addedBy", "value" => 3, "validate" => "required"),
);
$arrForm1 = array(
    array("type" => "mxstring", "name" => "balanceAmounttotal", "value" => $res["balanceAmount"] ?? 0, "title" => "Available Balance Amount", "attr" => " readonly=readonly", "attrp" => " class='c1'"),

);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f70">
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm);
                ?>
            </ul>
        </div>
        <div class="wrap-form f30">
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm1);
                ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>