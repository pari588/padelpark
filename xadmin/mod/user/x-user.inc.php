<?php

function addUser()
{

    global $DB;
    $DB->types = "i";
    $DB->vals = array($_POST["userLoginOTP"]);
    $DB->sql = "SELECT userLoginOTP FROM `" . $DB->pre . "user` WHERE userLoginOTP=? " . mxWhere();
    $DB->dbQuery();
    if ($DB->numRows > 0) {
        setResponse(["err" => 1, "msg" => "Login OTP already exists", "alert" => "Login OTP already exists"]);
    } else {
        $DB->table = $DB->pre . "user";
        $DB->data = $_POST;
        if ($DB->dbInsert()) {
            $userID = $DB->insertID;
            addUserOffDays($userID);
            setResponse(["err" => 0, "param" => "id=$userID"]);
        } else {
            setResponse(["err" => 1]);
        }
    }
}

function updateUser()
{
    global $DB, $MXRES;
    $userID = intval($_POST["userID"]);

    $DB->types = "ii";
    $DB->vals = array($userID, $_POST["userLoginOTP"]);
    $DB->sql = "SELECT userLoginOTP FROM `" . $DB->pre . "user` WHERE userID <> ?   AND userLoginOTP=? " . mxWhere();
    $DB->dbQuery();
    if ($DB->numRows > 0) {
        setResponse(["err" => 1, "msg" => "Login OTP already exists", "alert" => "Login OTP already exists"]);
    } else {

        $DB->table = $DB->pre . "user";
        $DB->data = $_POST;
        if ($DB->dbUpdate("userID='$userID'")) {
            //To delete existing user Off days data.
            $DB->vals = array($userID);
            $DB->types = "i";
            $DB->sql = "DELETE FROM " . $DB->pre . "user_off_days WHERE userID=?";
            $DB->dbQuery();
            // End.
            addUserOffDays($userID);
            setResponse(["err" => 0, "param" => "id=$userID"]);
        } else {
            setResponse(["err" => 1]);
        }
    }
}

function addUserOffDays($userID = 0)
{
    global $DB;
    if ($userID) {
        if (isset($_POST["offDays"]) && count($_POST["offDays"]) > 0) {
            foreach ($_POST["offDays"] as $k => $v) {
                $arrIn = array(
                    "userID" => $userID,
                    "weekdayNo" => $v
                );
                $DB->table = $DB->pre . "user_off_days";
                $DB->data = $arrIn;
                $DB->dbInsert();
            }
        }
    }
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
                addUser();
                break;
            case "UPDATE":
                updateUser();
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    setModVars(array("TBL" => "user", "PK" => "userID"));
}
