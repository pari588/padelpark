<?php
//Start: To save Add Payment Issue data.
function addPaymentIssue()
{
    global $DB;

    $res = calculateBalanceAmount('', $_POST["paymentDate"]);
    $res['balanceAmount'] = $res['creditAmount'] - $res['debitAmount'];
    $_POST['balanceAmount'] = ((float) $res['balanceAmount'] + (float) $_POST['amount']);


    $DB->table = $DB->pre . "payment_issue";
    $DB->data = $_POST;
    $result = checkAvailableBalanceAmount($_POST['amount']);
    $respMsg = $result["msg"];
    if ($result["count"] == 0) {
        if ($DB->dbInsert()) {
            $paymentIssueID = $DB->insertID;
            $arrPush = array(
                "paymentIssueID" => $paymentIssueID,
                "amount" => $_POST["amount"],
                "transactionType" => 1,
                "transactionDate" => $_POST["paymentDate"],
                "balanceAmount" => $_POST['balanceAmount'],
                "particulars" => $_POST["particulars"]
            );
            if ($paymentIssueID) {
                $DB->table = $DB->pre . "credit_debit";
                $DB->data = $arrPush;
                if ($DB->dbInsert()) {
                    updateCreditDebitBalance($_POST["paymentDate"], $_POST['balanceAmount']);

                    setResponse(array("err" => 0, "param" => "id=$paymentIssueID"));
                }
            }
        } else {
            setResponse(array("err" => 1));
        }
    } else {
        setResponse(array("err" => 1, "param" => "", "msg" => "$respMsg"));
    }
}
//End.

//Start: To update Payment Issue data.
function  updatePaymentIssue()
{
    global $DB;
    $paymentIssueID = intval($_POST["paymentIssueID"]);
    $DB->table = $DB->pre . "payment_issue";
    $DB->data = $_POST;
    $result = checkAvailableBalanceAmount($_POST['amount']);
    $respMsg = $result["msg"];
    if ($result["count"] == 0) {
        if ($DB->dbUpdate("paymentIssueID=?", "i", array($paymentIssueID))) {
            $arrPush = array(
                "amount" => $_POST["amount"],
                "particulars" => $_POST["particulars"],
                "transactionDate" => $_POST["paymentDate"],
            );
            $DB->table = $DB->pre . "credit_debit";
            $DB->data = $arrPush;
            $DB->dbUpdate("paymentIssueID=?", "i", array($paymentIssueID));
            $balanceAmount = updateCreditDebitBalance();
            setResponse(array("err" => 0, "param" => "id=$paymentIssueID"));
        } else {
            setResponse(array("err" => 1));
        }
    } else {
        setResponse(array("err" => 1, "param" => "", "msg" => "$respMsg"));
    }
}
//End.


function updateBalanceAfterTrash()
{
    global $DB, $MXRES;
    $response = array();
    $response['err'] = 1;
    $response['msg'] = "ERR";
    $paymentIssueIDs = $_POST['paymentIssueID'];
    $vals = explode(",", $paymentIssueIDs);
    // print_r($vals);exit;
    $paymentIssueID = min($vals) ?? 0;
    $status = $_POST['status'] ?? 0;
    $updatedBalanceAmount = $creditDebitAmount = 0;

    if (count($vals) > 0) {
        //updating status
        $inWhere = implode(",", array_fill(0, count($vals), "?"));
        $DB->vals = $vals;
        array_unshift($DB->vals, $status);
        $DB->types = implode("", array_fill(0, count($DB->vals), "i"));
        $DB->sql = "UPDATE " . $DB->pre . "credit_debit SET status=? WHERE paymentIssueID IN(" . $inWhere . ")";
        $DB->dbQuery();

        $inWhere = implode(",", array_fill(0, count($vals), "?"));
        $DB->vals = $vals;
        $DB->types = implode("", array_fill(0, count($DB->vals), "i"));
        $DB->sql = "SELECT creditDebitID,transactionDate FROM `" . $DB->pre . "credit_debit` WHERE paymentIssueID IN(" . $inWhere . ") ORDER BY transactionDate ASC";
        $creditDebitData = $DB->dbRow();



        //fetching data from credit_debit for updating amount
        $DB->vals = array($creditDebitData['transactionDate']);
        $DB->types = "s";
        $DB->sql = "SELECT creditDebitID,paymentIssueID,amount,transactionType,balanceAmount,transactionDate FROM `" . $DB->pre . "credit_debit` WHERE transactionDate >= ? ORDER BY transactionDate ASC";
        $creditDebitTrashData = $DB->dbRows();


        if ($DB->numRows > 0) {
            foreach ($creditDebitTrashData as $k => $v) {
                if (in_array($v['paymentIssueID'], $vals)) {
                    //calculating credit & debit amount
                    if ($status == 0) {
                        if ($v['transactionType'] == 1) {
                            $creditDebitAmount += $v['amount'];
                        } else if ($v['transactionType'] == 2) {
                            $creditDebitAmount -= $v['amount'];
                        }
                    } else {
                        if ($v['transactionType'] == 1) {
                            $creditDebitAmount -= $v['amount'];
                        } else if ($v['transactionType'] == 2) {
                            $creditDebitAmount += $v['amount'];
                        }
                    }
                }

                $updatedBalanceAmount = $v['balanceAmount'] - $creditDebitAmount;
                $DB->vals = array($updatedBalanceAmount, $v['creditDebitID']);
                $DB->types = "di";
                $DB->sql = "UPDATE `" . $DB->pre . "credit_debit` SET balanceAmount = ? WHERE creditDebitID = ?";
                $DB->dbQuery();
            }
            $response['err'] = 0;
            $response['msg'] = "OK";
        }
    }
    return $response;
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
                addPaymentIssue();
                break;
            case "UPDATE":
                updatePaymentIssue();
                break;
            case "updateBalanceAfterTrash":
                $MXRES = updateBalanceAfterTrash();
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "payment_issue", "PK" => "paymentIssueID"));
}
