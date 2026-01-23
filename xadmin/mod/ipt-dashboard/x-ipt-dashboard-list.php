<?php
// Get tournament stats
$DB->sql = "SELECT
    COUNT(*) as totalTournaments,
    COALESCE(SUM(CASE WHEN tournamentStatus='Open' THEN 1 ELSE 0 END), 0) as openTournaments,
    COALESCE(SUM(CASE WHEN tournamentStatus='In-Progress' THEN 1 ELSE 0 END), 0) as inProgressTournaments,
    COALESCE(SUM(CASE WHEN tournamentStatus='Completed' THEN 1 ELSE 0 END), 0) as completedTournaments,
    COALESCE(SUM(totalPrizePurse), 0) as totalPrizePurse,
    COALESCE(SUM(estimatedBudget), 0) as totalBudget
    FROM " . $DB->pre . "ipt_tournament WHERE status=1";
$tournamentStats = $DB->dbRow();
if (!$tournamentStats) $tournamentStats = array("totalTournaments" => 0, "openTournaments" => 0, "inProgressTournaments" => 0, "completedTournaments" => 0, "totalPrizePurse" => 0, "totalBudget" => 0);

// Get participant stats
$DB->sql = "SELECT
    COUNT(*) as totalParticipants,
    COALESCE(SUM(CASE WHEN participantStatus='Registered' THEN 1 ELSE 0 END), 0) as registered,
    COALESCE(SUM(CASE WHEN participantStatus='Confirmed' THEN 1 ELSE 0 END), 0) as confirmed,
    COALESCE(SUM(CASE WHEN participantStatus='Checked-In' THEN 1 ELSE 0 END), 0) as checkedIn,
    COALESCE(SUM(entryFee), 0) as totalEntryFees
    FROM " . $DB->pre . "ipt_participant WHERE status=1";
$participantStats = $DB->dbRow();
if (!$participantStats) $participantStats = array("totalParticipants" => 0, "registered" => 0, "confirmed" => 0, "checkedIn" => 0, "totalEntryFees" => 0);

// Get fixture stats
$DB->sql = "SELECT
    COUNT(*) as totalMatches,
    COALESCE(SUM(CASE WHEN matchStatus='Scheduled' THEN 1 ELSE 0 END), 0) as scheduled,
    COALESCE(SUM(CASE WHEN matchStatus='In-Progress' THEN 1 ELSE 0 END), 0) as inProgress,
    COALESCE(SUM(CASE WHEN matchStatus='Completed' THEN 1 ELSE 0 END), 0) as completed
    FROM " . $DB->pre . "ipt_fixture WHERE status=1";
$fixtureStats = $DB->dbRow();
if (!$fixtureStats) $fixtureStats = array("totalMatches" => 0, "scheduled" => 0, "inProgress" => 0, "completed" => 0);

// Get sponsor stats
$DB->sql = "SELECT
    COUNT(*) as totalSponsors,
    COALESCE(SUM(contractValue), 0) as totalContractValue,
    COALESCE(SUM(paymentReceived), 0) as totalReceived
    FROM " . $DB->pre . "ipt_sponsor WHERE status=1";
$sponsorStats = $DB->dbRow();
if (!$sponsorStats) $sponsorStats = array("totalSponsors" => 0, "totalContractValue" => 0, "totalReceived" => 0);

// Get prize stats
$DB->sql = "SELECT
    COUNT(*) as totalPrizes,
    COALESCE(SUM(prizeAmount), 0) as totalPrizeAmount,
    COALESCE(SUM(CASE WHEN disbursementStatus='Paid' THEN netAmount ELSE 0 END), 0) as paidAmount,
    COALESCE(SUM(CASE WHEN disbursementStatus='Pending' THEN netAmount ELSE 0 END), 0) as pendingAmount
    FROM " . $DB->pre . "ipt_prize WHERE status=1";
$prizeStats = $DB->dbRow();
if (!$prizeStats) $prizeStats = array("totalPrizes" => 0, "totalPrizeAmount" => 0, "paidAmount" => 0, "pendingAmount" => 0);

// Get inventory/expense stats
$DB->sql = "SELECT
    COALESCE(SUM(totalCost), 0) as totalInventoryCost,
    COALESCE(SUM(CASE WHEN itemType='Equipment' THEN totalCost ELSE 0 END), 0) as equipmentCost,
    COALESCE(SUM(CASE WHEN itemType='Balls' THEN totalCost ELSE 0 END), 0) as ballsCost,
    COALESCE(SUM(CASE WHEN itemType='Trophy' THEN totalCost ELSE 0 END), 0) as trophyCost,
    COALESCE(SUM(CASE WHEN itemType='Branding' THEN totalCost ELSE 0 END), 0) as brandingCost
    FROM " . $DB->pre . "ipt_inventory WHERE status=1";
$inventoryStats = $DB->dbRow();
if (!$inventoryStats) $inventoryStats = array("totalInventoryCost" => 0);

// Calculate P&L
$totalRevenue = floatval($participantStats["totalEntryFees"]) + floatval($sponsorStats["totalReceived"]);
$totalExpenses = floatval($prizeStats["paidAmount"]) + floatval($inventoryStats["totalInventoryCost"]);
$netPL = $totalRevenue - $totalExpenses;

// Get tournament-wise P&L for recent tournaments
$DB->sql = "SELECT t.tournamentID, t.tournamentCode, t.tournamentName, t.tournamentStatus, COALESCE(t.totalPrizePurse, 0) as totalPrizePurse,
            (SELECT COALESCE(SUM(p.entryFee), 0) FROM " . $DB->pre . "ipt_participant p WHERE p.tournamentID=t.tournamentID AND p.status=1) as entryRevenue,
            (SELECT COALESCE(SUM(s.paymentReceived), 0) FROM " . $DB->pre . "ipt_sponsor s WHERE s.tournamentID=t.tournamentID AND s.status=1) as sponsorRevenue,
            (SELECT COALESCE(SUM(pr.netAmount), 0) FROM " . $DB->pre . "ipt_prize pr WHERE pr.tournamentID=t.tournamentID AND pr.disbursementStatus='Paid' AND pr.status=1) as prizesPaid,
            (SELECT COALESCE(SUM(i.totalCost), 0) FROM " . $DB->pre . "ipt_inventory i WHERE i.tournamentID=t.tournamentID AND i.status=1) as inventoryCost
            FROM " . $DB->pre . "ipt_tournament t
            WHERE t.status=1
            ORDER BY t.startDate DESC LIMIT 5";
$tournamentPL = $DB->dbRows();
if (!$tournamentPL) $tournamentPL = array();

// Get recent tournaments
$DB->sql = "SELECT t.*,
            (SELECT COUNT(*) FROM " . $DB->pre . "ipt_participant p WHERE p.tournamentID=t.tournamentID AND p.status=1) as participantCount
            FROM " . $DB->pre . "ipt_tournament t WHERE t.status=1 ORDER BY t.startDate DESC LIMIT 5";
$recentTournaments = $DB->dbRows();
if (!$recentTournaments) $recentTournaments = array();

// Get upcoming matches
$DB->sql = "SELECT f.*, t.tournamentCode, c.categoryName
            FROM " . $DB->pre . "ipt_fixture f
            LEFT JOIN " . $DB->pre . "ipt_tournament t ON f.tournamentID=t.tournamentID
            LEFT JOIN " . $DB->pre . "ipt_tournament_category tc ON f.tcID=tc.tcID
            LEFT JOIN " . $DB->pre . "ipt_category c ON tc.categoryID=c.categoryID
            WHERE f.status=1 AND f.matchStatus='Scheduled' AND f.matchDate >= CURDATE()
            ORDER BY f.matchDate ASC, f.matchTime ASC LIMIT 10";
$upcomingMatches = $DB->dbRows();
if (!$upcomingMatches) $upcomingMatches = array();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <!-- Stats Cards Row 1 -->
        <div style="display:flex; flex-wrap:wrap; gap:20px; margin-bottom:25px;">
            <div style="flex:1; min-width:200px; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:#fff; padding:25px; border-radius:12px; box-shadow:0 4px 15px rgba(102,126,234,0.3);">
                <div style="font-size:14px; opacity:0.9; margin-bottom:8px;">Total Tournaments</div>
                <div style="font-size:42px; font-weight:700; line-height:1;"><?php echo number_format($tournamentStats["totalTournaments"]); ?></div>
                <div style="font-size:13px; margin-top:10px; opacity:0.8;">
                    <span style="background:rgba(255,255,255,0.2); padding:3px 8px; border-radius:4px; margin-right:5px;"><?php echo $tournamentStats["openTournaments"]; ?> Open</span>
                    <span style="background:rgba(255,255,255,0.2); padding:3px 8px; border-radius:4px;"><?php echo $tournamentStats["inProgressTournaments"]; ?> Live</span>
                </div>
            </div>
            <div style="flex:1; min-width:200px; background:linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color:#fff; padding:25px; border-radius:12px; box-shadow:0 4px 15px rgba(17,153,142,0.3);">
                <div style="font-size:14px; opacity:0.9; margin-bottom:8px;">Total Participants</div>
                <div style="font-size:42px; font-weight:700; line-height:1;"><?php echo number_format($participantStats["totalParticipants"]); ?></div>
                <div style="font-size:13px; margin-top:10px; opacity:0.8;">
                    <span style="background:rgba(255,255,255,0.2); padding:3px 8px; border-radius:4px; margin-right:5px;"><?php echo $participantStats["confirmed"]; ?> Confirmed</span>
                    <span style="background:rgba(255,255,255,0.2); padding:3px 8px; border-radius:4px;"><?php echo $participantStats["checkedIn"]; ?> Checked-In</span>
                </div>
            </div>
            <div style="flex:1; min-width:200px; background:linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color:#fff; padding:25px; border-radius:12px; box-shadow:0 4px 15px rgba(240,147,251,0.3);">
                <div style="font-size:14px; opacity:0.9; margin-bottom:8px;">Total Matches</div>
                <div style="font-size:42px; font-weight:700; line-height:1;"><?php echo number_format($fixtureStats["totalMatches"]); ?></div>
                <div style="font-size:13px; margin-top:10px; opacity:0.8;">
                    <span style="background:rgba(255,255,255,0.2); padding:3px 8px; border-radius:4px; margin-right:5px;"><?php echo $fixtureStats["scheduled"]; ?> Scheduled</span>
                    <span style="background:rgba(255,255,255,0.2); padding:3px 8px; border-radius:4px;"><?php echo $fixtureStats["completed"]; ?> Completed</span>
                </div>
            </div>
            <div style="flex:1; min-width:200px; background:linear-gradient(135deg, #fa709a 0%, #fee140 100%); color:#fff; padding:25px; border-radius:12px; box-shadow:0 4px 15px rgba(250,112,154,0.3);">
                <div style="font-size:14px; opacity:0.9; margin-bottom:8px;">Sponsors</div>
                <div style="font-size:42px; font-weight:700; line-height:1;"><?php echo number_format($sponsorStats["totalSponsors"]); ?></div>
                <div style="font-size:13px; margin-top:10px; opacity:0.8;">
                    Rs. <?php echo number_format($sponsorStats["totalReceived"]); ?> Received
                </div>
            </div>
        </div>

        <!-- Financial Stats Row -->
        <div style="display:flex; flex-wrap:wrap; gap:20px; margin-bottom:25px;">
            <div style="flex:1; min-width:250px; background:#fff; padding:25px; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.08);">
                <div style="display:flex; align-items:center; margin-bottom:15px;">
                    <div style="width:50px; height:50px; background:#e8f5e9; border-radius:10px; display:flex; align-items:center; justify-content:center; margin-right:15px;">
                        <i class="fa fa-rupee-sign" style="font-size:24px; color:#43a047;"></i>
                    </div>
                    <div>
                        <div style="font-size:13px; color:#666;">Entry Fees Collected</div>
                        <div style="font-size:28px; font-weight:700; color:#43a047;">Rs. <?php echo number_format($participantStats["totalEntryFees"]); ?></div>
                    </div>
                </div>
            </div>
            <div style="flex:1; min-width:250px; background:#fff; padding:25px; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.08);">
                <div style="display:flex; align-items:center; margin-bottom:15px;">
                    <div style="width:50px; height:50px; background:#fff3e0; border-radius:10px; display:flex; align-items:center; justify-content:center; margin-right:15px;">
                        <i class="fa fa-trophy" style="font-size:24px; color:#ff9800;"></i>
                    </div>
                    <div>
                        <div style="font-size:13px; color:#666;">Total Prize Pool</div>
                        <div style="font-size:28px; font-weight:700; color:#ff9800;">Rs. <?php echo number_format($tournamentStats["totalPrizePurse"]); ?></div>
                    </div>
                </div>
            </div>
            <div style="flex:1; min-width:250px; background:#fff; padding:25px; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.08);">
                <div style="display:flex; align-items:center; margin-bottom:15px;">
                    <div style="width:50px; height:50px; background:#e3f2fd; border-radius:10px; display:flex; align-items:center; justify-content:center; margin-right:15px;">
                        <i class="fa fa-handshake" style="font-size:24px; color:#1976d2;"></i>
                    </div>
                    <div>
                        <div style="font-size:13px; color:#666;">Sponsorship Value</div>
                        <div style="font-size:28px; font-weight:700; color:#1976d2;">Rs. <?php echo number_format($sponsorStats["totalContractValue"]); ?></div>
                    </div>
                </div>
            </div>
            <div style="flex:1; min-width:250px; background:#fff; padding:25px; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.08);">
                <div style="display:flex; align-items:center; margin-bottom:15px;">
                    <div style="width:50px; height:50px; background:#fce4ec; border-radius:10px; display:flex; align-items:center; justify-content:center; margin-right:15px;">
                        <i class="fa fa-medal" style="font-size:24px; color:#e91e63;"></i>
                    </div>
                    <div>
                        <div style="font-size:13px; color:#666;">Prizes Pending</div>
                        <div style="font-size:28px; font-weight:700; color:#e91e63;">Rs. <?php echo number_format($prizeStats["pendingAmount"]); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- P&L Summary Section -->
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:25px;">
            <div style="background:#fff; padding:20px; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.08);">
                <h3 style="margin:0 0 15px 0; font-size:16px; color:#333;">
                    <i class="fa fa-chart-line" style="color:#667eea;"></i> Financial P&L
                </h3>
                <table width="100%" cellpadding="6" style="font-size:13px;">
                    <tr style="border-bottom:1px dashed #eee;">
                        <td>Entry Fees</td>
                        <td align="right" style="color:#43a047; font-weight:600;">Rs. <?php echo number_format($participantStats["totalEntryFees"]); ?></td>
                    </tr>
                    <tr style="border-bottom:1px dashed #eee;">
                        <td>Sponsorship</td>
                        <td align="right" style="color:#43a047; font-weight:600;">Rs. <?php echo number_format($sponsorStats["totalReceived"]); ?></td>
                    </tr>
                    <tr style="background:#e8f5e9;">
                        <td style="font-weight:600;">Total Revenue</td>
                        <td align="right" style="color:#2e7d32; font-weight:700;">Rs. <?php echo number_format($totalRevenue); ?></td>
                    </tr>
                    <tr><td colspan="2" style="padding:5px;"></td></tr>
                    <tr style="border-bottom:1px dashed #eee;">
                        <td>Prize Money Paid</td>
                        <td align="right" style="color:#e53935; font-weight:600;">Rs. <?php echo number_format($prizeStats["paidAmount"]); ?></td>
                    </tr>
                    <tr style="border-bottom:1px dashed #eee;">
                        <td>Inventory/Expenses</td>
                        <td align="right" style="color:#e53935; font-weight:600;">Rs. <?php echo number_format($inventoryStats["totalInventoryCost"]); ?></td>
                    </tr>
                    <tr style="background:#ffebee;">
                        <td style="font-weight:600;">Total Expenses</td>
                        <td align="right" style="color:#c62828; font-weight:700;">Rs. <?php echo number_format($totalExpenses); ?></td>
                    </tr>
                </table>
                <div style="margin-top:15px; padding:15px; background:<?php echo $netPL >= 0 ? '#e8f5e9' : '#ffebee'; ?>; border-radius:8px; text-align:center;">
                    <div style="font-size:12px; color:#666;">Net Profit/Loss</div>
                    <div style="font-size:28px; font-weight:700; color:<?php echo $netPL >= 0 ? '#2e7d32' : '#c62828'; ?>;">
                        <?php echo $netPL >= 0 ? '+' : '-'; ?>Rs. <?php echo number_format(abs($netPL)); ?>
                    </div>
                </div>
            </div>
            <div style="background:#fff; padding:20px; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.08);">
                <h3 style="margin:0 0 15px 0; font-size:16px; color:#333;">
                    <i class="fa fa-list-alt" style="color:#f093fb;"></i> Tournament P&L
                </h3>
                <?php if (count($tournamentPL) > 0) { ?>
                <table width="100%" cellpadding="6" style="border-collapse:collapse; font-size:12px;">
                    <thead>
                        <tr style="background:#f5f5f5;">
                            <th align="left">Tournament</th>
                            <th align="right">Rev</th>
                            <th align="right">Exp</th>
                            <th align="right">P/L</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tournamentPL as $tpl) {
                            $tRevenue = floatval($tpl["entryRevenue"]) + floatval($tpl["sponsorRevenue"]);
                            $tExpenses = floatval($tpl["prizesPaid"]) + floatval($tpl["inventoryCost"]);
                            $tPL = $tRevenue - $tExpenses;
                        ?>
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="max-width:120px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                <strong><?php echo htmlspecialchars($tpl["tournamentCode"] ?? ""); ?></strong>
                            </td>
                            <td align="right" style="color:#43a047;"><?php echo number_format($tRevenue/1000, 0); ?>K</td>
                            <td align="right" style="color:#e53935;"><?php echo number_format($tExpenses/1000, 0); ?>K</td>
                            <td align="right" style="font-weight:600; color:<?php echo $tPL >= 0 ? '#2e7d32' : '#c62828'; ?>;">
                                <?php echo $tPL >= 0 ? '+' : '-'; ?><?php echo number_format(abs($tPL)/1000, 0); ?>K
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php } else { ?>
                <p style="color:#888; text-align:center; padding:20px;">No tournament data</p>
                <?php } ?>
            </div>
        </div>

        <!-- Recent Tournaments & Upcoming Matches -->
        <div style="display:flex; flex-wrap:wrap; gap:20px;">
            <div style="flex:1; min-width:400px; background:#fff; padding:25px; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.08);">
                <h3 style="margin:0 0 20px 0; font-size:18px; color:#333;">Recent Tournaments</h3>
                <?php if (count($recentTournaments) > 0) { ?>
                <table width="100%" cellpadding="10" style="border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f5f5f5;">
                            <th align="left" style="padding:12px; font-size:13px;">Tournament</th>
                            <th align="center" style="padding:12px; font-size:13px;">Date</th>
                            <th align="center" style="padding:12px; font-size:13px;">Players</th>
                            <th align="center" style="padding:12px; font-size:13px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentTournaments as $t) {
                            $statusColors = array("Draft" => "#9e9e9e", "Open" => "#4caf50", "Registration-Closed" => "#ff9800", "In-Progress" => "#2196f3", "Completed" => "#00bcd4", "Cancelled" => "#f44336");
                        ?>
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:12px;">
                                <strong><?php echo htmlspecialchars($t["tournamentCode"]); ?></strong><br>
                                <small style="color:#666;"><?php echo htmlspecialchars($t["tournamentName"]); ?></small>
                            </td>
                            <td align="center" style="padding:12px; font-size:13px;">
                                <?php echo date("d M Y", strtotime($t["startDate"])); ?>
                            </td>
                            <td align="center" style="padding:12px;">
                                <span style="font-weight:600;"><?php echo $t["participantCount"]; ?></span>/<?php echo $t["maxParticipants"]; ?>
                            </td>
                            <td align="center" style="padding:12px;">
                                <span style="background:<?php echo $statusColors[$t["tournamentStatus"]] ?? "#9e9e9e"; ?>; color:#fff; padding:4px 10px; border-radius:4px; font-size:12px;">
                                    <?php echo $t["tournamentStatus"]; ?>
                                </span>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php } else { ?>
                <p style="color:#888; text-align:center; padding:30px;">No tournaments yet</p>
                <?php } ?>
            </div>

            <div style="flex:1; min-width:400px; background:#fff; padding:25px; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.08);">
                <h3 style="margin:0 0 20px 0; font-size:18px; color:#333;">Upcoming Matches</h3>
                <?php if (count($upcomingMatches) > 0) { ?>
                <table width="100%" cellpadding="10" style="border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f5f5f5;">
                            <th align="left" style="padding:12px; font-size:13px;">Match</th>
                            <th align="center" style="padding:12px; font-size:13px;">Date</th>
                            <th align="center" style="padding:12px; font-size:13px;">Round</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcomingMatches as $m) { ?>
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:12px;">
                                <strong><?php echo htmlspecialchars($m["team1Name"] ?: "TBD"); ?></strong>
                                <span style="color:#999; margin:0 8px;">vs</span>
                                <strong><?php echo htmlspecialchars($m["team2Name"] ?: "TBD"); ?></strong><br>
                                <small style="color:#666;"><?php echo htmlspecialchars($m["tournamentCode"] . " - " . $m["categoryName"]); ?></small>
                            </td>
                            <td align="center" style="padding:12px; font-size:13px;">
                                <?php echo date("d M", strtotime($m["matchDate"])); ?>
                                <?php if ($m["matchTime"]) echo "<br><small>" . date("h:i A", strtotime($m["matchTime"])) . "</small>"; ?>
                            </td>
                            <td align="center" style="padding:12px;">
                                <span style="background:#e3f2fd; color:#1976d2; padding:4px 10px; border-radius:4px; font-size:12px;">
                                    <?php echo $m["roundName"]; ?>
                                </span>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php } else { ?>
                <p style="color:#888; text-align:center; padding:30px;">No upcoming matches</p>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
