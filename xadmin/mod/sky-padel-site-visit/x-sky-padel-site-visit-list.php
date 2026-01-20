<?php
$arrSearch = array(
    array("type" => "text", "name" => "visitID", "title" => "#ID", "where" => "AND v.visitID=?", "dtype" => "i"),
    array("type" => "text", "name" => "clientName", "title" => "Client", "where" => "AND l.clientName LIKE CONCAT('%',?,'%')", "dtype" => "s")
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT v.visitID FROM `" . $DB->pre . "sky_padel_site_visit` v LEFT JOIN `" . $DB->pre . "sky_padel_lead` l ON v.leadID=l.leadID WHERE v.status=?" . $MXFRM->where;
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
                array("#ID", "visitID", ' width="5%" align="center"', true),
                array("Lead No", "leadNo", ' width="10%" align="left"'),
                array("Client", "clientName", ' width="20%" align="left"'),
                array("Visit Date", "visitDate", ' width="10%" align="center"'),
                array("Visit Time", "visitTime", ' width="10%" align="center"'),
                array("Type", "visitType", ' width="10%" align="center"'),
                array("Status", "visitStatus", ' width="15%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT v.*, l.leadNo, l.clientName FROM `" . $DB->pre . "sky_padel_site_visit` v LEFT JOIN `" . $DB->pre . "sky_padel_lead` l ON v.leadID=l.leadID WHERE v.status=? " . $MXFRM->where . mxOrderBy("v.visitID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        $d["visitDate"] = isset($d["visitDate"]) && $d["visitDate"] != "0000-00-00" ? date("d-M-Y", strtotime($d["visitDate"])) : "-";
                        $d["visitTime"] = isset($d["visitTime"]) && $d["visitTime"] != "00:00:00" ? date("h:i A", strtotime($d["visitTime"])) : "-";
                        $statusClass = $d["visitStatus"] == "Completed" ? "badge-success" : ($d["visitStatus"] == "Cancelled" ? "badge-danger" : "badge-warning");
                        $d["visitStatus"] = '<span class="badge ' . $statusClass . '">' . $d["visitStatus"] . '</span>';
                    ?>
                        <tr><?php echo getMAction("mid", $d["visitID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["visitID"], strip_tags($d[$v[1]])) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records">No records found</div>
        <?php } ?>
    </div>
</div>
