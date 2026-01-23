<?php
$chkd = "";
$id = 0;
$D = array("langName" => "", "langPrefix" => "", "imageName" => "");
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"]);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "`=?";
    $D = $DB->dbRow();
}

$arrForm = array(
    array("type" => "text", "name" => "langName", "value" => $D["langName"], "title" => "Language Name", "validate" => "required,name"),
    array("type" => "text", "name" => "langPrefix", "value" => $D["langPrefix"], "title" => "Language Code", "validate" => "required,maxlen:2")
);

$arrFormS = array(
    array("type" => "file", "name" => "imageName", "value" => array($D["imageName"], $id), "title" => "Language Image", "validate" => "required", "params" => array("EXT" => "jpg|png", "MAXSIZE" => 0.5, "MAXFILES" => 1), "info" => '<span class="info">Dimention: 16x13 px (Only PNG,JPG)</span>')
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <ul class="tbl-form">
                <li>
                    <?php echo $MXFRM->getForm($arrForm); ?>
                </li>
            </ul>
        </div>
        <div class="wrap-form f50">
            <ul class="tbl-form">
                <li>
                    <?php echo $MXFRM->getForm($arrFormS, array("orgID" => $D["orgID"] ?? 0)); ?>
                </li>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>