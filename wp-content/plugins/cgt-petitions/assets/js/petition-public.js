/**
 * CGT Pétitions - JavaScript Public
 * Interactions et animations pour la page de pétition
 */

(function() {
	'use strict';

	const CGTPetition = {

		/**
		 * Initialize
		 */
		init: function() {
			this.animateCounter();
			this.animateProgressBar();
			this.handleFormSubmit();
			this.scrollToMessage();
			this.addFormValidation();
		},

		/**
		 * Animate signature counter
		 */
		animateCounter: function() {
			const counter = document.querySelector('.cgt-petition-stats__current');
			if (!counter) return;

			const target = parseInt(counter.textContent.replace(/\s/g, ''));
			if (isNaN(target)) return;

			const duration = 2000; // 2 seconds
			const increment = target / (duration / 16);
			let current = 0;

			const updateCounter = () => {
				current += increment;
				if (current < target) {
					counter.textContent = Math.floor(current).toLocaleString('fr-FR');
					requestAnimationFrame(updateCounter);
				} else {
					counter.textContent = target.toLocaleString('fr-FR');
				}
			};

			// Start animation when element is visible
			const observer = new IntersectionObserver((entries) => {
				entries.forEach(entry => {
					if (entry.isIntersecting) {
						updateCounter();
						observer.disconnect();
					}
				});
			}, { threshold: 0.5 });

			observer.observe(counter);
		},

		/**
		 * Animate progress bar
		 */
		animateProgressBar: function() {
			const progressBar = document.querySelector('.cgt-petition-progress__fill');
			if (!progressBar) return;

			const targetWidth = progressBar.style.width;

			// Start at 0
			progressBar.style.width = '0%';

			// Animate when visible
			const observer = new IntersectionObserver((entries) => {
				entries.forEach(entry => {
					if (entry.isIntersecting) {
						setTimeout(() => {
							progressBar.style.width = targetWidth;
						}, 500);
						observer.disconnect();
					}
				});
			}, { threshold: 0.5 });

			observer.observe(progressBar);
		},

		/**
		 * Handle form submission with loading state
		 */
		handleFormSubmit: function() {
			const form = document.querySelector('.cgt-petition-form');
			if (!form) return;

			form.addEventListener('submit', function(e) {
				const submitBtn = form.querySelector('.cgt-petition-form__submit');
				if (!submitBtn) return;

				// Disable button and show loading state
				submitBtn.disabled = true;
				submitBtn.textContent = 'Envoi en cours...';
				submitBtn.style.background = '#9ca3af';

				// Add spinner
				const spinner = document.createElement('span');
				spinner.innerHTML = ' ⏳';
				submitBtn.appendChild(spinner);
			});
		},

		/**
		 * Scroll to message if present
		 */
		scrollToMessage: function() {
			const message = document.querySelector('.cgt-petition-message');
			if (!message) return;

			// Small delay to ensure page is loaded
			setTimeout(() => {
				message.scrollIntoView({
					behavior: 'smooth',
					block: 'center'
				});

				// Add attention animation
				message.style.animation = 'pulse 0.5s ease-out';
			}, 300);
		},

		/**
		 * Add real-time form validation
		 */
		addFormValidation: function() {
			const form = document.querySelector('.cgt-petition-form');
			if (!form) return;

			const nameInput = form.querySelector('input[name="cgt_petition_name"]');
			const emailInput = form.querySelector('input[name="cgt_petition_email"]');
			const submitBtn = form.querySelector('.cgt-petition-form__submit');

			if (!nameInput || !emailInput || !submitBtn) return;

			// Name validation
			nameInput.addEventListener('input', function() {
				const value = this.value.trim();
				if (value.length < 2) {
					this.style.borderColor = '#f59e0b';
				} else {
					this.style.borderColor = '#10b981';
				}
				updateSubmitButton();
			});

			// Email validation
			emailInput.addEventListener('input', function() {
				const value = this.value.trim();
				const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);

				if (!value) {
					this.style.borderColor = '#d1d5db';
				} else if (isValid) {
					this.style.borderColor = '#10b981';
				} else {
					this.style.borderColor = '#ef4444';
				}
				updateSubmitButton();
			});

			// Email blur - show helper text
			emailInput.addEventListener('blur', function() {
				const value = this.value.trim();
				if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
					showEmailError(this);
				} else {
					removeEmailError(this);
				}
			});

			// Update submit button state
			function updateSubmitButton() {
				const nameValid = nameInput.value.trim().length >= 2;
				const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim());

				if (nameValid && emailValid) {
					submitBtn.style.opacity = '1';
					submitBtn.style.cursor = 'pointer';
				} else {
					submitBtn.style.opacity = '0.6';
					submitBtn.style.cursor = 'not-allowed';
				}
			}

			// Show email error
			function showEmailError(input) {
				removeEmailError(input);
				const error = document.createElement('div');
				error.className = 'cgt-petition-form__error';
				error.textContent = 'Veuillez entrer une adresse email valide';
				error.style.cssText = 'color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem; font-weight: 600;';
				input.parentNode.appendChild(error);
			}

			// Remove email error
			function removeEmailError(input) {
				const error = input.parentNode.querySelector('.cgt-petition-form__error');
				if (error) {
					error.remove();
				}
			}

			// Initial state
			updateSubmitButton();
		}

	};

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			CGTPetition.init();
		});
	} else {
		CGTPetition.init();
	}

})();
