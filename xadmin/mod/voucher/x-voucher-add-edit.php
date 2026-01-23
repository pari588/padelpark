<?php
$id = 0;
$D = array();
$arrDD = array();
// get balance amount
$id = intval(1);
$DB->vals = array(1);
$DB->types = "i";
$DB->sql = "SELECT balanceAmount FROM `" . $DB->pre . "petty_cash_book" . "` WHERE status=? ORDER BY pettyCashBookID DESC LIMIT 1";
$res = $DB->dbRow();
// End. 
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "` =?";
    $D = $DB->dbRow();
} else {
    $param = array("tbl" => "voucher", "noCol" => "voucherNo", "dtCol" => "voucherDate", "prefix" => "V");
    $D["voucherNo"] = getNextNo($param);
}
$whrArr = array("sql" => "status=?", "types" => "i", "vals" => array("1"));
$categoryOpt =getTableDD(["table" => $DB->pre . "pettycash_category", "key" => "pettyCashCatID", "val" => "pettyCashCat", "selected" => ( $D["pettyCashCatID"] ?? ""), "where" =>  $whrArr]);
 //getTableDD($DB->pre . "pettycash_category", "pettyCashCatID", "pettyCashCat", $D["pettyCashCatID"] ?? 0, $whrArr);
$arrForm = array(
    array("type" => "text", "name" => "voucherNo", "value" => $D["voucherNo"] ?? "", "title" => "Voucher No", "attrp" => ' class="c2"', "attr" => "readonly"),
    array("type" => "date", "name" => "voucherDate", "value" => $D["voucherDate"] ?? "", "title" => "Date", "validate" => "required", "attr"=>" readonly","attrp" => ' class="c2" ',"params" => array("numberOfMonths" => 2, "yearRange" => "-100:+0", "maxDate" => 0)),
    array("type" => "text", "name" => "voucherDebitTo", "value" => $D["voucherDebitTo"] ?? "", "title" => "Debit To", "attrp" => ' class="c2"', "validate" => "required"),
    array("type" => "text", "name" => "voucherAmt", "value" => $D["voucherAmt"] ?? "", "title" => "Amount", "validate" => "required,number", "attrp" => ' class="c2"'),
);
$arrForm1 = array(
    array("type" => "text", "name" => "voucherTitle", "value" => $D["voucherTitle"] ?? "", "title" => "Title", "validate" => "required", "attrp" => ' class="category c2"'),
    array("type" => "select", "name" => "pettyCashCatID", "value" => $categoryOpt, "title" => "Category", "validate" => "required", "attrp" => ' class="category c2"'),
    array("type" => "textarea", "name" => "voucherDesc", "value" => $D["voucherDesc"] ?? "", "title" => "Description", "params" => array("toolbar" => "basic", "height" => 150), "attrp" => ' width="40%"'),
    array("type" => "hidden", "name" => "balanceAmount", "value" => $res["balanceAmount"])
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm);
                ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm1);
                ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>