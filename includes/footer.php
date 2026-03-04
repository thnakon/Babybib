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
                            'Content-Type': 'application/json'
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

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-main">
                    <div class="footer-brand comfortaa-1">
                        Babybib
                    </div>
                    <p class="footer-description">
                        <?php echo __('tagline'); ?>
                    </p>
                    <div class="footer-help-section">
                        <h4 class="footer-title"><?php echo __('nav_help'); ?></h4>
                        <div class="footer-help-links">
                            <div class="help-row">
                                <a href="<?php echo SITE_URL; ?>/start.php"><?php echo __('nav_start'); ?></a> |
                                <a href="<?php echo SITE_URL; ?>/sort.php"><?php echo __('nav_sort'); ?></a> |
                                <a href="<?php echo SITE_URL; ?>/help-author.php"><?php echo __('help_author'); ?></a>
                            </div>
                            <div class="help-row">
                                <a href="<?php echo SITE_URL; ?>/help-place.php"><?php echo __('help_place'); ?></a> |
                                <a href="<?php echo SITE_URL; ?>/help-publisher.php"><?php echo __('help_publisher'); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="footer-title"><?php echo __('footer_dev_team'); ?></h4>
                    <ul class="footer-links footer-team">
                        <li><i class="fas fa-user-tie"></i> ผศ.ดร.ธนพรรณ กุลจันทร์</li>
                        <li><i class="fas fa-user"></i> นางสาวณัฐณิชา พิมพะสาลี</li>
                        <li><i class="fas fa-user"></i> นายธนากร ดวงคำวัฒนสิริ</li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-title"><?php echo __('footer_contact_us'); ?></h4>
                    <ul class="footer-links">
                        <li><a href="mailto:thanayok@gmail.com"><i class="fas fa-envelope"></i> thanayok@gmail.com</a></li>
                    </ul>
                    <h4 class="footer-title mt-4"><?php echo __('nav_share'); ?></h4>
                    <ul class="footer-links footer-social">
                        <li><a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(SITE_URL); ?>" target="_blank" class="social-facebook"><i class="fab fa-facebook"></i></a></li>
                        <li><a href="#" target="_blank" class="social-instagram"><i class="fab fa-instagram"></i></a></li>
                        <li><a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(SITE_URL); ?>" target="_blank" class="social-x">
                                <i class="fab fa-x-twitter"></i>
                                <span class="social-badge">X</span>
                            </a></li>
                        <li><a href="#" target="_blank" class="social-line"><i class="fab fa-line"></i></a></li>
                    </ul>
                    <div class="footer-legal-links mt-4">
                        <a href="<?php echo SITE_URL; ?>/terms.php"><?php echo __('terms_of_service'); ?></a>
                        <span class="separator">|</span>
                        <a href="<?php echo SITE_URL; ?>/privacy.php"><?php echo __('privacy_policy'); ?></a>
                    </div>
                </div>
                <div>
                    <h4 class="footer-title"><?php echo __('footer_feedback_title'); ?></h4>
                    <p class="footer-feedback-text">
                        <?php echo __('footer_feedback_desc'); ?>
                    </p>
                    <div class="footer-feedback-buttons" style="align-items: center;">
                        <a href="#" class="feedback-btn feedback-btn-evaluate" onclick="openEvaluationModal(); return false;">
                            <i class="fas fa-star"></i>
                            <?php echo __('footer_evaluate_btn'); ?>
                        </a>
                        <a href="#" class="feedback-btn feedback-btn-suggest" onclick="openFeedbackModal(); return false;">
                            <i class="fas fa-comment"></i>
                            <?php echo __('footer_suggest_btn'); ?>
                        </a>
                    </div>

                    <!-- Language Toggle in Footer -->
                    <div class="footer-lang-toggle">
                        <span class="footer-lang-label"><?php echo __('footer_lang_label'); ?></span>
                        <div class="lang-toggle">
                            <button class="lang-toggle-btn <?php echo (getCurrentLanguage() === 'th') ? 'active' : ''; ?>" onclick="changeLanguage('th')">TH</button>
                            <button class="lang-toggle-btn <?php echo (getCurrentLanguage() === 'en') ? 'active' : ''; ?>" onclick="changeLanguage('en')">EN</button>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            // Include visit tracker
            require_once __DIR__ . '/visit-tracker.php';
            $visitStats = getVisitStats();
            ?>

            <div class="footer-bottom">
                <div class="footer-copyright">
                    <p>&copy; <?php echo date('Y'); ?> Babybib - <?php echo __('footer_faculty'); ?></p>
                </div>
                <div class="footer-stats-inline">
                    <span><i class="fas fa-eye"></i> <?php echo __('footer_today'); ?>: <strong><?php echo formatVisitCount($visitStats['today']); ?></strong></span>
                    <span><i class="fas fa-calendar"></i> <?php echo __('footer_month'); ?>: <strong><?php echo formatVisitCount($visitStats['month']); ?></strong></span>
                    <span><i class="fas fa-users"></i> <?php echo __('footer_total'); ?>: <strong><?php echo formatVisitCount($visitStats['total']); ?></strong></span>
                </div>
            </div>
        </div>
    </footer>
    <style>
        /* Footer Enhanced Design */
        .footer {
            background: linear-gradient(180deg, #0f172a 0%, #1e1b4b 100%);
            padding: 80px 0 40px;
            position: relative;
            overflow: hidden;
            border-top: 4px solid var(--primary);
        }

        /* Colorful Light Blobs for Footer */
        .footer::before {
            content: '';
            position: absolute;
            top: -10%;
            left: -5%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.1) 0%, transparent 70%);
            filter: blur(60px);
            z-index: 0;
            pointer-events: none;
        }

        .footer::after {
            content: '';
            position: absolute;
            bottom: -5%;
            right: -5%;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(0, 176, 255, 0.08) 0%, transparent 70%);
            filter: blur(50px);
            z-index: 0;
            pointer-events: none;
        }

        .footer .container {
            position: relative;
            z-index: 1;
        }

        .footer-title {
            color: var(--white);
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 24px;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }

        .footer-links a,
        .help-row a,
        .footer-legal-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: underline;
            /* Add underline as requested */
            text-underline-offset: 4px;
            transition: all 0.3s ease;
        }

        .footer-links a:hover,
        .help-row a:hover,
        .footer-legal-links a:hover {
            color: var(--white);
            text-decoration-color: var(--primary);
            transform: translateX(3px);
            display: inline-block;
        }

        .footer-social {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }

        .footer-social li a {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            color: white;
            font-size: 20px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255, 255, 255, 0.1);
            text-decoration: none !important;
            /* No underline for icons */
        }

        .footer-social li a:hover {
            transform: translateY(-8px) scale(1.1);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .social-facebook:hover {
            background: #1877F2 !important;
            border-color: #1877F2 !important;
        }

        .social-instagram:hover {
            background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%) !important;
            border-color: transparent !important;
        }

        .social-line:hover {
            background: #00B900 !important;
            border-color: #00B900 !important;
        }

        .social-x:hover {
            background: #000000 !important;
            border-color: #333 !important;
        }

        .social-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #FF3B30;
            color: white;
            font-size: 10px;
            font-weight: 900;
            padding: 1px 6px;
            border-radius: 6px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            border: 2px solid #1e1b4b;
            z-index: 2;
        }

        .footer-brand.comfortaa-1 {
            font-size: 45px;
            /* Larger brand */
            margin-bottom: 20px;
            transition: all 0.5s ease;
            filter: drop-shadow(0 0 10px rgba(139, 92, 246, 0.3));
        }

        .footer-brand:hover {
            transform: scale(1.05);
            filter: drop-shadow(0 0 20px rgba(139, 92, 246, 0.6));
        }

        .footer-feedback-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .feedback-btn {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px 18px;
            border-radius: 14px;
            flex: 1;
            min-width: 130px;
            text-decoration: none !important;
            /* No underline for buttons */
            font-weight: 700;
        }

        .feedback-btn-evaluate:hover {
            background: linear-gradient(135deg, #00C853, #64DD17) !important;
            color: white !important;
            box-shadow: 0 8px 20px rgba(0, 200, 83, 0.4) !important;
            scale: 1.05;
        }

        .feedback-btn-suggest:hover {
            background: linear-gradient(135deg, #00B0FF, #0091EA) !important;
            color: white !important;
            box-shadow: 0 8px 20px rgba(0, 176, 255, 0.4) !important;
            scale: 1.05;
        }

        /* Today's Stats Glowing */
        .footer-stats-inline span strong {
            color: #00B0FF;
            text-shadow: 0 0 10px rgba(0, 176, 255, 0.5);
        }

        @media (max-width: 991px) {
            .footer-content {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .footer-content {
                grid-template-columns: 1fr;
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
                        'Content-Type': 'application/json'
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