<?php
class manageTemplate
{
    var $tplInc = "";
    var $tplFile = "";
    var $pageUri = "";
    var $requestUri = "";
    var $tplDefault = "";
    var $tplTitle = "";
    var $modName = "";
    var $pageType = "";
    var $access = array();
    var $mAccess = array();
    var $pageUrl = "";
    var $modUrl = "";
    var $params = "";
    var $modIncUrl = "";
    var $modCore = false;

    public function getAccess($roleID = "")
    {
        $arr = array();
        global $DB, $MXADMINMENU;
        if ($roleID == "SUPER") {
            global $MXACCESS, $MXFORCENAV;
            foreach ($MXADMINMENU as $v) {
                if ($this->modName == $v["seoUri"])
                    $this->tplTitle = ucfirst($this->pageType) . " " . $v["menuTitle"];
                $this->mAccess[$v["seoUri"]] = $MXACCESS;
            }
            $DB->sql = "SELECT seoUri,menuTitle,forceNav FROM `" . $DB->pre . "x_admin_menu` WHERE status = ?";
            $DB->vals[] = 1;
            $DB->types = "i";
            $A = $DB->dbRows();
            if ($DB->numRows > 0) {
                foreach ($DB->rows as $m) {
                    $this->mAccess[$m["seoUri"]] = $MXACCESS;
                    if ($m["seoUri"] == $this->modName) {
                        $this->tplTitle = ucfirst($this->pageType) . " " . $m["menuTitle"];
                        $MXFORCENAV = $m["forceNav"];
                    }
                }
            }
            if ($this->mAccess[$this->modName])
                $arr = $this->mAccess[$this->modName];
        } else {
            $arr = array();
            if (intval($roleID)) {
                $DB->sql = "SELECT A.adminMenuID,A.accessType,M.seoUri,M.menuTitle FROM `" . $DB->pre . "x_admin_role_access` AS A 
                        LEFT JOIN `" . $DB->pre . "x_admin_menu` AS M ON M.adminMenuID=A.adminMenuID AND M.status = ? 
                        WHERE A.roleID=?";

                $DB->vals = array(1, $roleID);
                $DB->types = "ii";
                $A = $DB->dbRows();
                if ($DB->numRows > 0) {
                    foreach ($DB->rows as $m) {
                        if (array_key_exists($m["adminMenuID"], $MXADMINMENU)) {
                            $m["seoUri"] = $MXADMINMENU[$m["adminMenuID"]]["seoUri"];
                            $m["menuTitle"] = $MXADMINMENU[$m["adminMenuID"]]["menuTitle"];
                        }

                        $this->mAccess[$m["seoUri"]] = json_decode($m["accessType"]);
                        if ($m["seoUri"] == $this->modName)
                            $this->tplTitle = ucfirst($this->pageType) . " " . $m["menuTitle"];
                    }
                    if ($this->mAccess[$this->modName])
                        $arr = $this->mAccess[$this->modName];
                }
            }
        }
        return $arr;
    }

    private function setFiles()
    {
        global $MXPGFILE, $MXACTION;
        $fileName = "";
        $this->tplFile = ADMINPATH . "/core-admin/x-404.php";
        $this->tplTitle = "404 : Page not found";
        $this->access = $this->getAccess($_SESSION[SITEURL]["MXROLE"]);

        if ($this->access && isset($MXACTION[$this->pageType])) {
            if (in_array("view", $this->access)) {
                $arrC = array_intersect($MXACTION[$this->pageType], $this->access);
                if ($arrC)
                    $fileName = "x-" . $this->modName . "-" . $MXPGFILE[$this->pageType] . ".php";
            }
        }
        if ($fileName) {
            $mPath = "/mod/" . $this->modName . "/";
            $amPath = "/core-admin/mod/" . $this->modName . "/";
            if (file_exists(ADMINPATH . $amPath . $fileName) && is_file(ADMINPATH . $amPath . $fileName)) {
                $this->tplFile = ADMINPATH . $amPath . $fileName;
                $this->tplInc = ADMINPATH . $amPath . "x-" . $this->modName . ".inc.php";
                $this->modIncUrl = ADMINURL . $amPath . "x-" . $this->modName . ".inc.php";
                $this->modUrl = ADMINURL . $amPath;
                $this->modCore = true;
            } elseif (file_exists(ADMINPATH . $mPath . $fileName) && is_file(ADMINPATH . $mPath . $fileName)) {
                $this->tplFile = ADMINPATH . $mPath . $fileName;
                $this->tplInc = ADMINPATH . $mPath . "x-" . $this->modName . ".inc.php";
                $this->modIncUrl = ADMINURL . $mPath . "x-" . $this->modName . ".inc.php";
                $this->modUrl = ADMINURL . $mPath;
            } else {
                $this->tplFile = ADMINPATH . "/core-admin/x-404.php";
                $this->tplTitle = "404 : Page not found";
            }
            $this->pageUrl = ADMINURL . "/" . $this->pageUri . "/";
        }
    }

    public function setPage()
    {
        global $FOLDER, $MXSET;
        if ($_SERVER["QUERY_STRING"])
            $this->params = $_SERVER["QUERY_STRING"];
        $this->requestUri = str_replace($FOLDER, "", $this->requestUri);
        $this->requestUri = str_replace("/" . ADMINDIR, "", $this->requestUri);
        $arrU = parse_url($this->requestUri);
        $this->pageUri = basename($arrU["path"]);
        if (isAdminUser()) {
            if (!$this->pageUri || $this->pageUri == "login") {
                header("location:" . ADMINURL . "/$this->tplDefault/");
                exit;
            }
            $arrT = explode("-", $this->pageUri);
            $this->pageType = end($arrT);
            $this->modName = str_replace("-" . $this->pageType, "", $this->pageUri);
            $this->setFiles();
        } else if ($this->pageUri != "login") {
            header("location:" . ADMINURL . "/login/?redirect=" . urlencode($this->requestUri));
            exit;
        }
        $MXSET = array_merge($MXSET, array("MODINCURL" => $this->modIncUrl, "MODURL" => $this->modUrl));
    }
}
