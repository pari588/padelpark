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

// Get all coaches for "Coach Being Reviewed" dropdown
$DB->sql = "SELECT coachID, CONCAT(firstName, ' ', IFNULL(lastName,'')) as coachName, certificationLevel FROM " . $DB->pre . "ipa_coach WHERE status=1 ORDER BY firstName";
$coaches = $DB->dbRows();
$coachOpt = '<option value="">Select Coach to Review</option>';
$currentCoach = $D["coachID"] ?? "";
foreach ($coaches as $c) {
    $sel = ($currentCoach == $c["coachID"]) ? ' selected="selected"' : '';
    $coachOpt .= '<option value="' . $c["coachID"] . '"' . $sel . '>' . htmlspecialchars($c["coachName"]) . ' (' . $c["certificationLevel"] . ')</option>';
}

// Get only Head Coaches for reviewer dropdown
$DB->sql = "SELECT coachID, CONCAT(firstName, ' ', IFNULL(lastName,'')) as coachName FROM " . $DB->pre . "ipa_coach WHERE status=1 AND certificationLevel='Head Coach' ORDER BY firstName";
$headCoaches = $DB->dbRows();
$reviewerOpt = '<option value="">Select Reviewer (Head Coach)</option>';
$currentReviewer = $D["reviewerID"] ?? "";
foreach ($headCoaches as $c) {
    $sel = ($currentReviewer == $c["coachID"]) ? ' selected="selected"' : '';
    $reviewerOpt .= '<option value="' . $c["coachID"] . '"' . $sel . '>' . htmlspecialchars($c["coachName"]) . '</option>';
}

// Review periods
$periods = array("Monthly", "Quarterly", "Annual", "Probation");
$periodOpt = '';
$currentPeriod = $D["reviewPeriod"] ?? "Monthly";
foreach ($periods as $p) {
    $sel = ($currentPeriod == $p) ? ' selected="selected"' : '';
    $periodOpt .= '<option value="' . $p . '"' . $sel . '>' . $p . '</option>';
}

// Review status
$statuses = array("Draft", "Submitted", "Acknowledged", "Disputed");
$statusOpt = '';
$currentStatus = $D["reviewStatus"] ?? "Draft";
foreach ($statuses as $s) {
    $sel = ($currentStatus == $s) ? ' selected="selected"' : '';
    $statusOpt .= '<option value="' . $s . '"' . $sel . '>' . $s . '</option>';
}

$arrFormBasic = array(
    array("type" => "select", "name" => "coachID", "value" => $coachOpt, "title" => "Coach Being Reviewed", "validate" => "required"),
    array("type" => "select", "name" => "reviewerID", "value" => $reviewerOpt, "title" => "Reviewed By", "validate" => "required"),
    array("type" => "select", "name" => "reviewPeriod", "value" => $periodOpt, "title" => "Review Period"),
);

$arrFormDates = array(
    array("type" => "date", "name" => "periodStartDate", "value" => $D["periodStartDate"] ?? date("Y-m-01"), "title" => "Period Start"),
    array("type" => "date", "name" => "periodEndDate", "value" => $D["periodEndDate"] ?? date("Y-m-t"), "title" => "Period End"),
    array("type" => "date", "name" => "reviewDate", "value" => $D["reviewDate"] ?? date("Y-m-d"), "title" => "Review Date"),
    array("type" => "select", "name" => "reviewStatus", "value" => $statusOpt, "title" => "Status"),
);

$MXFRM = new mxForm();
?>

<style>
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
    color: #8b5cf6;
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
.overall-score {
    text-align: center;
    padding: 20px;
    background: #8b5cf6;
    border-radius: 8px;
    margin-top: 15px;
}
.overall-score .label {
    font-size: 12px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.8);
    margin-bottom: 5px;
}
.overall-score .value {
    font-size: 36px;
    font-weight: 700;
    color: #fff;
}
.overall-score .category {
    font-size: 14px;
    font-weight: 600;
    color: rgba(255,255,255,0.9);
    text-transform: uppercase;
    margin-top: 5px;
}
</style>

<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Review Details</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormBasic); ?>
            </ul>
        </div>

        <div class="wrap-form f50">
            <h2 class="form-head">Period & Status</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormDates); ?>
            </ul>
        </div>

        <div class="wrap-form f50">
            <h2 class="form-head">Performance Metrics (0-5)</h2>
            <ul class="tbl-form">
                <?php
                $metricFields = array(
                    'sessionQuality' => 'Session Quality',
                    'studentEngagement' => 'Student Engagement',
                    'punctuality' => 'Punctuality',
                    'professionalism' => 'Professionalism',
                    'technicalKnowledge' => 'Technical Knowledge',
                    'communicationSkills' => 'Communication Skills',
                    'studentProgress' => 'Student Progress',
                    'teamwork' => 'Teamwork'
                );
                foreach ($metricFields as $fieldName => $fieldLabel) {
                    $val = floatval($D[$fieldName] ?? 0);
                ?>
                <li>
                    <div class="rating-header">
                        <span class="rating-label"><?php echo $fieldLabel; ?></span>
                        <span class="rating-value" id="val_<?php echo $fieldName; ?>"><?php echo number_format($val, 1); ?></span>
                    </div>
                    <input type="range" name="<?php echo $fieldName; ?>" class="rating-slider"
                           min="0" max="5" step="0.5" value="<?php echo $val; ?>"
                           oninput="document.getElementById('val_<?php echo $fieldName; ?>').textContent=parseFloat(this.value).toFixed(1);calcMetricScore();">
                </li>
                <?php } ?>
                <li>
                    <div class="overall-score">
                        <div class="label">Overall Rating</div>
                        <div class="value" id="overallScore"><?php echo number_format($D["overallRating"] ?? 0, 1); ?></div>
                        <div class="category" id="perfCategory"><?php echo $D["performanceCategory"] ?? "Unsatisfactory"; ?></div>
                    </div>
                </li>
            </ul>
        </div>

        <div class="wrap-form f50">
            <h2 class="form-head">Feedback & Action Plan</h2>
            <ul class="tbl-form">
                <li>
                    <label>Strengths</label>
                    <textarea name="strengths" rows="3" style="width: 100%;"><?php echo htmlspecialchars($D["strengths"] ?? ""); ?></textarea>
                </li>
                <li>
                    <label>Areas for Development</label>
                    <textarea name="areasForDevelopment" rows="3" style="width: 100%;"><?php echo htmlspecialchars($D["areasForDevelopment"] ?? ""); ?></textarea>
                </li>
                <li>
                    <label>Action Plan</label>
                    <textarea name="actionPlan" rows="3" style="width: 100%;"><?php echo htmlspecialchars($D["actionPlan"] ?? ""); ?></textarea>
                </li>
                <li>
                    <label>Head Coach Comments</label>
                    <textarea name="headCoachComments" rows="3" style="width: 100%;"><?php echo htmlspecialchars($D["headCoachComments"] ?? ""); ?></textarea>
                </li>
            </ul>
        </div>

        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>

<script>
function calcMetricScore() {
    var sliders = document.querySelectorAll('.rating-slider');
    var total = 0;
    sliders.forEach(function(s) { total += parseFloat(s.value) || 0; });
    var avg = sliders.length > 0 ? (total / sliders.length) : 0;
    document.getElementById('overallScore').textContent = avg.toFixed(1);
    var cat = 'Unsatisfactory';
    if (avg >= 4.5) cat = 'Excellent';
    else if (avg >= 3.5) cat = 'Good';
    else if (avg >= 2.5) cat = 'Satisfactory';
    else if (avg >= 1.5) cat = 'Needs Improvement';
    document.getElementById('perfCategory').textContent = cat;
}

$(document).ready(function() {
    calcMetricScore();
    // Form submission
    $('#frmAddEdit').on('submit', function(e) {
        e.preventDefault();

        var formData = $(this).serialize();
        var action = <?php echo $id > 0 ? "'UPDATE'" : "'ADD'"; ?>;
        formData += '&xAction=' + action;
        <?php if ($id > 0) { ?>
        formData += '&reviewID=<?php echo $id; ?>';
        <?php } ?>

        $.ajax({
            url: '<?php echo ADMINURL; ?>/mod/ipa-coach-review/x-ipa-coach-review.inc.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(r) {
                if (r.err == 0) {
                    window.location.href = '?p=ipa-coach-review';
                } else {
                    alert(r.msg || 'Error saving review');
                }
            },
            error: function() {
                alert('Error saving review. Please try again.');
            }
        });
    });
});
</script>

<script>
// Define required JavaScript variables for form submission
var MODINCURL = '<?php echo ADMINURL; ?>/mod/ipa-coach-review/x-ipa-coach-review.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/ipa-coach-review/';
var ADMINURL = '<?php echo ADMINURL; ?>';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
</script>
