<?php
$feedbackID = intval($_GET["id"] ?? 0);
$isEdit = $feedbackID > 0;
$data = array();

if ($isEdit) {
    $DB->vals = array($feedbackID);
    $DB->types = "i";
    $DB->sql = "SELECT f.*,
                CONCAT(p.firstName, ' ', IFNULL(p.lastName,'')) as playerName,
                CONCAT(c.firstName, ' ', IFNULL(c.lastName,'')) as coachName,
                s.sessionCode, s.sessionDate, s.startTime
                FROM " . $DB->pre . "ipa_coach_session_feedback f
                LEFT JOIN " . $DB->pre . "ipa_player p ON f.playerID = p.playerID
                LEFT JOIN " . $DB->pre . "ipa_coach c ON f.coachID = c.coachID
                LEFT JOIN " . $DB->pre . "ipa_session s ON f.sessionID = s.sessionID
                WHERE f.feedbackID=?";
    $DB->dbRows();
    if (!empty($DB->rows)) {
        $data = $DB->rows[0];
    }
}

// Build coach dropdown for new entries
$DB->sql = "SELECT coachID, CONCAT(firstName, ' ', IFNULL(lastName,'')) as coachName FROM " . $DB->pre . "ipa_coach WHERE status=1 ORDER BY firstName";
$coaches = $DB->dbRows() ?: array();

// Build player dropdown for new entries
$DB->sql = "SELECT playerID, CONCAT(firstName, ' ', IFNULL(lastName,'')) as playerName FROM " . $DB->pre . "ipa_player WHERE status=1 ORDER BY firstName";
$players = $DB->dbRows() ?: array();

// Build session dropdown for new entries
$DB->sql = "SELECT sessionID, sessionCode, sessionDate FROM " . $DB->pre . "ipa_session WHERE status=1 AND sessionStatus='Completed' ORDER BY sessionDate DESC LIMIT 50";
$sessions = $DB->dbRows() ?: array();

$progressStatuses = ["Excellent", "Good", "Average", "Needs Work"];
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <div class="wrap-form">
            <h2 class="form-head">
                <i class="fa fa-star"></i>
                <?php echo $isEdit ? "Edit Coach Feedback" : "Add Coach Feedback"; ?>
            </h2>

            <?php if ($isEdit && !empty($data)): ?>
            <div style="background:#f0fdf4; padding:15px; border-radius:8px; margin:15px; border:1px solid #bbf7d0;">
                <div style="display:flex; gap:30px; flex-wrap:wrap;">
                    <div>
                        <span style="color:#888; font-size:12px;">Player</span><br>
                        <strong><?php echo htmlspecialchars($data["playerName"]); ?></strong>
                    </div>
                    <div>
                        <span style="color:#888; font-size:12px;">Coach</span><br>
                        <strong><?php echo htmlspecialchars($data["coachName"]); ?></strong>
                    </div>
                    <div>
                        <span style="color:#888; font-size:12px;">Session</span><br>
                        <strong><?php echo htmlspecialchars($data["sessionCode"]); ?></strong>
                        <small style="color:#666;">(<?php echo date("d M Y", strtotime($data["sessionDate"])); ?>)</small>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <form id="feedbackForm" class="frm-fld">
                <input type="hidden" name="feedbackID" value="<?php echo $feedbackID; ?>">

                <?php if (!$isEdit): ?>
                <div class="frm-row">
                    <label>Session <span class="required">*</span></label>
                    <select name="sessionID" id="sessionID" class="inp-fld" required>
                        <option value="">Select Session</option>
                        <?php foreach ($sessions as $s): ?>
                        <option value="<?php echo $s["sessionID"]; ?>"><?php echo htmlspecialchars($s["sessionCode"] . " - " . date("d M Y", strtotime($s["sessionDate"]))); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="frm-row">
                    <label>Player <span class="required">*</span></label>
                    <select name="playerID" id="playerID" class="inp-fld" required>
                        <option value="">Select Player</option>
                        <?php foreach ($players as $p): ?>
                        <option value="<?php echo $p["playerID"]; ?>"><?php echo htmlspecialchars($p["playerName"]); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="frm-row">
                    <label>Coach <span class="required">*</span></label>
                    <select name="coachID" id="coachID" class="inp-fld" required>
                        <option value="">Select Coach</option>
                        <?php foreach ($coaches as $c): ?>
                        <option value="<?php echo $c["coachID"]; ?>"><?php echo htmlspecialchars($c["coachName"]); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <h3 style="margin:20px 15px 10px; font-size:14px; color:#666; border-bottom:1px solid #eee; padding-bottom:8px;">
                    <i class="fa fa-star" style="color:#f59e0b;"></i> Skill Ratings (1-5)
                </h3>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:15px; padding:15px;">
                    <?php
                    $skills = [
                        "forehandRating" => "Forehand",
                        "backhandRating" => "Backhand",
                        "serveRating" => "Serve",
                        "volleyRating" => "Volley",
                        "footworkRating" => "Footwork",
                        "gameAwarenessRating" => "Game Awareness"
                    ];
                    foreach ($skills as $field => $label):
                        $value = $data[$field] ?? 0;
                    ?>
                    <div class="rating-box" style="background:#f9fafb; padding:12px; border-radius:8px;">
                        <label style="display:block; margin-bottom:8px; font-size:13px; color:#374151;"><?php echo $label; ?></label>
                        <div class="star-rating" data-field="<?php echo $field; ?>">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?php echo $i <= $value ? 'active' : ''; ?>" data-value="<?php echo $i; ?>">&#9733;</span>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="<?php echo $field; ?>" value="<?php echo $value; ?>">
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="frm-row">
                    <label>Progress Status</label>
                    <select name="progressStatus" class="inp-fld">
                        <?php foreach ($progressStatuses as $st): ?>
                        <option value="<?php echo $st; ?>" <?php echo (($data["progressStatus"] ?? "Good") == $st) ? "selected" : ""; ?>><?php echo $st; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="frm-row">
                    <label>Session Notes</label>
                    <textarea name="sessionNotes" class="inp-fld" rows="3" placeholder="How did the student perform in this session?"><?php echo htmlspecialchars($data["sessionNotes"] ?? ""); ?></textarea>
                </div>

                <div class="frm-row">
                    <label>Areas to Work On</label>
                    <textarea name="areasToWork" class="inp-fld" rows="3" placeholder="What should the student focus on improving?"><?php echo htmlspecialchars($data["areasToWork"] ?? ""); ?></textarea>
                </div>

                <div class="frm-row" style="text-align:center; padding:20px;">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa fa-save"></i> <?php echo $isEdit ? "Update Feedback" : "Save Feedback"; ?>
                    </button>
                    <a href="<?php echo ADMINURL; ?>/ipa-coach-feedback-list/" class="btn btn-default btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.star-rating {
    display: flex;
    gap: 5px;
}
.star-rating .star {
    font-size: 24px;
    color: #d1d5db;
    cursor: pointer;
    transition: color 0.2s, transform 0.1s;
}
.star-rating .star:hover,
.star-rating .star.active {
    color: #f59e0b;
}
.star-rating .star:hover {
    transform: scale(1.1);
}
</style>

<script>
$(document).ready(function() {
    // Star rating interaction
    $('.star-rating .star').on('click', function() {
        var $container = $(this).closest('.star-rating');
        var field = $container.data('field');
        var value = $(this).data('value');

        // Update hidden input
        $container.siblings('input[name="' + field + '"]').val(value);

        // Update star visuals
        $container.find('.star').each(function() {
            if ($(this).data('value') <= value) {
                $(this).addClass('active');
            } else {
                $(this).removeClass('active');
            }
        });
    });

    // Hover effect
    $('.star-rating .star').on('mouseenter', function() {
        var $container = $(this).closest('.star-rating');
        var hoverValue = $(this).data('value');
        $container.find('.star').each(function() {
            if ($(this).data('value') <= hoverValue) {
                $(this).css('color', '#fbbf24');
            } else {
                $(this).css('color', '');
            }
        });
    });

    $('.star-rating').on('mouseleave', function() {
        $(this).find('.star').css('color', '');
    });

    // Form submission
    $('#feedbackForm').on('submit', function(e) {
        e.preventDefault();

        var formData = $(this).serialize();
        var feedbackID = $('input[name="feedbackID"]').val();
        formData += '&xAction=' + (feedbackID > 0 ? 'UPDATE' : 'ADD');

        $.ajax({
            url: '<?php echo ADMINURL; ?>/mod/ipa-coach-feedback/x-ipa-coach-feedback.inc.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(res) {
                if (res.err == 0) {
                    alert(res.msg);
                    window.location.href = '<?php echo ADMINURL; ?>/ipa-coach-feedback-list/';
                } else {
                    alert('Error: ' + res.msg);
                }
            },
            error: function(xhr) {
                console.log('Error:', xhr.responseText);
                alert('Request failed');
            }
        });
    });
});
</script>

<script>
// Define required JavaScript variables for form submission
var MODINCURL = '<?php echo ADMINURL; ?>/mod/ipa-coach-feedback/x-ipa-coach-feedback.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/ipa-coach-feedback/';
var ADMINURL = '<?php echo ADMINURL; ?>';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
</script>
