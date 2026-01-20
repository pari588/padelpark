<?php
/**
 * Sky Padel Client Portal - Quotation View & Approve
 * Uses shared layout system for consistent design
 */
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/layout.php';
requireLogin();

$email = getClientEmail();
$db = getDB();
$prefix = DB_PREFIX;

$quotationID = intval($_GET['id'] ?? 0);

// Get client info
$stmt = $db->prepare("SELECT * FROM {$prefix}sky_padel_lead WHERE clientEmail = ? AND status = 1 ORDER BY created DESC LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();

// Get quotation with verification
$stmt = $db->prepare("
    SELECT q.*, l.clientName, l.clientEmail, l.clientPhone, l.siteAddress, l.siteCity, l.siteState,
           CONCAT_WS(', ', l.siteCity, l.siteState) as projectLocation
    FROM {$prefix}sky_padel_quotation q
    INNER JOIN {$prefix}sky_padel_lead l ON q.leadID = l.leadID
    WHERE q.quotationID = ? AND l.clientEmail = ? AND q.status = 1
");
$stmt->bind_param("is", $quotationID, $email);
$stmt->execute();
$quotation = $stmt->get_result()->fetch_assoc();

if (!$quotation) {
    redirect(SITE_URL . '/quotations.php');
}

// Get quotation milestones/items
$stmt = $db->prepare("
    SELECT * FROM {$prefix}sky_padel_quotation_milestone
    WHERE quotationID = ?
    ORDER BY sortOrder ASC
");
$stmt->bind_param("i", $quotationID);
$stmt->execute();
$milestones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Check if can approve/reject
$canApprove = in_array($quotation['quotationStatus'], ['Sent', 'Client Reviewing']);

// Handle approval
$approvalSuccess = false;
$approvalError = '';
$rejectionSuccess = false;
$contractID = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve']) && $canApprove) {
    $signature = trim($_POST['signature'] ?? '');

    if (empty($signature)) {
        $approvalError = 'Please provide your signature to approve.';
    } else {
        // Update quotation
        $stmt = $db->prepare("
            UPDATE {$prefix}sky_padel_quotation
            SET quotationStatus = 'Approved', approvedDate = NOW()
            WHERE quotationID = ?
        ");
        $stmt->bind_param("i", $quotationID);

        if ($stmt->execute()) {
            logActivity($email, 'ApproveQuotation', 'Quotation', $quotationID);

            // Generate contract
            require_once __DIR__ . '/../xadmin/mod/sky-padel-contract/x-sky-padel-contract.inc.php';
            $contractID = generateContract($quotationID);

            if ($contractID) {
                // Redirect to contract signing
                header("Location: " . SITE_URL . "/contract-sign.php?id=" . $contractID);
                exit;
            } else {
                $approvalSuccess = true;
                $quotation['quotationStatus'] = 'Approved';
                $canApprove = false;
            }
        } else {
            $approvalError = 'Failed to approve quotation. Please try again.';
        }
    }
}

// Handle rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject']) && $canApprove) {
    $rejectionReason = trim($_POST['rejection_reason'] ?? '');

    $stmt = $db->prepare("
        UPDATE {$prefix}sky_padel_quotation
        SET quotationStatus = 'Rejected', rejectedDate = NOW(), rejectionReason = ?
        WHERE quotationID = ?
    ");
    $stmt->bind_param("si", $rejectionReason, $quotationID);

    if ($stmt->execute()) {
        $rejectionSuccess = true;
        $quotation['quotationStatus'] = 'Rejected';
        $canApprove = false;
        logActivity($email, 'RejectQuotation', 'Quotation', $quotationID);
    } else {
        $approvalError = 'Failed to reject quotation. Please try again.';
    }
}

logActivity($email, 'ViewQuotation', 'Quotation', $quotationID);

renderHeader('Quotation ' . $quotation['quotationNo']);
?>

<style>
/* Quotation Document Specific Styles */
.back-link {
    display: inline-flex;
    align-items: center;
    gap: var(--space-sm);
    font-size: 0.9rem;
    color: var(--color-text-secondary);
    text-decoration: none;
    margin-bottom: var(--space-lg);
    transition: all var(--transition-fast);
}
.back-link:hover {
    color: var(--color-primary);
}
.back-link svg {
    width: 18px;
    height: 18px;
}

.quotation-document {
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-lg);
}

.document-header {
    padding: var(--space-xl);
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 50%, var(--color-secondary-dark) 100%);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    position: relative;
}

.document-header::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        linear-gradient(90deg, transparent 49.5%, rgba(255,255,255,0.1) 49.5%, rgba(255,255,255,0.1) 50.5%, transparent 50.5%),
        linear-gradient(0deg, transparent 49.5%, rgba(255,255,255,0.1) 49.5%, rgba(255,255,255,0.1) 50.5%, transparent 50.5%);
    background-size: 100% 100%;
    pointer-events: none;
}

.document-title {
    font-family: var(--font-display);
    font-size: 2.5rem;
    letter-spacing: 0.04em;
    margin-bottom: var(--space-xs);
    position: relative;
}

.document-number {
    font-size: 0.9rem;
    opacity: 0.9;
    position: relative;
}

.document-status {
    padding: var(--space-sm) var(--space-lg);
    background: rgba(255,255,255,0.2);
    border-radius: var(--radius-full);
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: relative;
}

.document-status.approved {
    background: var(--color-success);
}

.document-status.rejected {
    background: var(--color-error);
}

.document-body {
    padding: var(--space-xl);
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-xl);
    margin-bottom: var(--space-xl);
    padding-bottom: var(--space-xl);
    border-bottom: 1px solid var(--color-border);
}

.detail-section h4 {
    font-family: var(--font-display);
    font-size: 0.9rem;
    letter-spacing: 0.1em;
    color: var(--color-text-muted);
    margin-bottom: var(--space-md);
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: var(--space-sm) 0;
    font-size: 0.95rem;
}

.detail-row .label {
    color: var(--color-text-secondary);
}

.detail-row .value {
    font-weight: 600;
    text-align: right;
}

/* Milestones Table */
.section-title {
    font-family: var(--font-display);
    font-size: 1.5rem;
    letter-spacing: 0.04em;
    margin-bottom: var(--space-lg);
}

.milestones-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: var(--space-xl);
}

.milestones-table th {
    text-align: left;
    padding: var(--space-md);
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--color-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    background: var(--color-bg);
    border-bottom: 1px solid var(--color-border);
}

.milestones-table td {
    padding: var(--space-md);
    border-bottom: 1px solid var(--color-border-subtle);
    font-size: 0.95rem;
}

.milestones-table tr:last-child td {
    border-bottom: none;
}

/* Summary Box */
.quotation-summary {
    display: flex;
    justify-content: flex-end;
    margin-bottom: var(--space-xl);
}

.summary-box {
    width: 340px;
    background: linear-gradient(135deg, var(--color-bg) 0%, var(--color-border-subtle) 100%);
    border-radius: var(--radius-md);
    padding: var(--space-lg);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: var(--space-sm) 0;
    font-size: 0.95rem;
}

.summary-row.total {
    border-top: 2px solid var(--color-border);
    margin-top: var(--space-md);
    padding-top: var(--space-md);
    font-size: 1.2rem;
    font-weight: 700;
}

.summary-row.total .value {
    color: var(--color-primary);
    font-family: var(--font-display);
    font-size: 1.5rem;
}

/* Terms Section */
.terms-section {
    background: var(--color-bg);
    border-radius: var(--radius-md);
    padding: var(--space-lg);
    margin-bottom: var(--space-xl);
}

.terms-section h4 {
    font-family: var(--font-display);
    font-size: 1rem;
    letter-spacing: 0.05em;
    margin-bottom: var(--space-md);
}

.terms-section p {
    font-size: 0.9rem;
    color: var(--color-text-secondary);
    line-height: 1.7;
    white-space: pre-line;
}

/* Approval Section */
.approval-section {
    background: linear-gradient(135deg, var(--color-success-bg) 0%, rgba(34, 197, 94, 0.05) 100%);
    border: 2px solid var(--color-success);
    border-radius: var(--radius-lg);
    padding: var(--space-xl);
    text-align: center;
}

.approval-section.pending {
    background: linear-gradient(135deg, var(--color-warning-bg) 0%, rgba(251, 191, 36, 0.05) 100%);
    border-color: var(--color-accent);
}

.approval-section.rejected {
    background: linear-gradient(135deg, var(--color-error-bg) 0%, rgba(239, 68, 68, 0.05) 100%);
    border-color: var(--color-error);
}

.approval-icon {
    width: 72px;
    height: 72px;
    margin: 0 auto var(--space-lg);
    background: linear-gradient(135deg, var(--color-success), var(--color-secondary-dark));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 24px rgba(34, 197, 94, 0.3);
}

.approval-icon.pending {
    background: linear-gradient(135deg, var(--color-accent), var(--color-fire));
    box-shadow: 0 8px 24px var(--color-accent-glow);
}

.approval-icon.rejected {
    background: linear-gradient(135deg, var(--color-error), #DC2626);
    box-shadow: 0 8px 24px rgba(239, 68, 68, 0.3);
}

.approval-icon svg {
    width: 36px;
    height: 36px;
    stroke: white;
    stroke-width: 2;
}

.approval-title {
    font-family: var(--font-display);
    font-size: 1.75rem;
    letter-spacing: 0.04em;
    margin-bottom: var(--space-sm);
}

.approval-text {
    color: var(--color-text-secondary);
    margin-bottom: var(--space-lg);
}

.approval-date {
    font-size: 0.85rem;
    color: var(--color-text-muted);
}

/* Signature Form */
.signature-form {
    max-width: 500px;
    margin: 0 auto;
}

.signature-box {
    background: var(--color-surface);
    border: 2px dashed var(--color-border);
    border-radius: var(--radius-md);
    padding: var(--space-lg);
    margin-bottom: var(--space-lg);
}

.signature-input {
    width: 100%;
    padding: var(--space-md);
    font-family: 'Brush Script MT', 'Segoe Script', cursive;
    font-size: 1.75rem;
    text-align: center;
    border: none;
    border-bottom: 2px solid var(--color-border);
    background: transparent;
    color: var(--color-text);
}

.signature-input:focus {
    outline: none;
    border-color: var(--color-primary);
}

.signature-input::placeholder {
    font-family: var(--font-body);
    font-size: 0.9rem;
    color: var(--color-text-muted);
}

.signature-label {
    font-size: 0.8rem;
    color: var(--color-text-muted);
    margin-top: var(--space-md);
}

.btn-reject {
    background: var(--color-surface);
    color: var(--color-error);
    border: 2px solid var(--color-error);
}
.btn-reject:hover {
    background: var(--color-error);
    color: white;
}

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: var(--color-surface);
    border-radius: var(--radius-lg);
    width: 90%;
    max-width: 500px;
    box-shadow: var(--shadow-xl);
    overflow: hidden;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-lg);
    border-bottom: 1px solid var(--color-border);
}

.modal-header h3 {
    font-family: var(--font-display);
    font-size: 1.25rem;
    letter-spacing: 0.04em;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.75rem;
    cursor: pointer;
    color: var(--color-text-muted);
    line-height: 1;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-sm);
    transition: all var(--transition-fast);
}

.modal-close:hover {
    background: var(--color-error-bg);
    color: var(--color-error);
}

.modal-body {
    padding: var(--space-lg);
}

.reject-textarea {
    width: 100%;
    padding: var(--space-md);
    border: 2px solid var(--color-border);
    border-radius: var(--radius-md);
    font-family: var(--font-body);
    font-size: 0.95rem;
    resize: vertical;
    min-height: 100px;
}

.reject-textarea:focus {
    outline: none;
    border-color: var(--color-primary);
}

.modal-footer {
    display: flex;
    gap: var(--space-md);
    justify-content: flex-end;
    padding: var(--space-lg);
    border-top: 1px solid var(--color-border);
    background: var(--color-bg);
}

.btn-cancel {
    background: var(--color-surface);
    color: var(--color-text-secondary);
    border: 1px solid var(--color-border);
}

.btn-reject-confirm {
    background: linear-gradient(135deg, var(--color-error), #DC2626);
    color: white;
    box-shadow: 0 4px 14px rgba(239, 68, 68, 0.3);
}

@media (max-width: 768px) {
    .document-header {
        flex-direction: column;
        gap: var(--space-md);
        padding: var(--space-lg);
    }
    .document-body {
        padding: var(--space-lg);
    }
    .detail-grid {
        grid-template-columns: 1fr;
        gap: var(--space-lg);
    }
    .summary-box {
        width: 100%;
    }
    .milestones-table {
        font-size: 0.85rem;
    }
    .milestones-table th,
    .milestones-table td {
        padding: var(--space-sm);
    }
}
</style>

<a href="<?php echo SITE_URL; ?>/quotations.php" class="back-link">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M19 12H5M12 19l-7-7 7-7"/>
    </svg>
    Back to Quotations
</a>

<?php if ($approvalSuccess): ?>
    <div class="alert alert-success">Quotation approved successfully! Thank you for your confirmation.</div>
<?php endif; ?>

<?php if ($rejectionSuccess): ?>
    <div class="alert alert-warning">Quotation has been rejected. Our team will contact you shortly to discuss your requirements.</div>
<?php endif; ?>

<?php if ($approvalError): ?>
    <div class="alert alert-error"><?php echo e($approvalError); ?></div>
<?php endif; ?>

<!-- Quotation Document -->
<div class="quotation-document">
    <div class="document-header">
        <div>
            <h1 class="document-title">QUOTATION</h1>
            <div class="document-number"><?php echo e($quotation['quotationNo']); ?></div>
        </div>
        <span class="document-status<?php echo $quotation['quotationStatus'] == 'Approved' ? ' approved' : ($quotation['quotationStatus'] == 'Rejected' ? ' rejected' : ''); ?>">
            <?php echo e($quotation['quotationStatus']); ?>
        </span>
    </div>

    <div class="document-body">
        <!-- Details -->
        <div class="detail-grid">
            <div class="detail-section">
                <h4>CLIENT DETAILS</h4>
                <div class="detail-row">
                    <span class="label">Name</span>
                    <span class="value"><?php echo e($quotation['clientName']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Email</span>
                    <span class="value"><?php echo e($quotation['clientEmail']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Phone</span>
                    <span class="value"><?php echo e($quotation['clientPhone']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Location</span>
                    <span class="value"><?php echo e($quotation['siteCity']); ?>, <?php echo e($quotation['siteState']); ?></span>
                </div>
            </div>
            <div class="detail-section">
                <h4>QUOTATION DETAILS</h4>
                <div class="detail-row">
                    <span class="label">Date</span>
                    <span class="value"><?php echo formatDate($quotation['quotationDate']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Valid Until</span>
                    <span class="value"><?php echo formatDate($quotation['validUntil']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Configuration</span>
                    <span class="value"><?php echo e($quotation['courtConfiguration']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Advance</span>
                    <span class="value"><?php echo $quotation['advancePercentage']; ?>%</span>
                </div>
            </div>
        </div>

        <!-- Payment Milestones -->
        <?php if (!empty($milestones)): ?>
            <h3 class="section-title">PAYMENT SCHEDULE</h3>
            <table class="milestones-table">
                <thead>
                    <tr>
                        <th>Milestone</th>
                        <th>Description</th>
                        <th style="text-align: right;">Percentage</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($milestones as $m): ?>
                        <tr>
                            <td><strong><?php echo e($m['milestoneName']); ?></strong></td>
                            <td><?php echo e($m['description'] ?? '-'); ?></td>
                            <td style="text-align: right;"><?php echo $m['percentage']; ?>%</td>
                            <td style="text-align: right; font-weight: 600;"><?php echo formatMoney($m['amount']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Summary -->
        <div class="quotation-summary">
            <div class="summary-box">
                <div class="summary-row">
                    <span>Base Amount</span>
                    <span><?php echo formatMoney($quotation['baseAmount']); ?></span>
                </div>
                <div class="summary-row">
                    <span>Installation</span>
                    <span><?php echo formatMoney($quotation['installationCost']); ?></span>
                </div>
                <?php if ($quotation['additionalFeatures'] > 0): ?>
                    <div class="summary-row">
                        <span>Additional Features</span>
                        <span><?php echo formatMoney($quotation['additionalFeatures']); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($quotation['discount'] > 0): ?>
                    <div class="summary-row" style="color: var(--color-success);">
                        <span>Discount</span>
                        <span>- <?php echo formatMoney($quotation['discount']); ?></span>
                    </div>
                <?php endif; ?>
                <div class="summary-row">
                    <span>Tax</span>
                    <span><?php echo formatMoney($quotation['taxAmount']); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span class="value"><?php echo formatMoney($quotation['totalAmount']); ?></span>
                </div>
            </div>
        </div>

        <!-- Terms -->
        <?php if ($quotation['termsAndConditions']): ?>
            <div class="terms-section">
                <h4>TERMS & CONDITIONS</h4>
                <p><?php echo e($quotation['termsAndConditions']); ?></p>
            </div>
        <?php endif; ?>

        <!-- Approval Section -->
        <?php if ($quotation['quotationStatus'] == 'Approved'): ?>
            <div class="approval-section">
                <div class="approval-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                </div>
                <h3 class="approval-title">QUOTATION APPROVED</h3>
                <p class="approval-text">This quotation has been approved and accepted.</p>
                <?php if ($quotation['approvedDate']): ?>
                    <p class="approval-date">Approved on: <?php echo date('d M Y, h:i A', strtotime($quotation['approvedDate'])); ?></p>
                <?php endif; ?>
            </div>
        <?php elseif ($quotation['quotationStatus'] == 'Rejected'): ?>
            <div class="approval-section rejected">
                <div class="approval-icon rejected">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </div>
                <h3 class="approval-title">QUOTATION REJECTED</h3>
                <p class="approval-text">This quotation has been rejected. Our team will reach out to discuss your requirements.</p>
                <?php if ($quotation['rejectedDate']): ?>
                    <p class="approval-date">Rejected on: <?php echo date('d M Y, h:i A', strtotime($quotation['rejectedDate'])); ?></p>
                <?php endif; ?>
            </div>
        <?php elseif ($canApprove): ?>
            <div class="approval-section pending">
                <div class="approval-icon pending">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </div>
                <h3 class="approval-title">REVIEW & APPROVE</h3>
                <p class="approval-text">Please review the quotation details above and sign below to approve, or reject if you need changes.</p>

                <form method="POST" class="signature-form" id="approvalForm">
                    <div class="signature-box">
                        <input type="text" name="signature" id="signatureInput" class="signature-input" placeholder="Type your full name as signature">
                        <p class="signature-label">By signing, you agree to the terms and conditions stated above.</p>
                    </div>
                    <div class="flex justify-center gap-md" style="flex-wrap: wrap;">
                        <button type="submit" name="approve" class="btn btn-success btn-lg">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            Approve Quotation
                        </button>
                        <button type="button" class="btn btn-reject btn-lg" onclick="showRejectModal()">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 6L6 18M6 6l12 12"/>
                            </svg>
                            Reject Quotation
                        </button>
                    </div>
                </form>
            </div>

            <!-- Rejection Modal -->
            <div id="rejectModal" class="modal-overlay" style="display: none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>REJECT QUOTATION</h3>
                        <button type="button" class="modal-close" onclick="hideRejectModal()">&times;</button>
                    </div>
                    <form method="POST" id="rejectForm">
                        <div class="modal-body">
                            <p style="margin-bottom: var(--space-lg); color: var(--color-text-secondary);">
                                Please let us know why you're rejecting this quotation (optional). This helps us improve our service.
                            </p>
                            <textarea name="rejection_reason" class="reject-textarea" rows="4" placeholder="Enter your reason (optional)..."></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-cancel" onclick="hideRejectModal()">Cancel</button>
                            <button type="submit" name="reject" class="btn btn-reject-confirm">Confirm Rejection</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                function showRejectModal() {
                    document.getElementById('rejectModal').style.display = 'flex';
                }
                function hideRejectModal() {
                    document.getElementById('rejectModal').style.display = 'none';
                }
                // Close modal on outside click
                document.getElementById('rejectModal').addEventListener('click', function(e) {
                    if (e.target === this) hideRejectModal();
                });
                // Validate approval form
                document.getElementById('approvalForm').addEventListener('submit', function(e) {
                    if (e.submitter && e.submitter.name === 'approve') {
                        var sig = document.getElementById('signatureInput').value.trim();
                        if (!sig) {
                            e.preventDefault();
                            alert('Please type your full name as signature to approve.');
                        }
                    }
                });
            </script>
        <?php endif; ?>
    </div>
</div>

<?php renderFooter(); ?>
