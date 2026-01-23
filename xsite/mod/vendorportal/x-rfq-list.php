<?php
/**
 * Vendor Portal - RFQ List
 */

$pageTitle = 'Available RFQs';
include("x-header.php");

$status = $_GET['status'] ?? 'all';
$rfqs = vpGetAvailableRFQs($status, 50);
?>

<div class="page-header">
    <div class="page-header-left">
        <h1>Available RFQs</h1>
        <p>View and respond to Request for Quotations</p>
    </div>
</div>

<!-- Filter Tabs -->
<div class="card" style="margin-bottom: var(--space-lg);">
    <div class="card-body" style="padding: var(--space-md) var(--space-lg);">
        <div style="display: flex; gap: var(--space-sm);">
            <a href="<?php echo VP_BASEURL; ?>/vendorportal/rfq-list" class="btn <?php echo $status === 'all' ? 'btn-primary' : 'btn-ghost'; ?>">
                All RFQs
            </a>
            <a href="<?php echo VP_BASEURL; ?>/vendorportal/rfq-list?status=Published" class="btn <?php echo $status === 'Published' ? 'btn-primary' : 'btn-ghost'; ?>">
                Open
            </a>
            <a href="<?php echo VP_BASEURL; ?>/vendorportal/rfq-list?status=Closed" class="btn <?php echo $status === 'Closed' ? 'btn-primary' : 'btn-ghost'; ?>">
                Closed
            </a>
        </div>
    </div>
</div>

<?php if (count($rfqs) > 0): ?>
    <div class="rfq-grid animate-stagger">
        <?php foreach ($rfqs as $rfq): ?>
            <?php
            $isOverdue = strtotime($rfq['submissionDeadline']) < time();
            $isOpen = $rfq['rfqStatus'] === 'Published' && !$isOverdue;
            ?>
            <div class="rfq-card">
                <div class="rfq-card-header">
                    <div class="rfq-card-number"><?php echo vpClean($rfq['rfqNumber']); ?></div>
                    <h3 class="rfq-card-title"><?php echo vpClean($rfq['title']); ?></h3>
                </div>
                <div class="rfq-card-body">
                    <div class="rfq-card-meta">
                        <div class="rfq-meta-item">
                            <span class="rfq-meta-label">Type</span>
                            <span class="rfq-meta-value"><?php echo vpClean($rfq['rfqType']); ?></span>
                        </div>
                        <div class="rfq-meta-item">
                            <span class="rfq-meta-label">Items</span>
                            <span class="rfq-meta-value"><?php echo $rfq['itemCount']; ?> items</span>
                        </div>
                        <div class="rfq-meta-item">
                            <span class="rfq-meta-label">Deadline</span>
                            <span class="rfq-meta-value deadline <?php echo $isOverdue ? 'overdue' : ''; ?>">
                                <?php echo vpFormatDate($rfq['submissionDeadline'], 'd M Y, H:i'); ?>
                                <?php if ($isOverdue): ?>
                                    <br><small>(Expired)</small>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="rfq-meta-item">
                            <span class="rfq-meta-label">Category</span>
                            <span class="rfq-meta-value"><?php echo vpClean($rfq['categoryName'] ?? 'General'); ?></span>
                        </div>
                    </div>

                    <?php if ($rfq['description']): ?>
                        <p class="text-muted text-sm mb-0" style="line-height: 1.5;">
                            <?php echo vpClean(substr($rfq['description'], 0, 120)); ?>
                            <?php echo strlen($rfq['description']) > 120 ? '...' : ''; ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="rfq-card-footer">
                    <?php echo vpGetStatusBadge($rfq['rfqStatus']); ?>

                    <?php if ($rfq['myQuoteID']): ?>
                        <a href="<?php echo VP_BASEURL; ?>/vendorportal/quote-view?id=<?php echo $rfq['myQuoteID']; ?>" class="btn btn-outline btn-sm">
                            <i class="fas fa-eye"></i>
                            View My Quote
                        </a>
                    <?php elseif ($isOpen): ?>
                        <a href="<?php echo VP_BASEURL; ?>/vendorportal/quote-submit?rfqID=<?php echo $rfq['rfqID']; ?>" class="btn btn-accent btn-sm">
                            <i class="fas fa-paper-plane"></i>
                            Submit Quote
                        </a>
                    <?php else: ?>
                        <span class="text-muted text-sm">
                            <i class="fas fa-lock"></i> Closed
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-file-invoice"></i>
            </div>
            <h3>No RFQs Available</h3>
            <p>There are currently no RFQs matching your criteria. Check back later for new opportunities.</p>
            <a href="<?php echo VP_BASEURL; ?>/vendorportal/dashboard" class="btn btn-primary">
                Back to Dashboard
            </a>
        </div>
    </div>
<?php endif; ?>

<?php include("x-footer.php"); ?>
