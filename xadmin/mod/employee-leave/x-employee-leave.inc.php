<?php
//Start: To save leave data.
function addleave()
{
    $response = addEmployeeLeave($_POST);
    setResponse($response);
}

//Start: To update leave data.
function  updateleave()
{
    global $DB;
    $leaveID = intval($_POST["leaveID"]);
    if (isset($_POST["leaveType"]))
        $_POST["leaveType"] = cleanTitle($_POST["leaveType"]);
    if (isset($_POST["reason"]))
        $_POST["reason"] = cleanTitle($_POST["reason"]);
    if (isset($_POST["fromDate"]))
        $_POST["fromDate"] = cleanTitle($_POST["fromDate"]);
    if (isset($_POST["toDate"]))
        $_POST["toDate"] = cleanTitle($_POST["toDate"]);
    if (isset($_POST["emailID"]))
        $_POST["emailID"] = cleanTitle($_POST["emailID"]);
    $_POST["attachedFile"]  = mxGetFileName("attachedFile");
    if (isset($_POST['userID']) && $_POST['userID'] > 0) {
        $_POST["userID"] = $_POST['userID'];
    } else {
        $_POST["userID"] = $_SESSION[SITEURL]["MXID"];
    }

    $DB->table = $DB->pre . "leave";
    $DB->data = $_POST;

    if ($DB->dbUpdate("leaveID=?", "i", array($leaveID))) {
        if ($leaveID) {
            addUpdateLeaveDetails($leaveID,$_POST);
            $year = date("Y", strtotime($_POST["fromDate"]));
            $month = date("m", strtotime($_POST["fromDate"]));
            $resp = updateUserLeaves($year, $month, $_POST["userID"]);
            if($resp == 'OK'){
                setResponse(array("err" => 0, "param" => "id=$leaveID"));
            }else{
                setResponse(array("err" => 1, "msg"=>'Something went wrong!'));
            }
        }
    } else {
        setResponse(array("err" => 1));
    }
}


function getApproveDisPopup($leaveID = 0, $userID = 0)
{
    $str = ""; $leaveData = [];
    if ($leaveID > 0  && $userID > 0) {
        global $DB;
        
        $i = 1;
        $arrRoleName  = $attDetail = array();
        $arrLeaveStatus = json_decode(LEAVESTATUS, 1);
        $DB->vals = array($leaveID, 1);

        $DB->types = "ii";
        $DB->sql = "SELECT L.userID,L.attachedFile, L.reason,L.dateAdded,L.leaveType,LT.leaveTypeName as mainLeaveType,L.leaveStatus, L.snote, LD.leaveID,LD.leaveDate,LD.leaveTime,LD.lType, L.cancelReason FROM `" . $DB->pre .  "leave` AS L 
                    LEFT JOIN `" . $DB->pre . "leave_details` AS LD ON L.leaveID=LD.leaveID 
                    LEFT JOIN `" . $DB->pre . "x_admin_user` AS U ON U.userID=L.userID 
                    LEFT JOIN `" . $DB->pre . "leave_type` AS LT ON L.leaveType=LT.leaveTypeID 
                    WHERE L.leaveID = ? AND L.status = ?";
        $leaveData = $DB->dbRows();
        if($DB->numRows>0){

            $DB->vals = array($userID, 1);
            $DB->types = "ii";
            $DB->sql = "SELECT displayName FROM " . $DB->pre . "x_admin_user WHERE userID = ?  AND status = ?";
            $usr = $DB->dbRow();
            $appliedOn = (isset($leaveData[0]['dateAdded']) && $leaveData[0]['dateAdded']!='')?$leaveData[0]['dateAdded']:$leaveData[0]['leaveDate'];
            $str .= '<ul class="attndt-info mark-out-list">
                <li>
                    <label>Name:</label>
                    <p>' . $usr['displayName'] . '</p>
                    </li>
                    <li>
                    <label>Leave Type:</label>
                    <p>' . $leaveData[0]['mainLeaveType'] . '</p>
                    </li>
                    <li>
                    <label>Reason:</label>
                    <p>' . $leaveData[0]['reason'] . '</p>
                    </li>
                <li>
                    <label>Applied On:</label>
                    <p>' . date('l dS F Y', strtotime($appliedOn ?? 0)) . '</p>
                </li>
                <li><label>Details : </label>
                <table width="100%" border="0" cellpadding="7" cellspacing="0" class="tbl">
                    <tr>
                        <th align="center" width="1%">Sr.No</th>
                        <th align="center" width="10%">Date</th>
                        <th align="left" width="10%">Type</th>
                    </tr>';
            foreach ($leaveData as $D) {
                if ($D['lType'] == 1) {
                    $D['lType'] = "FullDay";
                } else if ($D['lType'] == 2) {
                    $D['lType'] = "First-Half";
                } else if ($D['lType'] == 3) {
                    $D['lType'] = "Second-Half";
                } else if ($D['lType'] == -1) {
                    $D['lType'] = "Official Holiday";
                } else {
                    $D['lType'] = "";
                }
                $str  .= '<tr>
                            <td align="center">' . $i . '</td>
                            <td align="center">' . $D['leaveDate'] . '</td>
                            <td align="left">' . $D['lType'] . '</td>';
                $str .= '</tr>';
                $i++;
            }
        }
    }
    return ["err"=> ($str!="" ? 0 : 1) ,"str"=>$str,"leaveData"=>$leaveData];
}
function updateLeaveStatus()
{
    $response['data'] = '';
    $response['count'] = 0;
    global $DB;
    $leaveID = $_POST['leaveIDs'] = intval($_POST["leaveID"]);

    if (isset($_POST["leaveStatus"]))
        $_POST["leaveStatus"] = cleanTitle($_POST["leaveStatus"]);
    if (isset($_POST["snote"]))
        $_POST["snote"] = cleanTitle($_POST["snote"]);
        $DB->table = $DB->pre . "leave";
        $DB->data = $_POST;
    if ($DB->dbUpdate("leaveID=?", "i", array($leaveID))) {
        $response = updateBalanceLeave();
        if($response['err'] == 0)
            setResponse(array("err" => 0, "alert" => "Leave Status Updated Successfully", "msg" => "Leave Status Updated Successfully"));
        else
            setResponse($response);
    } else {
        setResponse(array("err" => 1, "alert" => "Something went Wrong", "msg" => "Something went Wrong"));
    }
}

function updateBalanceLeave()
{
    global $DB, $MXRES;
    $response = array('err' => 1, 'msg' => 'Something went wrong!');
    $leaveIDs = $_POST['leaveIDs'];
    if ($leaveIDs) {
        $DB->sql = "SELECT fromDate,toDate,userID FROM `" . $DB->pre . "leave`
                        WHERE leaveID  IN ($leaveIDs) ";
        $resultD = $DB->dbRows();
        foreach ($resultD as $key => $value) {
            $userID = $value['userID'];
            $fromDate = $value['fromDate'];
            $year = date("Y", strtotime($fromDate));
            $month = date("m", strtotime($fromDate));
            $resp = updateUserLeaves($year,$month, $userID);
            if(isset($resp['err']) && $resp['err'] == 1){
                $response = array('err' => 1, 'msg' => 'Error while updating user leaves');
            }
        }
        $response = array('err' => 0, 'msg' => 'Your Yearly balance is updated');
    }
    return $response;
}
function getUserBalanceLeave($selectedUser = 0)
{
    global $DB;
    $response = array('err' => 1, 'msg' => "User ID is empty.");
    if(isset($selectedUser) && $selectedUser > 0){
        $halfDayLeave = $fullDayLeave = $monthlyappliedLeave = 0;
        $getFinancialYear = getFinancialYear();
        $startDate = $getFinancialYear['start'] . '-04-01';
        $endDate = $getFinancialYear['end']  . '-03-31';
        $DB->vals = array(1, 1, -1, 'Cancel');
        $DB->types = "iiis";

        $DB->sql = "SELECT  COUNT(*)  AS totalAppliedLeave,LD.lType 
                    FROM `" . $DB->pre .  "leave_details` AS LD 
                    LEFT JOIN `" . $DB->pre . "leave` AS L ON LD.leaveID=L.leaveID 
                    WHERE  L.userID = '" . $selectedUser . "' AND LD.leaveDate >='" . $startDate . "' AND LD.leaveDate <='" . $endDate . "' AND LD.status=? AND L.status=? AND LD.lType!=? AND L.leaveStatus!=? 
                    AND DATE(LD.leaveDate) NOT IN (
                        SELECT DATE(ahDate) FROM " . $DB->pre . "attendance_holidays WHERE ahDate >=" . $startDate . " AND ahDate <='" . $endDate . "' AND status = 1
                    )
                    GROUP BY LD.lType";
        $appliedLeave = $DB->dbRows();
        if ($DB->numRows > 0) {
            foreach ($appliedLeave as $k => $v) {
                if ($v['lType'] == 1) {
                    $fullDayLeave = $v['totalAppliedLeave'];
                } else {
                    $halfDayLeave += $v['totalAppliedLeave'];
                }
            }
            $halfDayLeave = ($halfDayLeave / 2) ?? 0;
            $appliedLeave = $fullDayLeave + $halfDayLeave;
            $monthlyappliedLeave = $appliedLeave;
        }

        $DB->vals = array($selectedUser);
        $DB->types = "i";
        $DB->sql = "SELECT totalLeaves FROM " . $DB->pre . "x_admin_user WHERE userID = ?";
        $userRes = $DB->dbRow();
        $totolLeave = $userRes['totalLeaves'];

        if(!isset($totolLeave) && $totolLeave <= 0){
            $DB->vals = array(1);
            $DB->types = "i";
            $DB->sql = "SELECT totalLeave,FYStartDate,FYEndDate FROM " . $DB->pre . "leave_setting WHERE FYStartDate <= '" . $startDate . "' AND FYEndDate >= '" . $endDate . "' AND  status = ?";
            $res = $DB->dbRow();
            $totolLeave = $res['totalLeave'];
        }

        $balanceLeave = $totolLeave - $monthlyappliedLeave;
        $response['msg'] = "Getting Balance of User";
        if ($balanceLeave > 0) {
            $response['err'] = 0;
            $response['data'] = $balanceLeave;
        } else if ($balanceLeave <= 0) {
            $response['err'] = 0;
            $response['data'] = 0;
        } else {
            $response['err'] = 0;
            $response['data'] = $totolLeave;
        }
    }
    return $response;
}


if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    require_once(SITEPATH . '/inc/common.inc.php');
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
                addleave();
                break;
            case "UPDATE":
                updateleave();
                break;
            case "mxDelFile":
                $param = array("dir" => "leave", "tbl" => "leave", "pk" => "leaveID");
                mxDelFile(array_merge($_REQUEST, $param));
                break;
            case "updateBalanceLeave":
                $MXRES = updateBalanceLeave();
                break;
            case "getHolidays":
                require_once(SITEPATH . '/inc/common.inc.php');
                $MXRES = getHolidaysDD($_POST['fromDate'], $_POST['toDate']);
                break;
            case "getApproveDisPopup":
                $MXRES = getApproveDisPopup($_POST['leaveID'], $_POST['userID']);
                break;
            case "updateLeaveStatus":
                updateLeaveStatus($_POST);
                break;
            case "getUserBalanceLeave":
                $MXRES = getUserBalanceLeave($_POST['selectedUser']);
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "leave", "PK" => "leaveID", "UDIR" => array("leaveImage" => "leave")));
}
