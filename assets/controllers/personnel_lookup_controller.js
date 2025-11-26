import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['matricule', 'nom', 'grade', 'errorMessage'];
    
    connect() {
        this.debounceTimer = null;
    }

    async searchPersonnel() {
        // Effacer les erreurs précédentes
        this.clearErrors();

        const matricule = this.matriculeTarget.value.trim();

        // Si le champ est vide, ne rien faire
        if (!matricule) {
            return;
        }

        // Utiliser un délai pour éviter trop de requêtes
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(async () => {
            try {
                // Appel à l'API pour rechercher le personnel
                const response = await fetch(`/api/personnel-local/${encodeURIComponent(matricule)}`);
                const data = await response.json();

                if (data.found) {
                    // Personnel trouvé
                    this.nomTarget.value = data.nom || '';
                    this.gradeTarget.value = data.grade || '';
                    
                    // Réinitialiser l'apparence
                    this.matriculeTarget.style.borderColor = '#d1d1d1';
                    this.matriculeTarget.style.backgroundColor = '#fafafa';
                } else {
                    // Personnel non trouvé
                    this.showError('Personnel non trouvé avec ce matricule.');
                    this.matriculeTarget.style.borderColor = '#dc3545';
                    this.matriculeTarget.style.backgroundColor = '#fff5f5';
                    this.nomTarget.value = '';
                    this.gradeTarget.value = '';
                }
            } catch (error) {
                console.error('Erreur lors de la recherche:', error);
                this.showError('Erreur lors de la recherche du personnel.');
                this.matriculeTarget.style.borderColor = '#dc3545';
                this.matriculeTarget.style.backgroundColor = '#fff5f5';
            }
        }, 500); // Délai de 500ms pour le debounce
    }

    showError(message) {
        this.errorMessageTarget.textContent = message;
        this.errorMessageTarget.style.display = 'block';
    }

    clearErrors() {
        this.errorMessageTarget.textContent = '';
        this.errorMessageTarget.style.display = 'none';
        this.matriculeTarget.style.borderColor = '#d1d1d1';
        this.matriculeTarget.style.backgroundColor = '#fafafa';
    }
}
