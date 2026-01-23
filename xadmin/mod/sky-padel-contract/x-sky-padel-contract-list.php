<?php
// contractStatus dropdown
$contractStatusArr = array("" => "All", "Pending Signature" => "Pending Signature", "Signed" => "Signed", "Cancelled" => "Cancelled");
$contractStatusOpt = '';
$selContractStatus = $_GET["contractStatus"] ?? "";
foreach ($contractStatusArr as $k => $v) {
    $sel = ($selContractStatus == $k) ? ' selected="selected"' : '';
    $contractStatusOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}


$arrSearch = array(
    array("type" => "text", "name" => "contractID", "title" => "#ID", "where" => "AND c.contractID=?", "dtype" => "i"),
    array("type" => "text", "name" => "contractNo", "title" => "Contract No", "where" => "AND c.contractNo LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "clientName", "title" => "Client", "where" => "AND c.clientName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "contractStatus", "title" => "Status", "where" => "AND c.contractStatus=?", "dtype" => "s", "value" => $contractStatusOpt, "default" => false)
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT c.contractID FROM `" . $DB->pre . "sky_padel_contract` c WHERE c.status=?" . $MXFRM->where;
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
                array("#ID", "contractID", ' width="5%" align="center"', true),
                array("Contract No", "contractNo", ' width="12%" align="left"'),
                array("Quotation", "quotationNo", ' width="10%" align="left"'),
                array("Client", "clientName", ' width="15%" align="left"'),
                array("Date", "contractDate", ' width="8%" align="center"'),
                array("Amount", "contractAmount", ' width="10%" align="right"'),
                array("Status", "contractStatus", ' width="12%" align="center"'),
                array("Signed", "signedAt", ' width="10%" align="center"'),
                array("Actions", "actions", ' width="15%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT c.*, q.quotationNo
                        FROM `" . $DB->pre . "sky_padel_contract` c
                        LEFT JOIN `" . $DB->pre . "sky_padel_quotation` q ON c.quotationID = q.quotationID
                        WHERE c.status=? " . $MXFRM->where . mxOrderBy("c.contractID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        $d["contractDate"] = isset($d["contractDate"]) && $d["contractDate"] != "0000-00-00" ? date("d-M-Y", strtotime($d["contractDate"])) : "-";
                        $d["contractAmount"] = "₹" . number_format($d["contractAmount"] ?? 0, 2);

                        // Signed date
                        $d["signedAt"] = !empty($d["signedAt"]) ? date("d-M-Y H:i", strtotime($d["signedAt"])) : "-";

                        // Status badge
                        $originalStatus = $d["contractStatus"];
                        $statusClasses = array(
                            "Pending Signature" => "badge-warning",
                            "Signed" => "badge-success",
                            "Cancelled" => "badge-danger"
                        );
                        $statusClass = $statusClasses[$originalStatus] ?? "badge-secondary";
                        $d["contractStatus"] = '<span class="badge ' . $statusClass . '">' . $originalStatus . '</span>';

                        // Actions
                        $d["actions"] = '';
                        $d["actions"] .= '<a href="' . ADMINURL . '/sky-padel-contract-view/?id=' . $d["contractID"] . '" class="btn-action" title="View Details"><i class="fa fa-eye"></i></a> ';

                        if ($originalStatus == "Pending Signature") {
                            $d["actions"] .= '<a href="' . ADMINURL . '/sky-padel-contract-edit/?id=' . $d["contractID"] . '" class="btn-action" title="Edit Contract"><i class="fa fa-edit"></i></a> ';
                        }

                        $d["actions"] .= '<a href="' . ADMINURL . '/mod/sky-padel-contract/x-sky-padel-contract-pdf.php?id=' . $d["contractID"] . '" class="btn-action" title="Download PDF" target="_blank"><i class="fa fa-file-pdf"></i></a>';

                        if ($originalStatus == "Pending Signature") {
                            $d["actions"] .= ' <button onclick="cancelContract(' . $d["contractID"] . ')" class="btn-action" title="Cancel" style="border:none;background:none;cursor:pointer;"><i class="fa fa-times" style="color:#dc3545;"></i></button>';
                        }
                    ?>
                        <tr><?php echo getMAction("mid", $d["contractID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["contractID"], strip_tags($d[$v[1]])) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records">No contracts found</div>
        <?php } ?>
    </div>
</div>

<script>
function cancelContract(contractID) {
    if (confirm('Are you sure you want to cancel this contract?')) {
        $.mxajax({
            url: MODURL + 'x-sky-padel-contract.inc.php',
            data: { xAction: 'CANCEL', contractID: contractID }
        }).done(function(res) {
            if (res.err == 0) {
                location.reload();
            } else {
                alert(res.msg || 'Failed to cancel contract');
            }
        });
    }
}
</script>
