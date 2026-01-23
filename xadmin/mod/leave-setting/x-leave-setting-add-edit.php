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
if (count($arrDD) < 1) {
    $v = array();
    $arrDD[] = $v;
}
$currently_selected = date('Y');
$nextYear = date('Y', strtotime(' + 1 years'));
$latest_year = date('Y');
$previousYear =date('Y', strtotime(' - 1 years'));

foreach (range($latest_year, $nextYear ) as $i) {

    $finanacialYear = array("2021-04-01/2022-03-31"=>"April 2021-March 2022", "$previousYear-04-01/$latest_year-03-31" => "April" . " " . $previousYear . "-" . "March" . " " .  $latest_year,"$latest_year-04-01/$nextYear-03-31" => "April" . " " . $latest_year . "-" . "March" . " " .  $nextYear);
}
$financiaYearOption=($D["FYStartDate"]??0).'/'.($D["FYEndDate"]??0);
$arrForm = array(
    array("type" => "select", "name" => "FYStartDate", "value" =>  getArrayDD(["data" => array("data" => $finanacialYear), "selected" => ($financiaYearOption ??0)]), "title" => " Select financial Year", "validate" => "required", "prop" => ' class="text" readonly'),
    array("type" => "text", "name" => "totalLeave", "value" => $D["totalLeave"]?? "", "title" => "Total Leave", "validate" => "required,min:1,max:30", "prop" => ' class="text"')
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