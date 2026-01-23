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

// Get players for dropdown
$DB->sql = "SELECT playerID, CONCAT(firstName, ' ', IFNULL(lastName,'')) as playerName, playerCode, currentLevel FROM " . $DB->pre . "ipa_player WHERE status=1 ORDER BY firstName";
$players = $DB->dbRows();
$playerOpt = '<option value="">Select Player</option>';
$currentPlayer = $D["playerID"] ?? "";
foreach ($players as $p) {
    $sel = ($currentPlayer == $p["playerID"]) ? ' selected="selected"' : '';
    $playerOpt .= '<option value="' . $p["playerID"] . '" data-level="' . $p["currentLevel"] . '"' . $sel . '>' . $p["playerCode"] . ' - ' . htmlspecialchars($p["playerName"]) . '</option>';
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

// Assessment types
$types = array("Session", "Monthly", "Quarterly", "Level-Test");
$typeOpt = '';
$currentType = $D["assessmentType"] ?? "Session";
foreach ($types as $t) {
    $sel = ($currentType == $t) ? ' selected="selected"' : '';
    $typeOpt .= '<option value="' . $t . '"' . $sel . '>' . $t . '</option>';
}

// Levels
$levels = array("Beginner", "Intermediate", "Advanced", "Pro");
$levelOpt = '';
$currentLevel = $D["currentLevel"] ?? "Beginner";
foreach ($levels as $l) {
    $sel = ($currentLevel == $l) ? ' selected="selected"' : '';
    $levelOpt .= '<option value="' . $l . '"' . $sel . '>' . $l . '</option>';
}
$recLevelOpt = '';
$recLevel = $D["recommendedLevel"] ?? "Beginner";
foreach ($levels as $l) {
    $sel = ($recLevel == $l) ? ' selected="selected"' : '';
    $recLevelOpt .= '<option value="' . $l . '"' . $sel . '>' . $l . '</option>';
}

$arrFormBasic = array(
    array("type" => "select", "name" => "playerID", "value" => $playerOpt, "title" => "Player", "validate" => "required"),
    array("type" => "select", "name" => "coachID", "value" => $coachOpt, "title" => "Assessed By", "validate" => "required"),
    array("type" => "select", "name" => "assessmentType", "value" => $typeOpt, "title" => "Assessment Type"),
    array("type" => "date", "name" => "assessmentDate", "value" => $D["assessmentDate"] ?? date("Y-m-d"), "title" => "Assessment Date", "validate" => "required"),
);

$arrFormLevel = array(
    array("type" => "select", "name" => "currentLevel", "value" => $levelOpt, "title" => "Current Level"),
    array("type" => "select", "name" => "recommendedLevel", "value" => $recLevelOpt, "title" => "Recommended Level"),
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
</style>

<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Assessment Details</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormBasic); ?>
            </ul>
        </div>

        <div class="wrap-form f50">
            <h2 class="form-head">Skill Level</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormLevel); ?>
            </ul>
        </div>

        <div class="wrap-form f50">
            <h2 class="form-head">Skill Ratings (0-5)</h2>
            <ul class="tbl-form">
                <?php
                $skillFields = array(
                    'technicalSkills' => 'Technical Skills',
                    'tacticalAwareness' => 'Tactical Awareness',
                    'physicalFitness' => 'Physical Fitness',
                    'mentalStrength' => 'Mental Strength',
                    'gameStrategy' => 'Game Strategy',
                    'consistency' => 'Consistency'
                );
                foreach ($skillFields as $fieldName => $fieldLabel) {
                    $val = floatval($D[$fieldName] ?? 0);
                ?>
                <li>
                    <div class="rating-header">
                        <span class="rating-label"><?php echo $fieldLabel; ?></span>
                        <span class="rating-value" id="val_<?php echo $fieldName; ?>"><?php echo number_format($val, 1); ?></span>
                    </div>
                    <input type="range" name="<?php echo $fieldName; ?>" class="rating-slider"
                           min="0" max="5" step="0.5" value="<?php echo $val; ?>"
                           oninput="document.getElementById('val_<?php echo $fieldName; ?>').textContent=parseFloat(this.value).toFixed(1);calcSkillScore();">
                </li>
                <?php } ?>
                <li>
                    <div class="overall-score">
                        <div class="label">Overall Score</div>
                        <div class="value" id="overallScore"><?php echo number_format($D["overallScore"] ?? 0, 1); ?></div>
                    </div>
                </li>
            </ul>
        </div>

        <div class="wrap-form f50">
            <h2 class="form-head">Feedback</h2>
            <ul class="tbl-form">
                <li>
                    <label>Strengths</label>
                    <textarea name="strengths" rows="3" style="width: 100%;"><?php echo htmlspecialchars($D["strengths"] ?? ""); ?></textarea>
                </li>
                <li>
                    <label>Areas for Improvement</label>
                    <textarea name="areasForImprovement" rows="3" style="width: 100%;"><?php echo htmlspecialchars($D["areasForImprovement"] ?? ""); ?></textarea>
                </li>
                <li>
                    <label>Training Recommendations</label>
                    <textarea name="trainingRecommendations" rows="3" style="width: 100%;"><?php echo htmlspecialchars($D["trainingRecommendations"] ?? ""); ?></textarea>
                </li>
                <li>
                    <label>Coach Notes</label>
                    <textarea name="coachNotes" rows="3" style="width: 100%;"><?php echo htmlspecialchars($D["coachNotes"] ?? ""); ?></textarea>
                </li>
            </ul>
        </div>

        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>

<script>
function calcSkillScore() {
    var sliders = document.querySelectorAll('.rating-slider');
    var total = 0;
    sliders.forEach(function(s) { total += parseFloat(s.value) || 0; });
    var avg = sliders.length > 0 ? (total / sliders.length) : 0;
    document.getElementById('overallScore').textContent = avg.toFixed(1);
}

$(document).ready(function() {
    calcSkillScore();
    // Form submission
    $('#frmAddEdit').on('submit', function(e) {
        e.preventDefault();

        var formData = $(this).serialize();
        var action = <?php echo $id > 0 ? "'UPDATE'" : "'ADD'"; ?>;
        formData += '&xAction=' + action;
        <?php if ($id > 0) { ?>
        formData += '&assessmentID=<?php echo $id; ?>';
        <?php } ?>

        $.ajax({
            url: '<?php echo ADMINURL; ?>/mod/ipa-coach-assessment/x-ipa-coach-assessment.inc.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(r) {
                if (r.err == 0) {
                    window.location.href = '?p=ipa-coach-assessment';
                } else {
                    alert(r.msg || 'Error saving assessment');
                }
            },
            error: function() {
                alert('Error saving assessment. Please try again.');
            }
        });
    });
});
</script>
