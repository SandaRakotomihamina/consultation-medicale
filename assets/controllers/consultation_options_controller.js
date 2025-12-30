import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['exemptionSelect', 'exemptionInput', 'adresseSelect', 'adresseInput', 'debut', 'fin', 'patc'];

    connect() {
        this.initExemptions();
        this.initAdresses();
        this.setupToggles();
    }

    async initExemptions() {
        const select = this.hasExemptionSelectTarget ? this.exemptionSelectTarget : document.querySelector('select[name$="[Exemption]"]');
        if (!select) return;
        select.disabled = true;
        try {
            const res = await fetch('/api/exemption-options');
            const data = await res.json();
            select.innerHTML = '';
            for (const v of data.items || []) {
                const opt = document.createElement('option');
                opt.value = v; opt.textContent = v;
                select.appendChild(opt);
            }
        } catch (e) {
            console.warn('Impossible de charger les exemptions', e);
        }
    }

    async initAdresses() {
        const select = this.hasAdresseSelectTarget ? this.adresseSelectTarget : document.querySelector('select[name$="[Adrresse]"]');
        if (!select) return;
        select.disabled = true;
        try {
            const res = await fetch('/api/adresse-options');
            const data = await res.json();
            select.innerHTML = '';
            for (const v of data.items || []) {
                const opt = document.createElement('option');
                opt.value = v; opt.textContent = v;
                select.appendChild(opt);
            }
        } catch (e) {
            console.warn('Impossible de charger les adresses', e);
        }
    }

    setupToggles() {
        // find checkboxes by id pattern
        const exemptionCheckbox = document.getElementById('enable_exemption_checkbox');
        const debut = this.hasDebutTarget ? this.debutTarget : document.querySelector('input[name$="[debutExemption]"]');
        const fin = this.hasFinTarget ? this.finTarget : document.querySelector('input[name$="[finExemption]"]');
        const exemptionSelect = this.hasExemptionSelectTarget ? this.exemptionSelectTarget : document.querySelector('select[name$="[Exemption]"]');
        const exemptionAddBtn = document.querySelector('[data-consultation-options-target="exemptionAddBtn"]');
        if (exemptionCheckbox && exemptionSelect) {
            const today = new Date();
            // set min for debut to today
            if (debut) {
                debut.disabled = true;
                debut.setAttribute('min', today.toISOString().split('T')[0]);
            }
            if (fin) {
                fin.disabled = true;
            }

            exemptionCheckbox.checked = false;
            exemptionSelect.disabled = true;
            if (exemptionAddBtn) exemptionAddBtn.disabled = true;

            exemptionCheckbox.addEventListener('change', () => {
                const checked = exemptionCheckbox.checked;
                exemptionSelect.disabled = !checked;
                if (debut) debut.disabled = !checked;
                if (fin) fin.disabled = !checked;
                if (exemptionAddBtn) exemptionAddBtn.disabled = !checked;
                if (!checked) {
                    exemptionSelect.selectedIndex = -1;
                    if (debut) debut.value = '';
                    if (fin) fin.value = '';
                }
            });

            // When debut changes, update min for fin
            if (debut && fin) {
                debut.addEventListener('change', () => {
                    if (debut.value) {
                        fin.setAttribute('min', debut.value);
                        if (fin.value && fin.value < debut.value) {
                            fin.value = '';
                        }
                    } else {
                        fin.removeAttribute('min');
                    }
                });
            }
        }

        const adresseCheckbox = document.getElementById('enable_adresse_checkbox');
        const adresseSelect = this.hasAdresseSelectTarget ? this.adresseSelectTarget : document.querySelector('select[name$="[Adrresse]"]');
        const adresseAddBtn = document.querySelector('[data-consultation-options-target="adresseAddBtn"]');
        if (adresseCheckbox && adresseSelect) {
            adresseCheckbox.checked = false;
            adresseSelect.disabled = true;
            if (adresseAddBtn) adresseAddBtn.disabled = true;
            adresseCheckbox.addEventListener('change', () => {
                const checked = adresseCheckbox.checked;
                adresseSelect.disabled = !checked;
                if (adresseAddBtn) adresseAddBtn.disabled = !checked;
                if (!checked) adresseSelect.selectedIndex = -1;
            });
        }

        const patcCheckbox = document.getElementById('enable_patc_checkbox');
        const patcInput = this.hasPatcTarget ? this.patcTarget : document.querySelector('input[name$="[PATC]"]');
        if (patcCheckbox && patcInput) {
            patcCheckbox.checked = false;
            patcInput.disabled = true;
            patcCheckbox.addEventListener('change', () => {
                patcInput.disabled = !patcCheckbox.checked;
                if (!patcCheckbox.checked) patcInput.value = '';
            });
        }
    }

    async addExemption() {
        const exemptionCheckbox = document.getElementById('enable_exemption_checkbox');
        if (!exemptionCheckbox || !exemptionCheckbox.checked) return;

        const input = this.hasExemptionInputTarget ? this.exemptionInputTarget : document.getElementById('exemption_new_input');
        const select = this.hasExemptionSelectTarget ? this.exemptionSelectTarget : document.querySelector('select[name$="[Exemption]"]');
        const value = input ? input.value.trim() : '';
        if (!value) return;
        const res = await fetch('/api/exemption-options', {method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({value})});
        if (res.ok) {
            // add to select
            const opt = document.createElement('option'); opt.value = value; opt.textContent = value; opt.selected = true; select.appendChild(opt);
            input.value = '';
        }
    }

    async addAdresse() {
        const adresseCheckbox = document.getElementById('enable_adresse_checkbox');
        if (!adresseCheckbox || !adresseCheckbox.checked) return;

        const input = this.hasAdresseInputTarget ? this.adresseInputTarget : document.getElementById('adresse_new_input');
        const select = this.hasAdresseSelectTarget ? this.adresseSelectTarget : document.querySelector('select[name$="[Adrresse]"]');
        const value = input ? input.value.trim() : '';
        if (!value) return;
        const res = await fetch('/api/adresse-options', {method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({value})});
        if (res.ok) {
            const opt = document.createElement('option'); opt.value = value; opt.textContent = value; opt.selected = true; select.appendChild(opt);
            input.value = '';
        }
    }
}
