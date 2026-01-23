<script type="text/javascript" src="<?php echo mxGetUrl($TPL->modUrl . '/inc/js/x-petty-cash-book.inc.js'); ?>"></script>
<?php
$id = 0;
$D = array();
$arrDD = array();

if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "` =?";
    $D = $DB->dbRow();
}
$transactionTypeArr = array("2" => "Debit", "1" => "Credit");
if (isset($D["transactionType"])) {
    $transactionType = array($transactionTypeArr, $D["transactionType"]);
} else {
    $transactionType = array($transactionTypeArr, 2);
}

$paymentModeOpt = array("Cash" => "Cash", "Cheque" => "Cheque");
if (isset($D["paymentMode"])) {
    $paymentMode = array($paymentModeOpt, $D["paymentMode"]);
} else {
    $paymentMode = array($paymentModeOpt, "Cash");
}

$whrArr = array("sql" => "status=?", "types" => "i", "vals" => array("1"));
$categoryOpt = getTableDD(["table" => $DB->pre . "pettycash_category", "key" => "pettyCashCatID", "val" => "pettyCashCat", "selected" => ($D['pettyCashCatID'] ?? ""), "where" =>  $whrArr]);
// getTableDD($DB->pre . "pettycash_category", "pettyCashCatID", "pettyCashCat", $D["pettyCashCatID"] ?? 0, $whrArr);
$arrForm = array(
    array("type" => "radio", "name" => "transactionType", "value" => $transactionType, "title" => "Transaction Type", "validate" => "checked:1", "attrp" => ' class="transaction-type c2"'),
    array("type" => "date", "name" => "transactionDate", "value" => $D["transactionDate"] ?? "", "title" => "Transaction Date", "validate" => "required", "attrp" => ' class="calendar c2"', "attr" => " readonly", "params" => array("numberOfMonths" => 2, "yearRange" => "-100:+0", "maxDate" => 0)),
    array("type" => "text", "name" => "amount", "value" => $D["amount"] ?? 0, 2 ?? "", "title" => "Amount", "validate" => "number", "attrp" => ' class="c2"', "validate" => "required,number,min:1"),
    array("type" => "select", "name" => "pettyCashCatID", "value" => $categoryOpt, "title" => "Category", "validate" => "required", "attrp" => ' class="category c2"'),
    array(
        "type" => "radio", "name" => "paymentMode", "value" => $paymentMode, "title" => "Payment Mode", "validate" => "checked:1",
        "attrp" => ' class="payment-mode c2" style="display:none"  rel="' . $paymentModeOpt[$D["paymentMode"] ?? "Cash"] ?? "" . '" '
    ),
    array(
        "type" => "text", "name" => "transactionNo", "value" => $D["transactionNo"] ?? "", "title" => "Cheque No.",
        "attrp" => ' class="transaction-no" style="display:none"'
    ),
);
$arrForm1 = array(
    array("type" => "textarea", "name" => "pettyCashNote", "value" => $D["pettyCashNote"] ?? "", "title" => "Details", "attrp" => ' class="text c2"', "attr" => ' rows="0"', "validate" => "required"),
    array("type" => "file", "name" => "doc1", "value" => array($D["doc1"] ?? "", $id ?? 0), "title" => "Document 1", "params" => array("MAXFILES" => "5", "EXT" => "jpg|jpeg|png|gif|xlsx|xls|csv|pdf|doc|docx"), "attrp" => " class='c2'")
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