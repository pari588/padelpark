<script language="javascript" type="text/javascript" src="<?php echo mxGetUrl($TPL->modUrl . 'setting.inc.js'); ?>"></script>
<style>
    ul.tbl-ignore {
        max-height: 305px;
        overflow-y: auto;
    }

    div.show-color {
        height: 25px;
        width: calc(100% - 15px);
        background-color: #e64446;
        position: absolute;
        right: 13px;
        bottom: -32px;
    }

    .log-tbl i {
        float: right;
        margin-top: 5px;
    }
</style>
<?php
$D = $MXSET;
$optMenuWhere = array("sql" => "status=?", "types" => "i", "vals" => array(1));
$optDefaultPage  = getTableDD(["table" => $DB->pre . "x_admin_menu", "key" => "seoUri", "val" => "menuTitle", "selected" => ($D["DEFAULTPAGE"] ?? ""), "where" => $optMenuWhere, "org" => false, "lang" => false]);

$arrTimeZone  = ["data" => generatTtimezoneList()];
$optTimeZone = getArrayDD(["data" => $arrTimeZone, "selected" => ($D['TIMEZONE'] ?? ""), "org" => false, "lang" => false]);

$optLangWhere = array("sql" => "status=?", "types" => "i", "vals" => array(1));
$optLanguage  = getTableDD(["table" => $DB->pre . "x_language", "key" => "langPrefix", "val" => "langName", "selected" => ($D["LANGDEFAULT"] ?? ""), "where" => $optLangWhere, "org" => false, "lang" => false]);

//----------Log Start----------------
$strBtn = "";
$strBtnDelLog = "";
if ($_SESSION[SITEURL]["MXID"] == "SUPER") {
    $strBtn = '<a href="#" class="fa-reset btn d"> RESET DEFAULT</a>';
    $strBtnDelLog = '<a href="#" class="btn fa-trash-o c"> DELETE LOG</a> ';
}

$arrTables = mxGetAllTables();
unset($arrTables['mx_x_log_action']);
unset($arrTables['mx_x_log_request']);

$strActionLog = $strRequestLog = "";

$DB->sql = "SELECT table_name, round(((data_length + index_length) / 1024 / 1024), 2) `size_mb` 
            FROM information_schema.TABLES 
            WHERE table_schema = '$DBNAME' AND table_name IN ('mx_x_log_action','mx_x_log_request')";
$data = $DB->dbRows();
foreach ($data as $d) {
    if ($d["table_name"] == "mx_x_log_action")
        $strActionLog = "ACTION LOG: " . ($d["size_mb"]-0.02) . " MB";
    if ($d["table_name"] == "mx_x_log_request")
        $strRequestLog = "REQUEST LOG: " . ($d["size_mb"]-0.02) . " MB";
}
//----------Log End----------------

$arrGeneral = array(
    array("type" => "text", "name" => "PAGETITLE", "value" => $D["PAGETITLE"] ?? "", "title" => "Page Title", "validate" => "required"),
    array("type" => "select", "name" => "DEFAULTPAGE", "value" => $optDefaultPage, "title" => "Landing Page", "validate" => "required"),
    array("type" => "file", "name" => "FAVICON", "value" => array($D["FAVICON"], $D["FAVICON"] ?? ""), "title" => "Favicon", "validate" => "required", "params" => array("EXT" => "png"), "info" => '<span class="info">PNG 20px X 20px</span>'),
    array("type" => "file", "name" => "LOADERIMAGE", "value" => array($D["LOADERIMAGE"] ?? "", $D["LOADERIMAGE"]), "title" => "Preloader", "validate" => "required", "params" => array("EXT" => "png"), "info" => '<span class="info">PNG 300px X 300px</span>'),
);

$arrTheme = array(
    array("type" => "file", "name" => "LOGOLIGHT", "value" => array($D["LOGOLIGHT"] ?? "", $D["LOGOLIGHT"] ?? ""), "title" => "Logo Light", "validate" => "required", "params" => array("EXT" => "png"), "info" => '<span class="info">PNG 460px X 200px</span>', "attrp" => ' class="c2"'),
    array("type" => "text", "name" => "COLORLIGHT", "value" => $D["COLORLIGHT"], "title" => "Color Light", "validate" => "required", "info" => '<span class="info">E.g. (e64446)</span> <div class="show-color"></div>', "attrp" => ' class="c2"', "class" => "txt-color"),
    array("type" => "file", "name" => "LOGOMODERATE", "value" => array($D["LOGOMODERATE"] ?? "", $D["LOGOMODERATE"] ?? ""), "title" => "Logo Moderate", "validate" => "required", "params" => array("EXT" => "png"), "info" => '<span class="info">PNG 460px X 200px</span>', "attrp" => ' class="c2"'),
    array("type" => "text", "name" => "COLORMODERATE", "value" => $D["COLORMODERATE"], "title" => "Color Moderate", "validate" => "required", "info" => '<span class="info">E.g. (e64446) <div class="show-color"></div></span>', "attrp" => ' class="c2"', "class" => "txt-color"),
    array("type" => "file", "name" => "LOGODARK", "value" => array($D["LOGODARK"], $D["LOGODARK"]), "title" => "Logo Dark", "validate" => "required", "params" => array("EXT" => "png"), "info" => '<span class="info">PNG 460px X 200px</span>', "attrp" => ' class="c2"'),
    array("type" => "text", "name" => "COLORDARK", "value" => $D["COLORDARK"], "title" => "Color Dark", "validate" => "required", "info" => '<span class="info">E.g. (e64446) <div class="show-color"></div></span>', "attrp" => ' class="c2"', "class" => "txt-color"),
);

$arrFiles = array(
    array("type" => "text", "name" => "FILEIMAGE", "value" => $D["FILEIMAGE"], "title" => "File Types", "validate" => "required", "info" => '<span class="info">Eg. jpg|png</span>'),
    array("type" => "text", "name" => "MAXFILES", "value" => $D["MAXFILES"], "title" => "Max No of Files", "validate" => "required,number", "attrp" => ' class="c2"'),
    array("type" => "text", "name" => "MAXSIZE", "value" => $D["MAXSIZE"], "title" => "Max Size in MB", "validate" => "required,number", "attrp" => ' class="c2"')
);

$arrMultiLang = array(
    array("type" => "checkbox", "name" => "MULTILINGUAL", "value" => $D["MULTILINGUAL"], "title" => "Multilingual", "nolabel" => false, "attrp" => ' class="c2"'),
    array("type" => "checkbox", "name" => "LANGTYPE", "value" => $D["LANGTYPE"], "title" => "Multi Table", "nolabel" => false, "attrp" => ' class="c2"'),
    array("type" => "select", "name" => "LANGDEFAULT", "value" => $optLanguage, "title" => "Site Default Language", "validate" => "required", "default" => false)
);

$arrMultiOrg = array(
    array("type" => "checkbox", "name" => "MULTIORG", "value" => $D["MULTIORG"], "title" => "Multi Organization", "nolabel" => false),
    array("type" => "text", "name" => "ORGPARENTMOD", "value" => $D["ORGPARENTMOD"], "title" => "Org Parent Mod",  "attrp" => ' class="c2"'),
    array("type" => "text", "name" => "ORGCHILDMOD", "value" => $D["ORGCHILDMOD"], "title" => "Org Child Mod",  "attrp" => ' class="c2"')
);

$arrDateTime = array(
    array("type" => "select", "name" => "TIMEZONE", "value" => $optTimeZone, "title" => "Time Zone", "validate" => "required"),
    array("type" => "text", "name" => "DATEFORMAT", "value" => $D["DATEFORMAT"], "title" => "Date Format", "validate" => "required", "attrp" => ' class="c2"'),
    array("type" => "text", "name" => "TIMEFORMAT", "value" => $D["TIMEFORMAT"], "title" => "Time Format", "validate" => "required", "attrp" => ' class="c2"'),
);

$arrRobots = array(
    array("type" => "textarea", "name" => "ROBOTSD", "value" => $D["ROBOTSD"], "title" => "Robots.txt DEV,DEMO", "validate" => "required", "attr" => ' style="height:160px;"'),
    array("type" => "textarea", "name" => "ROBOTSL", "value" => $D["ROBOTSL"], "title" => "Robots.txt LIVE", "validate" => "required", "attr" => ' style="height:160px;"')
);

$arrLog = array(
    array("type" => "checkbox", "name" => "LOGIGNORETBL", "value" => array($arrTables, explode(",", ($D["LOGIGNORETBL"] ?? ""))), "title" => "Ignore Log Action Table", "class" => "c4 small tbl-ignore", "nolabel" => true),
    array("type" => "text", "name" => "LOGREQUESTDAYS", "value" => $D["LOGREQUESTDAYS"], "title" => "LOG REQUEST DAYS", "validate" => "required,number", "info" => '<span class="info">0 to stop</span>', "attrp" => ' class="c4"'),
    array("type" => "text", "name" => "LOGACTIONDAYS", "value" => $D["LOGACTIONDAYS"], "title" => "Log Action DAYS", "validate" => "required", "default" => false, "info" => '<span class="info">0 to stop</span>', "attrp" => ' class="c4"'),
    array("type" => "mxstring", "value" => $strBtnDelLog . '<a href="#" class="btn fa-reset o"> OPTIMIZE LOG</a>&nbsp;&nbsp;&nbsp; <a href="#" class="btn fa-reset j"> RESET JS KEY</a> <input type="hidden" name="JSKEY" id="JSKEY" value="' . $D["JSKEY"] . '" /><span class="info jskey"> JS KEY: ' . $D["JSKEY"] . '</span>', "attrp" => ' class="c2"')
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav('', $strBtn, array("add", "list", "trash")); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f20">
            <h2 class="form-head">ADMIN GENERAL SETTING</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrGeneral); ?>
            </ul>
        </div>
        <div class="wrap-form f30">
            <h2 class="form-head">ADMIN THEME SETTING</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrTheme); ?>
            </ul>
        </div>
        <div class="wrap-form f25">
            <h2 class="form-head">DATE/TIME & FILE SETTING</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrDateTime); ?>
            </ul>
            <h2 class="form-head">FILE SETTING</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFiles); ?>
            </ul>
        </div>
        <div class="wrap-form f25">
            <h2 class="form-head">MULTILINGUAL SETTING</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrMultiLang); ?>
            </ul>
            <h2 class="form-head">MULTI ORGANIZATION SETTING</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrMultiOrg); ?>
            </ul>
        </div>
        <div class="wrap-form f20">
            <h2 class="form-head">ROBOTS SETTING</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrRobots); ?>
            </ul>
        </div>
        <div class="wrap-form f80">
            <h3 class="form-head log-tbl"> <i class="chk">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; IGNORE ALL <input type="checkbox" value="1" class="ignore-all"><em></em></i>LOG SETTING <span class="info">Select tables to ignore log</span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(<?php echo $strActionLog;?>)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(<?php echo $strRequestLog;?>)</h3>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrLog); ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>