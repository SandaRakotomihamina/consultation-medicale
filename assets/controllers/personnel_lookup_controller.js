import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['matricule', 'nom', 'grade', 'errorMessage'];
    static values = { checkUserExists: Boolean };
    
    connect() {
        this.debounceTimer = null;
        // Par défaut, on vérifie l'existence dans User (pour new_user)
        if (this.hasCheckUserExistsValue === false) {
            this.checkUserExistsValue = true;
        }
    }

    async searchPersonnel() {
        const matricule = this.matriculeTarget.value.trim();

        if (!matricule) {
            clearTimeout(this.debounceTimer);
            this.clearErrors();
            if (this.hasNomTarget) {
                this.nomTarget.value = '';
            }
            if (this.hasGradeTarget) {
                this.gradeTarget.value = '';
            }
            return;
        }

        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(async () => {
            try {
                // Vérifier si l'utilisateur existe déjà dans la table User (si activé)
                if (this.checkUserExistsValue) {
                    const checkResponse = await fetch('/api/check-user-exists', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            matricule: matricule,
                            username: ''
                        })
                    });
                    const checkData = await checkResponse.json();

                    // Si l'utilisateur existe déjà avec ce matricule, afficher une erreur
                    if (checkData.errors.matricule) {
                        this.showError(checkData.errors.matricule);
                        this.matriculeTarget.style.borderColor = '#dc3545';
                        this.matriculeTarget.style.backgroundColor = '#fff5f5';
                        this.nomTarget.value = '';
                        this.gradeTarget.value = '';
                        return;
                    }
                }

                // Chercher dans l'API personnel-local
                const response = await fetch(`/api/personnel-local/${encodeURIComponent(matricule)}`);
                const data = await response.json();

                if (data.found) {
                    this.hideError();
                    this.nomTarget.value = data.nom || '';
                    this.gradeTarget.value = data.grade || '';
                    
                    this.matriculeTarget.style.borderColor = '#28a745';
                    this.matriculeTarget.style.backgroundColor = '#f0fff4'; 
                } else {
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
        }, 500);
    }

    showError(message) {
        if (this.hasErrorMessageTarget) {
            this.errorMessageTarget.textContent = message;
            this.errorMessageTarget.style.display = 'block';
        }
    }

    hideError() {
        if (this.hasErrorMessageTarget) {
            this.errorMessageTarget.textContent = '';
            this.errorMessageTarget.style.display = 'none';
        }
    }

    clearErrors() {
        this.hideError();
        this.matriculeTarget.style.borderColor = '#d1d1d1';
        this.matriculeTarget.style.backgroundColor = '#fafafa';
    }
}
