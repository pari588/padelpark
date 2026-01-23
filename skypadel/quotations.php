<?php
/**
 * Sky Padel Client Portal - Quotations List
 * Premium Athletic Modernism Design
 */
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/layout.php';
requireLogin();

$email = getClientEmail();
$db = getDB();
$prefix = DB_PREFIX;

// Get all quotations for this client
$stmt = $db->prepare("
    SELECT q.*, l.clientName, CONCAT_WS(', ', l.siteCity, l.siteState) as projectLocation
    FROM {$prefix}sky_padel_quotation q
    INNER JOIN {$prefix}sky_padel_lead l ON q.leadID = l.leadID
    WHERE l.clientEmail = ? AND q.status = 1
    ORDER BY q.created DESC
");
$stmt->bind_param("s", $email);
$stmt->execute();
$quotations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

logActivity($email, 'ViewQuotation', 'QuotationList', 0);

renderHeader('Quotations');
?>

<?php if (empty($quotations)): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
            </div>
            <h4 class="empty-state-title">No Quotations Yet</h4>
            <p class="empty-state-text">Your quotations will appear here once they are created.</p>
        </div>
    </div>
<?php else: ?>
    <div class="grid grid-2">
        <?php foreach ($quotations as $quote): ?>
            <div class="card finance-card <?php echo $quote['quotationStatus'] === 'Approved' ? 'paid' : ($quote['quotationStatus'] === 'Sent' ? 'pending' : ''); ?>">
                <div class="card-body">
                    <div class="flex justify-between items-center mb-lg">
                        <div>
                            <p class="text-xs text-muted" style="text-transform: uppercase; letter-spacing: 0.1em;"><?php echo e($quote['quotationNo']); ?></p>
                            <h3 class="heading-4 mt-sm"><?php echo e($quote['quotationTitle'] ?? 'Padel Court Installation'); ?></h3>
                        </div>
                        <span class="badge <?php echo getStatusBadgeClass($quote['quotationStatus']); ?>">
                            <?php echo e($quote['quotationStatus']); ?>
                        </span>
                    </div>

                    <div class="flex justify-between items-center mb-lg" style="padding: var(--space-md); background: var(--color-bg-warm); border-radius: var(--radius-md);">
                        <div>
                            <p class="text-xs text-muted">Total Amount</p>
                            <p class="amount"><?php echo formatMoney($quote['totalAmount']); ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-muted">Date</p>
                            <p class="font-medium"><?php echo date('M d, Y', strtotime($quote['quotationDate'])); ?></p>
                        </div>
                    </div>

                    <?php if ($quote['projectLocation']): ?>
                    <div class="flex items-center gap-sm text-sm text-secondary mb-lg">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <?php echo e($quote['projectLocation']); ?>
                    </div>
                    <?php endif; ?>

                    <a href="<?php echo SITE_URL; ?>/quotation-view.php?id=<?php echo $quote['quotationID']; ?>" class="btn btn-primary" style="width: 100%;">
                        <?php if (in_array($quote['quotationStatus'], ['Sent', 'Client Reviewing'])): ?>
                            Review & Approve
                        <?php else: ?>
                            View Details
                        <?php endif; ?>
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
