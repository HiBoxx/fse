/**
 * Page de Connexion CGT - JavaScript
 * Gestion de la modal d'adhésion et validation du formulaire
 */

(function() {
    'use strict';

    // Attendre que le DOM soit chargé
    document.addEventListener('DOMContentLoaded', function() {

        // Éléments de la modal
        const btnAdhesion = document.getElementById('btn-adhesion');
        const adhesionModal = document.getElementById('adhesion-modal');
        const modalOverlay = document.getElementById('modal-overlay');
        const modalClose = document.getElementById('modal-close');
        const btnCancel = document.getElementById('btn-cancel');
        const adhesionForm = document.getElementById('adhesion-form');

        /**
         * Ouvrir la modal
         */
        function openModal() {
            if (adhesionModal) {
                adhesionModal.style.display = 'flex';
                document.body.style.overflow = 'hidden'; // Empêcher le scroll

                // Focus sur le premier champ
                setTimeout(() => {
                    const firstInput = adhesionModal.querySelector('input:not([type="hidden"])');
                    if (firstInput) {
                        firstInput.focus();
                    }
                }, 100);
            }
        }

        /**
         * Fermer la modal
         */
        function closeModal() {
            if (adhesionModal) {
                adhesionModal.style.display = 'none';
                document.body.style.overflow = ''; // Restaurer le scroll
            }
        }

        /**
         * Event listeners pour ouvrir/fermer la modal
         */
        if (btnAdhesion) {
            btnAdhesion.addEventListener('click', openModal);
        }

        if (modalClose) {
            modalClose.addEventListener('click', closeModal);
        }

        if (btnCancel) {
            btnCancel.addEventListener('click', closeModal);
        }

        if (modalOverlay) {
            modalOverlay.addEventListener('click', closeModal);
        }

        // Fermer avec la touche Echap
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && adhesionModal && adhesionModal.style.display === 'flex') {
                closeModal();
            }
        });

        /**
         * Validation du formulaire
         */
        if (adhesionForm) {
            adhesionForm.addEventListener('submit', function(e) {
                const isValid = validateForm();

                if (!isValid) {
                    e.preventDefault();
                    return false;
                }

                // Afficher un indicateur de chargement
                const submitBtn = adhesionForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<svg class="spinner" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle></svg> Envoi en cours...';
                }
            });
        }

        /**
         * Valider le formulaire
         */
        function validateForm() {
            let isValid = true;
            const errors = [];

            // Validation email
            const email = document.getElementById('email');
            if (email && email.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email.value)) {
                    isValid = false;
                    errors.push('L\'adresse email n\'est pas valide');
                    highlightError(email);
                }
            }

            // Validation téléphone (10 chiffres)
            const tel = document.getElementById('tel');
            if (tel && tel.value) {
                const telRegex = /^[0-9]{10}$/;
                const cleanTel = tel.value.replace(/\s/g, '');
                if (!telRegex.test(cleanTel)) {
                    isValid = false;
                    errors.push('Le numéro de téléphone doit contenir 10 chiffres');
                    highlightError(tel);
                }
            }

            // Validation code postal (5 chiffres)
            const codePostal = document.getElementById('code_postal');
            if (codePostal && codePostal.value) {
                const cpRegex = /^[0-9]{5}$/;
                if (!cpRegex.test(codePostal.value)) {
                    isValid = false;
                    errors.push('Le code postal doit contenir 5 chiffres');
                    highlightError(codePostal);
                }
            }

            // Validation SIRET (14 chiffres) - optionnel
            const siret = document.getElementById('entreprise_siret');
            if (siret && siret.value) {
                const siretRegex = /^[0-9]{14}$/;
                if (!siretRegex.test(siret.value)) {
                    isValid = false;
                    errors.push('Le numéro SIRET doit contenir 14 chiffres');
                    highlightError(siret);
                }
            }

            // Validation Code APE/NAF - optionnel
            const codeApe = document.getElementById('code_ape_naf');
            if (codeApe && codeApe.value) {
                const apeRegex = /^[0-9]{4}[A-Z]$/;
                if (!apeRegex.test(codeApe.value.toUpperCase())) {
                    isValid = false;
                    errors.push('Le code APE/NAF doit être au format 1234A');
                    highlightError(codeApe);
                }
            }

            // Validation date de naissance (pas dans le futur)
            const dateNaissance = document.getElementById('date_naissance');
            if (dateNaissance && dateNaissance.value) {
                const selectedDate = new Date(dateNaissance.value);
                const today = new Date();
                if (selectedDate > today) {
                    isValid = false;
                    errors.push('La date de naissance ne peut pas être dans le futur');
                    highlightError(dateNaissance);
                }
            }

            // Vérifier les conditions acceptées
            const accepteConditions = document.getElementById('accepte_conditions');
            if (accepteConditions && !accepteConditions.checked) {
                isValid = false;
                errors.push('Vous devez accepter que vos données soient traitées');
                highlightError(accepteConditions);
            }

            const accepteRgpd = document.getElementById('accepte_rgpd');
            if (accepteRgpd && !accepteRgpd.checked) {
                isValid = false;
                errors.push('Vous devez prendre connaissance de la politique de confidentialité');
                highlightError(accepteRgpd);
            }

            // Afficher les erreurs
            if (!isValid && errors.length > 0) {
                showErrors(errors);
            }

            return isValid;
        }

        /**
         * Mettre en évidence un champ avec erreur
         */
        function highlightError(field) {
            if (field) {
                field.style.borderColor = '#ef4444';
                field.style.backgroundColor = '#fee2e2';

                // Retirer le highlight après correction
                field.addEventListener('input', function() {
                    field.style.borderColor = '';
                    field.style.backgroundColor = '';
                }, { once: true });

                field.addEventListener('change', function() {
                    field.style.borderColor = '';
                    field.style.backgroundColor = '';
                }, { once: true });
            }
        }

        /**
         * Afficher les erreurs
         */
        function showErrors(errors) {
            // Supprimer les anciennes erreurs
            const oldErrorBox = document.querySelector('.validation-errors');
            if (oldErrorBox) {
                oldErrorBox.remove();
            }

            // Créer la box d'erreurs
            const errorBox = document.createElement('div');
            errorBox.className = 'validation-errors';
            errorBox.style.cssText = `
                background: #fee2e2;
                border: 2px solid #ef4444;
                color: #991b1b;
                padding: 1rem;
                border-radius: 8px;
                margin-bottom: 1.5rem;
            `;

            const errorTitle = document.createElement('strong');
            errorTitle.textContent = 'Veuillez corriger les erreurs suivantes :';
            errorBox.appendChild(errorTitle);

            const errorList = document.createElement('ul');
            errorList.style.cssText = 'margin: 0.5rem 0 0 1.5rem; padding: 0;';

            errors.forEach(error => {
                const li = document.createElement('li');
                li.textContent = error;
                li.style.marginBottom = '0.25rem';
                errorList.appendChild(li);
            });

            errorBox.appendChild(errorList);

            // Insérer au début du formulaire
            const modalBody = document.querySelector('.modal-body');
            if (modalBody) {
                modalBody.insertBefore(errorBox, modalBody.firstChild);

                // Scroll vers le haut de la modal
                modalBody.scrollTop = 0;
            }

            // Retirer après 10 secondes
            setTimeout(() => {
                if (errorBox.parentNode) {
                    errorBox.remove();
                }
            }, 10000);
        }

        /**
         * Auto-formatage des champs
         */

        // Formatage téléphone
        const telInput = document.getElementById('tel');
        if (telInput) {
            telInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 10) {
                    value = value.substring(0, 10);
                }
                e.target.value = value;
            });
        }

        const telEntreprise = document.getElementById('entreprise_tel');
        if (telEntreprise) {
            telEntreprise.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 10) {
                    value = value.substring(0, 10);
                }
                e.target.value = value;
            });
        }

        // Formatage code postal
        const codePostalInput = document.getElementById('code_postal');
        if (codePostalInput) {
            codePostalInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 5) {
                    value = value.substring(0, 5);
                }
                e.target.value = value;
            });
        }

        const codePostalEntreprise = document.getElementById('entreprise_code_postal');
        if (codePostalEntreprise) {
            codePostalEntreprise.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 5) {
                    value = value.substring(0, 5);
                }
                e.target.value = value;
            });
        }

        // Formatage SIRET
        const siretInput = document.getElementById('entreprise_siret');
        if (siretInput) {
            siretInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 14) {
                    value = value.substring(0, 14);
                }
                e.target.value = value;
            });
        }

        // Formatage Code APE/NAF en majuscules
        const codeApeInput = document.getElementById('code_ape_naf');
        if (codeApeInput) {
            codeApeInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.toUpperCase();
            });
        }

        /**
         * Ouvrir automatiquement la modal si présent dans l'URL
         */
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('action') === 'adhesion' || urlParams.get('adhesion') === 'form') {
            openModal();
        }

        // Si succès, afficher la modal avec le message
        if (urlParams.get('adhesion') === 'success') {
            openModal();
        }

        /**
         * Animation spinner pour le bouton submit
         */
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            .spinner {
                animation: spin 1s linear infinite;
            }
        `;
        document.head.appendChild(style);

    });

})();
