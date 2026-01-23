<?php
// Get tournaments for dropdown
$DB->sql = "SELECT tournamentID, tournamentCode, tournamentName FROM " . $DB->pre . "ipt_tournament WHERE status=1 ORDER BY startDate DESC";
$DB->dbRows();
$tournaments = $DB->rows ?: array();
$tournamentOpt = '<option value="">Select Tournament</option>';
$selTournament = intval($_GET["tournamentID"] ?? 0);
foreach ($tournaments as $t) {
    $sel = ($selTournament == $t["tournamentID"]) ? ' selected="selected"' : '';
    $tournamentOpt .= '<option value="' . $t["tournamentID"] . '"' . $sel . '>' . htmlspecialchars($t["tournamentCode"] . " - " . $t["tournamentName"]) . '</option>';
}

// Get categories for selected tournament
$categories = array();
$selCategory = intval($_GET["categoryID"] ?? 0);
if ($selTournament) {
    $DB->vals = array($selTournament);
    $DB->types = "i";
    $DB->sql = "SELECT c.categoryID, c.categoryCode, c.categoryName,
                (SELECT COUNT(*) FROM " . $DB->pre . "ipt_participant p WHERE p.tournamentID=" . $selTournament . " AND p.categoryID=c.categoryID AND p.status=1) as participantCount,
                (SELECT COUNT(*) FROM " . $DB->pre . "ipt_fixture f WHERE f.tournamentID=" . $selTournament . " AND f.categoryID=c.categoryID AND f.status=1) as fixtureCount
                FROM " . $DB->pre . "ipt_tournament_category tc
                JOIN " . $DB->pre . "ipt_category c ON tc.categoryID=c.categoryID
                WHERE tc.tournamentID=? AND tc.status=1
                ORDER BY c.categoryName";
    $DB->dbRows();
    $categories = $DB->rows ?: array();
}

// Category dropdown
$categoryOpt = '<option value="">All Categories</option>';
foreach ($categories as $c) {
    $sel = ($selCategory == $c["categoryID"]) ? ' selected="selected"' : '';
    $categoryOpt .= '<option value="' . $c["categoryID"] . '"' . $sel . '>' . htmlspecialchars($c["categoryName"]) . '</option>';
}

// Build search form
$arrSearch = array(
    array("type" => "select", "name" => "tournamentID", "title" => "Tournament", "where" => "AND f.tournamentID=?", "dtype" => "i", "value" => $tournamentOpt, "default" => false),
    array("type" => "select", "name" => "categoryID", "title" => "Category", "where" => "AND f.categoryID=?", "dtype" => "i", "value" => $categoryOpt, "default" => false)
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT f.fixtureID FROM `" . $DB->pre . "ipt_fixture` f WHERE f.status=?" . $MXFRM->where;
$DB->dbQuery();
$MXTOTREC = $DB->numRows;

echo $strSearch;
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <?php if (!$selTournament) { ?>
            <!-- No Tournament Selected - Show prompt -->
            <div class="no-records" style="text-align:center; padding:60px 20px;">
                <i class="fa fa-trophy" style="font-size:48px;color:#ddd;margin-bottom:15px;display:block;"></i>
                <h3 style="margin:0 0 10px 0; color:#666;">Select a Tournament</h3>
                <p style="margin:0; color:#888;">
                    Choose a tournament from the dropdown above to manage fixtures.
                </p>
            </div>
        <?php } else if (!$selCategory && count($categories) > 0) { ?>
            <!-- Category Overview with Generate Buttons -->
            <h3 style="margin:0 0 20px 0;"><i class="fa fa-trophy"></i> Categories</h3>
            <table width="100%" border="0" cellspacing="0" cellpadding="10" class="tbl-list">
                <thead>
                    <tr>
                        <th align="left">Category</th>
                        <th align="center" width="15%">Participants</th>
                        <th align="center" width="15%">Fixtures</th>
                        <th align="center" width="30%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat) { ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($cat["categoryCode"] . " - " . $cat["categoryName"]); ?></strong></td>
                        <td align="center">
                            <span class="badge badge-info"><?php echo intval($cat["participantCount"]); ?></span>
                        </td>
                        <td align="center">
                            <span class="badge badge-<?php echo intval($cat["fixtureCount"]) > 0 ? 'success' : 'secondary'; ?>">
                                <?php echo intval($cat["fixtureCount"]); ?>
                            </span>
                        </td>
                        <td align="center">
                            <?php if (intval($cat["fixtureCount"]) > 0) { ?>
                                <a href="ipt-fixture-list/?tournamentID=<?php echo $selTournament; ?>&categoryID=<?php echo $cat["categoryID"]; ?>" class="btn btn-sm btn-info">
                                    <i class="fa fa-eye"></i> View Fixtures
                                </a>
                                <button type="button" class="btn btn-sm btn-warning" onclick="regenerateDraw(<?php echo $cat["categoryID"]; ?>, '<?php echo addslashes($cat["categoryName"]); ?>')">
                                    <i class="fa fa-sync"></i> Regenerate
                                </button>
                            <?php } else if (intval($cat["participantCount"]) >= 2) { ?>
                                <button type="button" class="btn btn-sm btn-success" onclick="generateDraw(<?php echo $cat["categoryID"]; ?>, '<?php echo addslashes($cat["categoryName"]); ?>')">
                                    <i class="fa fa-magic"></i> Generate Draw
                                </button>
                            <?php } else { ?>
                                <span class="text-muted">Need 2+ participants</span>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

        <?php } else if ($selTournament && $selCategory && $MXTOTREC > 0) { ?>
            <!-- Fixture List for specific category -->
            <?php
            $MXCOLS = array(
                array("#", "matchNo", ' width="5%" align="center"', true),
                array("Round", "roundName", ' width="12%" align="center"'),
                array("Team 1", "team1Name", ' width="25%" align="left"'),
                array("Score", "score", ' width="10%" align="center"'),
                array("Team 2", "team2Name", ' width="25%" align="left"'),
                array("Status", "matchStatus", ' width="10%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT f.*, c.categoryName
                        FROM `" . $DB->pre . "ipt_fixture` f
                        LEFT JOIN `" . $DB->pre . "ipt_category` c ON f.categoryID=c.categoryID
                        WHERE f.status=?" . $MXFRM->where . " ORDER BY f.roundNumber DESC, f.bracketPosition ASC" . mxQryLimit();
            $DB->dbRows();
            ?>
            <div style="margin-bottom:15px;">
                <a href="ipt-fixture-list/?tournamentID=<?php echo $selTournament; ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fa fa-arrow-left"></i> Back to Categories
                </a>
            </div>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Format team names with seeds
                        $t1 = htmlspecialchars($d["team1Name"] ?: "TBD");
                        if ($d["team1Seed"] > 0) $t1 = '<span class="badge badge-warning">[' . $d["team1Seed"] . ']</span> ' . $t1;
                        $d["team1Name"] = $t1;

                        $t2 = htmlspecialchars($d["team2Name"] ?: "TBD");
                        if ($d["team2Seed"] > 0) $t2 = '<span class="badge badge-warning">[' . $d["team2Seed"] . ']</span> ' . $t2;
                        $d["team2Name"] = $t2;

                        // Highlight winner
                        if ($d["winnerID"] == $d["team1ID"] && $d["winnerID"]) {
                            $d["team1Name"] = '<strong style="color:#2e7d32;">' . $d["team1Name"] . '</strong>';
                        }
                        if ($d["winnerID"] == $d["team2ID"] && $d["winnerID"]) {
                            $d["team2Name"] = '<strong style="color:#2e7d32;">' . $d["team2Name"] . '</strong>';
                        }

                        // Score display
                        if ($d["matchStatus"] == "Completed") {
                            $d["score"] = $d["team1Set1"] . "-" . $d["team2Set1"];
                            if ($d["team1Set2"] || $d["team2Set2"]) $d["score"] .= ", " . $d["team1Set2"] . "-" . $d["team2Set2"];
                            if ($d["team1Set3"] || $d["team2Set3"]) $d["score"] .= ", " . $d["team1Set3"] . "-" . $d["team2Set3"];
                        } else {
                            $d["score"] = "vs";
                        }

                        // Round badge
                        $d["roundName"] = '<span class="badge badge-info">' . $d["roundName"] . '</span>';

                        // Status badge
                        $statusColors = array("Scheduled" => "secondary", "In-Progress" => "warning", "Completed" => "success", "Bye" => "dark");
                        $d["matchStatus"] = '<span class="badge badge-' . ($statusColors[$d["matchStatus"]] ?? "secondary") . '">' . $d["matchStatus"] . '</span>';
                    ?>
                        <tr><?php echo getMAction("mid", $d["fixtureID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["fixtureID"], $d[$v[1]]) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records" style="text-align:center; padding:60px 20px;">
                <i class="fa fa-calendar-alt" style="font-size:48px;color:#ddd;margin-bottom:15px;display:block;"></i>
                <p style="margin:0; color:#888;">
                    No fixtures found for this category.
                </p>
                <a href="ipt-fixture-list/?tournamentID=<?php echo $selTournament; ?>" class="btn btn-primary" style="margin-top:15px;">
                    Back to Categories
                </a>
            </div>
        <?php } ?>
    </div>
</div>

<script>
function generateDraw(categoryID, categoryName) {
    if (!confirm('Generate draw for ' + categoryName + '?\n\nThis will create fixtures based on participant seeding.')) {
        return;
    }

    var tournamentID = <?php echo $selTournament ?: 0; ?>;

    $.ajax({
        url: '<?php echo ADMINURL; ?>/mod/ipt-fixture/x-ipt-fixture.inc.php',
        type: 'POST',
        data: {
            xAction: 'GENERATE_DRAW',
            tournamentID: tournamentID,
            categoryID: categoryID
        },
        dataType: 'json',
        success: function(res) {
            if (res.err == 0) {
                alert(res.msg || 'Draw generated!');
                window.location.href = 'ipt-fixture-list/?tournamentID=' + tournamentID + '&categoryID=' + categoryID;
            } else {
                alert('Error: ' + (res.msg || 'Unknown error'));
            }
        },
        error: function(xhr) {
            console.log('Error:', xhr.responseText);
            alert('Request failed');
        }
    });
}

function regenerateDraw(categoryID, categoryName) {
    if (!confirm('Regenerate draw for ' + categoryName + '?\n\nThis will DELETE all existing fixtures and create new ones.')) {
        return;
    }

    var tournamentID = <?php echo $selTournament ?: 0; ?>;

    $.ajax({
        url: '<?php echo ADMINURL; ?>/mod/ipt-fixture/x-ipt-fixture.inc.php',
        type: 'POST',
        data: {
            xAction: 'GENERATE_DRAW',
            tournamentID: tournamentID,
            categoryID: categoryID
        },
        dataType: 'json',
        success: function(res) {
            if (res.err == 0) {
                alert(res.msg || 'Draw regenerated!');
                window.location.href = 'ipt-fixture-list/?tournamentID=' + tournamentID + '&categoryID=' + categoryID;
            } else {
                alert('Error: ' + (res.msg || 'Unknown error'));
            }
        },
        error: function(xhr) {
            console.log('Error:', xhr.responseText);
            alert('Request failed');
        }
    });
}

// Hide the Add button - fixtures are auto-generated
$(function() {
    $('.page-nav a[href*="-add"]').hide();

    // Auto-submit when tournament is selected
    $('select[name="tournamentID"]').on('change', function() {
        $(this).closest('form').submit();
    });
});
</script>
<style>
/* Hide Add button for fixtures - they are auto-generated */
.page-nav a[href*="-add"], .page-nav a[href*="/add"] { display: none !important; }
</style>
