<?php
/**
 * Sky Padel Client Portal - Invoices List
 * Premium Athletic Modernism Design
 */
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/layout.php';
requireLogin();

$email = getClientEmail();
$db = getDB();
$prefix = DB_PREFIX;

// Get proforma invoices
$stmt = $db->prepare("
    SELECT pf.*, p.projectNo, p.projectName
    FROM {$prefix}sky_padel_proforma_invoice pf
    INNER JOIN {$prefix}sky_padel_project p ON pf.projectID = p.projectID
    INNER JOIN {$prefix}sky_padel_lead l ON p.leadID = l.leadID
    WHERE l.clientEmail = ? AND pf.status = 1
    ORDER BY pf.created DESC
");
$stmt->bind_param("s", $email);
$stmt->execute();
$invoices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

logActivity($email, 'ViewInvoice', 'InvoiceList', 0);

renderHeader('Invoices');
?>

<!-- Summary Cards -->
<div class="grid grid-3 mb-xl">
    <?php
    $totalInvoices = count($invoices);
    $paidInvoices = count(array_filter($invoices, fn($i) => ($i['invoiceStatus'] ?? '') === 'Paid'));
    $totalAmount = array_sum(array_column($invoices, 'totalAmount'));
    ?>
    <div class="card stat-card">
        <div class="stat-label">Total Invoices</div>
        <div class="stat-value"><?php echo $totalInvoices; ?></div>
    </div>
    <div class="card stat-card">
        <div class="stat-label">Paid Invoices</div>
        <div class="stat-value" style="color: var(--color-success);"><?php echo $paidInvoices; ?></div>
    </div>
    <div class="card stat-card">
        <div class="stat-label">Total Amount</div>
        <div class="stat-value"><?php echo formatMoney($totalAmount); ?></div>
    </div>
</div>

<?php if (empty($invoices)): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M9 17h6m-6-4h6M9 9h6M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <h4 class="empty-state-title">No Invoices Yet</h4>
            <p class="empty-state-text">Your invoices will appear here once they are generated.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-header">
            <h2 class="heading-4">All Invoices</h2>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice No.</th>
                        <th>Project</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td>
                                <span class="font-semibold"><?php echo e($invoice['proformaNo']); ?></span>
                                <br><span class="text-xs text-muted">Proforma Invoice</span>
                            </td>
                            <td>
                                <span class="font-medium"><?php echo e($invoice['projectName']); ?></span>
                                <br><span class="text-xs text-muted"><?php echo e($invoice['projectNo']); ?></span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($invoice['invoiceDate'])); ?></td>
                            <td>
                                <span class="amount amount-sm"><?php echo formatMoney($invoice['totalAmount']); ?></span>
                            </td>
                            <td>
                                <span class="badge <?php echo getStatusBadgeClass($invoice['invoiceStatus'] ?? 'Pending'); ?>">
                                    <?php echo e($invoice['invoiceStatus'] ?? 'Pending'); ?>
                                </span>
                            </td>
                            <td>
                                <a href="#" class="btn btn-secondary btn-sm">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                                        <polyline points="7 10 12 15 17 10"/>
                                        <line x1="12" y1="15" x2="12" y2="3"/>
                                    </svg>
                                    Download
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php renderFooter(); ?>
