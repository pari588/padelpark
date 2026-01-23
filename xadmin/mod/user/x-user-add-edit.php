<?php

$offDaysArr = ["1" => "Monday", "2" => "Tuesday", "3" => "Wednesday", "4" => "Thursday", "5" => "Friday", "6" => "Saturday", "7" => "Sunday"];
$userOffDaysArr = [];

$D = array("userName" => "", "userEmail" => "", "userMobile" => "", "userPass" => "");
$id = 0;
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"]);
    $DB->types = "ii";
    $DB->vals = array(1, $id);
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND " . $MXMOD["PK"] . "=?";
    $D = $DB->dbRow();

    //Get User Off Data.
    $DB->vals = array($id);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "user_off_days  WHERE " . $MXMOD["PK"] . "=?";
    $userOffDaysData = $DB->dbRows();
    foreach ($userOffDaysData as $k => $v) {
        $userOffDaysArr[$k] = $v['weekdayNo'];
    }


    //End.
}
$arrForm0 = array(
    array("type" => "text", "name" => "userName", "value" => $D["userName"] ?? "", "title" => "Username", "validate" => "required", "attrp" => " class='c1'"),
    array("type" => "text", "name" => "userEmail", "value" => $D["userEmail"] ?? "", "title" => "Email", "validate" => "email", "attrp" => " class='c1'"),
    array("type" => "text", "name" => "userMobileNo", "value" => $D["userMobileNo"] ?? "", "title" => "Mobile", "validate" => "number", "attrp" => " class='c1'"),
    array("type" => "text", "name" => "userLoginOTP", "value" => $D["userLoginOTP"] ?? "", "title" => "Login OTP", "validate" => "required,number", "attrp" => " class='c1'"),
    array("type" => "text", "name" => "userCity", "value" => $D["userCity"] ?? "", "title" => "City", "attrp" => " class='c1'")
);

$arrForm1 = array(
    array("type" => "time", "name" => "userFromTime", "value" => $D["userFromTime"] ?? "", "title" => "From Time", "validate" => "required", "attrp" => " class='c2' "),
    array("type" => "time", "name" => "userToTime", "value" => $D["userToTime"] ?? "", "title" => "To Time", "validate" => "required", "attrp" => " class='c2' "),
    array("type" => "text", "name" => "workingHrs", "value" => $D["workingHrs"] ?? "", "title" => "Working Hours", "attrp" => " class='c2'", "validate" => "required"),
    array("type" => "text", "name" => "overtimeAllowance", "value" => $D["overtimeAllowance"] ?? "", "title" => "Overtime Allowance", "attrp" => " class='c2'", "validate" => "required"),
    array("type" => "time", "name" => "taxiAllowanceTime", "value" => $D["taxiAllowanceTime"] ?? "", "title" => "Taxi Allowance Time", "validate" => "required", "attrp" => " class='c2' "),
    array("type" => "text", "name" => "taxiAllowance", "value" => $D["taxiAllowance"] ?? "", "title" => "Taxi Allowance", "attrp" => " class='c2'", "validate" => "required"),
    array("type" => "time", "name" => "dinnerTime", "value" => $D["dinnerTime"] ?? "", "title" => "Dinner Time", "validate" => "required", "attrp" => " class='c2' "),
    array("type" => "text", "name" => "dinnerAllowance", "value" => $D["dinnerAllowance"] ?? "", "title" => "Dinner Allowance", "attrp" => " class='c2'", "validate" => "required"),
    array("type" => "text", "name" => "offDayPriceBelow4Hr", "value" => $D["offDayPriceBelow4Hr"] ?? "", "title" => "Off Day Price Below 4 hrs", "attrp" => " class='c2'", "validate" => "required"),
    array("type" => "text", "name" => "offDayPriceAbove4Hr", "value" => $D["offDayPriceAbove4Hr"] ?? "", "title" => "Off Day Price Above 4 hrs", "attrp" => " class='c2'", "validate" => "required"),
    array("type" => "checkbox", "name" => "offDays", "value" => array($offDaysArr, $userOffDaysArr), "title" => "Off Days"),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form  f50">
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm0); ?>
            </ul>
        </div>
        <div class="wrap-form  f50">
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm1); ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>