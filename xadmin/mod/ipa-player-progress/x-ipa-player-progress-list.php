<?php
// Build skill level dropdown
$levelOpt = '<option value="">All Levels</option>';
$selLevel = $_GET["skillLevel"] ?? "";
foreach (["Beginner", "Intermediate", "Advanced", "Professional"] as $lvl) {
    $sel = ($selLevel == $lvl) ? ' selected="selected"' : '';
    $levelOpt .= '<option value="' . $lvl . '"' . $sel . '>' . $lvl . '</option>';
}

// Search by name
$searchName = $_GET["searchName"] ?? "";

$arrSearch = array(
    array("type" => "text", "name" => "searchName", "title" => "Player Name", "where" => "AND (p.firstName LIKE ? OR p.lastName LIKE ?)", "dtype" => "ss", "default" => ""),
    array("type" => "select", "name" => "skillLevel", "title" => "Level", "where" => "AND p.skillLevel=?", "dtype" => "s", "value" => $levelOpt, "default" => false)
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);

// Fix search for name (need to add wildcards)
$searchVals = $MXFRM->vals;
foreach ($searchVals as $k => $v) {
    if (strpos($MXFRM->types, 'ss') !== false && $k < 2) {
        $searchVals[$k] = "%" . $v . "%";
    }
}

$DB->vals = $searchVals;
array_unshift($DB->vals, 1); // status=1
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT p.playerID FROM `" . $DB->pre . "ipa_player` p WHERE p.status=?" . $MXFRM->where;
$DB->dbQuery();
$MXTOTREC = $DB->numRows;
if (!$MXFRM->where && $MXTOTREC < 1) $strSearch = "";
echo $strSearch;
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <div class="wrap-form">
            <h2 class="form-head"><i class="fa fa-chart-line"></i> Player Progress Dashboard</h2>
            <p style="padding:15px; color:#666;">
                Select a player to view their skill progress, session history, and assessment reports.
            </p>
        </div>

        <?php if ($MXTOTREC > 0) {
            $DB->vals = $searchVals;
            array_unshift($DB->vals, 1);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT p.*,
                        CONCAT(p.firstName, ' ', IFNULL(p.lastName,'')) as playerName,
                        (SELECT COUNT(*) FROM " . $DB->pre . "ipa_session_participant sp WHERE sp.playerID = p.playerID AND sp.status=1) as sessionCount,
                        (SELECT COUNT(*) FROM " . $DB->pre . "ipa_coach_session_feedback f WHERE f.playerID = p.playerID AND f.status=1) as feedbackCount,
                        (SELECT AVG(f2.overallRating) FROM " . $DB->pre . "ipa_coach_session_feedback f2 WHERE f2.playerID = p.playerID AND f2.status=1 AND f2.overallRating > 0) as avgRating
                        FROM `" . $DB->pre . "ipa_player` p
                        WHERE p.status=?" . $MXFRM->where . " ORDER BY p.firstName" . mxQryLimit();
            $DB->dbRows();
        ?>

        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:20px; padding:20px;">
            <?php foreach ($DB->rows as $player):
                $avgRating = $player["avgRating"] ? number_format($player["avgRating"], 1) : "-";
                $levelColors = [
                    "Beginner" => "#10b981",
                    "Intermediate" => "#3b82f6",
                    "Advanced" => "#8b5cf6",
                    "Professional" => "#f59e0b"
                ];
                $levelColor = $levelColors[$player["skillLevel"]] ?? "#6b7280";
            ?>
            <div class="player-card" onclick="viewProgress(<?php echo $player['playerID']; ?>)" style="cursor:pointer;">
                <div class="player-avatar">
                    <?php echo strtoupper(substr($player["firstName"], 0, 1) . substr($player["lastName"] ?? "", 0, 1)); ?>
                </div>
                <div class="player-info">
                    <div class="player-name"><?php echo htmlspecialchars($player["playerName"]); ?></div>
                    <div class="player-level" style="background:<?php echo $levelColor; ?>;"><?php echo $player["skillLevel"] ?: "Not Set"; ?></div>
                </div>
                <div class="player-stats">
                    <div class="stat">
                        <span class="stat-value"><?php echo $player["sessionCount"]; ?></span>
                        <span class="stat-label">Sessions</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value"><?php echo $player["feedbackCount"]; ?></span>
                        <span class="stat-label">Feedback</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value"><?php echo $avgRating; ?></span>
                        <span class="stat-label">Avg Rating</span>
                    </div>
                </div>
                <div class="view-btn">
                    <i class="fa fa-chart-line"></i> View Progress
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php } else { ?>
            <div class="no-records" style="text-align:center; padding:60px 20px;">
                <i class="fa fa-users" style="font-size:48px;color:#ddd;margin-bottom:15px;display:block;"></i>
                <p style="margin:0; color:#888;">No players found</p>
            </div>
        <?php } ?>
    </div>
</div>

<style>
.player-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.2s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.player-card:hover {
    border-color: #10b981;
    box-shadow: 0 4px 12px rgba(16,185,129,0.15);
    transform: translateY(-2px);
}
.player-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: 600;
    margin: 0 auto 15px;
}
.player-info {
    text-align: center;
    margin-bottom: 15px;
}
.player-name {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    margin-bottom: 8px;
}
.player-level {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    color: #fff;
    font-size: 11px;
    font-weight: 500;
}
.player-stats {
    display: flex;
    justify-content: space-around;
    border-top: 1px solid #f3f4f6;
    padding-top: 15px;
    margin-bottom: 15px;
}
.player-stats .stat {
    text-align: center;
}
.player-stats .stat-value {
    display: block;
    font-size: 18px;
    font-weight: 700;
    color: #111827;
}
.player-stats .stat-label {
    font-size: 11px;
    color: #6b7280;
}
.view-btn {
    text-align: center;
    padding: 10px;
    background: #f0fdf4;
    border-radius: 8px;
    color: #10b981;
    font-weight: 500;
    font-size: 13px;
}
.player-card:hover .view-btn {
    background: #10b981;
    color: #fff;
}
</style>

<script>
function viewProgress(playerID) {
    window.location.href = '<?php echo ADMINURL; ?>/ipa-player-progress-view/?id=' + playerID;
}
</script>
