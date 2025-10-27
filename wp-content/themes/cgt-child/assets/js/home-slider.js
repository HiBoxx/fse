/**
 * Home Slider
 * Gestion du slider d'images sur la page d'accueil
 */

(function() {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {
		const slider = document.querySelector('.home-slider');
		if (!slider) return;

		const slides = slider.querySelectorAll('.home-slider__slide');
		const dots = slider.querySelectorAll('.home-slider__dot');
		const prevBtn = slider.querySelector('.home-slider__btn--prev');
		const nextBtn = slider.querySelector('.home-slider__btn--next');

		if (slides.length <= 1) return;

		let currentSlide = 0;
		let autoplayInterval;
		let isAnimating = false; // Debounce flag
		const autoplayDelay = 5000; // 5 secondes

		/**
		 * Affiche une slide spécifique
		 */
		function showSlide(index) {
			// Debounce - Prevent rapid clicks
			if (isAnimating) return;
			isAnimating = true;

			// Gestion du loop
			if (index >= slides.length) {
				currentSlide = 0;
			} else if (index < 0) {
				currentSlide = slides.length - 1;
			} else {
				currentSlide = index;
			}

			// Masquer toutes les slides
			slides.forEach(slide => {
				slide.classList.remove('is-active');
			});

			// Désactiver tous les dots
			dots.forEach(dot => {
				dot.classList.remove('is-active');
			});

			// Afficher la slide active
			slides[currentSlide].classList.add('is-active');

			// Activer le dot correspondant
			if (dots[currentSlide]) {
				dots[currentSlide].classList.add('is-active');
			}

			// Reset debounce after animation duration (600ms matches CSS transition)
			setTimeout(function() {
				isAnimating = false;
			}, 600);
		}

		/**
		 * Slide suivante
		 */
		function nextSlide() {
			showSlide(currentSlide + 1);
		}

		/**
		 * Slide précédente
		 */
		function prevSlide() {
			showSlide(currentSlide - 1);
		}

		/**
		 * Démarre l'autoplay
		 */
		function startAutoplay() {
			stopAutoplay(); // Clear any existing interval
			autoplayInterval = setInterval(nextSlide, autoplayDelay);
		}

		/**
		 * Arrête l'autoplay
		 */
		function stopAutoplay() {
			if (autoplayInterval) {
				clearInterval(autoplayInterval);
			}
		}

		// Event listeners pour les boutons
		if (prevBtn) {
			prevBtn.addEventListener('click', function() {
				prevSlide();
				stopAutoplay();
				startAutoplay(); // Restart autoplay after manual interaction
			});
		}

		if (nextBtn) {
			nextBtn.addEventListener('click', function() {
				nextSlide();
				stopAutoplay();
				startAutoplay();
			});
		}

		// Event listeners pour les dots
		dots.forEach(function(dot, index) {
			dot.addEventListener('click', function() {
				showSlide(index);
				stopAutoplay();
				startAutoplay();
			});
		});

		// Pause autoplay au survol
		slider.addEventListener('mouseenter', stopAutoplay);
		slider.addEventListener('mouseleave', startAutoplay);

		// Support du clavier
		document.addEventListener('keydown', function(e) {
			if (!slider.matches(':hover')) return;

			if (e.key === 'ArrowLeft') {
				prevSlide();
				stopAutoplay();
				startAutoplay();
			} else if (e.key === 'ArrowRight') {
				nextSlide();
				stopAutoplay();
				startAutoplay();
			}
		});

		// Support du swipe sur mobile
		let touchStartX = 0;
		let touchEndX = 0;

		slider.addEventListener('touchstart', function(e) {
			touchStartX = e.changedTouches[0].screenX;
			stopAutoplay();
		}, {passive: true});

		slider.addEventListener('touchend', function(e) {
			touchEndX = e.changedTouches[0].screenX;
			handleSwipe();
			startAutoplay();
		}, {passive: true});

		function handleSwipe() {
			const swipeThreshold = 50;
			const diff = touchStartX - touchEndX;

			if (Math.abs(diff) > swipeThreshold) {
				if (diff > 0) {
					// Swipe left - next slide
					nextSlide();
				} else {
					// Swipe right - previous slide
					prevSlide();
				}
			}
		}

		// Démarrer l'autoplay
		startAutoplay();

		// Pause autoplay quand la page n'est pas visible
		document.addEventListener('visibilitychange', function() {
			if (document.hidden) {
				stopAutoplay();
			} else {
				startAutoplay();
			}
		});
	});

	// ========================================
	// NEWSLETTER MODAL
	// ========================================
	const newsletterModal = document.getElementById('newsletterModal');
	const openModalBtn = document.getElementById('openNewsletterModal');
	const closeModalBtn = document.getElementById('closeNewsletterModal');
	const newsletterForm = document.getElementById('newsletterForm');
	const newsletterMessage = document.getElementById('newsletterMessage');
	let lastFocusedElement;

	/**
	 * Focus trap - Keeps focus within modal
	 */
	function trapFocus(element) {
		const focusableElements = element.querySelectorAll(
			'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
		);
		const firstElement = focusableElements[0];
		const lastElement = focusableElements[focusableElements.length - 1];

		// Handle tab key
		const handleTab = function(e) {
			if (e.key !== 'Tab') return;

			if (e.shiftKey) {
				// Shift + Tab
				if (document.activeElement === firstElement) {
					e.preventDefault();
					lastElement.focus();
				}
			} else {
				// Tab
				if (document.activeElement === lastElement) {
					e.preventDefault();
					firstElement.focus();
				}
			}
		};

		element.addEventListener('keydown', handleTab);
		return handleTab; // Return for cleanup
	}

	if (openModalBtn && newsletterModal) {
		// Open modal
		openModalBtn.addEventListener('click', function() {
			lastFocusedElement = document.activeElement; // Save current focus
			newsletterModal.classList.add('is-open');
			document.body.style.overflow = 'hidden';

			// Trap focus
			const modalContent = newsletterModal.querySelector('.newsletter-modal__content');
			if (modalContent) {
				trapFocus(modalContent);
			}

			// Focus on first input field
			const firstInput = newsletterModal.querySelector('input:not([type="checkbox"])');
			if (firstInput) {
				setTimeout(function() {
					firstInput.focus();
				}, 100);
			}
		});
	}

	if (closeModalBtn && newsletterModal) {
		// Close modal
		closeModalBtn.addEventListener('click', function() {
			closeModal();
		});

		// Close modal on overlay click
		const overlay = newsletterModal.querySelector('.newsletter-modal__overlay');
		if (overlay) {
			overlay.addEventListener('click', function() {
				closeModal();
			});
		}
	}

	// Close modal function
	function closeModal() {
		newsletterModal.classList.remove('is-open');
		document.body.style.overflow = '';

		// Restore focus to element that opened the modal
		if (lastFocusedElement) {
			lastFocusedElement.focus();
		}
	}

	// Close modal on ESC key
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape' && newsletterModal && newsletterModal.classList.contains('is-open')) {
			closeModal();
		}
	});

	// Handle form submission
	if (newsletterForm) {
		newsletterForm.addEventListener('submit', function(e) {
			e.preventDefault();

			// Get form data
			const formData = new FormData(newsletterForm);
			formData.append('action', 'cgt_subscribe_newsletter');
			formData.append('nonce', cgtNewsletterData.nonce);

			// Disable submit button
			const submitBtn = newsletterForm.querySelector('button[type="submit"]');
			const originalText = submitBtn.textContent;
			submitBtn.disabled = true;
			submitBtn.textContent = 'Envoi en cours...';

			// Hide previous messages
			newsletterMessage.style.display = 'none';
			newsletterMessage.className = 'newsletter-form__message';

			// Send AJAX request
			fetch(cgtNewsletterData.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				// Show message
				newsletterMessage.style.display = 'block';
				newsletterMessage.textContent = data.data.message;

				if (data.success) {
					newsletterMessage.classList.add('newsletter-form__message--success');
					// Reset form
					newsletterForm.reset();
					// Close modal after 2 seconds
					setTimeout(function() {
						closeModal();
						// Reset message for next time
						setTimeout(function() {
							newsletterMessage.style.display = 'none';
							newsletterMessage.className = 'newsletter-form__message';
						}, 300);
					}, 2000);
				} else {
					newsletterMessage.classList.add('newsletter-form__message--error');
				}

				// Re-enable submit button
				submitBtn.disabled = false;
				submitBtn.textContent = originalText;
			})
			.catch(error => {
				console.error('Error:', error);
				newsletterMessage.style.display = 'block';
				newsletterMessage.textContent = 'Une erreur est survenue. Veuillez réessayer.';
				newsletterMessage.classList.add('newsletter-form__message--error');
				submitBtn.disabled = false;
				submitBtn.textContent = originalText;
			});
		});
	}
})();
