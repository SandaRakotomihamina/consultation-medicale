import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['button'];
    
    connect() {
        // Écouter les événements de scroll
        window.addEventListener('scroll', this.handleScroll.bind(this));
    }
    
    disconnect() {
        window.removeEventListener('scroll', this.handleScroll.bind(this));
    }
    
    handleScroll() {
        // Afficher le bouton si on a scrollé plus de 300px vers le bas
        if (window.pageYOffset > 300) {
            this.buttonTarget.classList.add('visible');
        } else {
            this.buttonTarget.classList.remove('visible');
        }
    }
    
    scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
}
