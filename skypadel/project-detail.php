<?php
/**
 * Sky Padel Client Portal - Project Detail
 * Premium Athletic Modernism Design
 */
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/layout.php';
requireLogin();

$email = getClientEmail();
$db = getDB();
$prefix = DB_PREFIX;

$projectID = intval($_GET['id'] ?? 0);

// Get project with verification
$stmt = $db->prepare("
    SELECT p.*, l.clientName, l.clientEmail, l.siteAddress, l.siteCity, l.siteState
    FROM {$prefix}sky_padel_project p
    LEFT JOIN {$prefix}sky_padel_lead l ON p.leadID = l.leadID
    WHERE p.projectID = ? AND l.clientEmail = ? AND p.status = 1
");
$stmt->bind_param("is", $projectID, $email);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();

if (!$project) {
    redirect(SITE_URL . '/projects.php');
}

// Get milestones
$stmt = $db->prepare("
    SELECT * FROM {$prefix}sky_padel_milestone
    WHERE projectID = ? AND status = 1
    ORDER BY milestoneOrder ASC, created ASC
");
$stmt->bind_param("i", $projectID);
$stmt->execute();
$milestones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get payments
$stmt = $db->prepare("
    SELECT * FROM {$prefix}sky_padel_payment
    WHERE projectID = ? AND status = 1
    ORDER BY dueDate ASC
");
$stmt->bind_param("i", $projectID);
$stmt->execute();
$payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate progress
$totalMilestones = count($milestones);
$completedMilestones = count(array_filter($milestones, fn($m) => $m['milestoneStatus'] === 'Completed'));
$progress = $totalMilestones > 0 ? round(($completedMilestones / $totalMilestones) * 100) : 0;

// Payment summary
$totalPaymentDue = array_sum(array_column($payments, 'dueAmount'));
$totalPaid = array_sum(array_map(fn($p) => $p['paidAmount'] ?? 0, $payments));

logActivity($email, 'ViewProject', 'Project', $projectID);

renderHeader($project['projectName']);
?>

<style>
/* Project Detail Specific Styles */
.project-hero {
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
    border-radius: var(--radius-xl);
    color: white;
    padding: var(--space-2xl);
    position: relative;
    overflow: hidden;
    margin-bottom: var(--space-xl);
}

.project-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background:
        linear-gradient(90deg, transparent 49.5%, rgba(255,255,255,0.1) 49.5%, rgba(255,255,255,0.1) 50.5%, transparent 50.5%),
        linear-gradient(0deg, transparent 49.5%, rgba(255,255,255,0.1) 49.5%, rgba(255,255,255,0.1) 50.5%, transparent 50.5%);
    background-size: 80px 80px;
    pointer-events: none;
}

.project-hero::after {
    content: '';
    position: absolute;
    bottom: -50%;
    right: -20%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
    pointer-events: none;
}

.project-hero-content {
    position: relative;
    z-index: 1;
}

.project-hero-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--space-xl);
}

.project-title-group {
    flex: 1;
}

.project-hero .project-number {
    font-size: 0.85rem;
    letter-spacing: 0.15em;
    opacity: 0.8;
    margin-bottom: var(--space-sm);
}

.project-hero .project-name {
    font-family: var(--font-display);
    font-size: 2.5rem;
    letter-spacing: 0.03em;
    margin-bottom: var(--space-md);
}

.project-hero .project-location {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    font-size: 1rem;
    opacity: 0.9;
}

.project-hero .badge {
    font-size: 0.85rem;
    padding: 10px 20px;
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.3);
    backdrop-filter: blur(10px);
}

.hero-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--space-lg);
    padding-top: var(--space-xl);
    border-top: 1px solid rgba(255,255,255,0.2);
}

.hero-stat {
    text-align: center;
}

.hero-stat-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    opacity: 0.7;
    margin-bottom: var(--space-sm);
}

.hero-stat-value {
    font-family: var(--font-display);
    font-size: 1.5rem;
    letter-spacing: 0.02em;
}

/* Progress Ring */
.progress-ring-container {
    text-align: center;
}

.progress-ring {
    width: 120px;
    height: 120px;
    transform: rotate(-90deg);
}

.progress-ring-bg {
    fill: none;
    stroke: rgba(255,255,255,0.2);
    stroke-width: 8;
}

.progress-ring-fill {
    fill: none;
    stroke: var(--color-accent);
    stroke-width: 8;
    stroke-linecap: round;
    stroke-dasharray: 314;
    stroke-dashoffset: calc(314 - (314 * var(--progress)) / 100);
    transition: stroke-dashoffset 1s ease;
}

.progress-ring-text {
    font-family: var(--font-display);
    font-size: 2rem;
    color: white;
    margin-top: -90px;
    position: relative;
}

.progress-ring-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    opacity: 0.7;
    margin-top: var(--space-sm);
}

/* Section Headers */
.section-header {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    margin-bottom: var(--space-lg);
}

.section-icon {
    width: 48px;
    height: 48px;
    background: var(--color-primary-subtle);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-primary);
}

.section-title {
    font-family: var(--font-display);
    font-size: 1.5rem;
    letter-spacing: 0.02em;
    color: var(--color-text);
}

/* Horizontal Timeline */
.milestone-track {
    margin-bottom: var(--space-xl);
}

.milestone-progress-bar {
    display: flex;
    align-items: center;
    gap: 4px;
    background: var(--color-bg-warm);
    border-radius: 20px;
    padding: 6px;
    margin-bottom: var(--space-lg);
}

.milestone-dot {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--color-surface);
    border: 3px solid var(--color-border);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    z-index: 2;
    transition: all var(--transition-base);
}

.milestone-dot.completed {
    background: var(--color-success);
    border-color: var(--color-success);
    color: white;
}

.milestone-dot.in-progress {
    background: var(--color-primary);
    border-color: var(--color-primary);
    color: white;
    animation: pulse 2s infinite;
}

.milestone-connector {
    flex: 1;
    height: 4px;
    background: var(--color-border);
    border-radius: 2px;
}

.milestone-connector.completed {
    background: var(--color-success);
}

.milestone-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: var(--space-md);
}

.milestone-card {
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    padding: var(--space-lg);
    transition: all var(--transition-base);
    position: relative;
    overflow: hidden;
}

.milestone-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: var(--color-border);
}

.milestone-card.completed::before {
    background: var(--color-success);
}

.milestone-card.in-progress::before {
    background: var(--color-primary);
}

.milestone-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.milestone-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--space-md);
}

.milestone-order {
    font-family: var(--font-display);
    font-size: 0.85rem;
    color: var(--color-text-muted);
    letter-spacing: 0.1em;
}

.milestone-name {
    font-weight: 600;
    font-size: 1rem;
    color: var(--color-text);
    margin-top: var(--space-xs);
}

.milestone-description {
    font-size: 0.9rem;
    color: var(--color-text-secondary);
    margin-bottom: var(--space-md);
    line-height: 1.5;
}

.milestone-date {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    font-size: 0.85rem;
    color: var(--color-text-muted);
}

/* Payment Cards */
.payment-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--space-md);
}

.payment-card {
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    padding: var(--space-lg);
    transition: all var(--transition-base);
    position: relative;
}

.payment-card::after {
    content: '';
    position: absolute;
    left: var(--space-lg);
    right: var(--space-lg);
    bottom: 0;
    height: 4px;
    border-radius: 0 0 var(--radius-lg) var(--radius-lg);
    background: var(--color-border);
}

.payment-card.paid::after {
    background: var(--color-success);
}

.payment-card.pending::after {
    background: var(--color-accent);
}

.payment-card.overdue::after {
    background: var(--color-error);
}

.payment-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.payment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--space-lg);
}

.payment-milestone-name {
    font-weight: 600;
    color: var(--color-text);
    margin-bottom: var(--space-xs);
}

.payment-due-date {
    font-size: 0.85rem;
    color: var(--color-text-muted);
    display: flex;
    align-items: center;
    gap: var(--space-xs);
}

.payment-amount {
    text-align: right;
}

.payment-amount-value {
    font-family: var(--font-display);
    font-size: 1.5rem;
    color: var(--color-text);
    letter-spacing: 0.02em;
}

.payment-summary-bar {
    display: flex;
    gap: var(--space-lg);
    padding: var(--space-md);
    background: var(--color-bg-warm);
    border-radius: var(--radius-md);
    margin-bottom: var(--space-xl);
}

.payment-summary-item {
    flex: 1;
    text-align: center;
    padding: var(--space-md);
}

.payment-summary-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--color-text-muted);
    margin-bottom: var(--space-sm);
}

.payment-summary-value {
    font-family: var(--font-display);
    font-size: 1.25rem;
    color: var(--color-text);
}

.payment-summary-value.success { color: var(--color-success); }
.payment-summary-value.accent { color: var(--color-accent); }

/* Back Navigation */
.back-nav {
    margin-bottom: var(--space-lg);
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: var(--space-sm);
    color: var(--color-text-secondary);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
    transition: color var(--transition-fast);
}

.back-link:hover {
    color: var(--color-primary);
}

@media (max-width: 768px) {
    .hero-stats {
        grid-template-columns: repeat(2, 1fr);
    }

    .project-hero .project-name {
        font-size: 1.75rem;
    }

    .project-hero-top {
        flex-direction: column;
        gap: var(--space-lg);
    }

    .milestone-progress-bar {
        display: none;
    }
}
</style>

<!-- Back Navigation -->
<div class="back-nav">
    <a href="<?php echo SITE_URL; ?>/projects.php" class="back-link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back to Projects
    </a>
</div>

<!-- Project Hero -->
<div class="project-hero">
    <div class="project-hero-content">
        <div class="project-hero-top">
            <div class="project-title-group">
                <div class="project-number"><?php echo e($project['projectNo']); ?></div>
                <h1 class="project-name"><?php echo e($project['projectName']); ?></h1>
                <div class="project-location">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    <?php echo e(implode(', ', array_filter([$project['siteCity'], $project['siteState']]))); ?>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: var(--space-xl);">
                <span class="badge">
                    <?php echo e($project['projectStatus']); ?>
                </span>

                <!-- Progress Ring -->
                <div class="progress-ring-container">
                    <svg class="progress-ring" viewBox="0 0 120 120" style="--progress: <?php echo $progress; ?>">
                        <circle class="progress-ring-bg" cx="60" cy="60" r="50"/>
                        <circle class="progress-ring-fill" cx="60" cy="60" r="50"/>
                    </svg>
                    <div class="progress-ring-text"><?php echo $progress; ?>%</div>
                    <div class="progress-ring-label">Complete</div>
                </div>
            </div>
        </div>

        <div class="hero-stats">
            <div class="hero-stat">
                <div class="hero-stat-label">Contract Value</div>
                <div class="hero-stat-value"><?php echo formatMoney($project['contractAmount']); ?></div>
            </div>
            <div class="hero-stat">
                <div class="hero-stat-label">Start Date</div>
                <div class="hero-stat-value"><?php echo $project['startDate'] ? date('M d, Y', strtotime($project['startDate'])) : 'TBD'; ?></div>
            </div>
            <div class="hero-stat">
                <div class="hero-stat-label">Expected Completion</div>
                <div class="hero-stat-value"><?php echo $project['expectedEndDate'] ? date('M d, Y', strtotime($project['expectedEndDate'])) : 'TBD'; ?></div>
            </div>
            <div class="hero-stat">
                <div class="hero-stat-label">Milestones</div>
                <div class="hero-stat-value"><?php echo $completedMilestones; ?> / <?php echo $totalMilestones; ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Milestones Section -->
<div class="section-header">
    <div class="section-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2L2 7l10 5 10-5-10-5z"/>
            <path d="M2 17l10 5 10-5"/>
            <path d="M2 12l10 5 10-5"/>
        </svg>
    </div>
    <h2 class="section-title">Project Milestones</h2>
</div>

<?php if (empty($milestones)): ?>
    <div class="card mb-xl">
        <div class="empty-state" style="padding: var(--space-xl);">
            <p class="text-muted">No milestones defined yet.</p>
        </div>
    </div>
<?php else: ?>
    <!-- Milestone Progress Track -->
    <div class="milestone-track">
        <div class="milestone-progress-bar">
            <?php foreach ($milestones as $index => $milestone):
                $isCompleted = $milestone['milestoneStatus'] === 'Completed';
                $isInProgress = $milestone['milestoneStatus'] === 'InProgress';
                $prevCompleted = $index > 0 && $milestones[$index - 1]['milestoneStatus'] === 'Completed';
            ?>
                <?php if ($index > 0): ?>
                    <div class="milestone-connector <?php echo $prevCompleted ? 'completed' : ''; ?>"></div>
                <?php endif; ?>
                <div class="milestone-dot <?php echo $isCompleted ? 'completed' : ($isInProgress ? 'in-progress' : ''); ?>">
                    <?php if ($isCompleted): ?>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    <?php elseif ($isInProgress): ?>
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
                            <circle cx="12" cy="12" r="6"/>
                        </svg>
                    <?php else: ?>
                        <span style="font-family: var(--font-display); font-size: 0.8rem; color: var(--color-text-muted);"><?php echo $index + 1; ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="milestone-cards">
            <?php foreach ($milestones as $index => $milestone):
                $statusClass = match($milestone['milestoneStatus']) {
                    'Completed' => 'completed',
                    'InProgress' => 'in-progress',
                    default => 'pending'
                };
            ?>
                <div class="milestone-card <?php echo $statusClass; ?>">
                    <div class="milestone-card-header">
                        <div>
                            <div class="milestone-order">STEP <?php echo $index + 1; ?></div>
                            <div class="milestone-name"><?php echo e($milestone['milestoneName']); ?></div>
                        </div>
                        <span class="badge <?php echo getStatusBadgeClass($milestone['milestoneStatus']); ?>">
                            <?php echo e($milestone['milestoneStatus']); ?>
                        </span>
                    </div>
                    <?php if (!empty($milestone['milestoneDescription'])): ?>
                        <p class="milestone-description"><?php echo e($milestone['milestoneDescription']); ?></p>
                    <?php endif; ?>
                    <div class="milestone-date">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        <?php if ($milestone['completedDate']): ?>
                            Completed: <?php echo date('M d, Y', strtotime($milestone['completedDate'])); ?>
                        <?php elseif (!empty($milestone['dueDate'])): ?>
                            Due: <?php echo date('M d, Y', strtotime($milestone['dueDate'])); ?>
                        <?php else: ?>
                            Date TBD
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Payments Section -->
<div class="section-header">
    <div class="section-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
            <line x1="1" y1="10" x2="23" y2="10"/>
        </svg>
    </div>
    <h2 class="section-title">Payment Schedule</h2>
</div>

<?php if (empty($payments)): ?>
    <div class="card">
        <div class="empty-state" style="padding: var(--space-xl);">
            <p class="text-muted">No payment schedule defined yet.</p>
        </div>
    </div>
<?php else: ?>
    <!-- Payment Summary -->
    <div class="payment-summary-bar">
        <div class="payment-summary-item">
            <div class="payment-summary-label">Total Contract</div>
            <div class="payment-summary-value"><?php echo formatMoney($totalPaymentDue); ?></div>
        </div>
        <div class="payment-summary-item">
            <div class="payment-summary-label">Amount Paid</div>
            <div class="payment-summary-value success"><?php echo formatMoney($totalPaid); ?></div>
        </div>
        <div class="payment-summary-item">
            <div class="payment-summary-label">Balance Due</div>
            <div class="payment-summary-value accent"><?php echo formatMoney($totalPaymentDue - $totalPaid); ?></div>
        </div>
    </div>

    <div class="payment-grid">
        <?php foreach ($payments as $payment):
            $isOverdue = strtotime($payment['dueDate']) < time() && $payment['paymentStatus'] !== 'Paid';
            $statusClass = $payment['paymentStatus'] === 'Paid' ? 'paid' : ($isOverdue ? 'overdue' : 'pending');
        ?>
            <div class="payment-card <?php echo $statusClass; ?>">
                <div class="payment-header">
                    <div>
                        <div class="payment-milestone-name"><?php echo e($payment['paymentMilestone'] ?? 'Payment'); ?></div>
                        <div class="payment-due-date">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            Due: <?php echo date('M d, Y', strtotime($payment['dueDate'])); ?>
                            <?php if ($isOverdue): ?>
                                <span class="badge badge-error" style="margin-left: var(--space-sm);">Overdue</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="payment-amount">
                        <div class="payment-amount-value"><?php echo formatMoney($payment['dueAmount']); ?></div>
                        <span class="badge <?php echo getStatusBadgeClass($payment['paymentStatus']); ?>">
                            <?php echo e($payment['paymentStatus']); ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php renderFooter(); ?>
