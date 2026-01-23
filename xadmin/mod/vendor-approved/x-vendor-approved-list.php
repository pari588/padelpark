<?php
/**
 * Approved Vendors - List View
 * Supports projectID filtering via GET parameter
 */

// Check for project filter from menu params
$projectID = intval($_GET["projectID"] ?? 0);
$projectFilter = "";
$projectFilterVals = array();
$projectFilterTypes = "";

if ($projectID > 0) {
    $projectFilter = " AND projectID=?";
    $projectFilterVals = array($projectID);
    $projectFilterTypes = "i";
}

// Build project dropdown for search
$projectOpt = '<option value="">-- All Projects --</option>';
$DB->sql = "SELECT DISTINCT projectID FROM mx_vendor_onboarding WHERE projectID IS NOT NULL AND projectID > 0";
$projectRows = $DB->dbRows();
$projectNames = array(1 => "Sky Padel", 2 => "GamePark", 3 => "Other"); // Map project IDs to names
foreach ($projectRows as $p) {
    $sel = ($projectID == $p["projectID"]) ? ' selected' : '';
    $name = isset($projectNames[$p["projectID"]]) ? $projectNames[$p["projectID"]] : "Project " . $p["projectID"];
    $projectOpt .= '<option value="' . $p["projectID"] . '"' . $sel . '>' . $name . '</option>';
}

$arrSearch = array(
    array("type" => "text", "name" => "vendorID", "title" => "#ID", "where" => "AND vendorID=?", "dtype" => "i"),
    array("type" => "text", "name" => "vendorCode", "title" => "Code", "where" => "AND vendorCode LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "legalName", "title" => "Company", "where" => "AND legalName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "vendorType", "title" => "Type", "where" => "AND vendorType=?", "dtype" => "s", "option" => '
        <option value="">-- All --</option>
        <option value="Goods">Goods</option>
        <option value="Services">Services</option>
        <option value="Both">Both</option>
    '),
    array("type" => "select", "name" => "projectID", "title" => "Project", "where" => "AND projectID=?", "dtype" => "i", "option" => $projectOpt)
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);

// Build query with project filter
$DB->vals = array_merge(array($MXSTATUS), $projectFilterVals, $MXFRM->vals);
$DB->types = "i" . $projectFilterTypes . $MXFRM->types;
$DB->sql = "SELECT vendorID FROM `" . $DB->pre . "vendor_onboarding` WHERE status=? AND vendorStatus='Approved'" . $projectFilter . $MXFRM->where;
$DB->dbQuery();
$MXTOTREC = $DB->numRows;
if (!$MXFRM->where && !$projectFilter && $MXTOTREC < 1) $strSearch = "";
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
                array("Type", "vendorType", ' width="8%" align="center"'),
                array("Contact", "contactPersonName", ' width="12%" align="left"'),
                array("Email", "contactEmail", ' width="15%" align="left"'),
                array("City", "city", ' width="10%" align="left"'),
                array("Portal", "portalEnabled", ' width="8%" align="center"')
            );
            $DB->vals = array_merge(array($MXSTATUS), $projectFilterVals, $MXFRM->vals);
            $DB->types = "i" . $projectFilterTypes . $MXFRM->types;
            $DB->sql = "SELECT * FROM `" . $DB->pre . "vendor_onboarding` WHERE status=? AND vendorStatus='Approved'" . $projectFilter . $MXFRM->where . mxOrderBy("vendorID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Type badge
                        $typeBadges = array("Goods" => "badge-info", "Services" => "badge-warning", "Both" => "badge-primary");
                        $typeClass = isset($typeBadges[$d["vendorType"]]) ? $typeBadges[$d["vendorType"]] : "badge-secondary";
                        $d["vendorType"] = $d["vendorType"] ? '<span class="badge ' . $typeClass . '">' . htmlentities($d["vendorType"]) . '</span>' : "-";

                        // Portal status
                        $d["portalEnabled"] = $d["portalEnabled"] ? '<span class="badge badge-success">Active</span>' : '-';

                        // City
                        $d["city"] = $d["city"] ?: "-";
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
            <div class="no-records">No approved vendors found<?php echo $projectID ? ' for this project' : ''; ?></div>
        <?php } ?>
    </div>
</div>
