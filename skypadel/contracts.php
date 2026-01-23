<?php
/**
 * Sky Padel Client Portal - Contracts List
 * Premium Athletic Modernism Design
 */
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/layout.php';
requireLogin();

$email = getClientEmail();
$db = getDB();
$prefix = DB_PREFIX;

// Get all contracts for this client
$stmt = $db->prepare("
    SELECT c.*, q.quotationNo, l.clientName,
           CONCAT_WS(', ', l.siteCity, l.siteState) as projectLocation
    FROM {$prefix}sky_padel_contract c
    INNER JOIN {$prefix}sky_padel_quotation q ON c.quotationID = q.quotationID
    INNER JOIN {$prefix}sky_padel_lead l ON c.leadID = l.leadID
    WHERE l.clientEmail = ? AND c.status = 1
    ORDER BY c.created DESC
");
$stmt->bind_param("s", $email);
$stmt->execute();
$contracts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

logActivity($email, 'ViewContracts', 'ContractList', 0);

renderHeader('Contracts');
?>

<?php if (empty($contracts)): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
            </div>
            <h4 class="empty-state-title">No Contracts Yet</h4>
            <p class="empty-state-text">Your contracts will appear here once a quotation is approved.</p>
            <a href="<?php echo SITE_URL; ?>/quotations.php" class="btn btn-primary mt-lg">
                View Quotations
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="grid grid-2">
        <?php foreach ($contracts as $contract):
            $isPending = $contract['contractStatus'] === 'Pending Signature';
            $isSigned = $contract['contractStatus'] === 'Signed';
        ?>
            <div class="card contract-card <?php echo $isSigned ? 'signed' : ($isPending ? 'pending' : ''); ?>">
                <div class="card-body">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-lg">
                        <div>
                            <p class="text-xs text-muted" style="text-transform: uppercase; letter-spacing: 0.1em;"><?php echo e($contract['contractNo']); ?></p>
                            <h3 class="heading-4 mt-sm">Contract Agreement</h3>
                        </div>
                        <span class="badge <?php echo getStatusBadgeClass($contract['contractStatus']); ?>">
                            <?php echo e($contract['contractStatus']); ?>
                        </span>
                    </div>

                    <!-- Amount & Date -->
                    <div class="flex justify-between items-center mb-lg" style="padding: var(--space-md); background: var(--color-bg); border-radius: var(--radius-md);">
                        <div>
                            <p class="text-xs text-muted">Contract Value</p>
                            <p class="amount"><?php echo formatMoney($contract['contractAmount']); ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-muted">Date</p>
                            <p class="font-medium"><?php echo date('M d, Y', strtotime($contract['contractDate'])); ?></p>
                        </div>
                    </div>

                    <!-- Quotation Reference -->
                    <div class="flex items-center gap-sm text-sm text-secondary mb-md">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        Quotation: <?php echo e($contract['quotationNo']); ?>
                    </div>

                    <?php if ($contract['projectLocation']): ?>
                    <div class="flex items-center gap-sm text-sm text-secondary mb-lg">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <?php echo e($contract['projectLocation']); ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($isSigned && $contract['signedAt']): ?>
                    <div class="flex items-center gap-sm text-sm mb-lg" style="color: var(--color-success);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        Signed on <?php echo date('M d, Y', strtotime($contract['signedAt'])); ?>
                    </div>
                    <?php endif; ?>

                    <a href="<?php echo SITE_URL; ?>/contract-sign.php?id=<?php echo $contract['contractID']; ?>" class="btn <?php echo $isPending ? 'btn-accent' : 'btn-primary'; ?>" style="width: 100%;">
                        <?php if ($isPending): ?>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                            Sign Contract
                        <?php else: ?>
                            View Contract
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<style>
.contract-card {
    border-left: 4px solid var(--color-primary);
    transition: all var(--transition-base);
}

.contract-card.signed {
    border-left-color: var(--color-success);
}

.contract-card.pending {
    border-left-color: var(--color-accent);
}

.contract-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-xl);
}

.btn-accent {
    background: linear-gradient(135deg, var(--color-accent) 0%, var(--color-fire) 100%);
    color: var(--color-text);
    box-shadow: 0 4px 14px var(--color-accent-glow);
    animation: pulse-glow 2s infinite;
}

@keyframes pulse-glow {
    0%, 100% { box-shadow: 0 4px 14px var(--color-accent-glow); }
    50% { box-shadow: 0 4px 24px var(--color-accent-glow), 0 0 40px var(--color-accent-glow); }
}
</style>

<?php renderFooter(); ?>
