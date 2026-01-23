<?php
$arrSearch = array(
    array("type" => "text", "name" => "userID", "title" => "#ID", "where" => "AND userID=?", "dtype" => "i"),
    array("type" => "text", "name" => "userName", "title" => "User Name", "where" => "AND userName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "userEmail", "title" => "User Email", "where" => "AND userEmail LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "userMobileNo", "title" => "User Mobile", "where" => "AND userMobileNo LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "userLoginOTP", "title" => "Login OTP", "where" => "AND userLoginOTP LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "userCity", "title" => "User City", "where" => "AND userCity LIKE CONCAT('%',?,'%')", "dtype" => "s")
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT " . $MXMOD['PK'] . " FROM `" . $DB->pre . $MXMOD["TBL"] . "` 
            WHERE status=?" . $MXFRM->where;
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
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "userID", ' width="1%" align="center" title="ID"', true),
                array("Username", "userName", ' width="12%" align="left" '),
                array("User Email", "userEmail", ' width="12%" align="left" '),
                array("User Mobile", "userMobileNo", ' width="12%" align="left" '),
                array("Login OTP", "userLoginOTP", ' width="12%" align="left" '),
                array("City", "userCity", ' width="12%" align="left" ')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT userID,userName,userEmail,userMobileNo,userLoginOTP,userCity,userType
                        FROM `" . $DB->pre . $MXMOD["TBL"] . "`
                        WHERE status=?" . $MXFRM->where . mxWhere() . mxOrderBy(" userID DESC ") . " LIMIT $MXOFFSET,$MXSHOWREC";
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="2" cellpadding="6" class="tbl-list">
                <thead>
                    <tr>
                        <?php echo getListTitle($MXCOLS); ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($DB->rows as $d) {
                    ?>
                        <tr>
                            <?php echo getMAction("mid", $d["userID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3])) {
                                        echo getViewEditUrl("id=" . $d["userID"], $d[$v[1]]);
                                    } else {
                                        echo $d[$v[1]];
                                    }
                                    ?>
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