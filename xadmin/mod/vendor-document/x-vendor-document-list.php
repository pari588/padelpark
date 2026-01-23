<?php
/**
 * Vendor Document - List View
 */
$vendorID = intval($_GET["vendorID"] ?? 0);
$vendorWhere = "";
if ($vendorID > 0) {
    $vendorWhere = " AND vendorID = " . $vendorID;
}

$arrSearch = array(
    array("type" => "text", "name" => "documentID", "title" => "#ID", "where" => "AND documentID=?", "dtype" => "i"),
    array("type" => "select", "name" => "documentType", "title" => "Type", "where" => "AND documentType=?", "dtype" => "s", "option" => '
        <option value="">-- All --</option>
        <option value="GST Certificate">GST Certificate</option>
        <option value="PAN Card">PAN Card</option>
        <option value="Company Registration">Company Registration</option>
        <option value="MSME Certificate">MSME Certificate</option>
        <option value="Bank Statement">Bank Statement</option>
        <option value="Cancelled Cheque">Cancelled Cheque</option>
        <option value="Other">Other</option>
    '),
    array("type" => "select", "name" => "verificationStatus", "title" => "Status", "where" => "AND verificationStatus=?", "dtype" => "s", "option" => '
        <option value="">-- All --</option>
        <option value="Pending">Pending</option>
        <option value="Verified">Verified</option>
        <option value="Rejected">Rejected</option>
    ')
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);

$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT documentID FROM `" . $DB->pre . "vendor_document` WHERE status=?" . $MXFRM->where . $vendorWhere;
$DB->dbQuery();
$MXTOTREC = $DB->numRows;
if (!$MXFRM->where && $MXTOTREC < 1) $strSearch = "";
echo $strSearch;
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <?php
        // Show vendor info if filtered
        if ($vendorID > 0) {
            $DB->sql = "SELECT legalName, vendorCode FROM " . $DB->pre . "vendor_onboarding WHERE vendorID = ?";
            $DB->vals = array($vendorID);
            $DB->types = "i";
            $vendor = $DB->dbRow();
            if ($vendor) {
                echo '<div class="alert alert-info mb-3">';
                echo '<strong>Documents for:</strong> ' . htmlentities($vendor["legalName"]) . ' (' . $vendor["vendorCode"] . ')';
                echo ' <a href="' . ADMINURL . 'vendor-document-list" class="btn btn-sm btn-outline-secondary ml-2">View All</a>';
                echo '</div>';
            }
        }

        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "documentID", ' width="5%" align="center"', true),
                array("Vendor", "legalName", ' width="18%" align="left"'),
                array("Type", "documentType", ' width="12%" align="left"'),
                array("File", "filePath", ' width="20%" align="left"'),
                array("Uploaded", "createdAt", ' width="10%" align="center"'),
                array("Expiry", "expiryDate", ' width="10%" align="center"'),
                array("Status", "verificationStatus", ' width="10%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT d.*, v.legalName, v.vendorCode
                        FROM `" . $DB->pre . "vendor_document` d
                        LEFT JOIN `" . $DB->pre . "vendor_onboarding` v ON d.vendorID = v.vendorID
                        WHERE d.status=?" . $MXFRM->where . $vendorWhere . mxOrderBy("documentID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Status badge
                        $statusBadges = array("Pending" => "badge-warning", "Verified" => "badge-success", "Rejected" => "badge-danger");
                        $statusClass = isset($statusBadges[$d["verificationStatus"]]) ? $statusBadges[$d["verificationStatus"]] : "badge-secondary";
                        $d["verificationStatus"] = '<span class="badge ' . $statusClass . '">' . htmlentities($d["verificationStatus"]) . '</span>';

                        // Format dates
                        $d["createdAt"] = $d["createdAt"] ? date("d-M-Y", strtotime($d["createdAt"])) : "-";
                        $d["expiryDate"] = $d["expiryDate"] ? date("d-M-Y", strtotime($d["expiryDate"])) : "-";

                        // File link
                        $d["filePath"] = $d["filePath"] ? '<a href="' . UPLOADURL . 'vendor-documents/' . $d["filePath"] . '" target="_blank">' . htmlentities($d["documentName"] ?: $d["filePath"]) . '</a>' : "-";
                    ?>
                        <tr><?php echo getMAction("mid", $d["documentID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("documentID=" . $d["documentID"], strip_tags($d[$v[1]])) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records">No documents found</div>
        <?php } ?>
    </div>
</div>
