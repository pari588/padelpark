<?php
// quotationStatus dropdown
$quotationStatusArr = array("" => "All", "Draft" => "Draft", "Sent" => "Sent", "Approved" => "Approved", "Rejected" => "Rejected");
$quotationStatusOpt = '';
$selQuotationStatus = $_GET["quotationStatus"] ?? "";
foreach ($quotationStatusArr as $k => $v) {
    $sel = ($selQuotationStatus == $k) ? ' selected="selected"' : '';
    $quotationStatusOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}


$arrSearch = array(
    array("type" => "text", "name" => "quotationID", "title" => "#ID", "where" => "AND q.quotationID=?", "dtype" => "i"),
    array("type" => "text", "name" => "quotationNo", "title" => "Quotation No", "where" => "AND q.quotationNo LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "clientName", "title" => "Client", "where" => "AND l.clientName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "quotationStatus", "title" => "Status", "where" => "AND q.quotationStatus=?", "dtype" => "s", "value" => $quotationStatusOpt, "default" => false)
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT q.quotationID FROM `" . $DB->pre . "sky_padel_quotation` q LEFT JOIN `" . $DB->pre . "sky_padel_lead` l ON q.leadID=l.leadID WHERE q.status=?" . $MXFRM->where;
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
                array("#ID", "quotationID", ' width="5%" align="center"', true),
                array("Quotation No", "quotationNo", ' width="12%" align="left"'),
                array("Lead", "leadNo", ' width="8%" align="left"'),
                array("Client", "clientName", ' width="15%" align="left"'),
                array("Date", "quotationDate", ' width="8%" align="center"'),
                array("Amount", "totalAmount", ' width="10%" align="right"'),
                array("Status", "quotationStatus", ' width="10%" align="center"'),
                array("Actions", "actions", ' width="22%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT q.*, l.leadNo, l.clientName FROM `" . $DB->pre . "sky_padel_quotation` q LEFT JOIN `" . $DB->pre . "sky_padel_lead` l ON q.leadID=l.leadID WHERE q.status=? " . $MXFRM->where . mxOrderBy("q.quotationID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        $d["quotationDate"] = isset($d["quotationDate"]) && $d["quotationDate"] != "0000-00-00" ? date("d-M-Y", strtotime($d["quotationDate"])) : "-";
                        $d["totalAmount"] = "₹" . number_format($d["totalAmount"] ?? 0, 2);

                        // Add revision badge to quotation number
                        $revisionBadge = "";
                        if (($d["revisionNumber"] ?? 0) > 0) {
                            $revisionBadge = ' <span class="badge badge-info" style="font-size:10px;">R' . $d["revisionNumber"] . '</span>';
                        }
                        $d["quotationNo"] = $d["quotationNo"] . $revisionBadge;

                        // Save original status before formatting
                        $originalStatus = $d["quotationStatus"];
                        $statusClasses = array(
                            "Draft" => "badge-secondary",
                            "Sent" => "badge-warning",
                            "Client Reviewing" => "badge-info",
                            "Approved" => "badge-success",
                            "Rejected" => "badge-danger",
                            "Expired" => "badge-dark"
                        );
                        $statusClass = $statusClasses[$originalStatus] ?? "badge-secondary";
                        $d["quotationStatus"] = '<span class="badge ' . $statusClass . '">' . $originalStatus . '</span>';

                        // Add action links based on original status
                        $d["actions"] = '';

                        if ($originalStatus == "Approved") {
                            // Show proforma link if generated
                            if (!empty($d["proformaID"])) {
                                $d["actions"] .= '<a href="' . ADMINURL . '/sky-padel-proforma-edit/?id=' . $d["proformaID"] . '" class="btn-action" style="margin-right:5px;" title="View Proforma"><i class="fa fa-file-invoice"></i> PI</a>';
                            }

                            // Check if project already exists for this quotation
                            $DB->vals = array(1, $d["quotationID"]);
                            $DB->types = "ii";
                            $DB->sql = "SELECT projectID FROM `" . $DB->pre . "sky_padel_project` WHERE status=? AND quotationID=?";
                            $projectData = $DB->dbRow();
                            if (!$projectData) {
                                $d["actions"] .= '<a href="' . ADMINURL . '/sky-padel-project-add/?quotationID=' . $d["quotationID"] . '" class="btn-action" style="background:#22c55e;color:#fff;padding:3px 8px;border-radius:4px;text-decoration:none;font-size:12px;">+ Project</a>';
                            } else {
                                $d["actions"] .= '<a href="' . ADMINURL . '/sky-padel-project-edit/?id=' . $projectData["projectID"] . '" class="btn-action" title="View Project"><i class="fa fa-folder-open"></i></a>';
                            }
                        } elseif ($originalStatus == "Rejected") {
                            // Check if a revision already exists and is pending
                            $parentID = $d["parentQuotationID"] ?: $d["quotationID"];
                            $DB->vals = array(1, $parentID, $parentID);
                            $DB->types = "iii";
                            $DB->sql = "SELECT quotationID FROM `" . $DB->pre . "sky_padel_quotation` WHERE status=? AND (parentQuotationID=? OR quotationID=?) AND isLatestRevision=1 AND quotationStatus IN ('Draft', 'Sent', 'Client Reviewing')";
                            $pendingRevision = $DB->dbRow();

                            if (!$pendingRevision) {
                                $d["actions"] .= '<a href="' . ADMINURL . '/sky-padel-quotation-add/?revisionOf=' . $d["quotationID"] . '" class="btn-action" style="background:#f59e0b;color:#fff;padding:3px 8px;border-radius:4px;text-decoration:none;font-size:12px;"><i class="fa fa-redo"></i> Revise</a>';
                            } else {
                                $d["actions"] .= '<a href="' . ADMINURL . '/sky-padel-quotation-edit/?id=' . $pendingRevision["quotationID"] . '" class="btn-action" style="color:#666;font-size:11px;">Revision Pending</a>';
                            }
                        }
                    ?>
                        <tr><?php echo getMAction("mid", $d["quotationID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["quotationID"], strip_tags($d[$v[1]])) : ($d[$v[1]] ?? ""); ?></td>
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
