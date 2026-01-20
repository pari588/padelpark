<?php
/**
 * Sky Padel Client Portal - Payments
 * Premium Athletic Modernism Design
 */
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/layout.php';
requireLogin();

$email = getClientEmail();
$db = getDB();
$prefix = DB_PREFIX;

// Get all payments
$stmt = $db->prepare("
    SELECT pay.*, p.projectNo, p.projectName
    FROM {$prefix}sky_padel_payment pay
    INNER JOIN {$prefix}sky_padel_project p ON pay.projectID = p.projectID
    INNER JOIN {$prefix}sky_padel_lead l ON p.leadID = l.leadID
    WHERE l.clientEmail = ? AND pay.status = 1
    ORDER BY pay.dueDate ASC
");
$stmt->bind_param("s", $email);
$stmt->execute();
$payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate totals
$totalDue = 0;
$totalPaid = 0;
$totalPending = 0;

foreach ($payments as $pay) {
    $totalDue += floatval($pay['dueAmount'] ?? 0);
    $totalPaid += floatval($pay['paidAmount'] ?? 0);
    if (in_array($pay['paymentStatus'], ['Pending', 'Partial', 'Overdue'])) {
        $totalPending += floatval($pay['balanceAmount'] ?? $pay['dueAmount'] ?? 0);
    }
}

logActivity($email, 'ViewPayment', 'PaymentList', 0);

renderHeader('Payments');
?>

<!-- Summary Cards -->
<div class="grid grid-3 mb-xl">
    <div class="card stat-card">
        <div class="stat-label">Total Due</div>
        <div class="stat-value"><?php echo formatMoney($totalDue); ?></div>
    </div>
    <div class="card stat-card">
        <div class="stat-label">Total Paid</div>
        <div class="stat-value" style="color: var(--color-success);"><?php echo formatMoney($totalPaid); ?></div>
    </div>
    <div class="card stat-card">
        <div class="stat-label">Pending Amount</div>
        <div class="stat-value" style="color: var(--color-accent);"><?php echo formatMoney($totalPending); ?></div>
    </div>
</div>

<?php if (empty($payments)): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                </svg>
            </div>
            <h4 class="empty-state-title">No Payments Yet</h4>
            <p class="empty-state-text">Your payment schedule will appear here once created.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-header">
            <h2 class="heading-4">Payment Schedule</h2>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Milestone</th>
                        <th>Project</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment):
                        $isOverdue = strtotime($payment['dueDate']) < time() && $payment['paymentStatus'] !== 'Paid';
                    ?>
                        <tr>
                            <td>
                                <span class="font-semibold"><?php echo e($payment['paymentMilestone'] ?? 'Payment'); ?></span>
                            </td>
                            <td>
                                <span class="font-medium"><?php echo e($payment['projectName']); ?></span>
                                <br><span class="text-xs text-muted"><?php echo e($payment['projectNo']); ?></span>
                            </td>
                            <td>
                                <span class="<?php echo $isOverdue ? 'text-error font-semibold' : ''; ?>">
                                    <?php echo date('M d, Y', strtotime($payment['dueDate'])); ?>
                                </span>
                                <?php if ($isOverdue): ?>
                                    <br><span class="text-xs text-error">Overdue</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="amount amount-sm"><?php echo formatMoney($payment['dueAmount'] ?? 0); ?></span>
                            </td>
                            <td>
                                <span class="font-medium" style="color: var(--color-success);">
                                    <?php echo formatMoney($payment['paidAmount'] ?? 0); ?>
                                </span>
                            </td>
                            <td>
                                <span class="font-semibold" style="color: var(--color-accent);">
                                    <?php echo formatMoney($payment['balanceAmount'] ?? 0); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo getStatusBadgeClass($payment['paymentStatus']); ?>">
                                    <?php echo e($payment['paymentStatus']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<style>
    .text-error { color: var(--color-error); }
</style>

<?php renderFooter(); ?>
