<?php
/*
IPA Coach Assessment Module
Coaches assess player skills and progress
*/

if (isset($_POST["xAction"])) {
    ob_start();
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    ob_end_clean();

    mxCheckRequest(true, true);

    $xAction = $_POST["xAction"];

    if ($xAction == "ADD") {
        $sessionID = intval($_POST["sessionID"] ?? 0);
        $playerID = intval($_POST["playerID"] ?? 0);
        $coachID = intval($_POST["coachID"] ?? 0);
        $programID = intval($_POST["programID"] ?? 0);
        $assessmentType = $_POST["assessmentType"] ?? "Session";
        $assessmentDate = $_POST["assessmentDate"] ?? date("Y-m-d");

        // Skill ratings
        $technicalSkills = floatval($_POST["technicalSkills"] ?? 0);
        $tacticalAwareness = floatval($_POST["tacticalAwareness"] ?? 0);
        $physicalFitness = floatval($_POST["physicalFitness"] ?? 0);
        $mentalStrength = floatval($_POST["mentalStrength"] ?? 0);
        $gameStrategy = floatval($_POST["gameStrategy"] ?? 0);
        $consistency = floatval($_POST["consistency"] ?? 0);

        // Calculate overall
        $overallScore = round(($technicalSkills + $tacticalAwareness + $physicalFitness + $mentalStrength + $gameStrategy + $consistency) / 6, 2);

        $currentLevel = $_POST["currentLevel"] ?? "Beginner";
        $recommendedLevel = $_POST["recommendedLevel"] ?? "Beginner";
        $levelChangeRecommended = ($currentLevel != $recommendedLevel) ? 1 : 0;

        $strengths = trim($_POST["strengths"] ?? "");
        $areasForImprovement = trim($_POST["areasForImprovement"] ?? "");
        $trainingRecommendations = trim($_POST["trainingRecommendations"] ?? "");
        $coachNotes = trim($_POST["coachNotes"] ?? "");

        $DB->vals = array($sessionID > 0 ? $sessionID : null, $playerID, $coachID, $programID > 0 ? $programID : null, $assessmentType, $assessmentDate, $technicalSkills, $tacticalAwareness, $physicalFitness, $mentalStrength, $gameStrategy, $consistency, $overallScore, $currentLevel, $recommendedLevel, $levelChangeRecommended, $strengths, $areasForImprovement, $trainingRecommendations, $coachNotes);
        $DB->types = "iiiissdddddddsssiss" . "s";
        $DB->sql = "INSERT INTO " . $DB->pre . "ipa_coach_assessment
                    (sessionID, playerID, coachID, programID, assessmentType, assessmentDate, technicalSkills, tacticalAwareness, physicalFitness, mentalStrength, gameStrategy, consistency, overallScore, currentLevel, recommendedLevel, levelChangeRecommended, strengths, areasForImprovement, trainingRecommendations, coachNotes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $DB->dbQuery();
        $assessmentID = $DB->insertID;

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "msg" => "Assessment saved successfully", "id" => $assessmentID));
        exit;
    }

    if ($xAction == "UPDATE") {
        $assessmentID = intval($_POST["assessmentID"] ?? 0);

        // Skill ratings
        $technicalSkills = floatval($_POST["technicalSkills"] ?? 0);
        $tacticalAwareness = floatval($_POST["tacticalAwareness"] ?? 0);
        $physicalFitness = floatval($_POST["physicalFitness"] ?? 0);
        $mentalStrength = floatval($_POST["mentalStrength"] ?? 0);
        $gameStrategy = floatval($_POST["gameStrategy"] ?? 0);
        $consistency = floatval($_POST["consistency"] ?? 0);

        $overallScore = round(($technicalSkills + $tacticalAwareness + $physicalFitness + $mentalStrength + $gameStrategy + $consistency) / 6, 2);

        $currentLevel = $_POST["currentLevel"] ?? "Beginner";
        $recommendedLevel = $_POST["recommendedLevel"] ?? "Beginner";
        $levelChangeRecommended = ($currentLevel != $recommendedLevel) ? 1 : 0;

        $strengths = trim($_POST["strengths"] ?? "");
        $areasForImprovement = trim($_POST["areasForImprovement"] ?? "");
        $trainingRecommendations = trim($_POST["trainingRecommendations"] ?? "");
        $coachNotes = trim($_POST["coachNotes"] ?? "");

        $DB->vals = array($technicalSkills, $tacticalAwareness, $physicalFitness, $mentalStrength, $gameStrategy, $consistency, $overallScore, $currentLevel, $recommendedLevel, $levelChangeRecommended, $strengths, $areasForImprovement, $trainingRecommendations, $coachNotes, $assessmentID);
        $DB->types = "dddddddssiss" . "ssi";
        $DB->sql = "UPDATE " . $DB->pre . "ipa_coach_assessment
                    SET technicalSkills=?, tacticalAwareness=?, physicalFitness=?, mentalStrength=?, gameStrategy=?, consistency=?, overallScore=?, currentLevel=?, recommendedLevel=?, levelChangeRecommended=?, strengths=?, areasForImprovement=?, trainingRecommendations=?, coachNotes=?
                    WHERE assessmentID=?";
        $DB->dbQuery();

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "msg" => "Assessment updated successfully"));
        exit;
    }

    if ($xAction == "DELETE") {
        $assessmentID = intval($_POST["assessmentID"] ?? 0);

        $DB->vals = array(0, $assessmentID);
        $DB->types = "ii";
        $DB->sql = "UPDATE " . $DB->pre . "ipa_coach_assessment SET status=? WHERE assessmentID=?";
        $DB->dbQuery();

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "msg" => "Assessment deleted successfully"));
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode(array("err" => 1, "msg" => "Invalid action"));
    exit;
} else {
    if (function_exists("setModVars")) {
        setModVars(array("TBL" => "ipa_coach_assessment", "PK" => "assessmentID"));
    }
}
