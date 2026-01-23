<?php
/**
 * Vendor Portal - Dashboard
 */

$pageTitle = 'Dashboard';
include("x-header.php");

$stats = vpGetDashboardStats();
$recentRFQs = vpGetAvailableRFQs('Published', 5);
$myQuotes = vpGetMyQuotes(5);
?>

<div class="page-header">
    <div class="page-header-left">
        <h1>Welcome back, <?php echo vpClean($vendor['legalName']); ?></h1>
        <p>Here's an overview of your vendor portal activity</p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo VP_BASEURL; ?>/vendorportal/rfq-list" class="btn btn-primary">
            <i class="fas fa-file-invoice"></i>
            View RFQs
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid animate-stagger">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-header">
            <div class="stat-card-label">Available RFQs</div>
            <div class="stat-card-icon">
                <i class="fas fa-file-invoice"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $stats['availableRFQs']; ?></div>
        <div class="stat-card-change positive">
            <i class="fas fa-arrow-up"></i>
            Open for quotation
        </div>
    </div>

    <div class="stat-card stat-card-accent">
        <div class="stat-card-header">
            <div class="stat-card-label">Submitted Quotes</div>
            <div class="stat-card-icon">
                <i class="fas fa-paper-plane"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $stats['submittedQuotes']; ?></div>
        <div class="stat-card-change">
            Total submissions
        </div>
    </div>

    <div class="stat-card stat-card-warning">
        <div class="stat-card-header">
            <div class="stat-card-label">Draft Quotes</div>
            <div class="stat-card-icon">
                <i class="fas fa-edit"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $stats['pendingQuotes']; ?></div>
        <div class="stat-card-change">
            Pending submission
        </div>
    </div>

    <div class="stat-card stat-card-success">
        <div class="stat-card-header">
            <div class="stat-card-label">Awarded Orders</div>
            <div class="stat-card-icon">
                <i class="fas fa-trophy"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $stats['awardedOrders']; ?></div>
        <div class="stat-card-change positive">
            <i class="fas fa-check"></i>
            Won quotes
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-xl);">
    <!-- Recent RFQs -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-invoice text-primary"></i>
                Latest RFQs
            </h3>
            <a href="<?php echo VP_BASEURL; ?>/vendorportal/rfq-list" class="btn btn-ghost btn-sm">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (count($recentRFQs) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>RFQ</th>
                            <th>Deadline</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentRFQs as $rfq): ?>
                            <tr>
                                <td>
                                    <div class="table-cell-main"><?php echo vpClean($rfq['title']); ?></div>
                                    <div class="table-cell-sub font-mono"><?php echo vpClean($rfq['rfqNumber']); ?></div>
                                </td>
                                <td>
                                    <?php
                                    $isOverdue = strtotime($rfq['submissionDeadline']) < time();
                                    $deadlineClass = $isOverdue ? 'text-danger' : '';
                                    ?>
                                    <span class="<?php echo $deadlineClass; ?>">
                                        <?php echo vpFormatDate($rfq['submissionDeadline'], 'd M Y'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($rfq['myQuoteID']): ?>
                                        <a href="<?php echo VP_BASEURL; ?>/vendorportal/quote-view?id=<?php echo $rfq['myQuoteID']; ?>" class="btn btn-outline btn-sm">
                                            View Quote
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo VP_BASEURL; ?>/vendorportal/quote-submit?rfqID=<?php echo $rfq['rfqID']; ?>" class="btn btn-accent btn-sm">
                                            Submit Quote
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h3>No RFQs Available</h3>
                    <p>There are currently no open RFQs for you to quote on.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- My Recent Quotes -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-alt text-accent"></i>
                My Recent Quotes
            </h3>
            <a href="<?php echo VP_BASEURL; ?>/vendorportal/quotes" class="btn btn-ghost btn-sm">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (count($myQuotes) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Quote</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($myQuotes as $quote): ?>
                            <tr>
                                <td>
                                    <div class="table-cell-main font-mono"><?php echo vpClean($quote['quoteNumber']); ?></div>
                                    <div class="table-cell-sub"><?php echo vpClean($quote['rfqTitle']); ?></div>
                                </td>
                                <td>
                                    <strong><?php echo vpFormatCurrency($quote['totalAmount']); ?></strong>
                                </td>
                                <td>
                                    <?php echo vpGetStatusBadge($quote['quoteStatus']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3>No Quotes Yet</h3>
                    <p>You haven't submitted any quotes yet. Check out available RFQs to get started.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card" style="margin-top: var(--space-xl);">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-bolt text-warning"></i>
            Quick Actions
        </h3>
    </div>
    <div class="card-body">
        <div style="display: flex; gap: var(--space-md); flex-wrap: wrap;">
            <a href="<?php echo VP_BASEURL; ?>/vendorportal/rfq-list" class="btn btn-outline">
                <i class="fas fa-search"></i>
                Browse RFQs
            </a>
            <a href="<?php echo VP_BASEURL; ?>/vendorportal/quotes" class="btn btn-outline">
                <i class="fas fa-list"></i>
                View My Quotes
            </a>
            <a href="<?php echo VP_BASEURL; ?>/vendorportal/profile" class="btn btn-outline">
                <i class="fas fa-building"></i>
                Update Profile
            </a>
            <a href="<?php echo VP_BASEURL; ?>/vendorportal/documents" class="btn btn-outline">
                <i class="fas fa-upload"></i>
                Upload Documents
            </a>
        </div>
    </div>
</div>

<?php include("x-footer.php"); ?>
