/**
 * Page Proposer un Article - JavaScript Interactif
 * Gestion des étapes, validation et upload de fichiers
 */

(function() {
    'use strict';

    let currentStep = 1;
    const totalSteps = 4;

    // Initialisation au chargement du DOM
    document.addEventListener('DOMContentLoaded', function() {
        initStepNavigation();
        initCharCounters();
        initFileUploads();
        initFormValidation();
    });

    /**
     * Initialisation de la navigation par étapes
     */
    function initStepNavigation() {
        const nextButtons = document.querySelectorAll('[data-next-step]');
        const prevButtons = document.querySelectorAll('[data-prev-step]');

        nextButtons.forEach(button => {
            button.addEventListener('click', function() {
                const nextStep = parseInt(this.getAttribute('data-next-step'));
                if (validateCurrentStep()) {
                    goToStep(nextStep);
                } else {
                    // Shake animation on error
                    const currentStepElement = document.querySelector(`.form-step[data-step="${currentStep}"]`);
                    currentStepElement.classList.add('shake');
                    setTimeout(() => {
                        currentStepElement.classList.remove('shake');
                    }, 500);
                }
            });
        });

        prevButtons.forEach(button => {
            button.addEventListener('click', function() {
                const prevStep = parseInt(this.getAttribute('data-prev-step'));
                goToStep(prevStep);
            });
        });
    }

    /**
     * Aller à une étape spécifique
     */
    function goToStep(step) {
        // Masquer toutes les étapes
        document.querySelectorAll('.form-step').forEach(stepElement => {
            stepElement.classList.remove('active');
        });

        // Afficher l'étape ciblée
        const targetStep = document.querySelector(`.form-step[data-step="${step}"]`);
        if (targetStep) {
            targetStep.classList.add('active');
            currentStep = step;
            updateProgressBar(step);
            updateStepIndicators(step);

            // Scroll en haut du formulaire
            targetStep.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    /**
     * Mettre à jour la barre de progression
     */
    function updateProgressBar(step) {
        const progressLine = document.querySelector('.progress-line');
        const percentage = ((step - 1) / (totalSteps - 1)) * 100;
        if (progressLine) {
            progressLine.style.width = percentage + '%';
        }
    }

    /**
     * Mettre à jour les indicateurs d'étapes
     */
    function updateStepIndicators(step) {
        document.querySelectorAll('.progress-step').forEach((indicator, index) => {
            const stepNumber = index + 1;

            // Réinitialiser
            indicator.classList.remove('active', 'completed');

            if (stepNumber < step) {
                indicator.classList.add('completed');
            } else if (stepNumber === step) {
                indicator.classList.add('active');
            }
        });
    }

    /**
     * Valider l'étape courante
     */
    function validateCurrentStep() {
        const currentStepElement = document.querySelector(`.form-step[data-step="${currentStep}"]`);
        const requiredInputs = currentStepElement.querySelectorAll('[required]');
        let isValid = true;

        requiredInputs.forEach(input => {
            const formGroup = input.closest('.form-group');

            if (!input.value.trim()) {
                isValid = false;
                if (formGroup) {
                    formGroup.classList.add('has-error');
                    input.classList.add('error');
                }
            } else {
                if (formGroup) {
                    formGroup.classList.remove('has-error');
                    formGroup.classList.add('has-success');
                    input.classList.remove('error');
                    input.classList.add('success');
                }
            }

            // Validation email
            if (input.type === 'email' && input.value.trim()) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(input.value)) {
                    isValid = false;
                    if (formGroup) {
                        formGroup.classList.add('has-error');
                        input.classList.add('error');
                    }
                }
            }
        });

        return isValid;
    }

    /**
     * Initialiser les compteurs de caractères
     */
    function initCharCounters() {
        const counters = document.querySelectorAll('.char-counter');

        counters.forEach(counter => {
            const fieldId = counter.getAttribute('data-field');
            const maxChars = parseInt(counter.getAttribute('data-max'));
            const field = document.getElementById(fieldId);

            if (field) {
                // Mise à jour initiale
                updateCharCounter(field, counter, maxChars);

                // Mise à jour en temps réel
                field.addEventListener('input', function() {
                    updateCharCounter(field, counter, maxChars);
                });
            }
        });
    }

    /**
     * Mettre à jour un compteur de caractères
     */
    function updateCharCounter(field, counter, maxChars) {
        const currentLength = field.value.length;
        const remaining = maxChars - currentLength;

        counter.textContent = `${currentLength} / ${maxChars} caractères`;

        // Changer la couleur selon le nombre de caractères restants
        counter.classList.remove('warning', 'error');

        if (remaining < maxChars * 0.1) {
            counter.classList.add('error');
        } else if (remaining < maxChars * 0.2) {
            counter.classList.add('warning');
        }

        // Empêcher de dépasser la limite
        if (currentLength > maxChars) {
            field.value = field.value.substring(0, maxChars);
        }
    }

    /**
     * Initialiser les zones d'upload de fichiers
     */
    function initFileUploads() {
        initImageUpload();
        initPDFUpload();
    }

    /**
     * Initialiser l'upload d'image
     */
    function initImageUpload() {
        const uploadZone = document.getElementById('image-upload-zone');
        const fileInput = document.getElementById('cgt_article_featured');
        const preview = document.getElementById('image-preview');
        const filePreview = document.getElementById('image-file-preview');
        const removeBtn = filePreview.querySelector('[data-file="image"]');

        if (!uploadZone || !fileInput) return;

        // Click sur la zone
        uploadZone.addEventListener('click', () => fileInput.click());

        // Changement de fichier
        fileInput.addEventListener('change', function() {
            handleImageSelection(this.files[0], preview, filePreview);
        });

        // Drag & Drop
        uploadZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });

        uploadZone.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
        });

        uploadZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');

            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                fileInput.files = e.dataTransfer.files;
                handleImageSelection(file, preview, filePreview);
            }
        });

        // Bouton de suppression
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                fileInput.value = '';
                preview.classList.remove('show');
                preview.innerHTML = '';
                filePreview.classList.remove('show');
            });
        }
    }

    /**
     * Gérer la sélection d'image
     */
    function handleImageSelection(file, preview, filePreview) {
        if (!file) return;

        // Vérifier la taille (5 MB max)
        if (file.size > 5 * 1024 * 1024) {
            alert('L\'image est trop volumineuse. Max 5 MB.');
            return;
        }

        // Vérifier le type
        if (!file.type.match('image.*')) {
            alert('Seules les images sont autorisées.');
            return;
        }

        // Afficher la prévisualisation
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Prévisualisation">`;
            preview.classList.add('show');
        };
        reader.readAsDataURL(file);

        // Afficher les infos du fichier
        filePreview.querySelector('.file-preview-name').textContent = file.name;
        filePreview.querySelector('.file-preview-size').textContent = formatFileSize(file.size);
        filePreview.classList.add('show');
    }

    /**
     * Initialiser l'upload de PDF
     */
    function initPDFUpload() {
        const uploadZone = document.getElementById('pdf-upload-zone');
        const fileInput = document.getElementById('cgt_article_pdf');
        const filePreview = document.getElementById('pdf-file-preview');
        const removeBtn = filePreview.querySelector('[data-file="pdf"]');

        if (!uploadZone || !fileInput) return;

        // Click sur la zone
        uploadZone.addEventListener('click', () => fileInput.click());

        // Changement de fichier
        fileInput.addEventListener('change', function() {
            handlePDFSelection(this.files[0], filePreview);
        });

        // Drag & Drop
        uploadZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });

        uploadZone.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
        });

        uploadZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');

            const file = e.dataTransfer.files[0];
            if (file && file.type === 'application/pdf') {
                fileInput.files = e.dataTransfer.files;
                handlePDFSelection(file, filePreview);
            }
        });

        // Bouton de suppression
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                fileInput.value = '';
                filePreview.classList.remove('show');
            });
        }
    }

    /**
     * Gérer la sélection de PDF
     */
    function handlePDFSelection(file, filePreview) {
        if (!file) return;

        // Vérifier la taille (15 MB max)
        if (file.size > 15 * 1024 * 1024) {
            alert('Le PDF est trop volumineux. Max 15 MB.');
            return;
        }

        // Vérifier le type
        if (file.type !== 'application/pdf') {
            alert('Seuls les fichiers PDF sont autorisés.');
            return;
        }

        // Afficher les infos du fichier
        filePreview.querySelector('.file-preview-name').textContent = file.name;
        filePreview.querySelector('.file-preview-size').textContent = formatFileSize(file.size);
        filePreview.classList.add('show');
    }

    /**
     * Formater la taille de fichier
     */
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';

        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    /**
     * Initialiser la validation du formulaire
     */
    function initFormValidation() {
        const form = document.getElementById('article-form');

        if (!form) return;

        form.addEventListener('submit', function(e) {
            // Valider toutes les étapes
            let allValid = true;

            for (let step = 1; step <= totalSteps; step++) {
                const stepElement = document.querySelector(`.form-step[data-step="${step}"]`);
                const requiredInputs = stepElement.querySelectorAll('[required]');

                requiredInputs.forEach(input => {
                    if (!input.value.trim()) {
                        allValid = false;
                        const formGroup = input.closest('.form-group');
                        if (formGroup) {
                            formGroup.classList.add('has-error');
                        }
                    }
                });
            }

            if (!allValid) {
                e.preventDefault();
                alert('Merci de remplir tous les champs obligatoires.');
                goToStep(1); // Retour à la première étape avec erreurs
            }
        });

        // Validation en temps réel sur tous les champs requis
        const allRequiredInputs = form.querySelectorAll('[required]');
        allRequiredInputs.forEach(input => {
            input.addEventListener('blur', function() {
                const formGroup = this.closest('.form-group');

                if (!this.value.trim()) {
                    if (formGroup) {
                        formGroup.classList.add('has-error');
                        this.classList.add('error');
                    }
                } else {
                    if (formGroup) {
                        formGroup.classList.remove('has-error');
                        formGroup.classList.add('has-success');
                        this.classList.remove('error');
                        this.classList.add('success');
                    }
                }

                // Validation email
                if (this.type === 'email' && this.value.trim()) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(this.value)) {
                        if (formGroup) {
                            formGroup.classList.add('has-error');
                            this.classList.add('error');
                        }
                    }
                }
            });

            // Retirer l'erreur lors de la saisie
            input.addEventListener('input', function() {
                const formGroup = this.closest('.form-group');
                if (this.value.trim() && formGroup) {
                    formGroup.classList.remove('has-error');
                    this.classList.remove('error');
                }
            });
        });
    }

})();
