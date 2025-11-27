import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        // Afficher l'animation au chargement initial
        this.showLoading();

        // Cacher l'animation une fois la page complètement chargée
        if (document.readyState === 'complete') {
            this.hideLoading();
        } else {
            window.addEventListener('load', () => this.hideLoading());
        }

        // Intercepter toutes les soumissions de formulaire
        document.addEventListener('submit', (event) => {
            // Afficher l'animation avant la soumission
            this.showLoading();
        });

        // Intercepter les clics sur les liens (pour la navigation)
        document.addEventListener('click', (event) => {
            const link = event.target.closest('a');
            if (link && !link.hasAttribute('data-turbo') && !link.getAttribute('href')?.startsWith('#')) {
                // Vérifier que ce n'est pas un lien local/ancre
                const href = link.getAttribute('href');
                if (href && !href.startsWith('javascript:') && !href.startsWith('mailto:')) {
                    this.showLoading();
                }
            }
        });

        // Écouter les événements Turbo (pour les requêtes AJAX)
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
