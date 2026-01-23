<?php
/**
 * Generate Student Feedback Links
 * Allows admins to generate shareable feedback links for session participants
 */

// Get completed sessions that haven't had feedback sent
$DB->sql = "SELECT s.sessionID, s.sessionCode, s.sessionDate, s.startTime, s.endTime,
                   s.studentFeedbackSent,
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
            <a href="<?php echo ADMINURL; ?>/ipa-student-feedback-list/" class="btn btn-default btn-sm"><i class="fa fa-list"></i> Feedback List</a>
            <a href="<?php echo ADMINURL; ?>/ipa-student-feedback-generate/" class="btn btn-primary btn-sm"><i class="fa fa-link"></i> Generate Links</a>
        </div>
        <div class="wrap-form">
            <h2 class="form-head"><i class="fa fa-link"></i> Generate Feedback Links</h2>
            <p style="padding:15px; color:#666;">
                Select a completed session to generate feedback links for all participants. Links can be shared via WhatsApp, SMS, or Email.
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
                    $feedbackSent = $s['studentFeedbackSent'] == 1;
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
                        <?php if ($feedbackSent): ?>
                            <span class="badge badge-success"><i class="fa fa-check"></i> Sent</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td align="center">
                        <?php if ($s['participantCount'] > 0): ?>
                        <button type="button" class="btn btn-sm btn-primary" onclick="generateLinks(<?php echo $s['sessionID']; ?>, '<?php echo addslashes($s['sessionCode']); ?>')">
                            <i class="fa fa-link"></i> <?php echo $feedbackSent ? 'Regenerate' : 'Generate'; ?> Links
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
</div>

<!-- Modal for showing generated links -->
<div id="linksModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9999; overflow:auto;">
    <div style="background:#fff; max-width:700px; margin:50px auto; border-radius:12px; box-shadow:0 10px 40px rgba(0,0,0,0.2);">
        <div style="padding:20px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0;"><i class="fa fa-link"></i> Feedback Links Generated</h3>
            <button type="button" onclick="closeModal()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#999;">&times;</button>
        </div>
        <div id="linksContent" style="padding:20px; max-height:60vh; overflow-y:auto;">
            <!-- Links will be inserted here -->
        </div>
        <div style="padding:15px 20px; border-top:1px solid #eee; background:#f8f9fa; border-radius:0 0 12px 12px;">
            <button type="button" class="btn btn-success" onclick="copyAllLinks()">
                <i class="fa fa-copy"></i> Copy All Links
            </button>
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Close</button>
        </div>
    </div>
</div>

<script>
var generatedLinks = [];

function generateLinks(sessionID, sessionCode) {
    if (!confirm('Generate feedback links for session ' + sessionCode + '?')) {
        return;
    }

    $.ajax({
        url: '<?php echo ADMINURL; ?>/mod/ipa-student-feedback/x-ipa-student-feedback.inc.php',
        type: 'POST',
        data: {
            xAction: 'GENERATE_BULK_LINKS',
            sessionID: sessionID
        },
        dataType: 'json',
        success: function(res) {
            if (res.err == 0 && res.links) {
                generatedLinks = res.links;
                showLinksModal(res.links, sessionCode);
                // Reload page to update status
                setTimeout(function() {
                    location.reload();
                }, 500);
            } else {
                alert('Error: ' + (res.msg || 'Failed to generate links'));
            }
        },
        error: function(xhr) {
            console.log('Error:', xhr.responseText);
            alert('Request failed');
        }
    });
}

function showLinksModal(links, sessionCode) {
    var html = '<p style="margin-bottom:15px; color:#666;">Session: <strong>' + sessionCode + '</strong> - ' + links.length + ' link(s) generated</p>';

    html += '<table style="width:100%; border-collapse:collapse;">';
    html += '<thead><tr style="background:#f5f5f5;"><th style="padding:10px; text-align:left; border-bottom:1px solid #ddd;">Student</th><th style="padding:10px; text-align:left; border-bottom:1px solid #ddd;">Contact</th><th style="padding:10px; text-align:center; border-bottom:1px solid #ddd;">Actions</th></tr></thead>';
    html += '<tbody>';

    for (var i = 0; i < links.length; i++) {
        var link = links[i];
        html += '<tr style="border-bottom:1px solid #eee;">';
        html += '<td style="padding:10px;"><strong>' + link.playerName + '</strong></td>';
        html += '<td style="padding:10px;">';
        if (link.phone) html += '<i class="fa fa-phone" style="color:#888; margin-right:5px;"></i>' + link.phone + '<br>';
        if (link.email) html += '<i class="fa fa-envelope" style="color:#888; margin-right:5px;"></i>' + link.email;
        html += '</td>';
        html += '<td style="padding:10px; text-align:center;">';
        html += '<button class="btn btn-sm btn-outline-primary" onclick="copyLink(\'' + link.url + '\')"><i class="fa fa-copy"></i></button> ';
        if (link.phone) {
            var waMsg = encodeURIComponent('Hi ' + link.playerName + '! Please share your feedback for your recent training session at Indian Padel Academy: ' + link.url);
            html += '<a href="https://wa.me/' + link.phone.replace(/[^0-9]/g, '') + '?text=' + waMsg + '" target="_blank" class="btn btn-sm btn-success"><i class="fab fa-whatsapp"></i></a>';
        }
        html += '</td>';
        html += '</tr>';
    }

    html += '</tbody></table>';

    document.getElementById('linksContent').innerHTML = html;
    document.getElementById('linksModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('linksModal').style.display = 'none';
}

function copyLink(url) {
    navigator.clipboard.writeText(url).then(function() {
        alert('Link copied to clipboard!');
    });
}

function copyAllLinks() {
    var text = '';
    for (var i = 0; i < generatedLinks.length; i++) {
        var link = generatedLinks[i];
        text += link.playerName + ': ' + link.url + '\n';
    }
    navigator.clipboard.writeText(text).then(function() {
        alert('All links copied to clipboard!');
    });
}

// Close modal on outside click
document.getElementById('linksModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
