<?php
// error_reporting(1);
include("../../../core-admin/common.inc.php");
include("../../../inc/site.inc.php");

use Spipu\Html2Pdf\Html2Pdf;

$ABSINVDIR = UPLOADPATH . "/voucher/";
$URLINVDIR = SITEURL . "/uploads/voucher/";

function getPOPDFData($voucherID = 0)
{
	global $DB;
	$d = array();

	$DB->vals = array($voucherID);
	$DB->types = "i";
	$DB->sql = "SELECT * FROM `" . $DB->pre . "voucher` WHERE voucherID = ?";
	$d = $DB->dbRow();
	return $d;
}

function createPDFName($d = array())
{
	return $d["voucherDate"] . "-" . $d["voucherNo"] . "-" . makeSeoUri($d["voucherTitle"]);
}

function createPOPDF($d = array(), $action = "")
{
	global $DB, $ABSINVDIR, $URLINVDIR;

	$fileName = createPDFName($d);
	if (!file_exists($ABSINVDIR))
		mkdir($ABSINVDIR);

	$strI = file_get_contents(ADMINURL . "/mod/voucher/inc/x-invoice-priview.php?id=" . $d["voucherID"]);
	if (isset($_GET["type"]) && $_GET["type"] == 1  && isset($_GET["debug"])) {
		echo $strI;
		exit;
	}
	$fileName = str_replace("/", "-", $fileName);

	$htmlPath = $ABSINVDIR . "/html/" . $fileName . ".html";


	if (!file_put_contents($htmlPath, $strI)) {
		$flg = false;
	}

	$pdfPath = $ABSINVDIR . $fileName . ".pdf";

	$params = ["margin_left" => 15, "margin_right" => 8, "margin_top" => 8, "margin_bottom" => 8, 'mode' => 'utf-8', 'format' => 'A4', "default_font_size" => 12];
	// require_once(ROOTPATH . '/vendor/autoload.php');
	require_once (ROOTPATH . '/vendor/autoload.php');

	$mpdf = new \Mpdf\Mpdf($params);
	$mpdf->WriteHTML($strI);
	if (!isset($_GET["debug"])) {
		$mpdf->Output($pdfPath);
	} else {
		$mpdf->Output();
	}

	$fileName = $fileName . ".pdf";
	$arryIn = array("voucherFile" => $fileName);
	$DB->table = $DB->pre . "voucher";
	$DB->data = $arryIn;
	$DB->dbUpdate("voucherID=?", "i", array($d["voucherID"]));
	return $fileName;
}
