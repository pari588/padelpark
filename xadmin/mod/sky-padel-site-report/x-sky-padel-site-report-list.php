<?php
$arrSearch = array(
    array("type" => "text", "name" => "reportID", "title" => "#ID", "where" => "AND r.reportID=?", "dtype" => "i"),
    array("type" => "text", "name" => "clientName", "title" => "Client", "where" => "AND l.clientName LIKE CONCAT('%',?,'%')", "dtype" => "s")
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT r.reportID FROM `" . $DB->pre . "sky_padel_site_report` r LEFT JOIN `" . $DB->pre . "sky_padel_lead` l ON r.leadID=l.leadID WHERE r.status=?" . $MXFRM->where;
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
                array("#ID", "reportID", ' width="5%" align="center"', true),
                array("Lead", "leadNo", ' width="10%" align="left"'),
                array("Client", "clientName", ' width="20%" align="left"'),
                array("Report Date", "reportDate", ' width="10%" align="center"'),
                array("Suitability", "suitabilityRating", ' width="10%" align="center"'),
                array("Est. Cost", "estimatedCost", ' width="15%" align="right"'),
                array("Photos", "photos", ' width="10%" align="center"', "", "nosort")
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT r.*, l.leadNo, l.clientName FROM `" . $DB->pre . "sky_padel_site_report` r LEFT JOIN `" . $DB->pre . "sky_padel_lead` l ON r.leadID=l.leadID WHERE r.status=? " . $MXFRM->where . mxOrderBy("r.reportID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        $d["reportDate"] = isset($d["reportDate"]) && $d["reportDate"] != "0000-00-00" ? date("d-M-Y", strtotime($d["reportDate"])) : "-";
                        $d["estimatedCost"] = "₹" . number_format($d["estimatedCost"] ?? 0, 2);
                        if ($d["photos"] != "") {
                            $arrFile = explode(",", $d["photos"]);
                            $d["photos"] = getFile(array("path" => "sky-padel-site-report/" . $arrFile[0], "title" => "Photos"));
                        }
                    ?>
                        <tr><?php echo getMAction("mid", $d["reportID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["reportID"], strip_tags($d[$v[1]])) : ($d[$v[1]] ?? ""); ?></td>
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
