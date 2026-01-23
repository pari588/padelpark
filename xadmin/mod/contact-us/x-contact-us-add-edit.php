<?php

$id = 0;
$D = array();
$ModTypeArr = ["1" => "Motor", "2" => "Pump", "3" => "Automation", "4" => "Other"];

if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "` =?";
    $D = $DB->dbRow();
    $D["modType"] = $ModTypeArr[$D["modType"]] ?? "";
}

$arrForm = array(
    array("type" => "text", "name" => "userName", "value" => $D["userName"] ?? "", "title" => "First Name", "attr" => ' readonly'),
    array("type" => "text", "name" => "userLastName", "value" => $D["userLastName"] ?? "", "title" => "Last Name", "attr" => ' readonly'),
    array("type" => "text", "name" => "userEmail", "value" => $D["userEmail"] ?? "", "title" => "Email", "attr" => ' readonly'),
    array("type" => "text", "name" => "userMobile", "value" => $D["userMobile"] ?? "", "title" => "Mobile Number", "attr" => ' readonly')
);
$arrForm1 = array(
    array("type" => "textarea", "name" => "userMessage", "value" => $D["userMessage"] ?? "", "title" => "Message", "attr" => ' readonly'),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">

    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Sender Information</h2>
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm);
                ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Message</h2>
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm1);
                ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>