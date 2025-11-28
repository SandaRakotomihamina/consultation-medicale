import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { interval: { type: Number, default: 5000 } };
    static targets = ["slide"];

    connect() {
        this.current = 0;
        this.show(this.current);
        this.start();

        // Pause on hover
        this.element.addEventListener('mouseenter', () => this.stop());
        this.element.addEventListener('mouseleave', () => this.start());
    }

    disconnect() {
        this.stop();
    }

    start() {
        this.stop();
        this.timer = setInterval(() => this.next(), this.intervalValue);
    }

    stop() {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
    }

    show(index) {
        if (!this.hasSlideTarget) return;
        this.slideTargets.forEach((el, i) => {
            if (i === index) {
                el.style.display = '';
                el.classList.add('active');
                el.setAttribute('aria-hidden', 'false');
            } else {
                el.style.display = 'none';
                el.classList.remove('active');
                el.setAttribute('aria-hidden', 'true');
            }
        });
    }

    next() {
        if (!this.hasSlideTarget) return;
        this.current = (this.current + 1) % this.slideTargets.length;
        this.show(this.current);
    }

    previous() {
        if (!this.hasSlideTarget) return;
        this.current = (this.current - 1 + this.slideTargets.length) % this.slideTargets.length;
        this.show(this.current);
    }
}
