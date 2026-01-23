<?php
$id = 0;
$D = array();
$arrDD = array();
$class = 'style="display:none"';
$D['recordType'] = 3;
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "` =?";
    $D = $DB->dbRow();
}

$recordTypeArr = array("1" => "Auto", "2" => "Manual", "3" => "Backend");
$recordType = array($recordTypeArr, $D['recordType'] ?? "");

$userWhr = array("sql" => "status = ? AND userType=?", "types" => "ii", "vals" => array(1, 1), "ORDER BY DESC");
$userOpt =  getTableDD(["table" => $DB->pre . "user", "key" => "userID", "val" => "userName", "selected" => ($D["userID"] ?? 0), "where" =>  $userWhr]);

$arrForm = array(
    array("type" => "select", "name" => "userID", "value" => $userOpt, "title" => "Driver", "validate" => "required", "attrp" => ''),
    array("type" => "radio", "name" => "recordType", "value" => $recordType, "title" => "Record Type", "validate" => "checked:1", "attrp" => ' class="record-type"'),
    array("type" => "date", "name" => "dmDate", "value" => $D["dmDate"] ?? "", "title" => "Date", "validate" => "required", "attrp" => " class='c1' "),
    array("type" => "datetime", "name" => "fromTime", "value" => $D["fromTime"] ?? "", "title" => "From Time", "attrp" => " class='c1' ", "validate" => "required"),
    array("type" => "datetime", "name" => "toTime", "value" => $D["toTime"] ?? "", "title" => "To Time", "attrp" => " class='c1' ", "validate" => "required"),
);
$arrForm1 = array(
    array("type" => "textarea", "name" => "otherExpense", "value" => $D["otherExpense"] ?? "", "title" => "Description",  "attrp" => " class='c1' "),
    array("type" => "text", "name" => "expenseAmt", "value" => $D["expenseAmt"] ?? "0.00", "title" => "Amount",  "attrp" => " class='c2' "),
    array("type" => "file", "name" => "supportingDoc", "value" => array($D["supportingDoc"] ?? "", $id ?? 0), "title" => "Supporting Document", "params" => array("EXT" => "jpg|jpeg|png|pdf"), "attrp" => " class='c2' "),
);
$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f40">
            <h2 class="form-head">Date Time</h2>
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm);
                ?>
            </ul>
        </div>
        <div class="wrap-form f60">
            <h2 class="form-head">Other Expense Detail</h2>
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm1);
                ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>