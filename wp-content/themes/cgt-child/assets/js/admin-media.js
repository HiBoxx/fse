(function () {
	'use strict';

	const FILTERS = [
		{ label: 'Images', key: 'image' },
		{ label: 'PDF', key: 'pdf' },
	];

	let activeFilter = null;
	let gridObserver = null;

	function createFiltersUI() {
		const toolbar =
			document.querySelector('.wp-filter .media-toolbar-secondary') ||
			document.querySelector('.wp-filter');

		if (!toolbar) {
			return null;
		}

		let container = toolbar.querySelector('.cgt-media-filters');
		if (container) {
			return container;
		}

		container = document.createElement('div');
		container.className = 'cgt-media-filters';

		FILTERS.forEach((filter) => {
			const button = document.createElement('button');
			button.type = 'button';
			button.className = 'cgt-media-filter';
			button.dataset.filterKey = filter.key;
			button.setAttribute('aria-pressed', 'false');
			button.textContent = filter.label;
			button.addEventListener('click', onFilterButtonClick);
			container.appendChild(button);
		});

		toolbar.appendChild(container);
		return container;
	}

	function onFilterButtonClick(event) {
		const button = event.currentTarget;
		const filterKey = button.dataset.filterKey;

		if (activeFilter === filterKey) {
			activeFilter = null;
		} else {
			activeFilter = filterKey;
		}

		updateButtonsState(button.parentElement);
		applyFilter();
	}

	function updateButtonsState(container) {
		const buttons = container.querySelectorAll('.cgt-media-filter');
		buttons.forEach((btn) => {
			const isActive = activeFilter === btn.dataset.filterKey;
			btn.classList.toggle('is-active', isActive);
			btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
		});
	}

	function parseMimeFromClassList(classList) {
		let mime = '';
		let subtype = '';

		classList.forEach((cls) => {
			if (cls.startsWith('mime-type-')) {
				const raw = cls.replace('mime-type-', '');
				const firstDash = raw.indexOf('-');
				if (firstDash !== -1) {
					const primary = raw.substring(0, firstDash);
					const secondary = raw.substring(firstDash + 1);
					mime = primary + '/' + secondary;
				} else {
					mime = raw;
				}
			}
			if (cls.startsWith('subtype-')) {
				subtype = cls.replace('subtype-', '');
			}
			if (!mime && cls.startsWith('type-')) {
				const typeCandidate = cls.replace('type-', '');
				if (typeCandidate && typeCandidate !== 'attachment') {
					mime = typeCandidate;
				}
			}
		});

		const type = mime.includes('/') ? mime.split('/')[0] : mime;

		return {
			type: type || '',
			subtype: subtype || '',
			mime: mime || '',
		};
	}

	function doesItemMatchFilter(type, subtype, mime) {
		if (!activeFilter) {
			return true;
		}

		const normalizedType = (type || '').toLowerCase();
		const normalizedSubtype = (subtype || '').toLowerCase();
		const normalizedMime = (mime || '').toLowerCase();
		const combined = normalizedType + '/' + normalizedSubtype;

		if (activeFilter === 'image') {
			return (
				normalizedType === 'image' ||
				normalizedMime.startsWith('image/') ||
				combined.startsWith('image/')
			);
		}

		if (activeFilter === 'pdf') {
			return (
				normalizedSubtype === 'pdf' ||
				normalizedMime === 'application/pdf' ||
				combined === 'application/pdf' ||
				normalizedMime.includes('pdf') ||
				combined.includes('pdf')
			);
		}

		return true;
	}

	function filterListView() {
		const rows = document.querySelectorAll('.wp-list-table.media tbody tr');
		if (!rows.length) {
			return;
		}

		let visibleCount = 0;
		rows.forEach((row) => {
			if (row.classList.contains('no-items')) {
				return;
			}

			if (!row.dataset.mediaMime || !row.dataset.mediaSubtype) {
				const parsed = parseMimeFromClassList(row.classList);
				row.dataset.mediaMime = parsed.mime;
				row.dataset.mediaSubtype = parsed.subtype;
				row.dataset.mediaType = parsed.type;
			}

			const matches = doesItemMatchFilter(
				row.dataset.mediaType,
				row.dataset.mediaSubtype,
				row.dataset.mediaMime
			);

			row.style.display = matches ? '' : 'none';
			if (matches) {
				visibleCount++;
			}
		});

		toggleListEmptyMessage(visibleCount === 0);
	}

	function toggleListEmptyMessage(isEmpty) {
		const tbody = document.querySelector('.wp-list-table.media tbody');
		if (!tbody) {
			return;
		}

		let emptyRow = document.getElementById('cgt-media-filter-empty');

		if (!isEmpty) {
			if (emptyRow) {
				emptyRow.remove();
			}
			return;
		}

		if (!emptyRow) {
			const colSpan =
				(tbody.querySelector('tr:not(.no-items) td') || {}).colSpan || 6;
			emptyRow = document.createElement('tr');
			emptyRow.id = 'cgt-media-filter-empty';
			emptyRow.innerHTML = `<td colspan="${colSpan}" class="no-items">${cgtMediaFilter.i18nEmptyNotice}</td>`;
		}

		tbody.appendChild(emptyRow);
	}

	function filterGridView() {
		const attachments = document.querySelectorAll(
			'.media-frame.mode-grid .attachments .attachment'
		);
		if (!attachments.length) {
			return;
		}

		let visibleCount = 0;
		attachments.forEach((item) => {
			const type = item.dataset.type || '';
			const subtype = item.dataset.subtype || '';
			const mime = item.dataset.mimeType || '';
			const matches = doesItemMatchFilter(type, subtype, mime);
			item.style.display = matches ? '' : 'none';
			if (matches) {
				visibleCount++;
			}
		});

		toggleGridEmptyMessage(visibleCount === 0);
	}

	function toggleGridEmptyMessage(isEmpty) {
		const browser = document.querySelector('.media-frame.mode-grid .attachments-browser');
		if (!browser) {
			return;
		}

		let emptyNotice = document.getElementById('cgt-media-grid-empty');

		if (!isEmpty) {
			if (emptyNotice) {
				emptyNotice.remove();
			}
			return;
		}

		if (!emptyNotice) {
			emptyNotice = document.createElement('div');
			emptyNotice.id = 'cgt-media-grid-empty';
			emptyNotice.className = 'notice notice-info';
			emptyNotice.textContent = cgtMediaFilter.i18nEmptyNotice;
			emptyNotice.style.marginTop = '1rem';
		}

		browser.appendChild(emptyNotice);
	}

	function applyFilter() {
		filterListView();
		filterGridView();
	}

	function observeGrid() {
		const gridContainer = document.querySelector(
			'.media-frame.mode-grid .attachments'
		);

		if (!gridContainer || typeof MutationObserver === 'undefined') {
			return;
		}

		if (gridObserver) {
			gridObserver.disconnect();
		}

		gridObserver = new MutationObserver(() => {
			window.requestAnimationFrame(applyFilter);
		});

		gridObserver.observe(gridContainer, { childList: true });
	}

	function handleViewSwitches() {
		const switchLinks = document.querySelectorAll('.view-switch a');
		switchLinks.forEach((link) => {
			link.addEventListener('click', () => {
				setTimeout(() => {
					observeGrid();
					applyFilter();
				}, 250);
			});
		});
	}

	document.addEventListener('DOMContentLoaded', () => {
		if (!document.body.classList.contains('upload-php')) {
			return;
		}

		const container = createFiltersUI();
		if (!container) {
			return;
		}

		updateButtonsState(container);
		applyFilter();
		observeGrid();
		handleViewSwitches();

		document.addEventListener('click', (event) => {
			const target = event.target;
			if (target && target.closest('.attachments-browser .media-toolbar-secondary button.media-button-select')) {
				setTimeout(applyFilter, 100);
			}
		});
	});
})();
