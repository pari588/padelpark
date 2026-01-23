<?php
//Start: Custome where to show current lead users.
$leadType = "";
$leadVals = array();
$customWhr = "";
$userID = $_SESSION[SITEURL]["MXID"];
// To check if admin login then show all list
$DB->vals = array(1, $userID);
$DB->types = "ii";
$DB->sql = "SELECT R.roleID  FROM `" . $DB->pre . "x_admin_role` AS R
LEFT JOIN `" . $DB->pre . "x_admin_user` AS AU ON AU.roleID = R.roleID
WHERE AU.status=? AND AU.userID=?";
$roleID =  $DB->dbRow();
// End.
if ($userID != "SUPER" && $roleID["roleID"] != 1) {
    $leadVals = array($userID);
    $leadType  = "i";
    $customWhr = " AND currentLead=?";
}
// End.
$adminUserWhr = array("sql" => "status=? ", "types" => "i", "vals" => array(1));
//$adminUseArr = getDataArray($DB->pre . "x_admin_user", "userID", "displayName", $adminUserWhr);

$params = ["table" => $DB->pre . "x_admin_user", "key" => "userID", "val" => "displayName", "where" => $adminUserWhr];
$adminUseArr  = getDataArray($params);

$arrSearch = array(
    array("type" => "text", "name" => "leadID",  "title" => "#ID", "where" => "AND leadID=?", "dtype" => "i", "attr" => "style='width:50px;'"),
    array("type" => "text", "name" => "partyName",  "title" => "Party Name", "where" => "AND partyName LIKE CONCAT('%',?,'%')", "dtype" => "s", "attr" => "style='width:110px;'"),
    array("type" => "text", "name" => "partyAddr",  "title" => "Address", "where" => "AND partyAddr LIKE CONCAT('%',?,'%')", "dtype" => "s", "attr" => "style='width:110px;'"),
    array("type" => "text", "name" => "leadLocation",  "title" => "Location", "where" => "AND leadLocation LIKE CONCAT('%',?,'%')", "dtype" => "s", "attr" => "style='width:90px;'"),
    array("type" => "text", "name" => "contactPerson",  "title" => "Contact Person", "where" => "AND contactPerson LIKE CONCAT('%',?,'%')", "dtype" => "s", "attr" => "style='width:110px;'"),
    array("type" => "text", "name" => "contactNumber",  "title" => "Contact Number", "where" => "AND contactNumber LIKE CONCAT('%',?,'%')", "dtype" => "s", "attr" => "style='width:115px;'"),
    array("type" => "text", "name" => "remark",  "title" => "Remark", "where" => "AND remark LIKE CONCAT('%',?,'%')", "dtype" => "s", "attr" => "style='width:105px;'"),
    array("type" => "text", "name" => "geolocation",  "title" => "Geolocation", "where" => "AND geolocation LIKE CONCAT('%',?,'%')", "dtype" => "s", "attr" => "style='width:105px;'"),
    array("type" => "date", "name" => "fromDate", "title" => "From Date", "where" => "AND DATE(leadDate) >=?", "dtype" => "s", "attr" => "style='width:110px;'"),
    array("type" => "date", "name" => "toDate", "title" => "To Date", "where" => "AND DATE(leadDate) <=?", "dtype" => "s", "attr" => "style='width:110px;'")
);
// END
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
$DB->vals = array_merge($DB->vals, $leadVals);
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types . $leadType;
$DB->sql = "SELECT " . $MXMOD["PK"] . " FROM `" . $DB->pre . $MXMOD["TBL"] . "`  WHERE status=?"  . $MXFRM->where . $customWhr;
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
                array("Party Name", "partyName", ' align="left"'),
                array("Address", "partyAddr", ' align="left"'),
                array("Location", "leadLocation", ' align="left"'),
                array("Contact Person", "contactPerson", ' align="left"'),
                array("Contact Number", "contactNumber", ' align="left"'),
                array("Remark", "remark", ' align="left"'),
                array("Geolocation", "geolocation", ' align="left"'),
                array("Date", "leadDate", ' align="left"')
            );
            $DB->vals = $MXFRM->vals;
            $DB->vals = array_merge($DB->vals, $leadVals);
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types . $leadType;
            $DB->sql = "SELECT *  FROM `" . $DB->pre . $MXMOD["TBL"] . "`WHERE status=? "  . $MXFRM->where . $customWhr . mxOrderBy(" leadID DESC ") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead>
                    <tr> <?php echo getListTitle($MXCOLS); ?></tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($DB->rows as $d) {  ?>
                        <tr> <?php echo getMAction("mid", $d["leadID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td <?php echo $v[2];
                                    ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3])) {
                                        echo getViewEditUrl("id=" . $d["leadID"], $d[$v[1]]);
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