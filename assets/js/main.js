/**
 * Babybib Main JavaScript
 * ========================
 */

// ===== CONFIGURATION =====
const CONFIG = {
    siteUrl: document.querySelector('meta[name="csrf-token"]')?.closest('head')?.querySelector('link[href*="babybib"]')?.href?.split('/assets')[0] || '',
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || ''
};

// ===== TOAST NOTIFICATIONS (New Design) =====
const Toast = {
    container: null,
    MAX_TOASTS: 5,

    config: {
        success: { icon: 'fa-check', title: 'สำเร็จ!', titleEn: 'Success!' },
        error: { icon: 'fa-xmark', title: 'เกิดข้อผิดพลาด', titleEn: 'Error' },
        warning: { icon: 'fa-exclamation', title: 'คำเตือน', titleEn: 'Warning' },
        info: { icon: 'fa-info', title: 'ข้อมูล', titleEn: 'Info' }
    },

    init() {
        this.container = document.getElementById('toast-container');
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'toast-container';
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        }
    },

    show(message, type = 'info', duration = 4000, options = {}) {
        if (!this.container) this.init();

        // Check limit
        if (this.container.childElementCount >= this.MAX_TOASTS) {
            const oldToast = this.container.firstElementChild;
            if (oldToast) this.close(oldToast, true);
        }

        const cfg = this.config[type] || this.config['info'];
        const isEn = document.body.classList.contains('lang-en');
        const title = options.title || (isEn ? cfg.titleEn : cfg.title);

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        if (options.persistent) toast.classList.add('persistent');

        // Action button HTML
        let actionHtml = '';
        if (options.action) {
            const btnClass = options.action.primary ? 'primary' : '';
            actionHtml = `
                <div class="toast-actions">
                    <button class="toast-btn ${btnClass}" data-action="${options.action.type || 'default'}">
                        ${options.action.label}
                    </button>
                </div>`;
        }

        // Progress bar (only if not persistent)
        const progressHtml = options.persistent ? '' : `<div class="toast-progress animate" style="animation-duration: ${duration}ms"></div>`;

        toast.innerHTML = `
            <div class="toast-icon"><i class="fa-solid ${cfg.icon}"></i></div>
            <div class="toast-content">
                <span class="toast-title">${title}</span>
                <span class="toast-message">${message}</span>
                ${actionHtml}
            </div>
            <button class="toast-close" onclick="Toast.close(this.parentElement)"><i class="fa-solid fa-xmark"></i></button>
            ${progressHtml}
        `;

        // Add action handler
        if (options.action && options.action.callback) {
            const btn = toast.querySelector('.toast-btn');
            btn.addEventListener('click', () => {
                options.action.callback(toast, btn);
            });
        }

        this.container.appendChild(toast);

        // Animate In
        requestAnimationFrame(() => toast.classList.add('show'));

        // Auto Remove
        if (!options.persistent) {
            setTimeout(() => this.close(toast), duration);
        }

        return toast;
    },

    close(toast, immediate = false) {
        if (!toast) return;
        toast.classList.remove('show');
        toast.classList.add('hide');

        if (immediate) {
            toast.remove();
        } else {
            toast.addEventListener('transitionend', () => {
                if (toast.parentElement) toast.remove();
            }, { once: true });
        }
    },

    clearAll() {
        const toasts = document.querySelectorAll('.toast');
        toasts.forEach(t => this.close(t));
    },

    success(message, options = {}) { return this.show(message, 'success', 4000, options); },
    error(message, options = {}) { return this.show(message, 'error', 5000, options); },
    warning(message, options = {}) { return this.show(message, 'warning', 4500, options); },
    info(message, options = {}) { return this.show(message, 'info', 4000, options); }
};

// ===== MODAL (New Design) =====
const Modal = {
    create(options) {
        const { title, content, footer, size = '', icon = null, onOpen, onClose } = options;

        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';

        // Icon wrapper HTML
        const iconHtml = icon ? `
            <div class="modal-icon-wrapper">
                <i class="${icon}"></i>
            </div>
        ` : '';

        overlay.innerHTML = `
            <div class="modal-box ${size}">
                <button class="modal-close-btn" onclick="Modal.close(this)">
                    <i class="fas fa-times"></i>
                </button>
                ${iconHtml}
                <div class="modal-header">
                    <h2>${title}</h2>
                </div>
                <div class="modal-body">
                    ${content}
                </div>
                ${footer ? `<div class="modal-actions">${footer}</div>` : ''}
            </div>
        `;

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) Modal.close(overlay.querySelector('.modal-close-btn'));
        });

        overlay._onClose = onClose;
        document.body.appendChild(overlay);

        // Trigger onOpen if provided
        if (typeof onOpen === 'function') {
            onOpen(overlay);
        }

        // Trigger animation
        requestAnimationFrame(() => overlay.classList.add('active'));

        return overlay;
    },

    close(btn) {
        const overlay = btn?.closest('.modal-overlay');
        if (!overlay) return;

        if (overlay._onClose) overlay._onClose();

        overlay.classList.remove('active');
        setTimeout(() => overlay.remove(), 300);
    },

    confirm(options) {
        const isEn = document.body.classList.contains('lang-en');
        const {
            title,
            message,
            confirmText = isEn ? 'Confirm' : 'ยืนยัน',
            cancelText = isEn ? 'Cancel' : 'ยกเลิก',
            onConfirm,
            danger = false,
            icon = danger ? 'fas fa-exclamation-triangle' : 'fas fa-question-circle'
        } = options;

        const modal = this.create({
            title,
            icon,
            content: `<p>${message}</p>`,
            footer: `
                <button class="btn-modal btn-modal-cancel" onclick="Modal.close(this)">${cancelText}</button>
                <button class="btn-modal btn-modal-confirm ${danger ? 'danger' : ''}" id="modal-confirm-btn">${confirmText}</button>
            `
        });

        modal.querySelector('#modal-confirm-btn').onclick = () => {
            if (onConfirm) onConfirm();
            Modal.close(modal.querySelector('.modal-close-btn'));
        };

        return modal;
    },

    alert(title, message, icon = 'fas fa-info-circle') {
        const isEn = document.body.classList.contains('lang-en');
        const okText = isEn ? 'OK' : 'ตกลง';
        return this.create({
            title,
            icon,
            content: `<p>${message}</p>`,
            footer: `<button class="btn-modal btn-modal-confirm" onclick="Modal.close(this)">${okText}</button>`
        });
    }
};

// ===== API HELPER =====
const API = {
    async request(url, options = {}) {
        const defaults = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CONFIG.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        const config = { ...defaults, ...options };
        if (options.body && typeof options.body === 'object') {
            config.body = JSON.stringify(options.body);
        }

        try {
            const response = await fetch(url, config);
            const text = await response.text();

            let data;
            try {
                data = text ? JSON.parse(text) : {};
            } catch (e) {
                console.error('Invalid JSON response:', text);
                throw new Error('Server returned invalid data. Please check console.');
            }

            if (!response.ok) {
                throw new Error(data.error || `Request failed with status ${response.status}`);
            }

            return data;
        } catch (error) {
            console.error('API Request Error:', error);
            throw error;
        }
    },

    get(url) {
        return this.request(url, { method: 'GET' });
    },

    post(url, body) {
        return this.request(url, { method: 'POST', body });
    },

    put(url, body) {
        return this.request(url, { method: 'PUT', body });
    },

    delete(url, body) {
        return this.request(url, { method: 'DELETE', body });
    }
};

// ===== COPY TO CLIPBOARD =====
async function copyToClipboard(text, button = null) {
    try {
        await navigator.clipboard.writeText(text);

        if (button) {
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i>';
            button.classList.add('copied');

            setTimeout(() => {
                button.innerHTML = originalHTML;
                button.classList.remove('copied');
            }, 2000);
        }

        Toast.success(document.body.classList.contains('lang-en') ? 'Copied!' : 'คัดลอกแล้ว!');
        return true;
    } catch (err) {
        Toast.error(document.body.classList.contains('lang-en') ? 'Failed to copy' : 'ไม่สามารถคัดลอกได้');
        return false;
    }
}

// ===== FORM VALIDATION =====
const Validator = {
    email(value) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    },

    password(value) {
        return value.length >= 8 && /[A-Z]/.test(value);
    },

    required(value) {
        return value !== null && value !== undefined && value.toString().trim() !== '';
    },

    minLength(value, min) {
        return value.length >= min;
    },

    match(value1, value2) {
        return value1 === value2;
    },

    getPasswordStrength(password) {
        let strength = 0;

        if (password.length >= 8) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;

        if (strength <= 1) return { level: 'weak', score: 33 };
        if (strength <= 2) return { level: 'medium', score: 66 };
        return { level: 'strong', score: 100 };
    }
};

// ===== DEBOUNCE =====
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ===== THROTTLE =====
function throttle(func, limit) {
    let inThrottle;
    return function (...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// ===== ESCAPE HTML =====
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ===== FORMAT DATE =====
function formatDate(dateString, locale = 'th-TH') {
    const date = new Date(dateString);
    return date.toLocaleDateString(locale, {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// ===== HELP MODAL =====
function showHelpModal(type) {
    const isEn = document.body.classList.contains('lang-en');

    const helpContent = {
        author: {
            title: isEn ? 'Author in Citation' : 'ผู้แต่งในการอ้างอิง',
            content: isEn ? `
                <h4>General Rules</h4>
                <p>Use the author's surname followed by initials for first and middle names.</p>
                <ul>
                    <li><strong>One author:</strong> Smith, J. K.</li>
                    <li><strong>Two authors:</strong> Smith, J. K., & Johnson, M.</li>
                    <li><strong>3-20 authors:</strong> List all authors</li>
                    <li><strong>21+ authors:</strong> List first 19, then "...", then last author</li>
                </ul>
            ` : `
                <h4>หลักการทั่วไป</h4>
                <p>ใช้นามสกุลของผู้แต่งตามด้วยอักษรย่อชื่อ</p>
                <ul>
                    <li><strong>ผู้แต่ง 1 คน:</strong> สมชาย ใจดี</li>
                    <li><strong>ผู้แต่ง 2 คน:</strong> สมชาย ใจดี และ สมหญิง ใจงาม</li>
                    <li><strong>ผู้แต่ง 3-20 คน:</strong> ระบุทุกคน</li>
                    <li><strong>ผู้แต่ง 21+ คน:</strong> ระบุ 19 คนแรก แล้วตามด้วย "..." และผู้แต่งคนสุดท้าย</li>
                </ul>
            `
        },
        place: {
            title: isEn ? 'Place in Citation' : 'สถานที่ในการอ้างอิง',
            content: isEn ? `
                <h4>Publication Place</h4>
                <p>In APA 7, publication location is generally not required for most sources.</p>
                <ul>
                    <li>For books: Publisher name only (no location needed)</li>
                    <li>For reports: Include organization location if not well-known</li>
                </ul>
            ` : `
                <h4>สถานที่พิมพ์</h4>
                <p>ใน APA 7 ไม่จำเป็นต้องระบุสถานที่พิมพ์สำหรับแหล่งข้อมูลส่วนใหญ่</p>
                <ul>
                    <li>หนังสือ: ระบุเฉพาะชื่อสำนักพิมพ์ (ไม่ต้องระบุสถานที่)</li>
                    <li>รายงาน: ระบุที่ตั้งองค์กรหากไม่เป็นที่รู้จัก</li>
                </ul>
            `
        },
        publisher: {
            title: isEn ? 'Publisher in Citation' : 'สำนักพิมพ์ในการอ้างอิง',
            content: isEn ? `
                <h4>Publisher Name</h4>
                <p>Write the publisher name as it appears on the source.</p>
                <ul>
                    <li>Omit terms like "Inc.", "Co.", "Publishers"</li>
                    <li>Keep "Press" and "Books" in the name</li>
                    <li>For multiple publishers, separate with semicolons</li>
                </ul>
            ` : `
                <h4>ชื่อสำนักพิมพ์</h4>
                <p>เขียนชื่อสำนักพิมพ์ตามที่ปรากฏในแหล่งข้อมูล</p>
                <ul>
                    <li>ตัดคำว่า "จำกัด" หรือ "มหาชน" ออก</li>
                    <li>คงคำว่า "สำนักพิมพ์" ไว้</li>
                    <li>หากมีหลายสำนักพิมพ์ ใช้ ; คั่น</li>
                </ul>
            `
        }
    };

    const help = helpContent[type] || helpContent.author;
    Modal.create({
        title: help.title,
        content: help.content,
        footer: `<button class="btn btn-primary" onclick="Modal.close(this)">OK</button>`
    });
}

// ===== LOADING STATE =====
function setLoading(element, loading = true) {
    if (loading) {
        element.dataset.originalContent = element.innerHTML;
        element.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        element.disabled = true;
    } else {
        element.innerHTML = element.dataset.originalContent;
        element.disabled = false;
    }
}

// ===== INITIALIZE =====
document.addEventListener('DOMContentLoaded', function () {
    Toast.init();

    // Add page enter animation
    document.body.classList.add('page-enter');
});
