<?php
// invoiceStatus dropdown
$invoiceStatusArr = array("" => "All", "Generated" => "Generated", "Sent" => "Sent", "Acknowledged" => "Acknowledged", "Paid" => "Paid", "Cancelled" => "Cancelled");
$invoiceStatusOpt = '';
$selInvoiceStatus = $_GET["invoiceStatus"] ?? "";
foreach ($invoiceStatusArr as $k => $v) {
    $sel = ($selInvoiceStatus == $k) ? ' selected="selected"' : '';
    $invoiceStatusOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}


$arrSearch = array(
    array("type" => "text", "name" => "proformaID", "title" => "#ID", "where" => "AND p.proformaID=?", "dtype" => "i"),
    array("type" => "text", "name" => "proformaNo", "title" => "Proforma No", "where" => "AND p.proformaNo LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "clientName", "title" => "Client", "where" => "AND p.clientName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "invoiceStatus", "title" => "Status", "where" => "AND p.invoiceStatus=?", "dtype" => "s", "value" => $invoiceStatusOpt, "default" => false)
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT p.proformaID FROM `" . $DB->pre . "sky_padel_proforma_invoice` p WHERE p.status=?" . $MXFRM->where;
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
                array("#ID", "proformaID", ' width="5%" align="center"', true),
                array("Proforma No", "proformaNo", ' width="12%" align="left"'),
                array("Quotation", "quotationNo", ' width="10%" align="left"'),
                array("Client", "clientName", ' width="18%" align="left"'),
                array("Date", "invoiceDate", ' width="10%" align="center"'),
                array("Amount", "totalAmount", ' width="12%" align="right"'),
                array("Status", "invoiceStatus", ' width="10%" align="center"'),
                array("Actions", "actions", ' width="13%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT p.*, q.quotationNo
                        FROM `" . $DB->pre . "sky_padel_proforma_invoice` p
                        LEFT JOIN `" . $DB->pre . "sky_padel_quotation` q ON p.quotationID=q.quotationID
                        WHERE p.status=? " . $MXFRM->where . mxOrderBy("p.proformaID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        $d["invoiceDate"] = isset($d["invoiceDate"]) && $d["invoiceDate"] != "0000-00-00" ? date("d-M-Y", strtotime($d["invoiceDate"])) : "-";
                        $d["totalAmount"] = "₹" . number_format($d["totalAmount"] ?? 0, 2);

                        // Status badge colors
                        $originalStatus = $d["invoiceStatus"];
                        $statusClasses = array(
                            "Generated" => "badge-info",
                            "Sent" => "badge-warning",
                            "Acknowledged" => "badge-primary",
                            "Partial Payment" => "badge-secondary",
                            "Paid" => "badge-success",
                            "Cancelled" => "badge-danger"
                        );
                        $statusClass = $statusClasses[$originalStatus] ?? "badge-secondary";
                        $d["invoiceStatus"] = '<span class="badge ' . $statusClass . '">' . $originalStatus . '</span>';

                        // Quotation link
                        if (!empty($d["quotationNo"])) {
                            $d["quotationNo"] = '<a href="' . ADMINURL . '/sky-padel-quotation-edit/?id=' . $d["quotationID"] . '">' . $d["quotationNo"] . '</a>';
                        } else {
                            $d["quotationNo"] = "-";
                        }

                        // Action buttons
                        $d["actions"] = '';

                        // PDF button - always show
                        $d["actions"] .= '<a href="' . ADMINURL . '/mod/sky-padel-proforma/x-sky-padel-proforma-pdf.php?id=' . $d["proformaID"] . '" class="btn-action" title="Download PDF" style="background:#dc3545;color:#fff;padding:5px 12px;border-radius:4px;margin-right:5px;text-decoration:none;font-size:12px;display:inline-block;"><i class="fa fa-file-pdf"></i> PDF</a> ';

                        // Send email button (only for Generated or Sent status)
                        if ($originalStatus == "Generated" || $originalStatus == "Sent") {
                            $d["actions"] .= '<a href="javascript:void(0);" onclick="sendProformaEmail(' . $d["proformaID"] . ')" class="btn-action" title="Send Email"><i class="fa fa-envelope"></i></a> ';
                        }

                        // Check if project exists for this quotation
                        if ($originalStatus == "Sent" || $originalStatus == "Acknowledged") {
                            $DB->vals = array(1, $d["quotationID"]);
                            $DB->types = "ii";
                            $DB->sql = "SELECT projectID FROM `" . $DB->pre . "sky_padel_project` WHERE status=? AND quotationID=?";
                            $proj = $DB->dbRow();
                            if (!$proj) {
                                $d["actions"] .= '<a href="' . ADMINURL . '/sky-padel-project-add/?quotationID=' . $d["quotationID"] . '&proformaID=' . $d["proformaID"] . '" class="btn-action" title="Create Project"><i class="fa fa-folder-plus"></i> Project</a>';
                            } else {
                                $d["actions"] .= '<a href="' . ADMINURL . '/sky-padel-project-edit/?id=' . $proj["projectID"] . '" class="btn-action" title="View Project"><i class="fa fa-folder-open"></i></a>';
                            }
                        }
                    ?>
                        <tr><?php echo getMAction("mid", $d["proformaID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["proformaID"], strip_tags($d[$v[1]])) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records">No proforma invoices found</div>
        <?php } ?>
    </div>
</div>

<script>
function sendProformaEmail(proformaID) {
    if (confirm('Send proforma invoice email to client?')) {
        $.ajax({
            url: '<?php echo ADMINURL; ?>/mod/sky-padel-proforma/x-sky-padel-proforma.inc.php',
            type: 'POST',
            data: {
                xAction: 'SEND_EMAIL',
                modName: 'sky-padel-proforma',
                proformaID: proformaID,
                xToken: '<?php echo $_SESSION[SITEURL]["CSRF_TOKEN"]; ?>'
            },
            dataType: 'json',
            success: function(res) {
                if (res.err == 0) {
                    alert('Email sent successfully!');
                    location.reload();
                } else {
                    alert('Failed to send email: ' + (res.msg || 'Unknown error'));
                }
            },
            error: function() {
                alert('Error sending email');
            }
        });
    }
}
</script>
