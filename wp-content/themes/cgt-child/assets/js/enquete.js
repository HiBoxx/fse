/**
 * Enquête (Survey) JavaScript
 * Handles voting and AJAX submissions
 */

(function($) {
	'use strict';

	const EnqueteApp = {
		init: function() {
			this.cacheElements();
			this.bindEvents();
			this.initializeState();
		},

		cacheElements: function() {
			this.$form = $('#enquete-form');
			this.$submitBtn = $('.enquete-submit-btn');
			this.$successMessage = $('#enquete-success-message');
		},

		bindEvents: function() {
			if (this.$form.length) {
				this.$form.on('submit', this.handleSubmit.bind(this));
			}

			// Add hover effects for options
			$('.enquete-option').on('mouseenter', function() {
				$(this).find('input[type="radio"]').focus();
			});
		},

		initializeState: function() {
			// Check if user has already voted
			if (typeof cgtEnquete !== 'undefined' && cgtEnquete.has_voted) {
				this.showResults();
			}
		},

		handleSubmit: function(e) {
			e.preventDefault();

			// Check if already voted
			if (typeof cgtEnquete !== 'undefined' && cgtEnquete.has_voted) {
				this.showMessage('error', cgtEnquete.strings.already_voted);
				return;
			}

			// Check if enquete is active
			if (typeof cgtEnquete !== 'undefined' && !cgtEnquete.is_active) {
				this.showMessage('error', 'Cette enquête n\'est plus active.');
				return;
			}

			// Validate form
			if (!this.validateForm()) {
				this.showMessage('error', 'Veuillez répondre à toutes les questions.');
				return;
			}

			// Collect votes
			const votes = this.collectVotes();

			// Show loading state
			this.$form.addClass('is-loading');
			this.$submitBtn.prop('disabled', true);

			// Submit via AJAX
			this.submitVotes(votes);
		},

		validateForm: function() {
			let isValid = true;
			const $questions = this.$form.find('.enquete-question');

			$questions.each(function() {
				const $question = $(this);
				const $options = $question.find('input[type="radio"]');

				if ($options.length > 0) {
					const isAnswered = $options.filter(':checked').length > 0;

					if (!isAnswered) {
						isValid = false;
						$question.addClass('has-error');

						// Add visual feedback
						$question.css('border-left', '4px solid #dc3545');
						setTimeout(function() {
							$question.css('border-left', '');
							$question.removeClass('has-error');
						}, 2000);
					}
				}
			});

			return isValid;
		},

		collectVotes: function() {
			const votes = {};
			const $questions = this.$form.find('.enquete-question');

			$questions.each(function() {
				const $question = $(this);
				const questionIndex = $question.data('question-index');
				const $checkedOption = $question.find('input[type="radio"]:checked');

				if ($checkedOption.length) {
					votes[questionIndex] = parseInt($checkedOption.val(), 10);
				}
			});

			return votes;
		},

		submitVotes: function(votes) {
			const self = this;

			$.ajax({
				url: cgtEnquete.ajaxurl,
				type: 'POST',
				data: {
					action: 'cgt_enquete_vote',
					post_id: cgtEnquete.post_id,
					votes: votes,
					nonce: cgtEnquete.nonce
				},
				success: function(response) {
					if (response.success) {
						self.handleVoteSuccess(response.data);
					} else {
						self.handleVoteError(response.data ? response.data.message : null);
					}
				},
				error: function() {
					self.handleVoteError();
				},
				complete: function() {
					self.$form.removeClass('is-loading');
					self.$submitBtn.prop('disabled', false);
				}
			});
		},

		handleVoteSuccess: function(data) {
			// Show success message
			this.$successMessage.slideDown(300);

			// Hide form
			this.$form.fadeOut(300, () => {
				// Reload page to show results
				setTimeout(() => {
					window.location.reload();
				}, 1500);
			});

			// Set cookie to prevent multiple votes
			if (typeof cgtEnquete !== 'undefined') {
				const cookieName = 'cgt_enquete_' + cgtEnquete.post_id + '_voted';
				document.cookie = cookieName + '=1; path=/; max-age=' + (365 * 24 * 60 * 60);
			}
		},

		handleVoteError: function(message) {
			const errorMessage = message || (typeof cgtEnquete !== 'undefined' ? cgtEnquete.strings.vote_error : 'Une erreur est survenue.');
			this.showMessage('error', errorMessage);
		},

		showMessage: function(type, message) {
			// Create alert element if it doesn't exist
			let $alert = $('#enquete-temp-alert');

			if (!$alert.length) {
				$alert = $('<div id="enquete-temp-alert" class="enquete-alert"></div>');
				this.$form.before($alert);
			}

			// Set classes based on type
			$alert.removeClass('enquete-alert--success enquete-alert--info enquete-alert--warning enquete-alert--error');

			if (type === 'success') {
				$alert.addClass('enquete-alert--success');
			} else if (type === 'error') {
				$alert.addClass('enquete-alert--warning');
			}

			// Add icon
			let icon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
			if (type === 'success') {
				icon += '<path d="M22 11.08V12a10 10 0 11-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>';
			} else {
				icon += '<path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>';
			}
			icon += '</svg>';

			// Set content
			$alert.html(icon + '<span>' + message + '</span>');

			// Show alert
			$alert.slideDown(300);

			// Scroll to alert
			$('html, body').animate({
				scrollTop: $alert.offset().top - 100
			}, 300);

			// Auto-hide after 5 seconds
			setTimeout(function() {
				$alert.slideUp(300);
			}, 5000);
		},

		showResults: function() {
			// This function would handle showing results
			// For now, results are rendered server-side
			console.log('User has already voted - showing results');
		}
	};

	// Initialize when document is ready
	$(document).ready(function() {
		EnqueteApp.init();
	});

	// Animations on scroll (optional enhancement)
	if ('IntersectionObserver' in window) {
		const observerOptions = {
			threshold: 0.1,
			rootMargin: '0px 0px -100px 0px'
		};

		const observer = new IntersectionObserver(function(entries) {
			entries.forEach(function(entry) {
				if (entry.isIntersecting) {
					entry.target.style.opacity = '1';
					entry.target.style.transform = 'translateY(0)';
					observer.unobserve(entry.target);
				}
			});
		}, observerOptions);

		// Observe question cards
		document.querySelectorAll('.enquete-question').forEach(function(card, index) {
			card.style.opacity = '0';
			card.style.transform = 'translateY(20px)';
			card.style.transition = 'opacity 0.5s ease-out ' + (index * 0.1) + 's, transform 0.5s ease-out ' + (index * 0.1) + 's';
			observer.observe(card);
		});
	}

})(jQuery);
