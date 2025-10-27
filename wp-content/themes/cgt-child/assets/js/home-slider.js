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
		const autoplayDelay = 5000; // 5 secondes

		/**
		 * Affiche une slide spécifique
		 */
		function showSlide(index) {
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
})();
