<?php
// Build skill level dropdown
$levelArr = array("" => "All Levels", "Beginner" => "Beginner", "Intermediate" => "Intermediate", "Advanced" => "Advanced", "Pro" => "Pro");
$levelOpt = '';
$selLevel = $_GET["currentLevel"] ?? "";
foreach ($levelArr as $k => $v) {
    $sel = ($selLevel == $k) ? ' selected="selected"' : '';
    $levelOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

// Build membership type dropdown
$memArr = array("" => "All Types", "Standard" => "Standard", "Premium" => "Premium", "Annual" => "Annual", "Lifetime" => "Lifetime");
$memOpt = '';
$selMem = $_GET["membershipType"] ?? "";
foreach ($memArr as $k => $v) {
    $sel = ($selMem == $k) ? ' selected="selected"' : '';
    $memOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$arrSearch = array(
    array("type" => "text", "name" => "playerCode", "title" => "Code", "where" => "AND p.playerCode LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "firstName", "title" => "Name", "where" => "AND (p.firstName LIKE CONCAT('%',?,'%') OR p.lastName LIKE CONCAT('%',?,'%'))", "dtype" => "ss"),
    array("type" => "text", "name" => "phone", "title" => "Phone", "where" => "AND p.phone LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "currentLevel", "title" => "Level", "where" => "AND p.currentLevel=?", "dtype" => "s", "value" => $levelOpt, "default" => false),
    array("type" => "select", "name" => "membershipType", "title" => "Membership", "where" => "AND p.membershipType=?", "dtype" => "s", "value" => $memOpt, "default" => false)
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT p.playerID FROM `" . $DB->pre . "ipa_player` p WHERE p.status=?" . $MXFRM->where;
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
                array("Code", "playerCode", ' width="10%" align="center"', true),
                array("Name", "fullName", ' width="20%" align="left"'),
                array("Phone", "phone", ' width="12%" align="center"'),
                array("Level", "currentLevel", ' width="12%" align="center"'),
                array("Membership", "membershipType", ' width="12%" align="center"'),
                array("Ranking", "ipaRanking", ' width="8%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT p.*, CONCAT(p.firstName, ' ', IFNULL(p.lastName,'')) as fullName
                        FROM `" . $DB->pre . "ipa_player` p
                        WHERE p.status=?" . $MXFRM->where . mxOrderBy("p.playerID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Format level badge
                        $levelColors = array("Beginner" => "secondary", "Intermediate" => "info", "Advanced" => "warning", "Pro" => "success");
                        $d["currentLevel"] = '<span class="badge badge-' . ($levelColors[$d["currentLevel"]] ?? "secondary") . '">' . $d["currentLevel"] . '</span>';

                        // Format membership badge
                        $memColors = array("Standard" => "secondary", "Premium" => "info", "Annual" => "primary", "Lifetime" => "success");
                        $d["membershipType"] = '<span class="badge badge-' . ($memColors[$d["membershipType"]] ?? "secondary") . '">' . $d["membershipType"] . '</span>';

                        // Format ranking
                        $d["ipaRanking"] = $d["ipaRanking"] > 0 ? '#' . $d["ipaRanking"] : '-';
                    ?>
                        <tr><?php echo getMAction("mid", $d["playerID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["playerID"], $d[$v[1]]) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records" style="text-align:center; padding:60px 20px;">
                <i class="fa fa-users" style="font-size:48px;color:#ddd;margin-bottom:15px;display:block;"></i>
                <p style="margin:0; color:#888; font-size:15px;">No players found. Register your first player to get started.</p>
            </div>
        <?php } ?>
    </div>
</div>
