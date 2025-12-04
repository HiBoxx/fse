(function () {
	'use strict';

	function initBuilder(container) {
		const list = container.querySelector('[data-question-list]');
		const template = container.querySelector('#cgt-question-template');
		const addQuestionBtn = container.querySelector('[data-add-question]');

		if (!list || !template || !addQuestionBtn) {
			return;
		}

		let questionIndex = 0;

		function createOptionRow(block, value = '') {
			const optionList = block.querySelector('[data-option-list]');
			if (!optionList) {
				return;
			}

			const optionRow = document.createElement('div');
			optionRow.className = 'cgt-option-row';

			const input = document.createElement('input');
			input.type = 'text';
			input.required = true;
			input.name = `questions[${block.dataset.index}][options][]`;
			input.value = value;
			input.placeholder = 'Réponse possible';

			const removeBtn = document.createElement('button');
			removeBtn.type = 'button';
			removeBtn.className = 'cgt-btn cgt-btn-ghost cgt-btn-xs';
			removeBtn.textContent = 'Supprimer';
			removeBtn.addEventListener('click', function () {
				const total = optionList.querySelectorAll('.cgt-option-row').length;
				if (total <= 2) {
					block.classList.add('shake');
					setTimeout(() => block.classList.remove('shake'), 300);
					return;
				}
				optionRow.remove();
			});

			optionRow.appendChild(input);
			optionRow.appendChild(removeBtn);
			optionList.appendChild(optionRow);
		}

		function refreshQuestionTitles() {
			list.querySelectorAll('.cgt-question-block').forEach((block, idx) => {
				const title = block.querySelector('.cgt-question-block__title');
				if (title) {
					title.textContent = `Question ${idx + 1}`;
				}
			});
		}

		function addQuestion(initialData) {
			const fragment = template.content.cloneNode(true);
			const block = fragment.querySelector('.cgt-question-block');

			if (!block) {
				return;
			}

			const currentIndex = questionIndex++;
			block.dataset.index = currentIndex;

			const inputs = block.querySelectorAll('[data-name]');
			inputs.forEach((input) => {
				const name = input.getAttribute('data-name');
				input.name = name.replace(/__index__/g, currentIndex);
				if (initialData && initialData.label && input.name.indexOf('[label]') !== -1) {
					input.value = initialData.label;
				}
			});

			const optionList = block.querySelector('[data-option-list]');
			if (initialData && Array.isArray(initialData.options)) {
				initialData.options.forEach((option) => createOptionRow(block, option.label));
			} else {
				createOptionRow(block);
				createOptionRow(block);
			}

			const addOptionBtn = block.querySelector('[data-add-option]');
			if (addOptionBtn) {
				addOptionBtn.addEventListener('click', function () {
					createOptionRow(block);
				});
			}

			const removeQuestionBtn = block.querySelector('[data-remove-question]');
			if (removeQuestionBtn) {
				removeQuestionBtn.addEventListener('click', function () {
					const total = list.querySelectorAll('.cgt-question-block').length;
					if (total <= 1) {
						block.classList.add('shake');
						setTimeout(() => block.classList.remove('shake'), 300);
						return;
					}
					block.remove();
					refreshQuestionTitles();
				});
			}

			list.appendChild(block);
			refreshQuestionTitles();
		}

		addQuestionBtn.addEventListener('click', function () {
			addQuestion();
		});

		if (!list.querySelector('.cgt-question-block')) {
			addQuestion();
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-questionnaire-builder]').forEach(initBuilder);
	});
})();
