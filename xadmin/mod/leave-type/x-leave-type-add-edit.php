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

    //Getting details data
    $DB->vals = array($id);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "leave_type WHERE " . $MXMOD["PK"] . "=?";
    $data = $DB->dbRows();
    foreach ($data as $k => $v) {
        $arrDD[$k] = $v;
    }
    //End.
}
if (count($arrDD) < 1) {
    $v = array();
    $arrDD[] = $v;
}


$arrForm = array(
    array("type" => "text", "name" => "leaveTypeName", "value" => $D["leaveTypeName"] ?? "", "title" => "Leave Type Name", "validate" => "required"),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form">
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm);
                ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>