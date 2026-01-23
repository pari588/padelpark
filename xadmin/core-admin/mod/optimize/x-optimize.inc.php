<?php
function mxArrayIntersect($arrayOne, $arrayTwo)
{
    $arr = array();
    if (is_array($arrayOne)) {
        $index = array_flip($arrayOne);
        if (is_array($arrayTwo))
            $second = array_flip($arrayTwo);
        $x = array_intersect_key($index, $second);
        $arr = array_flip($x);
    }
    return $arr;
}

function mxArrayDiff($b, $a)
{
    $at = array_flip($a);
    $d = array();
    foreach ($b as $i)
        if (!isset($at[$i]))
            $d[] = $i;
    return $d;
}

function removeDir($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir")
                    rmdir($dir . "/" . $object);
                else
                    unlink($dir . "/" . $object);
            }
        }
        reset($objects);
        if (rmdir($dir)) {
            return true;
        }
    }
    return false;
}

function delTmpDir()
{
    global $MXRES;
    $dirPath = UPLOADPATH;
    $myscan = scandir($dirPath);
    $cnt = 0;
    $cntT = 0;
    foreach ($myscan as $entry) {
        $tmpPath = $dirPath . '/' . $entry . "/tmp";
        if (is_dir($tmpPath)) {
            $cntT++;
            if (removeDir($tmpPath)) {
                $cnt++;
            }
        }
    }
    $MXRES["msg"] = "$cnt of $cntT  tmp folders deleted....";
}

function scanUploadDir($dirName, $arrDir = array())
{
    $dirPath = UPLOADPATH . "/" . $dirName;
    $myscan = scandir($dirPath);
    foreach ($myscan as $entry) {
        if ($entry != "setting") {
            if (($entry == '.' || $entry == '..' || $entry == '.DS_Store' || $entry == ".htaccess")) {
                if (!strpos($dirName, "/tmp") && $dirName != "") {
                    $arrDir[$dirName]["size"] = 0.00;
                    $arrDir[$dirName]["count"] = 0;
                }
                continue;
            } else if (is_dir($dirPath . '/' . $entry) && $entry != "tmp") {
                $arrDir = scanUploadDir($dirName . '/' . $entry, $arrDir);
            } else if (is_file($dirPath . '/' . $entry)) {
                if (!isset($arrDir[$dirName]["size"]))
                    $arrDir[$dirName]["size"] = 0;

                if (!isset($arrDir[$dirName]["count"]))
                    $arrDir[$dirName]["count"] = 0;

                $arrDir[$dirName]["size"] = $arrDir[$dirName]["size"] + filesize($dirPath . '/' . $entry);
                $arrDir[$dirName]["count"] = $arrDir[$dirName]["count"] + 1;
            }
        }
    }
    return $arrDir;
}

function getFilesInDir($dirName, $arrFiles = array())
{
    $dirPath = UPLOADPATH . "/" . $dirName;
    $myscan = scandir($dirPath);
    $arrFiles = array();
    foreach ($myscan as $entry) {
        if (!in_array($entry, array(".", "..", ".htaccess", "tmp"))) {
            $arrFiles["files"][] = $entry;
            if (isset($arrFiles["size"]))
                $arrFiles["size"] = $arrFiles["size"] + filesize($dirPath . '/' . $entry);
        }
    }
    return $arrFiles;
}

function getFilesInTable($tableName = "", $fieldNames = array(), $onlyCnt = false)
{
    global $DB, $MXRES;
    $MXRES["err"] = 0;
    $MXRES["data"] = "";
    $arrImages = array();
    $count = 0;
    if (isset($fieldNames) && count($fieldNames) > 0 && isset($tableName) && $tableName != "") {
        $strFields = implode("`,`", $fieldNames);
        $where = "";
        $or = "";
        foreach ($fieldNames as $f) {
            $where .= " $or`$f` NOT IN ('')";
            $or = "OR ";
        }
        $DB->sql = "SELECT `$strFields` FROM `$tableName` WHERE ($where)";
        $DB->dbRows();
        if ($DB->numRows > 0) {
            foreach ($DB->rows as $d) {
                foreach ($fieldNames as $f) {
                    if (isset($d[$f]) && trim($d[$f]) != "") {
                        if ($onlyCnt) {
                            $count++;
                        } else {
                            $arrImages[] = $d[$f];
                        }
                    }
                }
            }
        }
    }
    return array("files" => $arrImages, "count" => $count);
}

function optimizeFolder()
{
    global $MXRES;
    $MXRES["err"] = 1;
    $MXRES["data"] = "";
    $MXRES["msg"] = "No files to delete.";
    $dirName = trim($_POST["dirName"]);
    $cntDr = $cntDel = $cntDb = $sizeDel = 0;
    $sizeDir = 0;
    removeDir(UPLOADPATH . "/" . $dirName . "/tmp");
    if (isset($dirName) && $dirName != "") {
        $tableName = trim($_POST["tableName"]);
        $fieldNames = $_POST["fieldNames"];
        $arrFilesT = getFilesInTable($tableName, $fieldNames);
        $cntDb = count($arrFilesT["files"]);
        if ($cntDb > 0) {
            $arrFilesD = getFilesInDir($dirName);
            if (isset($arrFilesD["size"]))
                $sizeDir = $arrFilesD["size"];

            $arrIntsect = array();
            if (isset($arrFilesD["files"]) && isset($arrFilesT["files"]))
                $arrIntsect = mxArrayIntersect($arrFilesD["files"], $arrFilesT["files"]);
            if (count($arrIntsect) > 0) {
                $arrDel = mxArrayDiff($arrFilesD["files"], $arrFilesT["files"]);
                $cntDr = count($arrFilesD["files"]);
                $cntDel = count($arrDel);
                if ($cntDel > 0) {
                    $cntD = 0;
                    $cntF = 0;
                    $dirPath = UPLOADPATH . "/" . $dirName;
                    foreach ($arrDel as $file) {
                        if (is_file($dirPath . "/" . $file)) {
                            $sizeF = filesize($dirPath . '/' . $file);
                            if (unlink($dirPath . "/" . $file)) {
                                $sizeDel = $sizeDel + $sizeF;
                                $cntD++;
                            } else {
                                $cntF++;
                            }
                        }
                    }
                    $cntDel = $cntD;
                    $MXRES["msg"] = "($cntD) Files  deleted<br>($cntF) Files failed to delete";
                } else {
                    $MXRES["msg"] = "Sorry! No files to delete";
                }
            } else {
                $MXRES["msg"] = "Sorry! There is no matching files in table and directory.<br> You can empty the directory manually";
            }
        } else {
            $MXRES["msg"] = "Sorry! This table columns have no file names.<br> You can empty the directory manually";
        }
    }
    return array("dbCount" => $cntDb, "dirCount" => ($cntDr - $cntDel), "dirSize" => ($sizeDir - $sizeDel));
}

function getTableFieldList($tableName = "")
{
    global $DB, $MXRES;
    $str = "";
    $arrFields = array();
    $MXRES["err"] = 0;
    $MXRES["data"] = "";
    $arrSelected = array("img", "image", "pic", "thumb", "photo", "file", "picture", "media");
    $arrSkip = array("langCode", "langChild", "seoUri");
    $DB->sql = "DESCRIBE " . $tableName;
    $DB->dbRows();
    foreach ($DB->rows as $d) {
        $chkd = "";
        foreach ($arrSelected as $imgFld) {
            if (strpos(strtolower($d["Field"]), $imgFld) !== false) {
                $chkd = ' checked="checked"';
                $arrFields[] = $d["Field"];
            }
        }
        if ((strpos(strtolower($d["Type"]), "varchar") !== false || strpos(strtolower($d["Type"]), "text") !== false) && !in_array($d["Field"], $arrSkip)) {
            $str .= '<li><i class="chk"><input type="checkbox" name="fieldNames[]" value="' . $d["Field"] . '"' . $chkd . ' /><em></em></i> ' . $d["Field"] . '</li>';
        }
    }
    return array("html" => $str, "fields" => $arrFields, "count" => getFilesInTable($tableName, $arrFields, true));
}

//$_POST = $_GET;
if (isset($_POST["xAction"])) {
    require_once("../../../../core/core.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "getTableFieldList":
                $tableName = trim($_POST["tableName"]);
                $MXRES["data"] = getTableFieldList($tableName);
                break;
            case "getFilesInTable":
                $tableName = trim($_POST["tableName"]);
                $fieldNames = $_POST["fieldNames"];
                $MXRES["data"] = getFilesInTable($tableName, $fieldNames, true);
                break;
            case "optimizeFolder":
                $MXRES["data"] = optimizeFolder();
                break;
            case "delTmpDir":
                delTmpDir();
                break;
        }
    }
    echo json_encode($MXRES);
}
