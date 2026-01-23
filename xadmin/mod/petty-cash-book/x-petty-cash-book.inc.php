<?php
/*
addPettyCashBook = To save petty cash book data.
updatePettyCashBook = To update petty cash book data.
balanceAmountcheck = To check availabale balance amount.
*/
//Start: To save petty cash book data.
function addPettyCashB()
{
    $response = addPettyCashBook($_POST);
    setResponse($response);
}
//End.
//Start: To update petty cash book data.
function  updatePettyCashBook()
{
    global $DB;
    $pettyCashBookID = intval($_POST["pettyCashBookID"]);
    $_POST["doc1"] = mxGetFileName("doc1");
    $_POST["doc2"] = mxGetFileName("doc2");
    $_POST["doc3"] = mxGetFileName("doc3");
    $_POST["doc4"] = mxGetFileName("doc4");
    $_POST["doc5"] = mxGetFileName("doc5");
    $DB->table = $DB->pre . "petty_cash_book";
    $DB->data = $_POST;
    $result = balanceAmountcheck($_POST['amount']);
    $respMsg = $result["msg"];
    if ($result["count"] == 0) {
        if ($DB->dbUpdate("pettyCashBookID=?", "i", array($pettyCashBookID))) {
            updatePettycashBalance();
            if ($pettyCashBookID) {
                setResponse(array("err" => 0, "param" => "id=$pettyCashBookID"));
            }
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
    $pettyCashBookIDs = $_POST['pettyCashBookID'];
    $vals = explode(",", $pettyCashBookIDs);
    $pettyCashBookID = min($vals) ?? 0;

    $status = $_POST['status'] ?? 0;
    $updatedBalanceAmount = $creditDebitAmount = 0;

    $DB->vals = array($pettyCashBookID);
    $DB->types = "i";
    $DB->sql = "SELECT pettyCashBookID,amount,transactionType,balanceAmount FROM `" . $DB->pre . "petty_cash_book` WHERE pettyCashBookID >= ?";
    $pettyCashBookTrashData = $DB->dbRows();
    if ($DB->numRows > 0) {
        foreach ($pettyCashBookTrashData as $k => $v) {
            if (in_array($v['pettyCashBookID'], $vals)) {
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
            $DB->vals = array($updatedBalanceAmount, $v['pettyCashBookID']);
            $DB->types = "di";
            $DB->sql = "UPDATE `" . $DB->pre . "petty_cash_book` SET balanceAmount = ? WHERE pettyCashBookID = ?";
            $DB->dbQuery();
        }
        $response['err'] = 0;
        $response['msg'] = "OK";
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
                addPettyCashB();
                break;
            case "UPDATE":
                updatePettyCashBook();
                break;
            case "updateBalanceAfterTrash":
                $MXRES = updateBalanceAfterTrash();
                break;
            case "mxDelFile":
                $param = array("dir" => "petty-cash-book", "tbl" => "petty_cash_book", "pk" => "pettyCashBookID");
                mxDelFile(array_merge($_REQUEST, $param));
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "petty_cash_book", "PK" => "pettyCashBookID", "UDIR" => array("doc1" => "petty-cash-book")));
}
