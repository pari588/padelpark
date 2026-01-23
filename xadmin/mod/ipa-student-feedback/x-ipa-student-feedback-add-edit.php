<?php
$id = 0;
$D = array();
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "`=?";
    $D = $DB->dbRow();
}

// Get sessions for dropdown
$DB->sql = "SELECT s.sessionID, s.sessionCode, s.sessionDate, CONCAT(c.firstName, ' ', IFNULL(c.lastName,'')) as coachName
            FROM " . $DB->pre . "ipa_session s
            LEFT JOIN " . $DB->pre . "ipa_coach c ON s.coachID = c.coachID
            WHERE s.status=1
            ORDER BY s.sessionDate DESC";
$sessions = $DB->dbRows();
$sessionOpt = '<option value="">Select Session</option>';
$currentSession = $D["sessionID"] ?? "";
foreach ($sessions as $s) {
    $sel = ($currentSession == $s["sessionID"]) ? ' selected="selected"' : '';
    $sessionOpt .= '<option value="' . $s["sessionID"] . '" data-coach="' . $s["coachName"] . '"' . $sel . '>' . $s["sessionCode"] . ' (' . date("d M Y", strtotime($s["sessionDate"])) . ' - ' . htmlspecialchars($s["coachName"]) . ')</option>';
}

// Get players for dropdown
$DB->sql = "SELECT playerID, CONCAT(firstName, ' ', IFNULL(lastName,'')) as playerName, playerCode FROM " . $DB->pre . "ipa_player WHERE status=1 ORDER BY firstName";
$players = $DB->dbRows();
$playerOpt = '<option value="">Select Player</option>';
$currentPlayer = $D["playerID"] ?? "";
foreach ($players as $p) {
    $sel = ($currentPlayer == $p["playerID"]) ? ' selected="selected"' : '';
    $playerOpt .= '<option value="' . $p["playerID"] . '"' . $sel . '>' . $p["playerCode"] . ' - ' . htmlspecialchars($p["playerName"]) . '</option>';
}

// Get coaches for dropdown
$DB->sql = "SELECT coachID, CONCAT(firstName, ' ', IFNULL(lastName,'')) as coachName FROM " . $DB->pre . "ipa_coach WHERE status=1 AND coachStatus='Active' ORDER BY firstName";
$coaches = $DB->dbRows();
$coachOpt = '<option value="">Select Coach</option>';
$currentCoach = $D["coachID"] ?? "";
foreach ($coaches as $c) {
    $sel = ($currentCoach == $c["coachID"]) ? ' selected="selected"' : '';
    $coachOpt .= '<option value="' . $c["coachID"] . '"' . $sel . '>' . htmlspecialchars($c["coachName"]) . '</option>';
}

$arrFormBasic = array(
    array("type" => "select", "name" => "sessionID", "value" => $sessionOpt, "title" => "Session"),
    array("type" => "select", "name" => "playerID", "value" => $playerOpt, "title" => "Player", "validate" => "required"),
    array("type" => "select", "name" => "coachID", "value" => $coachOpt, "title" => "Coach", "validate" => "required"),
    array("type" => "date", "name" => "feedbackDate", "value" => $D["feedbackDate"] ?? date("Y-m-d"), "title" => "Feedback Date", "validate" => "required"),
);

$MXFRM = new mxForm();

// Rating fields
$ratingFields = array(
    'overallRating' => 'Overall Rating',
    'technicalSkillsRating' => 'Technical Skills',
    'communicationRating' => 'Communication',
    'punctualityRating' => 'Punctuality',
    'engagementRating' => 'Engagement'
);
?>

<style>
.rating-item {
    padding: 12px 0;
    border-bottom: 1px solid #ddd;
}
.rating-item:last-child {
    border-bottom: none;
}
.rating-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}
.rating-label {
    font-size: 14px;
    font-weight: 600;
}
.rating-value {
    font-size: 18px;
    font-weight: 700;
    color: #f59e0b;
}
.rating-slider {
    width: 100%;
    height: 6px;
    -webkit-appearance: none;
    appearance: none;
    background: #ddd;
    border-radius: 3px;
    outline: none;
}
.rating-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 18px;
    height: 18px;
    background: #8b5cf6;
    border-radius: 50%;
    cursor: pointer;
}
.rating-slider::-moz-range-thumb {
    width: 18px;
    height: 18px;
    background: #8b5cf6;
    border-radius: 50%;
    cursor: pointer;
    border: none;
}
</style>

<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Feedback Details</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormBasic); ?>
            </ul>
        </div>

        <div class="wrap-form f50">
            <h2 class="form-head">Ratings (0-5)</h2>
            <ul class="tbl-form">
                <?php foreach ($ratingFields as $fieldName => $fieldLabel) {
                    $val = floatval($D[$fieldName] ?? 0);
                ?>
                <li>
                    <div class="rating-header">
                        <span class="rating-label"><?php echo $fieldLabel; ?></span>
                        <span class="rating-value" id="disp_<?php echo $fieldName; ?>"><?php echo number_format($val, 1); ?></span>
                    </div>
                    <input type="range" name="<?php echo $fieldName; ?>" class="rating-slider"
                           min="0" max="5" step="0.5" value="<?php echo $val; ?>"
                           oninput="document.getElementById('disp_<?php echo $fieldName; ?>').textContent=parseFloat(this.value).toFixed(1)">
                </li>
                <?php } ?>
            </ul>
        </div>

        <div class="wrap-form f100">
            <h2 class="form-head">Comments</h2>
            <ul class="tbl-form">
                <li>
                    <label>Feedback Comments</label>
                    <textarea name="feedbackComments" rows="4" style="width: 100%;"><?php echo htmlspecialchars($D["feedbackComments"] ?? ""); ?></textarea>
                </li>
                <li>
                    <label>Improvement Suggestions</label>
                    <textarea name="improvementSuggestions" rows="4" style="width: 100%;"><?php echo htmlspecialchars($D["improvementSuggestions"] ?? ""); ?></textarea>
                </li>
            </ul>
        </div>

        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>

<script>
$(document).ready(function() {
    // Form submission
    $('#frmAddEdit').on('submit', function(e) {
        e.preventDefault();

        var formData = $(this).serialize();
        var action = <?php echo $id > 0 ? "'UPDATE'" : "'ADD'"; ?>;
        formData += '&xAction=' + action;
        <?php if ($id > 0) { ?>
        formData += '&feedbackID=<?php echo $id; ?>';
        <?php } ?>

        $.ajax({
            url: '<?php echo ADMINURL; ?>/mod/ipa-student-feedback/x-ipa-student-feedback.inc.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(r) {
                if (r.err == 0) {
                    window.location.href = '?p=ipa-student-feedback';
                } else {
                    alert(r.msg || 'Error saving feedback');
                }
            },
            error: function() {
                alert('Error saving feedback. Please try again.');
            }
        });
    });
});
</script>

<script>
// Define required JavaScript variables for form submission
var MODINCURL = '<?php echo ADMINURL; ?>/mod/ipa-student-feedback/x-ipa-student-feedback.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/ipa-student-feedback/';
var ADMINURL = '<?php echo ADMINURL; ?>';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
</script>
