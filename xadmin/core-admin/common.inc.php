<?php
// Guard to prevent double inclusion of function definitions
if (!defined('MXADMIN_COMMON_INCLUDED')) {
    define('MXADMIN_COMMON_INCLUDED', true);

function mxSetLogIcon()
{
    global $MXSET, $DB, $MXDBLOG, $TPL;
    if (isset($MXSET["LOGIGNORETBL"]) && trim($MXSET["LOGIGNORETBL"]) !== "") {
        $arrTblSkip = explode(",", $MXSET["LOGIGNORETBL"]);
        $table = trim($_SESSION[SITEURL][$TPL->modName]["TBL"]);

        if (isset($table) && $table != "") {
            $tbl = $DB->pre . $table;
            if (in_array($tbl, $arrTblSkip)) {
                $MXDBLOG = false;
            }
        }
    }
}

function setTokenID()
{
    global $MXSET;
    if (isset($MXSET["TOKENID"]) && $MXSET["TOKENID"] != "") {
        $sesid = session_id();
        if (empty($sesid)) {
            session_start();
            $sesid = session_id();
        }
        $_SESSION[SITEURL][$MXSET["TOKENID"]] = $sesid;
    }
}
function isSiteUser()
{
    global $MXSET;
    if (isset($_SESSION[$MXSET["MXLOGINKEY"]]))
        $UID = $_SESSION[$MXSET["MXLOGINKEY"]];
    if (isset($UID) && ($UID > 0)) {
        return true;
    } else {
        return false;
    }
}

function isAdminUser()
{
    if (isset($_SESSION[SITEURL]['MXID']) && ($_SESSION[SITEURL]['MXID'] > 0 || $_SESSION[SITEURL]['MXID'] === "SUPER")) {
        return true;
    } else {
        return false;
    }
}

function loginAdminUser($userName = "", $userPass = "")
{
    global $DB, $MXADMIN;
    $flg = false;
    if (isset($userName) && $userName != "" && isset($userPass) && $userPass != "") {
        if ($userName == $MXADMIN["user"] && md5($userPass) == $MXADMIN["pass"]) {
            setTokenID();
            $_SESSION[SITEURL]['LOGINTYPE'] = "backend";
            $_SESSION[SITEURL]['MXID'] = "SUPER";
            $_SESSION[SITEURL]['MXNAME'] = "Maxdigi Solutions";
            $_SESSION[SITEURL]['MXROLE'] = "SUPER";
            $arrT = getSetting("THEME");
            $arrF = getSetting("FONT");
            $_SESSION[SITEURL]['THEME'] = $arrT["THEME"];
            $_SESSION[SITEURL]['FONT'] = $arrF["FONT"];
            $flg = true;
        } else {

            $DB->vals = array($userName, md5($userPass), 1, 1);
            $DB->types = "ssii";

            global $MXSET;
            $strSel = "";
            if (isset($MXSET["MULTIORG"]) && $MXSET["MULTIORG"] == 1) {
                $strSel = ",UR.orgID";
            }

            $DB->sql = "SELECT UR.displayName,UR.userID,UR.roleID,UR.userTheme,UR.userFont,UT.rolePage,UT.roleKey,roleKeyP,UT.parentID $strSel
                FROM `" . $DB->pre . "x_admin_user` AS UR"
                . " LEFT JOIN `" . $DB->pre . "x_admin_role` AS UT ON UR.roleID = UT.roleID"
                . " WHERE UR.userName=? AND UR.userPass=? AND UR.status=? AND UT.status=?";

            $arrUsr = $DB->dbRow();
            if ($DB->numRows > 0) {
                $flg = true;
                if (isset($MXSET["MULTIORG"]) && $MXSET["MULTIORG"] == 1) {
                    if (isset($arrUsr["orgID"]) && $arrUsr["orgID"] > 0 && $strSel != "") {
                        $DB->vals = array($arrUsr["orgID"], 1);
                        $DB->types = "ii";
                        $DB->sql = "SELECT * FROM `" . $DB->pre . "x_organization` WHERE orgID=? AND status=?";
                        $arrOrg = $DB->dbRow();
                        if ($DB->numRows > 0) {
                            $orgID = $arrOrg["orgID"];
                            $_SESSION[SITEURL]['ORGID'] = $orgID;
                            $_SESSION[SITEURL]['ORGDATA'] = $arrOrg;
                            if (isset($arrOrg["parentID"]) && $arrOrg["parentID"] == 0) {
                                $orgID = $arrOrg["orgID"];
                                $DB->vals = array($orgID);
                                $DB->types = "i";
                                $DB->sql = "SELECT GROUP_CONCAT(orgID SEPARATOR ',') AS strOrgIDs FROM `" . $DB->pre . "x_organization` WHERE parentID=? GROUP BY NULL";
                                $arrOrgC = $DB->dbRow();
                                if ($DB->numRows > 0) {
                                    $arrOrgIDs = explode(",", $arrOrgC["strOrgIDs"]);
                                    array_push($arrOrgIDs, $orgID);
                                    $_SESSION[SITEURL]['ORGIDS'] = $arrOrgIDs;
                                }
                            }
                        } else {
                            $flg = false;
                        }
                    }
                }
                if ($flg) {
                    setTokenID();
                    $_SESSION[SITEURL]['LOGINTYPE'] = "backend";
                    $_SESSION[SITEURL]['MXID'] = $arrUsr["userID"];
                    $_SESSION[SITEURL]['MXNAME'] = $arrUsr["displayName"];
                    $_SESSION[SITEURL]['MXROLE'] = $arrUsr["roleID"];
                    $_SESSION[SITEURL]['MXROLEP'] = $arrUsr["parentID"];
                    $_SESSION[SITEURL]['MXROLEKEY'] = $arrUsr["roleKey"];
                    $_SESSION[SITEURL]['MXROLEKEYP'] = $arrUsr["roleKeyP"];
                    $_SESSION[SITEURL]['THEME'] = $arrUsr["userTheme"];
                    $_SESSION[SITEURL]['FONT'] = $arrUsr["userFont"];
                    $_SESSION[SITEURL]['DEFAULTPAGE'] = $arrUsr["rolePage"];

                    $DB->vals = array($arrUsr["roleID"]);
                    $DB->types = "i";
                    $DB->sql = "SELECT GROUP_CONCAT(roleID SEPARATOR ',') AS strRoleIDs FROM `" . $DB->pre . "x_admin_role` WHERE parentID=? GROUP BY NULL";
                    $DB->dbRow();
                    if ($DB->numRows > 0) {
                        $arrRoleIDs = explode(",", $DB->row["strRoleIDs"]);
                        $_SESSION[SITEURL]['MXROLEC'] = $arrRoleIDs;
                    }

                    $DB->vals = array(date("Y-m-d H:i:s"), $_SESSION[SITEURL]['MXID']);
                    $DB->types = "si";
                    $DB->sql = "UPDATE " . $DB->pre . "x_admin_user SET dateLogin=? WHERE userID=?";
                    $DB->dbQuery();
                }
            }
        }
    }
    return $flg;
}


function saveMeta($params = array())
{
    global $MXSET;
    if ($MXSET["MULTILINGUAL"] == 1 && isset($_REQUEST["langCode"]) && trim($_REQUEST["langCode"]) != $MXSET["LANGDEFAULT"]) {
        return "";
    }
    global $MXMODNAME;
    $str = "";
    $metaTitle = "";
    $metaKeyword = "";
    $metaDesc = "";
    if (isset($_REQUEST["metaTitle"]) && is_string($_REQUEST["metaTitle"])) {
        $metaTitle = htmlspecialchars(trim($_REQUEST["metaTitle"]));
    }

    if (isset($_REQUEST["metaKeyword"]) && is_string($_REQUEST["metaKeyword"])) {
        $metaKeyword = htmlspecialchars(trim($_REQUEST["metaKeyword"]));
    }

    if (isset($_REQUEST["metaDesc"]) && is_string($_REQUEST["metaDesc"])) {
        $metaDesc = htmlspecialchars(trim($_REQUEST["metaDesc"]));
    }

    if (!isset($_REQUEST["metaType"]))
        $_REQUEST["metaType"] = 0;

    $defaults = array("metaKey" => $MXMODNAME, "metaValue" => 0, "metaType" => intval($_REQUEST["metaType"]), "metaTitle" => $metaTitle, "metaKeyword" => $metaKeyword, "metaDesc" => $metaDesc);
    $data = array_merge($defaults, $params);
    $str = '';
    extract($data);
    if (isset($metaKey) && $metaKey != "") {
        global $DB;
        if ($metaTitle != "" || $metaKeyword != "" || $metaDesc != "") {
            $DB->vals = array($metaKey, $metaValue, $metaType);
            $DB->types = "sss";
            $DB->sql = "SELECT metaID FROM `" . $DB->pre . "x_meta` WHERE `metaKey` = ? AND `metaValue` = ? AND `metaType` = ?";
            $DB->dbQuery();
            if ($DB->numRows > 0) {
                $DB->table = $DB->pre . "x_meta";
                $DB->data = $data;
                if ($DB->dbUpdate("metaKey=? AND metaValue=?", "ss", array($metaKey, $metaValue))) {
                    $str = "OK";
                }
            } else {
                $DB->table = $DB->pre . "x_meta";
                $DB->data = $data;
                if ($DB->dbInsert()) {
                    $str = "OK";
                }
            }
            resetAutoIncreament($DB->pre . "x_meta", "metaID");
        } else {
            $DB->vals = array($metaKey, $metaValue);
            $DB->types = "ss";
            $DB->sql = "DELETE FROM `" . $DB->pre . "x_meta` WHERE `metaKey` = ? AND `metaValue` = ?";
            $DB->dbQuery();
            resetAutoIncreament($DB->pre . "x_meta", "metaID");
            $str = "DEL";
        }
    }
    return $str;
}

function getAdminMenu()
{
    global $MXADMINMENU, $MXSET, $TPL;
    $str = "";
    if ($MXADMINMENU) {
        if (isset($MXSET["MULTIORG"]) && $MXSET["MULTIORG"] == 0)
            unset($MXADMINMENU['100000']);

        foreach ($MXADMINMENU as $k => $v) {
            if (isset($TPL->mAccess[$v["seoUri"]])) {
                $str .= '<li><a class="' . $v["class"] . '" href="' . ADMINURL . '/' . $v["dUri"] . '/" title="' . $v["menuTitle"] . '">' . $v["menuTitle"] . '</a></li>';
            }
        }
    }
    return $str;
}

function getAdminSMenu()
{
    global $DB, $TPL;
    $str = "";
    $strS = "";
    $DB->sql = "SELECT * FROM `" . $DB->pre . "x_admin_menu` WHERE status = 1 AND parentID = 0 AND hideMenu=0 ORDER BY xOrder ASC";
    $DB->dbRows();
    if ($DB->numRows > 0) {
        $main = $DB->rows;
        foreach ($main as $v) {
            $DB->vals = array($v['adminMenuID']);
            $DB->types = "i";
            $DB->sql = "SELECT * FROM `" . $DB->pre . "x_admin_menu` WHERE parentID = ? AND hideMenu=0 ORDER BY xOrder ASC";
            $DB->dbRows();
            $strT = "";
            $classM = "";
            $strS = "";
            if ($DB->numRows) {
                $sub = $DB->rows;
                foreach ($sub as $d) {
                    if (isset($TPL->mAccess[$d["seoUri"]])) {
                        $classL = "";
                        if ($TPL->pageUri == $d["seoUri"] . "-" . $TPL->pageType)
                            $classL = $classM = ' class="active"';

                        $strAdd = "";
                        if (in_array("add", $TPL->mAccess[$d["seoUri"]]) && $d["forceNav"] == "")
                            $strAdd = '<a href="' . ADMINURL . '/' . $d["seoUri"] . '-add/' . $d["params"] . '" class="add" title="Add New ' . $d["menuTitle"] . '"></a>';

                        $pgType = ($d["forceNav"] != "" ? $d["forceNav"] : "list");
                        $strT .= '<li' . $classL . '>' . $strAdd . '<a href="' . ADMINURL . '/' . $d["seoUri"] . '-' . $pgType . '/' . $d["params"] . '">' . $d["menuTitle"] . '</a></li>';
                    }
                }
                if ($strT) {
                    $strS = '<ul>' . $strT . '</ul>';
                    if ($TPL->pageUri == $v["seoUri"] . "-" . $TPL->pageType)
                        $classM = ' class="active"';

                    $pgType = ($v["forceNav"] != "" ? $v["forceNav"] : "list");
                    $str .= '<li' . $classM . '><a href="#" class="down-arrow"></a><a href="' . ADMINURL . '/' . $v["seoUri"] . '-' . $pgType . '/' . $v["params"] . '">' . $v["menuTitle"] . '</a>' . $strS . '</li>';
                }
            } else {
                if (isset($TPL->mAccess[$v["seoUri"]])) {
                    $strAdd = "";
                    $pgType = ($v["forceNav"] != "" ? $v["forceNav"] : "list");

                    if ($TPL->pageUri == $v["seoUri"] . "-list" || $TPL->pageUri == $v["seoUri"] . "-add" || $TPL->pageUri == $v["seoUri"] . "-edit")
                        $classM = ' class="active"';

                    //echo "\n\n".$TPL->pageUri.":".$v["seoUri"] . "-$pgType";    

                    if (in_array("add", $TPL->mAccess[$v["seoUri"]]) && $v["forceNav"] == "")
                        $strAdd = '<a href="' . ADMINURL . '/' . $v["seoUri"] . '-add/' . $v["params"] . '" class="add" title="Add New ' . $v["menuTitle"] . '"></a>';

                    $str .= '<li' . $classM . '>' . $strAdd . '<a href="' . ADMINURL . '/' . $v["seoUri"] . '-' . $pgType . '/' . $v["params"] . '">' . $v["menuTitle"] . '</a>' . $strS . '</li>';
                }
            }
        }
    }
    return $str;
}

function getPageNav($moreL = "", $moreR = "", $arrSkip = array(), $qParam = "")
{
    global $TPL, $MXPGMENU;
    $str = $strL = $strR = $mandatory = "";

    if ($MXPGMENU[$TPL->pageType] && count($TPL->access) > 0) {

        global $MXFORCENAV;
        if ($MXFORCENAV == "") {
            foreach ($MXPGMENU[$TPL->pageType] as $v) {
                if (!in_array($v, $arrSkip)) {
                    $flg = 0;
                    if ($v == "add") {
                        if (in_array("add", $TPL->access))
                            $flg = 1;
                    } else {
                        $flg = 1;
                    }
                    if ($flg == 1)
                        $str .= '<a href="' . ADMINURL . '/' . $TPL->modName . '-' . $v . '/' . $qParam . '" class="fa-' . $v . ' btn" title="' . ucfirst($v) . '"> ' . ucfirst($v) . '</a>';
                }
            }
        }

        if ($TPL->pageType == "trash" || $TPL->pageType == "list") {
            global $MXFRM, $MXTOTREC;
            if (isset($MXFRM) && !in_array("paging", $arrSkip))
                $strR .= getPaging($MXFRM->param);

            if (((isset($MXFRM->where) && $MXFRM->where != "") || $MXTOTREC > 0) && !in_array("search", $arrSkip))
                $strR .= '<a href="#" class="fa-search btn search" title="Search"> Search</a>';

            $strR .= $str;

            if (!in_array("print", $arrSkip) && $MXTOTREC > 0)
                $strR .= '<a href="#" class="fa-print btn print" title="Print"> Print</a>';

            if (!in_array("export", $arrSkip) && $MXTOTREC > 0 && isset($_SESSION[SITEURL][$TPL->modName]["EXPCOLS"]) && isset($_SESSION[SITEURL][$TPL->modName]["EXPCOLS"]))
                $strR .= '<a href="#" class="fa-export btn export" title="Export"> Export</a>';

            if (!in_array("trash", $arrSkip))
                $strL =  getMAction("menu");
        }

        if (($TPL->pageType == "add" || $TPL->pageType == "edit") && (!in_array("add", $arrSkip) || !in_array("update", $arrSkip))) {

            if ($TPL->pageType == "edit")
                $btnName = "UPDATE";
            else
                $btnName = "SAVE";

            $mandatory = '<div class="mandatory">Fields with (<em>* </em>) are mandatory</div>';

            $save = "";
            if ($TPL->pageType !== "view")
                $save = '<a href="#" class="fa-save btn" rel="frmAddEdit"> ' . $btnName . ' </a>';

            $strR = $save . $str;
        } else if ($TPL->pageType == "view")
            $strR =  $str;
    }

    if (trim($strL) != "" || trim($moreL) != "")
        $strL = '<div id="nav-left" class="nav-left">' . $strL . $moreL . '</div>';

    if (trim($strR) != "" || trim($moreR) != "")
        $strR = '<div class="nav-right" id="nav-right">' . $strR . $moreR . '</div>';

    $langCode = "";
    if (isset($_REQUEST["langCode"]))
        $langCode = htmlspecialchars(trim($_REQUEST["langCode"]));
    return '<div class="page-nav" id="page-nav">' . $strL . '<h1 class="pg-ttl">' . getLangFlag($langCode) . $TPL->tplTitle . '</h1>' . $mandatory . $strR . '</div>';
}

function getListTitle($arrCol = array(), $trash = true)
{
    $str = '';
    if (count($arrCol) > 0) {
        global $TPL, $MXDBLOG, $MXSET;
        if ($trash)
            $str = getMAction("top");

        $order = "asc";
        if (isset($_GET["order"]) && $_GET["order"] == "asc") {
            $order = "desc";
        }

        $params = "";
        if (isset($TPL->params)) {
            parse_str($TPL->params, $arrParams);
            unset($arrParams["orderBy"]);
            unset($arrParams["order"]);
            if (count($arrParams) > 0) {
                $params = "&" . http_build_query($arrParams, '', '&');
            }
        }

        if ($MXDBLOG && isset($MXSET["LOGACTIONDAYS"]) && $MXSET["LOGACTIONDAYS"] > 0) {
            $str .= '<th width="1%" class="noprint">LOG</th>';
        }

        foreach ($arrCol as $v) {
            $orderClass = "sort";
            if (isset($_GET["order"]) && $_GET["orderBy"] == $v[1] && $_GET["orderBy"] != "") {
                $orderClass = $order;
            }
            if (isset($v[4]) && $v[4] == 'nosort') {
                $str .= '<th' . $v[2] . '>' . $v[0] . '</th>';
            } else {
                $str .= '<th' . $v[2] . '><a class="' . $orderClass . '" href="?orderBy=' . $v[1] . '&order=' . $order . $params . '">' . $v[0] . '</a></th>';
            }
        }
    }
    return $str;
}

function getMAction($type = "mid", $id = 0, $trash = true, $draggable = false)
{
    global $MXTOTREC;
    $str = "";

    if (!isset($trash))
        $trash = true;

    if ($MXTOTREC > 0) {
        $titleWrap = '<th align="center" class="noprint" width="1%">REPLACEIT</th>';
        $itemWrap = '<td align="center" class="noprint" width="1%">REPLACEIT</td>';
        if (isset($draggable) && $draggable == true) {
            $itemWrap = '<div class="item center noprint" style="flex:0 0 30px">REPLACEIT</div>';
            $titleWrap = '<li class="item center noprint" style="flex:0 0 30px">REPLACEIT</li>';
        }
        if ($trash) {
            global $TPL, $MXMACTION;
            $arrA = array_intersect($MXMACTION[$TPL->pageType], $TPL->access);
            if (count($arrA)) {
                if ($type) {
                    global $MXNOTRASHID;
                    if ($type == "top") {
                        $str = str_replace('REPLACEIT', '<i class="chk"><input type="checkbox" class="chkAll" title="Select All" /><em></em></i>', $titleWrap);
                    }

                    if ($type == "mid") {
                        if ((($id == $_SESSION[SITEURL]['MXID'] || $id == '1') && $TPL->modName == "admin-user") || in_array($id, $MXNOTRASHID)) {
                            $str = str_replace('REPLACEIT', '&nbsp;', $itemWrap);
                        } else {
                            $str = str_replace('REPLACEIT', '<i class="chk"><input type="checkbox" class="list-item" value="' . $id . '" /><em></em></i>', $itemWrap);
                        }
                    }

                    if ($type == "menu") {
                        foreach ($arrA as $a)
                            $str .= '<a href="#" class="fa-trash-o btn ' . $a . ' action" rel="' . $a . '"> ' . strtoupper($a) . '</a>';
                    }
                }
            }
        }
        global $MXDBLOG, $MXSET;
        if ($type == "mid" && ($MXDBLOG && isset($MXSET["LOGACTIONDAYS"]) && $MXSET["LOGACTIONDAYS"] > 0)) {
            $str .= str_replace('REPLACEIT', '<a href="#" class="fa-history btn ico noprint" title="Change History" rel="' . $id . '"></a>', $itemWrap);
        }
    }
    return $str;
}

function getViewEditUrl($param = "", $val = "", $langC = "", $show = true, $view = true)
{
    global $TPL;
    $str = "";

    if ($param && $val && $TPL->pageType == "list" && $show == true) {
        $strE = "";
        $url = ADMINURL . '/' . $TPL->modName;
        $access = $TPL->mAccess[$TPL->modName];
        $strE = "";
        $strV = "";

        if (in_array("edit", $access)) {
            $strE = '<a href="' . $url . '-edit/?' . $param . '" class="edit" title="Edit"></a>';
        }
        if (in_array("view", $access) && $param != "" && $view == true) {
            $strV = '<a href="' . $url . '-view/?' . $param . '" class="view" title="View"></a>';
        }

        //----------------------------------------------------------------------
        global $MXSET;

        $classL = "";

        if ((!$TPL->modCore || $TPL->modName == "menu") && $MXSET["MULTILINGUAL"] == 1 && isset($langC) && $langC !== "") {
            global $MXLANGS;
            if (count($MXLANGS) > 0) {
                $classL = " lang";

                $arrChilds = array();
                if (isset($langC) && $langC !== "")
                    $arrChilds = explode(",", $langC);

                parse_str($param, $arrP);

                $pgType = "-edit";
                foreach ($MXLANGS as $v) {
                    if ($v["langPrefix"] !== $MXSET["LANGDEFAULT"]) {
                        $pgType = "-add/";
                        $lcode = "?langCode=" . $v["langPrefix"];
                        $id = "&parentLID=" . $arrP["id"] . "&" . $param;
                        $img = '<img src="' . UPLOADURL . '/language/' . $v["imageName"] . '" alt="' . $v["langName"] . '" title="' . $v["langName"] . '" />';
                        if (in_array($v["langPrefix"], $arrChilds)) {
                            $pgType = "-edit/";
                            $strV .= '<a href="' . $url . "-view/" . $lcode . $id . '"  title="' . $v["langName"] . '">' . $img . '</a>';
                        }

                        $strE .= '<a href="' . $url . $pgType . $lcode . $id . '"  title="' . $v["langName"] . '">' . $img . '</a>';
                    }
                }
            }
        }
        $str = '<div class="veiw-edit' . $classL . '">' . $val
            . '<div class="ve-wrap">'
            . '<div class="edit">' . $strE . '</div>'
            . '<div class="view">' . $strV . '</div>'
            . '</div>'
            . '</div>';
    } else {
        $str = $val;
    }
    return $str;
}

function mxgetThemes()
{
    global $MXSET, $MXTHEMES;
    $str = "";
    foreach ($MXTHEMES as $k => $v) {
        $active = "";
        if ($MXSET["THEME"] == $v)
            $active = ' active';
        $str .= '<a class="' . $v . $active . '" href="#" title="' . $v . '">' . $k . '</a>';
    }
    return $str;
}

function mxgetFonts()
{
    global $MXSET, $MXFONTS;
    $str = "";
    foreach ($MXFONTS as $k => $v) {
        $active = "";
        if ($MXSET["FONT"] == $v)
            $active = ' active';
        $str .= '<a class="' . $v . $active . '" href="#" title="' . $v . '">' . $k . '</a>';
    }
    return $str;
}

if (!function_exists('mxGetMetaArray')) {
    function mxGetMetaArray($metaKey = "", $metaValue = 0, $metaType = 0)
    {
        $arr = array("metaTitle" => "", "metaKeyword" => "", "metaDesc" => "");
        if ($metaKey) {
            global $DB;
            $DB->vals = array($metaKey, $metaValue, $metaType);
        $DB->types = "sss";
        $DB->sql = "SELECT * FROM `" . $DB->pre . "x_meta` WHERE `metaKey` = ? AND `metaValue`=? AND metaType=?";
        $d = $DB->dbRow();
        if ($DB->numRows > 0)
            $arr = $d;
        }
        return $arr;
    }
} // End of if (!function_exists('mxGetMetaArray'))
} // End of if (!defined('MXADMIN_COMMON_INCLUDED'))
