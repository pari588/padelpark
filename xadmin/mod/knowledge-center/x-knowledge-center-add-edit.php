<?php
$id = 0;
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT *  FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND knowledgeCenterID =?";
    $D = $DB->dbRow();
}

$arrForm1 = array(
    array("type" => "text", "name" => "knowledgeCenterTitle", "value" => $D["knowledgeCenterTitle"] ?? "", "title" => "Title", "validate" => "required", "attrp" => " class='c1'"),
    array("type" => "editor", "name" => "knowledgeCenterContent", "value" => $D["knowledgeCenterContent"] ?? "", "title" => "Description"),
);

$arrForm2 = array(
    array("type" => "file", "name" => "knowledgeCenterImage", "value" => array($D["knowledgeCenterImage"] ?? "", $id), "title" => "Knowledge Center Image"),
    array("type" => "textarea", "name" => "synopsis", "value" => $D["synopsis"] ?? "", "title" => "Synopsis", "attr" => ' class="text" rows="8"'),
    array("type" => "datetime", "name" => "datePublish", "value" => $D["datePublish"] ?? "", "title" => "Published date", "validate" => "required"),
);


$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f60">
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm1, array("orgID" => $D["orgID"] ?? 0)); ?>

            </ul>
        </div>
        <div class="wrap-form f40">
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm2); ?>

            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>