import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.showLoading();

        if (document.readyState === 'complete') {
            this.hideLoading();
        } else {
            window.addEventListener('load', () => this.hideLoading());
        }

        document.addEventListener('submit', (event) => {
            this.showLoading();
        });

        document.addEventListener('click', (event) => {
            const link = event.target.closest('a');
            if (link && !link.hasAttribute('data-turbo') && !link.getAttribute('href')?.startsWith('#')) {
                const href = link.getAttribute('href');
                if (href && !href.startsWith('javascript:') && !href.startsWith('mailto:')) {
                    this.showLoading();
                }
            }
        });

        if (window.Turbo) {
            document.addEventListener('turbo:submit-start', () => this.showLoading());
            document.addEventListener('turbo:load', () => this.hideLoading());
        }
    }

    showLoading() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.style.display = 'flex';
        }
    }

    hideLoading() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }
}
