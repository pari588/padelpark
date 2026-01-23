<?php
/**
 * Vendor Portal - Documents Management
 * Upload, view, and manage compliance documents
 */

$pageTitle = 'Documents';
include("x-header.php");

$vendorID = vpGetVendorID();

// Handle document upload
$uploadMsg = '';
$uploadErr = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'upload' && isset($_FILES['document'])) {
        $docType = $_POST['documentType'] ?? '';
        $description = $_POST['description'] ?? '';
        $validFrom = $_POST['validFrom'] ?? null;
        $validUntil = $_POST['validUntil'] ?? null;

        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        $file = $_FILES['document'];

        if ($file['error'] === UPLOAD_ERR_OK) {
            if (!in_array($file['type'], $allowedTypes)) {
                $uploadMsg = 'Invalid file type. Only PDF, JPG, and PNG files are allowed.';
                $uploadErr = true;
            } elseif ($file['size'] > $maxSize) {
                $uploadMsg = 'File size exceeds 5MB limit.';
                $uploadErr = true;
            } else {
                // Create upload directory
                $uploadDir = BES_ROOT . '/uploads/vendor-documents/' . $vendorID . '/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Generate unique filename
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $fileName = strtolower(str_replace(' ', '-', $docType)) . '-' . date('YmdHis') . '.' . $ext;
                $filePath = $uploadDir . $fileName;
                $relativePath = 'uploads/vendor-documents/' . $vendorID . '/' . $fileName;

                if (move_uploaded_file($file['tmp_name'], $filePath)) {
                    // Insert into database
                    $DB->sql = "INSERT INTO mx_vendor_document
                                (vendorID, documentType, documentName, documentPath, documentSize, mimeType, description, validFrom, validUntil, uploadedBy, status)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
                    $DB->vals = [
                        $vendorID,
                        $docType,
                        $file['name'],
                        $relativePath,
                        $file['size'],
                        $file['type'],
                        $description,
                        $validFrom ?: null,
                        $validUntil ?: null,
                        $_SESSION[VENDOR_PORTAL_SESSION]['userID']
                    ];
                    $DB->types = "issssisssi";

                    if ($DB->dbQuery()) {
                        $uploadMsg = 'Document uploaded successfully.';
                    } else {
                        $uploadMsg = 'Error saving document to database.';
                        $uploadErr = true;
                    }
                } else {
                    $uploadMsg = 'Error uploading file. Please try again.';
                    $uploadErr = true;
                }
            }
        } else {
            $uploadMsg = 'Upload error. Please try again.';
            $uploadErr = true;
        }
    }

    if ($_POST['action'] === 'delete' && isset($_POST['documentID'])) {
        $docID = intval($_POST['documentID']);
        $DB->sql = "UPDATE mx_vendor_document SET status = 0 WHERE documentID = ? AND vendorID = ?";
        $DB->vals = [$docID, $vendorID];
        $DB->types = "ii";
        $DB->dbQuery();
        $uploadMsg = 'Document deleted successfully.';
    }
}

// Get vendor documents
$DB->sql = "SELECT * FROM mx_vendor_document WHERE vendorID = ? AND status = 1 ORDER BY uploadedAt DESC";
$DB->vals = [$vendorID];
$DB->types = "i";
$documents = $DB->dbRows();

// Document types for upload
$documentTypes = [
    'Registration Certificate',
    'GST Certificate',
    'PAN Card',
    'MSME Certificate',
    'Cancelled Cheque',
    'Bank Statement',
    'Address Proof',
    'ID Proof',
    'ISO Certificate',
    'Quality Certificate',
    'Other'
];

// Group documents by type for stats
$docsByType = [];
foreach ($documents as $doc) {
    $type = $doc['documentType'];
    if (!isset($docsByType[$type])) {
        $docsByType[$type] = [];
    }
    $docsByType[$type][] = $doc;
}

// Count verification statuses
$verifiedCount = 0;
$pendingCount = 0;
$rejectedCount = 0;
$expiredCount = 0;

foreach ($documents as $doc) {
    switch ($doc['verificationStatus']) {
        case 'Verified': $verifiedCount++; break;
        case 'Pending': $pendingCount++; break;
        case 'Rejected': $rejectedCount++; break;
        case 'Expired': $expiredCount++; break;
    }
}
?>

<?php if ($uploadMsg): ?>
    <div class="alert <?php echo $uploadErr ? 'alert-danger' : 'alert-success'; ?>">
        <span class="alert-icon">
            <i class="fas <?php echo $uploadErr ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
        </span>
        <span><?php echo vpClean($uploadMsg); ?></span>
    </div>
<?php endif; ?>

<div class="page-header">
    <div class="page-header-left">
        <h1>Documents</h1>
        <p>Manage your compliance and verification documents</p>
    </div>
    <div class="page-header-actions">
        <button class="btn btn-primary" onclick="toggleUploadModal()">
            <i class="fas fa-cloud-upload-alt"></i>
            Upload Document
        </button>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid animate-stagger">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-header">
            <div class="stat-card-label">Total Documents</div>
            <div class="stat-card-icon">
                <i class="fas fa-folder-open"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo count($documents); ?></div>
        <div class="stat-card-change">
            Uploaded files
        </div>
    </div>

    <div class="stat-card stat-card-success">
        <div class="stat-card-header">
            <div class="stat-card-label">Verified</div>
            <div class="stat-card-icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $verifiedCount; ?></div>
        <div class="stat-card-change positive">
            <i class="fas fa-shield-alt"></i>
            Approved documents
        </div>
    </div>

    <div class="stat-card stat-card-warning">
        <div class="stat-card-header">
            <div class="stat-card-label">Pending Review</div>
            <div class="stat-card-icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $pendingCount; ?></div>
        <div class="stat-card-change">
            Awaiting verification
        </div>
    </div>

    <div class="stat-card stat-card-accent">
        <div class="stat-card-header">
            <div class="stat-card-label">Action Required</div>
            <div class="stat-card-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $rejectedCount + $expiredCount; ?></div>
        <div class="stat-card-change">
            Rejected or expired
        </div>
    </div>
</div>

<!-- Documents Grid -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-file-alt text-primary"></i>
            Your Documents
        </h3>
        <div class="d-flex gap-sm">
            <select class="form-control" style="width: 180px; padding: 0.5rem 1rem;" id="filterType">
                <option value="">All Types</option>
                <?php foreach ($documentTypes as $type): ?>
                    <option value="<?php echo vpClean($type); ?>"><?php echo vpClean($type); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (count($documents) > 0): ?>
            <div class="documents-grid" id="documentsGrid">
                <?php foreach ($documents as $doc): ?>
                    <div class="document-card" data-type="<?php echo vpClean($doc['documentType']); ?>">
                        <div class="document-card-icon">
                            <?php
                            $iconClass = 'fa-file';
                            if (strpos($doc['mimeType'], 'pdf') !== false) {
                                $iconClass = 'fa-file-pdf';
                            } elseif (strpos($doc['mimeType'], 'image') !== false) {
                                $iconClass = 'fa-file-image';
                            }
                            ?>
                            <i class="fas <?php echo $iconClass; ?>"></i>
                        </div>
                        <div class="document-card-body">
                            <div class="document-type-badge"><?php echo vpClean($doc['documentType']); ?></div>
                            <h4 class="document-name"><?php echo vpClean($doc['documentName']); ?></h4>
                            <?php if ($doc['description']): ?>
                                <p class="document-desc"><?php echo vpClean($doc['description']); ?></p>
                            <?php endif; ?>
                            <div class="document-meta">
                                <span><i class="fas fa-calendar"></i> <?php echo vpFormatDate($doc['uploadedAt']); ?></span>
                                <span><i class="fas fa-hdd"></i> <?php echo formatFileSize($doc['documentSize']); ?></span>
                            </div>
                            <?php if ($doc['validUntil']): ?>
                                <div class="document-validity">
                                    <?php
                                    $isExpired = strtotime($doc['validUntil']) < time();
                                    $validityClass = $isExpired ? 'expired' : 'valid';
                                    ?>
                                    <span class="validity-badge <?php echo $validityClass; ?>">
                                        <i class="fas <?php echo $isExpired ? 'fa-exclamation-circle' : 'fa-calendar-check'; ?>"></i>
                                        Valid until: <?php echo vpFormatDate($doc['validUntil']); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="document-card-footer">
                            <div class="verification-status status-<?php echo strtolower($doc['verificationStatus']); ?>">
                                <?php
                                $statusIcons = [
                                    'Pending' => 'fa-clock',
                                    'Verified' => 'fa-check-circle',
                                    'Rejected' => 'fa-times-circle',
                                    'Expired' => 'fa-exclamation-circle'
                                ];
                                $icon = $statusIcons[$doc['verificationStatus']] ?? 'fa-circle';
                                ?>
                                <i class="fas <?php echo $icon; ?>"></i>
                                <?php echo vpClean($doc['verificationStatus']); ?>
                            </div>
                            <div class="document-actions">
                                <a href="<?php echo SITEURL . $doc['documentPath']; ?>" target="_blank" class="btn btn-ghost btn-icon" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo SITEURL . $doc['documentPath']; ?>" download class="btn btn-ghost btn-icon" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                <button type="button" class="btn btn-ghost btn-icon text-danger" title="Delete" onclick="confirmDelete(<?php echo $doc['documentID']; ?>, '<?php echo vpClean($doc['documentName']); ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php if ($doc['verificationStatus'] === 'Rejected' && $doc['rejectionReason']): ?>
                            <div class="document-rejection-reason">
                                <i class="fas fa-info-circle"></i>
                                <strong>Rejection Reason:</strong> <?php echo vpClean($doc['rejectionReason']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h3>No Documents Uploaded</h3>
                <p>Upload your compliance documents to get verified and start participating in RFQs.</p>
                <button class="btn btn-primary" style="margin-top: var(--space-md);" onclick="toggleUploadModal()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    Upload Your First Document
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Required Documents Checklist -->
<div class="card" style="margin-top: var(--space-xl);">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-clipboard-check text-accent"></i>
            Required Documents Checklist
        </h3>
    </div>
    <div class="card-body">
        <div class="checklist-grid">
            <?php
            $requiredDocs = ['Registration Certificate', 'GST Certificate', 'PAN Card', 'Cancelled Cheque'];
            foreach ($requiredDocs as $reqDoc):
                $hasDoc = isset($docsByType[$reqDoc]) && count($docsByType[$reqDoc]) > 0;
                $isVerified = false;
                if ($hasDoc) {
                    foreach ($docsByType[$reqDoc] as $d) {
                        if ($d['verificationStatus'] === 'Verified') {
                            $isVerified = true;
                            break;
                        }
                    }
                }
            ?>
                <div class="checklist-item <?php echo $hasDoc ? ($isVerified ? 'verified' : 'uploaded') : 'missing'; ?>">
                    <div class="checklist-icon">
                        <?php if ($isVerified): ?>
                            <i class="fas fa-check-circle"></i>
                        <?php elseif ($hasDoc): ?>
                            <i class="fas fa-clock"></i>
                        <?php else: ?>
                            <i class="fas fa-circle"></i>
                        <?php endif; ?>
                    </div>
                    <div class="checklist-content">
                        <div class="checklist-title"><?php echo $reqDoc; ?></div>
                        <div class="checklist-status">
                            <?php if ($isVerified): ?>
                                <span class="text-success">Verified</span>
                            <?php elseif ($hasDoc): ?>
                                <span class="text-warning">Pending verification</span>
                            <?php else: ?>
                                <span class="text-muted">Not uploaded</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (!$hasDoc): ?>
                        <button class="btn btn-outline btn-sm" onclick="toggleUploadModal('<?php echo $reqDoc; ?>')">
                            <i class="fas fa-upload"></i> Upload
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal-overlay" id="uploadModal">
    <div class="modal-container">
        <div class="modal-header">
            <h3><i class="fas fa-cloud-upload-alt"></i> Upload Document</h3>
            <button type="button" class="btn btn-ghost btn-icon" onclick="toggleUploadModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Document Type *</label>
                    <select name="documentType" class="form-control" required id="uploadDocType">
                        <option value="">Select document type</option>
                        <?php foreach ($documentTypes as $type): ?>
                            <option value="<?php echo vpClean($type); ?>"><?php echo vpClean($type); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Document File *</label>
                    <div class="file-upload-zone" id="dropZone">
                        <input type="file" name="document" id="documentFile" accept=".pdf,.jpg,.jpeg,.png" required>
                        <div class="file-upload-content">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Drag & drop your file here or <span class="text-primary">browse</span></p>
                            <small class="text-muted">PDF, JPG, PNG (max 5MB)</small>
                        </div>
                        <div class="file-preview" id="filePreview" style="display: none;">
                            <i class="fas fa-file"></i>
                            <span id="fileName"></span>
                            <button type="button" class="btn btn-ghost btn-icon" onclick="clearFile()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Optional description or notes"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md);">
                    <div class="form-group">
                        <label class="form-label">Valid From</label>
                        <input type="date" name="validFrom" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Valid Until</label>
                        <input type="date" name="validUntil" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="toggleUploadModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Upload Document
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3><i class="fas fa-trash text-danger"></i> Delete Document</h3>
            <button type="button" class="btn btn-ghost btn-icon" onclick="toggleDeleteModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="documentID" id="deleteDocID">
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteDocName"></strong>?</p>
                <p class="text-muted text-sm">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="toggleDeleteModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background: var(--vp-danger);">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* ============================================
   DOCUMENTS PAGE - PREMIUM B2B PORTAL STYLING
   Industrial-Luxury Aesthetic with Depth & Motion
   ============================================ */

/* Page-specific animations */
@keyframes docCardReveal {
    from {
        opacity: 0;
        transform: translateY(24px) scale(0.96);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

@keyframes pulseGlow {
    0%, 100% { box-shadow: 0 0 0 0 rgba(26, 95, 122, 0.4); }
    50% { box-shadow: 0 0 0 8px rgba(26, 95, 122, 0); }
}

@keyframes floatIcon {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-6px); }
}

@keyframes checkBounce {
    0% { transform: scale(0); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

/* Documents Grid - Masonry-inspired with stagger */
.documents-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 24px;
    padding: 24px;
}

.documents-grid > .document-card {
    animation: docCardReveal 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) backwards;
}

.documents-grid > .document-card:nth-child(1) { animation-delay: 0.05s; }
.documents-grid > .document-card:nth-child(2) { animation-delay: 0.1s; }
.documents-grid > .document-card:nth-child(3) { animation-delay: 0.15s; }
.documents-grid > .document-card:nth-child(4) { animation-delay: 0.2s; }
.documents-grid > .document-card:nth-child(5) { animation-delay: 0.25s; }
.documents-grid > .document-card:nth-child(6) { animation-delay: 0.3s; }

/* Document Card - Premium depth & hover */
.document-card {
    background: white;
    border: 1px solid var(--vp-slate-200);
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
}

.document-card::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 16px;
    padding: 1px;
    background: linear-gradient(135deg, transparent 40%, rgba(26, 95, 122, 0.3) 100%);
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: 0;
    transition: opacity 0.4s ease;
    pointer-events: none;
}

.document-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow:
        0 20px 40px -12px rgba(15, 23, 42, 0.15),
        0 8px 16px -8px rgba(15, 23, 42, 0.1),
        0 0 0 1px rgba(26, 95, 122, 0.1);
}

.document-card:hover::before {
    opacity: 1;
}

/* Document Icon Header - Gradient with floating animation */
.document-card-icon {
    background:
        linear-gradient(135deg, var(--vp-slate-800) 0%, var(--vp-slate-900) 60%),
        radial-gradient(circle at 80% 20%, rgba(232, 123, 53, 0.15) 0%, transparent 50%);
    padding: 28px 24px;
    text-align: center;
    color: white;
    font-size: 3rem;
    position: relative;
    overflow: hidden;
}

.document-card-icon::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 200%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.05),
        transparent
    );
    transition: left 0.6s ease;
}

.document-card:hover .document-card-icon::before {
    left: 100%;
}

.document-card-icon i {
    opacity: 0.9;
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
    transition: transform 0.3s ease;
}

.document-card:hover .document-card-icon i {
    animation: floatIcon 0.6s ease-in-out;
}

/* File type specific icon colors */
.document-card-icon .fa-file-pdf { color: #ff6b6b; }
.document-card-icon .fa-file-image { color: #4ecdc4; }
.document-card-icon .fa-file { color: #a0aec0; }

/* Document Card Body */
.document-card-body {
    padding: 20px 24px;
    position: relative;
}

/* Type Badge - Pill with subtle gradient */
.document-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    background: linear-gradient(135deg, var(--vp-primary-pale), rgba(26, 95, 122, 0.12));
    color: var(--vp-primary);
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    border-radius: 24px;
    margin-bottom: 12px;
    border: 1px solid rgba(26, 95, 122, 0.15);
    transition: all 0.3s ease;
}

.document-card:hover .document-type-badge {
    background: linear-gradient(135deg, rgba(26, 95, 122, 0.15), rgba(26, 95, 122, 0.2));
    transform: translateX(4px);
}

/* Document Name - Bold typography */
.document-name {
    font-family: var(--font-display);
    font-size: 1.0625rem;
    font-weight: 600;
    color: var(--vp-slate-900);
    margin: 0 0 6px;
    line-height: 1.35;
    word-break: break-word;
    letter-spacing: -0.01em;
}

.document-desc {
    font-size: 0.8125rem;
    color: var(--vp-slate-500);
    margin: 0 0 14px;
    line-height: 1.5;
}

/* Document Meta - Refined details */
.document-meta {
    display: flex;
    gap: 16px;
    font-size: 0.75rem;
    color: var(--vp-slate-500);
}

.document-meta span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 0;
}

.document-meta span i {
    font-size: 0.6875rem;
    opacity: 0.7;
}

/* Validity Badge - Expiry indicator */
.document-validity {
    margin-top: 14px;
}

.validity-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.75rem;
    font-weight: 500;
    padding: 6px 12px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.validity-badge.valid {
    background: linear-gradient(135deg, var(--vp-success-bg), rgba(5, 150, 105, 0.15));
    color: var(--vp-success);
    border: 1px solid rgba(5, 150, 105, 0.2);
}

.validity-badge.expired {
    background: linear-gradient(135deg, var(--vp-danger-bg), rgba(220, 38, 38, 0.15));
    color: var(--vp-danger);
    border: 1px solid rgba(220, 38, 38, 0.2);
    animation: pulseGlow 2s infinite;
    --glow-color: rgba(220, 38, 38, 0.4);
}

/* Document Card Footer - Actions bar */
.document-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 20px;
    background: linear-gradient(to bottom, var(--vp-slate-50), rgba(241, 245, 249, 0.8));
    border-top: 1px solid var(--vp-slate-100);
    backdrop-filter: blur(8px);
}

/* Verification Status - Prominent indicator */
.verification-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.8125rem;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.verification-status.status-pending {
    color: var(--vp-warning);
    background: rgba(217, 119, 6, 0.1);
}

.verification-status.status-verified {
    color: var(--vp-success);
    background: rgba(5, 150, 105, 0.1);
}

.verification-status.status-verified i {
    animation: checkBounce 0.5s ease 0.2s backwards;
}

.verification-status.status-rejected {
    color: var(--vp-danger);
    background: rgba(220, 38, 38, 0.1);
}

.verification-status.status-expired {
    color: var(--vp-slate-500);
    background: rgba(100, 116, 139, 0.1);
}

/* Document Actions - Icon buttons */
.document-actions {
    display: flex;
    gap: 4px;
}

.document-actions .btn-ghost {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.document-actions .btn-ghost:hover {
    transform: scale(1.15);
}

.document-actions .btn-ghost:hover:not(.text-danger) {
    background: var(--vp-primary-pale);
    color: var(--vp-primary);
}

.document-actions .btn-ghost.text-danger:hover {
    background: var(--vp-danger-bg);
}

/* Rejection Reason Banner */
.document-rejection-reason {
    padding: 14px 20px;
    background: linear-gradient(135deg, var(--vp-danger-bg), rgba(254, 226, 226, 0.8));
    color: var(--vp-danger);
    font-size: 0.8125rem;
    border-top: 1px solid rgba(220, 38, 38, 0.15);
    display: flex;
    align-items: flex-start;
    gap: 10px;
    line-height: 1.5;
}

.document-rejection-reason i {
    margin-top: 2px;
    flex-shrink: 0;
}

/* ============================================
   CHECKLIST - Requirements Tracker
   ============================================ */
.checklist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}

.checklist-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 18px 20px;
    background: var(--vp-slate-50);
    border-radius: 12px;
    border: 1px solid var(--vp-slate-200);
    transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    overflow: hidden;
}

.checklist-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: var(--vp-slate-300);
    transition: all 0.3s ease;
}

.checklist-item:hover {
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
}

.checklist-item.verified {
    background: linear-gradient(135deg, var(--vp-success-bg), rgba(209, 250, 229, 0.6));
    border-color: rgba(5, 150, 105, 0.25);
}

.checklist-item.verified::before {
    background: linear-gradient(to bottom, var(--vp-success), #34d399);
    width: 5px;
}

.checklist-item.uploaded {
    background: linear-gradient(135deg, var(--vp-warning-bg), rgba(254, 243, 199, 0.6));
    border-color: rgba(217, 119, 6, 0.25);
}

.checklist-item.uploaded::before {
    background: linear-gradient(to bottom, var(--vp-warning), #fbbf24);
    width: 5px;
}

.checklist-icon {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.checklist-item.verified .checklist-icon {
    background: linear-gradient(135deg, var(--vp-success), #34d399);
    color: white;
    box-shadow: 0 4px 12px rgba(5, 150, 105, 0.35);
}

.checklist-item.verified .checklist-icon i {
    animation: checkBounce 0.5s ease backwards;
}

.checklist-item.uploaded .checklist-icon {
    background: linear-gradient(135deg, var(--vp-warning), #fbbf24);
    color: white;
    box-shadow: 0 4px 12px rgba(217, 119, 6, 0.35);
}

.checklist-item.missing .checklist-icon {
    background: var(--vp-slate-200);
    color: var(--vp-slate-400);
    border: 2px dashed var(--vp-slate-300);
}

.checklist-content {
    flex: 1;
    min-width: 0;
}

.checklist-title {
    font-weight: 600;
    color: var(--vp-slate-800);
    font-size: 0.9375rem;
    margin-bottom: 2px;
}

.checklist-status {
    font-size: 0.75rem;
    font-weight: 500;
}

.checklist-item .btn-outline {
    flex-shrink: 0;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.checklist-item .btn-outline:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(26, 95, 122, 0.25);
}

/* ============================================
   MODAL - Glass-morphism & Premium Feel
   ============================================ */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    padding: 20px;
}

.modal-overlay.active {
    opacity: 1;
    visibility: visible;
}

.modal-container {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 20px;
    width: 100%;
    max-width: 540px;
    max-height: 90vh;
    overflow: hidden;
    transform: scale(0.9) translateY(40px);
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    box-shadow:
        0 25px 50px -12px rgba(15, 23, 42, 0.25),
        0 0 0 1px rgba(255, 255, 255, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.5);
}

.modal-overlay.active .modal-container {
    transform: scale(1) translateY(0);
}

.modal-container.modal-sm {
    max-width: 420px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 28px;
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    background: linear-gradient(to bottom, white, rgba(248, 250, 252, 0.5));
}

.modal-header h3 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
    font-family: var(--font-display);
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--vp-slate-900);
}

.modal-header h3 i {
    color: var(--vp-primary);
    font-size: 1.125rem;
}

.modal-header .btn-ghost {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-header .btn-ghost:hover {
    background: var(--vp-slate-100);
    transform: rotate(90deg);
}

.modal-body {
    padding: 28px;
    overflow-y: auto;
    max-height: 60vh;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 20px 28px;
    border-top: 1px solid rgba(226, 232, 240, 0.8);
    background: linear-gradient(to top, rgba(248, 250, 252, 0.9), white);
}

.modal-footer .btn {
    border-radius: 10px;
    font-weight: 600;
    padding: 12px 24px;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-footer .btn:hover {
    transform: translateY(-2px);
}

.modal-footer .btn-primary:hover {
    box-shadow: 0 8px 20px rgba(26, 95, 122, 0.35);
}

/* ============================================
   FILE UPLOAD ZONE - Glass-morphism Drop Area
   ============================================ */
.file-upload-zone {
    position: relative;
    border: 2px dashed var(--vp-slate-300);
    border-radius: 16px;
    padding: 40px 24px;
    text-align: center;
    transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    cursor: pointer;
    background:
        linear-gradient(135deg, rgba(248, 250, 252, 0.8), rgba(241, 245, 249, 0.5)),
        radial-gradient(circle at 50% 50%, rgba(26, 95, 122, 0.03) 0%, transparent 70%);
}

.file-upload-zone::before {
    content: '';
    position: absolute;
    inset: -2px;
    border-radius: 18px;
    background: linear-gradient(135deg, var(--vp-primary), var(--vp-accent));
    opacity: 0;
    z-index: -1;
    transition: opacity 0.3s ease;
}

.file-upload-zone:hover,
.file-upload-zone.dragover {
    border-color: transparent;
    background:
        linear-gradient(135deg, rgba(232, 244, 248, 0.9), rgba(26, 95, 122, 0.08)),
        radial-gradient(circle at 50% 50%, rgba(26, 95, 122, 0.08) 0%, transparent 60%);
    transform: scale(1.02);
    box-shadow: 0 8px 24px rgba(26, 95, 122, 0.15);
}

.file-upload-zone:hover::before,
.file-upload-zone.dragover::before {
    opacity: 1;
}

.file-upload-zone.dragover {
    animation: pulseGlow 1s infinite;
}

.file-upload-zone input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.file-upload-content {
    pointer-events: none;
}

.file-upload-content i {
    font-size: 3rem;
    background: linear-gradient(135deg, var(--vp-slate-400), var(--vp-primary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 16px;
    display: block;
    transition: transform 0.3s ease;
}

.file-upload-zone:hover .file-upload-content i {
    transform: translateY(-4px) scale(1.1);
}

.file-upload-content p {
    margin: 0 0 8px;
    color: var(--vp-slate-600);
    font-size: 0.9375rem;
    font-weight: 500;
}

.file-upload-content p .text-primary {
    font-weight: 600;
    text-decoration: underline;
    text-underline-offset: 3px;
}

.file-upload-content small {
    color: var(--vp-slate-400);
    font-size: 0.8125rem;
}

/* File Preview - Selected file indicator */
.file-preview {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 20px;
    background: linear-gradient(135deg, var(--vp-primary-pale), rgba(26, 95, 122, 0.1));
    border-radius: 12px;
    border: 1px solid rgba(26, 95, 122, 0.2);
    animation: docCardReveal 0.3s ease;
}

.file-preview i {
    font-size: 1.75rem;
    color: var(--vp-primary);
}

.file-preview span {
    flex: 1;
    font-weight: 600;
    color: var(--vp-slate-800);
    font-size: 0.9375rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.file-preview .btn-ghost {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    color: var(--vp-slate-500);
}

.file-preview .btn-ghost:hover {
    background: rgba(220, 38, 38, 0.1);
    color: var(--vp-danger);
}

/* ============================================
   EMPTY STATE - Refined & Inviting
   ============================================ */
.card-body > .empty-state {
    padding: 60px 40px;
}

.empty-state-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, var(--vp-slate-100), var(--vp-slate-50));
    border: 2px dashed var(--vp-slate-300);
    animation: floatIcon 3s ease-in-out infinite;
}

.empty-state-icon i {
    font-size: 2.5rem;
    background: linear-gradient(135deg, var(--vp-slate-400), var(--vp-primary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.empty-state h3 {
    font-family: var(--font-display);
    font-size: 1.375rem;
    font-weight: 600;
    letter-spacing: -0.01em;
}

.empty-state p {
    font-size: 0.9375rem;
    line-height: 1.6;
}

.empty-state .btn-primary {
    border-radius: 12px;
    padding: 14px 28px;
    font-weight: 600;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.empty-state .btn-primary:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 12px 24px rgba(26, 95, 122, 0.35);
}

/* ============================================
   FORM ENHANCEMENTS
   ============================================ */
.modal-body .form-group {
    margin-bottom: 20px;
}

.modal-body .form-label {
    font-weight: 600;
    color: var(--vp-slate-700);
    margin-bottom: 8px;
    font-size: 0.875rem;
}

.modal-body .form-control {
    border-radius: 10px;
    padding: 12px 16px;
    border: 1.5px solid var(--vp-slate-200);
    transition: all 0.25s ease;
}

.modal-body .form-control:focus {
    border-color: var(--vp-primary);
    box-shadow: 0 0 0 4px rgba(26, 95, 122, 0.1);
}

.modal-body textarea.form-control {
    resize: vertical;
    min-height: 80px;
}

/* ============================================
   RESPONSIVE REFINEMENTS
   ============================================ */
@media (max-width: 768px) {
    .documents-grid {
        grid-template-columns: 1fr;
        padding: 16px;
        gap: 16px;
    }

    .document-card-icon {
        padding: 24px 20px;
        font-size: 2.5rem;
    }

    .checklist-grid {
        grid-template-columns: 1fr;
    }

    .modal-container {
        margin: 16px;
        max-height: calc(100vh - 32px);
    }

    .modal-body {
        padding: 20px;
    }

    .file-upload-zone {
        padding: 32px 20px;
    }
}
</style>

<script>
function toggleUploadModal(docType) {
    const modal = document.getElementById('uploadModal');
    modal.classList.toggle('active');

    if (docType && modal.classList.contains('active')) {
        document.getElementById('uploadDocType').value = docType;
    }
}

function toggleDeleteModal() {
    document.getElementById('deleteModal').classList.toggle('active');
}

function confirmDelete(docID, docName) {
    document.getElementById('deleteDocID').value = docID;
    document.getElementById('deleteDocName').textContent = docName;
    toggleDeleteModal();
}

// File upload handling
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('documentFile');
const filePreview = document.getElementById('filePreview');
const uploadContent = document.querySelector('.file-upload-content');
const fileName = document.getElementById('fileName');

if (fileInput) {
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            showFilePreview(this.files[0]);
        }
    });
}

if (dropZone) {
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });
    });
}

function showFilePreview(file) {
    fileName.textContent = file.name;
    uploadContent.style.display = 'none';
    filePreview.style.display = 'flex';
}

function clearFile() {
    fileInput.value = '';
    uploadContent.style.display = 'block';
    filePreview.style.display = 'none';
}

// Filter by type
const filterType = document.getElementById('filterType');
if (filterType) {
    filterType.addEventListener('change', function() {
        const type = this.value;
        const cards = document.querySelectorAll('.document-card');

        cards.forEach(card => {
            if (!type || card.dataset.type === type) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
}

// Close modal on escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(modal => {
            modal.classList.remove('active');
        });
    }
});

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
        }
    });
});
</script>

<?php
include("x-footer.php");
?>
