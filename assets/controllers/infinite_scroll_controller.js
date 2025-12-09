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
        const searchQuery = this.feed.dataset.searchQuery || ''; // Récupérer la requête de recherche

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
                this.loading.innerHTML = '<svg width="60px" height="60px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.29289 1.29289C9.48043 1.10536 9.73478 1 10 1H18C19.6569 1 21 2.34315 21 4V20C21 21.6569 19.6569 23 18 23H6C4.34315 23 3 21.6569 3 20V8C3 7.73478 3.10536 7.48043 3.29289 7.29289L9.29289 1.29289ZM18 3H11V8C11 8.55228 10.5523 9 10 9H5V20C5 20.5523 5.44772 21 6 21H18C18.5523 21 19 20.5523 19 20V4C19 3.44772 18.5523 3 18 3ZM6.41421 7H9V4.41421L6.41421 7ZM7 13C7 12.4477 7.44772 12 8 12H16C16.5523 12 17 12.4477 17 13C17 13.5523 16.5523 14 16 14H8C7.44772 14 7 13.5523 7 13ZM7 17C7 16.4477 7.44772 16 8 16H16C16.5523 16 17 16.4477 17 17C17 17.5523 16.5523 18 16 18H8C7.44772 18 7 17.5523 7 17Z" fill="#000000 " stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg><p style="text-align:center; padding: 1rem;">Aucune consultation supplémentaire</p>';
            }
        } catch (error) {
            console.error('Erreur lors du chargement:', error);
            this.loading.innerHTML = '<p style="color: red;">Erreur lors du chargement</p>';
        } finally {
            this.isLoading = false;
        }
    }
}
