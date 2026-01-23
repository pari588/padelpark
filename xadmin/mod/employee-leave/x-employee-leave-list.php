<script type="text/javascript" src="<?php echo mxGetUrl($TPL->modUrl . '/inc/js/x-employee-leaves.inc.js'); ?>"></script>

<?php
$arradmin = array("0" => "Admin","1" => "Leave Manager");
$adminIDArr = getRoleIDS($arradmin);
if ($_SESSION[SITEURL]['MXROLE'] != 'SUPER' && !in_array($_SESSION[SITEURL]['MXROLE'], $adminIDArr)) {
    $cnd = ' AND L.userID IN (' . $_SESSION[SITEURL]['MXID'] . ')';
}else if(isset($_SESSION[SITEURL]['MXROLEKEY']) && $_SESSION[SITEURL]['MXROLEKEY'] == "leaveManager"){
    $cnd = ' AND (AU.techIlliterate = 1 OR L.userID = '.$_SESSION[SITEURL]['MXID'].')';
} else {
    $cnd = '';
}
// START : search array
$arrSearch = array(
    array("type" => "text", "name" => "leaveID",  "title" => "#ID", "where" => "AND L.leaveID=?", "dtype" => "i"),
    array("type" => "text", "name" => "Name",  "title" => "Name", "where" => "AND AU.displayName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "date", "name" => "fromDate", "title" => "Date From", "where" => "AND DATE(L.fromDate) >=?", "dtype" => "s", "attr" => "style='width:160px;'"),
    array("type" => "date", "name" => "toDate", "title" => "Date To", "where" => "AND DATE(L.toDate) <=?", "dtype" => "s", "attr" => "style='width:160px;'"),
);
// END

$userWhr = array("sql" => "status=? ", "types" => "i", "vals" => array(1));
$params = ["table" => $DB->pre . "x_admin_user", "key" => "userID", "val" => "displayName", "where" => $userWhr];
$userArr  = getDataArray($params);

$leaveWhr = array("sql" => "status=? ", "types" => "i", "vals" => array(1));
$params = ["table" => $DB->pre . "leave_type", "key" => "leaveTypeID", "val" => "leaveTypeName", "where" => $leaveWhr];
$leaveArr  = getDataArray($params);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);

$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT L.*,AU.displayName,AU.userID as userID 
            FROM `" . $DB->pre . "leave` AS L 
            LEFT JOIN `" . $DB->pre . "leave_details` AS LD ON L.leaveID=LD.leaveID 
            LEFT JOIN `" . $DB->pre . "x_admin_user` AS AU ON L.userID=AU.userID 
            WHERE L.status=? " . $MXFRM->where . $cnd. " GROUP BY L.leaveID";
$DB->dbQuery();
$MXTOTREC = $DB->numRows;

if (!$MXFRM->where && $MXTOTREC < 1)
    $strSearch = "";

echo $strSearch;
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "leaveID", ' width="1%" align="center"', true),
                array("Name", "displayName", ' width="1%" align="center"'),
                array("leave type", "leaveType", ' width="20%" nowrap align="left"'),
                array("From", "fromDate", ' width="10%" align="left"'),
                array("To", "toDate", ' width="10%" align="left"'),
                array("Leave Informed", "isInformedLeave", ' width="10%" align="center"','','nosort'),
                array("Reason", "reason", ' width="400%" align="left"'),
                array("Leave Status", "leaveStatus", ' width="20%" align="center"','','nosort'),
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT L.*,LD.leaveDate,AU.displayName,AU.userID as userID FROM `" . $DB->pre . "leave` AS L 
            LEFT JOIN `" . $DB->pre . "leave_details` AS LD ON L.leaveID=LD.leaveID 
            LEFT JOIN `" . $DB->pre . "x_admin_user` AS AU ON L.userID=AU.userID 
            WHERE L.status=? " . $MXFRM->where . $cnd ." GROUP BY L.leaveID " .mxOrderBy(" leaveID DESC ") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead>
                    <tr> <?php echo getListTitle($MXCOLS); ?></tr>
                </thead>
                <tbody>
                    <?php
                    $currDate = date('Y-m-d');
                    foreach ($DB->rows as $d) {
                        $d['isInformedLeave']  = '------';
                        if($d['dateAdded'] !='' && $d['leaveDate']!='' && $d['dateAdded'] > $d['leaveDate']){
                                $d['isInformedLeave']  = 'uninformed';
                        }
             
                        if (($_SESSION[SITEURL]['MXROLE'] == 'SUPER' || in_array($_SESSION[SITEURL]['MXROLE'], $adminIDArr))) {
                            $d["leaveStatus"] = '<button class="btn leavebutton btnleave" align="center"  leaveid="' . $d["leaveID"] . '" userID="' . $d["userID"] . '"  leavestatus="' . $d["leaveStatus"] . '" >' . $d["leaveStatus"] . ' </button>';
                        }
                        $d["userID"] = $userArr["data"][$d["userID"]] ?? "";
                        $d["leaveType"] = $leaveArr["data"][$d["leaveType"]] ?? "";

                    ?>
                        <tr> <?php echo getMAction("mid", $d["leaveID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td <?php echo $v[2];
                                    ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3]) && $v[3]!='') {
                                        echo getViewEditUrl("id=" . $d["leaveID"], $d[$v[1]]);
                                    } else {
                                        echo $d[$v[1]] ?? "";
                                    }
                                    ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

        <?php } else { ?>
            <div class="no-records">No records found</div>
        <?php } ?>
    </div>
</div>
<?php

$leaveStatus = json_decode(LEAVESTATUS, 1);
$arrForm = array(
    array("type" => "hidden", "name" => "leaveID"),
    array("type" => "text", "name" => "snote", "title" => "Note", "validate" => "", "attrp" => " class='c2'"),
    array("type" => "select", "name" => "leaveStatus", "value" => getArrayDD(["data" => array("data" => $leaveStatus), "selected" => 0]), "title" => "Select Leave Status", "validate" => "required", "attrp" => " class='c2'"),
);

$MXFRM = new mxForm();
$MXFRM->xAction = "updateLeaveStatus";
?>
<div class="popup attndt-popup att-details mxdialog" style="display:none">
    <div class="body">
        <a href="#" class="close del rl" onclick="getCanceledPopup()"></a>
        <h2 class="title">Leave details</h2>
        <div class="content">

            <div class="infoData">
            </div>
            <div class="frmData">
                <form class="leaveStatusForm" name="leaveStatusForm" id="leaveStatusForm" action="" method="post" enctype="multipart/form-data">
                    <ul>
                        <?php
                            echo $MXFRM->getForm($arrForm);
                        ?>
                    </ul>
                    <?php echo $MXFRM->closeForm(); ?>
                    <div class="mx-btn">
                        <a href="javascript:void(0)" class="fa-save button" rel="leaveStatusForm">Update</a>
                        <a href="javascript:void(0)" class="button thm-btn" rel="leaveStatusForm" onclick="getCanceledPopup()"> Close </a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
</div>