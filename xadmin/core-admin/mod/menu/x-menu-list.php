<link rel="stylesheet" type="text/css" href="<?php echo mxGetUrl(ADMINURL . '/core-admin/inc/dragsort/dragsort.css'); ?>" />
<script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(LIBURL . '/js/jquery-ui/jquery-ui.min.js'); ?>"></script>
<script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(ADMINURL . '/core-admin/js/jquery.mjs.nestedSortable.js'); ?>"></script>
<script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(ADMINURL . '/core-admin/inc/dragsort/dragsort.inc.js'); ?>"></script>
<script language="javascript" type="text/javascript" src="<?php echo mxGetUrl($TPL->modUrl . '/menu.inc.js'); ?>"></script>
<?php
include(ADMINPATH . "/core-admin/inc/dragsort/dragsort.inc.php");
$DB->vals = array($MXSTATUS);
$DB->types = "i";

$DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=?"  . mxWhere() . mxOrderBy("xOrder ASC");
$data = $DB->dbRows();
$MXTOTREC = $DB->numRows;

?>
<div class="wrap-right">
    <?php echo getPageNav('', '', array("trash", "search","print")); ?>
    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("Title", "menuTitle", ' class="title"', true),
                array("URI", "seoUri", ' class="uri"'),
                array("#ID", "menuID", ' class="item center" style="flex:0 0 60px"'),
                array("Image", "menuImage", ' class="item"'),
                array("Type", "menuType", ' class="item"'),
                array("Class", "menuClass", ' class="item"'),
                array("TplID", "templateID", ' class="item center" style="flex:0 0 60px"'),
                array("Target", "menuTarget", ' class="item center" style="flex:0 0 60px"'),
                array("Order", "xOrder", ' class="item center" style="flex:0 0 60px"'),
            );
            $arrMenu = getArrTree($data, "menuID", "parentID",  0);
            $params = array("arrSort" => $arrMenu, "pkName" => "menuID", "class" => ' class="dragsort-list sortable"', "imgCol" => "menuImage");
        ?>
            <?php echo getDragSortTitles(); ?>
            <?php echo buildDragSort($params); ?>
        <?php } else { ?>
            <div class="no-records">No records found</div>
        <?php } ?>
    </div>
</div>