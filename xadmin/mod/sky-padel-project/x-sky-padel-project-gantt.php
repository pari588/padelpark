<?php
/**
 * Sky Padel Project - Gantt Chart View
 * Visual timeline of project milestones
 */
require_once("x-sky-padel-project.inc.php");

$projectID = intval($_GET["id"] ?? 0);

// Get project details
$DB->vals = array($projectID);
$DB->types = "i";
$DB->sql = "SELECT * FROM " . $DB->pre . "sky_padel_project WHERE projectID = ? AND status = 1";
$project = $DB->dbRow();

if (!$project) {
    echo '<div class="wrap-right">' . getPageNav() . '<div class="wrap-data"><div class="alert alert-danger">Project not found</div></div></div>';
    return;
}

// Get milestones
$DB->vals = array($projectID);
$DB->types = "i";
$DB->sql = "SELECT * FROM " . $DB->pre . "sky_padel_milestone
            WHERE projectID = ? AND status = 1
            ORDER BY milestoneOrder ASC, dueDate ASC";
$DB->dbRows();
$milestones = $DB->rows;

// Calculate date range for chart
$startDate = $project["startDate"] ?: date("Y-m-d");
$endDate = $project["expectedEndDate"] ?: date("Y-m-d", strtotime("+3 months"));

// Extend range to include all milestone dates
foreach ($milestones as $m) {
    if ($m["dueDate"] && $m["dueDate"] < $startDate) $startDate = $m["dueDate"];
    if ($m["dueDate"] && $m["dueDate"] > $endDate) $endDate = $m["dueDate"];
    if ($m["completedDate"] && $m["completedDate"] > $endDate) $endDate = $m["completedDate"];
}

// Add buffer
$startDate = date("Y-m-d", strtotime($startDate . " -7 days"));
$endDate = date("Y-m-d", strtotime($endDate . " +14 days"));

$today = date("Y-m-d");
?>
<style>
.gantt-container {
    background: #fff;
    border-radius: 12px;
    padding: 24px;
    margin: 20px 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.gantt-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e5e7eb;
}

.gantt-title {
    font-size: 20px;
    font-weight: 600;
    color: #1f2937;
}

.gantt-subtitle {
    font-size: 14px;
    color: #6b7280;
    margin-top: 4px;
}

.gantt-legend {
    display: flex;
    gap: 20px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #6b7280;
}

.legend-color {
    width: 16px;
    height: 16px;
    border-radius: 4px;
}

.legend-color.pending { background: #fbbf24; }
.legend-color.in-progress { background: #3b82f6; }
.legend-color.completed { background: #10b981; }
.legend-color.delayed { background: #ef4444; }

.gantt-chart {
    overflow-x: auto;
    overflow-y: visible;
}

.gantt-table {
    width: 100%;
    min-width: 900px;
    border-collapse: collapse;
}

.gantt-table th {
    padding: 12px 8px;
    font-size: 11px;
    font-weight: 600;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e5e7eb;
    text-align: left;
    position: sticky;
    top: 0;
    background: #fff;
    z-index: 10;
}

.gantt-table th.timeline-header {
    text-align: center;
}

.gantt-table td {
    padding: 8px;
    vertical-align: middle;
    border-bottom: 1px solid #f3f4f6;
}

.milestone-name {
    font-weight: 600;
    color: #1f2937;
    font-size: 14px;
}

.milestone-dates {
    font-size: 12px;
    color: #6b7280;
    margin-top: 2px;
}

.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.pending { background: #fef3c7; color: #92400e; }
.status-badge.in-progress { background: #dbeafe; color: #1e40af; }
.status-badge.completed { background: #d1fae5; color: #065f46; }
.status-badge.delayed { background: #fee2e2; color: #991b1b; }

.timeline-cell {
    position: relative;
    min-width: 40px;
    padding: 0 !important;
}

.timeline-grid {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-left: 1px solid #f3f4f6;
}

.timeline-grid.today {
    border-left: 2px solid #ef4444;
    z-index: 5;
}

.timeline-grid.weekend {
    background: #fafafa;
}

.timeline-grid.month-start {
    border-left: 2px solid #d1d5db;
}

.gantt-bar {
    position: absolute;
    height: 24px;
    top: 50%;
    transform: translateY(-50%);
    border-radius: 6px;
    display: flex;
    align-items: center;
    padding: 0 8px;
    font-size: 11px;
    font-weight: 600;
    color: #fff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    z-index: 1;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.gantt-bar:hover {
    transform: translateY(-50%) scale(1.02);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.gantt-bar.pending { background: linear-gradient(90deg, #fbbf24 0%, #f59e0b 100%); }
.gantt-bar.in-progress { background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%); }
.gantt-bar.completed { background: linear-gradient(90deg, #10b981 0%, #059669 100%); }
.gantt-bar.delayed { background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%); }

.gantt-bar-progress {
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    background: rgba(0,0,0,0.15);
    border-radius: 6px 0 0 6px;
}

.month-header {
    text-align: center;
    font-weight: 600;
    color: #374151;
    padding: 8px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

.week-row th {
    font-size: 10px;
    padding: 4px 2px;
    text-align: center;
    background: #f9fafb;
}

.today-marker {
    position: absolute;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #ef4444;
    z-index: 100;
}

.today-label {
    position: absolute;
    top: -18px;
    left: 50%;
    transform: translateX(-50%);
    background: #ef4444;
    color: #fff;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 4px;
    white-space: nowrap;
}

.gantt-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-primary { background: #0d9488; color: #fff; }
.btn-primary:hover { background: #0f766e; }
.btn-secondary { background: #f3f4f6; color: #374151; }
.btn-secondary:hover { background: #e5e7eb; }

/* Milestone Tooltip */
.gantt-tooltip {
    display: none;
    position: fixed;
    background: #1f2937;
    color: #fff;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 13px;
    z-index: 1000;
    max-width: 300px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.gantt-tooltip.show { display: block; }
.gantt-tooltip h5 { font-size: 14px; font-weight: 600; margin-bottom: 8px; }
.gantt-tooltip p { margin: 4px 0; color: #d1d5db; }

/* Progress bar under project header */
.project-progress {
    margin-top: 10px;
}

.progress-bar {
    height: 8px;
    background: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    border-radius: 4px;
    transition: width 0.5s ease;
}

.progress-text {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
}
</style>

<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data" style="padding: 20px;">
        <div class="gantt-container">
            <div class="gantt-header">
                <div>
                    <div class="gantt-title">
                        <i class="fa fa-project-diagram" style="color: #0d9488;"></i>
                        <?php echo htmlspecialchars($project["projectNo"] . " - " . $project["projectName"]); ?>
                    </div>
                    <div class="gantt-subtitle">
                        <?php echo htmlspecialchars($project["clientName"]); ?> |
                        <?php echo $project["startDate"] ? date("d M Y", strtotime($project["startDate"])) : "Not Started"; ?> -
                        <?php echo $project["expectedEndDate"] ? date("d M Y", strtotime($project["expectedEndDate"])) : "TBD"; ?>
                    </div>

                    <?php
                    $totalMilestones = count($milestones);
                    $completedMilestones = count(array_filter($milestones, function($m) { return $m["milestoneStatus"] == "Completed"; }));
                    $progressPercent = $totalMilestones > 0 ? round(($completedMilestones / $totalMilestones) * 100) : 0;
                    ?>
                    <div class="project-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $progressPercent; ?>%;"></div>
                        </div>
                        <div class="progress-text">
                            <span><?php echo $completedMilestones; ?> of <?php echo $totalMilestones; ?> milestones completed</span>
                            <span><?php echo $progressPercent; ?>%</span>
                        </div>
                    </div>
                </div>
                <div class="gantt-legend">
                    <div class="legend-item"><div class="legend-color pending"></div> Pending</div>
                    <div class="legend-item"><div class="legend-color in-progress"></div> In Progress</div>
                    <div class="legend-item"><div class="legend-color completed"></div> Completed</div>
                    <div class="legend-item"><div class="legend-color delayed"></div> Delayed</div>
                </div>
            </div>

            <?php if (empty($milestones)): ?>
                <div style="text-align: center; padding: 60px 20px; color: #6b7280;">
                    <i class="fa fa-tasks" style="font-size: 48px; opacity: 0.5; margin-bottom: 16px; display: block;"></i>
                    <h4 style="margin-bottom: 8px;">No Milestones Yet</h4>
                    <p>Add milestones to this project to visualize them on the Gantt chart.</p>
                    <a href="<?php echo ADMINURL; ?>/sky-padel-project-edit/?id=<?php echo $projectID; ?>#milestones" class="btn-primary btn-action" style="margin-top: 16px; display: inline-flex;">
                        <i class="fa fa-plus"></i> Add Milestones
                    </a>
                </div>
            <?php else: ?>
                <div class="gantt-chart" id="ganttChart">
                    <?php
                    // Generate days array
                    $days = array();
                    $currentDate = $startDate;
                    while ($currentDate <= $endDate) {
                        $days[] = $currentDate;
                        $currentDate = date("Y-m-d", strtotime($currentDate . " +1 day"));
                    }

                    $totalDays = count($days);
                    $dayWidth = 40; // pixels per day
                    ?>

                    <table class="gantt-table" style="width: <?php echo 250 + 80 + ($totalDays * $dayWidth); ?>px;">
                        <thead>
                            <tr>
                                <th style="width: 250px;">Milestone</th>
                                <th style="width: 80px;">Status</th>
                                <?php
                                $currentMonth = '';
                                foreach ($days as $day):
                                    $monthYear = date("M Y", strtotime($day));
                                    $isMonthStart = ($monthYear != $currentMonth);
                                    $currentMonth = $monthYear;
                                    $isWeekend = in_array(date("N", strtotime($day)), array(6, 7));
                                    $isToday = ($day == $today);
                                ?>
                                <th class="timeline-header" style="width: <?php echo $dayWidth; ?>px; padding: 4px 0; font-size: 10px;<?php echo $isWeekend ? ' background:#fafafa;' : ''; ?><?php echo $isToday ? ' background:#fef2f2;' : ''; ?>">
                                    <?php if ($isMonthStart): ?>
                                        <div style="font-size: 11px; font-weight: 700; color: #374151;"><?php echo date("M", strtotime($day)); ?></div>
                                    <?php endif; ?>
                                    <div style="color: #9ca3af;"><?php echo date("j", strtotime($day)); ?></div>
                                    <div style="font-size: 9px; color: #d1d5db;"><?php echo date("D", strtotime($day))[0]; ?></div>
                                </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($milestones as $m):
                                $mStart = $m["dueDate"] ?: $startDate;
                                $mEnd = $m["completedDate"] ?: $m["dueDate"] ?: $startDate;

                                // For display, show bar from due date
                                $barStart = max(0, (strtotime($mStart) - strtotime($startDate)) / 86400);
                                $barEnd = max($barStart + 1, (strtotime($mEnd) - strtotime($startDate)) / 86400 + 1);
                                $barWidth = max(1, $barEnd - $barStart) * $dayWidth;
                                $barLeft = $barStart * $dayWidth;

                                $statusClass = strtolower(str_replace("InProgress", "in-progress", $m["milestoneStatus"]));

                                // Check if delayed
                                if ($m["milestoneStatus"] != "Completed" && $m["dueDate"] && $m["dueDate"] < $today) {
                                    $statusClass = "delayed";
                                }
                            ?>
                            <tr>
                                <td>
                                    <div class="milestone-name"><?php echo htmlspecialchars($m["milestoneName"]); ?></div>
                                    <div class="milestone-dates">
                                        Due: <?php echo $m["dueDate"] ? date("d M", strtotime($m["dueDate"])) : "-"; ?>
                                        <?php if ($m["completedDate"]): ?>
                                        | Done: <?php echo date("d M", strtotime($m["completedDate"])); ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo str_replace("InProgress", "In Progress", $m["milestoneStatus"]); ?>
                                    </span>
                                </td>
                                <?php foreach ($days as $idx => $day):
                                    $isWeekend = in_array(date("N", strtotime($day)), array(6, 7));
                                    $isToday = ($day == $today);
                                    $isMonthStart = (date("j", strtotime($day)) == 1);
                                ?>
                                <td class="timeline-cell" style="position: relative; height: 48px;">
                                    <div class="timeline-grid <?php echo $isWeekend ? 'weekend' : ''; ?> <?php echo $isToday ? 'today' : ''; ?> <?php echo $isMonthStart ? 'month-start' : ''; ?>"></div>
                                    <?php if ($idx == 0 && $m["dueDate"]): ?>
                                        <div class="gantt-bar <?php echo $statusClass; ?>"
                                             style="left: <?php echo $barLeft; ?>px; width: <?php echo max($barWidth, $dayWidth); ?>px;"
                                             data-milestone='<?php echo htmlspecialchars(json_encode($m)); ?>'>
                                            <?php echo htmlspecialchars($m["milestoneName"]); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="gantt-actions">
                <a href="<?php echo ADMINURL; ?>/sky-padel-project-view/?id=<?php echo $projectID; ?>" class="btn-action btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back to Project
                </a>
                <a href="<?php echo ADMINURL; ?>/sky-padel-project-edit/?id=<?php echo $projectID; ?>#milestones" class="btn-action btn-primary">
                    <i class="fa fa-edit"></i> Edit Milestones
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Tooltip -->
<div class="gantt-tooltip" id="ganttTooltip"></div>

<script>
document.querySelectorAll('.gantt-bar').forEach(function(bar) {
    bar.addEventListener('mouseenter', function(e) {
        var data = JSON.parse(this.dataset.milestone);
        var tooltip = document.getElementById('ganttTooltip');

        var html = '<h5>' + data.milestoneName + '</h5>';
        html += '<p><strong>Status:</strong> ' + data.milestoneStatus + '</p>';
        if (data.dueDate) html += '<p><strong>Due:</strong> ' + formatDate(data.dueDate) + '</p>';
        if (data.completedDate) html += '<p><strong>Completed:</strong> ' + formatDate(data.completedDate) + '</p>';
        if (data.milestoneDescription) html += '<p><strong>Notes:</strong> ' + data.milestoneDescription + '</p>';

        tooltip.innerHTML = html;
        tooltip.classList.add('show');

        positionTooltip(e, tooltip);
    });

    bar.addEventListener('mousemove', function(e) {
        positionTooltip(e, document.getElementById('ganttTooltip'));
    });

    bar.addEventListener('mouseleave', function() {
        document.getElementById('ganttTooltip').classList.remove('show');
    });
});

function positionTooltip(e, tooltip) {
    var x = e.clientX + 15;
    var y = e.clientY + 15;

    // Keep tooltip in viewport
    if (x + tooltip.offsetWidth > window.innerWidth - 20) {
        x = e.clientX - tooltip.offsetWidth - 15;
    }
    if (y + tooltip.offsetHeight > window.innerHeight - 20) {
        y = e.clientY - tooltip.offsetHeight - 15;
    }

    tooltip.style.left = x + 'px';
    tooltip.style.top = y + 'px';
}

function formatDate(dateStr) {
    if (!dateStr || dateStr == '0000-00-00') return '-';
    var d = new Date(dateStr);
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
}
</script>
