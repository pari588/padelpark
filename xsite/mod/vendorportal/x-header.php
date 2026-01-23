<?php
/**
 * Vendor Portal - Header/Layout Component
 */

include_once("x-vendorportal.inc.php");
vpRequireAuth();

$vendor = vpGetVendor();
$user = vpGetUser();
$stats = vpGetDashboardStats();
$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');
$currentPage = str_replace('x-', '', $currentPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Vendor Portal'; ?> | GamePark</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=Outfit:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITEURL; ?>/mod/vendorportal/inc/css/vendorportal.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="portal-layout">
        <!-- Sidebar -->
        <aside class="portal-sidebar" id="portalSidebar">
            <div class="portal-sidebar-header">
                <a href="<?php echo VP_BASEURL; ?>/vendorportal/dashboard" class="portal-logo">
                    <div class="portal-logo-icon">GP</div>
                    <div class="portal-logo-text">
                        GamePark
                        <span>Vendor Portal</span>
                    </div>
                </a>
            </div>

            <nav class="portal-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main Menu</div>
                    <a href="<?php echo VP_BASEURL; ?>/vendorportal/dashboard" class="nav-item <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                        <span class="nav-item-icon"><i class="fas fa-th-large"></i></span>
                        Dashboard
                    </a>
                    <a href="<?php echo VP_BASEURL; ?>/vendorportal/rfq-list" class="nav-item <?php echo $currentPage === 'rfq-list' ? 'active' : ''; ?>">
                        <span class="nav-item-icon"><i class="fas fa-file-invoice"></i></span>
                        Available RFQs
                        <?php if ($stats['availableRFQs'] > 0): ?>
                            <span class="nav-item-badge"><?php echo $stats['availableRFQs']; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?php echo VP_BASEURL; ?>/vendorportal/quotes" class="nav-item <?php echo $currentPage === 'quotes' ? 'active' : ''; ?>">
                        <span class="nav-item-icon"><i class="fas fa-file-alt"></i></span>
                        My Quotes
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Orders</div>
                    <a href="<?php echo VP_BASEURL; ?>/vendorportal/orders" class="nav-item <?php echo $currentPage === 'orders' ? 'active' : ''; ?>">
                        <span class="nav-item-icon"><i class="fas fa-shopping-cart"></i></span>
                        Purchase Orders
                        <?php if ($stats['awardedOrders'] > 0): ?>
                            <span class="nav-item-badge"><?php echo $stats['awardedOrders']; ?></span>
                        <?php endif; ?>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Account</div>
                    <a href="<?php echo VP_BASEURL; ?>/vendorportal/profile" class="nav-item <?php echo $currentPage === 'profile' ? 'active' : ''; ?>">
                        <span class="nav-item-icon"><i class="fas fa-building"></i></span>
                        Company Profile
                    </a>
                    <a href="<?php echo VP_BASEURL; ?>/vendorportal/documents" class="nav-item <?php echo $currentPage === 'documents' ? 'active' : ''; ?>">
                        <span class="nav-item-icon"><i class="fas fa-folder-open"></i></span>
                        Documents
                    </a>
                </div>
            </nav>

            <div class="portal-sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-user-avatar">
                        <?php echo strtoupper(substr($user['fullName'] ?? 'V', 0, 1)); ?>
                    </div>
                    <div class="sidebar-user-info">
                        <div class="sidebar-user-name"><?php echo vpClean($user['fullName'] ?? 'Vendor'); ?></div>
                        <div class="sidebar-user-role"><?php echo vpClean($vendor['vendorCode'] ?? ''); ?></div>
                    </div>
                    <a href="<?php echo VP_BASEURL; ?>/vendorportal/logout" class="btn btn-ghost btn-icon" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="portal-main">
            <header class="portal-header">
                <div class="portal-header-left">
                    <button class="btn btn-ghost btn-icon d-md-none" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <nav class="portal-breadcrumb">
                        <a href="<?php echo VP_BASEURL; ?>/vendorportal/dashboard">Portal</a>
                        <span>/</span>
                        <span class="portal-breadcrumb-current"><?php echo $pageTitle ?? 'Dashboard'; ?></span>
                    </nav>
                </div>
                <div class="portal-header-right">
                    <div class="header-notification" id="notificationDropdown">
                        <button class="btn btn-ghost btn-icon" onclick="toggleNotifications(event)">
                            <i class="fas fa-bell"></i>
                        </button>
                        <?php
                        $totalNotifications = $stats['availableRFQs'] + $stats['pendingQuotes'];
                        if ($totalNotifications > 0):
                        ?>
                            <span class="badge"><?php echo $totalNotifications; ?></span>
                        <?php endif; ?>

                        <!-- Notification Dropdown -->
                        <div class="notification-dropdown" id="notificationPanel">
                            <div class="notification-header">
                                <h4>Notifications</h4>
                                <span class="notification-count"><?php echo $totalNotifications; ?> new</span>
                            </div>
                            <div class="notification-list">
                                <?php if ($stats['availableRFQs'] > 0): ?>
                                <a href="<?php echo VP_BASEURL; ?>/vendorportal/rfq-list" class="notification-item">
                                    <div class="notification-icon notification-icon-primary">
                                        <i class="fas fa-file-invoice"></i>
                                    </div>
                                    <div class="notification-content">
                                        <div class="notification-title"><?php echo $stats['availableRFQs']; ?> New RFQs Available</div>
                                        <div class="notification-text">Submit your quotes before the deadline</div>
                                    </div>
                                    <i class="fas fa-chevron-right notification-arrow"></i>
                                </a>
                                <?php endif; ?>

                                <?php if ($stats['pendingQuotes'] > 0): ?>
                                <a href="<?php echo VP_BASEURL; ?>/vendorportal/quotes" class="notification-item">
                                    <div class="notification-icon notification-icon-warning">
                                        <i class="fas fa-edit"></i>
                                    </div>
                                    <div class="notification-content">
                                        <div class="notification-title"><?php echo $stats['pendingQuotes']; ?> Draft Quotes</div>
                                        <div class="notification-text">Complete and submit your pending quotes</div>
                                    </div>
                                    <i class="fas fa-chevron-right notification-arrow"></i>
                                </a>
                                <?php endif; ?>

                                <?php if ($stats['awardedOrders'] > 0): ?>
                                <a href="<?php echo VP_BASEURL; ?>/vendorportal/orders" class="notification-item">
                                    <div class="notification-icon notification-icon-success">
                                        <i class="fas fa-trophy"></i>
                                    </div>
                                    <div class="notification-content">
                                        <div class="notification-title"><?php echo $stats['awardedOrders']; ?> Awarded Orders</div>
                                        <div class="notification-text">View your won contracts</div>
                                    </div>
                                    <i class="fas fa-chevron-right notification-arrow"></i>
                                </a>
                                <?php endif; ?>

                                <?php if ($totalNotifications == 0): ?>
                                <div class="notification-empty">
                                    <i class="fas fa-bell-slash"></i>
                                    <p>No new notifications</p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="notification-footer">
                                <a href="<?php echo VP_BASEURL; ?>/vendorportal/dashboard">View Dashboard</a>
                            </div>
                        </div>
                    </div>
                    <a href="<?php echo VP_BASEURL; ?>/vendorportal/profile" class="btn btn-ghost btn-icon">
                        <i class="fas fa-user"></i>
                    </a>
                </div>
            </header>

            <div class="portal-content animate-fade-in">
