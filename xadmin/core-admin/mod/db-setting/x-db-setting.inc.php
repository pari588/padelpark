<?php
function getDBSTables()
{
    $arrTbl = mxGetAllTables();
    $arrAddTbl = array("mx_x_menu", "mx_x_admin_user","mx_x_setting");
    foreach ($arrTbl as $tName) {
        if (strpos($tName, 'mx_x_') === false || in_array($tName, $arrAddTbl)) {
            $arrTables[] = $tName;
        }
    }
    return $arrTables;
}

function getDBSCols()
{
    global $MXSET;
    $arrML = array(
        "mlparent" => array("langChild" => "VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL"),
        "mlchild" => array(
            "langCode" => "VARCHAR(4) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL",
            "langChild" => "VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL"
        )
    );

    $arrL = array(
        "language" => array(
            "langCode" => "VARCHAR(4) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL",
            "langChild" => "VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL",
            "parentLID" => "INT(11) NOT NULL DEFAULT '0'"
        )
    );

    $arrOrg = array(
        "organization" => array("orgID" => "INT(11) NOT NULL DEFAULT '0'")
    );

    $arrExtraCol = array(
        "seouri" => array("seoUri" => "VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL"),
        "status" => array("status" => "TINYINT(1) NOT NULL DEFAULT '1'")
    );

    if (isset($MXSET["MULTIORG"]) && $MXSET["MULTIORG"] == 1) {
        $arrExtraCol = array_merge($arrExtraCol, $arrOrg);
    }

    if (isset($MXSET["MULTILINGUAL"]) && $MXSET["MULTILINGUAL"] == 1) {
        if (isset($MXSET["LANGTYPE"]) && $MXSET["LANGTYPE"] == 1) {
            $arrExtraCol = array_merge($arrExtraCol, $arrML);
        } else {
            $arrExtraCol = array_merge($arrExtraCol, $arrL);
        }
    }
    return $arrExtraCol;
}

function getTablePk($table = "")
{
    $pkName = "";
    if (isset($table) && $table !== "") {
        global $DB;
        $DB->sql = "DESCRIBE " . $table;
        $DB->dbRows();
        if ($DB->numrows > 0) {
            foreach ($DB->rows as $d) {
                if ($d["Key"] == "PRI")
                    $pkName = $d["Field"];
            }
        }
    }
    return $pkName;
}

function ifTblColExists($table = "", $col = "")
{
    $flg = false;
    if (isset($col) && $col !== "" && isset($table) && $table !== "") {
        global $DB;
        $DB->sql = "SHOW COLUMNS from `$table` LIKE '" . $col . "'";
        $DB->dbQuery();
        if ($DB->numRows > 0) {
            $flg = true;
        }
    }
    return $flg;
}

/*function updateTableCols()
{
    global $MXRES;
    $flgAdded = $flgUpdated = $flgDel = 0;
    print_r($_POST["tables"]);
    if (isset($_POST["tables"]) && count($_POST["tables"]) > 0) {
        global $DB;
        $arrExtraCol = getDBSCols();
        $arrAllTbl =getDBSTables();
        foreach ($_POST["tables"] as $table => $arrColType) {
            ///$arrFld = mxGetTableFlds($table, array());

            foreach ($arrColType as $colType) {
                if (isset($arrExtraCol[$colType])) {
                    foreach ($arrExtraCol[$colType] as $fieldName => $dataType) {
                        $qType = "ADD";
                        if (ifTblColExists($table, $fieldName)) {
                            $flgUpdated++;
                            //if (array_key_exists($fieldName, $arrFld)) {
                            $qType = "MODIFY";
                            //$DB->sql = "ALTER TABLE `$table` DROP `$fieldName`";
                            //$DB->dbQuery();
                            $flgAdded++;
                        } else {
                            $flgAdded++;
                        }
                        $DB->sql = "ALTER TABLE `$table` $qType `$fieldName` $dataType";
                        //echo "\n".$DB->sql;
                        //$DB->dbQuery();
                        if (isset($arrAllTbl[$table]))
                            unset($arrAllTbl[$table]);
                    }
                }
            }
        }
        //print_r($arrAllTbl);
    }
    
    $MXRES["msg"] = "Columns Added: $flgAdded<br />Columns Updated: $flgUpdated<br />Columns Deleted: $flgDel";
}*/

function updateTableCols()
{
    global $MXRES;
    $flgAdded = $flgUpdated = $flgDel = 0;
    //print_r($_POST["tables"]);

    // $arrAllTbl = getDBSTables();
    // if (isset($_POST["tables"]) && count($_POST["tables"]) > 0) {
    //     foreach ($arrAllTbl as $table) {
    //         if(isset($_POST["tables"][$table])){

    //         }
    //     }
    // }

    if (isset($_POST["tables"]) && count($_POST["tables"]) > 0) {
        global $DB;
        $arrExtraCol = getDBSCols();

        foreach ($_POST["tables"] as $table => $arrColType) {
            ///$arrFld = mxGetTableFlds($table, array());

            foreach ($arrColType as $colType) {
                if (isset($arrExtraCol[$colType])) {
                    foreach ($arrExtraCol[$colType] as $fieldName => $dataType) {
                        $qType = "ADD";
                        if (ifTblColExists($table, $fieldName)) {
                            $flgUpdated++;
                            //if (array_key_exists($fieldName, $arrFld)) {
                            $qType = "MODIFY";
                            //$DB->sql = "ALTER TABLE `$table` DROP `$fieldName`";
                            //$DB->dbQuery();
                            $flgAdded++;
                        } else {
                            $flgAdded++;
                        }
                        $DB->sql = "ALTER TABLE `$table` $qType `$fieldName` $dataType";
                        //echo "\n".$DB->sql;
                        $DB->dbQuery();
                        if (isset($arrAllTbl[$table]))
                            unset($arrAllTbl[$table]);
                    }
                }
            }
        }
        //print_r($arrAllTbl);
    }

    $MXRES["msg"] = "Columns Added: $flgAdded\nColumns Updated: $flgUpdated\nColumns Deleted: $flgDel";
}

function getParseType($type = "")
{
    $arrType = array(
        "number" => array("tinyint", "smallint", "mediumint", "int", "bigint", "decimal", "float", "double", "bit"),
        "boolean" => array("boolean", "bool"),
        "string" => array("char", "varchar", "binary", "varbinary", "tinytext", "text", "mediumtext", "longtext", "enum", "set"),
        "datetime" => array("date", "time", "datetime", "timestamp", "year"),
        "spatial" => array("geometry", "point", "linestring", "polygon", "geometrycollection", "multilinestring", "multipoint", "multipolygon"),
        "json" => array("json"),
        "blob" => array("tinyblob", "blob", "mediumblob", "longblob")
    );

    $parseType = "";
    foreach ($arrType as $group => $groupVal) {
        if (in_array($type, $groupVal)) {
            $parseType = $group;
        }
    }
    return $parseType;
}

function restructureDB()
{
    global $DBNAME, $DB;
    $msg = "";
    $DB->sql = "SELECT ENGINE,TABLE_COLLATION,TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE table_schema = '$DBNAME'";
    $tblList = $DB->dbRows();
    //$tblList = array(array("TABLE_NAME" => "mx_x_admin_role"));

    foreach ($tblList as $tblInfo) {
        $tblName = $tblInfo['TABLE_NAME'];
        $engine = "INNODB";
        $collate = "utf8_unicode_ci";
        $charSet = "utf8";

        $DB->sql = "SELECT COLUMN_DEFAULT,COLUMN_NAME,DATA_TYPE,COLUMN_TYPE,COLUMN_KEY,CHARACTER_SET_NAME,COLLATION_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$tblName' AND TABLE_SCHEMA = '$DBNAME'";
        $colList = $DB->dbRows();
        foreach ($colList as $colInfo) {
            $defaultSet = $colInfo['COLUMN_DEFAULT'];
            $colName = $colInfo['COLUMN_NAME'];
            $colDataType = $colInfo['DATA_TYPE'];
            $colType = $colInfo['COLUMN_TYPE'];

            $defC = "";
            if ($colInfo['COLUMN_KEY'] != "PRI") {
                $groupName = getParseType($colDataType);
                if ($groupName !== "") {
                    if (($groupName == "number" || $groupName == "boolean") && $defaultSet == "") {
                        $defC = ' DEFAULT 0';
                    } else if ($groupName == "datetime" || $groupName == "string") {
                        if ($groupName == "string") {
                            if ($colInfo['CHARACTER_SET_NAME'] !== $charSet)
                                $defC .= " CHARACTER SET $charSet";

                            if ($colInfo['COLLATION_NAME'] !== $collate)
                                $defC .= " COLLATE $collate NULL";
                        }
                        if ($defaultSet == "" || ($groupName == "datetime" && $defaultSet !== NULL))
                            $defC .= ' DEFAULT NULL';
                    }

                    if ($defC !== "") {
                        $DB->sql = "SET SESSION sql_mode = ''";
                        $DB->dbQuery();
                        $DB->sql = "ALTER TABLE " . $tblName . " CHANGE `$colName` `$colName` " . $colType . " $defC";
                        if (!$DB->dbQuery())
                            $msg .= "<br>\nError while changing column defination for cloumn $colName of $tblName";
                    }
                    if ($groupName == "datetime" && $defaultSet == NULL) {
                        $DB->sql = "UPDATE $tblName  SET `$colName`= NULL WHERE `$colName` = '0000-00-00 00:00:00'";
                        if (!$DB->dbQuery())
                            $msg .= "<br>\nError while setting default value for column $colName of $tblName";
                    }
                }
            }
        }
        $defT = "";
        if ($tblInfo['ENGINE'] !== $engine)
            $defT .= " ENGINE=$engine";

        if ($tblInfo['TABLE_COLLATION'] !== $collate)
            $defT .= " CHARACTER SET $charSet COLLATE $collate";

        if ($defT !== "") {
            $DB->sql = "ALTER TABLE `$tblName` $defT";
            if (!$DB->dbQuery())
                $msg .= "<br>\nError while changing table defination $tblName";
        }
    }
    if ($msg == "")
        $msg = "Database restructuring successfull";
    return $msg;
}


if (isset($_POST["xAction"])) {
    require("../../../../core/core.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "updateTableCols":
                updateTableCols();
                break;
            case "restructureDB":
                $MXRES["err"] = 0;
                $MXRES["msg"] = restructureDB();
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "x_template", "PK" => "templateID"));
}
