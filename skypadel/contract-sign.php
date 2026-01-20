<?php
/**
 * Sky Padel Client Portal - Contract Signing Page
 * Uses shared layout system for consistent design
 */
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/layout.php';
requireLogin();

$email = getClientEmail();
$db = getDB();
$prefix = DB_PREFIX;

$contractID = intval($_GET['id'] ?? 0);

// Get client info
$stmt = $db->prepare("SELECT * FROM {$prefix}sky_padel_lead WHERE clientEmail = ? AND status = 1 ORDER BY created DESC LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();

// Get contract with verification (client email must match)
$stmt = $db->prepare("
    SELECT c.*, q.quotationNo, q.quotationDate, q.courtConfiguration, q.validUntil,
           l.clientName, l.clientEmail, l.clientPhone, l.siteAddress, l.siteCity, l.siteState
    FROM {$prefix}sky_padel_contract c
    INNER JOIN {$prefix}sky_padel_quotation q ON c.quotationID = q.quotationID
    INNER JOIN {$prefix}sky_padel_lead l ON c.leadID = l.leadID
    WHERE c.contractID = ? AND l.clientEmail = ? AND c.status = 1
");
$stmt->bind_param("is", $contractID, $email);
$stmt->execute();
$contract = $stmt->get_result()->fetch_assoc();

if (!$contract) {
    redirect(SITE_URL . '/contracts.php');
}

// Get contract milestones
$stmt = $db->prepare("
    SELECT * FROM {$prefix}sky_padel_contract_milestone
    WHERE contractID = ?
    ORDER BY sortOrder ASC
");
$stmt->bind_param("i", $contractID);
$stmt->execute();
$milestones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Check if already signed
$alreadySigned = ($contract['contractStatus'] === 'Signed');

// Check OTP verification status
$otpVerified = !empty($contract['otpVerifiedAt']) && (time() - strtotime($contract['otpVerifiedAt'])) < 900;

// Include contract functions
require_once __DIR__ . '/../xadmin/mod/sky-padel-contract/x-sky-padel-contract.inc.php';

// Handle AJAX requests for OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');

    switch ($_POST['ajax_action']) {
        case 'send_otp':
            $result = sendContractSigningOTP($contractID, $contract['clientEmail'], $contract['clientPhone']);
            echo json_encode($result);
            exit;

        case 'verify_otp':
            $otp = trim($_POST['otp'] ?? '');
            $result = verifyContractOTP($contractID, $otp);
            echo json_encode($result);
            exit;

        case 'sign_contract':
            $signedName = trim($_POST['signed_name'] ?? '');
            $agreeTerms = isset($_POST['agree_terms']) && $_POST['agree_terms'] === '1';
            $authorizeProject = isset($_POST['authorize_project']) && $_POST['authorize_project'] === '1';

            if (empty($signedName)) {
                echo json_encode(['err' => 1, 'msg' => 'Please enter your full name.']);
                exit;
            }
            if (!$agreeTerms) {
                echo json_encode(['err' => 1, 'msg' => 'You must agree to the terms.']);
                exit;
            }
            if (!$authorizeProject) {
                echo json_encode(['err' => 1, 'msg' => 'You must authorize the project.']);
                exit;
            }

            $clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            if (strpos($clientIP, ',') !== false) {
                $clientIP = trim(explode(',', $clientIP)[0]);
            }

            $result = signContractPortalWithOTP($contractID, $signedName, $clientIP, true);
            if ($result['err'] === 0) {
                logActivity($email, 'SignContractWithOTP', 'Contract', $contractID);
            }
            echo json_encode($result);
            exit;
    }
}

// Handle signing (legacy form submission)
$signSuccess = false;
$signError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sign']) && !$alreadySigned) {
    $signedName = trim($_POST['signed_name'] ?? '');
    $agreeTerms = isset($_POST['agree_terms']);
    $authorizeProject = isset($_POST['authorize_project']);

    if (empty($signedName)) {
        $signError = 'Please enter your full name to sign the contract.';
    } elseif (!$agreeTerms) {
        $signError = 'You must agree to the terms and conditions.';
    } elseif (!$authorizeProject) {
        $signError = 'You must authorize the project to proceed.';
    } else {
        $clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        if (strpos($clientIP, ',') !== false) {
            $clientIP = trim(explode(',', $clientIP)[0]);
        }

        $result = signContractPortalWithOTP($contractID, $signedName, $clientIP, true);

        if ($result['err'] === 0) {
            $signSuccess = true;
            $alreadySigned = true;
            $contract['contractStatus'] = 'Signed';
            $contract['signedBy'] = $signedName;
            $contract['signedAt'] = date('Y-m-d H:i:s');
            logActivity($email, 'SignContractWithOTP', 'Contract', $contractID);
        } else {
            $signError = $result['msg'] ?? 'Failed to sign contract. Please try again.';
        }
    }
}

logActivity($email, 'ViewContract', 'Contract', $contractID);

renderHeader('Contract ' . $contract['contractNo']);
?>

<style>
/* Contract Document Specific Styles */
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
.back-link:hover { color: var(--color-primary); }
.back-link svg { width: 18px; height: 18px; }

.contract-document {
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-lg);
}

.document-header {
    padding: var(--space-xl);
    background: linear-gradient(135deg, var(--color-secondary-dark) 0%, var(--color-primary-dark) 100%);
    color: white;
    position: relative;
    overflow: hidden;
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

.header-content {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.document-title {
    font-family: var(--font-display);
    font-size: 2.5rem;
    letter-spacing: 0.04em;
    margin-bottom: var(--space-xs);
}

.document-number {
    font-size: 0.9rem;
    opacity: 0.9;
    margin-bottom: var(--space-md);
}

.document-meta {
    display: flex;
    gap: var(--space-lg);
    font-size: 0.85rem;
    opacity: 0.85;
}

.document-status {
    padding: var(--space-sm) var(--space-lg);
    background: rgba(255,255,255,0.2);
    border-radius: var(--radius-full);
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.document-status.signed { background: var(--color-success); }
.document-status.pending { background: var(--color-accent); }

.document-body { padding: var(--space-xl); }

/* Summary Cards */
.summary-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-lg);
    margin-bottom: var(--space-xl);
}

.summary-card {
    background: linear-gradient(135deg, var(--color-bg) 0%, var(--color-border-subtle) 100%);
    border-radius: var(--radius-md);
    padding: var(--space-lg);
    text-align: center;
}

.summary-card .label {
    font-size: 0.75rem;
    color: var(--color-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: var(--space-sm);
}

.summary-card .value {
    font-family: var(--font-display);
    font-size: 1.5rem;
    color: var(--color-primary);
}

/* PDF Download */
.pdf-download {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-lg);
    padding: var(--space-lg);
    background: var(--color-bg);
    border-radius: var(--radius-md);
    margin-bottom: var(--space-xl);
}

.pdf-download span {
    color: var(--color-text-secondary);
    font-size: 0.9rem;
}

/* Detail Grid */
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

.detail-row .label { color: var(--color-text-secondary); }
.detail-row .value { font-weight: 600; text-align: right; }

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

.milestones-table tr:last-child td { border-bottom: none; }

.milestones-table tfoot td {
    background: var(--color-bg);
    font-weight: 700;
    border-top: 2px solid var(--color-border);
}

/* Terms Section */
.terms-section {
    background: var(--color-bg);
    border-radius: var(--radius-md);
    padding: var(--space-lg);
    margin-bottom: var(--space-xl);
    max-height: 280px;
    overflow-y: auto;
}

.terms-section h4 {
    font-family: var(--font-display);
    font-size: 1rem;
    letter-spacing: 0.05em;
    margin-bottom: var(--space-md);
    position: sticky;
    top: 0;
    background: var(--color-bg);
    padding-bottom: var(--space-sm);
}

.terms-section p {
    font-size: 0.9rem;
    color: var(--color-text-secondary);
    line-height: 1.7;
    white-space: pre-line;
}

/* Signature Section */
.signature-section {
    background: linear-gradient(135deg, var(--color-primary-subtle) 0%, rgba(0, 119, 182, 0.05) 100%);
    border: 2px solid var(--color-primary);
    border-radius: var(--radius-lg);
    padding: var(--space-xl);
}

.signature-section.signed {
    background: linear-gradient(135deg, var(--color-success-bg) 0%, rgba(34, 197, 94, 0.05) 100%);
    border-color: var(--color-success);
}

.signature-header {
    text-align: center;
    margin-bottom: var(--space-xl);
}

.signature-icon {
    width: 72px;
    height: 72px;
    margin: 0 auto var(--space-lg);
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 24px var(--color-primary-glow);
}

.signature-icon.signed {
    background: linear-gradient(135deg, var(--color-success), var(--color-secondary-dark));
    box-shadow: 0 8px 24px rgba(34, 197, 94, 0.3);
}

.signature-icon svg {
    width: 36px;
    height: 36px;
    stroke: white;
    stroke-width: 2;
}

.signature-title {
    font-family: var(--font-display);
    font-size: 1.75rem;
    letter-spacing: 0.04em;
    margin-bottom: var(--space-sm);
}

.signature-subtitle {
    color: var(--color-text-secondary);
    font-size: 0.95rem;
}

/* Signature Form */
.signature-flow { max-width: 600px; margin: 0 auto; }
.signature-step { margin-bottom: var(--space-lg); }
.signature-step.hidden { display: none; }

.step-info { text-align: center; margin-bottom: var(--space-lg); }
.step-info h4 {
    font-family: var(--font-display);
    font-size: 1.25rem;
    letter-spacing: 0.04em;
    margin: var(--space-md) 0 var(--space-sm);
}
.step-info p { color: var(--color-text-secondary); font-size: 0.9rem; }

.step-badge {
    display: inline-block;
    padding: var(--space-xs) var(--space-md);
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-full);
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--color-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.step-badge-success {
    display: inline-flex;
    align-items: center;
    gap: var(--space-xs);
    background: var(--color-success-bg);
    border-color: var(--color-success);
    color: var(--color-success);
}

.otp-send-info {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    padding: var(--space-lg);
    margin-bottom: var(--space-lg);
}

.send-to-item {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    font-size: 0.9rem;
    color: var(--color-text-secondary);
}

.send-to-item svg { color: var(--color-primary); }

/* OTP Input */
.otp-input-container {
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    padding: var(--space-lg);
    margin-bottom: var(--space-lg);
}

.otp-inputs {
    display: flex;
    justify-content: center;
    gap: var(--space-md);
    margin-bottom: var(--space-md);
}

.otp-digit {
    width: 52px;
    height: 60px;
    text-align: center;
    font-family: var(--font-display);
    font-size: 1.75rem;
    border: 2px solid var(--color-border);
    border-radius: var(--radius-md);
    background: var(--color-surface);
    color: var(--color-primary);
    transition: all var(--transition-fast);
}

.otp-digit:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 4px var(--color-primary-subtle);
}

.otp-digit.filled {
    background: var(--color-primary-subtle);
    border-color: var(--color-primary);
}

.otp-timer {
    text-align: center;
    font-size: 0.85rem;
    color: var(--color-text-muted);
}

.otp-timer span {
    font-weight: 700;
    color: var(--color-primary);
}

.otp-actions {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
    align-items: center;
}

.btn-link {
    background: none;
    border: none;
    color: var(--color-primary);
    font-family: var(--font-body);
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: underline;
    transition: all var(--transition-fast);
}

.btn-link:disabled {
    color: var(--color-text-muted);
    cursor: not-allowed;
    text-decoration: none;
}

/* Form Elements */
.form-group { margin-bottom: var(--space-lg); }

.form-label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: var(--space-sm);
}

.signature-input-wrapper {
    background: var(--color-surface);
    border: 2px dashed var(--color-border);
    border-radius: var(--radius-md);
    padding: var(--space-lg);
    text-align: center;
}

.signature-input {
    width: 100%;
    padding: var(--space-md);
    font-family: 'Brush Script MT', 'Segoe Script', cursive;
    font-size: 2rem;
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

.signature-hint {
    font-size: 0.8rem;
    color: var(--color-text-muted);
    margin-top: var(--space-md);
}

/* Checkboxes */
.checkbox-group {
    background: var(--color-surface);
    border-radius: var(--radius-md);
    padding: var(--space-lg);
    margin-bottom: var(--space-md);
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: var(--space-md);
    cursor: pointer;
    font-size: 0.9rem;
    line-height: 1.5;
}

.checkbox-label input {
    width: 22px;
    height: 22px;
    margin-top: 2px;
    accent-color: var(--color-primary);
    cursor: pointer;
}

.checkbox-text { flex: 1; }

.checkbox-text strong {
    display: block;
    margin-bottom: var(--space-xs);
}

.checkbox-text span {
    color: var(--color-text-secondary);
    font-size: 0.85rem;
}

/* Signed Display */
.signed-display {
    text-align: center;
    padding: var(--space-lg);
}

.signed-name {
    font-family: 'Brush Script MT', 'Segoe Script', cursive;
    font-size: 2.5rem;
    color: var(--color-success);
    margin-bottom: var(--space-md);
}

.signed-meta {
    font-size: 0.85rem;
    color: var(--color-text-muted);
}

.signed-meta div { margin-bottom: var(--space-xs); }

/* Loading state */
.btn.loading {
    pointer-events: none;
    opacity: 0.7;
}

.btn.loading::after {
    content: '';
    width: 18px;
    height: 18px;
    border: 2px solid transparent;
    border-top-color: currentColor;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin-left: var(--space-sm);
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .summary-cards { grid-template-columns: 1fr; }
    .detail-grid { grid-template-columns: 1fr; }
    .otp-digit { width: 44px; height: 52px; font-size: 1.5rem; }
    .pdf-download { flex-direction: column; text-align: center; }
}
</style>

<a href="<?php echo SITE_URL; ?>/contracts.php" class="back-link">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M19 12H5M12 19l-7-7 7-7"/>
    </svg>
    Back to Contracts
</a>

<?php if ($signSuccess): ?>
    <div class="alert alert-success">
        Contract signed successfully! A proforma invoice has been generated. Thank you for your confirmation.
    </div>
<?php endif; ?>

<?php if ($signError): ?>
    <div class="alert alert-error"><?php echo e($signError); ?></div>
<?php endif; ?>

<!-- Contract Document -->
<div class="contract-document">
    <div class="document-header">
        <div class="header-content">
            <div>
                <h1 class="document-title">CONTRACT AGREEMENT</h1>
                <div class="document-number"><?php echo e($contract['contractNo']); ?></div>
                <div class="document-meta">
                    <span>Quotation: <?php echo e($contract['quotationNo']); ?></span>
                    <span>Date: <?php echo formatDate($contract['contractDate']); ?></span>
                </div>
            </div>
            <span class="document-status <?php echo $alreadySigned ? 'signed' : 'pending'; ?>">
                <?php echo $alreadySigned ? 'Signed' : 'Pending Signature'; ?>
            </span>
        </div>
    </div>

    <div class="document-body">
        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="label">Contract Value</div>
                <div class="value"><?php echo formatMoney($contract['contractAmount']); ?></div>
            </div>
            <div class="summary-card">
                <div class="label">Advance Due (<?php echo $contract['advancePercentage'] ?? 50; ?>%)</div>
                <div class="value"><?php echo formatMoney($contract['advanceAmount']); ?></div>
            </div>
            <div class="summary-card">
                <div class="label">Configuration</div>
                <div class="value" style="font-size: 1rem;"><?php echo e($contract['courtConfiguration'] ?: 'Standard'); ?></div>
            </div>
        </div>

        <!-- PDF Download -->
        <div class="pdf-download">
            <span>Download a copy of this contract for your records</span>
            <a href="<?php echo ADMIN_URL; ?>/mod/sky-padel-contract/x-sky-padel-contract-pdf.php?id=<?php echo $contractID; ?>" class="btn btn-secondary" target="_blank">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="12" y1="18" x2="12" y2="12"/>
                    <polyline points="9 15 12 18 15 15"/>
                </svg>
                Download PDF
            </a>
        </div>

        <!-- Client & Project Details -->
        <div class="detail-grid">
            <div class="detail-section">
                <h4>CLIENT DETAILS</h4>
                <div class="detail-row">
                    <span class="label">Name</span>
                    <span class="value"><?php echo e($contract['clientName']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Email</span>
                    <span class="value"><?php echo e($contract['clientEmail']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Phone</span>
                    <span class="value"><?php echo e($contract['clientPhone']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Site Location</span>
                    <span class="value"><?php echo e($contract['siteCity']); ?>, <?php echo e($contract['siteState']); ?></span>
                </div>
            </div>
            <div class="detail-section">
                <h4>CONTRACT DETAILS</h4>
                <div class="detail-row">
                    <span class="label">Contract Date</span>
                    <span class="value"><?php echo formatDate($contract['contractDate']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Quotation Reference</span>
                    <span class="value"><?php echo e($contract['quotationNo']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Court Configuration</span>
                    <span class="value"><?php echo e($contract['courtConfiguration'] ?: '-'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Total Value</span>
                    <span class="value" style="color: var(--color-primary);"><?php echo formatMoney($contract['contractAmount']); ?></span>
                </div>
            </div>
        </div>

        <!-- Payment Milestones -->
        <?php if (!empty($milestones)): ?>
            <h3 class="section-title">PAYMENT SCHEDULE</h3>
            <table class="milestones-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th>Milestone</th>
                        <th>Description</th>
                        <th style="text-align: center;">Percentage</th>
                        <th style="text-align: right;">Amount</th>
                        <th style="text-align: center;">Due</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($milestones as $i => $m): ?>
                        <tr>
                            <td style="text-align: center;"><?php echo $i + 1; ?></td>
                            <td><strong><?php echo e($m['milestoneName']); ?></strong></td>
                            <td><?php echo e($m['milestoneDescription'] ?? '-'); ?></td>
                            <td style="text-align: center;"><?php echo $m['paymentPercentage']; ?>%</td>
                            <td style="text-align: right; font-weight: 600;"><?php echo formatMoney($m['paymentAmount']); ?></td>
                            <td style="text-align: center;"><?php echo $m['dueAfterDays'] > 0 ? $m['dueAfterDays'] . ' days' : 'On Order'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align: right;">Total Contract Value</td>
                        <td style="text-align: right; color: var(--color-primary);"><?php echo formatMoney($contract['contractAmount']); ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        <?php endif; ?>

        <!-- Scope of Work -->
        <?php if (!empty($contract['scopeOfWork'])): ?>
            <div class="terms-section">
                <h4>SCOPE OF WORK</h4>
                <p><?php echo e($contract['scopeOfWork']); ?></p>
            </div>
        <?php endif; ?>

        <!-- Terms & Conditions -->
        <?php if (!empty($contract['termsAndConditions'])): ?>
            <div class="terms-section">
                <h4>TERMS & CONDITIONS</h4>
                <p><?php echo e($contract['termsAndConditions']); ?></p>
            </div>
        <?php endif; ?>

        <!-- Payment Terms -->
        <?php if (!empty($contract['paymentTerms'])): ?>
            <div class="terms-section" style="max-height: 180px;">
                <h4>PAYMENT TERMS</h4>
                <p><?php echo e($contract['paymentTerms']); ?></p>
            </div>
        <?php endif; ?>

        <!-- Signature Section -->
        <div class="signature-section <?php echo $alreadySigned ? 'signed' : ''; ?>">
            <div class="signature-header">
                <div class="signature-icon <?php echo $alreadySigned ? 'signed' : ''; ?>">
                    <?php if ($alreadySigned): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    <?php else: ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    <?php endif; ?>
                </div>
                <h3 class="signature-title">
                    <?php echo $alreadySigned ? 'CONTRACT SIGNED' : 'SIGN CONTRACT'; ?>
                </h3>
                <p class="signature-subtitle">
                    <?php if ($alreadySigned): ?>
                        This contract has been electronically signed and is now in effect.
                    <?php else: ?>
                        Please review the contract details above and sign below to confirm.
                    <?php endif; ?>
                </p>
            </div>

            <?php if ($alreadySigned): ?>
                <div class="signed-display">
                    <div class="signed-name"><?php echo e($contract['signedBy']); ?></div>
                    <div class="signed-meta">
                        <div>Signed on: <?php echo date('d M Y, h:i A', strtotime($contract['signedAt'])); ?></div>
                        <?php if (!empty($contract['signatureMethod'])): ?>
                            <div>Verification: <?php echo e($contract['signatureMethod']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($contract['signatureIP'])): ?>
                            <div>IP Address: <?php echo e($contract['signatureIP']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- OTP-Based Signature Flow -->
                <div class="signature-flow">
                    <!-- Step 1: Request OTP -->
                    <div id="step1-otp-request" class="signature-step <?php echo $otpVerified ? 'hidden' : ''; ?>">
                        <div class="step-info">
                            <div class="step-badge">Step 1 of 3</div>
                            <h4>Verify Your Identity</h4>
                            <p>We'll send a One-Time Password (OTP) to your registered email and phone number for verification.</p>
                        </div>
                        <div class="otp-send-info">
                            <div class="send-to-item">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                    <polyline points="22,6 12,13 2,6"/>
                                </svg>
                                <span><?php echo e(maskEmail($contract['clientEmail'])); ?></span>
                            </div>
                            <?php if ($contract['clientPhone']): ?>
                            <div class="send-to-item">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                </svg>
                                <span><?php echo e(maskPhone($contract['clientPhone'])); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-primary" id="sendOtpBtn" onclick="sendOTP()">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 2L11 13"/>
                                <path d="M22 2l-7 20-4-9-9-4 20-7z"/>
                            </svg>
                            Send OTP
                        </button>
                    </div>

                    <!-- Step 2: Verify OTP -->
                    <div id="step2-otp-verify" class="signature-step hidden">
                        <div class="step-info">
                            <div class="step-badge">Step 2 of 3</div>
                            <h4>Enter OTP</h4>
                            <p id="otpSentMessage">OTP has been sent. Please enter the 6-digit code below.</p>
                        </div>
                        <div class="otp-input-container">
                            <div class="otp-inputs">
                                <input type="text" maxlength="1" class="otp-digit" data-index="0" autocomplete="off">
                                <input type="text" maxlength="1" class="otp-digit" data-index="1" autocomplete="off">
                                <input type="text" maxlength="1" class="otp-digit" data-index="2" autocomplete="off">
                                <input type="text" maxlength="1" class="otp-digit" data-index="3" autocomplete="off">
                                <input type="text" maxlength="1" class="otp-digit" data-index="4" autocomplete="off">
                                <input type="text" maxlength="1" class="otp-digit" data-index="5" autocomplete="off">
                            </div>
                            <input type="hidden" id="fullOtp">
                            <p class="otp-timer">OTP expires in <span id="otpTimer">10:00</span></p>
                        </div>
                        <div class="otp-actions">
                            <button type="button" class="btn btn-primary" id="verifyOtpBtn" onclick="verifyOTP()" disabled>
                                Verify OTP
                            </button>
                            <button type="button" class="btn-link" id="resendOtpBtn" onclick="sendOTP()" disabled>
                                Resend OTP
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Sign Contract -->
                    <div id="step3-signature" class="signature-step <?php echo $otpVerified ? '' : 'hidden'; ?>">
                        <div class="step-info">
                            <div class="step-badge step-badge-success">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                Verified
                            </div>
                            <h4>Sign Contract</h4>
                            <p>Your identity has been verified. Please complete the signing process below.</p>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Your Full Name (as signature)</label>
                            <div class="signature-input-wrapper">
                                <input type="text" name="signed_name" id="signedName" class="signature-input"
                                       placeholder="Type your full name here"
                                       value="<?php echo e($contract['clientName']); ?>">
                                <p class="signature-hint">This will serve as your electronic signature</p>
                            </div>
                        </div>

                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="agree_terms" id="agreeTerms">
                                <div class="checkbox-text">
                                    <strong>I agree to the Terms & Conditions</strong>
                                    <span>I have read, understood, and agree to be bound by the terms and conditions, scope of work, and payment schedule outlined in this contract.</span>
                                </div>
                            </label>
                        </div>

                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="authorize_project" id="authorizeProject">
                                <div class="checkbox-text">
                                    <strong>I authorize Sky Padel India to proceed</strong>
                                    <span>I authorize Sky Padel India Pvt. Ltd. to commence work on the project as per the agreed specifications and payment milestones.</span>
                                </div>
                            </label>
                        </div>

                        <button type="button" class="btn btn-success btn-lg" id="signBtn" onclick="signContract()" style="width: 100%;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                            Sign Contract with OTP Verification
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// OTP Timer
var otpTimerInterval = null;
var otpExpiresAt = null;

// Initialize OTP input handling
document.querySelectorAll('.otp-digit').forEach(function(input, index, inputs) {
    input.addEventListener('input', function(e) {
        var value = e.target.value;
        e.target.value = value.replace(/[^0-9]/g, '');

        if (e.target.value) {
            e.target.classList.add('filled');
            if (index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        } else {
            e.target.classList.remove('filled');
        }
        updateFullOTP();
    });

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && !e.target.value && index > 0) {
            inputs[index - 1].focus();
        }
    });

    input.addEventListener('paste', function(e) {
        e.preventDefault();
        var paste = (e.clipboardData || window.clipboardData).getData('text');
        var digits = paste.replace(/[^0-9]/g, '').split('');

        inputs.forEach(function(inp, i) {
            if (digits[i]) {
                inp.value = digits[i];
                inp.classList.add('filled');
            }
        });
        updateFullOTP();
    });
});

function updateFullOTP() {
    var otp = '';
    document.querySelectorAll('.otp-digit').forEach(function(input) {
        otp += input.value;
    });
    document.getElementById('fullOtp').value = otp;
    document.getElementById('verifyOtpBtn').disabled = otp.length !== 6;
}

function startOTPTimer(minutes) {
    var seconds = minutes * 60;
    otpExpiresAt = Date.now() + (seconds * 1000);

    clearInterval(otpTimerInterval);
    otpTimerInterval = setInterval(function() {
        var remaining = Math.max(0, Math.floor((otpExpiresAt - Date.now()) / 1000));
        var min = Math.floor(remaining / 60);
        var sec = remaining % 60;
        document.getElementById('otpTimer').textContent = min + ':' + (sec < 10 ? '0' : '') + sec;

        if (remaining <= 0) {
            clearInterval(otpTimerInterval);
            document.getElementById('verifyOtpBtn').disabled = true;
            document.getElementById('resendOtpBtn').disabled = false;
            document.getElementById('otpTimer').textContent = 'Expired';
        }

        if (remaining <= (minutes * 60 - 30)) {
            document.getElementById('resendOtpBtn').disabled = false;
        }
    }, 1000);
}

function sendOTP() {
    var btn = document.getElementById('sendOtpBtn');
    btn.classList.add('loading');
    btn.textContent = 'Sending...';

    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'ajax_action=send_otp'
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        btn.classList.remove('loading');
        btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg> Send OTP';

        if (data.err === 0) {
            document.getElementById('step1-otp-request').classList.add('hidden');
            document.getElementById('step2-otp-verify').classList.remove('hidden');
            document.getElementById('otpSentMessage').textContent = 'OTP has been sent to ' + data.sentTo + '. Please enter the 6-digit code below.';
            startOTPTimer(data.expiresIn || 10);
            document.querySelector('.otp-digit').focus();
            document.getElementById('resendOtpBtn').disabled = true;
        } else {
            alert(data.msg || 'Failed to send OTP');
        }
    })
    .catch(function(err) {
        btn.classList.remove('loading');
        btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg> Send OTP';
        alert('Error sending OTP. Please try again.');
    });
}

function verifyOTP() {
    var otp = document.getElementById('fullOtp').value;
    var btn = document.getElementById('verifyOtpBtn');

    if (otp.length !== 6) {
        alert('Please enter the complete 6-digit OTP');
        return;
    }

    btn.classList.add('loading');
    btn.textContent = 'Verifying...';

    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'ajax_action=verify_otp&otp=' + encodeURIComponent(otp)
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        btn.classList.remove('loading');
        btn.textContent = 'Verify OTP';

        if (data.err === 0 && data.verified) {
            clearInterval(otpTimerInterval);
            document.getElementById('step2-otp-verify').classList.add('hidden');
            document.getElementById('step3-signature').classList.remove('hidden');
            document.getElementById('step3-signature').scrollIntoView({ behavior: 'smooth' });
        } else {
            alert(data.msg || 'Invalid OTP');
            document.querySelectorAll('.otp-digit').forEach(function(input) {
                input.value = '';
                input.classList.remove('filled');
            });
            document.querySelector('.otp-digit').focus();
        }
    })
    .catch(function(err) {
        btn.classList.remove('loading');
        btn.textContent = 'Verify OTP';
        alert('Error verifying OTP. Please try again.');
    });
}

function signContract() {
    var name = document.getElementById('signedName').value.trim();
    var agreeTerms = document.getElementById('agreeTerms').checked;
    var authorizeProject = document.getElementById('authorizeProject').checked;

    if (!name) {
        alert('Please enter your full name to sign the contract.');
        return;
    }

    if (!agreeTerms) {
        alert('You must agree to the terms and conditions.');
        return;
    }

    if (!authorizeProject) {
        alert('You must authorize the project to proceed.');
        return;
    }

    if (!confirm('By clicking OK, you are electronically signing this contract with OTP verification. This action cannot be undone. Do you want to proceed?')) {
        return;
    }

    var btn = document.getElementById('signBtn');
    btn.classList.add('loading');
    btn.textContent = 'Signing...';

    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'ajax_action=sign_contract&signed_name=' + encodeURIComponent(name) +
              '&agree_terms=' + (agreeTerms ? '1' : '0') +
              '&authorize_project=' + (authorizeProject ? '1' : '0')
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.err === 0) {
            alert('Contract signed successfully! A proforma invoice has been generated.');
            window.location.reload();
        } else {
            btn.classList.remove('loading');
            btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> Sign Contract with OTP Verification';
            alert(data.msg || 'Failed to sign contract');
        }
    })
    .catch(function(err) {
        btn.classList.remove('loading');
        btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> Sign Contract with OTP Verification';
        alert('Error signing contract. Please try again.');
    });
}
</script>

<?php renderFooter(); ?>
