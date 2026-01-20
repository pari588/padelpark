<?php
// START : search array
$arrSearch = array(
    array("type" => "text", "name" => "leadID",  "title" => "#ID", "where" => "AND leadID=?", "dtype" => "i"),
    array("type" => "text", "name" => "leadNo",  "title" => "Lead No", "where" => "AND leadNo LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "clientName",  "title" => "Client Name", "where" => "AND clientName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "clientPhone",  "title" => "Phone", "where" => "AND clientPhone LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "siteCity",  "title" => "City", "where" => "AND siteCity LIKE CONCAT('%',?,'%')", "dtype" => "s")
);
// END

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);

$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT " . $MXMOD["PK"] . " FROM `" . $DB->pre . $MXMOD["TBL"] . "`  WHERE status=?" . $MXFRM->where;
$DB->dbQuery();
$MXTOTREC = $DB->numRows;

if (!$MXFRM->where && $MXTOTREC < 1)
    $strSearch = "";

echo $strSearch;
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "leadID", ' width="1%" align="center"', true),
                array("Lead No", "leadNo", ' width="8%" nowrap align="left"'),
                array("Date", "leadDate", ' width="8%" align="center"'),
                array("Client", "clientName", ' width="15%" align="left"'),
                array("Phone", "clientPhone", ' width="10%" align="center"'),
                array("City", "siteCity", ' width="8%" align="left"'),
                array("Requirement", "courtRequirement", ' width="12%" align="left"'),
                array("Status", "leadStatus", ' width="12%" align="center"'),
                array("Assigned To", "assignedTo", ' width="8%" align="left"'),
                array("Actions", "actions", ' width="18%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT l.leadID, l.leadNo, l.leadDate, l.clientName, l.clientPhone, l.siteCity,
                        l.courtRequirement, l.leadStatus, l.assignedTo, u.displayName as assignedName
                        FROM `" . $DB->pre . $MXMOD["TBL"] . "` l
                        LEFT JOIN `" . $DB->pre . "x_admin_user` u ON l.assignedTo = u.userID
                        WHERE l.status=? " . $MXFRM->where . mxOrderBy("l.leadID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead>
                    <tr> <?php echo getListTitle($MXCOLS); ?></tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($DB->rows as $d) {
                        // Format lead date
                        if (isset($d["leadDate"]) && $d["leadDate"] != "0000-00-00") {
                            $d["leadDate"] = date("d-M-Y", strtotime($d["leadDate"]));
                        } else {
                            $d["leadDate"] = "-";
                        }

                        // Format lead status with badges
                        $statusClass = "";
                        switch($d["leadStatus"]) {
                            case "New": $statusClass = "badge-info"; break;
                            case "Contacted": $statusClass = "badge-primary"; break;
                            case "Site Visit Scheduled": $statusClass = "badge-warning"; break;
                            case "Site Visit Done": $statusClass = "badge-secondary"; break;
                            case "Quotation Sent": $statusClass = "badge-warning"; break;
                            case "Converted": $statusClass = "badge-success"; break;
                            case "Lost": $statusClass = "badge-danger"; break;
                        }
                        $d["leadStatus"] = '<span class="badge ' . $statusClass . '">' . $d["leadStatus"] . '</span>';

                        // Use assigned name
                        $d["assignedTo"] = $d["assignedName"] ?? "-";

                        // Add action links with proper URLs
                        $d["actions"] = '<a href="' . ADMINURL . '/sky-padel-site-visit-add/?leadID=' . $d["leadID"] . '">Site Visit</a> | ';
                        $d["actions"] .= '<a href="' . ADMINURL . '/sky-padel-quotation-add/?leadID=' . $d["leadID"] . '">Quotation</a>';
                    ?>
                        <tr> <?php echo getMAction("mid", $d["leadID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td <?php echo $v[2];
                                    ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3]) && $v[3] != "") {
                                        echo getViewEditUrl("id=" . $d["leadID"], strip_tags($d[$v[1]]));
                                    } else {
                                        echo $d[$v[1]] ?? "";
                                    }
                                    ?></td>
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
