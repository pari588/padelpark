<?php
$playerID = intval($_GET["id"] ?? 0);

if ($playerID < 1) {
    echo '<div class="wrap-right"><div class="wrap-data"><p style="padding:20px;color:red;">Invalid player ID</p></div></div>';
    return;
}

// Get player info
$DB->vals = array($playerID);
$DB->types = "i";
$DB->sql = "SELECT p.*, CONCAT(p.firstName, ' ', IFNULL(p.lastName,'')) as playerName
            FROM " . $DB->pre . "ipa_player p WHERE p.playerID=?";
$DB->dbRows();
$player = !empty($DB->rows) ? $DB->rows[0] : null;

if (!$player) {
    echo '<div class="wrap-right"><div class="wrap-data"><p style="padding:20px;color:red;">Player not found</p></div></div>';
    return;
}
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <div style="padding:15px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <a href="<?php echo ADMINURL; ?>/ipa-player-progress-list/" class="btn btn-default btn-sm">
                    <i class="fa fa-arrow-left"></i> Back to Players
                </a>
            </div>
            <div>
                <a href="<?php echo ADMINURL; ?>/ipa-player-edit/?id=<?php echo $playerID; ?>" class="btn btn-default btn-sm">
                    <i class="fa fa-edit"></i> Edit Player
                </a>
            </div>
        </div>

        <!-- Player Header -->
        <div class="player-header">
            <div class="player-avatar-lg">
                <?php echo strtoupper(substr($player["firstName"], 0, 1) . substr($player["lastName"] ?? "", 0, 1)); ?>
            </div>
            <div class="player-details">
                <h2><?php echo htmlspecialchars($player["playerName"]); ?></h2>
                <div class="player-meta">
                    <span class="level-badge"><?php echo $player["skillLevel"] ?: "Not Set"; ?></span>
                    <?php if ($player["phone"]): ?><span><i class="fa fa-phone"></i> <?php echo htmlspecialchars($player["phone"]); ?></span><?php endif; ?>
                    <?php if ($player["email"]): ?><span><i class="fa fa-envelope"></i> <?php echo htmlspecialchars($player["email"]); ?></span><?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Loading indicator -->
        <div id="loadingIndicator" style="text-align:center; padding:60px;">
            <i class="fa fa-spinner fa-spin fa-3x" style="color:#10b981;"></i>
            <p style="margin-top:15px; color:#666;">Loading progress data...</p>
        </div>

        <!-- Progress Content (loaded via AJAX) -->
        <div id="progressContent" style="display:none;">
            <!-- Stats Cards -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dbeafe;color:#3b82f6;"><i class="fa fa-calendar-check"></i></div>
                    <div class="stat-info">
                        <span class="stat-value" id="totalSessions">-</span>
                        <span class="stat-label">Total Sessions</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#d1fae5;color:#10b981;"><i class="fa fa-check-circle"></i></div>
                    <div class="stat-info">
                        <span class="stat-value" id="attendanceRate">-</span>
                        <span class="stat-label">Attendance Rate</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef3c7;color:#f59e0b;"><i class="fa fa-star"></i></div>
                    <div class="stat-info">
                        <span class="stat-value" id="avgRating">-</span>
                        <span class="stat-label">Average Rating</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fce7f3;color:#ec4899;"><i class="fa fa-clipboard-list"></i></div>
                    <div class="stat-info">
                        <span class="stat-value" id="totalAssessments">-</span>
                        <span class="stat-label">Assessments</span>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="charts-row">
                <div class="chart-card">
                    <h3><i class="fa fa-bullseye"></i> Current Skill Levels</h3>
                    <div class="chart-container">
                        <canvas id="radarChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <h3><i class="fa fa-chart-line"></i> Progress Over Time</h3>
                    <div class="chart-container">
                        <canvas id="lineChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Session Feedback -->
            <div class="section-card">
                <h3><i class="fa fa-comments"></i> Recent Session Feedback</h3>
                <div id="feedbackList">
                    <!-- Loaded via JS -->
                </div>
            </div>

            <!-- Formal Assessments -->
            <div class="section-card">
                <h3><i class="fa fa-clipboard-check"></i> Formal Assessments</h3>
                <div id="assessmentList">
                    <!-- Loaded via JS -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.player-header {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 25px;
    background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
    border-bottom: 1px solid #d1fae5;
}
.player-avatar-lg {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: 600;
    flex-shrink: 0;
}
.player-details h2 {
    margin: 0 0 8px;
    font-size: 24px;
    color: #111827;
}
.player-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    color: #6b7280;
    font-size: 14px;
}
.player-meta .level-badge {
    background: #10b981;
    color: #fff;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    padding: 20px;
}
.stat-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 18px;
    display: flex;
    align-items: center;
    gap: 15px;
}
.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
.stat-info .stat-value {
    display: block;
    font-size: 24px;
    font-weight: 700;
    color: #111827;
}
.stat-info .stat-label {
    font-size: 12px;
    color: #6b7280;
}
.charts-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    padding: 0 20px 20px;
}
.chart-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 20px;
}
.chart-card h3 {
    margin: 0 0 15px;
    font-size: 16px;
    color: #374151;
}
.chart-container {
    position: relative;
    height: 280px;
}
.section-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    margin: 0 20px 20px;
    overflow: hidden;
}
.section-card h3 {
    margin: 0;
    padding: 15px 20px;
    font-size: 16px;
    color: #374151;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}
.feedback-item {
    padding: 15px 20px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    gap: 15px;
}
.feedback-item:last-child {
    border-bottom: none;
}
.feedback-date {
    min-width: 80px;
    text-align: center;
}
.feedback-date .day {
    font-size: 24px;
    font-weight: 700;
    color: #10b981;
}
.feedback-date .month {
    font-size: 11px;
    color: #6b7280;
    text-transform: uppercase;
}
.feedback-content {
    flex: 1;
}
.feedback-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}
.feedback-session {
    font-weight: 600;
    color: #111827;
}
.feedback-coach {
    color: #6b7280;
    font-size: 13px;
}
.feedback-ratings {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 8px;
}
.rating-badge {
    background: #f3f4f6;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11px;
}
.rating-badge strong {
    color: #f59e0b;
}
.feedback-notes {
    font-size: 13px;
    color: #4b5563;
    font-style: italic;
}
.progress-badge {
    padding: 3px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
}
.progress-excellent { background: #d1fae5; color: #059669; }
.progress-good { background: #dbeafe; color: #2563eb; }
.progress-average { background: #fef3c7; color: #d97706; }
.progress-needs-work { background: #fee2e2; color: #dc2626; }
.no-data {
    padding: 40px 20px;
    text-align: center;
    color: #9ca3af;
}
@media (max-width: 768px) {
    .charts-row { grid-template-columns: 1fr; }
    .player-header { flex-direction: column; text-align: center; }
}
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
var radarChart = null;
var lineChart = null;

$(document).ready(function() {
    loadPlayerProgress(<?php echo $playerID; ?>);
});

function loadPlayerProgress(playerID) {
    $.ajax({
        url: '<?php echo ADMINURL; ?>/mod/ipa-player-progress/x-ipa-player-progress.inc.php',
        type: 'POST',
        data: {
            xAction: 'GET_PLAYER_PROGRESS',
            playerID: playerID
        },
        dataType: 'json',
        success: function(res) {
            $('#loadingIndicator').hide();
            if (res.err == 0) {
                renderProgress(res);
                $('#progressContent').show();
            } else {
                alert('Error: ' + res.msg);
            }
        },
        error: function(xhr) {
            $('#loadingIndicator').hide();
            console.log('Error:', xhr.responseText);
            alert('Failed to load progress data');
        }
    });
}

function renderProgress(data) {
    // Stats
    var attendance = data.attendance;
    var attendanceRate = attendance.totalSessions > 0 ?
        Math.round((attendance.presentCount / attendance.totalSessions) * 100) + '%' : '-';
    $('#totalSessions').text(attendance.totalSessions);
    $('#attendanceRate').text(attendanceRate);
    $('#totalAssessments').text(data.assessments.length);

    // Calculate avg rating from feedback
    var totalRating = 0, ratingCount = 0;
    data.sessionFeedback.forEach(function(f) {
        if (f.overallRating > 0) {
            totalRating += parseFloat(f.overallRating);
            ratingCount++;
        }
    });
    $('#avgRating').text(ratingCount > 0 ? (totalRating / ratingCount).toFixed(1) : '-');

    // Radar Chart
    renderRadarChart(data.skillAverages);

    // Line Chart
    renderLineChart(data.progressTrend);

    // Feedback List
    renderFeedbackList(data.sessionFeedback);

    // Assessment List
    renderAssessmentList(data.assessments);
}

function renderRadarChart(skills) {
    var ctx = document.getElementById('radarChart').getContext('2d');

    if (radarChart) radarChart.destroy();

    radarChart = new Chart(ctx, {
        type: 'radar',
        data: {
            labels: ['Forehand', 'Backhand', 'Serve', 'Volley', 'Footwork', 'Game Awareness'],
            datasets: [{
                label: 'Current Level',
                data: [skills.forehand, skills.backhand, skills.serve, skills.volley, skills.footwork, skills.gameAwareness],
                backgroundColor: 'rgba(16, 185, 129, 0.2)',
                borderColor: 'rgba(16, 185, 129, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(16, 185, 129, 1)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgba(16, 185, 129, 1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    beginAtZero: true,
                    max: 5,
                    ticks: { stepSize: 1 }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
}

function renderLineChart(progressTrend) {
    var ctx = document.getElementById('lineChart').getContext('2d');

    if (lineChart) lineChart.destroy();

    if (progressTrend.length === 0) {
        ctx.font = '14px sans-serif';
        ctx.fillStyle = '#9ca3af';
        ctx.textAlign = 'center';
        ctx.fillText('No progress data available', ctx.canvas.width / 2, ctx.canvas.height / 2);
        return;
    }

    var labels = progressTrend.map(function(f) {
        var d = new Date(f.feedbackDate);
        return d.toLocaleDateString('en-GB', {day: '2-digit', month: 'short'});
    });

    lineChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Overall Rating',
                data: progressTrend.map(function(f) { return parseFloat(f.overallRating) || 0; }),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, max: 5 }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
}

function renderFeedbackList(feedbackList) {
    var html = '';

    if (feedbackList.length === 0) {
        html = '<div class="no-data"><i class="fa fa-comments" style="font-size:32px;margin-bottom:10px;display:block;"></i>No session feedback yet</div>';
    } else {
        feedbackList.forEach(function(f) {
            var d = new Date(f.feedbackDate);
            var day = d.getDate();
            var month = d.toLocaleDateString('en-GB', {month: 'short'});

            var progressClass = 'progress-' + f.progressStatus.toLowerCase().replace(' ', '-');

            html += '<div class="feedback-item">';
            html += '<div class="feedback-date"><div class="day">' + day + '</div><div class="month">' + month + '</div></div>';
            html += '<div class="feedback-content">';
            html += '<div class="feedback-header"><span class="feedback-session">' + (f.sessionCode || 'Session') + '</span><span class="feedback-coach">by ' + f.coachName + '</span></div>';
            html += '<div class="feedback-ratings">';
            html += '<span class="rating-badge">Overall: <strong>' + parseFloat(f.overallRating).toFixed(1) + '</strong></span>';
            if (f.forehandRating > 0) html += '<span class="rating-badge">FH: <strong>' + f.forehandRating + '</strong></span>';
            if (f.backhandRating > 0) html += '<span class="rating-badge">BH: <strong>' + f.backhandRating + '</strong></span>';
            if (f.serveRating > 0) html += '<span class="rating-badge">Srv: <strong>' + f.serveRating + '</strong></span>';
            html += '<span class="progress-badge ' + progressClass + '">' + f.progressStatus + '</span>';
            html += '</div>';
            if (f.sessionNotes) html += '<div class="feedback-notes">"' + f.sessionNotes + '"</div>';
            html += '</div></div>';
        });
    }

    $('#feedbackList').html(html);
}

function renderAssessmentList(assessments) {
    var html = '';

    if (assessments.length === 0) {
        html = '<div class="no-data"><i class="fa fa-clipboard-check" style="font-size:32px;margin-bottom:10px;display:block;"></i>No formal assessments yet</div>';
    } else {
        assessments.forEach(function(a) {
            var d = new Date(a.assessmentDate);
            var day = d.getDate();
            var month = d.toLocaleDateString('en-GB', {month: 'short'});

            html += '<div class="feedback-item">';
            html += '<div class="feedback-date"><div class="day">' + day + '</div><div class="month">' + month + '</div></div>';
            html += '<div class="feedback-content">';
            html += '<div class="feedback-header"><span class="feedback-session">' + a.assessmentType + ' Assessment</span><span class="feedback-coach">by ' + a.coachName + '</span></div>';
            html += '<div class="feedback-ratings">';
            html += '<span class="rating-badge">Overall: <strong>' + parseFloat(a.overallScore).toFixed(1) + '</strong></span>';
            html += '<span class="rating-badge">Level: <strong>' + a.currentLevel + '</strong></span>';
            if (a.levelChangeRecommended == 1) {
                html += '<span class="rating-badge" style="background:#fef3c7;color:#d97706;">Recommended: ' + a.recommendedLevel + '</span>';
            }
            html += '</div>';
            if (a.strengths) html += '<div class="feedback-notes"><strong>Strengths:</strong> ' + a.strengths + '</div>';
            if (a.areasForImprovement) html += '<div class="feedback-notes"><strong>Areas to improve:</strong> ' + a.areasForImprovement + '</div>';
            html += '</div></div>';
        });
    }

    $('#assessmentList').html(html);
}
</script>
