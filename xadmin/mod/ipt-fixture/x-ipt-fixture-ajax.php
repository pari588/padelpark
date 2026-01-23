<?php
/**
 * IPT Fixture AJAX handler
 * Returns categories and participants for dropdown population
 */
ob_start();
require_once("../../../core/core.inc.php");
require_once("../../inc/site.inc.php");
ob_end_clean();

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = array('success' => false, 'error' => 'No action specified');

switch ($action) {
    case 'getCategories':
        $tournamentID = intval($_POST['tournamentID'] ?? $_GET['tournamentID'] ?? 0);
        if ($tournamentID) {
            $DB->vals = array($tournamentID);
            $DB->types = "i";
            $DB->sql = "SELECT tc.tcID, c.categoryName, c.categoryCode
                        FROM " . $DB->pre . "ipt_tournament_category tc
                        JOIN " . $DB->pre . "ipt_category c ON tc.categoryID=c.categoryID
                        WHERE tc.tournamentID=? AND tc.status=1
                        ORDER BY c.sortOrder ASC, c.categoryName ASC";
            $DB->dbRows();
            $cats = is_array($DB->rows) ? $DB->rows : array();
            $response = array('success' => true, 'categories' => $cats);
        } else {
            $response = array('success' => false, 'error' => 'No tournamentID');
        }
        break;

    case 'getParticipants':
        $tournamentID = intval($_POST['tournamentID'] ?? $_GET['tournamentID'] ?? 0);
        $tcID = intval($_POST['tcID'] ?? $_GET['tcID'] ?? 0);
        if ($tournamentID && $tcID) {
            // First try with all common statuses
            $DB->vals = array($tournamentID, $tcID);
            $DB->types = "ii";
            $DB->sql = "SELECT participantID, teamName, player1Name, player2Name, seedNumber, participantStatus
                        FROM " . $DB->pre . "ipt_participant
                        WHERE tournamentID=? AND tcID=? AND status=1
                        ORDER BY CASE WHEN seedNumber > 0 THEN 0 ELSE 1 END, seedNumber ASC, player1Name ASC";
            $DB->dbRows();
            $participants = is_array($DB->rows) ? $DB->rows : array();

            // Format team names
            foreach ($participants as &$p) {
                if (empty($p['teamName'])) {
                    $p['teamName'] = $p['player1Name'];
                    if (!empty($p['player2Name'])) {
                        $p['teamName'] .= ' / ' . $p['player2Name'];
                    }
                }
                if ($p['seedNumber'] > 0) {
                    $p['teamName'] = '[' . $p['seedNumber'] . '] ' . $p['teamName'];
                }
            }

            $response = array('success' => true, 'participants' => $participants, 'count' => count($participants), 'tournamentID' => $tournamentID, 'tcID' => $tcID);
        } else {
            $response = array('success' => false, 'error' => 'Missing tournamentID or tcID', 'tournamentID' => $tournamentID, 'tcID' => $tcID);
        }
        break;

    case 'debug':
        // Debug endpoint to check database
        $tournamentID = intval($_POST['tournamentID'] ?? $_GET['tournamentID'] ?? 0);
        $tcID = intval($_POST['tcID'] ?? $_GET['tcID'] ?? 0);

        $DB->sql = "SELECT COUNT(*) as cnt FROM " . $DB->pre . "ipt_participant WHERE status=1";
        $total = $DB->dbRow();

        $response = array(
            'success' => true,
            'totalParticipants' => $total['cnt'],
            'dbPrefix' => $DB->pre,
            'tournamentID' => $tournamentID,
            'tcID' => $tcID
        );
        break;
}

echo json_encode($response);
exit;
