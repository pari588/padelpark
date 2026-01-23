<?php
/**
 * Vendor Quote - List View
 */
$arrSearch = array(
    array("type" => "text", "name" => "quoteID", "title" => "#ID", "where" => "AND q.quoteID=?", "dtype" => "i"),
    array("type" => "text", "name" => "quoteNumber", "title" => "Quote #", "where" => "AND q.quoteNumber LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "vendorName", "title" => "Vendor", "where" => "AND v.legalName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "quoteStatus", "title" => "Status", "where" => "AND q.quoteStatus=?", "dtype" => "s", "option" => '
        <option value="">-- All --</option>
        <option value="Draft">Draft</option>
        <option value="Submitted">Submitted</option>
        <option value="Under Review">Under Review</option>
        <option value="Shortlisted">Shortlisted</option>
        <option value="Accepted">Accepted</option>
        <option value="Rejected">Rejected</option>
    ')
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);

// Get RFQ filter if provided
$rfqID = intval($_GET["rfqID"] ?? 0);
$rfqWhere = $rfqID > 0 ? " AND q.rfqID = " . $rfqID : "";

$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT q.quoteID FROM `" . $DB->pre . "vendor_quote` q
            LEFT JOIN `" . $DB->pre . "vendor_onboarding` v ON q.vendorID = v.vendorID
            WHERE q.status=?" . $MXFRM->where . $rfqWhere;
$DB->dbQuery();
$MXTOTREC = $DB->numRows;
if (!$MXFRM->where && $MXTOTREC < 1) $strSearch = "";
echo $strSearch;

// Show RFQ filter info
if ($rfqID > 0) {
    $DB->sql = "SELECT rfqNumber, title FROM " . $DB->pre . "vendor_rfq WHERE rfqID = ?";
    $DB->vals = array($rfqID);
    $DB->types = "i";
    $rfq = $DB->dbRow();
    if ($rfq) {
        echo '<div style="padding:10px 15px; background:#e7f3ff; border-left:4px solid #007bff; margin-bottom:15px;">';
        echo '<strong>Filtered by RFQ:</strong> ' . htmlentities($rfq["rfqNumber"]) . ' - ' . htmlentities($rfq["title"]);
        echo ' &nbsp;<a href="' . ADMINURL . 'vendor-quote-list">[Clear Filter]</a>';
        echo '</div>';
    }
}
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <?php if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "quoteID", ' width="5%" align="center"', true),
                array("Quote #", "quoteNumber", ' width="12%"'),
                array("RFQ", "rfqNumber", ' width="12%"'),
                array("Vendor", "legalName", ' width="18%"'),
                array("Submitted", "submittedAt", ' width="10%" align="center"'),
                array("Amount", "totalAmount", ' width="10%" align="right"'),
                array("Status", "quoteStatus", ' width="10%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT q.*, v.legalName, v.vendorCode, r.rfqNumber
                        FROM `" . $DB->pre . "vendor_quote` q
                        LEFT JOIN `" . $DB->pre . "vendor_onboarding` v ON q.vendorID = v.vendorID
                        LEFT JOIN `" . $DB->pre . "vendor_rfq` r ON q.rfqID = r.rfqID
                        WHERE q.status=?" . $MXFRM->where . $rfqWhere . mxOrderBy("quoteID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Status badge
                        $statusBadges = array("Draft" => "badge-secondary", "Submitted" => "badge-info", "Under Review" => "badge-warning", "Shortlisted" => "badge-primary", "Accepted" => "badge-success", "Rejected" => "badge-danger");
                        $statusClass = isset($statusBadges[$d["quoteStatus"]]) ? $statusBadges[$d["quoteStatus"]] : "badge-secondary";
                        $d["quoteStatus"] = '<span class="badge ' . $statusClass . '">' . htmlentities($d["quoteStatus"]) . '</span>';

                        // Format date
                        $d["submittedAt"] = $d["submittedAt"] ? date("d-M-Y", strtotime($d["submittedAt"])) : "-";

                        // Format amount
                        $d["totalAmount"] = number_format($d["totalAmount"], 2);
                    ?>
                        <tr><?php echo getMAction("mid", $d["quoteID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("quoteID=" . $d["quoteID"], strip_tags($d[$v[1]])) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records">No quotes found</div>
        <?php } ?>
    </div>
</div>
