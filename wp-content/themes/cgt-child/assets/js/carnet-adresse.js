/**
 * Carnet d'adresse - Filtering functionality
 *
 * @package CGT_Child
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		const $filterButtons = $('.filter-btn');
		const $expertCards = $('.expert-card');
		const $expertsGrid = $('.experts-grid');
		const $expertsEmpty = $('.experts-empty');

		/**
		 * Filter experts by expertise
		 */
		$filterButtons.on('click', function() {
			const $btn = $(this);
			const filter = $btn.data('filter');

			// Update active state
			$filterButtons.removeClass('active');
			$btn.addClass('active');

			// Filter cards
			let visibleCount = 0;

			if (filter === 'all') {
				$expertCards.fadeIn(300);
				visibleCount = $expertCards.length;
			} else {
				$expertCards.each(function() {
					const $card = $(this);
					const expertises = $card.data('expertise');

					if (expertises && expertises.includes(filter)) {
						$card.fadeIn(300);
						visibleCount++;
					} else {
						$card.fadeOut(300);
					}
				});
			}

			// Show/hide empty state
			setTimeout(function() {
				if (visibleCount === 0) {
					$expertsGrid.hide();
					$expertsEmpty.fadeIn(300);
				} else {
					$expertsEmpty.hide();
					$expertsGrid.fadeIn(300);
				}
			}, 350);
		});

		/**
		 * Smooth animations on load
		 */
		$expertCards.each(function(index) {
			$(this).css({
				'animation-delay': (index * 0.05) + 's'
			});
		});

		/**
		 * Hover effect enhancement
		 */
		$expertCards.hover(
			function() {
				$(this).find('.expertise-badge').css({
					'transform': 'scale(1.05)',
					'transition': 'transform 200ms'
				});
			},
			function() {
				$(this).find('.expertise-badge').css({
					'transform': 'scale(1)'
				});
			}
		);

		/**
		 * Click on expertise badge to filter
		 */
		$(document).on('click', '.expertise-badge', function(e) {
			e.preventDefault();
			e.stopPropagation();

			const expertiseText = $(this).text().trim().toLowerCase();

			// Find matching filter button
			$filterButtons.each(function() {
				const btnText = $(this).text().replace(/\d+/, '').trim().toLowerCase();
				if (btnText === expertiseText) {
					$(this).trigger('click');

					// Scroll to filters
					$('html, body').animate({
						scrollTop: $('.carnet-filters').offset().top - 100
					}, 500);

					return false;
				}
			});
		});

		/**
		 * Add tooltips for contact items
		 */
		$('.contact-item a').hover(
			function() {
				$(this).css('font-weight', '600');
			},
			function() {
				$(this).css('font-weight', '400');
			}
		);

		/**
		 * Lazy loading animation on scroll
		 */
		if ('IntersectionObserver' in window) {
			const observerOptions = {
				root: null,
				rootMargin: '0px',
				threshold: 0.1
			};

			const observer = new IntersectionObserver(function(entries) {
				entries.forEach(function(entry) {
					if (entry.isIntersecting) {
						entry.target.style.opacity = '0';
						entry.target.style.transform = 'translateY(20px)';

						setTimeout(function() {
							entry.target.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
							entry.target.style.opacity = '1';
							entry.target.style.transform = 'translateY(0)';
						}, 50);

						observer.unobserve(entry.target);
					}
				});
			}, observerOptions);

			// Observe expert cards
			$expertCards.each(function() {
				observer.observe(this);
			});
		}
	});

})(jQuery);
