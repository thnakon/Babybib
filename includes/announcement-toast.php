<?php

/**
 * Babybib - Announcement Toast Component
 * =====================================
 */

if (!isset($db)) {
    try {
        $db = getDB();
    } catch (Exception $e) {
        return;
    }
}

// Get active announcements
try {
    $currentLang = $_SESSION['lang'] ?? 'th';
    $titleCol = $currentLang === 'th' ? 'title_th' : 'title_en';
    $contentCol = $currentLang === 'th' ? 'content_th' : 'content_en';

    // Get the latest active announcement
    $stmt = $db->query("SELECT id, $titleCol as title, $contentCol as content, created_at FROM announcements WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
    $latestAnn = $stmt->fetch();
} catch (Exception $e) {
    $latestAnn = null;
}

if ($latestAnn):
?>
    <div id="announcement-toast" class="ann-toast" style="display: none;">
        <div class="ann-toast-wrapper">
            <div class="ann-toast-icon">
                <i class="fas fa-bullhorn rotate-icon"></i>
                <div class="ann-toast-pulse"></div>
            </div>
            <div class="ann-toast-content">
                <div class="ann-toast-header">
                    <span class="ann-toast-badge"><?php echo $currentLang === 'th' ? 'ประกาศใหม่' : 'New Announcement'; ?></span>
                    <span class="ann-toast-time"><?php echo formatThaiDate($latestAnn['created_at']); ?></span>
                </div>
                <h4 class="ann-toast-title"><?php echo htmlspecialchars($latestAnn['title']); ?></h4>
                <p class="ann-toast-text"><?php echo nl2br(htmlspecialchars($latestAnn['content'])); ?></p>
            </div>
            <button id="close-ann-toast" class="ann-toast-close" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <style>
        .ann-toast {
            position: fixed;
            bottom: 30px;
            left: 30px;
            z-index: 9999;
            max-width: 380px;
            width: calc(100% - 60px);
            transform: translateX(-120%);
            transition: transform 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            filter: drop-shadow(0 15px 35px rgba(0, 0, 0, 0.15));
        }

        .ann-toast.show {
            transform: translateX(0);
        }

        .ann-toast-wrapper {
            background: white;
            border-radius: 20px;
            padding: 20px;
            display: flex;
            gap: 16px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(139, 92, 246, 0.1);
            background: linear-gradient(to bottom right, #ffffff, #f9fafb);
        }

        .ann-toast-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 6px;
            height: 100%;
            background: var(--primary-gradient);
        }

        .ann-toast-icon {
            width: 44px;
            height: 44px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
            position: relative;
        }

        .rotate-icon {
            animation: rotate-shake 3s ease-in-out infinite;
        }

        @keyframes rotate-shake {

            0%,
            100% {
                transform: rotate(0);
            }

            5%,
            15%,
            25% {
                transform: rotate(10deg);
            }

            10%,
            20%,
            30% {
                transform: rotate(-10deg);
            }

            35% {
                transform: rotate(0);
            }
        }

        .ann-toast-pulse {
            position: absolute;
            width: 100%;
            height: 100%;
            background: var(--primary);
            border-radius: 14px;
            opacity: 0.2;
            animation: toast-pulse 2s ease-out infinite;
        }

        @keyframes toast-pulse {
            0% {
                transform: scale(1);
                opacity: 0.2;
            }

            100% {
                transform: scale(1.5);
                opacity: 0;
            }
        }

        .ann-toast-content {
            flex: 1;
            min-width: 0;
        }

        .ann-toast-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        .ann-toast-badge {
            font-size: 10px;
            font-weight: 800;
            color: var(--primary);
            background: var(--primary-light);
            padding: 2px 8px;
            border-radius: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .ann-toast-time {
            font-size: 10px;
            color: var(--text-tertiary);
            font-weight: 500;
        }

        .ann-toast-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 4px;
            display: block;
        }

        .ann-toast-text {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.5;
            margin: 0;
            max-height: 200px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .ann-toast-text::-webkit-scrollbar {
            width: 4px;
        }

        .ann-toast-text::-webkit-scrollbar-track {
            background: transparent;
        }

        .ann-toast-text::-webkit-scrollbar-thumb {
            background: var(--gray-200);
            border-radius: 10px;
        }

        .ann-toast-close {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 24px;
            height: 24px;
            border: none;
            background: transparent;
            color: var(--text-tertiary);
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .ann-toast-close:hover {
            background: var(--gray-100);
            color: var(--danger);
        }

        @media (max-width: 480px) {
            .ann-toast {
                left: 15px;
                bottom: 15px;
                width: calc(100% - 30px);
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.getElementById('announcement-toast');
            const closeBtn = document.getElementById('close-ann-toast');
            const annId = '<?php echo $latestAnn['id']; ?>';

            // Show only if not seen before in this session OR if it's been more than 1 hour? 
            // User said "แสดงแค่ตอนเข้ามาครั้งแรกที่เห็นเท่านั้น" - let's use localStorage with ID.
            // Show every time the page is loaded
            toast.style.display = 'block';
            setTimeout(() => {
                toast.classList.add('show');
            }, 500);

            // Auto hide after 8 seconds (longer for long messages)
            const hideTimeout = setTimeout(() => {
                hideToast();
            }, 8500);

            function hideToast() {
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 600);
            }

            closeBtn.addEventListener('click', () => {
                clearTimeout(hideTimeout);
                hideToast();
            });

        });
    </script>
<?php endif; ?>