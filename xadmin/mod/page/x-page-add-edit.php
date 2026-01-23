<?php
$D = array("pageTitle" => "", "pageContent" => "", "synopsis" => "",  "pageImage" => "", "templateFile" => "");
$id = 0;
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"]);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "`=?";
    $D = $DB->dbRow();
}

$arrFrom = array(
    array("type" => "text", "name" => "pageTitle", "value" => $D["pageTitle"], "title" => "Page Title", "validate" => "required", "attrp" => ' class="c1"'),
    array("type" => "editor", "name" => "pageContent", "value" => $D["pageContent"], "title" => "Content"),
    array("type" => "editor", "name" => "synopsis", "value" => $D["synopsis"], "title" => "Other Content")
);

$arrFromS = array(
    array("type" => "file", "name" => "pageImage", "value" => array($D["pageImage"], $id), "title" => "file")
);

if ($_SESSION[SITEURL]["MXID"] == "1" || $_SESSION[SITEURL]["MXID"] == "SUPER") {
    $arrTemplates = getPageTemplates();
    $templateFile =getArrayDD(["data" => array("data" => $arrTemplates), "selected" => $D["templateFile"]??0]);// getArrayDD($arrTemplates, $D["templateFile"]);
    array_push($arrFromS, array("type" => "select", "name" => "templateFile", "value" => $templateFile, "title" => "Template File"));
}
$MXFRM = new mxForm();
$arrSkip = array();
if (isset($_GET["h"]) && $_GET["h"] == 1)
    $arrSkip = array("add", "list", "trash");

?>
<div class="wrap-right">
    <?php echo getPageNav("", "", $arrSkip); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f70">
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFrom); ?>
            </ul>
        </div>
        <div class="wrap-form f30">
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFromS); ?>
                <?php echo $MXFRM->getFormMeta(); ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>