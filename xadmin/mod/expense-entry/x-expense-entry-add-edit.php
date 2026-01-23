<script type="text/javascript" src="<?php echo mxGetUrl($TPL->modUrl . '/inc/js/x-expense-entry.inc.js'); ?>"></script>
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

    $D["fileAttachment"] = array($D["fileAttachment"], $D["expenseEntryID"]);
}



$whrArr = array("sql" => "status=?", "types" => "i", "vals" => array("1"));
$D["expenseTypeID"] = getTableDD(["table" => $DB->pre . "expense_type", "key" => "expenseTypeID", "val" => "expenceTitle", "selected" => ($D['expenseTypeID'] ?? ""), "where" =>  $whrArr]);
$arrDD[] = $D;

$arrForm = array(
    array("type" => "hidden", "name" => "balanceAmount", "value" => $res["balanceAmount"] ?? 0, "title" => "Balance Amount", "attr" => " readonly=readonly", "attrp" => " class='c3'"),
);

$arrForm2 = array(
    array("type" => "hidden", "name" => "expenseEntryID"),
    array("type" => "hidden", "name" => "creditDebitID"),
    array("type" => "date", "name" => "expenseEntryDate", "value" => $D["expenseEntryDate"] ?? "", "title" => "Expense Entry Date", "validate" => "required", "attr" => " readonly=readonly", "attrp" => ' width="15%"', "params" => array("changeMonth" => true, "changeYear" => true, "yearRange" => "-100y:+1", "maxDate" => "0d")),
    array("type" => "select", "name" => "expenseTypeID", "title" => " Select Type", "validate" => "required","attrp" => ' width="15%"'),
    array("type" => "text", "name" => "amount", "validate" => "required,number", "title" => "Expense Entry Amount","attrp" => ' width="15%"'),
    array("type" => "textarea", "name" => "particulars", "title" => "Particulars","validate" => "required","attrp" => ' width="15%"'),
    array("type" => "file", "name" => "fileAttachment", "title" => "Upload Documents", "params" => array("EXT" => "jpg|jpeg|png|gif|pdf"), "attrp" => ' width="15%"'),
);

$MXFRM = new mxForm();
$removeAdd = '';
if ($TPL->pageType == 'edit')
    $removeAdd = 'remove-add-row';
else
    $removeAdd = '';
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f100">
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm);
                ?>
            </ul>
            <div class="wrap-form f100 expense-entry <?php echo $removeAdd ?>">
                <h2 class="form-head">Expense Entry Details</h2>
                <?php
                echo $MXFRM->getFormG(array("flds" => $arrForm2, "vals" => $arrDD, "type" => 0, "addDel" => true,));
                ?>
            </div>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>