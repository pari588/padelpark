<?php
// Get completed sessions that haven't had coach feedback
$DB->sql = "SELECT s.sessionID, s.sessionCode, s.sessionDate, s.startTime, s.endTime,
                   s.coachFeedbackCompleted, s.coachID,
                   CONCAT(c.firstName, ' ', IFNULL(c.lastName,'')) as coachName,
                   pr.programName,
                   (SELECT COUNT(*) FROM " . $DB->pre . "ipa_session_participant sp WHERE sp.sessionID = s.sessionID AND sp.status=1) as participantCount
            FROM " . $DB->pre . "ipa_session s
            LEFT JOIN " . $DB->pre . "ipa_coach c ON s.coachID = c.coachID
            LEFT JOIN " . $DB->pre . "ipa_program pr ON s.programID = pr.programID
            WHERE s.status=1 AND s.sessionStatus='Completed'
            ORDER BY s.sessionDate DESC, s.startTime DESC
            LIMIT 50";
$DB->dbRows();
$sessions = $DB->rows ?: array();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <div style="padding:10px 15px; border-bottom:1px solid #eee;">
            <a href="<?php echo ADMINURL; ?>/ipa-coach-feedback-list/" class="btn btn-default btn-sm"><i class="fa fa-list"></i> Feedback List</a>
            <a href="<?php echo ADMINURL; ?>/ipa-coach-feedback-session/" class="btn btn-primary btn-sm"><i class="fa fa-users"></i> Session Feedback</a>
        </div>

        <!-- Session Selection -->
        <div id="sessionSelection">
            <div class="wrap-form">
                <h2 class="form-head"><i class="fa fa-calendar-check"></i> Select Session for Feedback</h2>
                <p style="padding:15px; color:#666;">
                    Select a completed session to provide skill feedback for all participants.
                </p>
            </div>

            <?php if (!empty($sessions)): ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="10" class="tbl-list">
                <thead>
                    <tr>
                        <th align="left">Session</th>
                        <th align="center" width="12%">Date</th>
                        <th align="center" width="12%">Time</th>
                        <th align="left" width="15%">Coach</th>
                        <th align="center" width="10%">Students</th>
                        <th align="center" width="10%">Status</th>
                        <th align="center" width="15%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sessions as $s):
                        $sessionDate = date('d M Y', strtotime($s['sessionDate']));
                        $sessionTime = date('h:i A', strtotime($s['startTime']));
                        $feedbackDone = $s['coachFeedbackCompleted'] == 1;
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($s['sessionCode']); ?></strong>
                            <?php if ($s['programName']): ?>
                            <br><small style="color:#888;"><?php echo htmlspecialchars($s['programName']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td align="center"><?php echo $sessionDate; ?></td>
                        <td align="center"><?php echo $sessionTime; ?></td>
                        <td><?php echo htmlspecialchars($s['coachName']); ?></td>
                        <td align="center">
                            <span class="badge badge-info"><?php echo $s['participantCount']; ?></span>
                        </td>
                        <td align="center">
                            <?php if ($feedbackDone): ?>
                                <span class="badge badge-success"><i class="fa fa-check"></i> Done</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td align="center">
                            <?php if ($s['participantCount'] > 0): ?>
                            <button type="button" class="btn btn-sm btn-primary" onclick="loadSessionFeedback(<?php echo $s['sessionID']; ?>, <?php echo $s['coachID']; ?>)">
                                <i class="fa fa-star"></i> <?php echo $feedbackDone ? 'Edit' : 'Give'; ?> Feedback
                            </button>
                            <?php else: ?>
                                <span class="text-muted">No participants</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-records" style="text-align:center; padding:60px 20px;">
                <i class="fa fa-calendar-check" style="font-size:48px;color:#ddd;margin-bottom:15px;display:block;"></i>
                <p style="margin:0; color:#888;">No completed sessions found</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Feedback Form (hidden initially) -->
        <div id="feedbackFormSection" style="display:none;">
            <div class="wrap-form">
                <h2 class="form-head">
                    <i class="fa fa-star"></i> Session Feedback
                    <button type="button" class="btn btn-sm btn-default" onclick="backToSessions()" style="float:right;">
                        <i class="fa fa-arrow-left"></i> Back to Sessions
                    </button>
                </h2>

                <div id="sessionInfo" style="background:#f0fdf4; padding:15px; margin:15px; border-radius:8px; border:1px solid #bbf7d0;">
                    <!-- Session info will be loaded here -->
                </div>

                <form id="bulkFeedbackForm">
                    <input type="hidden" name="sessionID" id="formSessionID" value="">
                    <input type="hidden" name="coachID" id="formCoachID" value="">

                    <div id="participantFeedback">
                        <!-- Participant feedback forms will be loaded here -->
                    </div>

                    <div style="text-align:center; padding:20px; border-top:1px solid #eee; margin-top:20px;">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa fa-save"></i> Save All Feedback
                        </button>
                        <button type="button" class="btn btn-default btn-lg" onclick="backToSessions()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.participant-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 20px;
    margin: 15px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.participant-card .player-name {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    margin-bottom: 5px;
}
.participant-card .player-level {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 15px;
}
.skill-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 12px;
    margin-bottom: 15px;
}
.skill-item {
    background: #f9fafb;
    padding: 10px;
    border-radius: 6px;
    text-align: center;
}
.skill-item label {
    display: block;
    font-size: 11px;
    color: #6b7280;
    margin-bottom: 6px;
}
.star-rating-mini {
    display: flex;
    justify-content: center;
    gap: 3px;
}
.star-rating-mini .star {
    font-size: 18px;
    color: #d1d5db;
    cursor: pointer;
    transition: color 0.15s;
}
.star-rating-mini .star:hover,
.star-rating-mini .star.active {
    color: #f59e0b;
}
.progress-select {
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 13px;
    width: 100%;
    max-width: 200px;
}
.notes-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-top: 10px;
}
.notes-row textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 13px;
    resize: vertical;
}
@media (max-width: 768px) {
    .notes-row {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
var currentSessionID = 0;
var currentCoachID = 0;

function loadSessionFeedback(sessionID, coachID) {
    currentSessionID = sessionID;
    currentCoachID = coachID;

    $.ajax({
        url: '<?php echo ADMINURL; ?>/mod/ipa-coach-feedback/x-ipa-coach-feedback.inc.php',
        type: 'POST',
        data: {
            xAction: 'GET_SESSION_PARTICIPANTS',
            sessionID: sessionID
        },
        dataType: 'json',
        success: function(res) {
            if (res.err == 0) {
                renderFeedbackForm(res.session, res.participants);
                $('#sessionSelection').hide();
                $('#feedbackFormSection').show();
            } else {
                alert('Error: ' + res.msg);
            }
        },
        error: function(xhr) {
            console.log('Error:', xhr.responseText);
            alert('Failed to load session data');
        }
    });
}

function renderFeedbackForm(session, participants) {
    $('#formSessionID').val(session.sessionID);
    $('#formCoachID').val(session.coachID);

    // Session info
    var sessionDate = new Date(session.sessionDate).toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'});
    var infoHtml = '<div style="display:flex;gap:30px;flex-wrap:wrap;">' +
        '<div><span style="color:#888;font-size:12px;">Session</span><br><strong>' + session.sessionCode + '</strong></div>' +
        '<div><span style="color:#888;font-size:12px;">Date</span><br><strong>' + sessionDate + '</strong></div>' +
        '<div><span style="color:#888;font-size:12px;">Coach</span><br><strong>' + session.coachName + '</strong></div>' +
        '<div><span style="color:#888;font-size:12px;">Program</span><br><strong>' + (session.programName || '-') + '</strong></div>' +
        '<div><span style="color:#888;font-size:12px;">Students</span><br><strong>' + participants.length + '</strong></div>' +
        '</div>';
    $('#sessionInfo').html(infoHtml);

    // Participant cards
    var html = '';
    var skills = [
        {field: 'forehandRating', label: 'Forehand'},
        {field: 'backhandRating', label: 'Backhand'},
        {field: 'serveRating', label: 'Serve'},
        {field: 'volleyRating', label: 'Volley'},
        {field: 'footworkRating', label: 'Footwork'},
        {field: 'gameAwarenessRating', label: 'Game Sense'}
    ];

    for (var i = 0; i < participants.length; i++) {
        var p = participants[i];
        html += '<div class="participant-card" data-player-id="' + p.playerID + '">';
        html += '<div class="player-name">' + p.playerName + '</div>';
        html += '<div class="player-level">Level: ' + (p.skillLevel || 'Not Set') + '</div>';

        html += '<div class="skill-grid">';
        for (var j = 0; j < skills.length; j++) {
            var skill = skills[j];
            var value = parseInt(p[skill.field]) || 0;
            html += '<div class="skill-item">';
            html += '<label>' + skill.label + '</label>';
            html += '<div class="star-rating-mini" data-field="' + skill.field + '">';
            for (var k = 1; k <= 5; k++) {
                html += '<span class="star ' + (k <= value ? 'active' : '') + '" data-value="' + k + '">&#9733;</span>';
            }
            html += '</div>';
            html += '<input type="hidden" name="' + skill.field + '_' + p.playerID + '" value="' + value + '">';
            html += '</div>';
        }
        html += '</div>';

        var progressStatus = p.progressStatus || 'Good';
        html += '<div style="margin-bottom:10px;">';
        html += '<label style="font-size:12px;color:#6b7280;">Progress Status</label><br>';
        html += '<select class="progress-select" name="progressStatus_' + p.playerID + '">';
        ['Excellent', 'Good', 'Average', 'Needs Work'].forEach(function(st) {
            html += '<option value="' + st + '"' + (st == progressStatus ? ' selected' : '') + '>' + st + '</option>';
        });
        html += '</select>';
        html += '</div>';

        html += '<div class="notes-row">';
        html += '<div><label style="font-size:12px;color:#6b7280;">Session Notes</label>';
        html += '<textarea name="sessionNotes_' + p.playerID + '" rows="2" placeholder="How did they perform?">' + (p.sessionNotes || '') + '</textarea></div>';
        html += '<div><label style="font-size:12px;color:#6b7280;">Areas to Work On</label>';
        html += '<textarea name="areasToWork_' + p.playerID + '" rows="2" placeholder="What should they focus on?">' + (p.areasToWork || '') + '</textarea></div>';
        html += '</div>';

        html += '</div>';
    }

    $('#participantFeedback').html(html);

    // Attach star rating events
    attachStarEvents();
}

function attachStarEvents() {
    $('.star-rating-mini .star').off('click').on('click', function() {
        var $container = $(this).closest('.star-rating-mini');
        var field = $container.data('field');
        var value = $(this).data('value');
        var playerID = $(this).closest('.participant-card').data('player-id');

        // Update hidden input
        $('input[name="' + field + '_' + playerID + '"]').val(value);

        // Update stars
        $container.find('.star').each(function() {
            $(this).toggleClass('active', $(this).data('value') <= value);
        });
    });
}

function backToSessions() {
    $('#feedbackFormSection').hide();
    $('#sessionSelection').show();
}

$(document).ready(function() {
    $('#bulkFeedbackForm').on('submit', function(e) {
        e.preventDefault();

        var sessionID = $('#formSessionID').val();
        var coachID = $('#formCoachID').val();
        var feedbacks = [];

        $('.participant-card').each(function() {
            var playerID = $(this).data('player-id');
            var fb = {
                playerID: playerID,
                forehandRating: $('input[name="forehandRating_' + playerID + '"]').val(),
                backhandRating: $('input[name="backhandRating_' + playerID + '"]').val(),
                serveRating: $('input[name="serveRating_' + playerID + '"]').val(),
                volleyRating: $('input[name="volleyRating_' + playerID + '"]').val(),
                footworkRating: $('input[name="footworkRating_' + playerID + '"]').val(),
                gameAwarenessRating: $('input[name="gameAwarenessRating_' + playerID + '"]').val(),
                progressStatus: $('select[name="progressStatus_' + playerID + '"]').val(),
                sessionNotes: $('textarea[name="sessionNotes_' + playerID + '"]').val(),
                areasToWork: $('textarea[name="areasToWork_' + playerID + '"]').val()
            };
            feedbacks.push(fb);
        });

        $.ajax({
            url: '<?php echo ADMINURL; ?>/mod/ipa-coach-feedback/x-ipa-coach-feedback.inc.php',
            type: 'POST',
            data: {
                xAction: 'BULK_ADD',
                sessionID: sessionID,
                coachID: coachID,
                feedbacks: JSON.stringify(feedbacks)
            },
            dataType: 'json',
            success: function(res) {
                if (res.err == 0) {
                    alert(res.msg);
                    location.reload();
                } else {
                    alert('Error: ' + res.msg);
                }
            },
            error: function(xhr) {
                console.log('Error:', xhr.responseText);
                alert('Failed to save feedback');
            }
        });
    });
});
</script>
