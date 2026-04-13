/**
 * Babybib - Premium Onboarding Tour
 * Handles first-time user guidance on generate.php
 */

class BabybibTour {
    constructor() {
        this.completedKey = 'babybib_tour_completed';
        this.steps = this.getSteps();
        this.currentStep = 0;
        this.overlay = null;
        this.spotlight = null;
        this.tooltip = null;
        this.isTyping = false;
        this.trackingLoop = null;
        this.activeElement = null;
    }

    getSteps() {
        return [
            {
                element: '[data-code="book"]',
                title: 'เริ่มต้นที่นี่!',
                message: 'คลิกที่ "หนังสือ" เพื่อเริ่มต้นสร้างบรรณานุกรมเล่มแรกของคุณ',
                action: 'click',
                onNext: () => {
                    // Wait for form transition and scroll
                    return new Promise(resolve => setTimeout(resolve, 1000));
                }
            },
            {
                element: '#author-section-card',
                title: 'ใส่ชื่อผู้แต่ง',
                message: 'ส่วนนี้ใช้สำหรับกรอกชื่อผู้แต่ง คุณสามารถเพิ่มผู้แต่งได้หลายคนครับ',
                action: 'next'
            },
            {
                element: '#info-section-card',
                title: 'กรอกข้อมูลที่ต้องการ',
                message: 'ระบบจะช่วยคุณกรอกข้อมูลขของหนังสือ เพื่อให้คุณเห็นภาพการทำงานครับ',
                action: 'auto-fill',
                data: {
                    'title': 'การจัดการสารสนเทศในยุคดิจิทัล',
                    'year': '2567',
                    'publisher': 'บริษัท บีบี กราฟิก จำกัด'
                }
            },
            {
                element: '#result-box-bib',
                title: 'ตัวอย่างบรรณานุกรม',
                message: 'นี่คือหน้าตาบรรณานุกรมที่ระบบสร้างให้แบบเรียลไทม์ตามมาตรฐาน APA 7th!',
                action: 'next'
            },
            {
                element: '.btn-generate',
                title: 'บันทึกข้อมูล',
                message: 'เมื่อมั่นใจแล้ว กด "บันทึกบรรณานุกรม" เพื่อเก็บเข้าคลังส่วนตัวของคุณได้เลย!',
                action: 'final-click'
            }
        ];
    }

    isFirstVisit() {
        return !localStorage.getItem(this.completedKey);
    }

    init() {
        if (!this.isFirstVisit()) return;
        
        console.log('Starting Babybib Tour...');
        this.createUI();
        this.showStep(0);
    }

    createUI() {
        // Overlay
        this.overlay = document.createElement('div');
        this.overlay.className = 'tour-overlay';
        
        // Spotlight
        this.spotlight = document.createElement('div');
        this.spotlight.className = 'tour-spotlight';
        
        // Tooltip container
        this.tooltip = document.createElement('div');
        this.tooltip.className = 'tour-tooltip';
        
        document.body.appendChild(this.overlay);
        document.body.appendChild(this.spotlight);
        document.body.appendChild(this.tooltip);

        // Add CSS
        const style = document.createElement('style');
        style.textContent = `
            .tour-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.25); /* Lighter dimming */
                backdrop-filter: none; /* Removed blur as requested */
                -webkit-backdrop-filter: none;
                z-index: 9990;
                pointer-events: none;
                opacity: 1;
                transition: opacity 0.3s ease;
            }
            .tour-spotlight {
                position: fixed;
                z-index: 9991;
                pointer-events: none;
                border-radius: 12px;
                background: transparent;
                /* Premium Brightness Filter: Makes it pop without blur or fog */
                backdrop-filter: brightness(1.3) contrast(1.1);
                -webkit-backdrop-filter: brightness(1.3) contrast(1.1);
                border: 3px solid white;
                box-shadow: 0 0 50px rgba(255, 255, 255, 0.6), 0 0 0 9999px rgba(0, 0, 0, 0.45);
                transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            }
            .tour-tooltip {
                position: fixed;
                z-index: 9992;
                background: rgba(255, 255, 255, 0.98); /* Solid background, no blur */
                border: 1px solid rgba(0, 0, 0, 0.1);
                border-radius: 16px;
                padding: 16px 20px;
                width: 280px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.15);
                font-family: inherit;
                transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
                opacity: 0;
                transform: translateY(10px);
            }
            .tour-tooltip.active {
                opacity: 1;
                transform: translateY(0);
            }
            .tour-title {
                font-size: 1.1rem;
                font-weight: 700;
                color: var(--primary);
                margin-bottom: 6px;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .tour-message {
                font-size: 0.9rem;
                color: #444;
                line-height: 1.5;
                margin-bottom: 12px;
            }
            .tour-next-btn {
                background: var(--primary-gradient);
                color: white;
                border: none;
                padding: 6px 16px;
                border-radius: 8px;
                font-weight: 600;
                font-size: 0.85rem;
                cursor: pointer;
                float: right;
                transition: transform 0.2s;
            }
            .tour-next-btn:hover {
                transform: scale(1.05);
            }
            .tour-spotlight.pulse {
                animation: spotlightPulse 2s infinite;
            }
            @keyframes spotlightPulse {
                0% { box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.4), 0 0 0 0px rgba(139, 92, 246, 0.4); }
                50% { box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.4), 0 0 0 15px rgba(139, 92, 246, 0.2); }
                100% { box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.4), 0 0 0 0px rgba(139, 92, 246, 0); }
            }
            .form-input.highlight {
                border-color: var(--primary) !important;
                box-shadow: 0 0 0 3px var(--primary-light) !important;
            }
        `;
        document.head.appendChild(style);
    }

    async showStep(index) {
        if (index >= this.steps.length) {
            this.finish();
            return;
        }

        this.currentStep = index;
        const step = this.steps[index];
        const el = document.querySelector(step.element);

        if (!el) {
            console.error('Element not found for tour step:', step.element);
            this.finish();
            return;
        }

        // Positioning & Tracking
        this.activeElement = el;
        if (this.currentStep > 0) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        
        this.startTracking();
        this.updateTooltip(el, step);

        // Interaction
        if (step.action === 'click') {
            const clickHandler = async () => {
                el.removeEventListener('click', clickHandler);
                if (step.onNext) await step.onNext();
                this.showStep(index + 1);
            };
            el.addEventListener('click', clickHandler);
        } else if (step.action === 'next') {
            // Next button is handled by updateTooltip
        } else if (step.action === 'auto-fill') {
            // Fill data after a small delay
            setTimeout(() => this.runAutoFill(step.data), 1000);
        } else if (step.action === 'final-click') {
            const finalClick = () => {
                el.removeEventListener('click', finalClick);
                this.finish();
            };
            el.addEventListener('click', finalClick);
        }
    }

    startTracking() {
        if (this.trackingLoop) cancelAnimationFrame(this.trackingLoop);
        
        const track = () => {
            if (this.activeElement) {
                this.updateSpotlight(this.activeElement);
                this.updateTooltipPosition(this.activeElement);
                this.trackingLoop = requestAnimationFrame(track);
            }
        };
        this.trackingLoop = requestAnimationFrame(track);
    }

    updateSpotlight(el) {
        const rect = el.getBoundingClientRect();
        const padding = 12;
        
        // Since spotlight is fixed, use rect values directly
        this.spotlight.style.width = `${rect.width + padding * 2}px`;
        this.spotlight.style.height = `${rect.height + padding * 2}px`;
        this.spotlight.style.top = `${rect.top - padding}px`;
        this.spotlight.style.left = `${rect.left - padding}px`;
        
        if (this.currentStep === 0 || this.currentStep === 2) {
            this.spotlight.classList.add('pulse');
        } else {
            this.spotlight.classList.remove('pulse');
        }
    }

    updateTooltip(el, step) {
        this.tooltip.innerHTML = `
            <div class="tour-title"><i class="fas fa-sparkles"></i> ${step.title}</div>
            <div class="tour-message">${step.message}</div>
            ${(step.action === 'next' || step.action === 'auto-fill') ? `<button class="tour-next-btn" id="tour-next">ถัดไป</button>` : ''}
        `;
        
        this.updateTooltipPosition(el);
        this.tooltip.classList.add('active');

        if (step.action === 'next' || step.action === 'auto-fill') {
            document.getElementById('tour-next').onclick = () => {
                this.showStep(this.currentStep + 1);
            };
        }
    }

    updateTooltipPosition(el) {
        if (!this.tooltip) return;
        const rect = el.getBoundingClientRect();
        
        // Position tooltip relative to viewport
        let top = rect.bottom + 20;
        let left = rect.left + (rect.width / 2) - 140;

        // Boundary check
        if (left < 10) left = 10;
        if (left + 280 > window.innerWidth) left = window.innerWidth - 290;
        
        // Flip if at bottom
        if (top + 160 > window.innerHeight) {
            top = rect.top - 180;
        }

        this.tooltip.style.top = `${top}px`;
        this.tooltip.style.left = `${left}px`;
    }

    async runAutoFill(data) {
        if (this.isTyping) return;
        this.isTyping = true;
        
        // Add author first if not enough? Selection defaults to 1 author.
        // Wait for dynamic fields to be ready
        let retry = 0;
        while (!document.getElementById('title') && retry < 10) {
            await new Promise(r => setTimeout(r, 200));
            retry++;
        }

        for (const [id, value] of Object.entries(data)) {
            const input = document.getElementById(id);
            if (input) {
                input.classList.add('highlight');
                await this.typeText(input, value);
                input.dispatchEvent(new Event('input', { bubbles: true }));
                await new Promise(r => setTimeout(r, 300));
                input.classList.remove('highlight');
            }
        }
        
        // Final author name
        const firstName = document.querySelector('input[name="first_names[]"]');
        const lastName = document.querySelector('input[name="last_names[]"]');
        if (firstName) await this.typeText(firstName, 'สมชาย');
        if (lastName) await this.typeText(lastName, 'รักการเรียน');

        this.isTyping = false;
    }

    async typeText(input, text) {
        input.value = '';
        for (let i = 0; i < text.length; i++) {
            input.value += text[i];
            await new Promise(r => setTimeout(r, 30 + Math.random() * 50));
        }
    }

    finish() {
        if (this.trackingLoop) cancelAnimationFrame(this.trackingLoop);
        this.activeElement = null;
        localStorage.setItem(this.completedKey, 'true');
        this.tooltip.classList.remove('active');
        this.overlay.style.opacity = '0';
        setTimeout(() => {
            this.overlay.remove();
            this.spotlight.remove();
            this.tooltip.remove();
        }, 300);
    }
}

// Injected into generate.php
document.addEventListener('DOMContentLoaded', () => {
    // Only run if user is in first-step view
    const isMainView = !document.getElementById('resource-selection').classList.contains('hidden');
    if (isMainView) {
        const tour = new BabybibTour();
        setTimeout(() => tour.init(), 1000); // Give time for animations
    }
});
