<?php
// Mod type dropdown for searching.
$ModTypeArr = ["1" => "Motor", "2" => "Pump", "4" => "Other"];
//$modTypeDD = getArrayDD($ModTypeArr, $_GET["modType"] ?? 0);
$modTypeDD = getArrayDD(["data" => array("data" => $ModTypeArr), "selected" => ($_GET["modType"] ?? 0)]);

// End. 
$arrSearch = array(
    array("type" => "text", "name" => "userID",  "title" => "#ID", "where" => "AND userID=?", "dtype" => "i"),
    array("type" => "text", "name" => "userName",  "title" => "First Name", "where" => "AND userName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "userLastName",  "title" => "Last Name", "where" => "AND userLastName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "userEmail",  "title" => "Email", "where" => "AND userEmail LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "userMobile",  "title" => "Mobile Number", "where" => "AND userMobile LIKE CONCAT('%',?,'%')", "dtype" => "s"),
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
    <?php echo getPageNav('', '', array("add")); ?>
    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "userID", ' width="8%" align="center"'),
                array("First Name", "userName", ' width="15%" align="left"'),
                array("Last Name", "userLastName", ' width="15%" align="left"'),
                array("Email", "userEmail", ' width="20%" align="left"'),
                array("Mobile", "userMobile", ' width="12%" align="left"'),
                array("Message", "userMessage", ' width="30%" nowrap align="left"'),
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "`WHERE status=? " . $MXFRM->where . mxOrderBy("userID DESC ") . mxQryLimit();
            $rt =  $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead>
                    <tr> <?php echo getListTitle($MXCOLS); ?></tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($DB->rows as $d) {
                        $d["modType"] = $ModTypeArr[$d["modType"]] ?? "";
                    ?>
                        <tr> <?php echo getMAction("mid", $d["userID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td <?php echo $v[2];
                                    ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3])) {
                                        echo getViewEditUrl("id=" . $d["userID"], $d[$v[1]]);
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