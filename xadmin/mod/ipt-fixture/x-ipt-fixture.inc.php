<?php
/*
addFixture = To save Fixture data.
updateFixture = To update Fixture data.
updateMatchResult = Update match scores and winner.
*/

function addFixture()
{
    global $DB;

    $DB->table = $DB->pre . "ipt_fixture";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        setResponse(array("err" => 0, "param" => "id=" . $DB->insertID));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateFixture()
{
    global $DB;
    $fixtureID = intval($_POST["fixtureID"]);

    $DB->table = $DB->pre . "ipt_fixture";
    $DB->data = $_POST;
    if ($DB->dbUpdate("fixtureID=?", "i", array($fixtureID))) {
        setResponse(array("err" => 0, "param" => "id=" . $fixtureID));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateMatchResult()
{
    global $DB;
    $fixtureID = intval($_POST["fixtureID"]);

    // Calculate winner based on sets won
    $team1Sets = 0;
    $team2Sets = 0;

    $team1Set1 = intval($_POST["team1Set1"] ?? 0);
    $team2Set1 = intval($_POST["team2Set1"] ?? 0);
    if ($team1Set1 > $team2Set1) $team1Sets++;
    else if ($team2Set1 > $team1Set1) $team2Sets++;

    $team1Set2 = intval($_POST["team1Set2"] ?? 0);
    $team2Set2 = intval($_POST["team2Set2"] ?? 0);
    if ($team1Set2 > $team2Set2) $team1Sets++;
    else if ($team2Set2 > $team1Set2) $team2Sets++;

    $team1Set3 = intval($_POST["team1Set3"] ?? 0);
    $team2Set3 = intval($_POST["team2Set3"] ?? 0);
    if ($team1Set3 > $team2Set3) $team1Sets++;
    else if ($team2Set3 > $team1Set3) $team2Sets++;

    // Determine winner
    $winnerID = null;
    $winnerName = "";
    if ($team1Sets >= 2) {
        $winnerID = intval($_POST["team1ID"]);
        $winnerName = $_POST["team1Name"];
    } else if ($team2Sets >= 2) {
        $winnerID = intval($_POST["team2ID"]);
        $winnerName = $_POST["team2Name"];
    }

    $DB->vals = array(
        $team1Set1, $team1Set2, $team1Set3,
        $team2Set1, $team2Set2, $team2Set3,
        $winnerID, $winnerName,
        "Completed",
        $fixtureID
    );
    $DB->types = "iiiiiiissi";
    $DB->sql = "UPDATE " . $DB->pre . "ipt_fixture SET
                team1Set1=?, team1Set2=?, team1Set3=?,
                team2Set1=?, team2Set2=?, team2Set3=?,
                winnerID=?, winnerName=?,
                matchStatus=?
                WHERE fixtureID=?";

    if ($DB->dbQuery()) {
        // Update participant statuses
        if ($winnerID) {
            // Winner continues (mark as Active)
            $DB->vals = array("Active", $winnerID);
            $DB->types = "si";
            $DB->sql = "UPDATE " . $DB->pre . "ipt_participant SET participantStatus=? WHERE participantID=?";
            $DB->dbQuery();

            // Loser is eliminated
            $loserID = ($winnerID == intval($_POST["team1ID"])) ? intval($_POST["team2ID"]) : intval($_POST["team1ID"]);
            if ($loserID) {
                $DB->vals = array("Eliminated", $loserID);
                $DB->types = "si";
                $DB->sql = "UPDATE " . $DB->pre . "ipt_participant SET participantStatus=? WHERE participantID=?";
                $DB->dbQuery();
            }

            // Advance winner to next round
            advanceWinner($fixtureID, $winnerID, $winnerName);
        }

        setResponse(array("err" => 0, "msg" => "Match result updated"));
    } else {
        setResponse(array("err" => 1));
    }
}

function advanceWinner($fixtureID, $winnerID, $winnerName)
{
    global $DB;

    // Get current fixture details
    $DB->vals = array($fixtureID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "ipt_fixture WHERE fixtureID=?";
    $fixture = $DB->dbRow();

    if ($fixture["roundNumber"] <= 1) {
        return; // Already at final
    }

    $nextRound = $fixture["roundNumber"] - 1;
    $nextBracketPos = ceil($fixture["bracketPosition"] / 2);
    $isTeam1 = ($fixture["bracketPosition"] % 2 == 1);

    // Check if next round match exists
    $DB->vals = array($fixture["tournamentID"], $fixture["categoryID"], $nextRound, $nextBracketPos);
    $DB->types = "iiii";
    $DB->sql = "SELECT fixtureID FROM " . $DB->pre . "ipt_fixture
                WHERE tournamentID=? AND categoryID=? AND roundNumber=? AND bracketPosition=?";
    $nextMatch = $DB->dbRow();

    if ($nextMatch) {
        // Update existing match
        if ($isTeam1) {
            $DB->vals = array($winnerID, $winnerName, $nextMatch["fixtureID"]);
            $DB->types = "isi";
            $DB->sql = "UPDATE " . $DB->pre . "ipt_fixture SET team1ID=?, team1Name=? WHERE fixtureID=?";
        } else {
            $DB->vals = array($winnerID, $winnerName, $nextMatch["fixtureID"]);
            $DB->types = "isi";
            $DB->sql = "UPDATE " . $DB->pre . "ipt_fixture SET team2ID=?, team2Name=? WHERE fixtureID=?";
        }
        $DB->dbQuery();
    } else {
        // Create next round match
        $roundNames = array(1 => "Final", 2 => "Semi-Final", 3 => "Quarter-Final", 4 => "Round of 16", 5 => "Round of 32");

        $data = array(
            "tournamentID" => $fixture["tournamentID"],
            "categoryID" => $fixture["categoryID"],
            "roundName" => $roundNames[$nextRound] ?? "Round " . $nextRound,
            "roundNumber" => $nextRound,
            "bracketPosition" => $nextBracketPos,
            "matchDate" => $fixture["matchDate"],
            "matchStatus" => "Scheduled"
        );

        if ($isTeam1) {
            $data["team1ID"] = $winnerID;
            $data["team1Name"] = $winnerName;
        } else {
            $data["team2ID"] = $winnerID;
            $data["team2Name"] = $winnerName;
        }

        $DB->table = $DB->pre . "ipt_fixture";
        $DB->data = $data;
        $DB->dbInsert();
    }
}

function generateDraw()
{
    global $DB;
    $tournamentID = intval($_POST["tournamentID"]);
    $categoryID = intval($_POST["categoryID"]);

    // Get category details
    $DB->vals = array($categoryID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "ipt_category WHERE categoryID=?";
    $category = $DB->dbRow();

    if (!$category) {
        setResponse(array("err" => 1, "msg" => "Category not found"));
        return;
    }

    // Get confirmed participants ordered by ranking
    $DB->vals = array($tournamentID, $categoryID, 1);
    $DB->types = "iii";
    $DB->sql = "SELECT participantID, player1Name, player2Name, teamName, seedNumber
                FROM " . $DB->pre . "ipt_participant
                WHERE tournamentID=? AND categoryID=? AND status=?
                AND participantStatus IN ('Confirmed', 'Checked-In', 'Registered')
                ORDER BY CASE WHEN seedNumber > 0 THEN 0 ELSE 1 END, seedNumber ASC, participantID ASC";
    $DB->dbRows();
    $participants = $DB->rows ?: array();

    $numPlayers = count($participants);

    if ($numPlayers < 2) {
        setResponse(array("err" => 1, "msg" => "Need at least 2 confirmed participants"));
        return;
    }

    // Calculate draw size (power of 2)
    $drawSize = 4;
    if ($numPlayers > 4) $drawSize = 8;
    if ($numPlayers > 8) $drawSize = 16;
    if ($numPlayers > 16) $drawSize = 32;
    if ($numPlayers > 32) $drawSize = 64;

    // Calculate number of rounds
    $numRounds = intval(log($drawSize, 2));
    $numByes = $drawSize - $numPlayers;

    // Round names
    $roundNames = array(
        1 => "Final",
        2 => "Semi-Final",
        3 => "Quarter-Final",
        4 => "Round of 16",
        5 => "Round of 32",
        6 => "Round of 64"
    );

    // Clear existing fixtures for this category first
    $DB->vals = array($tournamentID, $categoryID);
    $DB->types = "ii";
    $DB->sql = "DELETE FROM " . $DB->pre . "ipt_fixture WHERE tournamentID=? AND categoryID=?";
    $DB->dbQuery();

    // Helper function to get team name
    $getTeamName = function($player) {
        if (!$player) return "";
        if (!empty($player["teamName"])) return $player["teamName"];
        $name = $player["player1Name"];
        if (!empty($player["player2Name"])) $name .= " / " . $player["player2Name"];
        return $name;
    };

    // Standard bracket seeding - top seeds get BYEs
    // For 5 players in 8 draw: seeds 1,2,3 get BYEs, seeds 4,5,6,7,8 play first round
    // But we only have 5 players, so positions 6,7,8 are empty (BYEs for their opponents)

    $matchNo = 1;
    $firstRoundNum = $numRounds;

    // Create bracket structure
    // In a proper bracket, BYEs go to top seeds
    // Seed 1 vs Seed 8, Seed 4 vs Seed 5, Seed 3 vs Seed 6, Seed 2 vs Seed 7 (for 8 draw)

    $bracketSeeds = generateBracketSeeds($drawSize);

    // Place participants in slots
    $slots = array_fill(1, $drawSize, null);
    for ($i = 0; $i < $numPlayers; $i++) {
        $bracketPos = $bracketSeeds[$i];
        $slots[$bracketPos] = $participants[$i];
    }

    // First, create all matches for each round (working backwards from final)
    $allFixtures = array();

    // Build the bracket structure
    for ($round = 1; $round <= $numRounds; $round++) {
        $matchesInRound = pow(2, $round - 1);
        for ($pos = 1; $pos <= $matchesInRound; $pos++) {
            $allFixtures[$round][$pos] = array(
                "team1ID" => null, "team1Name" => "", "team1Seed" => null,
                "team2ID" => null, "team2Name" => "", "team2Seed" => null,
                "matchStatus" => "Scheduled", "winnerID" => null, "winnerName" => ""
            );
        }
    }

    // Fill in first round from slots
    $matchesInFirstRound = $drawSize / 2;
    for ($pos = 1; $pos <= $matchesInFirstRound; $pos++) {
        $slot1 = ($pos - 1) * 2 + 1;
        $slot2 = $slot1 + 1;
        $player1 = $slots[$slot1];
        $player2 = $slots[$slot2];

        $allFixtures[$firstRoundNum][$pos]["team1ID"] = $player1 ? $player1["participantID"] : null;
        $allFixtures[$firstRoundNum][$pos]["team1Name"] = $getTeamName($player1);
        $allFixtures[$firstRoundNum][$pos]["team1Seed"] = $player1 ? ($player1["seedNumber"] ?: null) : null;
        $allFixtures[$firstRoundNum][$pos]["team2ID"] = $player2 ? $player2["participantID"] : null;
        $allFixtures[$firstRoundNum][$pos]["team2Name"] = $getTeamName($player2);
        $allFixtures[$firstRoundNum][$pos]["team2Seed"] = $player2 ? ($player2["seedNumber"] ?: null) : null;

        // Handle BYEs - player advances directly, don't create a match for BYE
        if ($player1 && !$player2) {
            // Player 1 gets a BYE - advance to next round
            $nextRound = $firstRoundNum - 1;
            $nextPos = ceil($pos / 2);
            $isTeam1 = ($pos % 2 == 1);
            if ($nextRound >= 1) {
                if ($isTeam1) {
                    $allFixtures[$nextRound][$nextPos]["team1ID"] = $player1["participantID"];
                    $allFixtures[$nextRound][$nextPos]["team1Name"] = $getTeamName($player1);
                    $allFixtures[$nextRound][$nextPos]["team1Seed"] = $player1["seedNumber"] ?: null;
                } else {
                    $allFixtures[$nextRound][$nextPos]["team2ID"] = $player1["participantID"];
                    $allFixtures[$nextRound][$nextPos]["team2Name"] = $getTeamName($player1);
                    $allFixtures[$nextRound][$nextPos]["team2Seed"] = $player1["seedNumber"] ?: null;
                }
            }
            // Mark this match as a BYE (won't be saved as fixture)
            $allFixtures[$firstRoundNum][$pos]["isBye"] = true;
        } else if (!$player1 && $player2) {
            // Player 2 gets a BYE - advance to next round
            $nextRound = $firstRoundNum - 1;
            $nextPos = ceil($pos / 2);
            $isTeam1 = ($pos % 2 == 1);
            if ($nextRound >= 1) {
                if ($isTeam1) {
                    $allFixtures[$nextRound][$nextPos]["team1ID"] = $player2["participantID"];
                    $allFixtures[$nextRound][$nextPos]["team1Name"] = $getTeamName($player2);
                    $allFixtures[$nextRound][$nextPos]["team1Seed"] = $player2["seedNumber"] ?: null;
                } else {
                    $allFixtures[$nextRound][$nextPos]["team2ID"] = $player2["participantID"];
                    $allFixtures[$nextRound][$nextPos]["team2Name"] = $getTeamName($player2);
                    $allFixtures[$nextRound][$nextPos]["team2Seed"] = $player2["seedNumber"] ?: null;
                }
            }
            $allFixtures[$firstRoundNum][$pos]["isBye"] = true;
        } else if (!$player1 && !$player2) {
            // Both empty - skip
            $allFixtures[$firstRoundNum][$pos]["isEmpty"] = true;
        }
    }

    // Save fixtures to database (skip BYEs and empty matches)
    for ($round = $numRounds; $round >= 1; $round--) {
        $matchesInRound = pow(2, $round - 1);
        for ($pos = 1; $pos <= $matchesInRound; $pos++) {
            $fix = $allFixtures[$round][$pos];

            // Skip BYE matches and empty matches
            if (!empty($fix["isBye"]) || !empty($fix["isEmpty"])) {
                continue;
            }

            // Skip if both teams are empty (waiting for previous round)
            // But still create the fixture for future rounds

            $data = array(
                "tournamentID" => $tournamentID,
                "categoryID" => $categoryID,
                "matchNo" => $matchNo,
                "roundName" => $roundNames[$round] ?? "Round " . $round,
                "roundNumber" => $round,
                "bracketPosition" => $pos,
                "team1ID" => $fix["team1ID"],
                "team1Name" => $fix["team1Name"],
                "team1Seed" => $fix["team1Seed"],
                "team2ID" => $fix["team2ID"],
                "team2Name" => $fix["team2Name"],
                "team2Seed" => $fix["team2Seed"],
                "matchStatus" => "Scheduled",
                "status" => 1
            );

            $DB->table = $DB->pre . "ipt_fixture";
            $DB->data = $data;
            $DB->dbInsert();
            $matchNo++;
        }
    }

    $actualMatches = $matchNo - 1;
    setResponse(array("err" => 0, "msg" => "Draw generated: " . $numPlayers . " players, " . $actualMatches . " matches (" . $numByes . " byes)"));
}

function generateBracketSeeds($size)
{
    // Standard seeding positions for tournament brackets
    // Ensures top seeds are distributed across the bracket
    if ($size == 4) {
        return array(1, 4, 2, 3);
    } else if ($size == 8) {
        return array(1, 8, 4, 5, 2, 7, 3, 6);
    } else if ($size == 16) {
        return array(1, 16, 8, 9, 4, 13, 5, 12, 2, 15, 7, 10, 3, 14, 6, 11);
    } else if ($size == 32) {
        return array(1, 32, 16, 17, 8, 25, 9, 24, 4, 29, 13, 20, 5, 28, 12, 21,
                     2, 31, 15, 18, 7, 26, 10, 23, 3, 30, 14, 19, 6, 27, 11, 22);
    } else if ($size == 64) {
        return array(1, 64, 32, 33, 16, 49, 17, 48, 8, 57, 25, 40, 9, 56, 24, 41,
                     4, 61, 29, 36, 13, 52, 20, 45, 5, 60, 28, 37, 12, 53, 21, 44,
                     2, 63, 31, 34, 15, 50, 18, 47, 7, 58, 26, 39, 10, 55, 23, 42,
                     3, 62, 30, 35, 14, 51, 19, 46, 6, 59, 27, 38, 11, 54, 22, 43);
    }
    // Fallback for any size
    return range(1, $size);
}

function advanceWinnerInNewDraw($tournamentID, $categoryID, $currentRound, $currentBracketPos, $winnerID, $winnerName, $roundNames)
{
    global $DB;

    if ($currentRound <= 1) {
        return; // Already at final
    }

    $nextRound = $currentRound - 1;
    $nextBracketPos = ceil($currentBracketPos / 2);
    $isTeam1 = ($currentBracketPos % 2 == 1);

    // Check if next round match exists
    $DB->vals = array($tournamentID, $categoryID, $nextRound, $nextBracketPos);
    $DB->types = "iiii";
    $DB->sql = "SELECT fixtureID, team1ID, team2ID FROM " . $DB->pre . "ipt_fixture
                WHERE tournamentID=? AND categoryID=? AND roundNumber=? AND bracketPosition=? AND status=1";
    $nextMatch = $DB->dbRow();

    if ($nextMatch) {
        // Update existing match
        if ($isTeam1) {
            $DB->vals = array($winnerID, $winnerName, $nextMatch["fixtureID"]);
            $DB->types = "isi";
            $DB->sql = "UPDATE " . $DB->pre . "ipt_fixture SET team1ID=?, team1Name=? WHERE fixtureID=?";
        } else {
            $DB->vals = array($winnerID, $winnerName, $nextMatch["fixtureID"]);
            $DB->types = "isi";
            $DB->sql = "UPDATE " . $DB->pre . "ipt_fixture SET team2ID=?, team2Name=? WHERE fixtureID=?";
        }
        $DB->dbQuery();

        // Check if both players are set and one is from a BYE - auto advance again
        $DB->vals = array($nextMatch["fixtureID"]);
        $DB->types = "i";
        $DB->sql = "SELECT * FROM " . $DB->pre . "ipt_fixture WHERE fixtureID=?";
        $updated = $DB->dbRow();

        // If both slots are now filled and one came from BYE advancement, check for double-bye
        if ($updated["team1ID"] && !$updated["team2ID"]) {
            // Team 1 present, waiting for Team 2
        } else if (!$updated["team1ID"] && $updated["team2ID"]) {
            // Team 2 present, waiting for Team 1
        }
    } else {
        // Create next round match
        $data = array(
            "tournamentID" => $tournamentID,
            "categoryID" => $categoryID,
            "roundName" => $roundNames[$nextRound] ?? "Round " . $nextRound,
            "roundNumber" => $nextRound,
            "bracketPosition" => $nextBracketPos,
            "matchStatus" => "Scheduled",
            "status" => 1
        );

        if ($isTeam1) {
            $data["team1ID"] = $winnerID;
            $data["team1Name"] = $winnerName;
        } else {
            $data["team2ID"] = $winnerID;
            $data["team2Name"] = $winnerName;
        }

        $DB->table = $DB->pre . "ipt_fixture";
        $DB->data = $data;
        $DB->dbInsert();
    }
}

function seedFromIPA()
{
    global $DB;
    $tcID = intval($_POST["tcID"]);

    // Get tournament category
    $DB->vals = array($tcID);
    $DB->types = "i";
    $DB->sql = "SELECT tc.*, t.tournamentName FROM " . $DB->pre . "ipt_tournament_category tc
                JOIN " . $DB->pre . "ipt_tournament t ON tc.tournamentID=t.tournamentID
                WHERE tc.tcID=?";
    $tc = $DB->dbRow();

    if (!$tc) {
        setResponse(array("err" => 1, "msg" => "Category not found"));
        return;
    }

    $tournamentID = intval($tc["tournamentID"]);

    // Get confirmed participants with their IPA player links
    $DB->vals = array($tournamentID, $tcID, 1);
    $DB->types = "iii";
    $DB->sql = "SELECT p.participantID, p.player1Name, p.player1IPAID, p.player2Name, p.player2IPAID
                FROM " . $DB->pre . "ipt_participant p
                WHERE p.tournamentID=? AND p.tcID=? AND p.status=?
                AND p.participantStatus IN ('Confirmed', 'Checked-In')";
    $participants = $DB->dbRows();

    if (empty($participants)) {
        setResponse(array("err" => 1, "msg" => "No confirmed participants found"));
        return;
    }

    $seedUpdates = 0;

    foreach ($participants as $part) {
        $teamRanking = 0;
        $player1Rank = 0;
        $player2Rank = 0;

        // Get player 1 IPA ranking
        if (!empty($part["player1IPAID"])) {
            $DB->vals = array($part["player1IPAID"]);
            $DB->types = "s";
            $DB->sql = "SELECT ipaRanking FROM " . $DB->pre . "ipa_player WHERE playerCode=? AND status=1";
            $p1 = $DB->dbRow();
            if ($p1 && $p1["ipaRanking"] > 0) {
                $player1Rank = intval($p1["ipaRanking"]);
            }
        }

        // Get player 2 IPA ranking
        if (!empty($part["player2IPAID"])) {
            $DB->vals = array($part["player2IPAID"]);
            $DB->types = "s";
            $DB->sql = "SELECT ipaRanking FROM " . $DB->pre . "ipa_player WHERE playerCode=? AND status=1";
            $p2 = $DB->dbRow();
            if ($p2 && $p2["ipaRanking"] > 0) {
                $player2Rank = intval($p2["ipaRanking"]);
            }
        }

        // Team ranking = average of both players (lower is better)
        // If only one player has ranking, use that
        // If neither has ranking, use 9999 (unseeded)
        if ($player1Rank > 0 && $player2Rank > 0) {
            $teamRanking = floor(($player1Rank + $player2Rank) / 2);
        } else if ($player1Rank > 0) {
            $teamRanking = $player1Rank;
        } else if ($player2Rank > 0) {
            $teamRanking = $player2Rank;
        } else {
            $teamRanking = 9999; // Unseeded
        }

        // Update participant's seed number (inverted - lower ranking = lower seed number = higher seed)
        $DB->vals = array($teamRanking, $part["participantID"]);
        $DB->types = "ii";
        $DB->sql = "UPDATE " . $DB->pre . "ipt_participant SET seedNumber=? WHERE participantID=?";
        if ($DB->dbQuery()) {
            $seedUpdates++;
        }
    }

    // Now re-order and assign proper seed numbers (1, 2, 3...) based on ranking
    $DB->vals = array($tournamentID, $tcID, 1);
    $DB->types = "iii";
    $DB->sql = "SELECT participantID, seedNumber FROM " . $DB->pre . "ipt_participant
                WHERE tournamentID=? AND tcID=? AND status=?
                AND participantStatus IN ('Confirmed', 'Checked-In')
                ORDER BY seedNumber ASC, participantID ASC";
    $orderedParticipants = $DB->dbRows();

    $seedNum = 1;
    foreach ($orderedParticipants as $op) {
        if ($op["seedNumber"] < 9999) { // Only seed those with IPA rankings
            $DB->vals = array($seedNum, $op["participantID"]);
            $DB->types = "ii";
            $DB->sql = "UPDATE " . $DB->pre . "ipt_participant SET seedNumber=? WHERE participantID=?";
            $DB->dbQuery();
            $seedNum++;
        } else {
            // Unseeded - set to 0
            $DB->vals = array(0, $op["participantID"]);
            $DB->types = "ii";
            $DB->sql = "UPDATE " . $DB->pre . "ipt_participant SET seedNumber=? WHERE participantID=?";
            $DB->dbQuery();
        }
    }

    $seededCount = $seedNum - 1;
    setResponse(array("err" => 0, "msg" => "Seeding complete: " . $seededCount . " teams seeded from IPA rankings"));
}

function updateLiveScore()
{
    global $DB;
    $fixtureID = intval($_POST["fixtureID"]);

    // Get current fixture
    $DB->vals = array($fixtureID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "ipt_fixture WHERE fixtureID=?";
    $fixture = $DB->dbRow();

    if (!$fixture) {
        setResponse(array("err" => 1, "msg" => "Match not found"));
        return;
    }

    // Update scores
    $DB->vals = array(
        intval($_POST["team1Set1"] ?? 0),
        intval($_POST["team1Set2"] ?? 0),
        intval($_POST["team1Set3"] ?? 0),
        intval($_POST["team2Set1"] ?? 0),
        intval($_POST["team2Set2"] ?? 0),
        intval($_POST["team2Set3"] ?? 0),
        intval($_POST["currentSet"] ?? 1),
        $_POST["matchStatus"] ?? "In-Progress",
        $fixtureID
    );
    $DB->types = "iiiiiiisi";
    $DB->sql = "UPDATE " . $DB->pre . "ipt_fixture SET
                team1Set1=?, team1Set2=?, team1Set3=?,
                team2Set1=?, team2Set2=?, team2Set3=?,
                currentSet=?, matchStatus=?
                WHERE fixtureID=?";

    if ($DB->dbQuery()) {
        setResponse(array("err" => 0, "msg" => "Score updated"));
    } else {
        setResponse(array("err" => 1, "msg" => "Failed to update score"));
    }
}

function updateIPARankings()
{
    global $DB;
    $tournamentID = intval($_POST["tournamentID"]);

    // Get tournament details
    $DB->vals = array($tournamentID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "ipt_tournament WHERE tournamentID=?";
    $tournament = $DB->dbRow();

    if (!$tournament || $tournament["tournamentStatus"] != "Completed") {
        setResponse(array("err" => 1, "msg" => "Tournament must be completed first"));
        return;
    }

    // Get ranking points configuration
    $rankingPoints = array(
        1 => 1000,  // Winner
        2 => 750,   // Runner-up
        3 => 500,   // Semi-finalists
        4 => 500,
        5 => 300,   // Quarter-finalists
        6 => 300,
        7 => 300,
        8 => 300,
        9 => 150,   // Round of 16
    );

    // Get all participants with their final positions
    $DB->vals = array($tournamentID, 1);
    $DB->types = "ii";
    $DB->sql = "SELECT p.*, f.roundNumber, f.winnerID
                FROM " . $DB->pre . "ipt_participant p
                LEFT JOIN " . $DB->pre . "ipt_fixture f ON (
                    (f.team1ID = p.participantID OR f.team2ID = p.participantID)
                    AND f.matchStatus = 'Completed'
                    AND f.tournamentID = p.tournamentID
                )
                WHERE p.tournamentID=? AND p.status=?
                ORDER BY f.roundNumber ASC";
    $participants = $DB->dbRows();

    // Calculate positions and update IPA rankings
    $updatedPlayers = 0;

    foreach ($participants as $part) {
        // Determine position based on last match
        $position = 9; // Default to Round of 16 or lower

        if ($part["participantStatus"] == "Winner") {
            $position = 1;
        } else if ($part["roundNumber"] == 1 && $part["winnerID"] != $part["participantID"]) {
            $position = 2; // Lost in final
        } else if ($part["roundNumber"] == 2) {
            $position = 3; // Semi-finalist
        } else if ($part["roundNumber"] == 3) {
            $position = 5; // Quarter-finalist
        }

        $pointsEarned = $rankingPoints[$position] ?? 100;

        // Update player 1 IPA ranking if they have an IPA ID
        if (!empty($part["player1IPAID"])) {
            $DB->vals = array($part["player1IPAID"]);
            $DB->types = "s";
            $DB->sql = "SELECT playerID, ipaRanking, totalRankingPoints FROM " . $DB->pre . "ipa_player WHERE playerCode=?";
            $player = $DB->dbRow();

            if ($player) {
                $newPoints = intval($player["totalRankingPoints"]) + $pointsEarned;
                $DB->vals = array($newPoints, $player["playerID"]);
                $DB->types = "ii";
                $DB->sql = "UPDATE " . $DB->pre . "ipa_player SET totalRankingPoints=? WHERE playerID=?";
                $DB->dbQuery();
                $updatedPlayers++;
            }
        }

        // Update player 2 IPA ranking if they have an IPA ID
        if (!empty($part["player2IPAID"])) {
            $DB->vals = array($part["player2IPAID"]);
            $DB->types = "s";
            $DB->sql = "SELECT playerID, ipaRanking, totalRankingPoints FROM " . $DB->pre . "ipa_player WHERE playerCode=?";
            $player = $DB->dbRow();

            if ($player) {
                $newPoints = intval($player["totalRankingPoints"]) + $pointsEarned;
                $DB->vals = array($newPoints, $player["playerID"]);
                $DB->types = "ii";
                $DB->sql = "UPDATE " . $DB->pre . "ipa_player SET totalRankingPoints=? WHERE playerID=?";
                $DB->dbQuery();
                $updatedPlayers++;
            }
        }
    }

    // Recalculate IPA rankings based on total points (lower rank = higher points)
    $DB->vals = array(1);
    $DB->types = "i";
    $DB->sql = "SELECT playerID FROM " . $DB->pre . "ipa_player WHERE status=? ORDER BY totalRankingPoints DESC";
    $allPlayers = $DB->dbRows();

    $rank = 1;
    foreach ($allPlayers as $p) {
        $DB->vals = array($rank, $p["playerID"]);
        $DB->types = "ii";
        $DB->sql = "UPDATE " . $DB->pre . "ipa_player SET ipaRanking=? WHERE playerID=?";
        $DB->dbQuery();
        $rank++;
    }

    setResponse(array("err" => 0, "msg" => "Updated " . $updatedPlayers . " player rankings. IPA rankings recalculated."));
}

function clearDraw()
{
    global $DB;
    $tcID = intval($_POST["tcID"]);

    // Get tournamentID from the category record
    $DB->vals = array($tcID);
    $DB->types = "i";
    $DB->sql = "SELECT tournamentID FROM " . $DB->pre . "ipt_tournament_category WHERE tcID=?";
    $tc = $DB->dbRow();

    if (!$tc) {
        setResponse(array("err" => 1, "msg" => "Category not found"));
        return;
    }

    $tournamentID = intval($tc["tournamentID"]);

    // Soft delete all fixtures for this tournament category
    $DB->vals = array($tcID);
    $DB->types = "i";
    $DB->sql = "UPDATE " . $DB->pre . "ipt_fixture SET status=0 WHERE tcID=?";

    if ($DB->dbQuery()) {
        // Reset participant statuses back to Confirmed
        $DB->vals = array("Confirmed", $tournamentID, $tcID);
        $DB->types = "sii";
        $DB->sql = "UPDATE " . $DB->pre . "ipt_participant SET participantStatus=?
                    WHERE tournamentID=? AND tcID=? AND participantStatus IN ('Active', 'Eliminated')";
        $DB->dbQuery();

        setResponse(array("err" => 0, "msg" => "Draw cleared successfully"));
    } else {
        setResponse(array("err" => 1, "msg" => "Failed to clear draw"));
    }
}

// Handle AJAX actions
if (isset($_POST["xAction"])) {
    ob_start(); // Clean output buffer
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    ob_end_clean(); // Discard any output from includes

    header('Content-Type: application/json');
    $MXRES = mxCheckRequest(true, true); // Skip JWT token validation, use session auth
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addFixture(); break;
            case "UPDATE": updateFixture(); break;
            case "UPDATE_RESULT": updateMatchResult(); break;
            case "GENERATE_DRAW": generateDraw(); break;
            case "CLEAR_DRAW": clearDraw(); break;
            case "SEED_FROM_IPA": seedFromIPA(); break;
            case "UPDATE_LIVE_SCORE": updateLiveScore(); break;
            case "UPDATE_IPA_RANKINGS": updateIPARankings(); break;
        }
    }
    echo json_encode($MXRES);
    exit;
} else {
    if (function_exists("setModVars")) setModVars(array(
        "TBL" => "ipt_fixture",
        "PK" => "fixtureID",
        "hideAdd" => true  // Fixtures are auto-generated, not manually added
    ));
}
