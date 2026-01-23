<?php

$dutyArr = array("1" => "S1", "2" => "S2", "3" => "S3", "4" => "S4", "5" => "Other");
$MountingArr = array("1" => "B3 - FOOT", "2" => "B5 - FLANGE", "3" => "B35 - FOOT CUM FLANGE", "4" => "V1 - VERTICAL FLANGE", "5" => "B14 - FACE MOUNTED", "6" => "Other");
$typeOfMotorArr = array("1" => "TEFC - SAFE AREA STANDARD", "2" => "FLAME PROOF - GAS GROUP IIA/IIB", "3" => "FLAME PROOF - GAS GROUP IIC", "4" => "INCREASED SAFETY - Ex'e'", "5" => "NON SPARKING - Ex'n'", "6" => "Other");
$rotorTypeArr = array("1" => "SQUIRREL CAGE", "2" => "SLIP RING");
$voltageArr = array("1" => "415", "2" => "380", "3" => "440", "4" => "460", "4" => "480", "5" => "Other");
$frequencyArr = array("1" => "50", "2" => "60");
$shaftExtensionArr = array("1" => "SINGLE", "2" => "DOUBLE", "3" => "Other");
$expectedDeliveryTimeArr = array("1" => "EX.STOCK", "2" => "1-4 WEEKS", "3" => "4-8 WEEKS", "4" => "MORE THAN 8 WEEKS", "5" => "Other");
$offerRequirementIsArr = array("1" => "Estimated", "2" => "Firm");
$requirementForRplcArr = array("1" => "Yes", "2" => "No");

$arrSearch = array(
    array("type" => "text", "name" => "productInquiryID",  "title" => "#ID", "where" => "AND productInquiryID=?", "dtype" => "i"),
    array("type" => "text", "name" => "userName",  "title" => "User Name", "where" => "AND userName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "userEmail",  "title" => "User Email", "where" => "AND userEmail LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "userMobile",  "title" => "User Mobile", "where" => "AND userMobile LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    // array("type" => "date", "name" => "fromDate", "title" => "From Date", "where" => "AND DATE(dateAdded) >=?", "dtype" => "s", "attr" => "style='width:160px;'"),
    // array("type" => "date", "name" => "toDate", "title" => "To Date", "where" => "AND DATE(dateAdded) <=?", "dtype" => "s", "attr" => "style='width:140px;'")
);

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
    <?php echo getPageNav('', '', array("add"));  ?>
    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "productInquiryID", ' width="2%" align="center"'),
                array("Company Name", "companyName", ' width="16%" nowrap align="left"'),
                array("User Name", "userName", ' width="16%" nowrap align="left"'),
                array("User Email", "userEmail", ' width="16%" nowrap align="left"'),
                array("User Mobile", "userMobile", '  width="16%" nowrap align="left"'),
                array("Make Of Motor", "makeOfMotor", '  width="16%"  nowrap align="left"'),
                array("kw", "kw", '  width="16%" nowrap align="left"'),
                array("hp", "hp", '  width="16%" nowrap align="left"'),
                array("dutyID", "dutyID", '  width="16%" nowrap align="left"'),
                array("rpm", "rpm", '  width="16%" nowrap align="left"'),
                array("Other Specification", "otherSpec", '  width="16%" nowrap align="left"'),

                array("mounting", "mountingID", '  width="16%" nowrap align="left"'),
                array("Motor Type", "typeOfMotorID", '  width="16%" nowrap align="left"'),
                array("Rotor Type", "rotorTypeID", '  width="16%" nowrap align="left"'),
                array("voltage", "voltageID", '  width="16%" nowrap align="left"'),
                array("frequency", "frequencyID", '  width="16%" nowrap align="left"'),
                array("Shaft Extension", "shaftExtensionID", '  width="16%" nowrap align="left"'),
                array("Expected Delivery Time", "expectedDeliveryTimeID", '  width="16%" nowrap align="left"'),
                array("Offer RequirementIs", "offerRequirementIs", '  width="16%" nowrap align="left"'),
                array("Rplc", "requirementIsForRplc", '  width="16%" nowrap align="left"'),

                // array("makeOfMotorD", "makeOfMotorD", '  width="16%" align="left"'),
                // array("kwD", "kwD", '  width="16%" align="left"'),
                // array("hpD", "hpD", '  width="16%" align="left"'),
                // array("rpmD", "rpmD", '  width="16%" align="left"'),
                // array("mounting", "mounting", '  width="16%" align="left"'),
                // array("poleID", "poleID", '  width="16%" align="left"'),
                // array("application", "application", '  width="16%" align="left"'),
                // array("dutyOther", "dutyOther", '  width="16%" align="left"'),
                // array("mountingOther", "mountingOther", '  width="16%" align="left"'),
                // array("typeOfMotorOther", "typeOfMotorOther", '  width="16%" align="left"'),
                // array("voltageOther", "voltageOther", '  width="16%" align="left"'),
                // array("shaftExtensionOther", "shaftExtensionOther", '  width="16%" align="left"'),
                // array("expectedDeliveryTimeOther", "expectedDeliveryTimeOther", '  width="16%" align="left"'),


                //array("Date Added", "dateAdded", ' width="16%"  nowrap align="left"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "`WHERE status=? " . $MXFRM->where . mxOrderBy("productInquiryID DESC ") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead>
                    <tr> <?php echo getListTitle($MXCOLS); ?></tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($DB->rows as $d) {

                        $d['dutyID'] = $dutyArr[$d['dutyID']] ?? "";
                        $d['mountingID'] = $MountingArr[$d['mountingID']] ?? "";
                        $d['typeOfMotorID'] = $typeOfMotorArr[$d['typeOfMotorID']] ?? "";
                        $d['rotorTypeID'] = $rotorTypeArr[$d['rotorTypeID']] ?? "";
                        $d['voltageID'] = $voltageArr[$d['voltageID']] ?? "";
                        $d['frequencyID'] = $frequencyArr[$d['frequencyID']] ?? "";
                        $d['shaftExtensionID'] = $shaftExtensionArr[$d['shaftExtensionID']] ?? "";
                        $d['expectedDeliveryTimeID'] = $expectedDeliveryTimeArr[$d['expectedDeliveryTimeID']] ?? "";
                        $d['offerRequirementIs'] = $offerRequirementIsArr[$d['offerRequirementIs']] ?? "";
                        $d['requirementIsForRplc'] = $requirementForRplcArr[$d['requirementIsForRplc']] ?? "";




                    ?>
                        <tr> <?php echo getMAction("mid", $d["productInquiryID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td <?php echo $v[2];
                                    ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3])) {
                                        echo getViewEditUrl("id=" . $d["productInquiryID"], $d[$v[1]]);
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