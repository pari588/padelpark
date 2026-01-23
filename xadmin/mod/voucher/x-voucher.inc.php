<?php
/*
addVoucher = To save voucher data.
updateVoucher = To update voucher data.
addVoucherInPettyCash = Add voucher info into petty cash as debited amount.
*/
//Start: To save voucher data.
function addVoucher()
{
    global $DB;
    if (isset($_POST["voucherNo"]))
        $_POST["voucherNo"] = cleanTitle($_POST["voucherNo"]);
    if (isset($_POST["voucherDebitTo"]))
        $_POST["voucherDebitTo"] = cleanTitle($_POST["voucherDebitTo"]);
    if (isset($_POST["voucherDate"]))
        $_POST["voucherDate"] = $_POST["voucherDate"];
    if (isset($_POST["voucherDesc"]))
        $_POST["voucherDesc"] = trim($_POST["voucherDesc"]);

    $vData = $_POST;
    $response = addVoucherData($vData);
    if ($response['err'] == 0) {
        $voucherID = $response['voucherID'];
        addVoucherInPettyCash($voucherID);
    }
    setResponse($response);
}
//End.
//Start: To update voucher data.
function  updateVoucher()
{
    global $DB;
    $voucherID = intval($_POST["voucherID"]);
    if (isset($_POST["voucherNo"]))
        $_POST["voucherNo"] = cleanTitle($_POST["voucherNo"]);
    if (isset($_POST["voucherDate"]))
        $_POST["voucherDate"] = $_POST["voucherDate"];
    if (isset($_POST["voucherDebitTo"]))
        $_POST["voucherDebitTo"] = cleanTitle($_POST["voucherDebitTo"]);
    if (isset($_POST["voucherDesc"]))
        $_POST["voucherDesc"] = trim($_POST["voucherDesc"]);

    $_POST["voucherFile"] = $_POST["voucherDate"] . "-" . $_POST["voucherNo"] . "-" . makeSeoUri($_POST["voucherTitle"]);

    $DB->table = $DB->pre . "voucher";
    $DB->data = $_POST;

    if ($DB->dbUpdate("voucherID=?", "i", array($voucherID))) {
        if ($voucherID) {
            addVoucherInPettyCash($voucherID);
            setResponse(array("err" => 0, "param" => "id=$voucherID"));
        }
    } else {
        setResponse(array("err" => 1));
    }
}
//End.
//Start: Add voucher info into petty cash as debited amount.
function addVoucherInPettyCash($voucherID = 0)
{
    global $DB;
    if (intval($voucherID) > 0) {
        $arrInn = array(
            "voucherID" => $voucherID,
            "transactionType" => 2,
            "pettyCashNote" => $_POST["voucherDesc"],
            "amount" => $_POST["voucherAmt"],
            "balanceAmount" => $_POST["balanceAmount"],
            "pettyCashCatID" => $_POST["pettyCashCatID"],
            "transactionDate" => $_POST["voucherDate"],
            "paymentMode" => "Cash"
        );
        if ($_POST["xAction"] == "ADD") {
            $arrInn['balanceAmount'] = (float)($_POST['balanceAmount']) - (float) $_POST['voucherAmt'];
            $DB->table = $DB->pre . "petty_cash_book";
            $DB->data = $arrInn;
            $DB->dbInsert();
        } elseif ($_POST["xAction"] == "UPDATE") {
            $DB->table = $DB->pre . "petty_cash_book";
            $DB->data = $arrInn;
            $DB->dbUpdate("voucherID=?", "i", array($voucherID));
            updatePettycashBalance();
        }
    }
}
// End.
// Start: Creat voucher PDF into zip.
function creatVoucherZip()
{
    global $DB;
    $voucherIDArr = [];
    $voucherIDArr = explode(',', $_REQUEST["voucherID"]);
    if (sizeof($voucherIDArr) > 0) {
        $types = str_repeat("i", count($voucherIDArr));
        $inWhere = implode(",", array_fill(0, count($voucherIDArr), "?"));
        $DB->vals = $voucherIDArr;
        array_unshift($DB->vals, 1);
        $DB->types = "i" . $types;
        $DB->sql = "SELECT voucherFile FROM `" . $DB->pre . "voucher` WHERE status=? AND voucherID IN($inWhere)";
        $filesArr = $DB->dbRows();
        if ($DB->numRows > 0) {
            foreach ($filesArr as $key => $value) {
                $sourcePath = UPLOADPATH . "/voucher/" . $value['voucherFile'];
                $destinationPath = 'vocher-pdf/' . $value['voucherFile'];
                copy($sourcePath, $destinationPath);
            }
            $destinationArr = array();
            foreach ($filesArr as $key => $value) {
                array_push($destinationArr, "vocher-pdf/" . $value['voucherFile']);
            }
            $fileName = 'voucher.zip';
            createZipArchive($destinationArr, $fileName);
            header("Content-Disposition: attachment; filename=\"" . $fileName . "\"");
            header("Content-Length: " . filesize($fileName));
            if (readfile($fileName) == true) {
                unlink($fileName);
                $files = glob("vocher-pdf" . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        }
    }
}
// End.
// Start: Function to create zip using PDF.
function createZipArchive($files = array(), $destination = '', $overwrite = false)
{

    if (file_exists($destination) && !$overwrite) {
        return false;
    }

    $validFiles = array();
    if (is_array($files)) {
        foreach ($files as $file) {
            if (file_exists($file)) {
                $validFiles[] = $file;
            }
        }
    }

    if (count($validFiles)) {
        $zip = new ZipArchive();
        if ($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) == true) {
            foreach ($validFiles as $file) {
                $zip->addFile($file, $file);
            }
            $zip->close();
            return file_exists($destination);
        } else {
            return false;
        }
    } else {
        return false;
    }
}
// End.
$_POST = array_merge($_POST, $_GET);
if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest(true, true);
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
                addVoucher();
                break;
            case "UPDATE":
                updateVoucher();
                break;
            case "creatVoucherZip":
                $MXRES =  creatVoucherZip();
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "voucher", "PK" => "voucherID"));
}
