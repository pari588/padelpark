<?php
$id = 0;
$D = array();
$arrDD = array();
$sliderDD = array();
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "` =?";
    $D = $DB->dbRow();

    //Get best partner Data.
    $DB->vals = array($id);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "home_best_partner  WHERE " . $MXMOD["PK"] . "=?";
    $partnerDataArr = $DB->dbRows();

    foreach ($partnerDataArr as $k => $v) {
        $v["bestPartnerImg"] = array($v["bestPartnerImg"], $v["homeID"]);
        $partnerDD[$k] = $v;
    }
    //End.
    //Get home slider Data.
    $DB->vals = array($id);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "home_slider  WHERE " . $MXMOD["PK"] . "=?";
    $sliderDataArr = $DB->dbRows();

    foreach ($sliderDataArr as $k => $v) {
        $v["sliderImage"] = array($v["sliderImage"], $v["homeID"]);
        $sliderDD[$k] = $v;
    }
    //End.
}
if (is_array($partnerDD) && count($partnerDD) < 1) {
    $v = array();
    $partnerDD[] = $v;
}
if (is_array($sliderDD) && count($sliderDD) < 1) {
    $v = array();
    $sliderDD[] = $v;
}

$arrForm = array(
    array("type" => "text", "name" => "contactUsUrl", "value" => $D["contactUsUrl"], "title" => "Contact Us Button Link", "params" => array("toolbar" => "basic", "height" => 150), "attrp" => ' width="40%"'),
    array("type" => "text", "name" => "aboutUrl", "value" => $D["aboutUrl"], "title" => "Find Out More Button Link", "params" => array("toolbar" => "basic", "height" => 150), "attrp" => ' width="40%"'),
    array("type" => "editor", "name" => "homeDesc", "value" => $D["homeDesc"], "title" => "Home Descreption", "params" => array("toolbar" => "basic", "height" => 150), "attrp" => ' width="40%"')
);
$arrForm1 = array(
    array("type" => "text", "name" => "otherTitleOne", "value" => $D["otherTitleOne"] ?? "", "title" => "Title One", "validate" => "required"),
    array("type" => "textarea", "name" => "otherDescOne", "value" => $D["otherDescOne"], "title" => "Descreption One", "validate" => "required,minlen:5,maxlen:300", "attr" => ' rows="8" maxlength="300"', "info" => '<span class="info">(5 to 300 chars)</span>'),
    array("type" => "text", "name" => "otherTitleTwo", "value" => $D["otherTitleTwo"], "title" => "Title Two", "validate" => "required"),
    array("type" => "textarea", "name" => "otherDescTwo", "value" => $D["otherDescTwo"], "title" => "Descreption Two", "validate" => "required,minlen:5,maxlen:300", "attr" => ' rows="8" maxlength="300"', "info" => '<span class="info">(5 to 300 chars)</span>'),
    array("type" => "text", "name" => "otherTitleThree", "value" => $D["otherTitleThree"], "title" => "Title Three", "validate" => "required"),
    array("type" => "textarea", "name" => "otherDescThree", "value" => $D["otherDescThree"], "title" => "Descreption Three", "validate" => "required,minlen:5,maxlen:300", "attr" => ' rows="8" maxlength="300"', "info" => '<span class="info">(5 to 300 chars)</span>'),
    array("type" => "text", "name" => "otherTitleFour", "value" => $D["otherTitleFour"], "title" => "Title Four", "validate" => "required"),
    array("type" => "textarea", "name" => "otherDescFour", "value" => $D["otherDescFour"], "title" => "Descreption Four", "validate" => "required,minlen:5,maxlen:300", "attr" => ' rows="8" maxlength="300"', "info" => '<span class="info">(5 to 300 chars)</span>')
);
$arrForm2 = array(
    array("type" => "hidden", "name" => "homeSliderID"),
    array("type" => "text", "name" => "sliderTitle", "title" => "Home Slider Title"),
    array("type" => "file", "name" => "sliderImage", "title" => "Home Slider Image", "params" => array("EXT" => "jpg|jpeg|png|gif|webp")),
);
$arrForm3 = array(
    array("type" => "text", "name" => "serviceTitle", "value" => $D["serviceTitle"] ?? "", "title" => "Title", "validate" => "required", "attrp" => ' class="c2"'),
    array("type" => "file", "name" => "serviceImg", "value" => array($D["serviceImg"] ?? "", $id ?? 0), "title" => "Image", "params" => array("EXT" => "jpg|jpeg|png|gif|webp"), "attrp" => ' class="c2"'),
    array("type" => "textarea", "name" => "serviceSubTitle", "value" => $D["serviceSubTitle"], "title" => "Sub Title", "validate" => "required,minlen:5,maxlen:300", "attr" => ' rows="8" maxlength="300"', "info" => '<span class="info">(5 to 300 chars)</span>'),
    array("type" => "editor", "name" => "serviceDescOne", "value" => $D["serviceDescOne"], "title" => "Descreption One", "params" => array("toolbar" => "basic", "height" => 150), "attrp" => ' width="40%"'),
    array("type" => "editor", "name" => "serviceDescTwo", "value" => $D["serviceDescTwo"], "title" => "Descreption Two", "params" => array("toolbar" => "basic", "height" => 150), "attrp" => ' width="40%"'),
    array("type" => "editor", "name" => "serviceDescThree", "value" => $D["serviceDescThree"], "title" => "Descreption Three", "params" => array("toolbar" => "basic", "height" => 150), "attrp" => ' width="40%"')
);
$arrForm4 = array(
    array("type" => "hidden", "name" => "bestPartnerID"),
    array("type" => "text", "name" => "bestPartnerTitle", "title" => "Best Partner Title"),
    array("type" => "file", "name" => "bestPartnerImg", "title" => "Best Partner Image", "params" => array("EXT" => "jpg|jpeg|png|gif|webp")),
);
$arrForm5 = array(
    array("type" => "text", "name" => "effectiveIncrease", "value" => $D["effectiveIncrease"], "title" => "Effective Increase", "validate" => "required"),
    array("type" => "text", "name" => "yearsExperience", "value" => $D["yearsExperience"], "title" => "Years Experience")
);
$arrForm6 = array(
    array("type" => "text", "name" => "homeTitle", "value" => $D["homeTitle"], "title" => "Home Title", "validate" => "required"),
);
$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Home Slider</h2>
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm6);
                ?>
            </ul>
            <ul>
                <?php
                echo $MXFRM->getFormG(array("flds" => $arrForm2, "vals" => $sliderDD, "type" => 0, "addDel" => true));
                ?>
            </ul>
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm);
                ?>
            </ul>
            <h2 class="form-head">Our Services</h2>
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm3);
                ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Other Information</h2>
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm1);
                ?>
            </ul>
            <h2 class="form-head">Our Best Partners</h2>
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getFormG(array("flds" => $arrForm4, "vals" => $partnerDD, "type" => 0, "addDel" => true));
                ?>
            </ul>
            <h2 class="form-head">Counter One</h2>
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm5);
                ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>