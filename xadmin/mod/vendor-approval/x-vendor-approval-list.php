<?php
/**
 * Vendor Approval Dashboard - Pending Approvals View
 */
$arrSearch = array(
    array("type" => "text", "name" => "vendorID", "title" => "#ID", "where" => "AND vendorID=?", "dtype" => "i"),
    array("type" => "text", "name" => "legalName", "title" => "Company", "where" => "AND legalName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "vendorStatus", "title" => "Status", "where" => "AND vendorStatus=?", "dtype" => "s", "option" => '
        <option value="">-- All Pending --</option>
        <option value="Pending">Pending</option>
        <option value="Under Review">Under Review</option>
        <option value="Info Requested">Info Requested</option>
    ')
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);

// Default filter to pending statuses
$pendingFilter = "";
if (empty($_GET["vendorStatus"])) {
    $pendingFilter = " AND vendorStatus IN ('Pending','Under Review','Info Requested')";
}

$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT vendorID FROM `" . $DB->pre . "vendor_onboarding` WHERE status=?" . $MXFRM->where . $pendingFilter;
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
                array("#ID", "vendorID", ' width="5%" align="center"', true),
                array("Code", "vendorCode", ' width="10%" align="left"'),
                array("Company Name", "legalName", ' width="20%" align="left"'),
                array("Contact", "contactPersonName", ' width="15%" align="left"'),
                array("Email", "contactEmail", ' width="15%" align="left"'),
                array("GST", "gstNumber", ' width="12%" align="left"'),
                array("Status", "vendorStatus", ' width="10%" align="center"'),
                array("Submitted", "created", ' width="10%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT * FROM `" . $DB->pre . "vendor_onboarding` WHERE status=?" . $MXFRM->where . $pendingFilter . mxOrderBy("created DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Status badge
                        $statusBadges = array("Pending" => "badge-warning", "Under Review" => "badge-info", "Info Requested" => "badge-primary", "Approved" => "badge-success", "Disapproved" => "badge-danger", "Blocked" => "badge-dark");
                        $statusClass = isset($statusBadges[$d["vendorStatus"]]) ? $statusBadges[$d["vendorStatus"]] : "badge-secondary";
                        $d["vendorStatus"] = '<span class="badge ' . $statusClass . '">' . htmlentities($d["vendorStatus"]) . '</span>';

                        // Format date
                        $d["created"] = $d["created"] ? date("d-M-Y", strtotime($d["created"])) : "-";

                        // GST
                        $d["gstNumber"] = $d["gstNumber"] ?: "-";
                    ?>
                        <tr><?php echo getMAction("mid", $d["vendorID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("vendorID=" . $d["vendorID"], strip_tags($d[$v[1]]), "../vendor-onboarding-edit") : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records">No pending vendors for approval</div>
        <?php } ?>
    </div>
</div>
