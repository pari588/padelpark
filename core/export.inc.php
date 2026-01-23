<?php
//https://www.the-art-of-web.com/php/dataexport/#comments
function cleanExport(&$str)
{
    // escape tab characters
    $str = preg_replace("/\t/", "\\t", $str);
    // escape new lines
    $str = preg_replace("/\r?\n/", "\\n", $str);
    // convert 't' and 'f' to boolean values
    if ($str == 't') $str = 'TRUE';
    if ($str == 'f') $str = 'FALSE';
    // force certain number/date formats to be imported as strings
    if (preg_match("/^0/", $str) || preg_match("/^\+?\d{8,}$/", $str) || preg_match("/^\d{4}.\d{1,2}.\d{1,2}/", $str)) {
        $str = "'$str";
    }
    // escape fields that include double quotes
    if (strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
}

function cleanExportCSV(&$str)
{
    $str = mb_convert_encoding($str, 'UTF-16LE', 'UTF-8');
}

function getExportData()
{
    $arrD = array();
    if (isset($_GET["modName"]) && trim($_GET["modName"]) != "") {
        $modName = trim($_GET["modName"]);
        if (isset($_SESSION[SITEURL][$modName]["EXPCOLS"]) && isset($_SESSION[SITEURL][$modName]["EXPCOLS"])) {
            global $DB;
            $arrCol = $_SESSION[SITEURL][$modName]["EXPCOLS"];
            $arrQry = $_SESSION[SITEURL][$modName]["EXPSQL"];
            $strCols =  implode(",", array_keys($arrCol));
            $pos = strpos(strtolower($arrQry["sql"]), "from", 0);
            $limit = "";
            if (isset($_GET["offset"]) && trim($_GET["offset"]) >= 0 && isset($_GET["showrec"]) && trim($_GET["showrec"]) > 0) {
                $limit = " LIMIT " . intval($_GET["offset"]) . ", " . intval($_GET["showrec"]);
            }

            $DB->sql = "SELECT $strCols " . substr($arrQry["sql"], $pos, strlen($arrQry["sql"])) . $limit;
            $DB->vals = $arrQry["vals"];
            $DB->types = $arrQry["types"];
            $DB->dbRows();
            if ($DB->numRows > 0) {
                
                // START : CUSTOM CODE FOR SERIAL NUMBER IN EXPORT FILE
                $colData = array_slice($arrCol, 0, 0, true) + array("srNo" => " Sr No" ) + array_slice($arrCol, 0, count($arrCol)-0, true);
                $srNo = 0;
                foreach($DB->rows AS $k=>$v){
                    $resultData[$k] = array_slice($v, 0, 0, true) + array("srNo" => ++$srNo ) + array_slice($v, 0, count($v)-0, true);
                }
                // END : CUSTOM CODE FOR SERIAL NUMBER IN EXPORT FILE
          
                $arrD["data"] = $resultData;
                $arrD["cols"] = $colData;
            }
        }
    }

    return $arrD;
}

function exportXLS()
{
    $data = getExportData();
    if (count($data) > 0) {
        $fileName =  $_REQUEST["modName"] . "-" . date('YmdHis') . ".xls";
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=$fileName");
        echo implode("\t", array_values($data["cols"])) . "\r\n";
        foreach ($data["data"] as $d) {
            array_walk($d, __NAMESPACE__ . '\cleanExport');
            echo implode("\t", array_values($d)) . "\r\n";
        }
        exit;
    }
}

function exportCSV()
{
    $data = getExportData();
    if (count($data) > 0) {
        $fileName =  $_REQUEST["modName"] . "-" . date('YmdHis') . ".csv";
        header("Content-Type: text/csv; charset=UTF-16LE");
        header("Content-Disposition: attachment; filename=$fileName");
        $out = fopen("php://output", 'w');
        fputcsv($out, array_values($data["cols"]), ',', '"');
        foreach ($data["data"] as $d) {
            array_walk($d, __NAMESPACE__ . '\cleanExport');
            array_walk($d, __NAMESPACE__ . '\cleanExportCSV');
            fputcsv($out, array_values($d), ',', '"');
        }
        fclose($out);
        exit;
    }
}

function exportXLSX()
{
    $data = getExportData();
    if (count($data) > 0) {
        require("../lib/import-export/xlsxwriter.class.php");
        $fileName =  $_REQUEST["modName"] . "-" . date('YmdHis') . ".xlsx";

        header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($fileName) . '"');
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        $writer = new XLSXWriter();
        $writer->setAuthor('Some Author');
        $writer->writeSheetRow($_REQUEST["modName"], $data["cols"]);
        foreach ($data["data"] as $row)
            $writer->writeSheetRow($_REQUEST["modName"], $row);
        $writer->writeToStdOut();
    }
}




if (isset($_REQUEST["xAction"])) {
    require_once("core.inc.php");
    $chkLogin = false;
    if ($_REQUEST["xAction"] == "xLogin")
        $chkLogin = false;
    $MXRES = mxCheckRequest($chkLogin,true);

    if (isset($_REQUEST["modName"]))
        $_REQUEST["modName"] = makeSeoUri($_REQUEST["modName"]);
        
    if ($MXRES["err"] == 0) {
        switch ($_REQUEST["xAction"]) {
            case "exportXLSX":
                exportXLSX();
                break;
            case "exportCSV":
                exportCSV();
                break;
            case "exportXLS":
                exportXLS();
                break;
        }
    } else {
        echo json_encode($MXRES);
    }
}
