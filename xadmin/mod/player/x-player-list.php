<?php
$isActiveArr = ["data" => [1 => "Yes", 0 => "No"]];
$isActiveOpt = getArrayDD(array("data" => $isActiveArr, "selected" => ($_GET['isActive'] ?? "")));
$arrSearch = array(
    array("type" => "text", "name" => "customerID",  "title" => "#ID", "where" => "AND CV.customerID= ? ", "dtype" => "i"),
    array("type" => "text", "name" => "customerName",  "title" => "Company Name", "where" => "AND CV.customerName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "stateName", "title" => "State Name", "where" => "AND S.stateName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "cityName",  "title" => "City Name", "where" => "AND CT.cityName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "postalCode",  "title" => "Postal Code", "where" => "AND CV.postalCode LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "gstNumber",  "title" => "GST Number", "where" => "AND CV.gstNumber LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "isActive", "value" => $isActiveOpt, "title" => "Is Active", "where" => "AND CV.isActive=?", "dtype" => "i")
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;

array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT CV." . $MXMOD["PK"] . "  FROM `" . $DB->pre . $MXMOD["TBL"] . "` AS CV 
            LEFT JOIN " . $DB->pre . "state AS S ON CV.stateID = S.stateID  
            WHERE CV.status=?" . $MXFRM->where . mxWhere("CV.");

$DB->dbQuery();
$MXTOTREC = $DB->numRows;
if ($MXTOTREC < 1) {
    if ((!isset($MXFRM->where) || $MXFRM->where == "")) {
        $strSearch = "";
    }
}
echo $strSearch;
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <?php
        $colCLR = $colCLP = array();
        $strType = $whr = "";
        if ($MXTOTREC > 0) {
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT CV.*,S.stateName  FROM `" . $DB->pre . $MXMOD["TBL"] . "` AS CV 
                        LEFT JOIN " . $DB->pre . "state AS S ON CV.stateID = S.stateID  
                        WHERE CV.status=?" . $MXFRM->where . mxWhere("CV.") . mxOrderBy("CV.customerID  DESC , isActive DESC") . mxQryLimit();
            $DB->dbRows();
            $MXCOLS = array(
                array("#ID", "customerID", ' width="1%" align="center"', true),
                array("Company Name", "customerName", ' nowrap="nowrap" align="left"'),
                array("Email", "emailID", ' nowrap width="1%" align="left"'),
                array("Contact No", "phoneNo", ' nowrap width="1%"  align="left"'),
                array("state", "stateName", ' nowrap width="1%"  align="left"'),
                array("city", "cityName", ' nowrap width="1%"  align="left"'),
                array("Zip Code", "postalCode", ' nowrap width="1%"  align="center"'),
                array("gstNumber", "gstNumber", ' nowrap width="1%" align="left"'),
                array("panNumber", "panNumber", ' nowrap width="1%" align="left"'),
                array("Active", "isActive", ' nowrap="nowrap" width="1%"  align="center"')
            );
        ?>
            <table border="0" cellspacing="0" width="100%" cellpadding="8" class="tbl-list">
                <thead>
                    <tr>
                        <?php echo getListTitle($MXCOLS); ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $txtColour = '';
                    foreach ($DB->rows as $d) {
                        if ($d['isActive'] == 0) {
                            $txtColour = 'style="color:red;"';
                            $d['isActive'] = 'No';
                        } else if ($d['isActive'] == 1) {
                            $txtColour = '';
                            $d['isActive'] = 'Yes';
                        }
                    ?>
                        <tr>
                            <?php echo getMAction("mid", $d["customerID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td <?php echo $txtColour;
                                    echo $v[2]; ?>>
                                    <?php if (isset($v[3]) && $v[3]) {
                                        echo getViewEditUrl("id=" . $d["customerID"], $d[$v[1]]);
                                    } else {
                                        echo $d[$v[1]];
                                    } ?>
                                </td>
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
<div class="mxdialog detail-popup" style="display: none;">
    <div class="body" style="width: 500px;">
        <a href="#" class="close del rl"></a>
        <h2>Contact Person</h2>
        <div class="content">
        </div>
    </div>
</div>