<?php
/**
 * Vendor Onboarding - List View
 * With Category and Project filtering
 */

// Build category dropdown
$categoryOpt = '<option value="">-- All Categories --</option>';
$DB->sql = "SELECT categoryID, categoryName FROM mx_vendor_category WHERE status = 1 ORDER BY sortOrder ASC";
$catRows = $DB->dbRows();
foreach ($catRows as $cat) {
    $categoryOpt .= '<option value="' . $cat["categoryID"] . '">' . htmlentities($cat["categoryName"]) . '</option>';
}

// Build project dropdown
$projectOpt = '<option value="">-- All Projects --</option>';
$projects = array(1 => "Sky Padel", 2 => "GamePark", 3 => "Other");
foreach ($projects as $pid => $pname) {
    $projectOpt .= '<option value="' . $pid . '">' . $pname . '</option>';
}

$arrSearch = array(
    array("type" => "text", "name" => "vendorID", "title" => "#ID", "where" => "AND v.vendorID=?", "dtype" => "i"),
    array("type" => "text", "name" => "vendorCode", "title" => "Code", "where" => "AND v.vendorCode LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "legalName", "title" => "Company", "where" => "AND v.legalName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "vendorCategory", "title" => "Category", "where" => "AND v.vendorCategory=?", "dtype" => "i", "option" => $categoryOpt),
    array("type" => "select", "name" => "projectID", "title" => "Project", "where" => "AND v.projectID=?", "dtype" => "i", "option" => $projectOpt),
    array("type" => "select", "name" => "registrationSource", "title" => "Source", "where" => "AND v.registrationSource=?", "dtype" => "s", "option" => '
        <option value="">-- All --</option>
        <option value="Public">Public Registration</option>
        <option value="Invitation">Invitation</option>
        <option value="Admin">Admin Created</option>
    '),
    array("type" => "select", "name" => "vendorStatus", "title" => "Status", "where" => "AND v.vendorStatus=?", "dtype" => "s", "option" => '
        <option value="">-- All --</option>
        <option value="Pending">Pending</option>
        <option value="Approved">Approved</option>
        <option value="Disapproved">Disapproved</option>
        <option value="Blocked">Blocked</option>
    ')
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT v.vendorID FROM `" . $DB->pre . "vendor_onboarding` v WHERE v.status=?" . $MXFRM->where;
$DB->dbQuery();
$MXTOTREC = $DB->numRows;
if (!$MXFRM->where && $MXTOTREC < 1) $strSearch = "";
echo $strSearch;

// Project name mapping
$projectNames = array(1 => "Sky Padel", 2 => "GamePark", 3 => "Other");

?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <?php if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "vendorID", ' width="4%" align="center"', true),
                array("Code", "vendorCode", ' width="8%" align="left"'),
                array("Company Name", "legalName", ' width="14%" align="left"'),
                array("Category", "categoryName", ' width="8%" align="left"'),
                array("Project", "projectID", ' width="6%" align="center"'),
                array("Source", "registrationSource", ' width="6%" align="center"'),
                array("Contact", "contactPersonName", ' width="10%" align="left"'),
                array("Email", "contactEmail", ' width="12%" align="left"'),
                array("Status", "vendorStatus", ' width="8%" align="center"'),
                array("Portal", "portalEnabled", ' width="5%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT v.*, c.categoryName
                        FROM `" . $DB->pre . "vendor_onboarding` v
                        LEFT JOIN `" . $DB->pre . "vendor_category` c ON v.vendorCategory = c.categoryID
                        WHERE v.status=?" . $MXFRM->where . mxOrderBy("v.vendorID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Status badge
                        $statusBadges = array(
                            "Pending" => "badge-warning",
                            "Approved" => "badge-success",
                            "Disapproved" => "badge-danger",
                            "Blocked" => "badge-dark",
                            "Info Requested" => "badge-info",
                            "Under Review" => "badge-primary"
                        );
                        $statusClass = isset($statusBadges[$d["vendorStatus"]]) ? $statusBadges[$d["vendorStatus"]] : "badge-secondary";
                        $d["vendorStatus"] = '<span class="badge ' . $statusClass . '">' . htmlentities($d["vendorStatus"]) . '</span>';

                        // Portal status
                        $d["portalEnabled"] = $d["portalEnabled"] ? '<span class="badge badge-success">Yes</span>' : '-';

                        // Type
                        $d["vendorType"] = $d["vendorType"] ?: "-";

                        // Category
                        $d["categoryName"] = $d["categoryName"] ?: "-";

                        // Project
                        $d["projectID"] = isset($projectNames[$d["projectID"]]) ? '<span class="badge badge-info">' . $projectNames[$d["projectID"]] . '</span>' : "-";

                        // Registration Source
                        $sourceBadges = array(
                            "Public" => '<span class="badge badge-warning" title="Self-registered via portal">Public</span>',
                            "Invitation" => '<span class="badge badge-primary" title="Invited by admin">Invite</span>',
                            "Admin" => '<span class="badge badge-secondary" title="Created by admin">Admin</span>'
                        );
                        $d["registrationSource"] = isset($sourceBadges[$d["registrationSource"]]) ? $sourceBadges[$d["registrationSource"]] : "-";
                    ?>
                        <tr><?php echo getMAction("mid", $d["vendorID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("vendorID=" . $d["vendorID"], strip_tags($d[$v[1]])) : ($d[$v[1]] ?? ""); ?></td>
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
