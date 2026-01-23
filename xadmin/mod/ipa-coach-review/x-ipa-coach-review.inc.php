<?php
/*
IPA Coach Review Module
Head coach reviews other coaches' performance
*/

if (isset($_POST["xAction"])) {
    ob_start();
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    ob_end_clean();

    mxCheckRequest(true, true);

    $xAction = $_POST["xAction"];

    if ($xAction == "ADD") {
        $coachID = intval($_POST["coachID"] ?? 0);
        $reviewerID = intval($_POST["reviewerID"] ?? 0);
        $reviewPeriod = $_POST["reviewPeriod"] ?? "Monthly";
        $periodStartDate = $_POST["periodStartDate"] ?? date("Y-m-01");
        $periodEndDate = $_POST["periodEndDate"] ?? date("Y-m-t");
        $reviewDate = $_POST["reviewDate"] ?? date("Y-m-d");

        // Performance metrics
        $sessionQuality = floatval($_POST["sessionQuality"] ?? 0);
        $studentEngagement = floatval($_POST["studentEngagement"] ?? 0);
        $punctuality = floatval($_POST["punctuality"] ?? 0);
        $professionalism = floatval($_POST["professionalism"] ?? 0);
        $technicalKnowledge = floatval($_POST["technicalKnowledge"] ?? 0);
        $communicationSkills = floatval($_POST["communicationSkills"] ?? 0);
        $studentProgress = floatval($_POST["studentProgress"] ?? 0);
        $teamwork = floatval($_POST["teamwork"] ?? 0);

        // Calculate overall
        $overallRating = round(($sessionQuality + $studentEngagement + $punctuality + $professionalism + $technicalKnowledge + $communicationSkills + $studentProgress + $teamwork) / 8, 2);

        // Determine category
        if ($overallRating >= 4.5) $performanceCategory = "Excellent";
        elseif ($overallRating >= 3.5) $performanceCategory = "Good";
        elseif ($overallRating >= 2.5) $performanceCategory = "Satisfactory";
        elseif ($overallRating >= 1.5) $performanceCategory = "Needs Improvement";
        else $performanceCategory = "Unsatisfactory";

        $strengths = trim($_POST["strengths"] ?? "");
        $areasForDevelopment = trim($_POST["areasForDevelopment"] ?? "");
        $actionPlan = trim($_POST["actionPlan"] ?? "");
        $headCoachComments = trim($_POST["headCoachComments"] ?? "");

        $DB->vals = array($coachID, $reviewerID, $reviewPeriod, $periodStartDate, $periodEndDate, $reviewDate, $sessionQuality, $studentEngagement, $punctuality, $professionalism, $technicalKnowledge, $communicationSkills, $studentProgress, $teamwork, $overallRating, $performanceCategory, $strengths, $areasForDevelopment, $actionPlan, $headCoachComments);
        $DB->types = "iissssddddddddds" . "ssss";
        $DB->sql = "INSERT INTO " . $DB->pre . "ipa_coach_review
                    (coachID, reviewerID, reviewPeriod, periodStartDate, periodEndDate, reviewDate, sessionQuality, studentEngagement, punctuality, professionalism, technicalKnowledge, communicationSkills, studentProgress, teamwork, overallRating, performanceCategory, strengths, areasForDevelopment, actionPlan, headCoachComments)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $DB->dbQuery();
        $reviewID = $DB->insertID;

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "msg" => "Review saved successfully", "id" => $reviewID));
        exit;
    }

    if ($xAction == "UPDATE") {
        $reviewID = intval($_POST["reviewID"] ?? 0);

        // Performance metrics
        $sessionQuality = floatval($_POST["sessionQuality"] ?? 0);
        $studentEngagement = floatval($_POST["studentEngagement"] ?? 0);
        $punctuality = floatval($_POST["punctuality"] ?? 0);
        $professionalism = floatval($_POST["professionalism"] ?? 0);
        $technicalKnowledge = floatval($_POST["technicalKnowledge"] ?? 0);
        $communicationSkills = floatval($_POST["communicationSkills"] ?? 0);
        $studentProgress = floatval($_POST["studentProgress"] ?? 0);
        $teamwork = floatval($_POST["teamwork"] ?? 0);

        $overallRating = round(($sessionQuality + $studentEngagement + $punctuality + $professionalism + $technicalKnowledge + $communicationSkills + $studentProgress + $teamwork) / 8, 2);

        if ($overallRating >= 4.5) $performanceCategory = "Excellent";
        elseif ($overallRating >= 3.5) $performanceCategory = "Good";
        elseif ($overallRating >= 2.5) $performanceCategory = "Satisfactory";
        elseif ($overallRating >= 1.5) $performanceCategory = "Needs Improvement";
        else $performanceCategory = "Unsatisfactory";

        $strengths = trim($_POST["strengths"] ?? "");
        $areasForDevelopment = trim($_POST["areasForDevelopment"] ?? "");
        $actionPlan = trim($_POST["actionPlan"] ?? "");
        $headCoachComments = trim($_POST["headCoachComments"] ?? "");
        $reviewStatus = $_POST["reviewStatus"] ?? "Draft";

        $DB->vals = array($sessionQuality, $studentEngagement, $punctuality, $professionalism, $technicalKnowledge, $communicationSkills, $studentProgress, $teamwork, $overallRating, $performanceCategory, $strengths, $areasForDevelopment, $actionPlan, $headCoachComments, $reviewStatus, $reviewID);
        $DB->types = "dddddddddsssss" . "si";
        $DB->sql = "UPDATE " . $DB->pre . "ipa_coach_review
                    SET sessionQuality=?, studentEngagement=?, punctuality=?, professionalism=?, technicalKnowledge=?, communicationSkills=?, studentProgress=?, teamwork=?, overallRating=?, performanceCategory=?, strengths=?, areasForDevelopment=?, actionPlan=?, headCoachComments=?, reviewStatus=?
                    WHERE reviewID=?";
        $DB->dbQuery();

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "msg" => "Review updated successfully"));
        exit;
    }

    if ($xAction == "DELETE") {
        $reviewID = intval($_POST["reviewID"] ?? 0);

        $DB->vals = array(0, $reviewID);
        $DB->types = "ii";
        $DB->sql = "UPDATE " . $DB->pre . "ipa_coach_review SET status=? WHERE reviewID=?";
        $DB->dbQuery();

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "msg" => "Review deleted successfully"));
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode(array("err" => 1, "msg" => "Invalid action"));
    exit;
} else {
    if (function_exists("setModVars")) {
        setModVars(array("TBL" => "ipa_coach_review", "PK" => "reviewID"));
    }
}
