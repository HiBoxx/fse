/**
 * Admin Newsletter - Select All functionality
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Select/deselect all checkboxes
		$('#cb-select-all').on('change', function() {
			$('input[name="newsletter_ids[]"]').prop('checked', this.checked);
		});

		// Update select-all checkbox if individual checkboxes change
		$('input[name="newsletter_ids[]"]').on('change', function() {
			const totalCheckboxes = $('input[name="newsletter_ids[]"]').length;
			const checkedCheckboxes = $('input[name="newsletter_ids[]"]:checked').length;
			$('#cb-select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
		});
	});

})(jQuery);
