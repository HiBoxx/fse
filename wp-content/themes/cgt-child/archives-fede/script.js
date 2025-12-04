/**
 * Archives de la Fédération - JavaScript
 *
 * Gère la recherche fuzzy, les filtres, et la pagination côté client.
 */

(function() {
	'use strict';

	// Configuration
	const ITEMS_PER_PAGE = 20;
	let currentPage = 1;
	let filteredArticles = [];
	let allArticles = window.archivesData || [];

	// Éléments DOM
	const searchInput = document.getElementById('search-input');
	const searchClear = document.getElementById('search-clear');
	const filterYear = document.getElementById('filter-year');
	const filterBranch = document.getElementById('filter-branch');
	const filterCategory = document.getElementById('filter-category');
	const filterTag = document.getElementById('filter-tag');
	const filterReset = document.getElementById('filter-reset');
	const articlesList = document.getElementById('articles-list');
	const resultsCount = document.getElementById('results-count');
	const pagination = document.getElementById('pagination');

	/**
	 * Normaliser une chaîne pour la recherche (enlever accents, minuscules)
	 */
	function normalizeString(str) {
		return str
			.toLowerCase()
			.normalize('NFD')
			.replace(/[\u0300-\u036f]/g, '');
	}

	/**
	 * Recherche fuzzy simple
	 */
	function fuzzyMatch(search, text) {
		const searchNorm = normalizeString(search);
		const textNorm = normalizeString(text);

		// Recherche exacte
		if (textNorm.includes(searchNorm)) {
			return true;
		}

		// Recherche fuzzy : chaque caractère doit apparaître dans l'ordre
		let searchIndex = 0;
		for (let i = 0; i < textNorm.length && searchIndex < searchNorm.length; i++) {
			if (textNorm[i] === searchNorm[searchIndex]) {
				searchIndex++;
			}
		}
		return searchIndex === searchNorm.length;
	}

	/**
	 * Appliquer les filtres et la recherche
	 */
	function applyFilters() {
		const searchTerm = searchInput.value.trim();
		const yearFilter = filterYear ? filterYear.value : '';
		const branchFilter = filterBranch ? filterBranch.value : '';
		const categoryFilter = filterCategory ? filterCategory.value : '';
		const tagFilter = filterTag ? filterTag.value : '';

		filteredArticles = allArticles.filter(function(article) {
			// Filtre de recherche
			if (searchTerm) {
				const titleMatch = fuzzyMatch(searchTerm, article.title);
				const contentMatch = fuzzyMatch(searchTerm, article.content);
				if (!titleMatch && !contentMatch) {
					return false;
				}
			}

			// Filtre année
			if (yearFilter && article.year !== yearFilter) {
				return false;
			}

			// Filtre branche
			if (branchFilter) {
				const hasBranch = article.branches.some(function(b) {
					return b.slug === branchFilter;
				});
				if (!hasBranch) {
					return false;
				}
			}

			// Filtre catégorie
			if (categoryFilter) {
				const hasCategory = article.categories.some(function(c) {
					return c.slug === categoryFilter;
				});
				if (!hasCategory) {
					return false;
				}
			}

			// Filtre tag
			if (tagFilter) {
				const hasTag = article.tags.some(function(t) {
					return t.slug === tagFilter;
				});
				if (!hasTag) {
					return false;
				}
			}

			return true;
		});

		// Afficher/masquer le bouton clear
		if (searchClear) {
			searchClear.style.display = searchTerm ? 'block' : 'none';
		}

		// Réinitialiser à la page 1
		currentPage = 1;

		// Afficher les résultats
		displayResults();
	}

	/**
	 * Afficher les résultats
	 */
	function displayResults() {
		const totalResults = filteredArticles.length;
		const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
		const endIndex = startIndex + ITEMS_PER_PAGE;
		const pageArticles = filteredArticles.slice(startIndex, endIndex);

		// Mettre à jour le compteur
		if (resultsCount) {
			resultsCount.textContent = totalResults + ' article(s)';
		}

		// Afficher les articles
		if (pageArticles.length === 0) {
			articlesList.innerHTML = '<div class="no-results"><p>Aucun article ne correspond à votre recherche.</p></div>';
		} else {
			articlesList.innerHTML = pageArticles.map(renderArticle).join('');
		}

		// Afficher la pagination
		renderPagination(totalResults);

		// Scroll en haut de la liste
		articlesList.scrollIntoView({ behavior: 'smooth', block: 'start' });
	}

	/**
	 * Rendre un article HTML
	 */
	function renderArticle(article) {
		const date = formatDate(article.date);
		const articleUrl = window.archivesBaseUrl + 'article.php?id=' + article.id;

		let html = '<article class="archive-item">';
		html += '<div class="archive-item-header">';
		html += '<h2 class="archive-item-title"><a href="' + articleUrl + '">' + escapeHtml(article.title) + '</a></h2>';
		html += '<time class="archive-item-date">' + date + '</time>';
		html += '</div>';

		// Catégories
		if (article.categories && article.categories.length > 0) {
			html += '<div class="archive-item-categories">';
			article.categories.forEach(function(cat) {
				html += '<span class="badge badge-category">' + escapeHtml(cat.name) + '</span> ';
			});
			html += '</div>';
		}

		// Extrait
		if (article.excerpt) {
			html += '<div class="archive-item-excerpt">' + escapeHtml(article.excerpt) + '</div>';
		}

		// Branches
		if (article.branches && article.branches.length > 0) {
			html += '<div class="archive-item-branches">';
			html += '<strong>Branche(s) :</strong> ';
			const branchNames = article.branches.map(function(b) {
				return escapeHtml(b.name);
			});
			html += branchNames.join(', ');
			html += '</div>';
		}

		// Lien PDF
		if (article.pdf_url) {
			html += '<div class="archive-item-pdf">';
			html += '<a href="' + escapeHtml(article.pdf_url) + '" class="btn-pdf-small" target="_blank" rel="noopener">📄 PDF</a>';
			html += '</div>';
		}

		html += '</article>';
		return html;
	}

	/**
	 * Rendre la pagination
	 */
	function renderPagination(totalResults) {
		const totalPages = Math.ceil(totalResults / ITEMS_PER_PAGE);

		if (totalPages <= 1) {
			pagination.innerHTML = '';
			return;
		}

		let html = '<div class="pagination-wrapper">';

		// Bouton précédent
		if (currentPage > 1) {
			html += '<button class="pagination-btn" data-page="' + (currentPage - 1) + '">← Précédent</button>';
		}

		// Numéros de page
		html += '<span class="pagination-info">Page ' + currentPage + ' sur ' + totalPages + '</span>';

		// Bouton suivant
		if (currentPage < totalPages) {
			html += '<button class="pagination-btn" data-page="' + (currentPage + 1) + '">Suivant →</button>';
		}

		html += '</div>';
		pagination.innerHTML = html;

		// Attacher les événements
		pagination.querySelectorAll('.pagination-btn').forEach(function(btn) {
			btn.addEventListener('click', function() {
				currentPage = parseInt(this.getAttribute('data-page'), 10);
				displayResults();
			});
		});
	}

	/**
	 * Formater une date
	 */
	function formatDate(dateStr) {
		const parts = dateStr.split('-');
		if (parts.length === 3) {
			return parts[2] + '/' + parts[1] + '/' + parts[0];
		}
		return dateStr;
	}

	/**
	 * Échapper le HTML
	 */
	function escapeHtml(text) {
		const div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}

	/**
	 * Réinitialiser les filtres
	 */
	function resetFilters() {
		searchInput.value = '';
		if (filterYear) filterYear.value = '';
		if (filterBranch) filterBranch.value = '';
		if (filterCategory) filterCategory.value = '';
		if (filterTag) filterTag.value = '';
		applyFilters();
	}

	/**
	 * Gérer les paramètres URL
	 */
	function handleUrlParams() {
		const params = new URLSearchParams(window.location.search);

		if (params.has('search')) {
			searchInput.value = params.get('search');
		}
		if (params.has('year') && filterYear) {
			filterYear.value = params.get('year');
		}
		if (params.has('branch') && filterBranch) {
			filterBranch.value = params.get('branch');
		}
		if (params.has('category') && filterCategory) {
			filterCategory.value = params.get('category');
		}
		if (params.has('tag') && filterTag) {
			filterTag.value = params.get('tag');
		}
	}

	/**
	 * Mettre à jour l'URL
	 */
	function updateUrl() {
		const params = new URLSearchParams();

		if (searchInput.value) {
			params.set('search', searchInput.value);
		}
		if (filterYear && filterYear.value) {
			params.set('year', filterYear.value);
		}
		if (filterBranch && filterBranch.value) {
			params.set('branch', filterBranch.value);
		}
		if (filterCategory && filterCategory.value) {
			params.set('category', filterCategory.value);
		}
		if (filterTag && filterTag.value) {
			params.set('tag', filterTag.value);
		}

		const newUrl = params.toString() ? '?' + params.toString() : window.location.pathname;
		history.replaceState(null, '', newUrl);
	}

	/**
	 * Initialisation
	 */
	function init() {
		// Charger les paramètres URL
		handleUrlParams();

		// Initialiser les filtres
		filteredArticles = allArticles;
		applyFilters();

		// Événements de recherche
		if (searchInput) {
			searchInput.addEventListener('input', function() {
				applyFilters();
				updateUrl();
			});
		}

		if (searchClear) {
			searchClear.addEventListener('click', function() {
				searchInput.value = '';
				applyFilters();
				updateUrl();
			});
		}

		// Événements de filtres
		[filterYear, filterBranch, filterCategory, filterTag].forEach(function(filter) {
			if (filter) {
				filter.addEventListener('change', function() {
					applyFilters();
					updateUrl();
				});
			}
		});

		// Bouton reset
		if (filterReset) {
			filterReset.addEventListener('click', function() {
				resetFilters();
				updateUrl();
			});
		}
	}

	// Démarrer quand le DOM est prêt
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

})();
