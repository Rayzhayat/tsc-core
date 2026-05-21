/**
 * ========================================
 * NOTIFICATION SYSTEM - REAL-TIME POLLING
 * ========================================
 * Version: 2.0
 * Author: Your Name
 * Description: Real-time notification system with polling
 */

// ✅ WRAP dengan check jQuery dulu
if (typeof jQuery !== 'undefined') {
    (function($) {
        'use strict';

        // ========================================
        // CONFIGURATION
        // ========================================
        const POLL_INTERVAL = 10000; // 10 seconds
        const MAX_NOTIFICATIONS_DISPLAY = 10;
        const TOAST_DURATION = 3000; // 3 seconds
        
        let pollTimer = null;
        let originalTitle = document.title;

        // ========================================
        // INITIALIZE ON DOCUMENT READY
        // ========================================
        $(document).ready(function() {
            console.log('🔔 Notification System: Starting...');
            initNotificationSystem();
        });

        /**
         * Initialize notification system
         */
        function initNotificationSystem() {
            // Check if required elements exist
            if ($('#notifCount').length === 0 || $('#notificationList').length === 0) {
                console.warn('🔔 Notification System: Required elements not found, skipping initialization');
                return;
            }

            console.log('🔔 Notification System: Initialized successfully');
            
            // Load notifications immediately
            loadNotifications();
            
            // Start polling
            startPolling();
            
            // Bind event handlers
            bindEventHandlers();
        }

        /**
         * Start polling notifications
         */
        function startPolling() {
            if (pollTimer) {
                clearInterval(pollTimer);
            }
            
            pollTimer = setInterval(function() {
                loadNotifications();
            }, POLL_INTERVAL);
            
            console.log(`🔔 Notification Polling: Started (every ${POLL_INTERVAL/1000}s)`);
        }

        /**
         * Stop polling
         */
        function stopPolling() {
            if (pollTimer) {
                clearInterval(pollTimer);
                pollTimer = null;
                console.log('🔔 Notification Polling: Stopped');
            }
        }

        /**
         * Load notifications from server
         */
        function loadNotifications() {
            // Check if BASE_URL is defined
            if (typeof BASE_URL === 'undefined') {
                console.error('🔔 Error: BASE_URL is not defined');
                return;
            }

            $.ajax({
                url: BASE_URL + 'notification/get_notifications',
                method: 'GET',
                dataType: 'json',
                data: {
                    limit: MAX_NOTIFICATIONS_DISPLAY,
                    unread_only: false
                },
                success: function(response) {
                    if (response.success) {
                        updateNotificationBadge(response.unread_count);
                        renderNotifications(response.notifications);
                    } else {
                        console.warn('🔔 Server returned error:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('🔔 Error loading notifications:', error);
                    // Don't show error to user, just log it
                }
            });
        }

        /**
         * Update notification badge
         */
        function updateNotificationBadge(count) {
            const badge = $('#notifCount');
            
            if (!badge.length) return;

            if (count > 0) {
                badge.text(count > 99 ? '99+' : count).show();
                
                // Update page title with count
                document.title = `(${count}) ${originalTitle}`;
            } else {
                badge.hide();
                document.title = originalTitle;
            }
        }

        /**
         * Render notifications in dropdown
         */
        function renderNotifications(notifications) {
            const container = $('#notificationList');
            
            if (!container.length) return;

            if (!notifications || notifications.length === 0) {
                container.html(`
                    <div class="dropdown-item text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        <small>Tidak ada notifikasi</small>
                    </div>
                `);
                return;
            }
            
            let html = '';
            
            notifications.forEach(function(notif) {
                const isUnread = notif.is_read == 0;
                const iconColor = getIconColor(notif.type);
                const timeAgo = formatTimeAgo(notif.created_at);
                
                html += `
                    <a class="dropdown-item d-flex align-items-center notification-item ${isUnread ? 'unread bg-light' : ''}" 
                       href="javascript:void(0)" 
                       data-id="${notif.id}" 
                       data-url="${escapeHtml(notif.url || '#')}">
                        <div class="mr-3">
                            <div class="icon-circle bg-${iconColor}">
                                <i class="fas ${notif.icon || 'fa-bell'} text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="small text-gray-500">${timeAgo}</div>
                            <strong class="font-weight-bold">${escapeHtml(notif.title)}</strong>
                            <div class="small text-gray-700">${escapeHtml(notif.message)}</div>
                        </div>
                        ${isUnread ? '<div class="ml-2"><span class="badge badge-primary badge-pill">Baru</span></div>' : ''}
                    </a>
                `;
            });
            
            container.html(html);
            
            // Add click handlers to notification items
            $('.notification-item').off('click').on('click', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const url = $(this).data('url');
                markAsRead(id, url);
            });
        }

        /**
         * Mark notification as read
         */
        function markAsRead(id, redirectUrl) {
            if (typeof BASE_URL === 'undefined') {
                console.error('🔔 Error: BASE_URL is not defined');
                return;
            }

            $.ajax({
                url: BASE_URL + 'notification/mark_as_read/' + id,
                method: 'POST',
                success: function(response) {
                    if (response.success) {
                        // Reload notifications
                        loadNotifications();
                        
                        // Redirect if URL exists and is valid
                        if (redirectUrl && redirectUrl !== '#' && redirectUrl !== 'javascript:void(0)') {
                            window.location.href = redirectUrl;
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('🔔 Error marking as read:', error);
                }
            });
        }

        /**
         * Mark all notifications as read
         */
        function markAllAsRead() {
            if (typeof BASE_URL === 'undefined') {
                console.error('🔔 Error: BASE_URL is not defined');
                return;
            }

            $.ajax({
                url: BASE_URL + 'notification/mark_all_as_read',
                method: 'POST',
                success: function(response) {
                    if (response.success) {
                        loadNotifications();
                        showToast('Semua notifikasi telah ditandai dibaca', 'success');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('🔔 Error marking all as read:', error);
                    showToast('Gagal menandai semua notifikasi', 'danger');
                }
            });
        }

        /**
         * Bind event handlers
         */
        function bindEventHandlers() {
            // Mark all as read button
            $(document).on('click', '#markAllRead', function(e) {
                e.preventDefault();
                e.stopPropagation();
                markAllAsRead();
            });
            
            // View all notifications button
            $(document).on('click', '#viewAllNotif', function(e) {
                e.preventDefault();
                
                // Check if notification page exists
                if (typeof BASE_URL !== 'undefined') {
                    window.location.href = BASE_URL + 'notification';
                } else {
                    showToast('Fitur "Lihat Semua" sedang dalam pengembangan', 'info');
                }
            });
            
            // Stop polling when page is hidden (save resources)
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    console.log('🔔 Page hidden, stopping polling');
                    stopPolling();
                } else {
                    console.log('🔔 Page visible, resuming polling');
                    loadNotifications(); // Load immediately
                    startPolling();
                }
            });

            // Cleanup on page unload
            window.addEventListener('beforeunload', function() {
                stopPolling();
            });
        }

        /**
         * Helper: Get icon color based on notification type
         */
        function getIconColor(type) {
            const colors = {
                'info': 'primary',
                'success': 'success',
                'warning': 'warning',
                'danger': 'danger',
                'error': 'danger',
                'primary': 'info'
            };
            return colors[type] || 'primary';
        }

        /**
         * Helper: Format time ago
         */
        function formatTimeAgo(datetime) {
            if (!datetime) return 'Tidak diketahui';

            try {
                const now = new Date();
                const past = new Date(datetime);
                
                // Check if valid date
                if (isNaN(past.getTime())) {
                    return 'Tidak diketahui';
                }

                const diffMs = now - past;
                const diffSecs = Math.floor(diffMs / 1000);
                const diffMins = Math.floor(diffMs / 60000);
                const diffHours = Math.floor(diffMs / 3600000);
                const diffDays = Math.floor(diffMs / 86400000);
                
                if (diffSecs < 60) return 'Baru saja';
                if (diffMins < 60) return diffMins + ' menit lalu';
                if (diffHours < 24) return diffHours + ' jam lalu';
                if (diffDays < 7) return diffDays + ' hari lalu';
                if (diffDays < 30) return Math.floor(diffDays / 7) + ' minggu lalu';
                
                return past.toLocaleDateString('id-ID', { 
                    day: 'numeric', 
                    month: 'short', 
                    year: 'numeric' 
                });
            } catch (error) {
                console.error('Error formatting date:', error);
                return 'Tidak diketahui';
            }
        }

        /**
         * Helper: Escape HTML to prevent XSS
         */
        function escapeHtml(text) {
            if (!text) return '';
            
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            
            return String(text).replace(/[&<>"']/g, function(m) {
                return map[m];
            });
        }

        /**
         * Helper: Show toast notification
         */
        function showToast(message, type) {
            type = type || 'info';
            
            // Remove existing toasts
            $('.notification-toast').remove();
            
            const toast = $(`
                <div class="alert alert-${type} alert-dismissible fade show notification-toast position-fixed shadow-lg" 
                     style="top: 80px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px;">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-${getToastIcon(type)} mr-2"></i>
                        <span>${escapeHtml(message)}</span>
                    </div>
                </div>
            `);
            
            $('body').append(toast);
            
            // Auto hide after duration
            setTimeout(function() {
                toast.fadeOut(400, function() {
                    $(this).remove();
                });
            }, TOAST_DURATION);
        }

        /**
         * Helper: Get toast icon based on type
         */
        function getToastIcon(type) {
            const icons = {
                'success': 'check-circle',
                'danger': 'exclamation-triangle',
                'warning': 'exclamation-circle',
                'info': 'info-circle',
                'error': 'times-circle'
            };
            return icons[type] || 'bell';
        }

        // ========================================
        // EXPOSE PUBLIC API (Optional)
        // ========================================
        window.NotificationSystem = {
            reload: loadNotifications,
            markAsRead: markAsRead,
            markAllAsRead: markAllAsRead,
            showToast: showToast,
            startPolling: startPolling,
            stopPolling: stopPolling
        };

        console.log('🔔 Notification System: Public API exposed as window.NotificationSystem');

    })(jQuery);

} else {
    // jQuery not loaded yet
    console.error('🔔 Notification System: jQuery is not loaded!');
    console.error('🔔 Make sure jQuery is loaded BEFORE notification.js');
}