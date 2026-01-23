<script language="javascript" type="text/javascript" src="<?php echo mxGetUrl($TPL->modUrl . 'optimize.inc.js'); ?>"></script>
<?php
$arrTables = array();
$arrTblDr = array();
$arrTbl = mxGetAllTables();
foreach ($arrTbl as $tName) {
    if (!in_array($tName, array("mx_x_admin_menu", "mx_x_admin_role", "mx_x_admin_role_access", "mx_x_meta", "mx_x_setting", "mx_x_template", "mx_x_log_action", "mx_x_log_request"))) {
        $tKey = str_replace("mx_x_", "", $tName);
        $tKey = str_replace("mx_", "", $tKey);
        $arrTables[$tName] = $tName;
        $arrTblDr[$tKey] = $tName;
    }
}
?>
<div class="wrap-right">
    <?php echo getPageNav('', '<a href="#" class="fa-trash btn del-tmp"> DELETE ALL TMP DIR</a>', array("update", "add", "list", "trash")); ?>
    <div class="wrap-data">
        <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list no-resp">
            <thead>
                <tr>
                    <th nowrap="nowrap" width="1%">Sr No</th>
                    <th nowrap="nowrap" width="1%" align="left">Upload Dirs</th>
                    <th nowrap="nowrap" align="left">Tables for files</th>
                    <th align="left">Fields for files</th>
                    <th nowrap="nowrap" width="1%">TBL File<br>Count</th>
                    <th nowrap="nowrap" width="1%" align="center">Dir File<br>Count</th>
                    <th nowrap="nowrap" width="1%" align="center">Dir File<br>Size</th>
                    <th nowrap="nowrap" width="1%">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $cnt = 1;
                $dbc = 0;
                $arrDir = scanUploadDir("");
                foreach ($arrDir as $dir => $d) {
                    $tableName = "";
                    $fieldNames = array();
                    $dirS = preg_replace("|/|", "", $dir, 1);
                ?>
                    <tr class="row">
                        <td align="center">
                            <?php echo $cnt;
                            $cnt++;
                            ?>
                        </td>
                        <td align="left">
                            <?php echo $dir; ?>
                        </td>
                        <td colspan="2" class="file-data">
                            <form name="frmMeta<?php echo $cnt; ?>" action="" method="post">
                                <input type="hidden" name="metaKey" value="home" />
                                <input type="hidden" name="xAction" id="xAction" value="optimizeFolder" />
                                <table width="100%" border="0" cellpadding="8" cellspacing="0" class="tbl-list inner-table">
                                    <tr valign="top">
                                        <td width="35%">
                                            <div class="select-box">
                                                <select class="tableName" name="tableName" class="text table-names">
                                                    <option value="">--Select Table--</option>
                                                    <?php echo getArrayDD($arrTables, $arrTblDr[$dirS]); ?>
                                                </select>
                                            </div>
                                        </td>
                                        <td class="linear">
                                            <ul class="table-fields mx-list linear">
                                                <?php
                                                if (array_key_exists($dirS, $arrTblDr)) {
                                                    $tableName = $arrTblDr[$dirS];
                                                    $arrFl = getTableFieldList($arrTblDr[$dirS]);
                                                    $fieldNames = $arrFl["fields"];
                                                    echo $arrFl["html"];
                                                }
                                                ?>
                                            </ul>
                                        </td>
                                    </tr>
                                </table>
                                <input type="hidden" name="dirName" value="<?php echo $dirS; ?>" />
                            </form>
                        </td>
                        <?php
                        $dbCount["count"] = "0";
                        $dbc = 0;
                        if ($tableName != "") {
                            $dbCount = getFilesInTable($tableName, $fieldNames, true);
                            $style = "";
                            if ($dbCount["count"] != $d["count"])
                                $style = ' color: red;';
                        }
                        ?>
                        <td align="center" nowrap>
                            <span class="dbCount" style="float: left; height:28px; line-height:28px;<?php echo $style; ?>"><?php echo $dbCount["count"]; ?></span>
                            <input type="button" value="&#xf01e;" class="btn refresh fa" style="min-width: 30px;" />
                        </td>
                        <td align="center" nowrap="nowrap" class="dirCount"><?php echo $d["count"] ?></td>
                        <td align="right" nowrap="nowrap" class="dirSize" title="<?php echo $d["size"]; ?>"><?php echo formatFileSize($d["size"]); ?></td>

                        <td align="center">
                            <input type="button" value="optimize" class="btn optimize-it" style="min-width: 70px;" />
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" align="right">TOTAL</th>
                    <th nowrap="nowrap" align="center" class="dbCountT">0</th>
                    <th nowrap="nowrap" align="center" class="dirCountT">0</th>
                    <th nowrap="nowrap" align="right" class="dirSizeT">0</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>