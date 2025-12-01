import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.showLoading();

        if (document.readyState === 'complete') {
            this.hideLoading();
        } else {
            window.addEventListener('load', () => this.hideLoading());
        }

        // Afficher le loader sur la soumission de formulaire
        this.handleSubmit = (event) => {
            // Vérifier que ce n'est pas un formulaire annulé (ex: dans une pop-up)
            const form = event.target;
            if (form && form.tagName === 'FORM') {
                this.showLoading();
            }
        };
        document.addEventListener('submit', this.handleSubmit);

        // Afficher le loader sur les clics de lien (navigation)
        this.handleLinkClick = (event) => {
            const link = event.target.closest('a');
            if (link && !link.hasAttribute('data-turbo') && !link.getAttribute('href')?.startsWith('#')) {
                const href = link.getAttribute('href');
                if (href && !href.startsWith('javascript:') && !href.startsWith('mailto:')) {
                    this.showLoading();
                }
            }
        };
        document.addEventListener('click', this.handleLinkClick);

        // Afficher le loader sur les événements Turbo
        if (window.Turbo) {
            this.handleTurboSubmit = () => this.showLoading();
            this.handleTurboLoad = () => this.hideLoading();
            document.addEventListener('turbo:submit-start', this.handleTurboSubmit);
            document.addEventListener('turbo:load', this.handleTurboLoad);
        }
    }

    disconnect() {
        // Nettoyer les event listeners lors de la déconnexion du contrôleur
        document.removeEventListener('submit', this.handleSubmit);
        document.removeEventListener('click', this.handleLinkClick);
        if (window.Turbo) {
            document.removeEventListener('turbo:submit-start', this.handleTurboSubmit);
            document.removeEventListener('turbo:load', this.handleTurboLoad);
        }
    }

    showLoading() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.style.display = 'flex';
            // Auto-hide après 15 secondes en cas de timeout
            if (this.loadingTimeout) {
                clearTimeout(this.loadingTimeout);
            }
            this.loadingTimeout = setTimeout(() => {
                this.hideLoading();
            }, 15000);
        }
    }

    hideLoading() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
        if (this.loadingTimeout) {
            clearTimeout(this.loadingTimeout);
        }
    }
}
