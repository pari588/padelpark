<?php
class mxDb
{
    public $con, $pre, $table, $sql, $data, $insertID, $updatedID, $numRows, $rows, $row, $affectedRows, $types, $vals, $cols, $parentFld, $skipOrg;
    private $hasLang, $pkName, $dbstmt;

    public function __construct($DBHOST = "", $DBUSER = "", $DBPASS = "", $DBNAME = "", $charset = 'utf8mb4')
    {
        $this->con = new mysqli($DBHOST, $DBUSER, $DBPASS, $DBNAME);
        if ($this->con->connect_errno) {
            die("Failed to connect to MySQL: (" . $this->con->connect_errno . ") " . $this->con->connect_error);
        }
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->con->set_charset($charset);
        $this->dbReset();
    }

    private function dbReset()
    {
        $this->pre = "mx_";
        $this->table = $this->sql = $this->pkName = $this->types = "";
        $this->insertID = $this->updatedID = $this->numRows = $this->affectedRows = 0;
        $this->data = $this->row = $this->rows = $this->cols = $this->vals = $this->parentFld = array();
        $this->hasLang = $this->skipOrg = false;
    }

    public function getParseType($type = "")
    {
        $arrParse = array(
            "b" => array("tinyblob", "blob", "mediumblob", "longblob"),
            "i" => array("tinyint", "smallint", "mediumint", "int", "bit", "bigint", "boolean", "bool"),
            "d" => array("decimal", "float", "double"),
        );
        $p = "s";
        if (isset($type) && $type != "") {
            foreach ($arrParse as $k => $v) {
                if (in_array($type, $v))
                    $p = $k;
            }
        }
        return $p;
    }

    private function parseIn($table = "")
    {
        $this->cols = array();
        $this->vals = array();
        $this->types = "";
        $this->hasLang = false;

        $sql = "DESCRIBE " . $table;
        if ($qry = $this->con->query($sql)) {
            while ($col = $qry->fetch_assoc()) {
                $arr = explode("(", $col["Type"]);
                $type = trim($arr[0]);
                $name = $col["Field"];

                if ($col["Key"] == "PRI")
                    $this->pkName = $name;

                if ($name == "langChild")
                    $this->hasLang = true;

                if (array_key_exists($name, $this->data)) {
                    $val = $this->data[$name];
                    if ((!isset($val) || $val == "") && !isset($col["Default"]))
                        $val = NULL;

                    $this->cols[] = $name;
                    $this->vals[] = $val;
                    $this->types .= $this->getParseType($type);
                }
            }
            $qry->free();
        }
    }

    public function dbInsert()
    {
        if (isset($this->data) && is_array($this->data)) {
            $this->parseIn($this->table);
            global $MXSET;
            if ($MXSET["MULTILINGUAL"] == 1 && isset($_REQUEST["parentLID"]) && intval($_REQUEST["parentLID"]) > 0) {
                $flg = $this->updateLangChild(intval($_REQUEST["parentLID"]), "I");
                if ($flg == 1) {
                    return true;
                } else {
                    return false;
                }
            } else {
                $this->sql = "INSERT INTO `$this->table` (`" . implode('`,`', $this->cols) . "`) VALUES (" . implode(",", array_fill(0, count($this->cols), "?")) . ")";
                if (count($this->vals) > 0) {
                    if ($this->dbExecute()) {
                        $this->dbLogActionAddUpdate(0);
                        if ($this->hasLang)
                            $this->updateLangChild($this->insertID, "I");
                        $this->dbClose();
                        return true;
                    } else {
                        $this->dbClose();
                    }
                }
            }
        }
        return false;
    }

    public function dbUpdate($whereSql = "", $whereTypes = "", $whereVal = array())
    {
        if (isset($this->data) && is_array($this->data)) {
            $this->parseIn($this->table);
            global $MXSET;
            if ($MXSET["MULTILINGUAL"] == 1 && isset($_REQUEST["parentLID"]) && intval($_REQUEST["parentLID"]) > 0) {
                if ($this->hasLang) {
                    $flg = $this->updateLangChild(intval($_REQUEST["parentLID"]), "U");
                    if ($flg == 1) {
                        return true;
                    } else {
                        return false;
                    }
                }
            } else {

                if (count($whereVal) > 0) {
                    foreach ($whereVal as $val)
                        $this->vals[] = $val;
                    $this->types .= $whereTypes;
                }

                if (count($this->vals) > 0) {
                    $this->sql = "UPDATE `$this->table` SET " . implode("=?,", $this->cols) . "=?" . " WHERE $whereSql";
                    if ($this->dbExecute()) {
                        $this->dbClose();
                        $this->vals = $whereVal;
                        $this->types = $whereTypes;
                        $this->sql = "SELECT MAX($this->pkName) AS $this->pkName FROM `$this->table` WHERE $whereSql";
                        $row = $this->dbRow();
                        $this->updatedID = $row[$this->pkName];
                        $this->dbLogActionAddUpdate(1);
                        if ($this->hasLang)
                            $this->updateLangChild($this->updatedID, "U");
                        return true;
                    } else {
                        $this->dbClose();
                    }
                }
            }
        }
        return false;
    }

    public function dbQuery()
    {
        $this->rows = array();
        $this->row = array();
        if ($this->dbExecute()) {
            $result = $this->dbstmt->get_result();
            if (isset($this->dbstmt->affected_rows))
                $this->affectedRows = $this->dbstmt->affected_rows;
            if (isset($result->num_rows))
                $this->numRows = $result->num_rows;
            $this->dbClose();
            return true;
        } else {
            $this->dbClose();
        }
        return false;
    }

    public function dbRows()
    {
        $this->rows = array();
        if ($this->dbExecute()) {
            $result = $this->dbstmt->get_result();
            if (isset($result->num_rows))
                $this->numRows = $result->num_rows;
        }
        if (isset($result)) {
            if ($this->numRows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $this->rows[] = $row;
                }
            }
        }

        $this->dbClose();
        return $this->rows;
    }

    public function dbRow()
    {
        $this->row = array();
        if ($this->dbExecute()) {
            $result = $this->dbstmt->get_result();
            $this->numRows = $result->num_rows;
        }
        if (isset($result) > 0) {
            if ($this->numRows > 0) {
                $this->row = $result->fetch_assoc();
            }
        }

        $this->dbClose();
        return $this->row;
    }

    public function dbBind()
    {
        if (trim($this->types) !== "" && count($this->vals) > 0) {
            $refs = array();
            foreach ($this->vals as $k => $v)
                $refs[$k] = &$this->vals[$k];
            array_unshift($refs, $this->types);
            call_user_func_array(array($this->dbstmt, 'bind_param'), $refs);
        }
    }

    public function dbExecute()
    {
        mysqli_query($this->con, "SET SQL_MODE = ''");
        if (isset($this->sql) && trim($this->sql) !== "") {
            $this->dbstmt = $this->con->prepare($this->sql);
            if ($this->dbstmt !== false) {
                $this->dbBind();
                $this->dbstmt->execute();
                if (isset($this->dbstmt->affected_rows))
                    $this->affectedRows = $this->dbstmt->affected_rows;
                $this->insertID = $this->con->insert_id;
                return true;
            }
        }
        return false;
    }

    private function dbClose()
    {
        $this->sql = $this->types = "";
        $this->vals = array();
        if (isset($this->dbstmt)) {
            $this->dbstmt->close();
        }
    }

    public function showSql()
    {
        if (count($this->vals) && $this->types != '') {
            $sql = $this->sql;
            $arrTypes = str_split($this->types, 1);
            foreach ($arrTypes as  $k => $type) {
                $val = $this->vals[$k];
                if ($type == "s")
                    $val = "'$val'";
                $sql = preg_replace('/\?/', $val, $sql, 1);
            }
            echo "\n" . $sql . "\n";
        }
    }

    private function getAlias()
    {
        $alias = "";
        if (isset($this->sql) && $this->sql != "") {
            $string = preg_replace('/\s+/S', " ", $this->sql);
            $arrStr = explode(" ", strtoupper($string));
            //print_r($arrStr);
            if (count($arrStr) > 0) {
                $posFrom = array_search("FROM", $arrStr);
                if (isset($posFrom) && $posFrom > 0) {
                    //echo "\nposFrom:" . $posFrom;
                    if (isset($arrStr[$posFrom + 2])) {
                        //echo "\narrStr+2:" . $arrStr[$posFrom + 2];
                        if ($arrStr[$posFrom + 2] == "AS") {
                            if (isset($arrStr[$posFrom + 3])) {
                                $alias = $arrStr[$posFrom + 3] . ".";
                                //echo "\narrStr+3:" . $alias;
                            }
                        }
                    }
                }
            }
        }
        return $alias;
    }

    public function ifTableExists($table)
    {
        $qry = $this->con->query("SHOW TABLES LIKE '{$table}'");
        if ($qry->num_rows > 0) {
            $qry->free();
            return true;
        } else {
            $qry->free();
            return false;
        }
    }

    private function dbLogActionAddUpdate($actionType = 0)
    {
        global $MXSET;
        if (isset($MXSET["LOGACTIONDAYS"]) && $MXSET["LOGACTIONDAYS"] > 0) {
            $logDays = intval($MXSET["LOGACTIONDAYS"]);
            if (isset($MXSET["LOGIGNORETBL"]) && trim($MXSET["LOGIGNORETBL"]) !== "") {
                $arrTblSkip = explode(",", $MXSET["LOGIGNORETBL"]);
            }

            $arrTblSkip[] = "mx_x_log_action";
            $arrTblSkip[] = "mx_x_log_request";

            if (!in_array($this->table, $arrTblSkip)) {
                $tblName = $this->table;
                $pkName = $this->pkName;
                $pkValue = $this->insertID;
                $actionDate = date("Y-m-d H:i:s");
                $actionBy = 0;
                if (isAdminUser()) {
                    $actionBy = $_SESSION[SITEURL]['MXID'];
                } else if (isSiteUser()) {
                    if (isset($_SESSION[$MXSET["MXLOGINKEY"]]))
                        $actionBy = $_SESSION[$MXSET["MXLOGINKEY"]];
                }

                if ($actionBy > 0 || $actionBy == "SUPER") {
                    $flg = 0;
                    if ($actionType == 1) {
                        $pkValue = $this->updatedID;
                        $stmtC = $this->con->prepare("SELECT `pkValue` FROM `" . $this->pre . "x_log_action` WHERE `tblName`=? AND `pkName`=? AND `pkValue`=? AND actionType=? AND actionBy=?");
                        $stmtC->bind_param("ssiii", $tblName, $pkName, $pkValue, $actionType, $actionBy);
                        $stmtC->execute();
                        $result = $stmtC->get_result();
                        if (isset($result->num_rows) && $result->num_rows < 1) {
                            $flg = 1;
                        }
                        $stmtC->close();
                        $result->close();
                    }
                    if ($actionType == 0 || $flg == 1) {
                        $stmt = $this->con->prepare("INSERT INTO `" . $this->pre . "x_log_action` (actionBy, actionDate, tblName, pkName, pkValue, actionType) VALUES (?, ?, ?, ?, ?, ?)");
                    } else if ($this->affectedRows) {
                        $stmt = $this->con->prepare("UPDATE `" . $this->pre . "x_log_action` SET actionBy=?, actionDate=? WHERE `tblName`=? AND `pkName`=? AND `pkValue`=? AND actionType=?");
                    }

                    $stmt->bind_param("isssii", $actionBy, $actionDate, $tblName, $pkName, $pkValue, $actionType);
                    $stmt->execute();
                    $stmt->close();

                    $stmtC = $this->con->prepare("DELETE FROM " . $this->pre . "x_log_action WHERE actionDate < (NOW() - INTERVAL ? DAY)");
                    $stmtC->bind_param("i", $logDays);
                    $stmtC->execute();
                    $stmtC->close();
                }
            }
        }
    }

    private function updateLangChild($parentLID = 0, $action = "I")
    {
        global $MXSET;
        $flg = 0;
        if ($MXSET["MULTILINGUAL"] == 1 && isset($_REQUEST["langCode"]) && $_REQUEST["langCode"] != "") {
            if ($parentLID > 0) {
                $this->data["parentLID"] = $parentLID;
                $table = $this->table;
                $pkName = $this->pkName;
                if ($MXSET["LANGTYPE"] == 1) {
                    if ($this->ifTableExists($table . "_l")) {
                        $table = $table . "_l";
                        $this->parseIn($table);
                        if (count($this->vals) > 0) {
                            if ($action == "I") {
                                $this->sql = "INSERT INTO `$table` (`" . implode('`,`', $this->cols) . "`) VALUES (" . implode(",", array_fill(0, count($this->cols), "?")) . ")";
                            } else {
                                $this->vals[] = $parentLID;
                                $this->vals[] = $_REQUEST["langCode"];
                                $this->types .= "is";
                                $this->sql = "UPDATE `$table` SET " . implode(" = ? ,", $this->cols) . " = ? " . " WHERE `parentLID` = ? AND langCode=?";
                            }
                            if ($this->dbExecute()) {
                                $this->insertID = $parentLID;
                                $flg = 1;
                            }
                        }
                    }
                }

                if ($MXSET["LANGTYPE"] == 1 && $table == $this->table) {
                    $flg = 1;
                } else {
                    $sql = "SELECT GROUP_CONCAT(langCode SEPARATOR ',') AS langChild FROM `$table` WHERE `parentLID` = ?";

                    $stmt = $this->con->prepare($sql);
                    $stmt->bind_param("i", $parentLID);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $stmt->close();
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $sql = "UPDATE `$this->table` SET langChild=? WHERE `$pkName` = ?";
                        $stmt = $this->con->prepare($sql);
                        $stmt->bind_param("si", $row["langChild"], $parentLID);
                        $stmt->execute();
                        $stmt->close();
                        $flg = 1;
                    }
                    $result->close();
                }
            }
            $this->data = [];
            return $flg;
        }
    }
}
