<?php
$arrTables = getDBSTables();
$arrExtraCol = getDBSCols();
?>
<div class="wrap-right">
    <?php echo getPageNav('<a href="#" class="fa-reset btn r"> RESTRUCTURE DATABASE</a>', '<a href="#" class="fa-save btn" rel="frmAddEdit"> UPDATE TABLE COLS</a>', array("update", "add", "list", "trash")); ?>
    <form class="wrap-data" auto="false" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form">
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list no-resp" id="tbl-access">
                <thead>
                    <tr>
                        <th align="left" rowspan="2">Table Name</th>
                        <th width="1%" align="center">ALL</th>
                        <?php foreach ($arrExtraCol as $k => $v) { ?>
                            <th> <?php echo $k; ?> <br><i class="chk" style="margin-top:4px;"><input type="checkbox" class="checkbox allv" /><em></em></i></th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($arrTables as $table) { ?>
                        <tr>
                            <td align="left"><strong><?php echo $table; ?></strong></td>
                            <td align="center"><i class="chk"><input type="checkbox" class="checkbox allh" /><em></em></i></td>
                            <?php
                            $arrFld = mxGetTableFlds($table, array());
                            foreach ($arrExtraCol as $colType => $arrCols) {
                                $flg = 0;
                                foreach ($arrCols as $fieldName => $dtype) {
                                    if (!array_key_exists($fieldName, $arrFld)) {
                                        $flg = 1;
                                    }
                                }
                                $ckd = '';
                                if ($flg == 0) {
                                    $ckd = ' checked="checked"';
                                }
                            ?>
                                <td align="center" width="120"><i class="chk"><input class="tblcol" type="checkbox" name="tables[<?php echo $table; ?>][]" value="<?php echo $colType; ?>" <?php echo $ckd; ?>class="checkbox" /><em></em></i></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <input type="hidden" name="xAction" id="xAction" value="updateTableCols" />
        <input type="hidden" name="modName" value="<?php echo $TPL->modName; ?>" />
        <input type="hidden" name="pageType" value="<?php echo $TPL->pageType; ?>" />
    </form>
</div>
<script>
    $(document).ready(function() {
        $('.allh').click(function() {
            var status = $(this).prop('checked');
            $(this).closest("tr").find("input:checkbox").prop('checked', status);
        });
        $('.allv').click(function() {
            var index = $(this).parent().parent().index();
            var status = $(this).prop('checked');
            $("#tbl-access tr:not(:first)").each(function() {
                $(this).find("input").eq(index - 1).prop('checked', status);
            });
        });
        $('a.fa-save.btn').click(function() {
            var checked = $("input.tblcol:checked").length;
            var tr = $(this).closest("tr.row");
            if (checked > 0) {
                $confirmbox = $.mxconfirm({
                    top: "20%",
                    title: "WARNING",
                    msg: "Are you sure, you want to proceed.<br><br>This action will add remove the columns from the tables.<br><br>You may lose database column data...",
                    buttons: {
                        "Yes": {
                            "action": function() {
                                $confirmbox.hidemxdialog();
                                showMxLoader();
                                $.mxajax({
                                    url: MODINCURL,
                                    type: "POST",
                                    data: $("form#frmAddEdit").serialize(),
                                    dataType: "json",
                                }).then(function(resp) {
                                    hideMxLoader(resp.msg);
                                });
                            },
                            "class": "testclass"
                        },
                        "Cancel": {
                            "action": function() {
                                $confirmbox.hidemxdialog();
                                return false;
                            }
                        }
                    }
                });
            } else {
                $.mxalert({
                    msg: "Sorry! Nothing seleted to proceed..."
                });
            }
            return false;
        });

        $("a.fa-reset.r").click(function() {
            $mxpopupaa = $.mxconfirm({
                title: "WARNING",
                msg: "Are you sure you want to restructure database.",
                buttons: {
                    "Yes": {
                        "action": function() {
                            $mxpopupaa.hidemxdialog();
                            showMxLoader();
                            $.mxajax({
                                type: "POST",
                                url: MODINCURL,
                                data: {
                                    xAction: "restructureDB"
                                },
                            }).then(function(resp) {
                                hideMxLoader(resp.msg);
                            });
                            return false;
                        }
                    },
                    "Cancel": {
                        "action": function() {
                            $mxpopupaa.hidemxdialog();
                            return false;
                        }
                    }
                }
            });
            return false;
        });
    });
</script>