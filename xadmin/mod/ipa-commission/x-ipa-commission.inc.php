<?php
/*
IPA Commission Module
Track retail commission for coaches
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
        $saleDate = $_POST["saleDate"] ?? date("Y-m-d");
        $saleAmount = floatval($_POST["saleAmount"] ?? 0);
        $commissionRate = floatval($_POST["commissionRate"] ?? 5);
        $notes = trim($_POST["notes"] ?? "");

        if ($coachID < 1) {
            header('Content-Type: application/json');
            echo json_encode(array("err" => 1, "msg" => "Please select a coach"));
            exit;
        }

        if ($saleAmount <= 0) {
            header('Content-Type: application/json');
            echo json_encode(array("err" => 1, "msg" => "Please enter a valid sale amount"));
            exit;
        }

        $commissionAmount = round($saleAmount * $commissionRate / 100, 2);

        $DB->vals = array($coachID, $saleDate, $saleAmount, $commissionRate, $commissionAmount, $notes);
        $DB->types = "isddds";
        $DB->sql = "INSERT INTO " . $DB->pre . "ipa_coach_commission
                    (coachID, saleDate, saleAmount, commissionRate, commissionAmount, notes)
                    VALUES (?, ?, ?, ?, ?, ?)";
        $DB->dbQuery();
        $commissionID = $DB->insertID;

        // Update coach's total commission
        updateCoachTotalCommission($coachID);

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "msg" => "Commission record added", "id" => $commissionID));
        exit;
    }

    if ($xAction == "UPDATE") {
        $commissionID = intval($_POST["commissionID"] ?? 0);
        $saleDate = $_POST["saleDate"] ?? date("Y-m-d");
        $saleAmount = floatval($_POST["saleAmount"] ?? 0);
        $commissionRate = floatval($_POST["commissionRate"] ?? 5);
        $notes = trim($_POST["notes"] ?? "");

        $commissionAmount = round($saleAmount * $commissionRate / 100, 2);

        // Get coach ID before update
        $DB->vals = array($commissionID);
        $DB->types = "i";
        $DB->sql = "SELECT coachID FROM " . $DB->pre . "ipa_coach_commission WHERE commissionID=?";
        $DB->dbRows();
        $coachID = !empty($DB->rows) ? $DB->rows[0]["coachID"] : 0;

        $DB->vals = array($saleDate, $saleAmount, $commissionRate, $commissionAmount, $notes, $commissionID);
        $DB->types = "sddds" . "i";
        $DB->sql = "UPDATE " . $DB->pre . "ipa_coach_commission
                    SET saleDate=?, saleAmount=?, commissionRate=?, commissionAmount=?, notes=?
                    WHERE commissionID=?";
        $DB->dbQuery();

        if ($coachID > 0) {
            updateCoachTotalCommission($coachID);
        }

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "msg" => "Commission updated"));
        exit;
    }

    if ($xAction == "APPROVE") {
        $commissionID = intval($_POST["commissionID"] ?? 0);

        $DB->vals = array("Approved", $commissionID, "Pending");
        $DB->types = "sis";
        $DB->sql = "UPDATE " . $DB->pre . "ipa_coach_commission SET commissionStatus=? WHERE commissionID=? AND commissionStatus=?";
        $DB->dbQuery();

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "msg" => "Commission approved"));
        exit;
    }

    if ($xAction == "MARK_PAID") {
        $commissionID = intval($_POST["commissionID"] ?? 0);
        $paymentDate = $_POST["paymentDate"] ?? date("Y-m-d");
        $paymentReference = trim($_POST["paymentReference"] ?? "");

        // Get coach ID
        $DB->vals = array($commissionID);
        $DB->types = "i";
        $DB->sql = "SELECT coachID FROM " . $DB->pre . "ipa_coach_commission WHERE commissionID=?";
        $DB->dbRows();
        $coachID = !empty($DB->rows) ? $DB->rows[0]["coachID"] : 0;

        $DB->vals = array("Paid", $paymentDate, $paymentReference, $commissionID, "Approved");
        $DB->types = "sssiss";
        $DB->sql = "UPDATE " . $DB->pre . "ipa_coach_commission SET commissionStatus=?, paymentDate=?, paymentReference=? WHERE commissionID=? AND commissionStatus=?";
        $DB->dbQuery();

        if ($coachID > 0) {
            updateCoachTotalCommission($coachID);
        }

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "msg" => "Commission marked as paid"));
        exit;
    }

    if ($xAction == "DELETE") {
        $commissionID = intval($_POST["commissionID"] ?? 0);

        // Get coach ID before delete
        $DB->vals = array($commissionID);
        $DB->types = "i";
        $DB->sql = "SELECT coachID FROM " . $DB->pre . "ipa_coach_commission WHERE commissionID=?";
        $DB->dbRows();
        $coachID = !empty($DB->rows) ? $DB->rows[0]["coachID"] : 0;

        $DB->vals = array(0, $commissionID);
        $DB->types = "ii";
        $DB->sql = "UPDATE " . $DB->pre . "ipa_coach_commission SET status=? WHERE commissionID=?";
        $DB->dbQuery();

        if ($coachID > 0) {
            updateCoachTotalCommission($coachID);
        }

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "msg" => "Commission deleted"));
        exit;
    }

    if ($xAction == "GET_SUMMARY") {
        $coachID = intval($_POST["coachID"] ?? 0);
        $startDate = $_POST["startDate"] ?? date("Y-m-01");
        $endDate = $_POST["endDate"] ?? date("Y-m-t");

        $whereCoach = $coachID > 0 ? " AND c.coachID=?" : "";
        $vals = $coachID > 0 ? array($startDate, $endDate, $coachID) : array($startDate, $endDate);
        $types = $coachID > 0 ? "ssi" : "ss";

        $DB->vals = $vals;
        $DB->types = $types;
        $DB->sql = "SELECT c.coachID, CONCAT(co.firstName, ' ', IFNULL(co.lastName,'')) as coachName,
                           SUM(c.saleAmount) as totalSales,
                           SUM(c.commissionAmount) as totalCommission,
                           COUNT(*) as recordCount,
                           SUM(CASE WHEN c.commissionStatus='Paid' THEN c.commissionAmount ELSE 0 END) as paidAmount,
                           SUM(CASE WHEN c.commissionStatus IN ('Pending','Approved') THEN c.commissionAmount ELSE 0 END) as pendingAmount
                    FROM " . $DB->pre . "ipa_coach_commission c
                    LEFT JOIN " . $DB->pre . "ipa_coach co ON c.coachID = co.coachID
                    WHERE c.status=1 AND c.saleDate BETWEEN ? AND ?" . $whereCoach . "
                    GROUP BY c.coachID
                    ORDER BY totalCommission DESC";
        $DB->dbRows();

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "summary" => $DB->rows ?: array()));
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode(array("err" => 1, "msg" => "Invalid action"));
    exit;
} else {
    if (function_exists("setModVars")) {
        setModVars(array("TBL" => "ipa_coach_commission", "PK" => "commissionID"));
    }
}

function updateCoachTotalCommission($coachID) {
    global $DB;

    $DB->vals = array($coachID);
    $DB->types = "i";
    $DB->sql = "SELECT SUM(commissionAmount) as total FROM " . $DB->pre . "ipa_coach_commission WHERE coachID=? AND status=1 AND commissionStatus='Paid'";
    $DB->dbRows();
    $total = !empty($DB->rows) ? floatval($DB->rows[0]["total"]) : 0;

    $DB->vals = array($total, $coachID);
    $DB->types = "di";
    $DB->sql = "UPDATE " . $DB->pre . "ipa_coach SET totalCommissionEarned=? WHERE coachID=?";
    $DB->dbQuery();
}
