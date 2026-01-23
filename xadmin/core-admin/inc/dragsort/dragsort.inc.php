<?php

function getDragSortTitles($trash = true)
{
    global $MXCOLS, $MXDBLOG, $MXSET;

    if (!isset($trash))
        $trash = true;

    $str = '<ul class="dragsort-title">';

    if ($trash)
        $str .= getMAction("top", 0, $trash, true);

    if ($MXDBLOG && isset($MXSET["LOGACTIONDAYS"]) && $MXSET["LOGACTIONDAYS"] > 0) {
        $str .= '<li class="item center nprint" style="flex:0 0 45px">Log</li>';
    }

    foreach ($MXCOLS as $v) {
        $str .= '<li' . $v[2] . '>' . $v[0] . '</li>';
    }

    $str .= '</ul>';
    return $str;
}

function getDragSortRows($params = [])
{
    global $MXCOLS;
    $arr = [];
    $str = "";
    $arrDefauls = array("data" => array(), "pkName" => "", "imgCol" => "", "trash" => true);
    if (isset($params) && is_array($params))
        $arr = array_merge($arrDefauls, $params);

    $id = $arr["data"][$arr["pkName"]];

    //if ($arr["trash"])
    $str = getMAction("mid", $id, $arr["trash"], true);

    foreach ($MXCOLS as $v) {
        $strNm = '';
        if (isset($v[3]) && $v[3]) {
            $strNm = getViewEditUrl("id=" . $arr["data"][$arr["pkName"]], $arr["data"][$v[1]]);
        } else {
            $strNm =  $arr["data"][$v[1]];
        }

        if (isset($arr["imgCol"]) && isset($arr["data"][$v[1]]) && $v[1] == $arr["imgCol"]) {
            $arrFile = explode(",", $arr["data"][$v[1]]);
            $strNm = getFile(array("path" => "dragsort/" . $arrFile[0], "title" => "Menu Image"));
        }

        $str .= '<div' . $v[2] . '>' . $strNm . '</div>';
    }

    return '<div class="drag">' . $str . '</div>';
}


function buildDragSort($params = [])
{
    $arr = [];
    $arrDefauls = array("arrSort" => array(), "pkName" => "", "class" => "", "imgCol" => "", "trash" => true);
    if (isset($params) && is_array($params))
        $arr = array_merge($arrDefauls, $params);

    $str = '<ul' . $arr["class"] . '>';
    foreach ($arr["arrSort"] as $data) {
        $str .= '<li id="dragsort-' . $data[$arr["pkName"]] . '" class="dragsort-item">';
        $str .= getDragSortRows(array("data" => $data, "pkName" => $arr["pkName"], "imgCol" => $arr["imgCol"], "trash" => $arr["trash"]));
        if (!empty($data['childs'])) {
            $str .= buildDragSort(array("arrSort" => $data['childs'], "pkName" => $arr["pkName"], "imgCol" => $arr["imgCol"], "trash" => $arr["trash"]));
        }
        $str .= '</li>';
    }
    $str .= '</ul>';

    return $str;
}

function updateSortOrder()
{
    global $MXRES;
    if (isset($_POST["modName"])) {
        $mod = htmlspecialchars(trim($_POST["modName"]));
        $table = trim($_SESSION[SITEURL][$mod]["TBL"]);
        $pkName = trim($_SESSION[SITEURL][$mod]["PK"]);
        if (isset($_POST["dragsort"]) && count($_POST["dragsort"]) > 0) {
            global $DB;
            $xOrder = 0;
            foreach ($_POST["dragsort"] as $pkVal => $parentID) {
                $xOrder++;
                $DB->table = $DB->pre . $table;
                $DB->data = array("parentID" => intval($parentID), "xOrder" => $xOrder);
                if ($DB->dbUpdate("$pkName=?", "i", array($pkVal))) {
                    setResponse(["err" => 0, "param" => "id=$pkVal"]);
                    $MXRES["err"] = 0;
                    $MXRES["data"]["parentID"] = $parentID;
                    $MXRES["data"][$pkName] = $pkVal;
                }
            }
        } else {
            setResponse(["err" => 1]);
        }
    }
}

if (isset($_POST["xAction"])) {
    require_once("../../../../core/core.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "updateSortOrder":
                updateSortOrder();
                break;
        }
    }
    echo json_encode($MXRES);
}
