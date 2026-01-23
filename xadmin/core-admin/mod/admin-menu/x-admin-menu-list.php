<link rel="stylesheet" type="text/css" href="<?php echo mxGetUrl(ADMINURL . '/core-admin/inc/dragsort/dragsort.css'); ?>" />
<script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(LIBURL . '/js/jquery-ui/jquery-ui.min.js'); ?>"></script>
<script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(ADMINURL . '/core-admin/js/jquery.mjs.nestedSortable.js'); ?>"></script>
<script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(ADMINURL . '/core-admin/inc/dragsort/dragsort.inc.js'); ?>"></script>
<?php
include(ADMINPATH . "/core-admin/inc/dragsort/dragsort.inc.php");

$DB->vals = array($MXSTATUS);
$DB->types = "i";
$DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=?" .  mxOrderBy("xOrder ASC");
$data = $DB->dbRows();
$MXTOTREC = $DB->numRows;
?>
<div class="wrap-right">
    <?php echo getPageNav('<a href="#" class="fa-reset btn" id="reset-menu"> Reset Menu</a>', '', array("trash", "search","print")); ?>
    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("Menu Title", "menuTitle", ' class="title"', true),
                array("Menu URI", "seoUri", ' class="uri"'),
                array("#ID", "adminMenuID", ' class="item center" style="flex:0 0 60px"'),
                array("Params", "params", ' class="item"'),
                array("Force Nav", "forceNav", ' class="item"'),
                array("Hide Menu", "hideMenu", ' class="item center"'),
                array("Order", "xOrder", ' class="item center"')
            );
            $arrMenu = getArrTree($data, "adminMenuID", "parentID",  0);
            $params = array("arrSort" => $arrMenu, "pkName" => "adminMenuID", "class" => ' class="dragsort-list sortable"', "trash" => false);
        ?>
            <?php echo getDragSortTitles(false); ?>
            <?php echo buildDragSort($params); ?>

        <?php } else { ?>
            <div class="no-records">No records found</div>
        <?php } ?>
    </div>
</div>
<script language="javascript" type="text/javascript">
    // Define required JavaScript variables
    var MODINCURL = '<?php echo ADMINURL; ?>/core-admin/mod/admin-menu/x-admin-menu.inc.php';

    $(document).ready(function(e) {
        $("a#reset-menu").click(function() {
            showMxLoader();
            $.mxajax({
                url: MODINCURL,
                data: {
                    xAction: "recreateAdminMenu"
                },
                type: "POST",
                dataType: 'json'
            }).then(function(data) {
                onResponse(data);
                window.location.reload();
            });
            return false;
        });
        initDragSort(2);
    });
</script>