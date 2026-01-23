<?php
/**
 * IPT Fixture - Edit Only (Results Entry)
 * Fixtures are auto-generated, this page is only for viewing/entering results
 */

// Show message for add page (fixtures are auto-generated, not manually added)
if ($TPL->pageType == "add") {
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data" style="text-align:center; padding:60px 20px;">
        <i class="fa fa-magic" style="font-size:48px; color:#28a745; margin-bottom:15px; display:block;"></i>
        <h3>Fixtures are Auto-Generated</h3>
        <p>Fixtures cannot be added manually. They are automatically generated from the tournament's participant list.</p>
        <a href="ipt-fixture-list/" class="btn btn-primary"><i class="fa fa-arrow-left"></i> Go to Fixture List</a>
    </div>
</div>
<?php
    return;
}

$id = intval($_GET["id"] ?? 0);

if ($id <= 0) {
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data" style="text-align:center; padding:40px;">
        <p class="text-danger">No fixture ID provided. Please select a fixture from the list.</p>
        <a href="ipt-fixture-list/" class="btn btn-primary">Go to Fixture List</a>
    </div>
</div>
<?php
    return;
}

$DB->vals = array(1, $id);
$DB->types = "ii";
$DB->sql = "SELECT f.*, t.tournamentCode, t.tournamentName, c.categoryName
            FROM `" . $DB->pre . "ipt_fixture` f
            LEFT JOIN `" . $DB->pre . "ipt_tournament` t ON f.tournamentID=t.tournamentID
            LEFT JOIN `" . $DB->pre . "ipt_category` c ON f.categoryID=c.categoryID
            WHERE f.status=? AND f.fixtureID=?";
$D = $DB->dbRow();

if (!$D) {
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data" style="text-align:center; padding:40px;">
        <p class="text-danger">Fixture #<?php echo $id; ?> not found.</p>
        <a href="ipt-fixture-list/" class="btn btn-primary">Go to Fixture List</a>
    </div>
</div>
<?php
    return;
}

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmResult" method="post">
        <input type="hidden" name="fixtureID" value="<?php echo $id; ?>">
        <input type="hidden" name="team1ID" value="<?php echo $D["team1ID"]; ?>">
        <input type="hidden" name="team2ID" value="<?php echo $D["team2ID"]; ?>">
        <input type="hidden" name="team1Name" value="<?php echo htmlspecialchars($D["team1Name"]); ?>">
        <input type="hidden" name="team2Name" value="<?php echo htmlspecialchars($D["team2Name"]); ?>">

        <div class="wrap-form f50">
            <h2 class="form-head">Match Information</h2>
            <ul class="tbl-form">
                <li>
                    <div class="frm-lbl">Tournament</div>
                    <div class="frm-fld"><?php echo htmlspecialchars($D["tournamentCode"] . " - " . $D["tournamentName"]); ?></div>
                </li>
                <li>
                    <div class="frm-lbl">Category</div>
                    <div class="frm-fld"><span class="badge badge-info"><?php echo htmlspecialchars($D["categoryName"]); ?></span></div>
                </li>
                <li>
                    <div class="frm-lbl">Round</div>
                    <div class="frm-fld"><span class="badge badge-secondary"><?php echo $D["roundName"]; ?></span> - Match #<?php echo $D["matchNo"]; ?></div>
                </li>
                <li>
                    <div class="frm-lbl">Status</div>
                    <div class="frm-fld">
                        <span class="badge badge-<?php echo $D["matchStatus"] == "Completed" ? "success" : ($D["matchStatus"] == "In-Progress" ? "warning" : "secondary"); ?>">
                            <?php echo $D["matchStatus"]; ?>
                        </span>
                    </div>
                </li>
            </ul>
        </div>

        <?php if ($D["team1ID"] && $D["team2ID"]) { ?>
        <div class="wrap-form f50">
            <h2 class="form-head">Select Winner</h2>
            <ul class="tbl-form">
                <li>
                    <div class="frm-lbl">Winner</div>
                    <div class="frm-fld">
                        <label style="margin-right:20px; cursor:pointer;">
                            <input type="radio" name="winnerID" value="<?php echo $D["team1ID"]; ?>" <?php echo $D["winnerID"] == $D["team1ID"] ? 'checked' : ''; ?>>
                            <?php echo htmlspecialchars($D["team1Name"]); ?>
                        </label>
                        <label style="cursor:pointer;">
                            <input type="radio" name="winnerID" value="<?php echo $D["team2ID"]; ?>" <?php echo $D["winnerID"] == $D["team2ID"] ? 'checked' : ''; ?>>
                            <?php echo htmlspecialchars($D["team2Name"]); ?>
                        </label>
                    </div>
                </li>
            </ul>
        </div>
        <?php } ?>

        <div class="wrap-form f50">
            <h2 class="form-head" style="background:#1565c0; color:#fff;">
                <?php if ($D["team1Seed"]) { ?>[<?php echo $D["team1Seed"]; ?>] <?php } ?>
                <?php echo htmlspecialchars($D["team1Name"] ?: "TBD"); ?>
                <?php if ($D["winnerID"] == $D["team1ID"] && $D["winnerID"]) { ?><i class="fa fa-trophy" style="color:#ffc107;"></i><?php } ?>
            </h2>
            <ul class="tbl-form">
                <li>
                    <div class="frm-lbl">Set 1</div>
                    <div class="frm-fld"><input type="number" name="team1Set1" value="<?php echo $D["team1Set1"] ?? 0; ?>" class="form-control" style="width:80px;" min="0" max="99"></div>
                </li>
                <li>
                    <div class="frm-lbl">Set 2</div>
                    <div class="frm-fld"><input type="number" name="team1Set2" value="<?php echo $D["team1Set2"] ?? 0; ?>" class="form-control" style="width:80px;" min="0" max="99"></div>
                </li>
                <li>
                    <div class="frm-lbl">Set 3</div>
                    <div class="frm-fld"><input type="number" name="team1Set3" value="<?php echo $D["team1Set3"] ?? 0; ?>" class="form-control" style="width:80px;" min="0" max="99"></div>
                </li>
            </ul>
        </div>

        <div class="wrap-form f50">
            <h2 class="form-head" style="background:#e65100; color:#fff;">
                <?php if ($D["team2Seed"]) { ?>[<?php echo $D["team2Seed"]; ?>] <?php } ?>
                <?php echo htmlspecialchars($D["team2Name"] ?: "TBD"); ?>
                <?php if ($D["winnerID"] == $D["team2ID"] && $D["winnerID"]) { ?><i class="fa fa-trophy" style="color:#ffc107;"></i><?php } ?>
            </h2>
            <ul class="tbl-form">
                <li>
                    <div class="frm-lbl">Set 1</div>
                    <div class="frm-fld"><input type="number" name="team2Set1" value="<?php echo $D["team2Set1"] ?? 0; ?>" class="form-control" style="width:80px;" min="0" max="99"></div>
                </li>
                <li>
                    <div class="frm-lbl">Set 2</div>
                    <div class="frm-fld"><input type="number" name="team2Set2" value="<?php echo $D["team2Set2"] ?? 0; ?>" class="form-control" style="width:80px;" min="0" max="99"></div>
                </li>
                <li>
                    <div class="frm-lbl">Set 3</div>
                    <div class="frm-fld"><input type="number" name="team2Set3" value="<?php echo $D["team2Set3"] ?? 0; ?>" class="form-control" style="width:80px;" min="0" max="99"></div>
                </li>
            </ul>
        </div>

        <div class="wrap-form">
            <ul class="tbl-form">
                <li>
                    <div class="frm-lbl">&nbsp;</div>
                    <div class="frm-fld">
                        <button type="button" class="btn btn-success" onclick="saveResult()"><i class="fa fa-save"></i> Save Result</button>
                        <a href="ipt-fixture-list/?tournamentID=<?php echo $D["tournamentID"]; ?>&categoryID=<?php echo $D["categoryID"]; ?>" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
                    </div>
                </li>
            </ul>
        </div>
    </form>
</div>

<script>
function saveResult() {
    var form = document.getElementById('frmResult');
    var formData = new FormData(form);
    formData.append('xAction', 'UPDATE_RESULT');

    $.ajax({
        url: '<?php echo ADMINURL; ?>/mod/ipt-fixture/x-ipt-fixture.inc.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(res) {
            if (res.err == 0) {
                alert(res.msg || 'Result saved!');
                location.reload();
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
</script>

<script>
// Define required JavaScript variables for form submission
var MODINCURL = '<?php echo ADMINURL; ?>/mod/ipt-fixture/x-ipt-fixture.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/ipt-fixture/';
var ADMINURL = '<?php echo ADMINURL; ?>';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
</script>
