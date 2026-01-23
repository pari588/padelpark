<script language="javascript" type="text/javascript" src="<?php echo mxGetUrl($TPL->modUrl . 'template.inc.js'); ?>"></script>
<style>
    div.section {
        float: left;
        width: 100%;
    }
</style>
<?php
$fileMod = 0;
$modDir = $seoUri = "";
$D = array("seoUri" => "", "modType" => "", "metaKey" => "", "tblMaster" => "",  "pkMaster" => "", "titleMaster" => "", "tplFileCol" => "", "titleMaster" => "", "tblDetail" => "", "pkDetail" => "", "titleDetail" => "", "metaKeyD" => "", "xOrder" => "");
extract($_GET);

if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    if (isset($modDir) && isset($seoUri) && isset($fileMod)) {
        $DB->types = "iss";
        $DB->vals = array(1, $modDir, $seoUri);
        $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND modDir=? AND seoUri=? ORDER BY xOrder ASC";
        $DB->dbRow();
        if ($DB->numRows > 0) {
            $D = $DB->row;
        }
    }
}


$arrModType = ["data" => [0 => "Dynamic", 1 => "Static"]];
$arrTables["data"] = array();
$DB->sql = "SHOW TABLES";
$data = $DB->dbRows();
if ($DB->numRows > 0) {
    foreach ($data as $d) {
        foreach ($d as $tName) {
            $tName = str_replace("mx_", "", $tName);
            if (strpos($tName, 'x_') === false) {
                $arrTables["data"]["$tName"] = $tName;
            }
        }
    }
}

$arrFieldM["data"] = array();
$arrFieldD["data"] = array();

if (isset($D["tblMaster"]) && $D["tblMaster"] != "") {
    $arrFieldM["data"] = mxGetTableFlds($DB->pre . $D["tblMaster"]);
}

if (isset($D["tblDetail"]) && $D["tblDetail"] != "") {
    $arrFieldD["data"] = mxGetTableFlds($DB->pre . $D["tblDetail"]);
}


$hideD = "";
$hideS = "";
$readOnly = "";

if (!isset($D["modType"]) || $D["modType"] == "") {
    $D["modType"] = 0;
}

if (isset($D["modType"]) && $D["modType"] == 0) {
    $hideS = ' style="display: none;"';
} else {
    $readOnly = ' readonly="readonly"';
    $hideD = ' style="display: none;"';
}

if (!isset($D["metaKey"]) || trim($D["metaKey"]) == "") {
    $D["metaKey"] = $modDir . "/" . $seoUri;
}

if (!isset($D["metaKeyD"]) || trim($D["metaKeyD"]) == "") {
    if (isset($D["tblDetail"]) && trim($D["tblDetail"]) != "") {
        $D["metaKeyD"] = $D["tblDetail"];
    }
}

$arrLang = array(array("langPrefix" => $MXSET["LANGDEFAULT"]));
if ($MXSET["MULTILINGUAL"] == 1) {
    //  print_r($MXLANGS);
    if (count($MXLANGS) > 0) {
        $arrLang = $MXLANGS;
    }
}

$arrForm1 = array(
    array("type" => "select", "name" => "modType", "value" => getArrayDD(["data" => $arrModType, "selected" => $D['modType'] ?? "", "org" => false, "lang" => false]), "title" => "MODULE TYPE", "validate" => "required", "attrp" => ' class="c4"'),
    array("type" => "text", "name" => "seoUri", "value" => $seoUri, "title" => "Module seoUri", "validate" => "required", "attrp" => ' class="c4"'),
    array("type" => "text", "name" => "metaKey", "value" => $D["metaKey"], "title" => "Meta Key", "validate" => "required", "attrp" => ' class="c4"', "attr" => $readOnly),
    array("type" => "text", "name" => "xOrder", "value" => $D["xOrder"], "title" => "Priority",  "attrp" => ' class="c4"'),
    array("type" => "hidden", "name" => "modDir", "value" => $modDir),
    array("type" => "hidden", "name" => "fileMod", "value" => $fileMod),
    array("type" => "hidden", "name" => "modSeoUri", "value" => $modDir . "/" . $seoUri)
);

$arrForm2 = array(
    array("type" => "select", "name" => "tblMaster", "value" => getArrayDD(["data" => $arrTables, "selected" => $D['tblMaster'] ?? "", "org" => false, "lang" => false]), "title" => "TABLE LANDING PAGE",  "attrp" => ' class="c4"'),
    array("type" => "select", "name" => "pkMaster", "value" => getArrayDD(["data" => $arrFieldM, "selected" => $D['pkMaster'] ?? "", "org" => false, "lang" => false]), "title" => "PRIMARY KEY LANDING PAGE",  "attrp" => ' class="c4"'),
    array("type" => "select", "name" => "titleMaster", "value" => getArrayDD(["data" => $arrFieldM, "selected" => $D['titleMaster'] ?? "", "org" => false, "lang" => false]), "title" => "TITLE COLUMN LANDING PAGE",  "attrp" => ' class="c4"'),
    array("type" => "select", "name" => "tplFileCol", "value" => getArrayDD(["data" => $arrFieldM, "selected" => $D['tplFileCol'] ?? "", "org" => false, "lang" => false]), "title" => "TPL FILE COLUMN LANDING PAGE",  "attrp" => ' class="c4"'),
);


$arrForm4 = array(
    array("type" => "select", "name" => "tblDetail", "value" => getArrayDD(["data" => $arrTables, "selected" => $D['tblDetail'] ?? "", "org" => false, "lang" => false]), "title" => "TABLE DETAIL PAGE",  "attrp" => ' class="c4"'),
    array("type" => "select", "name" => "pkDetail", "value" => getArrayDD(["data" => $arrFieldD, "selected" => $D['pkDetail'] ?? "", "org" => false, "lang" => false]), "title" => "PRIMARY KEY DETAIL PAGE",  "attrp" => ' class="c4"'),
    array("type" => "select", "name" => "titleDetail", "value" => getArrayDD(["data" => $arrFieldD, "selected" => $D['titleDetail'] ?? "", "org" => false, "lang" => false]), "title" => "TITLE COLUMN DETAIL PAGE",  "attrp" => ' class="c4"'),
    array("type" => "text", "name" => "metaKeyD", "value" => $D["metaKeyD"], "title" => "Meta Key Detail",  "attrp" => ' class="c4"'),
);


$MXFRM = new mxForm();
//$MXFRM->xAction = $xAction;
?>
<div class="wrap-right">
    <?php echo getPageNav("", "", array("add", "trash")); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form">
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm1); ?>
            </ul>
            <?php if ($fileMod == 0) { ?>
                <h2 class="form-head">DETAILS PAGE INFO</h2>
                <ul class="tbl-form">
                    <?php echo $MXFRM->getForm($arrForm4); ?>
                </ul>
            <?php } ?>
            <div class="section dynamic" <?php echo $hideD; ?>>
                <h2 class="form-head">LANDING PAGE INFO</h2>
                <ul class="tbl-form">
                    <?php echo $MXFRM->getForm($arrForm2); ?>
                </ul>
            </div>
            <div class="section static" <?php echo $hideS; ?>>
                <h2 class="form-head">META DETAILS FOR STATIC PAGE</h2>
                <?php
                foreach ($arrLang as $lng) {
                    $arrMeta = array("metaTitle" => "", "metaKeyword" => "", "metaDesc" => "");
                    if ($D["metaKey"] !== "") {
                        $arrMeta = mxGetMetaArray($D["metaKey"], 0, $lng["langPrefix"]);
                    }
                ?>
                    <ul class="tbl-form">
                        <li class="c4" title="META LANGUAGE"><input type="text" name="langCodeT[]" id="langCode" value="<?php echo $lng["langPrefix"]; ?>" title="META LANGUAGE" placeholder="META LANGUAGE" xtype="text" readonly></li>
                        <li class="c4" title="META TITLE LANDING PAGE"><input type="text" name="metaTitleT[]" class="metaTitle" value="<?php echo $arrMeta["metaTitle"]; ?>" title="META TITLE LANDING PAGE" placeholder="META TITLE LANDING PAGE" xtype="text"></li>
                        <li class="c4" title="META KEYWORD LANDING PAGE"><textarea name="metaKeywordT[]" class="metaKeyword" title="META KEYWORD LANDING PAGE" placeholder="META KEYWORD LANDING PAGE" xtype="textarea"><?php echo $arrMeta["metaKeyword"]; ?></textarea></li>
                        <li class="c4" title="META DESCRIPTION LANDING PAGE"><textarea name="metaDescT[]" class="metaDesc" title="META DESCRIPTION LANDING PAGE" placeholder="META DESCRIPTION LANDING PAGE" xtype="textarea"><?php echo $arrMeta["metaDesc"]; ?></textarea></li>
                    </ul>
                <?php } ?>
            </div>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>