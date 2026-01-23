<?php
//Start: To save Add Expense Type data || Pramod Badgujar || 19 March 2024
function addExpenseType()
{
    global $DB;
    $DB->table = $DB->pre . "expense_type";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $expenseTypeID = $DB->insertID;
        if ($expenseTypeID) {
            setResponse(array("err" => 0, "param" => "id=$expenseTypeID"));
        }
    } else {
        setResponse(array("err" => 1));
    }
}
//End.

//Start: To update Expense Type data || Pramod Badgujar || 19 March 2024
function  updateExpenseType()
{
    global $DB;
    $expenseTypeID = intval($_POST["expenseTypeID"]);
    $_POST['dateModified']=date("Y-m-d H:i:s");      
    $DB->table = $DB->pre . "expense_type";
    $DB->data = $_POST;
    if ($DB->dbUpdate("expenseTypeID=?", "i", array($expenseTypeID))) {
        if ($expenseTypeID) {
            setResponse(array("err" => 0, "param" => "id=$expenseTypeID"));
        }
    } else {
        setResponse(array("err" => 1));
    }
}
//End.



if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
                addExpenseType();
                break;
            case "UPDATE":
                updateExpenseType();
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "expense_type", "PK" => "expenseTypeID"));
}
