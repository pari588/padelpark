<?php
function addUser()
{
    global $DB, $MXRES;
    $_POST["seoUri"] = makeSeoUri($_POST["displayName"]);
    $_POST["imageName"] = mxGetFileName("imageName");
    $_POST["userPass"] = md5($_POST["userPass"]);

    $_POST['techIlliterate']= (!isset($_POST['techIlliterate']) || $_POST['techIlliterate'] <= 0)? 0 : $_POST['techIlliterate'];
    $_POST['isLeaveManager']= (!isset($_POST['isLeaveManager']) || $_POST['isLeaveManager'] <= 0)? 0 : $_POST['isLeaveManager'];

    $DB->table = $DB->pre . "x_admin_user";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $userID = $DB->insertID;
        setResponse(["err" => 0, "param" => "id=$userID"]);
    } else {
        setResponse(["err" => 1]);
    }
}

function updateUser()
{
    global $DB, $MXRES;
    $_POST["imageName"] = mxGetFileName("imageName");
    if ($_POST["userPass"])
        $_POST["userPass"] = md5($_POST["userPass"]);
    else
        unset($_POST["userPass"]);
    $userID = intval($_POST["userID"]);

    $_POST['techIlliterate']= (!isset($_POST['techIlliterate']) || $_POST['techIlliterate'] <= 0)? 0 : $_POST['techIlliterate'];
    $_POST['isLeaveManager']= (!isset($_POST['isLeaveManager']) || $_POST['isLeaveManager'] <= 0)? 0 : $_POST['isLeaveManager'];

    $DB->table = $DB->pre . "x_admin_user";
    $DB->data = $_POST;
    if ($DB->dbUpdate("userID='$userID'")) {
        setResponse(["err" => 0, "param" => "id=$userID"]);
    } else {
        setResponse(["err" => 1]);
    }
}

function resetUnauthorizedLeavesCnt($userID = 0){
    $data = array("err" => 1, "msg" => "");
    global $DB;
    $lData['unauthorized'] = 0;
    $DB->table = $DB->pre . "x_admin_user";
    $DB->data = $lData;
    if ($DB->dbUpdate("userID='$userID'")) {
        $data = array("err" => 0, "msg" => "Count reset successfully.");
    }
    return $data;
}

function validateUserPin($userData = []){
    global $DB,$MXMOD; 
    $data = array('err'=>1,'msg'=>"Something Went Wrong");
    $userID = $userData['userID'] ?? 0;
    $userPin = $userData['userPin'] ?? '';
    if(isset($userPin) && $userPin !=''){
        $data = array('err'=>0,'msg'=>"User Pin saved");
        $DB->vals = array($userID,$userPin,1);
        $DB->types = "iii";
        $DB->sql = "SELECT userPin FROM `" . $DB->pre ."x_admin_user` 
                    WHERE userID !=? AND userPin = ? AND status= ?";
        $DB->dbRows();
        if($DB->numRows > 0){
            $data = array('err'=>1,'msg'=>"User pin is already being used by another user.");
        }
    }
    return $data;
}

if (isset($_POST["xAction"])) {
    require_once("../../../../core/core.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
                addUser();
                break;
            case "UPDATE":
                updateUser();
                break;
            case "validateUserPin":
                $MXRES = validateUserPin($_POST);
                break;
            case "resetUnauthorizedLeaves":
                $MXRES = resetUnauthorizedLeavesCnt($_POST['userID']);
                break;
            case "mxDelFile":
                $param = array("dir" => "x_admin_user", "tbl" => "x_admin_user", "pk" => "userID");
                mxDelFile(array_merge($_REQUEST, $param));
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "x_admin_user", "PK" => "userID", "UDIR" => array("imageName" => "admin_user")));
}
