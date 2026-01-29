    <!-- Rating Section -->
    <section class="rating-section">
        <div class="container">
            <div class="rating-wrapper">
                <div class="rating-content">
                    <div class="rating-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="rating-text">
                        <h3><?php echo $currentLang === 'th' ? 'คุณพึงพอใจการใช้งานแค่ไหน?' : 'How satisfied are you?'; ?></h3>
                        <p><?php echo $currentLang === 'th' ? 'ให้คะแนนเพื่อช่วยเราปรับปรุงระบบ' : 'Rate us to help improve our service'; ?></p>
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
                        <span><?php echo $currentLang === 'th' ? 'แย่' : 'Poor'; ?></span>
                        <span><?php echo $currentLang === 'th' ? 'ดีมาก' : 'Excellent'; ?></span>
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
                    th: 'ขอบคุณสำหรับความคิดเห็น เราจะปรับปรุงให้ดีขึ้น',
                    en: 'Thank you! We will improve.'
                },
                2: {
                    th: 'ขอบคุณ! เราพร้อมปรับปรุงให้ดีขึ้น',
                    en: 'Thank you! We\'re ready to improve.'
                },
                3: {
                    th: 'ขอบคุณสำหรับคะแนน!',
                    en: 'Thank you for your rating!'
                },
                4: {
                    th: 'ยินดีมาก! ขอบคุณที่ให้คะแนน',
                    en: 'Great! Thanks for rating!'
                },
                5: {
                    th: 'ยอดเยี่ยม! ขอบคุณมากที่ชอบเรา ❤️',
                    en: 'Excellent! Thanks for loving us ❤️'
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
                            Toast.success(lang === 'th' ? 'ขอบคุณสำหรับคะแนน!' : 'Thanks for rating!');
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
                    <div class="footer-brand">
                        <i class="fas fa-book-open"></i> Babybib
                    </div>
                    <p class="footer-description">
                        <?php echo __('tagline'); ?>
                    </p>
                    <div class="footer-help-section">
                        <h4 class="footer-title"><?php echo __('nav_help'); ?></h4>
                        <div class="footer-help-links">
                            <div class="help-row">
                                <a href="<?php echo SITE_URL; ?>/start.php"><?php echo __('nav_start'); ?></a>
                                <a href="<?php echo SITE_URL; ?>/sort.php"><?php echo __('nav_sort'); ?></a>
                                <a href="<?php echo SITE_URL; ?>/help-author.php"><?php echo __('help_author'); ?></a>
                            </div>
                            <div class="help-row">
                                <a href="<?php echo SITE_URL; ?>/help-place.php"><?php echo __('help_place'); ?></a>
                                <a href="<?php echo SITE_URL; ?>/help-publisher.php"><?php echo __('help_publisher'); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="footer-title"><?php echo $currentLang === 'th' ? 'ทีมพัฒนา' : 'Development Team'; ?></h4>
                    <ul class="footer-links footer-team">
                        <li><i class="fas fa-user-tie"></i> ผศ.ดร.ธนพรรณ กุลจันทร์</li>
                        <li><i class="fas fa-user"></i> นายชวชล สุปรียาพร</li>
                        <li><i class="fas fa-user"></i> นางสาวณัฐณิชา พิมพะสาลี</li>
                        <li><i class="fas fa-user"></i> นายธนากร ดวงคำวัฒนสิริ</li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-title"><?php echo $currentLang === 'th' ? 'ติดต่อเรา' : 'Contact Us'; ?></h4>
                    <ul class="footer-links">
                        <li><a href="mailto:thanayok@gmail.com"><i class="fas fa-envelope"></i> thanayok@gmail.com</a></li>
                    </ul>
                    <h4 class="footer-title mt-4"><?php echo __('nav_share'); ?></h4>
                    <ul class="footer-links footer-social">
                        <li><a href="#" target="_blank"><i class="fab fa-facebook"></i></a></li>
                        <li><a href="#" target="_blank"><i class="fab fa-instagram"></i></a></li>
                        <li><a href="#" target="_blank"><i class="fab fa-line"></i></a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-title"><?php echo $currentLang === 'th' ? 'ประเมินและข้อเสนอแนะ' : 'Feedback'; ?></h4>
                    <p class="footer-feedback-text">
                        <?php echo $currentLang === 'th'
                            ? 'ความคิดเห็นของท่านมีค่าสำหรับเรา<br>เพื่อช่วยให้เราพัฒนาระบบได้ดียิ่งขึ้น'
                            : 'Your feedback is valuable to us<br>to help us improve the system'; ?>
                    </p>
                    <div class="footer-feedback-buttons" style="align-items: center;">
                        <a href="#" class="feedback-btn feedback-btn-evaluate" onclick="openEvaluationModal(); return false;">
                            <i class="fas fa-star"></i>
                            <?php echo $currentLang === 'th' ? 'ประเมินระบบ' : 'Evaluate'; ?>
                        </a>
                        <a href="#" class="feedback-btn feedback-btn-suggest" onclick="openFeedbackModal(); return false;">
                            <i class="fas fa-comment"></i>
                            <?php echo $currentLang === 'th' ? 'ส่งข้อเสนอแนะ' : 'Feedback'; ?>
                        </a>
                    </div>

                    <!-- Language Toggle in Footer -->
                    <div class="footer-lang-toggle">
                        <span class="footer-lang-label"><?php echo $currentLang === 'th' ? 'ภาษา:' : 'Language:'; ?></span>
                        <div class="lang-toggle">
                            <button class="lang-toggle-btn <?php echo $currentLang === 'th' ? 'active' : ''; ?>" onclick="changeLanguage('th')">TH</button>
                            <button class="lang-toggle-btn <?php echo $currentLang === 'en' ? 'active' : ''; ?>" onclick="changeLanguage('en')">EN</button>
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
                <p>&copy; <?php echo date('Y'); ?> Babybib - Faculty of Humanities, Chiang Mai University.</p>
                <div class="footer-stats-inline">
                    <span><i class="fas fa-eye"></i> <?php echo $currentLang === 'th' ? 'วันนี้' : 'Today'; ?>: <strong><?php echo formatVisitCount($visitStats['today']); ?></strong></span>
                    <span><i class="fas fa-calendar"></i> <?php echo $currentLang === 'th' ? 'เดือน' : 'Month'; ?>: <strong><?php echo formatVisitCount($visitStats['month']); ?></strong></span>
                    <span><i class="fas fa-users"></i> <?php echo $currentLang === 'th' ? 'ทั้งหมด' : 'Total'; ?>: <strong><?php echo formatVisitCount($visitStats['total']); ?></strong></span>
                </div>
            </div>
        </div>
    </footer>
    <style>
        .footer-main {
            grid-column: span 1;
        }

        /* Footer Bottom Stats Inline */
        .footer-bottom {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .footer-stats-inline {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .footer-stats-inline span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
        }

        .footer-stats-inline span i {
            font-size: 11px;
            opacity: 0.7;
        }

        .footer-stats-inline span strong {
            color: white;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .footer-bottom {
                flex-direction: column;
                text-align: center;
            }

            .footer-stats-inline {
                justify-content: center;
            }
        }

        .footer-help-section {
            margin-top: var(--space-4);
        }

        .footer-help-links {
            display: flex;
            flex-direction: column;
            gap: var(--space-2);
        }

        .help-row {
            display: flex;
            flex-wrap: wrap;
            gap: var(--space-4);
        }

        .help-row a {
            color: rgba(255, 255, 255, 0.7);
            font-size: var(--text-sm);
            transition: color 0.3s ease;
        }

        .help-row a:hover {
            color: var(--primary);
        }

        .footer-team li {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.7);
            font-size: var(--text-sm);
        }

        .footer-team li i {
            color: rgba(255, 255, 255, 0.7);
            font-size: 12px;
        }

        .footer-social {
            display: flex;
            gap: var(--space-3);
        }

        .footer-social li a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: var(--gray-100);
            border-radius: 10px;
            color: var(--text-secondary);
            transition: all 0.3s ease;
        }

        .footer-social li a:hover {
            background: var(--primary);
            color: white;
        }

        .mt-4 {
            margin-top: var(--space-4);
        }

        .footer-feedback-text {
            color: rgba(255, 255, 255, 0.6);
            font-size: var(--text-sm);
            line-height: 1.6;
            margin-bottom: var(--space-3);
        }

        .footer-feedback-buttons {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: var(--space-2);
        }

        .feedback-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .feedback-btn:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        .feedback-btn i {
            font-size: 11px;
        }

        /* Footer Language Toggle */
        .footer-lang-toggle {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            margin-top: var(--space-4);
            padding-top: var(--space-3);
            border-top: 1px dashed rgba(255, 255, 255, 0.1);
        }

        .footer-lang-label {
            color: var(--white);
            font-size: var(--text-sm);
            font-weight: 500;
        }

        .footer-lang-toggle .lang-toggle {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            padding: 3px;
        }

        .footer-lang-toggle .lang-toggle-btn {
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 500;
            background: transparent;
            color: rgba(255, 255, 255, 0.7);
            border-radius: 4px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .footer-lang-toggle .lang-toggle-btn:hover {
            color: white;
        }

        .footer-lang-toggle .lang-toggle-btn.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
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