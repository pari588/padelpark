<?php
function scanpath($path, $arrDir = array(), $noDir = false)
{
    $dirurl = UPLOADPATH . $path;
    $arrDir = [];
    if (is_dir($dirurl)) {
        $myscan = scandir($dirurl);
        foreach ($myscan as $entry) {
            if ($entry === '.' || $entry === '..' || $entry === '.DS_Store') {
                $arrDir["$path"] = array();
                continue;
            } else if (is_dir($dirurl . '/' . $entry)) {
                if (!$noDir && $entry != "tmp") {
                    $arrDir = scanpath($path . '/' . $entry, $arrDir);
                }
            } else {
                $arrDir["$path"][] = array("n" => $entry, "s" => filesize($dirurl . '/' . $entry));
            }
        }
    }
    return $arrDir;
}

function mxDelFileF()
{
    global $MXRES;
    $MXRES = array("err" => 1, "msg" => "Cannot delete file something went wrong.", "data" => array(), "rurl" => "");
    $arrFiles = $_POST["fileNames"];
    $flg = 0;
    if (count($arrFiles) > 0) {
        $basePath = UPLOADPATH . $_POST["dirPath"] . "/";
        foreach ($arrFiles as $fileName) {
            $filePath = $basePath . $fileName;
            if (file_exists($filePath) && is_file($filePath)) {
                if (unlink($filePath)) {
                    $MXRES["data"][] = $fileName;
                    $flg++;
                }
            }
        }
    }
    if ($flg > 0) {
        $MXRES["err"] = 0;
        $MXRES["msg"] = "($flg) Files deleted successfuly";
    }
}

function mxFileUploadF()
{
    $newFileName = array();
    if (isset($_FILES)) {
        $uploadTo = "";
        if ($_REQUEST["dirPath"])
            $uploadTo = htmlspecialchars(trim($_REQUEST["dirPath"]));
        foreach ($_FILES as $fldName => $file) {
            if (isset($file)) {
                if (!file_exists(UPLOADPATH . "/" . $uploadTo))
                    mkdir(UPLOADPATH . "/" . $uploadTo, 0777);

                if (is_array($file["name"])) {
                    foreach ($file["name"] as $key => $name) {
                        if (isset($name) && $file[$key]["error"] == 0) {
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
        // print_r($_FILES);    exit();
    }
    return $newFileName;
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once(ADMINPATH . "/core-admin/common.inc.php");
    $MXRES["msg"] = "Sorry! You are not authorized to perform this action...";
    if (!isAdminUser()) {
        $MXRES["err"] = 1;
    }

    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $MXRES["err"] = 0;
    } else {
        $MXRES["err"] = 1;
    }

    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "uploadFile":
                $fileName = array();
                if ($_FILES) {
                    $fileName = mxFileUploadF();
                }
                if (count($fileName) > 0) {
                    $dirPath = htmlspecialchars($_REQUEST["dirPath"]);
                    $MXRES["data"] = scanpath($dirPath, array(), true);
                    $MXRES["err"] = 0;
                    $MXRES["msg"] = "File Uploaded succesfully...: $dirPath";
                } else {
                    $MXRES["data"] = "{}";
                    $MXRES["err"] = 1;
                    $MXRES["msg"] = "Sorry! nofile uploaded...";
                }

                break;
            case "createDir":
                $flg = 0;
                $dirPath = htmlspecialchars($_REQUEST["dirPath"]);
                if (!is_dir(UPLOADPATH . $dirPath)) {
                    if (mkdir(UPLOADPATH . $dirPath, 0755, true)) {
                        $flg++;
                    }
                }
                if ($flg > 0) {
                    $MXRES["data"] = scanpath($dirPath, array(), true);
                    $MXRES["err"] = 0;
                    $MXRES["msg"] = 'Directory created successfully...';
                } else {
                    $MXRES["data"] = "{}";
                    $MXRES["err"] = 1;
                    $MXRES["msg"] = "Sorry! Error creating direcory...";
                }
                break;

            case "mxDelFileF":
                mxDelFileF();
                break;
        }
    }
    echo json_encode($MXRES);
}
