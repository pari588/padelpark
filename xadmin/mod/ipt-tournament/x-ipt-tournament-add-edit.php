<?php
$id = 0;
$D = array();
$selectedCategories = array();
$categorySettings = array();

if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "` =?";
    $D = $DB->dbRow();

    // Get selected categories with entry fee and prize pool for this tournament
    $DB->vals = array($id);
    $DB->types = "i";
    $DB->sql = "SELECT tc.categoryID, tc.entryFee, tc.prizePool, tc.maxTeams
                FROM " . $DB->pre . "ipt_tournament_category tc
                WHERE tc.tournamentID=? AND tc.status=1";
    $DB->dbRows();
    $categorySettings = array();
    foreach ($DB->rows as $r) {
        $selectedCategories[] = $r["categoryID"];
        $categorySettings[$r["categoryID"]] = array(
            "entryFee" => $r["entryFee"],
            "prizePool" => $r["prizePool"],
            "maxTeams" => $r["maxTeams"]
        );
    }
}

// Get all categories
$DB->sql = "SELECT categoryID, categoryCode, categoryName FROM " . $DB->pre . "ipt_category WHERE status=1 ORDER BY sortOrder, categoryName";
$DB->dbRows();
$allCategories = $DB->rows ?: array();

// Build tournament type dropdown
$tourTypes = array("Local", "State", "National", "International", "Corporate", "Club");
$tourTypeOpt = "";
$currentType = $D["tournamentType"] ?? "Local";
foreach ($tourTypes as $tt) {
    $sel = ($currentType == $tt) ? ' selected="selected"' : '';
    $tourTypeOpt .= '<option value="' . $tt . '"' . $sel . '>' . $tt . '</option>';
}

// Build format dropdown
$formats = array("Knockout", "Round-Robin", "Swiss", "League", "Mixed");
$formatOpt = "";
$currentFormat = $D["tournamentFormat"] ?? "Knockout";
foreach ($formats as $f) {
    $sel = ($currentFormat == $f) ? ' selected="selected"' : '';
    $formatOpt .= '<option value="' . $f . '"' . $sel . '>' . $f . '</option>';
}

// Build registration source dropdown
$sources = array("Backend", "Hudle", "Website", "Mixed");
$sourceOpt = "";
$currentSource = $D["registrationSource"] ?? "Backend";
foreach ($sources as $s) {
    $sel = ($currentSource == $s) ? ' selected="selected"' : '';
    $sourceOpt .= '<option value="' . $s . '"' . $sel . '>' . $s . '</option>';
}

// Build status dropdown
$statuses = array("Draft", "Open", "Registration-Closed", "In-Progress", "Completed", "Cancelled");
$statusOpt = "";
$currentStatus = $D["tournamentStatus"] ?? "Draft";
foreach ($statuses as $st) {
    $sel = ($currentStatus == $st) ? ' selected="selected"' : '';
    $statusOpt .= '<option value="' . $st . '"' . $sel . '>' . $st . '</option>';
}

// Get locations dropdown
$whrArr = array("sql" => "status=?", "types" => "i", "vals" => array(1));
$locationOpt = getTableDD([
    "table" => $DB->pre . "pnp_location",
    "key" => "locationID",
    "val" => "locationName",
    "selected" => ($D['venueLocationID'] ?? 0),
    "where" => $whrArr
]);

$arrForm = array(
    array("type" => "text", "name" => "tournamentCode", "value" => $D["tournamentCode"] ?? "", "title" => "Tournament Code", "info" => '<span class="info">Leave blank for auto-generation</span>'),
    array("type" => "text", "name" => "tournamentName", "value" => $D["tournamentName"] ?? "", "title" => "Tournament Name", "validate" => "required"),
    array("type" => "select", "name" => "tournamentType", "value" => $tourTypeOpt, "title" => "Tournament Type", "validate" => "required"),
    array("type" => "select", "name" => "tournamentFormat", "value" => $formatOpt, "title" => "Format"),
    array("type" => "select", "name" => "tournamentStatus", "value" => $statusOpt, "title" => "Status"),
);

$arrFormDates = array(
    array("type" => "date", "name" => "startDate", "value" => $D["startDate"] ?? "", "title" => "Start Date", "validate" => "required"),
    array("type" => "date", "name" => "endDate", "value" => $D["endDate"] ?? "", "title" => "End Date", "validate" => "required"),
    array("type" => "date", "name" => "registrationOpenDate", "value" => $D["registrationOpenDate"] ?? "", "title" => "Registration Opens"),
    array("type" => "date", "name" => "registrationCloseDate", "value" => $D["registrationCloseDate"] ?? "", "title" => "Registration Closes"),
);

$arrFormVenue = array(
    array("type" => "select", "name" => "venueLocationID", "value" => $locationOpt, "title" => "Venue Location"),
    array("type" => "text", "name" => "venueName", "value" => $D["venueName"] ?? "", "title" => "Venue Name"),
    array("type" => "text", "name" => "venueCity", "value" => $D["venueCity"] ?? "", "title" => "Venue City"),
    array("type" => "textarea", "name" => "venueAddress", "value" => $D["venueAddress"] ?? "", "title" => "Venue Address", "params" => array("rows" => 2)),
);

$arrFormFinance = array(
    array("type" => "text", "name" => "entryFeeAmount", "value" => $D["entryFeeAmount"] ?? "0", "title" => "Entry Fee (Rs.)", "validate" => "number"),
    array("type" => "text", "name" => "totalPrizePurse", "value" => $D["totalPrizePurse"] ?? "0", "title" => "Total Prize Purse (Rs.)", "validate" => "number"),
    array("type" => "text", "name" => "estimatedBudget", "value" => $D["estimatedBudget"] ?? "0", "title" => "Estimated Budget (Rs.)", "validate" => "number"),
    array("type" => "text", "name" => "maxParticipants", "value" => $D["maxParticipants"] ?? "32", "title" => "Max Participants", "validate" => "required,number"),
);

$arrFormRegistration = array(
    array("type" => "select", "name" => "registrationSource", "value" => $sourceOpt, "title" => "Registration Source"),
    array("type" => "text", "name" => "hudleTournamentID", "value" => $D["hudleTournamentID"] ?? "", "title" => "Hudle Tournament ID"),
    array("type" => "checkbox", "name" => "isRankingEvent", "value" => $D["isRankingEvent"] ?? 0, "title" => "Is Ranking Event"),
);

$arrFormDirector = array(
    array("type" => "text", "name" => "directorName", "value" => $D["directorName"] ?? "", "title" => "Director Name"),
    array("type" => "text", "name" => "directorPhone", "value" => $D["directorPhone"] ?? "", "title" => "Director Phone"),
    array("type" => "text", "name" => "directorEmail", "value" => $D["directorEmail"] ?? "", "title" => "Director Email"),
);

$arrFormDetails = array(
    array("type" => "textarea", "name" => "description", "value" => $D["description"] ?? "", "title" => "Description", "params" => array("rows" => 3)),
    array("type" => "textarea", "name" => "rules", "value" => $D["rules"] ?? "", "title" => "Tournament Rules", "params" => array("rows" => 4)),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Tournament Info</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Dates</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormDates); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Venue</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormVenue); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Financial</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormFinance); ?>
            </ul>
        </div>
        <div class="wrap-form f100">
            <h2 class="form-head">Categories & Entry Fees</h2>
            <ul class="tbl-form">
                <li>
                    <div class="frm-lbl">Select Categories <span class="req">*</span></div>
                    <div class="frm-fld">
                        <?php if (empty($allCategories)) { ?>
                        <p style="color:#e53935; margin:10px 0;">No categories found. <a href="ipt-category-add/">Add categories first</a>.</p>
                        <?php } else { ?>
                        <table class="tbl-list" style="width:100%; margin-top:10px;">
                            <thead>
                                <tr>
                                    <th width="30" align="center"><i class="fa fa-check"></i></th>
                                    <th align="left">Category</th>
                                    <th width="130" align="center">Entry Fee (Rs.)</th>
                                    <th width="130" align="center">Prize Pool (Rs.)</th>
                                    <th width="100" align="center">Max Teams</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($allCategories as $cat) {
                                $catID = $cat["categoryID"];
                                $checked = in_array($catID, $selectedCategories) ? ' checked="checked"' : '';
                                $entryFee = $categorySettings[$catID]["entryFee"] ?? 0;
                                $prizePool = $categorySettings[$catID]["prizePool"] ?? 0;
                                $maxTeams = $categorySettings[$catID]["maxTeams"] ?? 16;
                            ?>
                                <tr class="category-row" data-cat="<?php echo $catID; ?>">
                                    <td align="center">
                                        <input type="checkbox" name="categories[]" value="<?php echo $catID; ?>" class="cat-checkbox"<?php echo $checked; ?> onchange="toggleCategoryRow(this)">
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($cat["categoryCode"]); ?></strong> - <?php echo htmlspecialchars($cat["categoryName"]); ?>
                                    </td>
                                    <td align="center">
                                        <input type="number" name="categoryEntryFee[<?php echo $catID; ?>]" value="<?php echo $entryFee; ?>" class="form-control cat-field" style="width:100px; text-align:right;" min="0" step="100" <?php echo !$checked ? 'disabled' : ''; ?>>
                                    </td>
                                    <td align="center">
                                        <input type="number" name="categoryPrizePool[<?php echo $catID; ?>]" value="<?php echo $prizePool; ?>" class="form-control cat-field" style="width:100px; text-align:right;" min="0" step="1000" <?php echo !$checked ? 'disabled' : ''; ?>>
                                    </td>
                                    <td align="center">
                                        <input type="number" name="categoryMaxTeams[<?php echo $catID; ?>]" value="<?php echo $maxTeams; ?>" class="form-control cat-field" style="width:70px; text-align:center;" min="2" max="64" <?php echo !$checked ? 'disabled' : ''; ?>>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                        <?php } ?>
                    </div>
                </li>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Registration Settings</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormRegistration); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Tournament Director</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormDirector); ?>
            </ul>
        </div>
        <div class="wrap-form f100">
            <h2 class="form-head">Details & Rules</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormDetails); ?>
            </ul>
        </div>

        <?php if ($TPL->pageType == "edit" && ($D["tournamentStatus"] ?? "") == "Completed") { ?>
        <div class="wrap-form f100" style="background:#e8f5e9; border:2px solid #43a047;">
            <h2 class="form-head" style="background:#43a047; color:#fff;">
                <i class="fa fa-trophy"></i> Post-Tournament Actions
            </h2>
            <div style="padding:20px; display:flex; gap:15px; flex-wrap:wrap; align-items:center;">
                <button type="button" class="btn btn-primary btn-lg" onclick="updateIPARankings()">
                    <i class="fa fa-chart-line"></i> Update IPA Rankings
                </button>
                <span style="color:#666;">
                    Award ranking points to participants based on their finish position and recalculate overall IPA rankings.
                </span>
            </div>
        </div>
        <?php } ?>

        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>

<script>
function toggleCategoryRow(checkbox) {
    var row = checkbox.closest('tr');
    var fields = row.querySelectorAll('.cat-field');
    fields.forEach(function(field) {
        field.disabled = !checkbox.checked;
        if (!checkbox.checked) {
            row.style.opacity = '0.5';
        } else {
            row.style.opacity = '1';
        }
    });
}

// Initialize row opacity on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.cat-checkbox').forEach(function(cb) {
        var row = cb.closest('tr');
        if (!cb.checked) {
            row.style.opacity = '0.5';
        }
    });
});
</script>

<?php if ($TPL->pageType == "edit" && ($D["tournamentStatus"] ?? "") == "Completed") { ?>
<script>
function updateIPARankings() {
    if (!confirm('Update IPA rankings for this tournament?\n\nThis will award ranking points to all participants based on their finish position and recalculate the overall IPA rankings.')) {
        return;
    }

    $.ajax({
        url: '<?php echo ADMINURL; ?>/mod/ipt-fixture/x-ipt-fixture.inc.php',
        type: 'POST',
        data: {
            xAction: 'UPDATE_IPA_RANKINGS',
            tournamentID: <?php echo $id; ?>
        },
        dataType: 'json',
        success: function(res) {
            if (res.err == 0) {
                alert(res.msg || 'IPA rankings updated successfully!');
            } else {
                alert('Error: ' + (res.msg || 'Unknown error'));
            }
        },
        error: function(xhr, status, error) {
            console.log('AJAX Error:', xhr.responseText);
            alert('Request failed. Check console for details.');
        }
    });
}
</script>
<?php } ?>

<script>
// Define required JavaScript variables for form submission
var MODINCURL = '<?php echo ADMINURL; ?>/mod/ipt-tournament/x-ipt-tournament.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/ipt-tournament/';
var ADMINURL = '<?php echo ADMINURL; ?>';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
</script>
