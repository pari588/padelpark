<?php
function mxDelFile($params = array()) {
    global $MXRES;
    $MXRES = array("err" => 1, "msg" => "Cannot delete file something went wrong.", "data" => array(), "rurl" => "");
    if (count($params) > 0) {
        extract($params);
        if (mxDeleteFile(UPLOADPATH . "/" . $dir . "/", $fl)) {
            global $DB;
            $DB->vals = array($id);
            $DB->types = "i";
            $DB->sql = "SELECT $fld FROM `" . $DB->pre . "$tbl` WHERE `$pk`=?";
            $d = $DB->dbRow();
            if ($DB->numRows > 0) {
                $arrFile = explode(",", $d["$fld"]);
                if (( $key = array_search($fl, $arrFile) ) !== false) {
                    unset($arrFile[$key]);
                }
                $DB->vals = array(implode(",", $arrFile), $id);
                $DB->types = "si";
                $DB->sql = "UPDATE `" . $DB->pre . "$tbl` SET `$fld`=? WHERE `$pk`=?";
                if ($DB->dbQuery()) {
                    $MXRES["err"] = 0;
                    $MXRES["msg"] = "File deleted successfully";
                }
            }
        }
    }
    return $MXRES;
}

function mxGetFileName($fldName = "", $k = -1, $pgType = "") {
    $fileName = "";
    if (isset($fldName) && trim($fldName) != "") {
        global $MXRES, $MXPGTYPE;
        if ($MXPGTYPE != "")
            $pgType = $MXPGTYPE;
        if ($pgType == "edit") {
            $arrFilesO = array();
            $arrFilesN = array();
            if ($k >= 0) {
                if (isset($MXRES["data"]["fileName"][$fldName][$k])) {
                    $arrFilesN = $MXRES["data"]["fileName"][$fldName][$k];
                }

                if (isset($_POST["O" . $fldName][$k]) && count($_POST["O" . $fldName][$k]) > 0) {
                    $arrFilesO = $_POST["O" . $fldName][$k];
                }
            } else {
                if (isset($MXRES["data"]["fileName"][$fldName])) {
                    $arrFilesN = $MXRES["data"]["fileName"][$fldName];
                }
                if (isset($_POST["O" . $fldName]) && count($_POST["O" . $fldName]) > 0) {
                    $arrFilesO = $_POST["O" . $fldName];
                }
            }

            if (isset($arrFilesO) && is_array($arrFilesO) && isset($arrFilesN) && is_array($arrFilesN))
                $fileName = implode(',', array_merge(array_values($arrFilesO), array_values($arrFilesN)));
        } else if ($pgType == "add") {
            if ($k >= 0) {
                if (isset($MXRES["data"]["fileName"][$fldName][$k])) {
                    $fileName = implode(',', $MXRES["data"]["fileName"][$fldName][$k]);
                }
            } else {
                if (isset($MXRES["data"]["fileName"][$fldName])) {
                    $fileName = implode(',', $MXRES["data"]["fileName"][$fldName]);
                }
            }
        }
    }
    return $fileName;
}

function mxUniqueFileName($fileDir = "", $fileName="") {
    $count = 0;
    $info = pathinfo($fileName);
    $fileNmNoEx = $info["filename"];
    $fileEx = $info["extension"];

    if (isset($fileNmNoEx) && isset($fileEx)) {
        while (file_exists($fileDir . "/" . $fileName)) {
            $count++;
            $fileName = $fileNmNoEx . '-' . $count . '.' . $fileEx;
        }
    }
    return $fileName;
}

function mxCopyFile($fileObj, $fileName = '', $uploadTo = "") {
    set_time_limit(0);

    $info = pathinfo($fileName);
    $fileNmNoEx = $info["filename"];
    $fileEx = $info["extension"];
    if ($fileName != '') {
        $fileName = makeSeoUri($fileNmNoEx) . "." . $fileEx;
    }

    if (file_exists(UPLOADPATH . "/" . $uploadTo . "/" . $fileName)) {
        $count = 0;
        $fileName = mxUniqueFileName(UPLOADPATH . "/" . $uploadTo, $fileName);
    }

    if (!copy($fileObj, UPLOADPATH . "/" . $uploadTo . "/" . $fileName)) {
        $fileName = "";
    }

    return $fileName;
}

function mxFileUpload() {
    $newFileName = array();
    if (isset($_FILES)) {
        global $MXMODNAME;
        foreach ($_FILES as $fldName => $file) {
            if (isset($file)) {
                if(isset($_SESSION[SITEURL][$MXMODNAME]["UDIR"][$fldName]))
                    $uploadTo = $_SESSION[SITEURL][$MXMODNAME]["UDIR"][$fldName];

                if (!isset($uploadTo) || $uploadTo == "")
                    $uploadTo = $MXMODNAME;
                if (!file_exists(UPLOADPATH . "/" . $uploadTo))
                    mkdir(UPLOADPATH . "/" . $uploadTo, 0777);

                if (isset($file["name"]) && is_array($file["name"])) {
                    foreach ($file["name"] as $key => $name) {
                        if(!isset($file[$key]["error"]))
                            $file[$key]["error"] = 0;
                        if (isset($name)) {
                            if (is_array($name) && count($name) > 0) {
                                foreach ($name as $k => $n) {
                                    $newFileName[$fldName][$key][$k] = mxCopyFile($file["tmp_name"][$key][$k], $n, $uploadTo);
                                }
                            } else {
                                $newFileName[$fldName][$key] = mxCopyFile($file["tmp_name"][$key], $name, $uploadTo);
                            }
                        }
                    }
                } else {
                    $newFileName = mxCopyFile($file["tmp_name"], $file["name"], $uploadTo);
                }
            }
        }
    }
    return $newFileName;
}
