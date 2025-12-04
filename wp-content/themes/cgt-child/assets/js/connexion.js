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

        const accountRequestTrigger = document.getElementById('openAccountRequestModal');
        const accountRequestModal = document.getElementById('accountRequestModal');
        const accountRequestClose = document.getElementById('closeAccountRequestModal');
        const accountRequestOverlay = document.querySelector('[data-account-request-close]');
        const accountRequestForm = document.getElementById('accountRequestForm');
        const accountRequestPassword = document.getElementById('accountRequestPassword');
        const accountRequestPasswordConfirm = document.getElementById('accountRequestPasswordConfirm');
        const accountRequestMessage = document.getElementById('accountRequestMessage');

        /**
         * Ouvrir la modal
         */
        function openAdhesionModal() {
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
        function closeAdhesionModal() {
            if (adhesionModal) {
                adhesionModal.style.display = 'none';
                document.body.style.overflow = ''; // Restaurer le scroll
            }
        }

        function isAdhesionModalOpen() {
            return adhesionModal && adhesionModal.style.display === 'flex';
        }

        /**
         * Event listeners pour ouvrir/fermer la modal
         */
        if (btnAdhesion) {
            btnAdhesion.addEventListener('click', openAdhesionModal);
        }

        if (modalClose) {
            modalClose.addEventListener('click', closeAdhesionModal);
        }

        if (btnCancel) {
            btnCancel.addEventListener('click', closeAdhesionModal);
        }

        if (modalOverlay) {
            modalOverlay.addEventListener('click', closeAdhesionModal);
        }

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
         * Gestion de la modal de demande de compte adhérent.
         */
        function openAccountModal() {
            if (accountRequestModal) {
                accountRequestModal.classList.add('is-open');
                accountRequestModal.style.display = 'flex';
                accountRequestModal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';

                setTimeout(() => {
                    const firstInput = accountRequestModal.querySelector('input:not([type="hidden"])');
                    if (firstInput) {
                        firstInput.focus();
                    }
                }, 100);
            }
        }

        function closeAccountModal() {
            if (accountRequestModal) {
                accountRequestModal.classList.remove('is-open');
                accountRequestModal.style.display = 'none';
                accountRequestModal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }
        }

        function isAccountModalOpen() {
            return accountRequestModal && accountRequestModal.classList.contains('is-open');
        }

        function resetAccountMessage() {
            if (!accountRequestMessage) {
                return;
            }
            accountRequestMessage.classList.remove('is-visible');
            accountRequestMessage.classList.remove('is-success');
        }

        function showAccountMessage(type, text) {
            if (!accountRequestMessage) {
                return;
            }

            resetAccountMessage();
            accountRequestMessage.textContent = text;
            accountRequestMessage.classList.add('is-visible');

            if (type === 'success') {
                accountRequestMessage.classList.add('is-success');
            }
        }

        if (accountRequestTrigger) {
            accountRequestTrigger.addEventListener('click', function() {
                if (accountRequestForm) {
                    const resetFields = accountRequestForm.querySelectorAll('input, select');
                    resetFields.forEach(function(field) {
                        field.style.borderColor = '';
                        field.style.backgroundColor = '';
                    });
                }

                if (accountRequestMessage) {
                    resetAccountMessage();
                    accountRequestMessage.textContent = '';
                }

                openAccountModal();
            });
        }

        if (accountRequestClose) {
            accountRequestClose.addEventListener('click', function() {
                closeAccountModal();
            });
        }

        if (accountRequestOverlay) {
            accountRequestOverlay.addEventListener('click', function() {
                closeAccountModal();
            });
        }

        if (accountRequestForm) {
            accountRequestForm.addEventListener('submit', function(e) {
                resetAccountMessage();

                const requiredFields = accountRequestForm.querySelectorAll('input[required], select[required]');
                let hasEmptyField = false;

                requiredFields.forEach(function(field) {
                    let invalid = false;

                    if (field.type === 'checkbox') {
                        invalid = !field.checked;
                    } else if (!field.value) {
                        invalid = true;
                    }

                    if (invalid) {
                        hasEmptyField = true;
                        field.style.borderColor = '#ef4444';
                        field.style.backgroundColor = '#fee2e2';
                        if (field.type === 'checkbox') {
                            field.style.outline = '2px solid #ef4444';
                        }
                    } else {
                        field.style.borderColor = '';
                        field.style.backgroundColor = '';
                        if (field.type === 'checkbox') {
                            field.style.outline = '';
                        }
                    }
                });

                if (hasEmptyField) {
                    e.preventDefault();
                    showAccountMessage('error', 'Merci de renseigner tous les champs obligatoires.');
                    return;
                }

                if (accountRequestPassword && accountRequestPasswordConfirm) {
                    if (accountRequestPassword.value !== accountRequestPasswordConfirm.value) {
                        e.preventDefault();
                        showAccountMessage('error', 'Les mots de passe ne correspondent pas.');
                        accountRequestPassword.style.borderColor = '#ef4444';
                        accountRequestPasswordConfirm.style.borderColor = '#ef4444';
                        accountRequestPassword.style.backgroundColor = '#fee2e2';
                        accountRequestPasswordConfirm.style.backgroundColor = '#fee2e2';
                        accountRequestPassword.focus();
                        return;
                    } else {
                        accountRequestPassword.style.borderColor = '';
                        accountRequestPasswordConfirm.style.borderColor = '';
                        accountRequestPassword.style.backgroundColor = '';
                        accountRequestPasswordConfirm.style.backgroundColor = '';
                    }
                }
            });
        }

        if (accountRequestModal) {
            const state = accountRequestModal.dataset.accountRequestState;
            const type = accountRequestModal.dataset.accountRequestType || 'error';

            if (state && state !== 'success') {
                openAccountModal();
            }

            if (state && accountRequestMessage && accountRequestMessage.textContent.trim().length > 0) {
                accountRequestMessage.classList.add('is-visible');
                if (type === 'success') {
                    accountRequestMessage.classList.add('is-success');
                }
            }
        }

        // Fermer les modales avec la touche Échap
        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Escape') {
                return;
            }

            if (isAdhesionModalOpen()) {
                closeAdhesionModal();
            }

            if (isAccountModalOpen()) {
                closeAccountModal();
            }
        });

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

        // Formatage code postal banque
        const banqueCodePostal = document.getElementById('banque_code_postal');
        if (banqueCodePostal) {
            banqueCodePostal.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 5) {
                    value = value.substring(0, 5);
                }
                e.target.value = value;
            });
        }

        /**
         * Auto-calculation: Montant de prélèvement = 2 x Cotisation mensuelle
         */
        const cotisationInput = document.getElementById('cotisation_mensuelle');
        const prelevementInput = document.getElementById('prelevement_montant');

        if (cotisationInput && prelevementInput) {
            cotisationInput.addEventListener('input', function() {
                const cotisation = parseFloat(cotisationInput.value) || 0;
                const prelevement = (cotisation * 2).toFixed(2);
                prelevementInput.value = prelevement;
            });
        }

        /**
         * Signature Pad Implementation
         */
        const signaturePad = document.getElementById('signature-pad');
        const clearSignatureBtn = document.getElementById('clear-signature');
        const signatureInput = document.getElementById('signature');
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;
        let ctx = null;

        if (signaturePad) {
            ctx = signaturePad.getContext('2d');
            ctx.strokeStyle = '#111111';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';

            // Mouse events
            signaturePad.addEventListener('mousedown', startDrawing);
            signaturePad.addEventListener('mousemove', draw);
            signaturePad.addEventListener('mouseup', stopDrawing);
            signaturePad.addEventListener('mouseout', stopDrawing);

            // Touch events
            signaturePad.addEventListener('touchstart', function(e) {
                e.preventDefault();
                const touch = e.touches[0];
                const rect = signaturePad.getBoundingClientRect();
                lastX = touch.clientX - rect.left;
                lastY = touch.clientY - rect.top;
                isDrawing = true;
            });

            signaturePad.addEventListener('touchmove', function(e) {
                e.preventDefault();
                if (!isDrawing) return;

                const touch = e.touches[0];
                const rect = signaturePad.getBoundingClientRect();
                const x = touch.clientX - rect.left;
                const y = touch.clientY - rect.top;

                ctx.beginPath();
                ctx.moveTo(lastX, lastY);
                ctx.lineTo(x, y);
                ctx.stroke();

                lastX = x;
                lastY = y;

                // Update hidden input
                signatureInput.value = signaturePad.toDataURL();
            });

            signaturePad.addEventListener('touchend', function(e) {
                e.preventDefault();
                isDrawing = false;
            });
        }

        function startDrawing(e) {
            isDrawing = true;
            const rect = signaturePad.getBoundingClientRect();
            lastX = e.clientX - rect.left;
            lastY = e.clientY - rect.top;
        }

        function draw(e) {
            if (!isDrawing) return;

            const rect = signaturePad.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(x, y);
            ctx.stroke();

            lastX = x;
            lastY = y;

            // Update hidden input with signature data
            signatureInput.value = signaturePad.toDataURL();
        }

        function stopDrawing() {
            isDrawing = false;
        }

        // Clear signature
        if (clearSignatureBtn) {
            clearSignatureBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (ctx && signaturePad) {
                    ctx.clearRect(0, 0, signaturePad.width, signaturePad.height);
                    signatureInput.value = '';
                }
            });
        }

        /**
         * Ouvrir automatiquement la modal si présent dans l'URL
         */
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('action') === 'adhesion' || urlParams.get('adhesion') === 'form') {
            openAdhesionModal();
        }

        // Si succès, afficher la modal avec le message
        if (urlParams.get('adhesion') === 'success') {
            openAdhesionModal();
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
