<?php

function addKnowledgeCenter()
{
    global $DB;
    $_POST['seoUri'] = makeSeoUri($_POST['knowledgeCenterTitle'], ['knowledge_center']);
    $_POST["knowledgeCenterImage"] = mxGetFileName("knowledgeCenterImage");
    $DB->table = $DB->pre . "knowledge_center";
    if (!isset($_POST["isFeatured"]))
        $_POST["isFeatured"] = 0;

    if (!isset($_POST["isPopular"]))
        $_POST["isPopular"] = 0;

    if (!isset($_POST["isPinned"]))
        $_POST["isPinned"] = 0;

    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $knowledgeCenterID = $DB->insertID;
        if ($knowledgeCenterID) {
            setResponse(array("err" => 0, "param" => "id=$knowledgeCenterID"));
        }
    } else {
        setResponse(array("err" => 1));
    }
}

function updateKnowledgeCenter()
{
    global $DB;
    $knowledgeCenterID = intval($_POST["knowledgeCenterID"]);
    $_POST['seoUri'] = makeSeoUri($_POST['knowledgeCenterTitle'], ['knowledge_center']);
    $_POST["knowledgeCenterImage"] = mxGetFileName("knowledgeCenterImage");

    if (!isset($_POST["isFeatured"]))
        $_POST["isFeatured"] = 0;

    if (!isset($_POST["isPopular"]))
        $_POST["isPopular"] = 0;

    if (!isset($_POST["isPinned"]))
        $_POST["isPinned"] = 0;

    $DB->table = $DB->pre . "knowledge_center";
    $DB->data = $_POST;
    if ($DB->dbUpdate("knowledgeCenterID=?", "i", array($knowledgeCenterID))) {
        setResponse(array("err" => 0, "param" => "id=$knowledgeCenterID"));
    } else {
        setResponse(1);
    }
}
if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
                addKnowledgeCenter();
                break;
            case "UPDATE":
                updateKnowledgeCenter();
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    setModVars(array("TBL" => "knowledge_center", "PK" => "knowledgeCenterID"));
}
