<div class="wrap-right">
    <?php echo getPageNav("", "", array("add", "trash")); ?>
    <div class="wrap-data">
        <?php
        if ($dir = @opendir(SITEPATH)) {
            $arrD = array();
            $DB->types = "i";
            $DB->vals = array(1);
            $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? ORDER BY xOrder ASC";
            $DB->dbRows();
            if ($DB->numRows > 0) {
                foreach ($DB->rows as $d) {
                    $arrD[$d["modDir"] . "/" . $d["seoUri"]] = $d;
                }
            }
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list small">
                <thead>
                    <tr>
                        <th align="left" width="1%">Modules</th>
                        <th align="left" width="1%">Mod Type</th>
                        <th align="left" width="1%">Meta Key</th>
                        <th align="left" width="1%">tblMaster</th>
                        <th align="left" width="1%">pkMaster</th>
                        <th align="left" width="1%">titleMaster</th>
                        <th align="left" width="1%">tplFileCol</th>
                        <th align="left" width="1%">tblDetail</th>
                        <th align="left" width="1%">pkDetail</th>
                        <th align="left" width="1%">titleDetail</th>
                        <th align="left" width="1%">metaKeyD</th>
                        <th align="left" width="1%">xOrder</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while (false !== ($modDir = readdir($dir))) {
                        if (is_dir(SITEPATH . "/" . $modDir) && startsWith("mod", $modDir)) {
                            //$arrseoUris = getArrayMods(SITEPATH . "/mod");
                            $arrseoUris = getArrayMods($modDir);
                            //echo "<br>".$modDir;
                            //print_r($arrseoUris);
                            ksort($arrseoUris);
                    ?>
                            <tr>
                                <th align="left" colspan="12" title="MOD DIR">MODULE DIRECTORY: <?php echo $modDir; ?></th>
                            </tr>
                            <?php foreach ($arrseoUris as $seoUri => $fileMod) { ?>
                                <tr>
                                    <td width="1%" align="left" title="#ID" nowrap><?php echo $modDir . "/" . $seoUri; ?>
                                        <div class="veiw-edit">
                                            <div class="ve-wrap" style="width: 56px; display: none;">
                                                <div class="edit" style="display: none;"><a href="<?php echo ADMINURL; ?>/template-edit/?modDir=<?php echo $modDir; ?>&seoUri=<?php echo $seoUri; ?>&fileMod=<?php echo $fileMod; ?>" class="edit" title="Edit"></a></div>
                                            </div>
                                        </div>
                                    </td>
                                    <?php
                                    $v = array("seoUri" => "", "modType" => "", "metaKey" => "", "tblMaster" => "",  "pkMaster" => "", "titleMaster" => "", "tplFileCol" => "", "titleMaster" => "", "tblDetail" => "", "pkDetail" => "", "titleDetail" => "", "metaKeyD" => "", "xOrder" => "");
                                    $modTypeS = "";
                                    if (array_key_exists($modDir . "/" . $seoUri, $arrD)) {
                                        //print_r($arrD[$modDir . "/" . $seoUri]);
                                        $v = array_merge($v, $arrD[$modDir . "/" . $seoUri]);
                                        if(isset($arrModType[$v["modType"]])){
                                            $modTypeS = $arrModType[$v["modType"]];
                                        }
                                    }
                                    ?>
                                    <td align="left"><?php echo $modTypeS; ?></td>
                                    <td align="left"><?php echo $v["metaKey"]; ?></td>
                                    <td align="left" width="1%"><?php echo $v["tblMaster"]; ?></td>
                                    <td align="left" width="1%"><?php echo $v["pkMaster"]; ?></td>
                                    <td align="left" width="1%"><?php echo $v["titleMaster"]; ?></td>
                                    <td align="left" width="1%"><?php echo $v["tplFileCol"]; ?></td>
                                    <td align="left" width="1%"><?php echo $v["tblDetail"]; ?></td>
                                    <td align="left" width="1%"><?php echo $v["pkDetail"]; ?></td>
                                    <td align="left" width="1%"><?php echo $v["titleDetail"]; ?></td>
                                    <td align="left" width="1%"><?php echo $v["metaKeyD"]; ?></td>
                                    <td align="left" width="1%"><?php echo $v["xOrder"]; ?></td>
                                </tr>
                            <?php } ?>
                    <?php }
                    } ?>
                </tbody>
            </table>

        <?php } else { ?>
            <div class="no-records">No mods found</div>
        <?php } ?>
    </div>
</div>