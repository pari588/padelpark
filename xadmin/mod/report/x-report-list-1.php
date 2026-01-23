<script type="text/javascript" src="<?php echo mxGetUrl($TPL->modUrl . '/inc/js/x-report.inc.js'); ?>"></script>
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

$arrForm = array(
    array("type" => "date", "name" => "fromDate", "value" => $D["fromDate"] ?? date('Y-m-d', strtotime('-15 days')), "title" => "From Date", "validate" => "required", "attrp" => ' class="c2"'),
    array("type" => "date", "name" => "toDate", "value" => $D["fromDate"] ?? date('Y-m-d'), "title" => "To Date", "validate" => "required", "attrp" => ' class="c2"'),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <div class="page-nav" id="page-nav">
        <h1 class="pg-ttl">Report Details</h1>
    </div>
    <?php //echo getPageNav("", "", array("update", "add")); 
    ?>
    <form class="wrap-data sticky-tbl" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form">
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm);
                ?>
                <li><a class="btn" id="listcheckBtn">submit</a></li>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm();
        ?>
        <div class="credit-debit-list tbl-wrap">
            <?php
            $res = checkReportList();
            echo $res['str'];
            ?>
        </div>
    </form>
</div>