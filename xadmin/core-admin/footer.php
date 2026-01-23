</div>
<?php if (isset($_SESSION[SITEURL][$TPL->modName]["EXPCOLS"]) && isset($_SESSION[SITEURL][$TPL->modName]["EXPCOLS"]) && $TPL->pageType == "list") { ?>
    <div class="mxdialog export-popup" style="display: none;">
        <div class="body" style="width: 500px;">
            <a href="#" class="close del"></a>
            <h2>EXPORT DETAILS</h2>
            <form class="wrap-data" name="frmExport" id="frmExport" action="" method="post" enctype="multipart/form-data">
                <div class="content">
                    <ul class="attndt-info export-popup">
                        <li class="c2">
                            <label>Start From Record<em></em></label>
                            <input type="text" class="only-numeric" name="offset" id="offset" value="0" min="0" max="<?php echo ($MXTOTREC - 1); ?>" title="1" placeholder="Enter Start Record">
                        </li>
                        <li class="c2">
                            <label>End Record (TOTAL: <?php echo $MXTOTREC; ?>)<em></em></label>
                            <input type="text" class="only-numeric" name="showrec" id="showrec" value="<?php echo $MXTOTREC; ?>" min="2" max="<?php echo $MXTOTREC; ?>" title="<?php echo $MXTOTREC; ?>" placeholder="Enter End Record">
                        </li>
                        <li class="linear"><label>Select Export Type <em>*</em></label>
                            <ul class="mx-list" xtype="radio">
                                <!-- <li><i class="rdo">XLSX <input type="radio" name="xAction" value="exportXLSX" checked="checked"><em></em></i></li> -->
                                <li><i class="rdo">CSV <input type="radio" name="xAction" value="exportCSV" checked="checked"><em></em></i></li>
                                <!-- <li><i class="rdo">XLS <input type="radio" name="xAction" value="exportXLS"><em></em></i></li> -->
                            </ul>
                        </li>
                        <li class="message">
                            <p class="e"></p>
                        </li>
                        <li class="cta">
                            <input type="hidden" name="modName" id="modName" value="<?php echo $TPL->modName; ?>" />
                            <input type="button" class="btn" id="btnExport" value="EXPORT" />
                        </li>
                    </ul>
                </div>
            </form>
        </div>
    </div>
<?php } ?>
</body>

</html>