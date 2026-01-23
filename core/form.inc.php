<?php

// ALL XTYPES = "text","password","textarea","select","editor","date", "time", "dateTime","captcha","autocomplete","file","radio","checkbox"
class mxForm
{
    var $validate, $incJs, $errMsg, $where, $param, $strCommon, $meta, $formType, $xAction, $types, $vals, $pageType, $modName;

    public function __construct()
    {
        global $TPL;
        $this->validate = array();
        $this->incJs = array();
        $this->errMsg = array();
        $this->where = "";
        $this->param = "";
        $this->strCommon = "";
        $this->meta = true;
        $this->formType = "";
        $this->xAction = "";
        $this->types = "";
        $this->vals = array();
        $this->pageType = $TPL->pageType;
        $this->modName = $TPL->modName;
    }

    private function mxFormJs($eleType = "")
    {
        $arrJsSet = array(
            "date" => array("ui", "dt"),
            "time" => array("ui", "dt"),
            "datetime" => array("ui", "dt"),
            "autocomplete" => array("ui", "ac"),
            "editor" => array("ck"),
            "file" => array("ui", "fl")
        );

        $arrJs = array(
            'ck' => '<script src="' . mxGetUrl(LIBURL . '/js/ckeditor/ckeditor.js') . '" type="text/javascript"></script><script>$(function() { initEditors(); }); </script>',
            'ui' => '<link href="' . mxGetUrl(LIBURL . '/js/jquery-ui/jquery-ui.min.css') . '" rel="stylesheet">
                     <script src="' . mxGetUrl(LIBURL . '/js/jquery-ui/jquery-ui.min.js') . '" type="text/javascript"></script>',
            'dt' => '<link href="' . mxGetUrl(LIBURL . '/js/jquery-ui/jquery-ui-timepicker-addon.min.css') . '" rel="stylesheet">
                     <script src="' . mxGetUrl(LIBURL . '/js/jquery-ui/jquery-ui-timepicker-addon.min.js') . '" type="text/javascript"></script>
                     <script>$(function() { initDateTime(); }); </script>',
            'fl' => '<script src="' . mxGetUrl(LIBURL . '/js/file-upload/jquery.fileupload.js') . '" type="text/javascript"></script>
                     <script src="' . mxGetUrl(LIBURL . '/js/file-upload/jquery.iframe-transport.js') . '" type="text/javascript"></script>
                     <link href="' . mxGetUrl(LIBURL . '/js/magnific-popup/magnific-popup.css') . '" rel="stylesheet">
                     <script src="' . mxGetUrl(LIBURL . '/js/magnific-popup/jquery.magnific-popup.min.js') . '" type="text/javascript"></script>
                     <script>$(function() { initMagnific(); }); </script>',
            'ac' => '<script src="' . mxGetUrl(COREURL . '/js/autocomplete.inc.js') . '" type="text/javascript"></script>',
        );

        if (array_key_exists($eleType, $arrJsSet)) {
            foreach ($arrJsSet[$eleType] as $jscss) {
                if (!isset($this->incJs[$jscss])) {
                    $this->incJs[$jscss] = $arrJs[$jscss];
                }
            }
        }
    }

    private function text($d)
    {
        return '<input type="text" name="' . $d["name"] . '" id="' . $d["id"] . '" value="' . $d["value"] . '"' . $d["attr"] . $d["class"] . ' title="' . $d["title"] . '" placeholder="' . $d["title"] . '" xtype="text" />';
    }

    private function autocomplete($d)
    {
        if (!isset($d["params"]) || $d["params"] == "")
            $d["params"] = array();
        $val = $d["value"];
        $str = "";
        if (isset($d["params"]["tag"]) && $d["params"]["tag"] == true) {
            $full = "";
            if (isset($d["params"]["full"]) && $d["params"]["full"] !== "") {
                $full = ' full';
            }
            if (!isset($d["params"]["tagwrap"]) || $d["params"]["tagwrap"] === "") {
                $str = '<ul class="mx-tag-wrap' . $full . '">' . setAutocompleteVal($d["value"]) . '</ul>';
            }
            $val = "";
        }
        $text = '<input type="text" autocomplete="off" name="' . $d["name"] . '" id="' . $d["id"] . '" value="' . $val . '" ' . $d["attr"] . $d["class"] . ' title="' . $d["title"] . '" placeholder="' . $d["title"] . '" xtype="autocomplete" params="' . htmlspecialchars(json_encode($d["params"]), ENT_QUOTES, 'UTF-8') . '" />' . $str;
        return $text;
    }

    private function textarea($d)
    {
        return '<textarea name="' . $d["name"] . '" id="' . $d["id"] . '"' . $d["attr"] . $d["class"] . ' title="' . $d["title"] . '" placeholder="' . $d["title"] . '" xtype="textarea">' . $d['value'] . '</textarea>';
    }

    private function editor($d)
    {
        if (!isset($d["params"]) || $d["params"] == "")
            $d["params"] = array();

        $str = '<textarea name="' . $d["name"] . '" id="' . $d["id"] . '"' . $d["attr"] . $d["class"] . ' title="' . $d["title"] . '" xtype="editor" params="' . htmlspecialchars(json_encode($d["params"]), ENT_QUOTES, 'UTF-8') . '">' . $d['value'] . '</textarea>';
        return $str;
    }

    private function file($d)
    {
        $strF = "";
        if (isset($d['value'][0]) && is_array($d["value"])) {
            $files = explode(",", $d['value'][0]);
            $id = trim($d['value'][1]);
            foreach ($files as $k => $v) {
                if (file_exists(UPLOADPATH . '/' . $d["udir"] . '/' . $v) && is_file(UPLOADPATH . '/' . $d["udir"] . '/' . $v)) {
                    $param = array("fl" => $v, "fld" => $d["nameF"], "id" => $id);
                    $strFile = getFile(array("path" => $d["udir"] . '/' . $v, "title" => $v));
                    $strDel = '';
                    if (isset($id) && $id != "")
                        $strDel = '<a href="#" class="del rs" onclick="return mxDelFile(this,' . htmlspecialchars(json_encode($param), ENT_QUOTES, 'UTF-8') . ');"></a>';
                    $strF .= '<li title="' . filesize(UPLOADPATH . '/' . $d["udir"] . '/' . $v) . ' : ' . $v . '">' . $strFile . $strDel . '
                                    <input type="hidden" name="O' . $d["name"] . '[]" value="' . $v . '" rowGrp="1" />
                              </li>';
                }
            }
        }
        if (!isset($d["params"]) || $d["params"] == "")
            $d["params"] = array();
        $str = '<div class="mx-file-upload' . $d["class"] . '" ' . $d["attr"] . ' xtype="file" params="' . htmlspecialchars(json_encode($d["params"]), ENT_QUOTES, 'UTF-8') . '">
                    <div class="drop"><a>Browse<br> OR <br> Drop Here</a><input type="file" name="' . $d["name"] . '" multiple /></div>
                    <ul>' . $strF . '</ul>
                </div>';
        return $str;
    }

    private function select($d)
    {

        if (isset($d["default"]) && $d["default"] === false)
            $default = '';
        else
            $default = '<option value="" class="default">--' . strtoupper($d['title']) . '--</option>';
        return '<div class="select-box' . $d["class"] . '"' . $d["attr"] . '><select name="' . $d['name'] . '" id="' . $d['id'] . '" title="' . $d["title"] . '" xtype="select">' . $default . $d['value'] . '</select></div>';
    }

    public function checkbox($d)
    {
        $str = "";
        if (is_array($d["value"])) {
            if (isset($d['value'][0])) {
                foreach ($d['value'][0] as $k => $v) {
                    $chkd = "";
                    if (isset($d['value'][1]) && in_array($k, $d['value'][1]))
                        $chkd = ' checked="checked"';
                    $str .= '<li><i class="chk">' . $v . ' <input type="checkbox" name="' . $d['name'] . '[]" value="' . $k . '"  ' . $chkd . ' rowGrp="1" /><em></em></i></li>';
                }
            }
        } else {
            $chkd = "";
            if ($d['value'] != "" && $d['value'] > 0)
                $chkd = ' checked="checked"';
            $str .= '<li><i class="chk">' . $d['title'] . ' <input type="checkbox" name="' . $d["name"] . '" value="1"' . $chkd . ' /><em></em></i></li>';
        }

        if (!isset($d["params"]) || $d["params"] == "")
            $d["params"] = array();


        $strSearch = "";
        $strChkAll = "";
        $strSChk = "";
        if (isset($d["params"]["search"]) && $d["params"]["search"]) {
            $strSearch = ' <input type="text" class="txt-chk-serach" placeholder="Serach"> ';
        }

        if (isset($d["params"]["checkall"]) && $d["params"]["checkall"]) {
            $strChkAll = ' <i class="chk"> All <input type="checkbox" class="chk-all"><em></em></i> ';
        }

        if ($strSearch !== "" || $strChkAll !== "") {
            $strSChk = '<div class="chkall-serach">' . $strSearch . $strChkAll . '</div>';
        }

        return $strSChk . '<ul class="mx-list' . $d["class"] . '" title="' . $d["title"] . '"' . $d["attr"]  . $d["class"] .  ' xtype="checkbox">' . $str . '</ul>';
    }

    public function radio($d)
    {
        $str = "";
        if (isset($d['value'][0])) {
            foreach ($d['value'][0] as $k => $v) {
                $chkd = "";
                if (isset($d['value'][1]) && trim($d['value'][1]) == "$k") {
                    $chkd = ' checked="checked"';
                }
                $str .= '<li><i class="rdo">' . $v . ' <input type="radio" name="' . $d['name'] . '" value="' . $k . '"' . $chkd . ' /><em></em></i></li>';
            }
        }

        if (!isset($d["params"]) || $d["params"] == "")
            $d["params"] = array();
        $strSearch = "";
        if (isset($d["params"]["search"]) && $d["params"]["search"] == true) {
            $strSearch = ' <div class="chkall-serach"><input type="text" class="txt-chk-serach" placeholder="Serach"></div>';
        }

        return $strSearch . '<ul title="' . $d["title"] . '" class="mx-list' . $d["class"] . '"' . $d["attr"] . ' xtype="radio">' . $str . '</ul>';
    }

    private function password($d)
    {
        return '<input type="password" name="' . $d["name"] . '" id="' . $d["id"] . '" value="' . $d["value"] . '"' . $d["attr"] . $d["class"] . '  placeholder="' . $d["title"] . '" title="' . $d["title"] . '" xtype="password" />';
    }

    private function date($d)
    {
        if (!isset($d["params"]) || $d["params"] == "")
            $d["params"] = array();
        $str = '<input type="text" name="' . $d["name"] . '" id="' . $d["id"] . '" value="' . $d["value"] . '"' . $d["attr"] . $d["class"]  . ' placeholder="' . $d["title"] . '" title="' . $d["title"] . '" xtype="date" params="' . htmlspecialchars(json_encode($d["params"]), ENT_QUOTES, 'UTF-8') . '" />';
        return $str;
    }

    private function time($d)
    {
        if (!isset($d["params"]) || $d["params"] == "")
            $d["params"] = array();
        $str = '<input type="text" name="' . $d["name"] . '" id="' . $d["id"] . '" value="' . $d["value"] . '"' . $d["attr"] . $d["class"]  . ' placeholder="' . $d["title"] . '" title="' . $d["title"] . '" xtype="time" params="' . htmlspecialchars(json_encode($d["params"]), ENT_QUOTES, 'UTF-8') . '" />';
        return $str;
    }

    private function datetime($d)
    {
        if (!isset($d["params"]) || $d["params"] == "")
            $d["params"] = array();
        $str = '<input type="text" name="' . $d["name"] . '" id="' . $d["id"] . '" value="' . $d["value"] . '"' . $d["attr"] . $d["class"] . ' placeholder="' . $d["title"] . '" title="' . $d["title"] . '" xtype="datetime" params="' . htmlspecialchars(json_encode($d["params"]), ENT_QUOTES, 'UTF-8') . '" />';
        return $str;
    }

    private function captcha($d)
    {
        return '<input type="text" name="' . $d["name"] . '" id="' . $d["id"] . '" value="' . $d["value"] . '"' . $d["attr"] . $d["class"] . ' placeholder="' . $d["title"] . '" title="' . $d["title"] . '" xtype="captcha" />';
    }

    private function hidden($d)
    {
        return '<input type="hidden" name="' . $d["name"] . '" id="' . $d["id"] . '" value="' . $d["value"] . '"' . $d['attr'] . $d["class"] . ' xtype="hidden" />';
    }

    private function mxstring($d)
    {
        return $d["value"];
    }

    public function getFormMeta()
    {
        global $MXSET;
        if ($MXSET["MULTILINGUAL"] == 1 && isset($_REQUEST["langCode"]) && trim($_REQUEST["langCode"]) != $MXSET["LANGDEFAULT"]) {
            return "";
        }

        $arrMeta = array("metaTitle" => "", "metaKeyword" => "", "metaDesc" => "");
        if ($this->pageType == "edit" && isset($_REQUEST["id"])) {
            $arrMeta = array_merge($arrMeta, mxGetMetaArray($this->modName, intval($_REQUEST["id"])));
        }

        if ($this->pageType == "view") {
            extract($arrMeta);
        } else {
            $metaTitle = '<input type="text" name="metaTitle" id="metaTitle" value="' . $arrMeta['metaTitle'] . '" title="Meta Title" placeholder="meta title" />';
            $metaKeyword = '<textarea name="metaKeyword" id="metaKeyword" rows="4" title="Meta Keywords"  placeholder="meta keyword">' . $arrMeta["metaKeyword"] . '</textarea>';
            $metaDesc = '<textarea name="metaDesc" id="metaDesc" rows="4" title="Meta Description"  placeholder="meta description">' . $arrMeta["metaDesc"] . '</textarea>';
        }

        return '<li><label>Meta Title</label>' . $metaTitle . '</li><li><label>Meta Keywords</label>' . $metaKeyword . '</li><li><label>Meta Description</label>' . $metaDesc . '</li>';
    }

    private function getTitle($v = array(), $type = "")
    {
        $em = "";
        if (strpos($v["validate"], "required") !== false)
            $em = ' <em>*</em>';
        $strTitle = $v["title"] . $em . $v["info"];
        if ($v["type"] == "checkbox" && !is_array($v["value"]) && $type == "") {
            $strTitle = "";
        }

        return $strTitle;
    }

    private function getField($v = array())
    {
        $strField = "";
        if (count($v) > 0) {
            $this->mxFormJs($v["type"]);
            if (isset($this->pageType) && $this->pageType !== "view") {
                if (isset($v["class"]) && $v["class"] != "") {
                    if (in_array($v["type"], array("select", "checkbox", "radio", "file"))) {
                        $v["class"] = " " . $v["class"];
                    } else {
                        $v["class"] = ' class="' . $v["class"] . '"';
                    }
                }
                $strField = $this->{$v["type"]}($v);
            } else {
                $arrTxt = array("text", "textarea", "editor", "date", "datetime", "time", "mxstring", "autocomplete");
                $arrSkip = array("password", "captcha", "hidden");
                if (!in_array($v["type"], $arrSkip)) {
                    if (in_array($v["type"], $arrTxt)) {
                        $strField = $v["value"];
                    } else {
                        if ($v["type"] == "select") {
                            preg_match('@(selected="selected">([^<]+)<\/option>)@', $v["value"], $arr);
                            if (isset($arr[2]))
                                $strField = $arr[2];
                        } else if ($v["type"] == "checkbox") {
                            if (is_array($v["value"])) {
                                if (isset($v["value"][0]) && isset($v["value"][1])) {
                                    if (is_array($v["value"][0]) && is_array($v["value"][1])) {
                                        $vals = array_intersect_key($v["value"][0], array_flip($v["value"][1]));
                                        $strField = implode(", ", $vals);
                                    } else {
                                        $strField = $v["value"][0][$v["value"][1]];
                                    }
                                }
                            } else {
                                $flg = "No";
                                if (isset($v["value"]))
                                    $flg = "YES";
                                $strField = $flg . " : " . $v["title"];
                            }
                        } else if ($v["type"] == "radio") {
                            if (isset($v["value"][0]) && isset($v["value"][1]))
                                $strField = $v["value"][0][$v["value"][1]];
                        } else if ($v["type"] == "file") {
                            if (isset($v['value'][0]) && trim($v['value'][0]) !== "") {
                                $files = explode(",", $v['value'][0]);
                                $strF = "";
                                foreach ($files as $k => $img) {
                                    $filePath = UPLOADPATH . '/' . $v["udir"] . '/' . $img;
                                    if (file_exists($filePath) && is_file($filePath)) {
                                        $strFile = getFile(array("path" => $v["udir"] . '/' . $img, "title" => $img));
                                        $strF .= '<li title="' . $img . '">' . $strFile . '</li>';
                                    }
                                }
                                $strField = '<div class="mx-file-upload">
                                                <ul>' . $strF . '</ul>
                                            </div>';
                            }
                        }
                    }
                }
            }
        }
        return $strField;
    }

    private function skipField($lang = false)
    {
        $flg = false;
        if (isset($lang)) {
            global $MXSET;
            if ($MXSET["MULTILINGUAL"] == 1 && isset($_REQUEST["langCode"]) && trim($_REQUEST["langCode"]) !== "") {
                if (trim($_REQUEST["langCode"]) !== $MXSET["LANGDEFAULT"]) {
                    if (!$lang)
                        $flg = true;
                }
            }
        }
        return $flg;
    }

    public function getFormS($arr = array())
    {
        $strFrm = "";
        $strH = "";
        if (count($arr)) {
            foreach ($arr as $v) {
                if ($v) {
                    $defaults = array("type" => "text", "name" => "", "value" => "", "title" => "", "validate" => "", "msg" => "", "attr" => "", "attrp" => "", "class" => "", "info" => "", "default" => true, "params" => array(), "where" => "", "dtype" => "");
                    $v = array_merge($defaults, $v);

                    $v["id"] = $v["name"];
                    if (isset($v["type"]) && $v["type"] == 'hidden') {
                        $strH .= $this->{$v["type"]}($v);
                    } else {
                        $this->mxFormJs($v["type"]);
                        if ($v["type"] !== "select") {
                            if ($v["type"] == "text" || $v["type"] == "date" || $v["type"] == "autocomplete")
                                if (isset($_GET[$v["name"]]))
                                    $v["value"] = htmlspecialchars($_GET[$v["name"]]);
                        }
                        if (isset($_GET[$v["name"]]) && trim($_GET[$v["name"]]) !== "") {
                            if ($v["dtype"] !== "" && $v["where"] !== "") {
                                array_push($this->vals, htmlspecialchars($_GET[$v["name"]]));
                                $this->types .= $v["dtype"];
                                $this->where .= " " . $v["where"];
                            }
                            $this->param .= "&" . $v["name"] . "=" . htmlspecialchars($_GET[$v["name"]]);
                        }
                        if (isset($v["type"]))
                            $strFrm .= '<li' . $v['attrp'] . ' title="' . $v["title"] . '">' . $this->{$v["type"]}($v) . '</li>';
                    }
                }
            }
            if ($strFrm) {
                global $MXSHOWREC;
                if (!isset($_GET["btnSearch"]) && count($_GET) > 0) {
                    foreach ($_GET as $k => $v)
                        $strH .= '<input type="hidden" name="' . htmlspecialchars($k) . '" value="' . htmlspecialchars($v) . '" />';
                }
                $strFrm = '<div class="search-data">
                                <a href="#" class="del"></a>
                                <form name="frmSearch" id="frmSearch" action="" method="get">
                                    <ul>' . $strFrm . '
                                        <li>
                                            <input type="submit" name="btnSearch" id="btnSearch" value="&#xf0a9;" class="btn search fa">
                                        </li>
                                        <li>
                                            <input type="button" name="btnRest" id="btnReset" value="&#xf01e;" class="btn refresh fa" />
                                            <input type="hidden" name="showRec" id="showRec" value="' . $MXSHOWREC . '" />
                                        </li>
                                    </ul>' . implode("\n", $this->incJs) . $this->strCommon . $strH . '
                                </form>
                            </div>';
            }
        }
        return $strFrm;
    }

    function getFormG($params = array())
    {
        global $MXMOD;
        $arrDefauls = array("flds" => array(), "vals" => array(), "type" => 0, "add" => true, "del" => true, "callback" => "", "tfoot" => "", "class" => "");

        if (isset($params) && is_array($params)) {
            $params = array_merge($arrDefauls, $params);
            extract($params);
        }
        $rec = 1;
        if (isset($vals) && is_array($vals)) {
            $rec = count($vals);
        }
        if ($rec < 1) {
            $rec = 1;
        }

        $str = "";
        $strTH = "";

        for ($i = 0; $i < $rec; $i++) {
            $strH = "";
            $strTD = "";
            $placeHidden = "PLACEHIDDEN";
            foreach ($flds as $k => $v) {
                if (count($v)) {
                    $defaults = array("type" => "text", "name" => "", "value" => "", "title" => "", "validate" => "", "msg" => "", "attr" => "", "attrp" => "", "class" => "", "info" => "", "default" => true, "params" => array(), "where" => "");
                    $v = array_merge($defaults, $v);

                    if ($this->skipField($v["lang"] ?? false))
                        continue;

                    $v["id"] = $v["name"] . "_" . $i;
                    if ($v["type"] == 'hidden') {
                        $name = $v["name"];
                        $v["name"] = $v["name"] . "[$i]";
                        if (isset($vals[$i][$name]))
                            $v["value"] = $vals[$i][$name];
                        $strH .= $this->getField($v);
                    } else {
                        $validate = "";
                        if ($v["validate"]) {
                            $validate .= ' validateG="' . $v["validate"] . '" msg="' . $v["msg"] . '" title="' . $v["title"] . '"';
                            $this->validate["validateG"][$v["name"]][$i] = array("func" => $v["validate"], "msg" => $v["msg"], "params" => $v["params"]);
                        }

                        if ($v["type"] == 'file' && (!isset($v["udir"]) || $v["udir"] == "")) {
                            $udir = "";
                            if (isset($MXMOD["UDIR"][$v['name']]))
                                $udir = $MXMOD["UDIR"][$v['name']];
                            if (!isset($udir) || $udir == "")
                                $udir = $this->modName;
                            $v["udir"] = $udir;
                        }
                        if (isset($vals[$i][$v["name"]]))
                            $v["value"] = $vals[$i][$v["name"]];
                        $v["nameF"] = $v["name"];
                        $v["name"] = $v["name"] . "[$i]";

                        $label = '<label>' . $this->getTitle($v) . '</label>';
                        if (isset($v['nolabel']) && $v['nolabel'])
                            $label = '';

                        if ($type == 1) {
                            $strTD .= '<li' . $v['attrp'] . $validate . '>' . $label . $this->getField($v, "G") . $placeHidden . '</li>';
                        } else {
                            $strTD .= '<td' . $v['attrp'] . $validate . '>' . $this->getField($v) . $placeHidden .  '</td>';
                            if ($i == 0)
                                $strTH .= '<th' . $v['attrp'] . '>' . $this->getTitle($v, "G") . '</th>';
                        }
                        $placeHidden = "";
                    }
                }
            }

            $strTD = str_replace("PLACEHIDDEN", $strH, $strTD);

            $btnDel = '';
            if (isset($this->pageType) && $this->pageType !== "view" && $del)
                $btnDel = '<a href="#" rel="" class="del row" data-callback="' . $callback . '"></a>';

            //if (isset($this->pageType) && $this->pageType !== "view" && $del)

            if ($type == 1) {
                if ($add || $del)
                    $btnDel = '<li  class="del-grp">' . $btnDel . '</li>';

                $str .= '<ul class="grp-set">' . $strTD . $btnDel . '</ul>';
            } else {
                if ($add || $del)
                    $btnDel = '<td align="center" width="1%">' . $btnDel . '</td>';

                $str .= '<tr class="grp-set">' . $strTD . $btnDel . '</tr>';
            }
        }

        $strTFoot = "";
        if (isset($tfoot) && $tfoot != "") {
            $strTFoot = "<tfoot>" . $tfoot . "</tfoot>";
        }

        $btnAdd = '';
        if (isset($this->pageType) && $this->pageType !== "view" && $add)
            $btnAdd = '<a href="#" class="add add-set" data-callback="' . $callback . '"></a>';

        if ($type == 1) {
            if ($add || $del)
                $btnAdd = '<div class="add-grp">' . $btnAdd . '</div>';

            return '<div class="grp-wrap' . $class . '">' . $btnAdd . $str . '</div>';
        } else {
            if ($add || $del)
                $btnAdd = '<th align="center" class="add-grp">' . $btnAdd . '</th>';

            return '<table class="grp-wrap tbl-list' . $class . '" border="0" cellspacing="0" cellpadding="8" width="100%"><thead><tr>' . $strTH . $btnAdd . '</tr></thead><tbody>' . $str . '</tbody>' . $strTFoot . '</table>';
        }
    }

    public function getForm($arr = array(), $arrOrg = array())
    {
        if (isset($arrOrg) && count($arrOrg) > 0)
            $arr = $this->getOrgDD($arr, $arrOrg);

        $strFrm = "";
        $strH = '';
        $this->where = "";

        if (is_array($arr)) {
            global $MXMOD;
            foreach ($arr as $v) {
                if (is_array($v)) {
                    $defaults = array("type" => "text", "name" => "", "value" => "", "title" => "", "validate" => "", "msg" => "", "attr" => "", "attrp" => "", "class" => "", "info" => "", "default" => "", "params" => array(), "where" => "");
                    $v = array_merge($defaults, $v);

                    if ($this->skipField($v["lang"] ?? false))
                        continue;

                    $v["id"] = $v["name"];
                    if ($v["type"] == 'hidden') {
                        $strH .= $this->{$v["type"]}($v);
                    } else {
                        $this->mxFormJs($v["type"]);
                        if (isset($v["validate"])) {
                            $v["attrp"] .= ' validate="' . $v["validate"] . '" msg="' . $v["msg"] . '" title="' . $v["title"] . '"';
                            $this->validate[$v["name"]] = array("func" => $v["validate"], "msg" => $v["msg"], "params" => $v["params"]);
                        }
                        $v['nameF'] = $v['name'];
                        if ($v["type"] == 'file') {
                            $udir = "";
                            if (!isset($v["udir"]) && isset($MXMOD["UDIR"][$v['name']]))
                                $udir = $MXMOD["UDIR"][$v['name']];
                            else if (!isset($v["udir"]) || $v["udir"] == "")
                                $udir = $this->modName;
                            $v["udir"] = $udir;
                        }
                        $label = '<label>' . $this->getTitle($v) . '</label>';
                        if (isset($v['nolabel']) && $v['nolabel'])
                            $label = '';

                        $strFrm .= '<li' . $v['attrp'] . '>' . $label . $this->getField($v) . '</li>';
                    }
                }
            }
        }
        return $strFrm . $strH;
    }

    public function closeForm()
    {
        global $MXSET;
        $strFrm = "";
        if ($this->pageType !== "view") {
            global $MXMOD;
            if (!$this->xAction) {
                if ($this->pageType == "edit")
                    $this->xAction = "UPDATE";
                else
                    $this->xAction = "ADD";
            }

            $langCode = $MXSET["LANGDEFAULT"];
            if (isset($_REQUEST["langCode"]) && trim($_REQUEST["langCode"]) !== "")
                $langCode = htmlspecialchars(trim($_REQUEST["langCode"]));

            $strHidden = '<input type="hidden" name="xAction" id="xAction" value="' . $this->xAction . '" />
                          <input type="hidden" name="modName" id="modName" value="' . $this->modName . '" />
                          <input type="hidden" name="pageType" id="pageType" value="' . $this->pageType . '" />
                          <input type="hidden" name="langCode" id="langCode" value="' . $langCode . '" />
                        <div class="mxdialog mx-file-browser" style="display: none;">
                            <div class="body" style="width: 80%; height: 90%;">
                                <a href="#" class="close del rl"></a>
                                <iframe id="mx-file-browser"  style="width: 100%; height: 100%;"></iframe>
                            </div>
                        </div>';

            if (isset($_REQUEST["id"]) && trim($_REQUEST["id"]) !== "")
                $strHidden .= '<input type="hidden" name="' . $MXMOD["PK"] . '" id="' . $MXMOD["PK"] . '" value="' . intval($_REQUEST["id"]) . '" />';

            if (isset($_REQUEST["parentLID"]) && trim($_REQUEST["parentLID"]) !== "")
                $strHidden .= '<input type="hidden" name="parentLID" id="parentLID" value="' . intval($_REQUEST["parentLID"]) . '" />';

            $strFrm = $strHidden . implode("\n", $this->incJs) . $this->strCommon;

            //DOn't delete below lines
            // if ($this->validate) {
            //     $_SESSION[SITEURL][$this->modName]["mxValidate"] = $this->validate;
            // }
        }
        return $strFrm;
    }

    public function getOrgDD($arr = array(), $arrOrg = array())
    {
        global $SKIPMODORG;
        if ($_SESSION[SITEURL]['LOGINTYPE'] === "backend" && !in_array($this->modName, $SKIPMODORG)) {

            global $MXSET;
            if (isset($MXSET["MULTIORG"]) && $MXSET["MULTIORG"] == 1) {
                $orgID = $orgIDL = $parentID = 0;
                if (isset($_SESSION[SITEURL]['ORGID']) && $_SESSION[SITEURL]['ORGID'] > 0) {
                    $orgIDL = $_SESSION[SITEURL]['ORGID'];
                    if (isset($_SESSION[SITEURL]['ORGDATA']["parentID"]) &&  $_SESSION[SITEURL]['ORGDATA']["parentID"] > 0)
                        $parentID = $_SESSION[SITEURL]['ORGDATA']["parentID"];
                }

                if (isset($arrOrg["orgID"]) && intval($arrOrg["orgID"]) > 0)
                    $orgID = $arrOrg["orgID"];
                else
                    $orgID = $orgIDL;

                $arrO = array();
                global $DB;
                $whereOrg = $strOptOrg = "";
                $DB->vals = array(1);
                $DB->types = "i";

                if (isset($_SESSION[SITEURL]['ORGIDS']) && $_SESSION[SITEURL]['ORGIDS'] > 1) {
                    if ($orgIDL > 0) {
                        array_push($DB->vals, $orgIDL);
                        array_push($DB->vals, $orgID);
                        $DB->types .= "ii";
                        $whereOrg = " AND (parentID=? OR orgID=?)";
                    }
                }

                if ($_SESSION[SITEURL]['MXID'] === "SUPER" || $whereOrg != "") {
                    $DB->sql = "SELECT orgID,orgName,parentID FROM `" . $DB->pre . "x_organization` WHERE status=?" . $whereOrg;
                    $data = $DB->dbRows();
                    if ($DB->numRows > 0)
                        $strOptOrg = getTreeDD($data, "orgID", "orgName", "parentID", $orgID);

                    $arrO = array("type" => "select", "name" => "orgID", "value" => $strOptOrg, "title" => "Organization", "validate" => "required");
                } else {
                    $arrO = array("type" => "hidden", "name" => "orgID", "value" => $orgID);
                }

                if (count($arrO) > 0) {
                    if (isset($arrOrg["orgFld"]) && is_array($arrOrg["orgFld"]))
                        $arrO = array_merge($arrO, $arrOrg["orgFld"]);
                    array_unshift($arr, $arrO);
                }
            }
        }
        return $arr;
    }
}
