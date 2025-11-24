import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.isLoading = false;
        
        // Récupérer les éléments
        this.feed = document.getElementById('consultations-feed');
        this.loading = document.getElementById('loading-indicator');
        
        window.addEventListener('scroll', this.handleScroll.bind(this));
    }

    disconnect() {
        window.removeEventListener('scroll', this.handleScroll.bind(this));
    }

    handleScroll() {
        if (!this.feed || !this.loading) return;
        
        const scrollPosition = window.innerHeight + window.scrollY;
        const pageHeight = document.documentElement.scrollHeight;
        const threshold = 300;

        if (scrollPosition >= pageHeight - threshold && !this.isLoading) {
            this.loadMore();
        }
    }

    async loadMore() {
        if (!this.feed || !this.loading) return;
        
        this.isLoading = true;
        this.loading.style.display = 'block';

        const currentPage = parseInt(this.feed.dataset.page || '1');
        const nextPage = currentPage + 1;

        try {
            const response = await fetch(`/api/consultations/load-more?page=${nextPage}`);
            const data = await response.json();

            if (data.html && data.count > 0) {
                this.feed.insertAdjacentHTML('beforeend', data.html);
                this.feed.dataset.page = nextPage;
                this.loading.style.display = 'none';
            } else {
                this.loading.innerHTML = '<p>Aucune consultation supplémentaire</p>';
            }
        } catch (error) {
            console.error('Erreur lors du chargement:', error);
            this.loading.innerHTML = '<p style="color: red;">Erreur lors du chargement</p>';
        } finally {
            this.isLoading = false;
        }
    }
}

