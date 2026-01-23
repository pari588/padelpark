<link rel="stylesheet" type="text/css" href="<?php echo mxGetUrl(ADMINURL . '/core-admin/inc/dragsort/dragsort.css'); ?>" />
<script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(LIBURL . '/js/jquery-ui/jquery-ui.min.js'); ?>"></script>
<script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(ADMINURL . '/core-admin/js/jquery.mjs.nestedSortable.js'); ?>"></script>
<script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(ADMINURL . '/core-admin/inc/dragsort/dragsort.inc.js'); ?>"></script>
<?php
include(ADMINPATH . "/core-admin/inc/dragsort/dragsort.inc.php");

$DB->vals = array($MXSTATUS);
$DB->types = "i";
$DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=?" . mxWhere("", false) .  mxOrderBy("xOrder ASC");
$data = $DB->dbRows();
$MXTOTREC = $DB->numRows;
?>
<div class="wrap-right">
    <?php echo getPageNav('', '', array("search", "print")); ?>
    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("Role Name", "roleName", ' class="title"', true),
                array("#ID", "roleID", ' class="item center" style="flex:0 0 60px"'),
                array("Role Email", "roleEmail", ' class="uri"'),
                array("Landing Page", "rolePage", ' class="uri"'),
                array("Role Kay", "roleKey", ' class="item center"'),
                array("Order", "xOrder", ' class="item center"')
            );

            $rID = 0;
            if ($data[0]["parentID"] > 0 && $TPL->pageType == 'trash')
                $rID = $data[0]["parentID"];

            $arrMenu = getArrTree($data, "roleID", "parentID",  $rID);
            $params = array("arrSort" => $arrMenu, "pkName" => "roleID", "class" => ' class="dragsort-list sortable"');
        ?>
            <?php echo getDragSortTitles(); ?>
            <?php echo buildDragSort($params); ?>

        <?php } else { ?>
            <div class="no-records">No records found</div>
        <?php } ?>
    </div>
</div>
<script>
    function updateRoleParentKey(result) {
        if (typeof(result.data) !== "undefined") {
            $.mxajax({
                url: MODINCURL,
                type: "POST",
                data: {
                    xAction: "updateRoleParentKey",
                    roleID: result.data.roleID,
                    parentID: result.data.parentID
                },
                dataType: "json",
            }).then(function(data) {
                //console.log(data);
            });
        }
    }

    $(document).ready(function() {
        initDragSort(6, "updateRoleParentKey");
    });
</script>