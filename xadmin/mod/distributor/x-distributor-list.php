<?php
// Distributor List - Using standard xAdmin layout

// distributorType dropdown
$distributorTypeArr = array("" => "All Types", "Distributor" => "Distributor", "Dealer" => "Dealer", "Retailer" => "Retailer", "Institutional" => "Institutional", "Government" => "Government");
$distributorTypeOpt = '';
$selDistributorType = $_GET["distributorType"] ?? "";
foreach ($distributorTypeArr as $k => $v) {
    $sel = ($selDistributorType == $k) ? ' selected="selected"' : '';
    $distributorTypeOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

// creditStatus dropdown
$creditStatusArr = array("" => "All Status", "Active" => "Active", "On Hold" => "On Hold", "Blocked" => "Blocked", "COD Only" => "COD Only");
$creditStatusOpt = '';
$selCreditStatus = $_GET["creditStatus"] ?? "";
foreach ($creditStatusArr as $k => $v) {
    $sel = ($selCreditStatus == $k) ? ' selected="selected"' : '';
    $creditStatusOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

// isActive dropdown
$isActiveArr = array("" => "All", "1" => "Active", "0" => "Inactive");
$isActiveOpt = '';
$selIsActive = $_GET["isActive"] ?? "";
foreach ($isActiveArr as $k => $v) {
    $sel = ($selIsActive == $k) ? ' selected="selected"' : '';
    $isActiveOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$arrSearch = array(
    array("type" => "text", "name" => "distributorID", "title" => "#ID", "where" => "AND d.distributorID=?", "dtype" => "i"),
    array("type" => "text", "name" => "distributorCode", "title" => "Code", "where" => "AND d.distributorCode LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "companyName", "title" => "Name", "where" => "AND d.companyName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "distributorType", "title" => "Type", "where" => "AND d.distributorType=?", "dtype" => "s", "value" => $distributorTypeOpt, "default" => false),
    array("type" => "select", "name" => "creditStatus", "title" => "Credit Status", "where" => "AND d.creditStatus=?", "dtype" => "s", "value" => $creditStatusOpt, "default" => false),
    array("type" => "select", "name" => "isActive", "title" => "Status", "where" => "AND d.isActive=?", "dtype" => "i", "value" => $isActiveOpt, "default" => false)
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT d.distributorID FROM `" . $DB->pre . "distributor` d WHERE d.status=?" . $MXFRM->where;
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
                array("#ID", "distributorID", ' width="4%" align="center"', true),
                array("Code", "distributorCode", ' width="8%" align="left"'),
                array("Company Name", "companyName", ' width="20%" align="left"'),
                array("Type", "distributorType", ' width="10%" align="center"'),
                array("Contact", "contactPerson", ' width="12%" align="left"'),
                array("GSTIN", "gstin", ' width="12%" align="left"'),
                array("Credit Limit", "creditLimit", ' width="10%" align="right"'),
                array("Outstanding", "currentOutstanding", ' width="10%" align="right"'),
                array("Credit Status", "creditStatus", ' width="8%" align="center"'),
                array("Status", "isActive", ' width="6%" align="center"')
            );

            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT d.* FROM `" . $DB->pre . "distributor` d WHERE d.status=?" . $MXFRM->where . mxOrderBy("d.companyName ASC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Format credit limit
                        $d["creditLimit"] = 'Rs. ' . number_format($d["creditLimit"], 0);

                        // Format outstanding
                        $outstanding = floatval($d["currentOutstanding"]);
                        $d["currentOutstanding"] = $outstanding > 0
                            ? '<span style="color:#dc3545;font-weight:bold;">Rs. ' . number_format($outstanding, 0) . '</span>'
                            : '<span style="color:#198754;">Rs. 0</span>';

                        // Format credit status with badges
                        $statusClasses = array(
                            "Active" => "badge-success",
                            "On Hold" => "badge-warning",
                            "Blocked" => "badge-danger",
                            "COD Only" => "badge-info"
                        );
                        $statusClass = $statusClasses[$d["creditStatus"]] ?? "badge-secondary";
                        $d["creditStatus"] = '<span class="badge ' . $statusClass . '">' . $d["creditStatus"] . '</span>';

                        // Format active status
                        $d["isActive"] = $d["isActive"] == 1
                            ? '<span class="badge badge-success">Active</span>'
                            : '<span class="badge badge-danger">Inactive</span>';

                        // Format GSTIN
                        $d["gstin"] = !empty($d["gstin"]) ? $d["gstin"] : '<span style="color:#999;">-</span>';

                        // Format contact with mobile
                        $contactDisplay = $d["contactPerson"] ?? '';
                        if (!empty($d["mobile"])) {
                            $contactDisplay .= $contactDisplay ? '<br><small>' . $d["mobile"] . '</small>' : $d["mobile"];
                        }
                        $d["contactPerson"] = $contactDisplay ?: '<span style="color:#999;">-</span>';
                    ?>
                        <tr><?php echo getMAction("mid", $d["distributorID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["distributorID"], strip_tags($d[$v[1]] ?? '')) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records">No distributors found.</div>
        <?php } ?>
    </div>
</div>
