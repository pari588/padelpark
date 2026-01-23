<?php
include("../../../../core/core.inc.php");
include("voucher-print.inc.php");

$arrIID = explode(",", $_GET["id"]);
$arrDown = array();
foreach ($arrIID as $voucherID) {
    $d = getPOPDFData($voucherID);
    if ($_GET["xAction"] == "down") {
        $arrDown[] = createPDFName($d);
    } else {
        $invoiceFile = "";
        if ($d["voucherFile"] != '' && $_GET['action'] != 'preview') {
            if (file_exists($ABSINVDIR . $d["voucherFile"]) && is_file(($ABSINVDIR . $d["voucherFile"]))) {
                $invoiceFile = $d["voucherFile"];
            }
        }
        if ($invoiceFile == "") {
            $invoiceFile = createPOPDF($d, $_GET['action']);
        }
    }
}


if ($_GET["xAction"] == "view") {
    $URLINVDIR = $URLINVDIR ;
    header("location:" . $URLINVDIR . $invoiceFile);
    exit;
} else if ($_GET["xAction"] == "down") {
    if (count($arrDown) > 0) {
        $zip = new ZipArchive();
        $zipName = date("Ymd-His");
        $zip->open($ABSINVDIR . $zipName . ".zip", ZipArchive::CREATE);
        foreach ($arrDown as $file) {
            if (file_exists($ABSINVDIR . $file)) {
                $zip->addFile($ABSINVDIR . $file, $file);
            }
        }
        $zip->close();
        header("Location:" . $URLINVDIR . $zipName . ".zip");
    }
} else if ($_GET["xAction"] == "check") {
    echo "OK";
}
