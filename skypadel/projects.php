<?php
/**
 * Sky Padel Client Portal - Projects List
 * Premium Athletic Modernism Design
 */
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/layout.php';
requireLogin();

$email = getClientEmail();
$projects = getClientProjects($email);

logActivity($email, 'ViewProject', 'ProjectList', 0);

renderHeader('My Projects');
?>

<?php if (empty($projects)): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
            </div>
            <h4 class="empty-state-title">No Projects Yet</h4>
            <p class="empty-state-text">Your padel court projects will appear here once they are created.</p>
        </div>
    </div>
<?php else: ?>
    <div class="grid grid-2">
        <?php foreach ($projects as $project):
            $progress = $project['totalMilestones'] > 0
                ? round(($project['completedMilestones'] / $project['totalMilestones']) * 100)
                : 0;
        ?>
            <div class="card project-card card-slice animate-slide-up">
                <div class="project-card-header court-pattern">
                    <div class="project-number"><?php echo e($project['projectNo']); ?></div>
                    <h3 class="project-name"><?php echo e($project['projectName']); ?></h3>
                    <div class="project-location">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <?php echo e($project['projectLocation'] ?: 'Location TBD'); ?>
                    </div>
                </div>
                <div class="project-card-body">
                    <div class="project-progress">
                        <div class="project-progress-header">
                            <span class="project-progress-label">Project Progress</span>
                            <span class="project-progress-value"><?php echo $progress; ?>%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $progress; ?>%;"></div>
                        </div>
                    </div>

                    <div class="project-meta">
                        <div class="project-meta-item">
                            <span class="project-meta-label">Status</span>
                            <span class="badge <?php echo getStatusBadgeClass($project['projectStatus']); ?>">
                                <?php echo e($project['projectStatus']); ?>
                            </span>
                        </div>
                        <div class="project-meta-item">
                            <span class="project-meta-label">Milestones</span>
                            <span class="project-meta-value"><?php echo $project['completedMilestones']; ?> / <?php echo $project['totalMilestones']; ?></span>
                        </div>
                        <div class="project-meta-item">
                            <span class="project-meta-label">Start Date</span>
                            <span class="project-meta-value"><?php echo $project['startDate'] ? date('M d, Y', strtotime($project['startDate'])) : 'TBD'; ?></span>
                        </div>
                        <div class="project-meta-item">
                            <span class="project-meta-label">Contract Value</span>
                            <span class="project-meta-value font-semibold"><?php echo formatMoney($project['contractAmount']); ?></span>
                        </div>
                    </div>
                </div>
                <div class="card-footer" style="padding: var(--space-md) var(--space-lg);">
                    <a href="<?php echo SITE_URL; ?>/project-detail.php?id=<?php echo $project['projectID']; ?>" class="btn btn-primary btn-sm" style="width: 100%;">
                        View Project Details
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php renderFooter(); ?>
