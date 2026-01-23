<script type="text/javascript" src="<?php echo mxGetUrl($TPL->modUrl . '/inc/js/x-employee-leaves.inc.js'); ?>"></script>

<?php
require_once(SITEPATH . '/inc/common.inc.php');
$id = 0;
$D = array();
$strSts = $strStsRP = '';
$arrDD = array();
$selectType = json_decode(SELECTTYPE, 1);

$holiday = array("-1" => "Official Holiday");

if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "` =?";
    $D = $DB->dbRow();
    $userID = $D['userID'];
    //Getting details data
    $DB->vals = array($id);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "leave_details  WHERE " . $MXMOD["PK"] . "=?";
    $data = $DB->dbRows();
    foreach ($data as $k => $v) {
        $v["leaveID"] = $v["leaveID"];
        $v["userID"] = $v["userID"];
        $v["leaveDateFormat"] = "";
        if (isset($v['leaveDate'])) {
            $v["leaveDateFormat"] = Date("l, jS F Y ", strtotime($v['leaveDate']));
        }
        if (isset($v["attachedFile"])) {
            $v["attachedFile"] = array($v["attachedFile"], $v["leaveID"]);
        }

        if ($v["lType"] == '-1') {
            $v["lType"] = getArrayDD(["data" => array("data" => $holiday), "selected" => ($v["lType"] ?? 0)]);
        } else {
            $v["lType"] = getArrayDD(["data" => array("data" => $selectType), "selected" => ($v["lType"] ?? 0)]);
        }
        $arrDD[$k] = $v;
    }
} else {
    $userID = $_SESSION[SITEURL]['MXID'];
}
if (count($arrDD) < 1) {
    $v = array();
    $arrDD[] = $v;
}
$userData =[];
$userResp = getUserLeaveData($userID); // get user data and user's leaves data
if($userResp['err'] == 0){
    $userData = $userResp['data'];
    $balanceLeave = $userData['totalLeaves'] - $userData['monthlyappliedLeave'];
}

$specialLeaveWhere = array("sql" => "status = ?", "types" => "i", "vals" => array(1), "ORDER BY DESC");
$leaveType =  getTableDD(["table" => $DB->pre . "leave_type", "key" => "leaveTypeID", "val" => "leaveTypeName", "selected" => ($D["leaveType"] ?? ""), "where" =>  $specialLeaveWhere]);
$leaveStatus = json_decode(LEAVESTATUS, 1);
$arrLeaveStatus = array('Pending' => 'Pending', 'Cancel' => 'Canceled');
$whr = '';
if(isset($_SESSION[SITEURL]['MXROLEKEY']) && $_SESSION[SITEURL]['MXROLEKEY'] == "leaveManager"){
 $whr .= " AND (techIlliterate = 1 OR userID= ".$_SESSION[SITEURL]['MXID'].")";
}
$userNameWhere = array("sql" => "status = ?".$whr, "types" => "i", "vals" => array(1), "ORDER BY ASC");
$userNameArr =  getTableDD(["table" => $DB->pre . "x_admin_user", "key" => "userID", "val" => "displayName", "selected" => ($D['userID'] ?? ""), "where" =>  $userNameWhere]);


$id = intval($_GET["id"] ?? 0);
$DB->vals = array(1, $id);
$DB->types = "ii";
$DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "` =?";
$D = $DB->dbRow();
$arrFormStrHidden = "";
if (($_SESSION[SITEURL]['MXROLE'] == "SUPER" || (isset($userData['userName']) && $userData['userName'] == "admin")) || (isset($_SESSION[SITEURL]['MXROLEKEY']) && $_SESSION[SITEURL]['MXROLEKEY'] == "leaveManager")) {
    $arrFormStr =
        array("type" => "select", "name" => "userID",  "value" => $userNameArr, "title" => "Name", "validate" => "required", "prop" => ' class="text" disabled', "attr" => ' readonly="readonly"');
} else {
    $arrFormStr =
        array("type" => "text", "name" => "displayName",  "value" => $userData['displayName'] ?? "", "title" => "Name", "validate" => "", "prop" => ' class="text" disabled', "attr" => ' readonly="readonly"');
    $arrFormStrHidden = array("type" => "hidden", "name" => "userID",  "value" => $userID, "title" => "Name", "validate" => "", "prop" => ' class="text" disabled', "attr" => ' readonly="readonly"');
}
$arrForm = array(
    $arrFormStr,
    $arrFormStrHidden,
    array("type" => "select", "name" => "leaveType", "value" => $leaveType ?? "", "title" => "leave  Type", "validate" => "required"),
    array("type" => "file", "name" => "attachedFile", "value" => array($D["attachedFile"] ?? 0, $id), "title" => "upload Documents", "attrp" => ' class="attachedFile" style="display:none"', "params" => array("EXT" => "jpg|png|pdf|zip|HEIC", "MAXSIZE" => 20, "MAXFILES" => 2), "info" => '<span class="info">Max 2 files, Max Size 20 Mb, File type jpg,png,pdf,zip,HEIC</span>'),
    array("type" => "textarea", "name" => "reason", "value" => $D["reason"] ?? "", "title" => "Reason", "validate" => "required,maxlen:500"),
    array("type" => "hidden", "name" => "emailID", "value" => $D['emailID'] ?? "", "title" => "Email To", "validate" => ""),
    array("type" => "hidden", "value" => "Please add more eated (e.g. abc@example.com,xyz@example.com)", "prop" => ' class="text"'),
    array("type" => "hidden", "name" => "leaveID", "value" => $D['leaveID'] ?? "", "title" => ""),
);
$arrForm1 = array(
    array("type" => "date", "name" => "fromDate", "id" => "fromDate", "value" => $D["fromDate"] ?? "", "title" => "From", "prop" => ' class="text" readonly', "attrp" => ' class="c2" ', "validate" => "required"),
    array("type" => "date", "name" => "toDate", "id" => "toDate", "value" => $D["toDate"] ?? "", "title" => "To", "prop" => ' class="text" readonly', "attrp" => ' class="c2" ', "validate" => "required"),
    array("type" => "text", "name" => "yrBalanceLeaves",  "value" => $balanceLeave ?? 0, "title" => "Balance Leave", "validate" => "", "prop" => ' class="text" disabled', "attr" => ' readonly="readonly"'),
);

$arrForm2 = array(
    array("type" => "hidden", "name" => "leaveDetailID"),
    array("type" => "text", "name" => "leaveDateFormat", "title" => "Date"),
    array("type" => "hidden", "name" => "leaveDate", "title" => "Date"),
    array("type" => "select", "name" => "lType", "value" => getArrayDD(["data" => array("data" => $selectType), "selected" => ($D["lType"] ?? 0)]), "title" => " Select Type", "validate" => "required"),
);
if ($TPL->pageType == "edit") {
    //Show LeaveStatus Dropdown only to employee who has applied for the leave
    if ($_SESSION[SITEURL]['MXID'] == $D['userID']) {
        $arrLeaveStatus = array('Pending' => 'Pending', 'Cancel' => 'Canceled');
        $arrFrom[14] = array("type" => "select", "name" => "leaveStatus", "value" => getArrayDD(["data" => array("data" => $arrLeaveStatus), "selected" => ($D["leaveStatus"] ?? 0)]), "title" => "Change Leave Status", "validate" => "", "prop" => ' class="select"');
        $arrFrom[15] = array("type" => "hidden", "name" => "oldleaveStatus", "value" => $D["leaveStatus"], "title" => "", "validate" => "", "prop" => ' class="text"');
    }
}

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm);
                ?>
            </ul>
        </div>
        <div class="wrap-form f50 leave-details">
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm1);
                ?>
                <h2 class="form-head">Leave Details</h2>
                <?php
                    echo $MXFRM->getFormG(array("flds" => $arrForm2, "vals" => $arrDD, "type" => 0, "add" => true, "del" => true, "class" => " small"));
                ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>