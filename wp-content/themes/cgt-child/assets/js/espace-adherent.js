/**
 * Espace Adhérent - JavaScript interactions
 */

(function() {
	'use strict';

	// Wait for DOM to be ready
	document.addEventListener('DOMContentLoaded', function() {

		// Branch selector toggle
		const changeBranchBtn = document.getElementById('changeBranchBtn');
		const cancelBranchBtn = document.getElementById('cancelBranchBtn');
		const branchSelectorForm = document.getElementById('branchSelectorForm');
		const branchContainer = document.querySelector('.member-dashboard__branch-container');

		if (changeBranchBtn && branchSelectorForm) {
			changeBranchBtn.addEventListener('click', function() {
				// Show form with animation
				branchSelectorForm.style.display = 'flex';
				setTimeout(function() {
					branchSelectorForm.classList.add('is-visible');
				}, 10);

				// Hide branch display
				if (branchContainer) {
					branchContainer.style.display = 'none';
				}

				// Focus on select
				const selectElement = branchSelectorForm.querySelector('select');
				if (selectElement) {
					selectElement.focus();
				}
			});
		}

		if (cancelBranchBtn && branchSelectorForm) {
			cancelBranchBtn.addEventListener('click', function() {
				// Hide form
				branchSelectorForm.classList.remove('is-visible');
				setTimeout(function() {
					branchSelectorForm.style.display = 'none';
				}, 300);

				// Show branch display
				if (branchContainer) {
					branchContainer.style.display = 'flex';
				}
			});
		}
	});
})();
