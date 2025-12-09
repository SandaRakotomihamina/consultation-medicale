import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.isLoading = false;
        
        this.feed = document.getElementById('consultations-feed');
        this.loading = document.getElementById('loading-indicator');
        this._boundHandleScroll = this.handleScroll.bind(this);
        window.addEventListener('scroll', this._boundHandleScroll);
    }

    disconnect() {
        if (this._boundHandleScroll) {
            window.removeEventListener('scroll', this._boundHandleScroll);
            this._boundHandleScroll = null;
        }
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
        const searchQuery = this.feed.dataset.searchQuery || '';

        try {
            let url = `/api/consultations/load-more?page=${nextPage}`;
            
            // Si on est sur une page de recherche (searchQuery non vide)
            if (searchQuery && searchQuery.trim()) {
                url = `/api/search/load-more?q=${encodeURIComponent(searchQuery)}&page=${nextPage}`;
            }

            const response = await fetch(url);
            const data = await response.json();

            if (data.html && data.count > 0) {
                this.feed.insertAdjacentHTML('beforeend', data.html);
                this.feed.dataset.page = nextPage;
                this.loading.style.display = 'none';
            } else {
                this.loading.innerHTML = '<p style="text-align:center; padding: 1rem;">Aucune consultation supplémentaire</p>';
            }
        } catch (error) {
            console.error('Erreur lors du chargement:', error);
            this.loading.innerHTML = '<p style="color: red;">Erreur lors du chargement</p>';
        } finally {
            this.isLoading = false;
        }
    }
}
