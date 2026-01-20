<?php
/**
 * Sky Padel Client Portal - Dashboard
 * Premium Athletic Modernism Design
 */
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/layout.php';
requireLogin();

$email = getClientEmail();
$projects = getClientProjects($email);

// Get client info
$db = getDB();
$prefix = DB_PREFIX;
$stmt = $db->prepare("SELECT * FROM {$prefix}sky_padel_lead WHERE clientEmail = ? AND status = 1 ORDER BY created DESC LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();

// Calculate stats
$totalProjects = count($projects);
$activeProjects = count(array_filter($projects, fn($p) => $p['projectStatus'] === 'Active'));
$completedProjects = count(array_filter($projects, fn($p) => $p['projectStatus'] === 'Completed'));

// Get total contract value
$totalContractValue = array_sum(array_column($projects, 'contractAmount'));

// Get pending payments
$stmt = $db->prepare("
    SELECT SUM(p.contractAmount - COALESCE(p.advanceReceived, 0)) as pendingAmount
    FROM {$prefix}sky_padel_project p
    JOIN {$prefix}sky_padel_lead l ON p.leadID = l.leadID
    WHERE l.clientEmail = ? AND p.status = 1 AND p.projectStatus != 'Cancelled'
");
$stmt->bind_param("s", $email);
$stmt->execute();
$pendingResult = $stmt->get_result()->fetch_assoc();
$pendingPayments = $pendingResult['pendingAmount'] ?? 0;

logActivity($email, 'ViewDashboard');

renderHeader('Dashboard');
?>

<!-- Stats Grid -->
<div class="grid grid-4 mb-xl">
    <div class="card stat-card animate-slide-up stagger-1">
        <div class="stat-label">Total Projects</div>
        <div class="stat-value"><?php echo $totalProjects; ?></div>
        <div class="stat-change positive">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
            All time
        </div>
    </div>

    <div class="card stat-card animate-slide-up stagger-2">
        <div class="stat-label">Active Projects</div>
        <div class="stat-value" style="color: var(--color-primary);"><?php echo $activeProjects; ?></div>
        <div class="stat-change" style="background: var(--color-primary-subtle); color: var(--color-primary-dark);">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
            In progress
        </div>
    </div>

    <div class="card stat-card animate-slide-up stagger-3">
        <div class="stat-label">Contract Value</div>
        <div class="stat-value"><?php echo formatMoney($totalContractValue); ?></div>
        <div class="stat-change" style="background: var(--color-info-bg); color: var(--color-info);">
            Total value
        </div>
    </div>

    <div class="card stat-card animate-slide-up stagger-4">
        <div class="stat-label">Pending Payments</div>
        <div class="stat-value" style="color: var(--color-accent);"><?php echo formatMoney($pendingPayments); ?></div>
        <div class="stat-change" style="background: var(--color-warning-bg); color: var(--color-accent-dark);">
            Due amount
        </div>
    </div>
</div>

<!-- Projects Section -->
<div class="card mb-xl">
    <div class="card-header">
        <h2 class="heading-4">Your Projects</h2>
        <a href="<?php echo SITE_URL; ?>/projects.php" class="btn btn-secondary btn-sm">View All</a>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($projects)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                </div>
                <h4 class="empty-state-title">No Projects Yet</h4>
                <p class="empty-state-text">Your projects will appear here once they are created.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-2" style="padding: var(--space-lg); gap: var(--space-lg);">
                <?php foreach (array_slice($projects, 0, 4) as $project):
                    $progress = $project['totalMilestones'] > 0
                        ? round(($project['completedMilestones'] / $project['totalMilestones']) * 100)
                        : 0;
                ?>
                    <div class="card project-card card-slice">
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
                            </div>
                        </div>
                        <div class="card-footer" style="padding: var(--space-md) var(--space-lg);">
                            <a href="<?php echo SITE_URL; ?>/project-detail.php?id=<?php echo $project['projectID']; ?>" class="btn btn-primary btn-sm" style="width: 100%;">
                                View Details
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M5 12h14M12 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Links -->
<div class="grid grid-3">
    <a href="<?php echo SITE_URL; ?>/quotations.php" class="card p-lg" style="text-decoration: none; transition: all 0.2s;">
        <div class="flex items-center gap-md mb-md">
            <div style="width: 48px; height: 48px; background: var(--color-primary-subtle); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
            </div>
            <div>
                <h3 class="heading-4" style="margin-bottom: 4px;">Quotations</h3>
                <p class="text-sm text-secondary">View and approve quotes</p>
            </div>
        </div>
    </a>

    <a href="<?php echo SITE_URL; ?>/invoices.php" class="card p-lg" style="text-decoration: none; transition: all 0.2s;">
        <div class="flex items-center gap-md mb-md">
            <div style="width: 48px; height: 48px; background: var(--color-info-bg); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-info)" stroke-width="2">
                    <path d="M9 17h6m-6-4h6M9 9h6M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h3 class="heading-4" style="margin-bottom: 4px;">Invoices</h3>
                <p class="text-sm text-secondary">Download your invoices</p>
            </div>
        </div>
    </a>

    <a href="<?php echo SITE_URL; ?>/payments.php" class="card p-lg" style="text-decoration: none; transition: all 0.2s;">
        <div class="flex items-center gap-md mb-md">
            <div style="width: 48px; height: 48px; background: var(--color-success-bg); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-success)" stroke-width="2">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                </svg>
            </div>
            <div>
                <h3 class="heading-4" style="margin-bottom: 4px;">Payments</h3>
                <p class="text-sm text-secondary">Track payment status</p>
            </div>
        </div>
    </a>
</div>

<?php renderFooter(); ?>
