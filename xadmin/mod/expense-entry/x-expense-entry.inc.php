<?php

//Start: To save Expense data.
function addExpense()
{
    global $DB;
    $_POST['transactionType'] = 2;
    $amount = 0;
    if (isset($_POST['amount']) && count($_POST['amount']) > 0) {
        foreach ($_POST['amount'] as $k => $v) {
            $amount += $v;
        }
    }
    $result = checkAvailableBalanceAmount($amount);
    $respMsg = $result["msg"];
    if ($result["count"] == 0) {
        addExpenseDetail();
        setResponse(array("err" => 0, "param" => "", "rurl" => SITEURL . "/xadmin/expense-entry-list/"));
    } else {
        setResponse(array("err" => 1, "param" => "", "msg" => "$respMsg"));
    }
}
//End.

//Start: To update Expense data.
function  updateExpense()
{
    global $DB;
    $DB->table = $DB->pre . "expense_entry";
    $DB->data = $_POST;
    $amount = 0;
    if (isset($_POST['amount']) && count($_POST['amount']) > 0) {
        foreach ($_POST['amount'] as $k => $v) {
            $amount += $v;
        }
    }

    $result = checkAvailableBalanceAmount($amount);
    $respMsg = $result["msg"];
    if ($result["count"] == 0) {
        if (isset($_POST["expenseEntryDate"]) && count($_POST["expenseEntryDate"]) > 0) {
            for ($i = 0; $i < count($_POST["expenseEntryDate"]); $i++) {
                $arrIn = array(
                    "expenseEntryDate" => $_POST["expenseEntryDate"][$i],
                    "expenseTypeID" => intval($_POST["expenseTypeID"][$i]),
                    "amount" => $_POST["amount"][$i],
                    "particulars" => $_POST["particulars"][$i]
                );
                $arrIn["fileAttachment"] = mxGetFileName("fileAttachment", $i);
                $DB->table = $DB->pre . "expense_entry";
                $DB->data = $arrIn;
                $DB->dbUpdate("expenseEntryID=?", "i", array($_POST["expenseEntryID"]));

                $res = calculateBalanceAmount();
                $res['balanceAmount'] = $res['creditAmount'] - $res['debitAmount'];

                $arrPush = array(
                    "expenseEntryID" => $_POST["expenseEntryID"],
                    "amount" =>  $_POST["amount"][$i],
                    "transactionType" => 2,
                    "transactionDate" => $_POST["expenseEntryDate"][$i],
                    "balanceAmount" => $res['balanceAmount'],
                    "particulars" => $_POST["particulars"][$i]
                );

                $DB->table = $DB->pre . "credit_debit";
                $DB->data = $arrPush;
                $DB->dbUpdate("creditDebitID=?", "i", array($_POST["creditDebitID"][$i]));
            }
        }
        $balanceAmount = updateCreditDebitBalance();
        setResponse(array("err" => 0, "param" => "id=" . $_POST['expenseEntryID']));
    } else {
        setResponse(array("err" => 1, "param" => "", "msg" => "$respMsg"));
    }
}
//End.
//Start: To  Add and Update Expense Details data.
function addExpenseDetail()
{
    global $DB;
    $totalExpenseAmount = 0;

    if (isset($_POST["expenseEntryID"]) && count($_POST["expenseEntryID"]) > 0) {
        for ($i = 0; $i < count($_POST["expenseEntryID"]); $i++) {

            $res = calculateBalanceAmount('', $_POST["expenseEntryDate"][$i]);
            $_POST['balanceAmount'] = $res['creditAmount'] - $res['debitAmount'];

            $arrIn = array(
                // "creditDebitID" => $creditDebitID,
                // "expenseEntryID" => $expenseEntryID,
                "expenseEntryDate" => $_POST["expenseEntryDate"][$i],
                "expenseTypeID" => intval($_POST["expenseTypeID"][$i]),
                "amount" => $_POST["amount"][$i],
                "particulars" => $_POST["particulars"][$i]
            );
            $arrIn["fileAttachment"] = mxGetFileName("fileAttachment", $i);
            $DB->table = $DB->pre . "expense_entry";
            $DB->data = $arrIn;
            if ($DB->dbInsert()) {
                $expenseEntryID = $DB->insertID;
                $_POST['balanceAmount'] -= $_POST["amount"][$i];
                $arrPush = array(
                    "expenseEntryID" => $expenseEntryID,
                    "amount" =>  $_POST["amount"][$i],
                    "transactionType" => 2,
                    "transactionDate" => $_POST["expenseEntryDate"][$i],
                    "balanceAmount" => $_POST['balanceAmount'],
                    "particulars" => $_POST["particulars"][$i]
                );
                $creditDebitID = intval($_POST["creditDebitID"][$i]);
                if ($creditDebitID > 0) {
                    $arrPush['creditDebitID'] = $creditDebitID;
                }
                $DB->table = $DB->pre . "credit_debit";
                $DB->data = $arrPush;
                if ($DB->dbInsert()) {
                    $creditDebitInsertID = $DB->insertID;

                    $DB->table = $DB->pre . "expense_entry";
                    $DB->data = array("creditDebitID" => $creditDebitInsertID);
                    $DB->dbUpdate("expenseEntryID=?", "i", array($expenseEntryID));
                }
                updateCreditDebitBalance($_POST["expenseEntryDate"][$i], $_POST['balanceAmount']);
            }
        }
    }
}
//End
function updateBalanceAfterTrash()
{
    global $DB, $MXRES;
    $response = array();
    $response['err'] = 1;
    $response['msg'] = "ERR";
    $expenseEntryIDs = $_POST['expenseEntryID'];
    $vals = explode(",", $expenseEntryIDs);
    // print_r($vals);exit;
    $expenseEntryID = min($vals) ?? 0;
    $status = $_POST['status'] ?? 0;
    $updatedBalanceAmount = $creditDebitAmount = 0;

    if (count($vals) > 0) {
        //updating status
        $inWhere = implode(",", array_fill(0, count($vals), "?"));
        $DB->vals = $vals;
        array_unshift($DB->vals, $status);
        $DB->types = implode("", array_fill(0, count($DB->vals), "i"));
        $DB->sql = "UPDATE " . $DB->pre . "credit_debit SET status=? WHERE expenseEntryID IN(" . $inWhere . ")";
        $DB->dbQuery();

        $inWhere = implode(",", array_fill(0, count($vals), "?"));
        $DB->vals = $vals;
        // $DB->vals = array($expenseEntryID);
        $DB->types = implode("", array_fill(0, count($DB->vals), "i"));
        $DB->sql = "SELECT creditDebitID,transactionDate FROM `" . $DB->pre . "credit_debit` WHERE expenseEntryID IN(" . $inWhere . ") ORDER BY transactionDate ASC";
        $creditDebitData = $DB->dbRow();

        //fetching data from credit_debit for updating amount
        $DB->vals = array($creditDebitData['transactionDate']);
        $DB->types = "i";
        $DB->sql = "SELECT creditDebitID,expenseEntryID,amount,transactionType,balanceAmount FROM `" . $DB->pre . "credit_debit` WHERE transactionDate >= ? ORDER BY transactionDate ASC";
        $creditDebitTrashData = $DB->dbRows();
        if ($DB->numRows > 0) {
            foreach ($creditDebitTrashData as $k => $v) {
                if (in_array($v['expenseEntryID'], $vals)) {
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

function fetchExpenseImages()
{
    global $DB, $MXRES;
    $response = array();
    $response['err'] = 1;
    $response['msg'] = "ERR";
    $response['str'] = "";
    $expenseEntryID = $_POST['expenseEntryID'];
    if ($expenseEntryID > 0) {

        $DB->vals = array(1, $expenseEntryID);
        $DB->types = "ii";
        $DB->sql = "SELECT ED.* FROM `" . $DB->pre . "expense_entry` AS ED
        WHERE ED.status=? AND ED.expenseEntryID = ?";
        $expenseImageData = $DB->dbRows();
        // print_r($expenseImageData)
        if ($DB->numRows > 0) {

            $response['str'] = ' <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
            <thead>
            <tr>
            <th width="1%" align="left">#ID</th>
            <th width="15%" align="left">File Name</th>
            <th width="4%" align="center">View</th>
            </tr>
            </thead> <tbody>';
            $str = $strView  = '';
            $srNo = 1;
            foreach ($expenseImageData as $expenseDetails) {
                // print_r($expenseDetails['fileAttachment']);
                $response['err'] = 0;
                if ($expenseDetails['fileAttachment'] != '') {

                    $file_path = $expenseDetails['fileAttachment'];
                    $extension = pathinfo($file_path, PATHINFO_EXTENSION);
                    if ($extension === 'pdf') {
                        $strView = '<a class="btn" href=' . UPLOADURL . '/expense-entry/' . $expenseDetails['fileAttachment'] . ' target="_blank">Click Here</a>';
                    } else {
                        $strView = '<a class="btn popup-gallery" rel="grp1234" href="' . UPLOADURL . '/expense-entry/' . $expenseDetails['fileAttachment'] . '" title="' . $expenseDetails['fileAttachment'] . '">
						Click Here  
						</a>';
                    }


                    $str .= '
                <tr class="appoint-row">
                <td>' . $srNo  . '</td>
                <td align="left"><div class="product-details__img">' . $expenseDetails['fileAttachment'] . '</td>
                <td align="center">' . $strView . '
                
                </td>
                </tr>';

                    $srNo++;
                }
            }

            if ($str == '') {
                $response['str'] = "No Record Found";
            } else {
                $response['str'] .= $str;
            }
        } else {
            $response['str'] = "No Record Found";
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
                addExpense();
                break;
            case "UPDATE":
                updateExpense();
                break;
            case "mxDelFile":
                $param = array("dir" => "expense-entry", "tbl" => "expense_entry", "pk" => "expenseEntryID");
                mxDelFile(array_merge($_REQUEST, $param));
                break;
            case "updateBalanceAfterTrash":
                $MXRES = updateBalanceAfterTrash();
                break;
            case "fetchExpenseImages":
                $MXRES = fetchExpenseImages();
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "expense_entry", "PK" => "expenseEntryID", "UDIR" => array("expense_entry" => "expense-entry")));
}
