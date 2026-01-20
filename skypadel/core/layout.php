<?php
/**
 * Sky Padel Client Portal - Shared Layout Components
 * Premium Athletic Modernism Design
 */

function renderHeader($pageTitle = 'Dashboard', $breadcrumb = []) {
    $email = getClientEmail();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/portal.css">
</head>
<body>
    <div class="portal-wrapper">
        <!-- Sidebar -->
        <aside class="portal-sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <img src="https://cdn.prod.website-files.com/66c705c26941f009cfd3255f/66c70de3185822b627ec80ac_SKYPADEL_INDIA_LOGO.png" alt="Sky Padel India" style="height: 48px; width: auto;">
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Overview</div>
                    <a href="<?php echo SITE_URL; ?>/dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7" rx="1"/>
                            <rect x="14" y="3" width="7" height="7" rx="1"/>
                            <rect x="3" y="14" width="7" height="7" rx="1"/>
                            <rect x="14" y="14" width="7" height="7" rx="1"/>
                        </svg>
                        Dashboard
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Projects</div>
                    <a href="<?php echo SITE_URL; ?>/projects.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'projects.php' ? 'active' : ''; ?>">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                        My Projects
                    </a>
                    <?php if (basename($_SERVER['PHP_SELF']) == 'project-detail.php'): ?>
                    <a href="#" class="nav-item active">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        Project Details
                    </a>
                    <?php endif; ?>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Documents</div>
                    <a href="<?php echo SITE_URL; ?>/quotations.php" class="nav-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['quotations.php', 'quotation-view.php']) ? 'active' : ''; ?>">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                        Quotations
                    </a>
                    <a href="<?php echo SITE_URL; ?>/contracts.php" class="nav-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['contracts.php', 'contract-sign.php']) ? 'active' : ''; ?>">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Contracts
                    </a>
                    <a href="<?php echo SITE_URL; ?>/invoices.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'invoices.php' ? 'active' : ''; ?>">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 17h6m-6-4h6M9 9h6M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Invoices
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Finances</div>
                    <a href="<?php echo SITE_URL; ?>/payments.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                        </svg>
                        Payments
                    </a>
                </div>
            </nav>

            <div class="sidebar-footer">
                <a href="<?php echo SITE_URL; ?>/logout.php" class="nav-item">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Logout
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="portal-main">
            <header class="portal-header">
                <button class="btn btn-ghost" id="sidebarToggle" style="display: none;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
                <div style="flex: 1;">
                    <h1 class="heading-3" style="color: var(--color-text);"><?php echo e($pageTitle); ?></h1>
                </div>
                <div class="flex items-center gap-md">
                    <span class="text-sm text-secondary"><?php echo e($email); ?></span>
                </div>
            </header>

            <div class="portal-content">
<?php
}

function renderFooter() {
?>
            </div>
        </main>
    </div>

    <script>
        // Mobile sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');

        if (window.innerWidth <= 768) {
            sidebarToggle.style.display = 'flex';
        }

        window.addEventListener('resize', () => {
            sidebarToggle.style.display = window.innerWidth <= 768 ? 'flex' : 'none';
            if (window.innerWidth > 768) {
                sidebar.classList.remove('open');
            }
        });

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    </script>
</body>
</html>
<?php
}

function getStatusBadgeClass($status) {
    $statusMap = [
        'Active' => 'badge-success',
        'Completed' => 'badge-success',
        'Paid' => 'badge-success',
        'Approved' => 'badge-success',
        'Pending' => 'badge-warning',
        'InProgress' => 'badge-info',
        'In Progress' => 'badge-info',
        'On Hold' => 'badge-warning',
        'Partially Paid' => 'badge-warning',
        'Sent' => 'badge-info',
        'Client Reviewing' => 'badge-info',
        'Cancelled' => 'badge-error',
        'Rejected' => 'badge-error',
        'Overdue' => 'badge-error',
        'Draft' => 'badge-neutral',
        'Lead' => 'badge-neutral',
        'Quoted' => 'badge-primary',
    ];

    return $statusMap[$status] ?? 'badge-neutral';
}

// Legacy functions for backwards compatibility
function getPortalStyles() {
    return ''; // Now using external CSS
}

function renderSidebar($activePage, $clientName, $clientEmail) {
    // Deprecated - using renderHeader() instead
}
