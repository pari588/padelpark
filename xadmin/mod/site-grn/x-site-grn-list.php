<?php
// grnStatus dropdown
$grnStatusArr = array("" => "All", "Draft" => "Draft", "Accepted" => "Accepted", "Rejected" => "Rejected");
$grnStatusOpt = '';
$selGrnStatus = $_GET["grnStatus"] ?? "";
foreach ($grnStatusArr as $k => $v) {
    $sel = ($selGrnStatus == $k) ? ' selected="selected"' : '';
    $grnStatusOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

// grnType dropdown
$grnTypeArr = array("" => "All", "From-Warehouse" => "From Warehouse", "Direct-Purchase" => "Direct Purchase", "Return" => "Return");
$grnTypeOpt = '';
$selGrnType = $_GET["grnType"] ?? "";
foreach ($grnTypeArr as $k => $v) {
    $sel = ($selGrnType == $k) ? ' selected="selected"' : '';
    $grnTypeOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$arrSearch = array(
    array("type" => "text", "name" => "grnID", "title" => "#ID", "where" => "AND g.grnID=?", "dtype" => "i"),
    array("type" => "text", "name" => "grnNo", "title" => "GRN No", "where" => "AND g.grnNo LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "projectName", "title" => "Project", "where" => "AND (p.projectNo LIKE CONCAT('%',?,'%') OR p.projectName LIKE CONCAT('%',?,'%'))", "dtype" => "ss"),
    array("type" => "select", "name" => "grnStatus", "title" => "Status", "where" => "AND g.grnStatus=?", "dtype" => "s", "value" => $grnStatusOpt, "default" => false),
    array("type" => "select", "name" => "grnType", "title" => "Type", "where" => "AND g.grnType=?", "dtype" => "s", "value" => $grnTypeOpt, "default" => false)
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT g.grnID FROM `" . $DB->pre . "site_grn` g
            LEFT JOIN `" . $DB->pre . "sky_padel_project` p ON g.projectID = p.projectID
            WHERE g.status=?" . $MXFRM->where;
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
                array("#ID", "grnID", ' width="4%" align="center"', true),
                array("GRN No", "grnNo", ' width="11%" align="left"'),
                array("Project", "projectDisplay", ' width="18%" align="left"'),
                array("Type", "grnType", ' width="12%" align="center"'),
                array("Total Items", "totalItems", ' width="8%" align="center"'),
                array("Total Qty", "totalQuantity", ' width="8%" align="right"'),
                array("GRN Date", "grnDate", ' width="10%" align="center"'),
                array("Status", "grnStatus", ' width="9%" align="center"'),
                array("Received By", "receivedBy", ' width="12%" align="left"')
            );

            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT g.*, p.projectNo, p.projectName
                        FROM `" . $DB->pre . "site_grn` g
                        LEFT JOIN `" . $DB->pre . "sky_padel_project` p ON g.projectID = p.projectID
                        WHERE g.status=?" . $MXFRM->where . mxOrderBy("g.grnDate DESC, g.grnID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Format project display
                        $d["projectDisplay"] = '<strong>' . $d["projectNo"] . '</strong><br><small>' . $d["projectName"] . '</small>';

                        // Format GRN date
                        $d["grnDate"] = date("d-M-Y", strtotime($d["grnDate"]));

                        // Status badge
                        $statusColors = array("Draft" => "badge-secondary", "Accepted" => "badge-success", "Rejected" => "badge-danger");
                        $d["grnStatus"] = '<span class="badge ' . ($statusColors[$d["grnStatus"]] ?? "badge-secondary") . '">' . $d["grnStatus"] . '</span>';
                    ?>
                        <tr><?php echo getMAction("mid", $d["grnID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["grnID"], strip_tags($d[$v[1]] ?? '')) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records">No GRN records found.</div>
        <?php } ?>
    </div>
</div>
