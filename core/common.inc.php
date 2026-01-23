<?php
function startsWith($needle, $haystack)
{
    return preg_match('/^' . preg_quote($needle) . "/", $haystack);
}

function endsWith($needle, $haystack)
{
    return preg_match("/" . preg_quote($needle) . '$/', $haystack);
}

function getArrayMods($modDir = "", $arrMod = array())
{
    $path = SITEPATH . "/$modDir";
    $arrModD = explode("/", $modDir);
    $masterMod = "";
    if (count($arrModD) > 0) {
        $masterMod = $arrModD[0];
    }

    if ($dir = @opendir($path . "/")) {
        while (false !== ($file = readdir($dir))) {
            if (!in_array($file, array(".", "..", "inc", ".DS_Store"))) {
                $relPath = str_replace(SITEPATH . "/$masterMod/", "", $path . "/" . $file);
                $type = 0;

                if (is_file($path . "/" . $file)) {
                    $type = 1;
                    if (startsWith("x-", $file) && endsWith(".php", $file) && !endsWith("inc.php", $file) && $file !== 'x-detail.php') {
                        $arrDirname = explode("/", pathinfo($path . "/" . $file, PATHINFO_DIRNAME));
                        $dirname = end($arrDirname);
                        if ($file != "x-" . $dirname . ".inc.php" && $file != "x-" . $dirname . ".php") {
                            $relPath =  str_replace(array("x-", ".php"), "", $relPath);
                        } else {
                            $relPath = "";
                        }
                    } else {
                        $relPath = "";
                    }
                }
                if (isset($relPath) && $relPath !== "") {
                    $arrMod[$relPath] = $type;
                    if (is_dir($path . "/" . $file)) {
                        $arrMod = getArrayMods($modDir . "/" . $file, $arrMod);
                    }
                }
            }
        }
        closedir($dir);
    }
    return $arrMod;
}

function datetoMysql($dateTime)
{
    if ($dateTime) {
        $format = "Y-m-d H:i:s";
        list($date, $time, $ap) = explode(" ", $dateTime);
        if ($date) {
            list($dD, $dM, $dY) = explode("-", $date);
        }
        if ($time) {
            list($tH, $tM, $tS) = explode(":", $time);
        } else {
            $format = "Y-m-d";
        }

        if (trim($ap) == "PM" && $tH < 12) {
            $tH = ($tH + 12);
        } else if (trim($ap) == "AM" && $tH > 11) {
            $tH = ($tH - 12);
        }
        $newDT = date($format, @mktime($tH, $tM, $tS, $dM, $dD, $dY));
        return $newDT;
    }
}

function getTreeDD($arrD = array(), $val = "adminMenuID", $text = "menuTitle", $nmParent = "parentID", $selected = "", $noselect = array())
{
    $options = "";
    $arr = getDepthArray($arrD, $nmParent, $val);
    if (!empty($arr) && sizeof($arr) >= 1 && is_array($arr)) {
        foreach ($arr as $k => $v) {
            $opt_disable = "";
            if ($v) {
                $sel = "";
                if ($v[$val] == "$selected") {
                    $sel = ' selected="selected"';
                }
                if (isset($v["depth"])) {
                    if (in_array($v["depth"], $noselect)) {
                        $opt_disable = "disabled";
                    }
                    $v[$text] = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $v["depth"]) . "&rArr; " . $v[$text];
                }

                $options .= "\n<option value=\"" . $v[$val] . "\"" . $sel . $opt_disable . " >" . $v[$text] . "</option>";
            }
        }
    }
    return $options;
}



function getArrayDD($arr = [])
{
    $options = "";
    if (isset($arr["data"]) && count($arr["data"]) > 0) {
        $defaults = ["data" => [], "selected" => "", "extFields" => []];
        $arr = array_merge($defaults, $arr);
        extract($arr);
        foreach ($data["data"] as $k => $v) {
            if ($v != "") {
                $sel = "";
                //echo "\nSELECXXX: $k, $selected";
                if (is_array($selected)) {

                    if (in_array($k, $selected)) {
                        $sel = ' selected="selected"';
                    }
                } else if ("$k" == "$selected") {
                    $sel = ' selected="selected"';
                }

                $extAttr = "";
                if (isset($extFields) && count($extFields) > 0) {
                    foreach ($extFields as $fldName) {
                        if (isset($data["$fldName"][$k]) && $data["$fldName"][$k] != "") {
                            $extAttr .= ' ' . $fldName . '="' . $data["$fldName"][$k] . '"';
                        }
                    }
                }
                $options .= "\n<option value=\"" . $k . "\"" . $sel . $extAttr . ">" . $v . "</option>";
            }
        }
    }
    return $options;
}

function getTableDD($arr = [])
{
    $dataArr  = getDataArray($arr);
    $selected = "";
    if (isset($arr["selected"])) {
        $selected = $arr["selected"];
    }

    $extFields = [];
    if (isset($arr["extFields"])) {
        $extFields = $arr["extFields"];
    }

    $options = "";
    if (isset($dataArr) && count($dataArr) > 0)
        $options  = getArrayDD(array("data" => $dataArr, "selected" => $selected, "extFields" => $extFields));

    return $options;
}

function getDataArray($arr = [])
{
    $defaults = ["table" => "", "key" => "", "val" => "", "where" => [], "order" => "", "extFields" => [], "org" => true, "lang" => true];
    $arr = array_merge($defaults, $arr);
    extract($arr);

    $arrData = array();
    if ($table && $key && $val) {
        global $DB;
        if (!isset($order) || $order == "") {
            $order = $val;
        }

        $strWhere = "";
        if (isset($where) && count($where) > 0) {
            $strWhere = "WHERE " . $where["sql"];
            $DB->vals = $where["vals"];
            $DB->types = $where["types"];
        }
        $extra = "";
        if (isset($extFields) && count($extFields) > 0) {
            $extra = "," . implode(", ", $extFields);
        }

        $DB->sql = "SELECT `$key`,$val $extra FROM `$table` $strWhere " . mxWhere("", $lang, $org) . " ORDER BY $order";
        $DB->dbRows();
        if ($DB->numRows > 0) {
            foreach ($DB->rows as $v) {
                $arrData["data"][$v[$key]] = $v[$val];
                if ($extra != "") {
                    foreach ($extFields as $fldName) {
                        $arrData["$fldName"][$v[$key]] = $v[$fldName];
                    }
                }
            }
        }
    }
    return $arrData;
}

function getArrTree(&$arr, $nmID = "adminMenuID", $nmParent = "parentID", $id = 0)
{
    $result = array();
    foreach ($arr as $a) {
        if ($id == $a[$nmParent]) {
            $a['childs'] = getArrTree($arr, $nmID, $nmParent, $a[$nmID]);
            $result[] = $a;
        }
    }
    return $result;
}

function getDepthArray($result, $fldParent = "", $fldId = "", $parent = 0, $level = 0, $finalArr = array(), $rt = true)
{
    if (sizeof($result) > 0 && $fldParent && $fldId) {
        foreach ($result as $rs) {
            if ($rs[$fldParent] == $parent) {
                $rs['depth'] = $level++;
                $finalArr[] = $rs;
                $rt = false;
                $finalArr = getDepthArray($result, $fldParent, $fldId, $rs[$fldId], $level, $finalArr, $rt);
                $level--;
            }
        }
    }
    return $rt ? $result : $finalArr;
}

function getCheckbox($arrD = array(), $fieldName = "", $select = array())
{
    $str = "";
    if ($arrD) {
        foreach ($arrD as $k => $v) {
            $chk = "";
            if (in_array($k, $select)) {
                $chk = ' checked="checked"';
            }

            $str .= '<li><i class="chk">' . $v . '<input type="checkbox" name="' . $fieldName . '[]" value="' . $k . '"' . $chk . ' /> <em></em></i></li>';
        }
    }
    return $str;
}

function getRadio($arrD = array(), $fieldName = "", $select = "")
{
    $str = "";

    if ($arrD) {
        foreach ($arrD as $k => $v) {
            $chk = "";
            if ($k == $select) {
                $chk = ' checked="checked"';
            }

            $str .= '<li><i class="rdo">' . $v . '<input type="radio" name="' . $fieldName . '" value="' . $k . '"' . $chk . ' /><em></em></i></li>';
        }
    }
    return $str;
}

function getCheckboxTree($arrD = array(), $fieldName = "", $text = "", $nmParent = "", $selected = array())
{
    $str = "";
    if ($arrD) {
        $str .= '<ul class="tree-list">';
        foreach ($arrD as $v) {
            $chk = "";
            if (in_array($v[$fieldName], $selected)) {
                $chk = ' checked="checked"';
            }

            $str .= '<li><i class="chk">' . $v[$text] . '<input type="checkbox" name="' . $fieldName . '[]" value="' . $v[$fieldName] . '"' . $chk . ' /> <em></em></i>';
            if ($v["childs"]) {
                $str .= getCheckboxTree($v["childs"], $fieldName, $text, $nmParent, $selected);
            }
            $str .= '</li>';
        }
        $str .= '</ul>';
    }
    return $str;
}

function getRadioTree($arrD = array(), $fieldName = "", $text = "", $nmParent = "", $selected = "")
{
    $str = "";
    if ($arrD) {
        $str .= '<ul class="tree-list">';
        foreach ($arrD as $v) {
            $chk = "";
            if ($v[$fieldName] == $selected) {
                $chk = ' checked="checked"';
            }

            $str .= '<li><i class="rdo">' . $v[$text] . '<input type="radio" name="' . $fieldName . '" value="' . $v[$fieldName] . '"' . $chk . ' /> <em></em></i>';
            if ($v["childs"]) {
                $str .= getRadioTree($v["childs"], $fieldName, $text, $nmParent, $selected);
            }
            $str .= '</li>';
        }
        $str .= '</ul>';
    }
    return $str;
}

function resetAutoIncreament($tbl = "", $pk = "")
{
    if ($tbl && $pk) {
        global $DB;
        $maxVal = '1';
        $DB->sql = "SELECT MAX($pk) AS maxVal FROM $tbl";
        $DB->dbRow();
        if ($DB->row["maxVal"]) {
            $maxVal = ($DB->row["maxVal"] + 1);
        }
        $DB->sql = "ALTER TABLE $tbl AUTO_INCREMENT = $maxVal";
        $DB->dbQuery();
    }
}

// === Other Functions ===
function generateKey($len = 7)
{
    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    srand((float) microtime() * 1000000);
    $i = 0;
    $key = '';

    while ($i <= $len) {
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $key = $key . $tmp;
        $i++;
    }
    return $key;
}

function getTimeDateDiff($date1)
{
    $date2 = date('Y-m-d H:i:s');
    $diff = abs(strtotime($date2) - strtotime($date1));
    $years = floor($diff / (365 * 60 * 60 * 24));
    $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
    $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
    $hours = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24) / (60 * 60));
    $minuts = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60) / 60);
    $seconds = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60 - $minuts * 60));

    if ($years) {
        if ($years == 1) {
            $str = $years . ' year ago';
        } else {
            $str = $years . ' years ago';
        }
    } elseif ($months) {
        if ($months == 1) {
            $str = $months . ' month ago';
        } else {
            $str = $months . ' months ago';
        }
    } elseif ($days) {
        if ($days == 1) {
            $str = $days . ' day ago';
        } else {
            $str = $days . ' days ago';
        }
    } elseif ($hours) {
        if ($hours == 1) {
            $str = $hours . ' hour ago';
        } else {
            $str = $hours . ' hours ago';
        }
    } elseif ($minuts) {
        if ($minuts == 1) {
            $str = $minuts . ' minute ago';
        } else {
            $str = $minuts . ' minutes ago';
        }
    } else
    if ($seconds == 1) {
        $str = $seconds . ' second ago';
    } else {
        $str = $seconds . ' seconds ago';
    }

    return $str;
}

function generatTtimezoneList()
{
    static $regions = array(
        DateTimeZone::AFRICA,
        DateTimeZone::AMERICA,
        DateTimeZone::ANTARCTICA,
        DateTimeZone::ASIA,
        DateTimeZone::ATLANTIC,
        DateTimeZone::AUSTRALIA,
        DateTimeZone::EUROPE,
        DateTimeZone::INDIAN,
        DateTimeZone::PACIFIC,
    );

    $timezones = array();
    foreach ($regions as $region) {
        $timezones = array_merge($timezones, DateTimeZone::listIdentifiers($region));
    }

    $timezone_offsets = array();
    foreach ($timezones as $timezone) {
        $tz = new DateTimeZone($timezone);
        $timezone_offsets[$timezone] = $tz->getOffset(new DateTime);
    }

    asort($timezone_offsets);

    $timezone_list = array();
    foreach ($timezone_offsets as $timezone => $offset) {
        $offset_prefix = $offset < 0 ? '-' : '+';
        $offset_formatted = gmdate('H:i', abs($offset));
        $pretty_offset = "UTC${offset_prefix}${offset_formatted}";
        $timezone_list[$timezone] = "(${pretty_offset}) $timezone";
    }
    return $timezone_list;
}

function ordinalSuffix($value, $sup = 0)
{
    is_numeric($value) or trigger_error("<b>\"$value\"</b> is not a number!, The value must be a number in the function <b>ordinal_suffix()</b>", E_USER_ERROR);
    if (substr($value, -2, 2) == 11 || substr($value, -2, 2) == 12 || substr($value, -2, 2) == 13) {
        $suffix = "th";
    } else if (substr($value, -1, 1) == 1) {
        $suffix = "st";
    } else if (substr($value, -1, 1) == 2) {
        $suffix = "nd";
    } else if (substr($value, -1, 1) == 3) {
        $suffix = "rd";
    } else {
        $suffix = "th";
    }
    if ($sup) {
        $suffix = $suffix;
    }
    return $value . $suffix;
}

function mxDeleteFile($path, $file)
{
    $sucess = false;
    if (file_exists($path . $file) && is_file($path . $file)) {
        if (unlink($path . $file)) {
            deleteTemp($path . "tmp/", $file);
            $sucess = true;
        }
    }
    return $sucess;
}

function deleteTemp($path, $file)
{
    foreach (glob($path . "*.*") as $filename) {
        $expldImg = explode("_", $filename);
        if ($expldImg) {
            if ($file == end($expldImg)) {
                if (unlink($filename)) {
                    return true;
                }
            }
        }
    }
}

function getFileType($ext = "")
{
    global $MXSET;
    $fType = "image";
    if (isset($ext) && $ext != "" && $MXSET !== "") {
        if (!in_array(strtolower($ext), explode("|", $MXSET["FILEIMAGE"]))) {
            $fType = "other";
        }
    }
    return $fType;
}

function getFile($params = array())
{
    $defaults = array("path" => "", "title" => "File title", "w" => 50, "h" => 50, "attr" => ' rel=""');
    $arr = array_merge($defaults, $params);
    $str = '';
    extract($arr);
    if (isset($path) && trim($path) != "") {
        if (file_exists(UPLOADPATH . '/' . $path) && is_file(UPLOADPATH . '/' . $path)) {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $fType = getFileType($ext);
            $target = "";
            $img = '';
            if ($fType == "other") {
                $target = ' target="_blank"';
            } else {
                $img = '<img src="' . COREURL . '/image.inc.php?path=' . $path . '&w=' . $w . '&h=' . $h . '" alt="' . $title . '" />';
            }
            $str = '<a href="' . UPLOADURL . '/' . $path . '"' . $target . ' fType="' . $fType . '" ext="' . strtolower($ext) . '" title="' . $title . '">' . $img . '</a>';
        } else {
            $str = '<a href="javascript:void(0)" class="no-img"></a>';
        }
    }
    return $str;
}

function getPaging($param = "", $type = "short")
{
    global $TPL, $MXTOTREC, $MXSHOWREC, $MXOFFSET;
    if ($MXTOTREC > $MXSHOWREC) {
        require_once "paging.class.inc.php";

        $param = "";
        if (isset($TPL->params)) {
            parse_str($TPL->params, $arrParams);
            if (count($arrParams) > 0) {
                $param = "&" . http_build_query($arrParams, '', '&');
            }
        }

        $pageUrl = $TPL->pageUrl . "?showRec=$MXSHOWREC" . $param;
        $paging = new Paging($pageUrl, $MXTOTREC, $MXSHOWREC, 10, 'offset', $type);
        $pageNav = $paging->GetPaging($MXOFFSET);
        return '<div class="mxpaging"><input type="text" name="showRecP" id="showRecP" value="' . $MXSHOWREC . '" class="show-rec" title="Show Records" />' . $pageNav . '</div>';
    }
}

function mxQryLimit()
{
    global $MXOFFSET, $MXSHOWREC, $DB;
    array_push($DB->vals, intval($MXOFFSET), intval($MXSHOWREC));
    $DB->types .= "ii";
    return "  LIMIT ?,?";
}

function mxOrderBy($orderP = "")
{
    global $DB;
    if (isset($_GET["orderBy"]) && isset($_GET["order"]) && $_GET["orderBy"] !== "" && $_GET["order"] !== "") {
        $orderBy = "";
        $order = "";
        if (isset($_GET["orderBy"]) && $_GET["orderBy"] !== "") {

            $orderBy = mysqli_real_escape_string($DB->con, $_GET["orderBy"]);

            if (isset($_GET["order"]) && $_GET["order"] !== "")
                $order = mysqli_real_escape_string($DB->con, $_GET["order"]);
        }
        $orderP = " ORDER BY $orderBy $order";
    } else if (isset($orderP) && $orderP !== "") {
        $orderP = " ORDER BY " . mysqli_real_escape_string($DB->con, $orderP);
    }
    return $orderP;
}

function mxGetUrl($url = "", $arrParam = array())
{
    global $MXSET;
    if (isset($url) && trim($url) != "") {
        $param = "";
        if (isset($MXSET["JSKEY"]) && trim($MXSET["JSKEY"]) !== "0") {
            $param = "x=" . $MXSET["JSKEY"];
        }

        if (isset($arrParam) && count($arrParam) > 0) {
            $param .= "&" . http_build_query($arrParam);
        }

        if ($param !== "") {
            $param = "?" . $param;
        }

        $url = $url . $param;
    }
    return $url;
}


function setResponse($params = [])
{
    global $MXPGTYPE, $MXMODNAME, $MXRES, $MXSET;
    $arrDefauls = array("err" => 1, "param" => "", "data" => "", "msg" => "", "rurl" => "", "alert" => "");
    $params = array_merge($arrDefauls, $params);
    extract($params);

    if (isset($err))
        $MXRES["err"] = $err;

    if (isset($rurl) && $rurl != "")
        $MXRES["rurl"] = $rurl;

    if (isset($alert) && $alert != "")
        $MXRES["alert"] = $alert;

    if (isset($msg) && $msg != "") {
        $MXRES["msg"] = $msg;
    } else {
        $status = "";
        $title = ucfirst(str_replace("-", " ", $MXMODNAME));
        if ($err == 0) {
            $status = "added";
            if ($MXPGTYPE == "edit")
                $status = "updated";

            $MXRES["msg"] = "$title $status successfully";
        } else {
            $MXRES["msg"] = "$title $status successfully";
        }
    }

    if (!isset($rurl) || $rurl == "") {
        $extr = "";
        if ($MXSET["MULTILINGUAL"] == 1 && (isset($_REQUEST["langCode"]) && trim($_REQUEST["langCode"]) !== $MXSET["LANGDEFAULT"]))
            $extr = "&langCode=" . trim($_REQUEST["langCode"]) . "&parentLID=" . intval($_REQUEST["parentLID"]);

        if ($param)
            $param = '?' . $param . $extr;

        $MXRES["rurl"] = ADMINURL . '/' . $MXMODNAME . '-edit/' . $param;
    } else {
        $MXRES["rurl"] = $rurl;
    }
}

function setModVars($arr = array())
{
    global $MXMOD;
    if (count($arr) > 0) {
        global $TPL;
        foreach ($arr as $k => $v) {
            $_SESSION[SITEURL][$TPL->modName][$k] = $v;
        }
        $MXMOD = $_SESSION[SITEURL][$TPL->modName];
    }
}

function mxSaveRequestLog()
{
    global $MXSET;
    if (isset($MXSET["LOGREQUESTDAYS"])) {
        $days = intval($MXSET["LOGREQUESTDAYS"]);
        if ($days > 0) {
            global $DB;
            $DB->table = $DB->pre . "x_log_request";
            $DB->data = array("logUrl" => $_SERVER["HTTP_ORIGIN"] . $_SERVER["REQUEST_URI"], "logData" => json_encode($_REQUEST), "logDate" => date("Y-m-d H:i:s"), "logIP" => getIpAddress());
            $DB->dbInsert();
            $DB->vals = array($days);
            $DB->types = "i";
            $DB->sql = "DELETE FROM " . $DB->pre . "x_log_request WHERE logDate < (NOW() - INTERVAL ? DAY)";
            $DB->dbQuery();
        }
    }
}

function mxCheckRequest($login = true, $ignoreToken = false)
{
    global $MXRES;
    // Only include admin files if not already defined (prevent redeclaration)
    if (!defined('MXADMIN_COMMON_INCLUDED')) {
        require_once ADMINPATH . "/core-admin/common.inc.php";
        require_once ADMINPATH . "/core-admin/settings.inc.php";
    }
    require_once COREPATH . "/jwt.inc.php";
    $MXRES = array();
    $MXRES["validtoken"] = 1;
    $MXRES["err"] = 0;
    $flgReq = 0;
    mxSaveRequestLog();
    if (!$ignoreToken && !mxValidateJwtToken()) {
        return $MXRES;
        $flgReq = 1;
    }

    if ($login == true) {
        if (strpos($_SERVER["REQUEST_URI"], ADMINDIR) !== false || ADMINURL == SITEURL) {
            if (!isAdminUser()) {
                $MXRES["err"] = 1;
                $MXRES["msg"] = "Invalid login";
                $flgReq = 1;
            }
        } else {
            if (!isSiteUser()) {
                $MXRES["err"] = 1;
                $MXRES["msg"] = "Invalid login";
                $flgReq = 1;
            }
        }
    }
    if ($flgReq == 0) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            global $MXPGTYPE, $MXMODNAME;
            if (isset($_REQUEST["modName"])) {
                $MXMODNAME = htmlspecialchars(trim($_REQUEST["modName"]));
            }

            if (isset($_REQUEST["pageType"])) {
                $MXPGTYPE = htmlspecialchars(trim($_REQUEST["pageType"]));
            }

            if ((isset($MXMODNAME) && $MXMODNAME != "")) {
                /*if (isset($_SESSION[SITEURL][$MXMODNAME]["mxValidate"])) {
                    require_once "validate.inc.php";
                    $objV = new mxValidate($_SESSION[SITEURL][ $MXMODNAME ][ "mxValidate" ]);
                    print_r(json_encode($objV->mxerr));
                    $MXRES["data"]["mxValidate"] = $objV->mxerr;
                    $MXRES[ "err" ] = count($objV->mxerr); 
                    $MXRES["err"] = 0;
                }*/
                if ($_FILES) {
                    $fileName = mxFileUpload("");
                    $MXRES["data"]["fileName"] = $fileName;
                }
            }
        }
    }
    return $MXRES;
}

function getJsVars()
{
    global $MXSETTINGSJ, $TPL;
    $arrTplParams = array(
        "MODINCURL" => $TPL->modIncUrl,
        "MODURL" => $TPL->modUrl,
        "PAGETYPE" => $TPL->pageType,
        "PAGEURL" => $TPL->pageUrl,
        "MODNAME" => $TPL->modName,
    );
    return array_merge($MXSETTINGSJ, $arrTplParams);
}

function getSetting($settingKey = "")
{
    global $DB, $MXSETTINGSJ;
    $arr = array();
    $where = "";
    if ($settingKey) {
        $where = " WHERE settingKey=?";
        $DB->types = "s";
        $DB->vals = array($settingKey);
    }
    $DB->sql = "SELECT * FROM `" . $DB->pre . "x_setting` $where";
    //$DB->showSql();
    $DB->dbRows();
    //print_r($DB->rows); 
    foreach ($DB->rows as $v) {
        $val = $v["settingDefault"];
        if (isset($v["settingVal"]) && trim($v["settingVal"]) !== "") {
            $val = $v["settingVal"];
        }

        $arr[$v["settingKey"]] = $val;

        if (isset($v["allowJs"]) && $v["allowJs"] === 1) {
            $MXSETTINGSJ[$v["settingKey"]] = $val;
        }
    }

    if (isset($_SESSION[SITEURL]["THEME"]) && $_SESSION[SITEURL]["THEME"] !== "") {
        $arr["THEME"] = $_SESSION[SITEURL]["THEME"];
        if (isset($MXSETTINGSJ["THEME"])) {
            $MXSETTINGSJ["THEME"] = $arr["THEME"];
        }
    }

    if (isset($_SESSION[SITEURL]["FONT"]) && $_SESSION[SITEURL]["FONT"] !== "") {
        $arr["FONT"] = $_SESSION[SITEURL]["FONT"];
        if (isset($MXSETTINGSJ["FONT"])) {
            $MXSETTINGSJ["FONT"] = $arr["FONT"];
        }
    }

    if (isset($_SESSION[SITEURL]["DEFAULTPAGE"]) && $_SESSION[SITEURL]["DEFAULTPAGE"] !== "") {
        $arr["DEFAULTPAGE"] = $_SESSION[SITEURL]["DEFAULTPAGE"];
    }
    return $arr;
}

function getLanguages($where = "", $types = "", $vals = array())
{
    $arrL = array();
    global $MXSET;
    if (isset($MXSET["MULTILINGUAL"]) && $MXSET["MULTILINGUAL"] == 1) {
        global $DB;
        if ($where !== "") {
            $DB->types .= $types;
            $DB->vals = $vals;
        }
        $DB->sql = "SELECT langID,langPrefix,langName,imageName FROM `" . $DB->pre . "x_language` WHERE status=1 $where " . mxWHere("", false);
        $DB->dbRows();
        if ($DB->numRows) {
            $arrL = $DB->rows;
        }
    }
    return $arrL;
}

function getLangFlag($langPrefix = "")
{
    global $MXSET;
    $str = "";
    if ($MXSET["MULTILINGUAL"] == 1) {
        global $DB;
        if (!isset($langPrefix) || $langPrefix == "") {
            $langPrefix = $MXSET["LANGDEFAULT"];
        }

        $DB->vals = array(1, $langPrefix);
        $DB->types = "is";
        $DB->sql = "SELECT langName,imageName FROM `" . $DB->pre . "x_language` WHERE status=? AND langPrefix = ?";
        $DB->dbRow();
        if ($DB->numRows) {
            $v = $DB->row;
            $str = '<img src="' . UPLOADURL . '/language/' . $v["imageName"] . '" alt="' . $v["langName"] . '" title="' . $v["langName"] . '" /> ';
        }
    }
    return $str;
}

function mxWhere($al = "", $lang = true, $org = true)
{
    global $MXSET, $DB;
    if (!isset($lang))
        $lang = true;

    if (!isset($org))
        $org = true;

    $where = "";
    if ($MXSET["MULTILINGUAL"] == 1 && $lang) {
        $langCode = $MXSET["LANGDEFAULT"];
        if (isset($_REQUEST["langCode"]) && trim($_REQUEST["langCode"]) !== "") {
            $langCode = trim($_REQUEST["langCode"]);
        }
        array_push($DB->vals, $langCode);
        $DB->types .= "s";
        $where = " AND " . $al . "langCode=?";
    }

    if (isset($MXSET["MULTIORG"]) && $MXSET["MULTIORG"] == 1 && $org) {
        if (isset($_SESSION[SITEURL]['ORGIDS']) && $_SESSION[SITEURL]['ORGIDS'] > 0) {
            $arrOrgIDs = $_SESSION[SITEURL]['ORGIDS'];
            $types   = implode("", array_fill(0, count($arrOrgIDs), "i"));
            $inWhere = implode(",", array_fill(0, count($arrOrgIDs), "?"));
            $DB->vals = array_merge($DB->vals, $arrOrgIDs);
            $DB->types .= $types;
            $where .= " AND " . $al . "orgID IN(" . $inWhere . ")";
        } else if (isset($_SESSION[SITEURL]['ORGID']) && $_SESSION[SITEURL]['ORGID'] > 0) {
            array_push($DB->vals, $_SESSION[SITEURL]['ORGID']);
            $DB->types .= "i";
            $where .= " AND " . $al . "orgID=?";
        }
    }

    return $where;
}

function mxWhereIn($strIn = "", $type = "s")
{
    $str = "";
    if (!isset($strIn) || trim($strIn) !== "") {
        global $DB;
        $s = explode(",", $strIn);
        foreach ($s as $v) {
            array_push($DB->vals, $v);
            $DB->types .= $type;
        }
        $str = implode(",", array_fill(0, count($s), "?"));
    }
    return $str;
}

function setAutocompleteVal($data = array())
{
    $str = "";
    if (isset($data) && is_array($data)) {
        foreach ($data as $v) {
            $str .= '<li><a href="#" class="del rs" onclick="mxDelTagAc(this); return false;"></a>' . $v . '</li>';
        }
    }
    return $str;
}

function mxGetTableFlds($table = "", $arrSkip = array("orgID", "seouri", "status", "addedby", "modifiedby", "dateadded", "datemodified", "langcode", "langchild", "parentlid", "xorder"))
{
    $arr = array();
    if (isset($table) && $table != "") {
        global $DB;
        $table = mysqli_real_escape_string($DB->con, $table);
        $DB->sql = "DESCRIBE " . $table;
        $DB->dbRows();
        if ($DB->numRows > 0) {
            foreach ($DB->rows as $d) {
                if (!in_array(strtolower($d["Field"]), $arrSkip)) {
                    $arr[$d["Field"]] = $d["Field"];
                }
            }
        }
    }
    return $arr;
}

function formatFileSize($bytes = 0)
{
    $val = "0";
    if (isset($bytes) && floatval($bytes) > 0) {
        $symbols = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
        $exp = floor(log($bytes) / log(1024));
        $val = sprintf('%.2f ' . $symbols[$exp], ($bytes / pow(1024, floor($exp))));
    }
    return $val;
}

function mxLog($str = "")
{
    //file_put_contents("log.txt", $str, FILE_APPEND);
    file_put_contents(SITEPATH . "/log.txt", $str);
}

function logRequest()
{
    global $fileName;
    $req_dump = print_r($_FILES, true);
    $req_dump .= print_r($_REQUEST, true);
    $req_dump .= print_r($_SESSION, true);
    mxLog($req_dump);
}

function numberToWord($number = 0)
{
    $no = round($number);
    $decimal = round($number - ($no = floor($number)), 2) * 100;
    $digits_length = strlen($no);
    $i = 0;
    $str = array();
    $words = array(
        0 => '',
        1 => 'One',
        2 => 'Two',
        3 => 'Three',
        4 => 'Four',
        5 => 'Five',
        6 => 'Six',
        7 => 'Seven',
        8 => 'Eight',
        9 => 'Nine',
        10 => 'Ten',
        11 => 'Eleven',
        12 => 'Twelve',
        13 => 'Thirteen',
        14 => 'Fourteen',
        15 => 'Fifteen',
        16 => 'Sixteen',
        17 => 'Seventeen',
        18 => 'Eighteen',
        19 => 'Nineteen',
        20 => 'Twenty',
        30 => 'Thirty',
        40 => 'Forty',
        50 => 'Fifty',
        60 => 'Sixty',
        70 => 'Seventy',
        80 => 'Eighty',
        90 => 'Ninety',
    );
    $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
    while ($i < $digits_length) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += $divider == 10 ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $str[] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural;
        } else {
            $str[] = null;
        }
    }

    $Rupees = implode(' ', array_reverse($str));
    $paise = ($decimal) ? "And Paise " . ($words[$decimal - $decimal % 10]) . " " . ($words[$decimal % 10]) : '';
    return ($Rupees ? 'Rupees ' . $Rupees : '') . $paise . " Only";
}

function isValidIpAddress($ip)
{
    if (filter_var(
        $ip,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_IPV4 |
            FILTER_FLAG_IPV6 |
            FILTER_FLAG_NO_PRIV_RANGE |
            FILTER_FLAG_NO_RES_RANGE
    ) === false) {
        return false;
    }
    return true;
}

function getIpAddress()
{
    $ipAddress = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        // to get shared ISP IP address
        $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
    } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // check for IPs passing through proxy servers
        // check if multiple IP addresses are set and take the first one
        $ipAddressList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        foreach ($ipAddressList as $ip) {
            if (!empty($ip)) {
                // if you prefer, you can check for valid IP address here
                $ipAddress = $ip;
                break;
            }
        }
    } else if (!empty($_SERVER['HTTP_X_FORWARDED'])) {
        $ipAddress = $_SERVER['HTTP_X_FORWARDED'];
    } else if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
        $ipAddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    } else if (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
        $ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
    } else if (!empty($_SERVER['HTTP_FORWARDED'])) {
        $ipAddress = $_SERVER['HTTP_FORWARDED'];
    } else if (!empty($_SERVER['REMOTE_ADDR'])) {
        $ipAddress = $_SERVER['REMOTE_ADDR'];
    } else if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        $ipAddress = $_SERVER['REMOTE_ADDR'];
    }
    if (!isValidIpAddress($ipAddress)) {
        $ipAddress = '';
    }

    return $ipAddress;
}

function mxGetAllTables($arrSkip = array())
{
    global $DB;
    $arrTables = array();
    $DB->sql = "SHOW TABLES";
    $data = $DB->dbRows();
    if ($DB->numRows > 0) {
        foreach ($data as $d) {
            foreach ($d as $tName) {
                if (!in_array($tName, $arrSkip))
                    $arrTables["$tName"] = $tName;
            }
        }
        ksort($arrTables);
    }

    return $arrTables;
}

function mxAddColAfter($arrAddto = [], $arr = [], $colName = "", $pos = 1)
{
    if (!isset($pos))
        $pos = 1;
    $arrRes = [];
    if (isset($arrAddto) && is_array($arrAddto) && isset($colName) && $colName != "") {
        foreach ($arrAddto as $k => $v) {
            $arrRes[] = $v;
            if ($v[$pos] == $colName) {
                $arrRes[] = $arr;
            }
        }
    }
    return $arrRes;
}

function mxAddColSetAfter($arrAddto = [], $arr = [], $colName = "", $pos = 1)
{
    if (!isset($pos))
        $pos = 1;
    $arrRes = [];
    if (isset($arrAddto) && is_array($arrAddto) && isset($colName) && $colName != "") {
        foreach ($arrAddto as $k => $v) {
            $arrRes[] = $v;
            if ($v[$pos] == $colName) {
                foreach ($arr as  $vv) {
                    $arrRes[] = $vv;
                }
            }
        }
    }
    return $arrRes;
}
