            </div><!-- /.portal-content -->

            <footer style="padding: var(--space-lg) var(--space-xl); text-align: center; color: var(--vp-slate-400); font-size: 0.8125rem; border-top: 1px solid var(--vp-slate-100);">
                <p style="margin: 0;">&copy; <?php echo date('Y'); ?> GamePark. All rights reserved. | <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>
            </footer>
        </main>
    </div>

    <style>
    /* Notification Dropdown Styles */
    .header-notification {
        position: relative;
    }

    .notification-dropdown {
        position: absolute;
        top: calc(100% + 12px);
        right: 0;
        width: 360px;
        background: white;
        border-radius: 16px;
        box-shadow:
            0 20px 40px rgba(15, 23, 42, 0.15),
            0 0 0 1px rgba(15, 23, 42, 0.05);
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px) scale(0.95);
        transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        z-index: 1000;
        overflow: hidden;
    }

    .notification-dropdown.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0) scale(1);
    }

    .notification-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid var(--vp-slate-100);
        background: linear-gradient(to bottom, white, var(--vp-slate-50));
    }

    .notification-header h4 {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
        color: var(--vp-slate-900);
    }

    .notification-count {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--vp-primary);
        background: var(--vp-primary-pale);
        padding: 4px 10px;
        border-radius: 20px;
    }

    .notification-list {
        max-height: 320px;
        overflow-y: auto;
    }

    .notification-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 20px;
        text-decoration: none;
        border-bottom: 1px solid var(--vp-slate-100);
        transition: all 0.2s ease;
    }

    .notification-item:hover {
        background: var(--vp-slate-50);
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .notification-icon-primary {
        background: var(--vp-primary-pale);
        color: var(--vp-primary);
    }

    .notification-icon-warning {
        background: var(--vp-warning-bg);
        color: var(--vp-warning);
    }

    .notification-icon-success {
        background: var(--vp-success-bg);
        color: var(--vp-success);
    }

    .notification-content {
        flex: 1;
        min-width: 0;
    }

    .notification-title {
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--vp-slate-800);
        margin-bottom: 2px;
    }

    .notification-text {
        font-size: 0.75rem;
        color: var(--vp-slate-500);
    }

    .notification-arrow {
        color: var(--vp-slate-300);
        font-size: 0.75rem;
        transition: transform 0.2s ease;
    }

    .notification-item:hover .notification-arrow {
        transform: translateX(4px);
        color: var(--vp-primary);
    }

    .notification-empty {
        padding: 40px 20px;
        text-align: center;
        color: var(--vp-slate-400);
    }

    .notification-empty i {
        font-size: 2rem;
        margin-bottom: 12px;
        display: block;
    }

    .notification-empty p {
        margin: 0;
        font-size: 0.875rem;
    }

    .notification-footer {
        padding: 14px 20px;
        text-align: center;
        background: var(--vp-slate-50);
        border-top: 1px solid var(--vp-slate-100);
    }

    .notification-footer a {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--vp-primary);
        text-decoration: none;
    }

    .notification-footer a:hover {
        text-decoration: underline;
    }

    /* Bell animation when has notifications */
    .header-notification .badge ~ .btn-ghost i {
        animation: bellRing 0.5s ease;
    }

    @keyframes bellRing {
        0%, 100% { transform: rotate(0); }
        20%, 60% { transform: rotate(15deg); }
        40%, 80% { transform: rotate(-15deg); }
    }
    </style>

    <script>
        function toggleSidebar() {
            document.getElementById('portalSidebar').classList.toggle('open');
        }

        function toggleNotifications(e) {
            e.stopPropagation();
            const panel = document.getElementById('notificationPanel');
            panel.classList.toggle('active');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('portalSidebar');
            const notificationDropdown = document.getElementById('notificationDropdown');
            const notificationPanel = document.getElementById('notificationPanel');

            // Close sidebar on mobile
            const isClickInsideSidebar = sidebar.contains(e.target);
            const isToggleBtn = e.target.closest('[onclick="toggleSidebar()"]');
            if (!isClickInsideSidebar && !isToggleBtn && sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
            }

            // Close notification dropdown
            if (!notificationDropdown.contains(e.target) && notificationPanel.classList.contains('active')) {
                notificationPanel.classList.remove('active');
            }
        });

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('notificationPanel').classList.remove('active');
            }
        });

        // CSRF token for all AJAX requests
        const csrfToken = '<?php echo vpGetCSRFToken(); ?>';

        $.ajaxSetup({
            beforeSend: function(xhr, settings) {
                if (settings.type === 'POST') {
                    if (settings.data) {
                        settings.data += '&vpToken=' + csrfToken;
                    } else {
                        settings.data = 'vpToken=' + csrfToken;
                    }
                }
            }
        });
    </script>
</body>
</html>
