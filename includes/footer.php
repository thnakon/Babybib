    <?php if (!isset($hideRating) || !$hideRating): ?>
    <!-- Rating Section -->
    <section class="rating-section">
        <div class="container">
            <div class="rating-wrapper">
                <div class="rating-content">
                    <div class="rating-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="rating-text">
                        <h3><?php echo __('rating_title'); ?></h3>
                        <p><?php echo __('rating_subtitle'); ?></p>
                    </div>
                </div>
                <div class="rating-stars-container">
                    <div class="rating-stars" id="rating-stars">
                        <button type="button" class="star-btn" data-rating="1" aria-label="1 star">
                            <i class="fas fa-star"></i>
                        </button>
                        <button type="button" class="star-btn" data-rating="2" aria-label="2 stars">
                            <i class="fas fa-star"></i>
                        </button>
                        <button type="button" class="star-btn" data-rating="3" aria-label="3 stars">
                            <i class="fas fa-star"></i>
                        </button>
                        <button type="button" class="star-btn" data-rating="4" aria-label="4 stars">
                            <i class="fas fa-star"></i>
                        </button>
                        <button type="button" class="star-btn" data-rating="5" aria-label="5 stars">
                            <i class="fas fa-star"></i>
                        </button>
                    </div>
                    <div class="rating-labels">
                        <span><?php echo __('rating_fair'); ?></span>
                        <span><?php echo __('rating_excellent'); ?></span>
                    </div>
                    <div class="rating-feedback" id="rating-feedback"></div>
                </div>
            </div>
        </div>
    </section>

    <style>
        /* Rating Section */
        .rating-section {
            background: linear-gradient(135deg, #EDE9FE 0%, #DDD6FE 50%, #C4B5FD 100%);
            padding: 40px 0;
            position: relative;
            overflow: hidden;
        }

        .rating-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.1) 0%, transparent 50%);
            animation: pulse-bg 8s ease-in-out infinite;
        }

        @keyframes pulse-bg {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.5;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        .rating-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 40px;
            position: relative;
            z-index: 1;
        }

        .rating-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .rating-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #8B5CF6, #7C3AED);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            color: white;
            box-shadow: 0 8px 24px rgba(139, 92, 246, 0.3);
            animation: heartbeat 2s ease-in-out infinite;
        }

        @keyframes heartbeat {

            0%,
            100% {
                transform: scale(1);
            }

            10%,
            30% {
                transform: scale(1.1);
            }

            20% {
                transform: scale(0.95);
            }
        }

        .rating-text h3 {
            font-size: 20px;
            font-weight: 700;
            color: #1e1b4b;
            margin-bottom: 4px;
        }

        .rating-text p {
            color: #5b21b6;
            font-size: 14px;
        }

        .rating-stars-container {
            text-align: center;
        }

        .rating-stars {
            display: flex;
            gap: 8px;
        }

        .star-btn {
            width: 48px;
            height: 48px;
            border: none;
            background: white;
            border-radius: 12px;
            cursor: pointer;
            font-size: 22px;
            color: #D1D5DB;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
        }

        .star-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(251, 191, 36, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: all 0.4s ease;
        }

        .star-btn:hover {
            transform: translateY(-4px) scale(1.1);
            box-shadow: 0 8px 20px rgba(251, 191, 36, 0.3);
        }

        .star-btn:hover::before {
            width: 100%;
            height: 100%;
        }

        .star-btn.hovered,
        .star-btn.active {
            color: #FBBF24;
            background: linear-gradient(135deg, #FEF3C7, #FDE68A);
        }

        .star-btn.active {
            animation: star-pop 0.4s ease;
        }

        @keyframes star-pop {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.3) rotate(15deg);
            }

            100% {
                transform: scale(1);
            }
        }

        .star-btn.selected {
            color: #F59E0B;
            background: linear-gradient(135deg, #FDE68A, #FCD34D);
            box-shadow: 0 4px 16px rgba(245, 158, 11, 0.4);
        }

        .rating-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            padding: 0 4px;
            font-size: 12px;
            color: #000;
        }

        .rating-feedback {
            margin-top: 12px;
            min-height: 24px;
            font-size: 14px;
            font-weight: 500;
            color: #059669;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }

        .rating-feedback.show {
            opacity: 1;
            transform: translateY(0);
        }

        .rating-feedback i {
            margin-right: 6px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .rating-wrapper {
                flex-direction: column;
                text-align: center;
                gap: 24px;
            }

            .rating-content {
                flex-direction: column;
            }

            .star-btn {
                width: 42px;
                height: 42px;
                font-size: 18px;
            }
        }
    </style>

    <script>
        (function() {
            const starsContainer = document.getElementById('rating-stars');
            const feedbackEl = document.getElementById('rating-feedback');
            const stars = starsContainer?.querySelectorAll('.star-btn');
            let currentRating = 0;
            let hasRated = localStorage.getItem('babybib_rated') === 'true';

            const feedbackMessages = {
                1: {
                    th: '<?php echo addslashes(__('rating_feedback_1', 'th')); ?>',
                    en: '<?php echo addslashes(__('rating_feedback_1', 'en')); ?>'
                },
                2: {
                    th: '<?php echo addslashes(__('rating_feedback_2', 'th')); ?>',
                    en: '<?php echo addslashes(__('rating_feedback_2', 'en')); ?>'
                },
                3: {
                    th: '<?php echo addslashes(__('rating_feedback_3', 'th')); ?>',
                    en: '<?php echo addslashes(__('rating_feedback_3', 'en')); ?>'
                },
                4: {
                    th: '<?php echo addslashes(__('rating_feedback_4', 'th')); ?>',
                    en: '<?php echo addslashes(__('rating_feedback_4', 'en')); ?>'
                },
                5: {
                    th: '<?php echo addslashes(__('rating_feedback_5', 'th')); ?>',
                    en: '<?php echo addslashes(__('rating_feedback_5', 'en')); ?>'
                }
            };

            const lang = document.body.classList.contains('lang-en') ? 'en' : 'th';

            if (hasRated && stars) {
                const savedRating = parseInt(localStorage.getItem('babybib_rating') || '0');
                if (savedRating > 0) {
                    currentRating = savedRating;
                    updateStars(savedRating, true);
                    showFeedback(savedRating);
                }
            }

            stars?.forEach(star => {
                star.addEventListener('mouseenter', () => {
                    if (hasRated) return;
                    const rating = parseInt(star.dataset.rating);
                    highlightStars(rating);
                });

                star.addEventListener('mouseleave', () => {
                    if (hasRated) return;
                    highlightStars(currentRating);
                });

                star.addEventListener('click', async () => {
                    const rating = parseInt(star.dataset.rating);
                    currentRating = rating;
                    updateStars(rating, true);
                    await submitRating(rating);
                });
            });

            starsContainer?.addEventListener('mouseleave', () => {
                if (!hasRated) {
                    highlightStars(currentRating);
                }
            });

            function highlightStars(rating) {
                stars.forEach(star => {
                    const starRating = parseInt(star.dataset.rating);
                    star.classList.toggle('hovered', starRating <= rating);
                });
            }

            function updateStars(rating, animate = false) {
                stars.forEach((star, index) => {
                    const starRating = parseInt(star.dataset.rating);
                    star.classList.remove('hovered');
                    star.classList.toggle('selected', starRating <= rating);

                    if (animate && starRating <= rating) {
                        star.classList.add('active');
                        setTimeout(() => {
                            star.style.animationDelay = `${index * 0.1}s`;
                        }, index * 100);
                    }
                });
            }

            function showFeedback(rating) {
                const msg = feedbackMessages[rating][lang];
                feedbackEl.innerHTML = `<i class="fas fa-check-circle"></i> ${msg}`;
                feedbackEl.classList.add('show');
            }

            async function submitRating(rating) {
                try {
                    const response = await fetch('<?php echo SITE_URL; ?>/api/rating/submit.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': CONFIG.csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            rating: rating,
                            page_url: window.location.pathname
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        hasRated = true;
                        localStorage.setItem('babybib_rated', 'true');
                        localStorage.setItem('babybib_rating', rating.toString());
                        showFeedback(rating);

                        if (typeof Toast !== 'undefined' && Toast.success) {
                            Toast.success('<?php echo addslashes(__('rating_success_toast')); ?>');
                        }
                    }
                } catch (err) {
                    console.error('Rating error:', err);
                }
            }
        })();
    </script>
    <?php endif; ?>

    <?php if (!isset($hideRating) || !$hideRating): ?>
    <!-- Footer -->
    <footer class="bg-base-200 text-base-content border-t border-base-300 py-12">
        <div class="container mx-auto px-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Col 1: Brand & Help -->
            <div>
                <div class="font-bold text-2xl tracking-wide bg-gradient-to-r from-violet-600 via-purple-600 to-fuchsia-600 bg-clip-text text-transparent mb-3">
                    Babybib
                </div>
                <p class="text-sm opacity-80 mb-4 leading-relaxed">
                    <?php echo __('tagline'); ?>
                </p>
                <h4 class="font-bold text-sm uppercase tracking-wider mb-2 opacity-90"><?php echo __('nav_help'); ?></h4>
                <div class="flex flex-col gap-1 text-sm opacity-80">
                    <a href="<?php echo SITE_URL; ?>/start.php" class="link link-hover"><?php echo __('nav_start'); ?></a>
                    <a href="<?php echo SITE_URL; ?>/sort.php" class="link link-hover"><?php echo __('nav_sort'); ?></a>
                    <a href="<?php echo SITE_URL; ?>/help-author.php" class="link link-hover"><?php echo __('help_author'); ?></a>
                    <a href="<?php echo SITE_URL; ?>/help-place.php" class="link link-hover"><?php echo __('help_place'); ?></a>
                    <a href="<?php echo SITE_URL; ?>/help-publisher.php" class="link link-hover"><?php echo __('help_publisher'); ?></a>
                </div>
            </div>

            <!-- Col 2: Dev Team -->
            <div>
                <h4 class="font-bold text-sm uppercase tracking-wider mb-3 opacity-90"><?php echo __('footer_dev_team'); ?></h4>
                <ul class="flex flex-col gap-2 text-sm opacity-80">
                    <li class="flex items-center gap-2"><i class="fas fa-user-tie text-primary w-4"></i> ผศ.ดร.ธนพรรณ กุลจันทร์</li>
                    <li class="flex items-center gap-2"><i class="fas fa-user text-primary w-4"></i> นางสาวณัฐณิชา พิมพะสาลี</li>
                    <li class="flex items-center gap-2"><i class="fas fa-user text-primary w-4"></i> นายธนากร ดวงคำวัฒนสิริ</li>
                </ul>
            </div>

            <!-- Col 3: Contact & Share -->
            <div>
                <h4 class="font-bold text-sm uppercase tracking-wider mb-3 opacity-90"><?php echo __('footer_contact_us'); ?></h4>
                <ul class="flex flex-col gap-2 text-sm opacity-80 mb-4">
                    <li><a href="mailto:thanayok@gmail.com" class="link link-hover flex items-center gap-2"><i class="fas fa-envelope text-primary w-4"></i> thanayok@gmail.com</a></li>
                </ul>
                <h4 class="font-bold text-sm uppercase tracking-wider mb-2 opacity-90"><?php echo __('nav_share'); ?></h4>
                <div class="flex items-center gap-2">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(SITE_URL); ?>" target="_blank" class="btn btn-sm btn-circle bg-[#1877F2] text-white border-none hover:brightness-110"><i class="fab fa-facebook text-base"></i></a>
                    <a href="#" target="_blank" class="btn btn-sm btn-circle bg-gradient-to-tr from-[#f09433] via-[#dc2743] to-[#bc1888] text-white border-none hover:brightness-110"><i class="fab fa-instagram text-base"></i></a>
                    <a href="https://line.me/R/msg/text/?<?php echo urlencode(__('tagline') . ' ' . SITE_URL); ?>" target="_blank" class="btn btn-sm btn-circle bg-[#00B900] text-white border-none hover:brightness-110"><i class="fab fa-line text-base"></i></a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(SITE_URL); ?>" target="_blank" class="btn btn-sm btn-circle bg-[#000] text-white border-none hover:brightness-110">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="white">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path>
                        </svg>
                    </a>
                </div>
                <div class="flex gap-3 text-xs opacity-75 mt-4">
                    <a href="<?php echo SITE_URL; ?>/terms.php" class="link link-hover"><?php echo __('terms_of_service'); ?></a>
                    <span>|</span>
                    <a href="<?php echo SITE_URL; ?>/privacy.php" class="link link-hover"><?php echo __('privacy_policy'); ?></a>
                </div>
            </div>

            <!-- Col 4: Feedback & Lang -->
            <div>
                <h4 class="font-bold text-sm uppercase tracking-wider mb-3 opacity-90"><?php echo __('footer_feedback_title'); ?></h4>
                <p class="text-sm opacity-85 leading-relaxed mb-3">
                    <?php echo __('footer_feedback_desc'); ?>
                </p>
                <div class="flex items-center gap-2 mb-4">
                    <a href="#" class="btn btn-sm btn-primary text-white px-4 rounded-lg shadow" onclick="openEvaluationModal(); return false;">
                        <i class="fas fa-star text-xs"></i>
                        <?php echo __('footer_evaluate_btn'); ?>
                    </a>
                    <a href="#" class="btn btn-sm btn-outline btn-primary px-4 rounded-lg" onclick="openFeedbackModal(); return false;">
                        <i class="fas fa-comment text-xs"></i>
                        <?php echo __('footer_suggest_btn'); ?>
                    </a>
                </div>

                <!-- Language Toggle in Footer -->
                <div class="flex items-center gap-3 pt-3 border-t border-dashed border-base-300">
                    <span class="text-xs font-semibold opacity-75"><?php echo __('footer_lang_label'); ?></span>
                    <div class="join">
                        <button class="join-item btn btn-xs <?php echo (getCurrentLanguage() === 'th') ? 'btn-primary text-white' : 'btn-ghost border border-base-300'; ?>" onclick="changeLanguage('th')">TH</button>
                        <button class="join-item btn btn-xs <?php echo (getCurrentLanguage() === 'en') ? 'btn-primary text-white' : 'btn-ghost border border-base-300'; ?>" onclick="changeLanguage('en')">EN</button>
                    </div>
                </div>
            </div>
        </div>

        <?php
        // Include visit tracker
        require_once __DIR__ . '/visit-tracker.php';
        $visitStats = getVisitStats();
        ?>

        <div class="container mx-auto px-4 mt-8 pt-6 border-t border-base-300 flex flex-col md:flex-row items-center justify-between gap-4 text-xs opacity-75">
            <p>&copy; <?php echo date('Y'); ?> Babybib - <?php echo __('footer_faculty'); ?></p>
            <div class="flex items-center gap-4">
                <span><i class="fas fa-eye text-primary mr-1"></i> <?php echo __('footer_today'); ?>: <strong><?php echo formatVisitCount($visitStats['today']); ?></strong></span>
                <span><i class="fas fa-calendar text-primary mr-1"></i> <?php echo __('footer_month'); ?>: <strong><?php echo formatVisitCount($visitStats['month']); ?></strong></span>
                <span><i class="fas fa-users text-primary mr-1"></i> <?php echo __('footer_total'); ?>: <strong><?php echo formatVisitCount($visitStats['total']); ?></strong></span>
            </div>
        </div>
    </footer>
    <?php endif; ?>
    <style>
        /* Embedded styling only for the rating stars since they are highly custom */
        .rating-section {
            background: linear-gradient(135deg, var(--primary-light) 0%, #DDD6FE 50%, #C4B5FD 100%);
            padding: 40px 0;
            position: relative;
            overflow: hidden;
        }

        .rating-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 40px;
            position: relative;
            z-index: 1;
        }

        .rating-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .rating-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            color: white;
            box-shadow: 0 8px 24px rgba(139, 92, 246, 0.3);
            animation: heartbeat 2s ease-in-out infinite;
        }

        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            10%, 30% { transform: scale(1.1); }
            20% { transform: scale(0.95); }
        }

        .rating-text h3 {
            font-size: 20px;
            font-weight: 700;
            color: #1e1b4b;
            margin-bottom: 4px;
        }

        .rating-text p {
            color: #5b21b6;
            font-size: 14px;
        }

        .rating-stars-container {
            text-align: center;
        }

        .rating-stars {
            display: flex;
            gap: 8px;
        }

        .star-btn {
            width: 48px;
            height: 48px;
            border: none;
            background: white;
            border-radius: 12px;
            cursor: pointer;
            font-size: 22px;
            color: #D1D5DB;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            position: relative;
        }

        .star-btn:hover {
            transform: translateY(-4px) scale(1.1);
            box-shadow: 0 8px 20px rgba(251, 191, 36, 0.3);
        }

        .star-btn.hovered,
        .star-btn.active {
            color: #FBBF24;
            background: linear-gradient(135deg, #FEF3C7, #FDE68A);
        }

        .star-btn.active {
            animation: star-pop 0.4s ease;
        }

        @keyframes star-pop {
            0% { transform: scale(1); }
            50% { transform: scale(1.3) rotate(15deg); }
            100% { transform: scale(1); }
        }

        .star-btn.selected {
            color: #F59E0B;
            background: linear-gradient(135deg, #FDE68A, #FCD34D);
            box-shadow: 0 4px 16px rgba(245, 158, 11, 0.4);
        }

        .rating-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            padding: 0 4px;
            font-size: 12px;
            color: #000;
        }

        .rating-feedback {
            margin-top: 12px;
            min-height: 24px;
            font-size: 14px;
            font-weight: 500;
            color: #059669;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }

        .rating-feedback.show {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            .rating-wrapper {
                flex-direction: column;
                text-align: center;
                gap: 24px;
            }

            .rating-content {
                flex-direction: column;
            }

            .star-btn {
                width: 42px;
                height: 42px;
                font-size: 18px;
            }
        }
    </style>

    <!-- Feedback Modal -->
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
            border-radius: 16px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
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
            border-bottom: 1px solid var(--border-light);
        }

        .feedback-modal-header h3 {
            font-size: 18px;
            font-weight: 600;
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
            margin-bottom: 16px;
        }

        .feedback-form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .feedback-form-group select,
        .feedback-form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-light);
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .feedback-form-group select:focus,
        .feedback-form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        }

        .feedback-form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .feedback-form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .feedback-btn-cancel {
            padding: 10px 20px;
            border: 1px solid var(--border-light);
            background: white;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .feedback-btn-cancel:hover {
            background: var(--gray-100);
        }

        .feedback-btn-submit {
            padding: 10px 20px;
            border: none;
            background: var(--primary);
            color: white;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .feedback-btn-submit:hover {
            background: var(--primary-dark);
        }

        .feedback-btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>

    <script>
        function openEvaluationModal() {
            window.open('https://docs.google.com/forms/d/1L7dFi3yVhjzhLYNocJbbI-fNttvK55GlbOJnH_Nt-qk/viewform', '_blank');
        }

        function openFeedbackModal() {
            const modal = document.getElementById('feedback-modal');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
            document.body.style.overflow = 'hidden';
        }

        function closeFeedbackModal() {
            const modal = document.getElementById('feedback-modal');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                document.body.style.overflow = '';
                document.getElementById('feedback-form').reset();
            }, 300);
        }

        // Close modal on overlay click
        document.getElementById('feedback-modal')?.addEventListener('click', function(e) {
            if (e.target === this) closeFeedbackModal();
        });

        // Handle form submission
        document.getElementById('feedback-form')?.addEventListener('submit', async function(e) {
            e.preventDefault();

            const type = document.getElementById('feedback-type').value;
            const message = document.getElementById('feedback-message').value;
            const submitBtn = this.querySelector('.feedback-btn-submit');

            if (!type || !message.trim()) {
                Toast?.error?.('<?php echo $currentLang === "th" ? "กรุณากรอกข้อมูลให้ครบ" : "Please fill all fields"; ?>') || alert('<?php echo $currentLang === "th" ? "กรุณากรอกข้อมูลให้ครบ" : "Please fill all fields"; ?>');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?php echo $currentLang === "th" ? "กำลังส่ง..." : "Sending..."; ?>';

            try {
                const response = await fetch('<?php echo SITE_URL; ?>/api/feedback/create.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': CONFIG.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        subject: type,
                        message: message
                    })
                });

                const data = await response.json();

                if (data.success) {
                    Toast?.success?.('<?php echo $currentLang === "th" ? "ส่งข้อเสนอแนะสำเร็จ ขอบคุณสำหรับความคิดเห็น" : "Feedback sent successfully. Thank you!"; ?>') || alert('<?php echo $currentLang === "th" ? "ส่งข้อเสนอแนะสำเร็จ" : "Feedback sent successfully"; ?>');
                    closeFeedbackModal();
                } else {
                    throw new Error(data.error || 'Error');
                }
            } catch (err) {
                Toast?.error?.('<?php echo $currentLang === "th" ? "เกิดข้อผิดพลาด กรุณาลองใหม่" : "An error occurred. Please try again."; ?>') || alert('<?php echo $currentLang === "th" ? "เกิดข้อผิดพลาด" : "Error"; ?>');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> <?php echo $currentLang === "th" ? "ส่งข้อเสนอแนะ" : "Send"; ?>';
            }
        });
    </script>

    <!-- Inactivity Timeout Detector -->
    <?php if (isLoggedIn()): ?>
        <script>
            (function() {
                let inactivityTimer;
                const timeoutDuration = 600000; // 600,000 ms = 10 minutes

                function resetTimer() {
                    clearTimeout(inactivityTimer);
                    inactivityTimer = setTimeout(logoutDueToInactivity, timeoutDuration);
                }

                function logoutDueToInactivity() {
                    window.location.href = '<?php echo SITE_URL; ?>/login.php?msg=timeout';
                }

                // Interaction events to monitor
                const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];

                // Set up event listeners
                events.forEach(event => {
                    document.addEventListener(event, resetTimer, {
                        passive: true
                    });
                });

                // Initial timer start
                resetTimer();
            })();
        </script>
    <?php endif; ?>

    <!-- Modal Container -->
    <div id="modal-container"></div>

    <!-- Scripts -->
    <?php if (isset($extraScripts)) echo $extraScripts; ?>
    </body>

    </html>