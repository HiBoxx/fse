/**
 * CGT Bibliothèque PDF - Admin JavaScript
 * Interactions modernes et UX améliorée
 */

(function($) {
	'use strict';

	const CGTLibrary = {

		/**
		 * Initialize
		 */
		init: function() {
			this.copyShortcodes();
			this.confirmDelete();
			this.filterAnimation();
			this.searchFilter();
			this.initTooltips();
			this.keyboardNav();
		},

		/**
		 * Copy shortcode to clipboard with animation
		 */
		copyShortcodes: function() {
			$(document).on('click', '.cgt-copy-btn', function(e) {
				e.preventDefault();

				const $btn = $(this);
				const shortcode = $btn.data('shortcode');
				const originalText = $btn.text();

				if (!shortcode) return;

				// Copy to clipboard
				navigator.clipboard.writeText(shortcode).then(function() {
					// Success animation
					$btn.addClass('copied')
						.text('✓ Copié !');

					// Visual feedback
					const $card = $btn.closest('.cgt-pdf-card');
					$card.css('transform', 'scale(1.02)');

					setTimeout(function() {
						$card.css('transform', '');
					}, 200);

					// Reset after 2 seconds
					setTimeout(function() {
						$btn.removeClass('copied')
							.text(originalText);
					}, 2000);

				}).catch(function(err) {
					console.error('Erreur lors de la copie:', err);
					$btn.text('❌ Erreur');

					setTimeout(function() {
						$btn.text(originalText);
					}, 2000);
				});
			});
		},

		/**
		 * Confirm delete with custom modal
		 */
		confirmDelete: function() {
			$(document).on('click', '.cgt-delete-pdf', function(e) {
				const confirmMessage = $(this).data('confirm');
				const title = $(this).closest('.cgt-pdf-card').find('h3').text();

				const confirmed = confirm(
					confirmMessage + '\n\n"' + title + '"'
				);

				if (!confirmed) {
					e.preventDefault();
					return false;
				}

				// Show loading state
				const $btn = $(this);
				$btn.text('Suppression...')
					.prop('disabled', true)
					.css('opacity', '0.6');
			});
		},

		/**
		 * Smooth filter animations
		 */
		filterAnimation: function() {
			$('.cgt-library-filters select').on('change', function() {
				const $form = $(this).closest('form');
				const $cards = $('.cgt-pdf-card');

				// Fade out cards
				$cards.css({
					'opacity': '0',
					'transform': 'translateY(10px)'
				});

				// Submit after animation
				setTimeout(function() {
					$form.submit();
				}, 200);
			});
		},

		/**
		 * Real-time search filter
		 */
		searchFilter: function() {
			// Add search input if not exists
			if ($('.cgt-library-search').length === 0) {
				const $searchBox = $('<div class="cgt-library-search-box">' +
					'<input type="text" class="cgt-library-search" placeholder="🔍 Rechercher un PDF..." />' +
					'</div>');

				$('.cgt-library-filters').before($searchBox);

				// Add inline styles
				$searchBox.css({
					'background': 'white',
					'padding': '1rem 1.5rem',
					'border-radius': '12px',
					'box-shadow': '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
					'margin-bottom': '1.5rem'
				});

				$('.cgt-library-search').css({
					'width': '100%',
					'padding': '0.75rem 1rem',
					'border': '2px solid #e5e7eb',
					'border-radius': '8px',
					'font-size': '1rem',
					'transition': 'all 0.3s ease'
				});
			}

			// Search functionality
			let searchTimeout;
			$(document).on('input', '.cgt-library-search', function() {
				clearTimeout(searchTimeout);
				const searchTerm = $(this).val().toLowerCase();

				searchTimeout = setTimeout(function() {
					$('.cgt-pdf-card').each(function() {
						const $card = $(this);
						const title = $card.find('h3').text().toLowerCase();
						const categories = $card.find('.cgt-pdf-categories').text().toLowerCase();

						if (title.includes(searchTerm) || categories.includes(searchTerm)) {
							$card.show().css({
								'animation': 'fadeInUp 0.3s ease-out',
								'opacity': '1'
							});
						} else {
							$card.hide();
						}
					});

					// Show empty state if no results
					const visibleCards = $('.cgt-pdf-card:visible').length;
					if (visibleCards === 0 && searchTerm !== '') {
						if ($('.cgt-search-empty').length === 0) {
							$('.cgt-library-grid').append(
								'<div class="cgt-search-empty cgt-library-empty">' +
									'<div class="icon">🔍</div>' +
									'<h3>Aucun résultat</h3>' +
									'<p>Aucun PDF ne correspond à votre recherche.</p>' +
								'</div>'
							);
						}
					} else {
						$('.cgt-search-empty').remove();
					}
				}, 300);
			});

			// Clear search
			$(document).on('keyup', '.cgt-library-search', function(e) {
				if (e.key === 'Escape') {
					$(this).val('').trigger('input');
				}
			});
		},

		/**
		 * Initialize tooltips
		 */
		initTooltips: function() {
			// Add title attributes for better UX
			$('.cgt-pdf-icon').attr('title', 'Document PDF');
			$('.cgt-copy-btn').attr('title', 'Copier le shortcode dans le presse-papier');
			$('.button-primary').attr('title', 'Modifier ce PDF');
			$('.button-delete').attr('title', 'Supprimer définitivement ce PDF');
		},

		/**
		 * Keyboard navigation
		 */
		keyboardNav: function() {
			// Focus management
			$(document).on('keydown', '.cgt-pdf-card', function(e) {
				const $card = $(this);

				// Arrow navigation
				if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
					e.preventDefault();
					$card.next('.cgt-pdf-card').focus();
				} else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
					e.preventDefault();
					$card.prev('.cgt-pdf-card').focus();
				}
			});

			// Make cards focusable
			$('.cgt-pdf-card').attr('tabindex', '0');
		}

	};

	// Initialize on document ready
	$(document).ready(function() {
		CGTLibrary.init();

		// Smooth entrance animation for cards
		$('.cgt-pdf-card').each(function(index) {
			const $card = $(this);
			setTimeout(function() {
				$card.css({
					'animation': 'fadeInUp 0.4s ease-out',
					'animation-fill-mode': 'both'
				});
			}, index * 50);
		});
	});

	// Add CSS animations
	const animations = `
		<style>
		@keyframes fadeInUp {
			from {
				opacity: 0;
				transform: translateY(20px);
			}
			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		.cgt-library-search:focus {
			outline: none;
			border-color: #e30613;
			box-shadow: 0 0 0 3px rgba(227, 6, 19, 0.1);
		}

		.cgt-pdf-card:focus {
			outline: 3px solid rgba(227, 6, 19, 0.3);
			outline-offset: 2px;
		}
		</style>
	`;
	$('head').append(animations);

})(jQuery);
