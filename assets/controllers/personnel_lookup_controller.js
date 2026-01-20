import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['matricule', 'nom', 'grade', 'libute', 'codute', 'local', 'errorMessage', 'roles', 'matriculeLabel', 'gradeLabel', 'nomLabel', 'matriculeField', 'gradeField', 'nomField', 'coduteField', 'libuteField', 'localField', 'suggestions'];
    static values = { checkUserExists: Boolean, userLibute: String, userLocal: String };
    
    connect() {
        this.debounceTimer = null;
        this.isUniteMode = false; // false = recherche personnel, true = recherche unité
        this.matriculeAllowed = true; // sera false si le personnel ne peut pas faire de demande ici

        // Attacher une protection de soumission si on est dans un formulaire
        this.formElement = this.element.closest('form');
        if (this.formElement) {
            this.formElement.addEventListener('submit', (e) => {
                if (!this.matriculeAllowed) {
                    e.preventDefault();
                    this.showError('Ce personnel ne peut pas faire de demande dans cette unité.');
                    if (this.hasMatriculeTarget) {
                        this.matriculeTarget.style.borderColor = '#dc3545';
                        this.matriculeTarget.style.backgroundColor = 'var(--bg-secondary)';
                    }
                }
            });
        }

        // Par défaut, on vérifie l'existence dans User (pour new_user)
        if (this.hasCheckUserExistsValue === false) {
            this.checkUserExistsValue = true;
        }

        // Si la cible roles est présente, initialiser l'état selon la sélection actuelle
        if (this.hasRolesTarget) {
            this.updateModeFromRoles();
        }

        // Mettre à jour les labels au chargement
        this.updateLabels();

        // Fermer les suggestions en cliquant en dehors
        document.addEventListener('click', (e) => {
            if (!this.element.contains(e.target)) {
                this.closeSuggestions();
            }
        });
    }

    onRoleChange() {
        this.updateModeFromRoles();
        this.updateLabels();
        // clear fields when switching mode
        if (this.hasMatriculeTarget) {
            this.matriculeTarget.value = '';
            this.matriculeTarget.style.borderColor = '';
            this.matriculeTarget.style.backgroundColor = '';
        }
        if (this.hasNomTarget) this.nomTarget.value = '';
        if (this.hasGradeTarget) this.gradeTarget.value = '';
        if (this.hasLibuteTarget) this.libuteTarget.value = '';
        if (this.hasCoduteTarget) this.coduteTarget.value = '';
        if (this.hasLocalTarget) this.localTarget.value = '';
        this.matriculeAllowed = true;
        this.closeSuggestions();
    }

    updateModeFromRoles() {
        try {
            const select = this.rolesTarget;
            if (!select) return;
            const values = Array.from(select.selectedOptions || []).map(o => o.value);
            this.isUniteMode = values.includes('ROLE_USER');
        } catch (e) {
            this.isUniteMode = false;
        }
    }

    updateLabels() {
        if (this.isUniteMode) {
            if (this.hasMatriculeLabelTarget) this.matriculeLabelTarget.textContent = 'Libelé de l\'unité';
            if (this.hasGradeLabelTarget) this.gradeLabelTarget.textContent = 'Code unité';
            if (this.hasNomLabelTarget) this.nomLabelTarget.textContent = 'Localisation';
            
            // Afficher les champs CODUTE, LIBUTE, LOCAL et masquer matricule, grade, nom
            if (this.hasMatriculeFieldTarget) this.matriculeFieldTarget.style.display = 'block';
            if (this.hasGradeFieldTarget) this.gradeFieldTarget.style.display = 'none';
            if (this.hasNomFieldTarget) this.nomFieldTarget.style.display = 'none';
            if (this.hasCoduteFieldTarget) this.coduteFieldTarget.style.display = 'block';
            if (this.hasLibuteFieldTarget) this.libuteFieldTarget.style.display = 'none';
            if (this.hasLocalFieldTarget) this.localFieldTarget.style.display = 'block';
        } else {
            if (this.hasMatriculeLabelTarget) this.matriculeLabelTarget.textContent = 'Matricule';
            if (this.hasGradeLabelTarget) this.gradeLabelTarget.textContent = 'Titre';
            if (this.hasNomLabelTarget) this.nomLabelTarget.textContent = 'Nom';
            
            // Afficher les champs matricule, grade, nom et masquer CODUTE, LIBUTE, LOCAL
            if (this.hasMatriculeFieldTarget) this.matriculeFieldTarget.style.display = 'block';
            if (this.hasGradeFieldTarget) this.gradeFieldTarget.style.display = 'block';
            if (this.hasNomFieldTarget) this.nomFieldTarget.style.display = 'block';
            if (this.hasCoduteFieldTarget) this.coduteFieldTarget.style.display = 'none';
            if (this.hasLibuteFieldTarget) this.libuteFieldTarget.style.display = 'none';
            if (this.hasLocalFieldTarget) this.localFieldTarget.style.display = 'none';
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
            if (this.hasLibuteTarget) {
                this.libuteTarget.value = '';
            }
            if (this.hasCoduteTarget) {
                this.coduteTarget.value = '';
            }
            if (this.hasLocalTarget) {
                this.localTarget.value = '';
            }
            this.closeSuggestions();
            return;
        }

        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(async () => {
            try {
                // Si on est en mode unité, chercher les suggestions d'unités
                if (this.isUniteMode) {
                    await this.searchUniteSuggestions(matricule);
                    return;
                }

                // Vérifier si l'utilisateur existe déjà dans la table User (si activé)
                // Quand on est en mode unité (recherche par LIBUTE), on ne fait pas cette vérification
                if (this.checkUserExistsValue && !this.isUniteMode) {
                    try {
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

                        // Si l'API refuse l'accès (ex: pas super-admin), on ignore et on continue
                        if (!checkResponse.ok) {
                            // pas d'arrêt ici: on continue la recherche principale
                            console.warn('check-user-exists non OK', checkResponse.status);
                        } else {
                            const checkData = await checkResponse.json();
                            // Si l'utilisateur existe déjà avec ce matricule, afficher une erreur
                            if (checkData && checkData.errors && checkData.errors.matricule) {
                                this.showError(checkData.errors.matricule);
                                this.matriculeTarget.style.borderColor = '#dc3545';
                                this.matriculeTarget.style.backgroundColor = 'var(--bg-secondary)';
                                this.nomTarget.value = '';
                                this.gradeTarget.value = '';
                                return;
                            }
                        }
                    } catch (e) {
                        // En cas d'erreur réseau, on ne bloque pas la recherche principale
                        console.warn('Erreur lors de la vérification d\'existance utilisateur:', e);
                    }
                }

                // Sinon, comportement précédent : chercher le personnel
                // À modifier : end-point pour appeler l'API de personnel (DEV ou PROD selon l'environnement)
                const response = await fetch(`/api/personnel/${encodeURIComponent(matricule)}`);

                if (!response.ok) {
                    if (response.status === 404) {
                        this.showError('Personnel non trouvé avec ce matricule.');
                    } else if (response.status === 403) {
                        this.showError('Accès refusé lors de la recherche du personnel.');
                    } else {
                        this.showError('Erreur lors de la recherche du personnel.');
                    }

                    this.matriculeTarget.style.borderColor = '#dc3545';
                    this.matriculeTarget.style.backgroundColor = 'var(--bg-secondary)';
                    this.nomTarget.value = '';
                    this.gradeTarget.value = '';
                    if (this.hasLibuteTarget) this.libuteTarget.value = '';
                    this.matriculeAllowed = false;
                } else {
                    const data = await response.json();

                    if (data.found) {
                        this.hideError();

                        // Préparer la LIBUTE du personnel
                        const personLibute = (data.LIBUTE || '').toString();

                        // Vérifier que la LIBUTE du personnel correspond à celle de l'utilisateur connecté
                        if (this.hasUserLibuteValue && this.userLibuteValue) {
                            const userUnite = ((this.userLibuteValue || '') + ' ' + (this.userLocalValue || '')).toString().trim();
                            console.log('Vérification LIBUTE:', { personLibute, userUnite });

                            if (personLibute !== '' && userUnite !== '' && personLibute !== userUnite) {
                                // Ne pas remplir les champs et bloquer la soumission
                                if (this.hasNomTarget) this.nomTarget.value = '';
                                if (this.hasGradeTarget) this.gradeTarget.value = '';
                                if (this.hasLibuteTarget) this.libuteTarget.value = '';

                                this.showError('Ce personnel ne peut pas faire de demande dans cette unité.');
                                this.matriculeTarget.style.borderColor = '#dc3545';
                                this.matriculeTarget.style.backgroundColor = 'var(--bg-secondary)';
                                this.matriculeAllowed = false;
                                return;
                            }
                        }

                        // Autorisé : remplir les champs
                        this.nomTarget.value = data.nom || '';
                        this.gradeTarget.value = data.grade || '';
                        if (this.hasLibuteTarget) this.libuteTarget.value = personLibute || '';

                        this.matriculeAllowed = true;
                        this.matriculeTarget.style.borderColor = '#28a745';
                        this.matriculeTarget.style.backgroundColor = 'var(--bg-secondary)'; 
                    } else {
                        this.showError('Personnel non trouvé avec ce matricule.');
                        this.matriculeTarget.style.borderColor = '#dc3545';
                        this.matriculeTarget.style.backgroundColor = 'var(--bg-secondary)';

                        this.nomTarget.value = '';
                        this.gradeTarget.value = '';
                    }
                }
            } catch (error) {
                console.error('Erreur lors de la recherche:', error);
                this.showError('Erreur lors de la recherche.');
                this.matriculeTarget.style.borderColor = '#dc3545';
                this.matriculeTarget.style.backgroundColor = 'var(--bg-secondary)';
            }
        }, 500);
    }

    async searchUniteSuggestions(searchTerm) {
        try {
            this.closeSuggestions();
            
            if (searchTerm.length < 2) {
                return;
            }

            // À modifier : end-point pour appeler l'API d'unité (DEV ou PROD selon l'environnement)
            const response = await fetch(`/api/unite-search?q=${encodeURIComponent(searchTerm)}`);

            if (!response.ok) {
                this.closeSuggestions();
                return;
            }

            const data = await response.json();
            const suggestions = data.suggestions || [];

            if (suggestions.length === 0) {
                this.closeSuggestions();
                return;
            }

            this.displaySuggestions(suggestions);
        } catch (error) {
            console.error('Erreur lors de la recherche des suggestions:', error);
            this.closeSuggestions();
        }
    }

    displaySuggestions(suggestions) {
        if (!this.hasSuggestionsTarget) {
            return;
        }

        const container = this.suggestionsTarget;
        container.innerHTML = '';

        suggestions.forEach((suggestion, index) => {
            const div = document.createElement('div');
            div.className = 'suggestion-item';
            div.innerHTML = `
                <strong>${suggestion.UNITY}</strong>
                <small>${suggestion.CODUTE}</small>
            `;
            div.style.cursor = 'pointer';
            div.style.padding = '0.75rem';
            div.style.borderBottom = '1px solid var(--border-color)';
            div.style.transition = 'background-color 0.2s ease';
            
            div.addEventListener('mouseenter', () => {
                div.style.backgroundColor = 'var(--bg-secondary, #f5f5f5)';
            });
            
            div.addEventListener('mouseleave', () => {
                div.style.backgroundColor = 'transparent';
            });

            div.addEventListener('click', () => {
                this.selectSuggestion(suggestion);
            });

            container.appendChild(div);
        });

        container.style.display = 'block';
    }

    async selectSuggestion(suggestion) {
        const unity = suggestion.UNITY || '';
        const parts = unity.split(' ');
        
        const codute = suggestion.CODUTE || '';
        const libute = parts[0] || '';
        const local = parts.slice(1).join(' ') || '';

        // Vérifier si l'unité existe déjà pour un utilisateur (en mode unité)
        if (this.isUniteMode && this.checkUserExistsValue) {
            try {
                const checkResponse = await fetch('/api/check-unite-exists', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        codute: codute,
                        libute: libute,
                        local: local
                    })
                });

                if (checkResponse.ok) {
                    const checkData = await checkResponse.json();
                    if (checkData && checkData.errors && checkData.errors.unite) {
                        // Une unité avec la même combinaison existe déjà
                        this.showError(checkData.errors.unite);
                        this.matriculeTarget.style.borderColor = '#dc3545';
                        this.matriculeTarget.style.backgroundColor = '#fff5f5';
                        this.closeSuggestions();
                        return;
                    }
                }
            } catch (e) {
                console.warn('Erreur lors de la vérification d\'existance de l\'unité:', e);
                // On continue même si la vérification échoue
            }
        }

        if (this.hasCoduteTarget) this.coduteTarget.value = codute;
        if (this.hasLibuteTarget) this.libuteTarget.value = libute;
        if (this.hasLocalTarget) this.localTarget.value = local;
        if (this.hasMatriculeTarget) this.matriculeTarget.value = unity;

        this.matriculeTarget.style.borderColor = '#28a745';
        this.matriculeTarget.style.backgroundColor = 'var(--bg-secondary)';

        this.hideError();
        this.closeSuggestions();
    }

    closeSuggestions() {
        if (this.hasSuggestionsTarget) {
            this.suggestionsTarget.style.display = 'none';
            this.suggestionsTarget.innerHTML = '';
        }
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
        this.matriculeTarget.style.backgroundColor = 'var(--bg-secondary)';
    }
}
