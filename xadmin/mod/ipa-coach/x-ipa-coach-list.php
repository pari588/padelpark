<?php
// Build status dropdown
$statusArr = array("" => "All Status", "Active" => "Active", "Inactive" => "Inactive", "On Leave" => "On Leave", "Terminated" => "Terminated");
$statusOpt = '';
$selStatus = $_GET["coachStatus"] ?? "";
foreach ($statusArr as $k => $v) {
    $sel = ($selStatus == $k) ? ' selected="selected"' : '';
    $statusOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

// Build certification level dropdown
$certArr = array("" => "All Levels", "Level 1" => "Level 1", "Level 2" => "Level 2", "Level 3" => "Level 3", "Head Coach" => "Head Coach");
$certOpt = '';
$selCert = $_GET["certificationLevel"] ?? "";
foreach ($certArr as $k => $v) {
    $sel = ($selCert == $k) ? ' selected="selected"' : '';
    $certOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$arrSearch = array(
    array("type" => "text", "name" => "coachCode", "title" => "Code", "where" => "AND c.coachCode LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "firstName", "title" => "Name", "where" => "AND (c.firstName LIKE CONCAT('%',?,'%') OR c.lastName LIKE CONCAT('%',?,'%'))", "dtype" => "ss"),
    array("type" => "select", "name" => "certificationLevel", "title" => "Level", "where" => "AND c.certificationLevel=?", "dtype" => "s", "value" => $certOpt, "default" => false),
    array("type" => "select", "name" => "coachStatus", "title" => "Status", "where" => "AND c.coachStatus=?", "dtype" => "s", "value" => $statusOpt, "default" => false)
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT c.coachID FROM `" . $DB->pre . "ipa_coach` c WHERE c.status=?" . $MXFRM->where;
$DB->dbQuery();
$MXTOTREC = $DB->numRows;
if (!$MXFRM->where && $MXTOTREC < 1) $strSearch = "";
echo $strSearch;
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <?php if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("Code", "coachCode", ' width="10%" align="center"', true),
                array("Name", "fullName", ' width="20%" align="left"'),
                array("Phone", "phone", ' width="12%" align="center"'),
                array("Level", "certificationLevel", ' width="12%" align="center"'),
                array("Rating", "avgStudentRating", ' width="8%" align="center"'),
                array("Sessions", "totalSessionsConducted", ' width="8%" align="center"'),
                array("Status", "coachStatus", ' width="10%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT c.*, CONCAT(c.firstName, ' ', IFNULL(c.lastName,'')) as fullName
                        FROM `" . $DB->pre . "ipa_coach` c
                        WHERE c.status=?" . $MXFRM->where . mxOrderBy("c.coachID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Format certification level badge
                        $certColors = array("Level 1" => "secondary", "Level 2" => "info", "Level 3" => "warning", "Head Coach" => "success");
                        $d["certificationLevel"] = '<span class="badge badge-' . ($certColors[$d["certificationLevel"]] ?? "secondary") . '">' . $d["certificationLevel"] . '</span>';

                        // Format rating with stars
                        $rating = floatval($d["avgStudentRating"]);
                        $d["avgStudentRating"] = $rating > 0 ? number_format($rating, 1) . ' <i class="fa fa-star" style="color:#f59e0b;"></i>' : '-';

                        // Format status badge
                        $statusColors = array("Active" => "success", "Inactive" => "secondary", "On Leave" => "warning", "Terminated" => "danger");
                        $d["coachStatus"] = '<span class="badge badge-' . ($statusColors[$d["coachStatus"]] ?? "secondary") . '">' . $d["coachStatus"] . '</span>';
                    ?>
                        <tr><?php echo getMAction("mid", $d["coachID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["coachID"], $d[$v[1]]) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records" style="text-align:center; padding:60px 20px;">
                <i class="fa fa-user-circle" style="font-size:48px;color:#ddd;margin-bottom:15px;display:block;"></i>
                <p style="margin:0; color:#888; font-size:15px;">No coaches found. Add your first coach to get started.</p>
            </div>
        <?php } ?>
    </div>
</div>
