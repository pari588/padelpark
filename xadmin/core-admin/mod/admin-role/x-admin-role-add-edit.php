<?php
$disable = "";
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->types = "ii";
    $DB->vals = array(1, $id);
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "` = ?";
    $D = $DB->dbRow();
    $S = getAccess($id);
    if ($TPL->pageType == "view") {
        $disable = ' disabled="disabled"';
    }
}
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form  f70">
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list" id="tbl-access">
                <thead>
                    <tr>
                        <th align="left">Menu Name</th>
                        <th width="1%">ALL</th>
                        <?php
                        foreach ($MXACCESS as $v) {
                            echo '<th align="center" width="60">' . ucfirst($v) . ' <br> <i class="chk" style="margin-top:4px;"><input type="checkbox" class="checkbox allv"' . $disable . ' /><em></em></i></th>';
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $DB->types = "i";
                    $DB->vals = array(0);
                    $DB->sql = "SELECT * FROM " . $DB->pre . "x_admin_menu WHERE menuType = ? ORDER BY xOrder ASC";
                    $DB->dbRows();
                    // echo $DB->numRows;die;
                    if ($DB->numRows) {
                        $sub = $DB->rows;
                        //echo '<tr><th colspan="8" align="left">'.$v["menuTitle"].'</th></tr>';
                        foreach ($sub as $d) {


                            if (isset($TPL->mAccess[$d["seoUri"]])) {
                                echo '<tr><td align="left">' . $d["menuTitle"] . '</td><td><i class="chk"><input type="checkbox" class="checkbox allh"' . $disable . ' /><em></em></i></td>';
                                foreach ($MXACCESS as $m) {
                                    $ckd = '';
                                    if (isset($S[$d["adminMenuID"]])) {
                                        if (in_array($m, $S[$d["adminMenuID"]]))
                                            $ckd = ' checked="checked"';
                                    }

                                    $strChk = '&nbsp';
                                    if (in_array($m, $TPL->mAccess[$d["seoUri"]])) {
                                        $strChk = '<i class="chk"><input type="checkbox" name="access[' . $d["adminMenuID"] . '][]" value="' . $m . '"' . $ckd . 'class="checkbox"' . $disable . ' /><em></em></i>';
                                    }

                                    echo '<td align="center">' . $strChk . '</td>';
                                }
                                echo '</tr>';
                            }
                        }
                    }
                    ?></tbody>
                <?php
                //if ($_SESSION[SITEURL]["MXROLE"] == "SUPER") {
                echo '<thead><tr><th align="left">Core Menu</th><th colspan="7">&nbsp;</th></tr></thead><tbody>';
                foreach ($MXADMINMENU as $k => $v) {
                    if (isset($TPL->mAccess[$v["seoUri"]])) {
                        echo '<tr><td align="left">' . $v["menuTitle"] . '</td><td><i class="chk"><input type="checkbox" class="checkbox allh"' . $disable . ' /><em></em></i></td>';
                        foreach ($MXACCESS as $m) {
                            $ckd = '';
                            if (isset($S[$k])) {
                                if (in_array($m, $S[$k]))
                                    $ckd = ' checked="checked"';
                            }
                            $strChk = '&nbsp';
                            if (in_array($m, $TPL->mAccess[$v["seoUri"]])) {
                                $strChk = '<i class="chk"><input type="checkbox" name="access[' . $k . '][]" value="' . $m . '"' . $ckd . 'class="checkbox"' . $disable . ' /><em></em></i>';
                            }
                            echo '<td align="center">' . $strChk . '</td>';
                        }
                        echo '</tr>';
                    }
                }
                //}
                ?>
                </tbody>
            </table>

        </div>
        <div class="wrap-form f30">
            <ul class="tbl-form">
                <?php

                $roleWhere = "";
                $DB->types = "i";
                $DB->vals = array(1);
                if (isset($id) && $id > 0) {
                    array_push($DB->vals, ($id ?? 0));
                    $DB->types = "ii";
                    $roleWhere = " AND roleID != ?";
                }

                $DB->sql = "SELECT roleID,roleName,parentID FROM `" . $DB->pre . "x_admin_role` WHERE status=?" . $roleWhere;
                $arrCats = $DB->dbRows();
                $strOptrole = getTreeDD($arrCats, "roleID", "roleName", "parentID", $D['parentID'] ?? 0);

                $arrWhere = array("sql" => "menuType = ?", "types" => "i", "vals" => array(0));
                $defaultPageOpt  = getTableDD(["table" => $DB->pre . "x_admin_menu", "key" => "seoUri", "val" => "menuTitle", "selected" => ($D["rolePage"] ?? ""), "where" => $arrWhere, "org" => false, "lang" => false]);
                $arrForm = array(
                    array("type" => "select", "name" => "parentID", "value" => $strOptrole, "title" => "Role Parent"),
                    array("type" => "text", "name" => "roleName", "value" => $D["roleName"] ?? "", "title" => "Role Name", "validate" => "required,name"),
                    array("type" => "text", "name" => "roleEmail", "value" => $D["roleEmail"] ?? "", "title" => "Role Email", "validate" => "email"),
                    array("type" => "text", "name" => "roleKey", "value" => $D["roleKey"] ?? "", "title" => "Role Key"),
                    array("type" => "select", "name" => "rolePage", "value" => $defaultPageOpt, "title" => "Role Landing Page", "validate" => "required")
                );
                $MXFRM = new mxForm();
                echo $MXFRM->getForm($arrForm, array("orgID" => $D["orgID"] ?? 0));
                ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
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
        });
    </script>
</div>