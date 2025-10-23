/**
 * Article Submission Form - Enhanced UX
 * Gestion des interactions, validation, et prévisualisations
 */

(function() {
    'use strict';

    // Attendre que le DOM soit chargé
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('article-submission-form');
        if (!form) return;

        // Initialiser tous les modules
        initCharacterCount();
        initFileUploadPreviews();
        initFormValidation();
        initSmoothScroll();
    });

    /**
     * Compteur de caractères pour les zones de texte
     */
    function initCharacterCount() {
        const charCounters = document.querySelectorAll('.char-count');

        charCounters.forEach(function(counter) {
            const targetId = counter.getAttribute('data-target');
            const targetElement = document.getElementById(targetId);

            if (!targetElement) return;

            // Fonction pour mettre à jour le compteur
            function updateCount() {
                const count = targetElement.value.length;
                const text = count === 0 ? '0 caractère' :
                             count === 1 ? '1 caractère' :
                             count + ' caractères';

                counter.textContent = text;

                // Ajouter une couleur si proche de la limite
                if (targetElement.maxLength && count > targetElement.maxLength * 0.9) {
                    counter.style.color = '#d00000';
                } else {
                    counter.style.color = '';
                }
            }

            // Écouter les changements
            targetElement.addEventListener('input', updateCount);
            targetElement.addEventListener('change', updateCount);

            // Initialiser le compteur
            updateCount();
        });
    }

    /**
     * Prévisualisation des fichiers uploadés
     */
    function initFileUploadPreviews() {
        const fileInputs = document.querySelectorAll('.file-input');

        fileInputs.forEach(function(input) {
            input.addEventListener('change', function(e) {
                handleFileSelection(e.target);
            });
        });

        function handleFileSelection(input) {
            const file = input.files[0];
            if (!file) {
                clearPreview(input.id);
                return;
            }

            // Vérifier la taille du fichier
            const maxSize = parseInt(input.getAttribute('data-max-size')) || 0;
            if (maxSize && file.size > maxSize) {
                const maxSizeMB = (maxSize / 1024 / 1024).toFixed(1);
                alert('Le fichier est trop volumineux. Taille maximum : ' + maxSizeMB + ' Mo');
                input.value = '';
                clearPreview(input.id);
                return;
            }

            // Vérifier le type MIME
            const acceptedTypes = input.accept.split(',').map(t => t.trim());
            if (acceptedTypes.length && !acceptedTypes.includes(file.type)) {
                alert('Type de fichier non autorisé. Formats acceptés : ' + input.accept);
                input.value = '';
                clearPreview(input.id);
                return;
            }

            // Afficher la prévisualisation
            displayPreview(input.id, file);
        }

        function displayPreview(inputId, file) {
            const previewId = 'preview_' + inputId.replace('cgt_article_', '');
            const previewContainer = document.getElementById(previewId);
            if (!previewContainer) return;

            // Nettoyer la prévisualisation précédente
            previewContainer.innerHTML = '';

            const previewItem = document.createElement('div');
            previewItem.className = 'file-preview-item';

            // Créer la prévisualisation selon le type
            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.className = 'file-preview-image';
                img.alt = file.name;

                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);

                previewItem.appendChild(img);
            } else if (file.type === 'application/pdf') {
                const icon = document.createElement('div');
                icon.className = 'file-preview-icon';
                icon.textContent = 'PDF';
                previewItem.appendChild(icon);
            }

            // Informations du fichier
            const info = document.createElement('div');
            info.className = 'file-preview-info';

            const name = document.createElement('div');
            name.className = 'file-preview-name';
            name.textContent = file.name;

            const size = document.createElement('div');
            size.className = 'file-preview-size';
            size.textContent = formatFileSize(file.size);

            info.appendChild(name);
            info.appendChild(size);
            previewItem.appendChild(info);

            // Bouton de suppression
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'file-preview-remove';
            removeBtn.textContent = 'Retirer';
            removeBtn.addEventListener('click', function() {
                const input = document.getElementById(inputId);
                if (input) {
                    input.value = '';
                }
                clearPreview(inputId);
            });

            previewItem.appendChild(removeBtn);
            previewContainer.appendChild(previewItem);
            previewContainer.classList.add('active');

            // Mettre à jour le label
            const label = document.querySelector('label[for="' + inputId + '"]');
            if (label) {
                const labelText = label.querySelector('.file-label-text');
                if (labelText) {
                    labelText.textContent = 'Fichier sélectionné';
                    label.style.borderColor = '#28a745';
                    label.style.background = '#d4edda';
                }
            }
        }

        function clearPreview(inputId) {
            const previewId = 'preview_' + inputId.replace('cgt_article_', '');
            const previewContainer = document.getElementById(previewId);
            if (previewContainer) {
                previewContainer.innerHTML = '';
                previewContainer.classList.remove('active');
            }

            // Restaurer le label
            const label = document.querySelector('label[for="' + inputId + '"]');
            if (label) {
                const labelText = label.querySelector('.file-label-text');
                if (labelText) {
                    if (inputId.includes('featured')) {
                        labelText.textContent = 'Choisir une image';
                    } else if (inputId.includes('pdf')) {
                        labelText.textContent = 'Choisir un PDF';
                    }
                    label.style.borderColor = '';
                    label.style.background = '';
                }
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Octets';
            const k = 1024;
            const sizes = ['Octets', 'Ko', 'Mo', 'Go'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
        }
    }

    /**
     * Validation du formulaire en temps réel
     */
    function initFormValidation() {
        const form = document.getElementById('article-submission-form');
        if (!form) return;

        const requiredFields = form.querySelectorAll('[required]');

        requiredFields.forEach(function(field) {
            // Validation en temps réel
            field.addEventListener('blur', function() {
                validateField(field);
            });

            field.addEventListener('input', function() {
                if (field.classList.contains('is-invalid')) {
                    validateField(field);
                }
            });
        });

        // Validation avant soumission
        form.addEventListener('submit', function(e) {
            let isValid = true;

            requiredFields.forEach(function(field) {
                if (!validateField(field)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();

                // Faire défiler jusqu'au premier champ invalide
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }

                // Afficher un message d'erreur global
                showFormError('Veuillez corriger les erreurs avant de soumettre le formulaire.');
            } else {
                // Afficher un indicateur de chargement
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<svg class="spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Envoi en cours...';
                }
            }
        });

        function validateField(field) {
            let isValid = true;
            let errorMessage = '';

            // Vérifier si le champ est vide
            if (field.hasAttribute('required') && !field.value.trim()) {
                isValid = false;
                errorMessage = 'Ce champ est obligatoire';
            }

            // Validation email
            if (field.type === 'email' && field.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(field.value)) {
                    isValid = false;
                    errorMessage = 'Adresse email invalide';
                }
            }

            // Validation checkbox
            if (field.type === 'checkbox' && field.hasAttribute('required') && !field.checked) {
                isValid = false;
                errorMessage = 'Vous devez accepter les conditions';
            }

            // Mettre à jour l'état visuel
            if (isValid) {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
                removeFieldError(field);
            } else {
                field.classList.remove('is-valid');
                field.classList.add('is-invalid');
                showFieldError(field, errorMessage);
            }

            return isValid;
        }

        function showFieldError(field, message) {
            removeFieldError(field);

            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            errorDiv.textContent = message;
            errorDiv.style.color = '#dc3545';
            errorDiv.style.fontSize = '0.875rem';
            errorDiv.style.marginTop = '0.25rem';

            field.parentNode.appendChild(errorDiv);
        }

        function removeFieldError(field) {
            const existingError = field.parentNode.querySelector('.field-error');
            if (existingError) {
                existingError.remove();
            }
        }

        function showFormError(message) {
            // Vérifier si un message d'erreur existe déjà
            let errorAlert = document.querySelector('.alert-error.form-validation-error');

            if (!errorAlert) {
                errorAlert = document.createElement('div');
                errorAlert.className = 'alert alert-error form-validation-error';
                errorAlert.innerHTML = '<svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg><span>' + message + '</span>';

                form.insertBefore(errorAlert, form.firstChild);

                // Auto-masquer après 5 secondes
                setTimeout(function() {
                    errorAlert.remove();
                }, 5000);
            }

            errorAlert.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    /**
     * Scroll fluide vers les sections
     */
    function initSmoothScroll() {
        const anchors = document.querySelectorAll('a[href^="#"]');

        anchors.forEach(function(anchor) {
            anchor.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;

                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

})();
