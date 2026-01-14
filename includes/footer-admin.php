        </div><!-- /.admin-content -->
        </main>
        </div><!-- /.admin-layout -->

        <footer class="admin-footer-minimal">
            <div class="footer-container">
                <div class="footer-left">
                    <p>&copy; <?php echo date('Y'); ?> <strong>Babybib Admin</strong>. All rights reserved.</p>
                </div>
                <div class="footer-right">
                    <a href="<?php echo SITE_URL; ?>" class="footer-link" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        <?php echo $currentLang === 'th' ? 'ไปยังหน้าเว็บไซต์' : 'Visit Website'; ?>
                    </a>
                </div>
            </div>
        </footer>

        <style>
            .admin-footer-minimal {
                background: linear-gradient(135deg, #1e1e2d 0%, #2d2b52 100%);
                padding: 20px 40px;
                margin-left: 280px;
                transition: all var(--transition);
                color: rgba(255, 255, 255, 0.7);
                border-top: 1px solid rgba(139, 92, 246, 0.15);
                position: relative;
                overflow: hidden;
            }

            .admin-footer-minimal::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 1px;
                background: linear-gradient(90deg, transparent, rgba(139, 92, 246, 0.3), transparent);
            }

            .footer-container {
                display: flex;
                justify-content: space-between;
                align-items: center;
                position: relative;
                z-index: 1;
            }

            .footer-left {
                display: flex;
                align-items: center;
                gap: 16px;
            }

            .footer-brand {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .footer-brand-icon {
                width: 32px;
                height: 32px;
                background: var(--primary-gradient);
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 14px;
                color: white;
                box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
            }

            .footer-left p {
                font-size: 0.8rem;
                color: rgba(255, 255, 255, 0.5);
                margin: 0;
            }

            .footer-left p strong {
                color: white;
                font-weight: 700;
            }

            .footer-right {
                display: flex;
                align-items: center;
                gap: 20px;
            }

            .footer-links {
                display: flex;
                gap: 16px;
            }

            .footer-link {
                font-size: 0.75rem;
                color: rgba(255, 255, 255, 0.5);
                text-decoration: none;
                transition: all 0.2s ease;
                display: flex;
                align-items: center;
                gap: 6px;
            }

            .footer-link:hover {
                color: var(--primary);
            }

            .footer-divider {
                width: 1px;
                height: 20px;
                background: rgba(255, 255, 255, 0.1);
            }

            .system-status {
                display: flex;
                align-items: center;
                gap: 10px;
                font-size: 0.7rem;
                font-weight: 700;
                color: #10B981;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                background: rgba(16, 185, 129, 0.1);
                padding: 6px 14px;
                border-radius: 20px;
                border: 1px solid rgba(16, 185, 129, 0.2);
            }

            .status-dot {
                width: 8px;
                height: 8px;
                background-color: #10B981;
                border-radius: 50%;
                animation: status-pulse 2s infinite;
            }

            @keyframes status-pulse {

                0%,
                100% {
                    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
                }

                50% {
                    box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
                }
            }

            .version-tag {
                font-size: 0.7rem;
                font-weight: 700;
                color: rgba(139, 92, 246, 0.9);
                background: rgba(139, 92, 246, 0.15);
                padding: 6px 12px;
                border-radius: 20px;
                border: 1px solid rgba(139, 92, 246, 0.2);
                letter-spacing: 0.05em;
            }

            @media (max-width: 1024px) {
                .admin-footer-minimal {
                    margin-left: 80px;
                    padding: 16px 24px;
                }

                .footer-links {
                    display: none;
                }
            }

            @media (max-width: 768px) {
                .admin-footer-minimal {
                    margin-left: 0;
                    padding: 16px 20px;
                }

                .footer-container {
                    flex-direction: column;
                    gap: 16px;
                    text-align: center;
                }

                .footer-left {
                    flex-direction: column;
                    gap: 12px;
                }

                .footer-right {
                    flex-wrap: wrap;
                    justify-content: center;
                }
            }
        </style>

        <!-- Feedback Modal (Shared logic but separate UI if needed, currently using shared) -->
        <div id="feedback-modal" class="feedback-modal-overlay" style="display: none;">
            <div class="feedback-modal-content">
                <div class="feedback-modal-header">
                    <h3><i class="fas fa-comment"></i> <?php echo $currentLang === 'th' ? 'ส่งข้อเสนอแนะ' : 'Send Feedback'; ?></h3>
                    <button class="feedback-modal-close" onclick="closeFeedbackModal()">&times;</button>
                </div>
                <div class="feedback-modal-body">
                    <form id="feedback-form">
                        <div class="feedback-form-group">
                            <label><?php echo $currentLang === 'th' ? 'ประเภท' : 'Type'; ?></label>
                            <select id="feedback-type" required>
                                <option value=""><?php echo $currentLang === 'th' ? '-- เลือกประเภท --' : '-- Select Type --'; ?></option>
                                <option value="ข้อเสนอแนะ"><?php echo $currentLang === 'th' ? 'ข้อเสนอแนะ' : 'Suggestion'; ?></option>
                                <option value="แจ้งปัญหา"><?php echo $currentLang === 'th' ? 'แจ้งปัญหา' : 'Report Issue'; ?></option>
                                <option value="คำถาม"><?php echo $currentLang === 'th' ? 'คำถาม' : 'Question'; ?></option>
                                <option value="อื่นๆ"><?php echo $currentLang === 'th' ? 'อื่นๆ' : 'Other'; ?></option>
                            </select>
                        </div>
                        <div class="feedback-form-group">
                            <label><?php echo $currentLang === 'th' ? 'รายละเอียด' : 'Details'; ?></label>
                            <textarea id="feedback-message" rows="5" placeholder="<?php echo $currentLang === 'th' ? 'กรุณากรอกรายละเอียด...' : 'Please enter details...'; ?>" required></textarea>
                        </div>
                        <div class="feedback-form-actions">
                            <button type="button" class="feedback-btn-cancel" onclick="closeFeedbackModal()"><?php echo $currentLang === 'th' ? 'ยกเลิก' : 'Cancel'; ?></button>
                            <button type="submit" class="feedback-btn-submit">
                                <i class="fas fa-paper-plane"></i>
                                <?php echo $currentLang === 'th' ? 'ส่งข้อเสนอแนะ' : 'Send'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <style>
            /* Admin Feedback Modal Specific Styles */
            .feedback-modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(4px);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .feedback-modal-overlay.show {
                opacity: 1;
            }

            .feedback-modal-content {
                background: white;
                border-radius: 20px;
                width: 90%;
                max-width: 450px;
                box-shadow: var(--shadow-xl);
                transform: scale(0.9) translateY(20px);
                transition: transform 0.3s ease;
            }

            .feedback-modal-overlay.show .feedback-modal-content {
                transform: scale(1) translateY(0);
            }

            .feedback-modal-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 20px 24px;
                border-bottom: 1px solid var(--gray-50);
            }

            .feedback-modal-header h3 {
                font-size: 1.15rem;
                font-weight: 800;
                color: var(--text-primary);
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .feedback-modal-header h3 i {
                color: var(--primary);
            }

            .feedback-modal-close {
                width: 32px;
                height: 32px;
                border: none;
                background: var(--gray-100);
                border-radius: 8px;
                font-size: 20px;
                cursor: pointer;
                color: var(--text-secondary);
                transition: all 0.2s;
            }

            .feedback-modal-close:hover {
                background: var(--danger);
                color: white;
            }

            .feedback-modal-body {
                padding: 24px;
            }

            .feedback-form-group {
                margin-bottom: 20px;
            }

            .feedback-form-group label {
                display: block;
                font-size: 0.85rem;
                font-weight: 700;
                color: var(--text-secondary);
                margin-bottom: 8px;
            }

            .feedback-form-group select,
            .feedback-form-group textarea {
                width: 100%;
                padding: 12px;
                border: 1px solid var(--gray-200);
                border-radius: 12px;
                font-size: 14px;
                background: var(--gray-50);
                transition: all 0.2s;
            }

            .feedback-form-group select:focus,
            .feedback-form-group textarea:focus {
                outline: none;
                border-color: var(--primary);
                background: white;
                box-shadow: 0 0 0 3px var(--primary-light);
            }

            .feedback-form-actions {
                display: flex;
                gap: 12px;
                justify-content: flex-end;
                margin-top: 24px;
            }

            .feedback-btn-cancel {
                padding: 10px 20px;
                background: var(--gray-100);
                border: none;
                border-radius: 10px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s;
            }

            .feedback-btn-submit {
                padding: 10px 24px;
                background: var(--primary);
                color: white;
                border: none;
                border-radius: 10px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 8px;
                transition: all 0.2s;
            }
        </style>

        <script>
            function toggleAdminSidebar() {
                document.getElementById('admin-sidebar').classList.toggle('open');
            }

            function changeLanguage(lang) {
                const url = new URL(window.location);
                url.searchParams.set('lang', lang);
                window.location = url.toString();
            }
            async function adminLogout() {
                try {
                    const res = await API.post('<?php echo SITE_URL; ?>/api/auth/logout.php');
                    if (res.success) {
                        Toast.success('<?php echo $currentLang === "th" ? "ออกจากระบบสำเร็จ! ไว้กลับมาใช้งานอีกนะครับ 👋" : "Logged out successfully! See you again soon 👋"; ?>');
                        setTimeout(() => {
                            window.location.href = '<?php echo SITE_URL; ?>';
                        }, 1500);
                    }
                } catch (e) {
                    window.location.href = '<?php echo SITE_URL; ?>';
                }
            }

            // Feedback Functions
            function openFeedbackModal() {
                const m = document.getElementById('feedback-modal');
                m.style.display = 'flex';
                setTimeout(() => m.classList.add('show'), 10);
                document.body.style.overflow = 'hidden';
            }

            function closeFeedbackModal() {
                const m = document.getElementById('feedback-modal');
                m.classList.remove('show');
                setTimeout(() => {
                    m.style.display = 'none';
                    document.body.style.overflow = '';
                    document.getElementById('feedback-form').reset();
                }, 300);
            }

            function showFeedbackModal() {
                openFeedbackModal();
            }

            document.getElementById('feedback-modal')?.addEventListener('click', function(e) {
                if (e.target === this) closeFeedbackModal();
            });
            document.getElementById('feedback-form')?.addEventListener('submit', async function(e) {
                e.preventDefault();
                const type = document.getElementById('feedback-type').value;
                const message = document.getElementById('feedback-message').value;
                const btn = this.querySelector('.feedback-btn-submit');
                const originalHtml = btn.innerHTML;

                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

                try {
                    const res = await API.post('<?php echo SITE_URL; ?>/api/feedback/create.php', {
                        subject: type,
                        message: message
                    });
                    if (res.success) {
                        Toast.success('<?php echo $currentLang === "th" ? "ส่งข้อเสนอแนะสำเร็จ" : "Feedback sent successfully"; ?>');
                        closeFeedbackModal();
                    }
                } catch (err) {
                    Toast.error('Error sending feedback');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            });

            if (window.innerWidth <= 1024) {
                const toggle = document.getElementById('sidebar-toggle');
                if (toggle) toggle.style.display = 'block';
            }

            // Fetch notification count on load
            async function fetchNotificationCount() {
                try {
                    const res = await fetch('<?php echo SITE_URL; ?>/api/admin/notifications.php');
                    const data = await res.json();
                    if (data.success && data.count > 0) {
                        const badge = document.getElementById('notif-badge');
                        if (badge) {
                            badge.textContent = data.count > 9 ? '9+' : data.count;
                            badge.style.display = 'flex';
                        }
                    }
                } catch (e) {
                    console.log('Could not fetch notifications');
                }
            }

            // Helper: Escape HTML
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text || '';
                return div.innerHTML;
            }

            // Notification dropdown toggle
            function toggleNotifDropdown(e) {
                e.stopPropagation();
                const menu = document.getElementById('notif-dropdown-menu');
                const isActive = menu.classList.contains('active');

                // Close all other dropdowns
                document.querySelectorAll('.admin-user-menu.active').forEach(el => el.classList.remove('active'));

                if (!isActive) {
                    menu.classList.add('active');
                    loadNotifications();
                } else {
                    menu.classList.remove('active');
                }
            }

            // Load notifications into dropdown
            async function loadNotifications() {
                const body = document.getElementById('notif-dropdown-body');
                try {
                    const res = await fetch('<?php echo SITE_URL; ?>/api/admin/notifications.php?action=list');
                    const data = await res.json();

                    if (data.success && data.notifications && data.notifications.length > 0) {
                        let html = '';
                        data.notifications.slice(0, 5).forEach(n => {
                            const iconClass = n.type === 'feedback' ? 'fas fa-comment-dots' :
                                n.type === 'user' ? 'fas fa-user-plus' : 'fas fa-bell';
                            const typeClass = n.type || 'system';
                            const link = n.link ? '<?php echo SITE_URL; ?>' + n.link : '<?php echo SITE_URL; ?>/admin/notifications.php';
                            const unread = !n.is_read ? 'unread' : '';

                            html += `
                                <a href="${link}" class="notif-dropdown-item ${unread}">
                                    <div class="notif-dropdown-icon ${typeClass}">
                                        <i class="${iconClass}"></i>
                                    </div>
                                    <div class="notif-dropdown-content">
                                        <div class="notif-dropdown-title">${escapeHtml(n.title)}</div>
                                        <div class="notif-dropdown-time">${formatTimeAgo(n.created_at)}</div>
                                    </div>
                                </a>
                            `;
                        });
                        body.innerHTML = html;
                    } else {
                        body.innerHTML = `
                            <div class="notif-dropdown-empty">
                                <i class="fas fa-bell-slash"></i>
                                <div><?php echo $currentLang === 'th' ? 'ไม่มีการแจ้งเตือน' : 'No notifications'; ?></div>
                            </div>
                        `;
                    }
                } catch (e) {
                    body.innerHTML = '<div class="notif-dropdown-empty">Error loading</div>';
                }
            }

            // Format time ago
            function formatTimeAgo(dateStr) {
                const date = new Date(dateStr);
                const now = new Date();
                const diff = Math.floor((now - date) / 1000);

                if (diff < 60) return '<?php echo $currentLang === "th" ? "เมื่อสักครู่" : "Just now"; ?>';
                if (diff < 3600) return Math.floor(diff / 60) + ' <?php echo $currentLang === "th" ? "นาทีที่แล้ว" : "min ago"; ?>';
                if (diff < 86400) return Math.floor(diff / 3600) + ' <?php echo $currentLang === "th" ? "ชั่วโมงที่แล้ว" : "hr ago"; ?>';
                return Math.floor(diff / 86400) + ' <?php echo $currentLang === "th" ? "วันที่แล้ว" : "days ago"; ?>';
            }

            // Initialize
            fetchNotificationCount();

            // Close dropdown on outside click
            document.addEventListener('click', function(e) {
                const dropdown = document.getElementById('notif-dropdown');
                const menu = document.getElementById('notif-dropdown-menu');
                if (dropdown && !dropdown.contains(e.target)) {
                    menu?.classList.remove('active');
                }
            });
        </script>

        <!-- Scripts -->
        <?php if (isset($extraScripts)) echo $extraScripts; ?>
        </body>

        </html>