<?php
/**
 * Vendor RFQ - List View
 */
$arrSearch = array(
    array("type" => "text", "name" => "rfqID", "title" => "#ID", "where" => "AND rfqID=?", "dtype" => "i"),
    array("type" => "text", "name" => "rfqNumber", "title" => "RFQ #", "where" => "AND rfqNumber LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "title", "title" => "Title", "where" => "AND title LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "rfqStatus", "title" => "Status", "where" => "AND rfqStatus=?", "dtype" => "s", "option" => '
        <option value="">-- All --</option>
        <option value="Draft">Draft</option>
        <option value="Published">Published</option>
        <option value="Closed">Closed</option>
        <option value="Awarded">Awarded</option>
    ')
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT rfqID FROM `" . $DB->pre . "vendor_rfq` WHERE status=?" . $MXFRM->where;
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
                array("#ID", "rfqID", ' width="5%" align="center"', true),
                array("RFQ Number", "rfqNumber", ' width="12%" align="left"'),
                array("Title", "title", ' width="25%" align="left"'),
                array("Type", "rfqType", ' width="8%" align="center"'),
                array("Deadline", "submissionDeadline", ' width="12%" align="center"'),
                array("Quotes", "quoteCount", ' width="8%" align="center"'),
                array("Status", "rfqStatus", ' width="10%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT r.*, (SELECT COUNT(*) FROM " . $DB->pre . "vendor_quote q WHERE q.rfqID = r.rfqID AND q.status = 1) as quoteCount
                        FROM `" . $DB->pre . "vendor_rfq` r WHERE r.status=? " . $MXFRM->where . mxOrderBy("rfqID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Status badge
                        $statusBadges = array("Draft" => "badge-secondary", "Published" => "badge-info", "Closed" => "badge-warning", "Awarded" => "badge-success", "Cancelled" => "badge-danger");
                        $statusClass = isset($statusBadges[$d["rfqStatus"]]) ? $statusBadges[$d["rfqStatus"]] : "badge-secondary";
                        $d["rfqStatus"] = '<span class="badge ' . $statusClass . '">' . htmlentities($d["rfqStatus"]) . '</span>';

                        // Format deadline
                        $d["submissionDeadline"] = $d["submissionDeadline"] ? date("d-M-Y", strtotime($d["submissionDeadline"])) : "-";

                        // Quote count badge
                        $d["quoteCount"] = '<span class="badge badge-primary">' . $d["quoteCount"] . '</span>';
                    ?>
                        <tr><?php echo getMAction("mid", $d["rfqID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("rfqID=" . $d["rfqID"], strip_tags($d[$v[1]])) : ($d[$v[1]] ?? ""); ?></td>
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
