(function () {
  'use strict';

  /** @type {Map<string, { query: string, role: string, status: string }>} */
  const tableStates = new Map();

  function normalize(text) {
    return (text || '').toLowerCase().trim();
  }

  function getTableState(tableId) {
    if (!tableStates.has(tableId)) {
      tableStates.set(tableId, { query: '', role: 'all', status: 'all' });
    }
    return tableStates.get(tableId);
  }

  function getRowSearchText(row) {
    if (row.dataset.search) {
      return normalize(row.dataset.search);
    }
    const clone = row.cloneNode(true);
    clone.querySelectorAll('button, .btn, [data-search-ignore]').forEach((el) => el.remove());
    return normalize(clone.textContent.replace(/\s+/g, ' '));
  }

  function updateTableEmpty(tableId, visibleCount) {
    const table = document.getElementById(tableId);
    if (!table) return;

    let emptyEl = document.querySelector(`[data-table-empty="${tableId}"]`);
    if (!emptyEl) {
      emptyEl = document.createElement('div');
      emptyEl.dataset.tableEmpty = tableId;
      emptyEl.className = 'table-empty-state text-center text-muted py-5 d-none';
      emptyEl.innerHTML = '<i class="bi bi-search display-6 d-block mb-2 opacity-50"></i><p class="mb-0">No results match your search or filters.</p>';
      const wrap = table.closest('.table-responsive') || table.parentElement;
      wrap?.parentElement?.appendChild(emptyEl);
    }

    const tbody = table.tBodies[0];
    const totalRows = tbody ? tbody.querySelectorAll('tr').length : 0;
    const hiddenByAdmin = tbody
      ? [...tbody.querySelectorAll('tr')].filter((r) => r.dataset.adminRemoved === 'true').length
      : 0;
    const searchableTotal = totalRows - hiddenByAdmin;

    emptyEl.classList.toggle('d-none', visibleCount > 0 || searchableTotal === 0);
    if (visibleCount === 0 && searchableTotal > 0) {
      const state = getTableState(tableId);
      const parts = [];
      if (state.query) parts.push('search');
      if (state.role !== 'all' || state.status !== 'all') parts.push('filters');
      const hint = parts.length ? `Try adjusting your ${parts.join(' and ')}.` : '';
      emptyEl.querySelector('p').textContent = `No results match your criteria. ${hint}`.trim();
    }
  }

  window.applyTableFilters = function applyTableFilters(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const state = getTableState(tableId);
    const tbody = table.tBodies[0];
    if (!tbody) return;

    const query = normalize(state.query);
    let visible = 0;

    tbody.querySelectorAll('tr').forEach((row) => {
      if (row.dataset.adminRemoved === 'true') {
        row.classList.add('d-none');
        return;
      }

      const matchQuery = !query || getRowSearchText(row).includes(query);
      const matchRole = state.role === 'all' || row.dataset.role === state.role;
      const matchStatus = state.status === 'all' || row.dataset.status === state.status;
      const show = matchQuery && matchRole && matchStatus;

      row.classList.toggle('d-none', !show);
      if (show) visible += 1;
    });

    updateTableEmpty(tableId, visible);

    const counter = document.querySelector(`[data-table-count="${tableId}"]`);
    if (counter) {
      counter.textContent = `${visible} result${visible === 1 ? '' : 's'}`;
    }
  };

  function initTableSearch() {
    document.querySelectorAll('[data-table-search]').forEach((input) => {
      const tableId = input.dataset.tableSearch;
      if (!tableId) return;

      if (input.dataset.initialValue) {
        input.value = input.dataset.initialValue;
        getTableState(tableId).query = normalize(input.dataset.initialValue);
      }

      input.addEventListener('input', () => {
        getTableState(tableId).query = input.value;
        applyTableFilters(tableId);
      });

      applyTableFilters(tableId);
    });
  }

  function initTableFilterPills() {
    document.querySelectorAll('[data-filter-table]').forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();

        const tableId = btn.dataset.filterTable;
        if (!tableId) return;

        const nav = btn.closest('.nav, .btn-group');
        if (nav) {
          nav.querySelectorAll('[data-filter-table]').forEach((sibling) => {
            const sameType =
              (btn.dataset.filterRole !== undefined && sibling.dataset.filterRole !== undefined) ||
              (btn.dataset.filterStatus !== undefined && sibling.dataset.filterStatus !== undefined);
            if (sameType) sibling.classList.remove('active');
          });
        }
        btn.classList.add('active');

        const state = getTableState(tableId);
        if (btn.dataset.filterRole !== undefined) {
          state.role = btn.dataset.filterRole;
        }
        if (btn.dataset.filterStatus !== undefined) {
          state.status = btn.dataset.filterStatus;
        }

        applyTableFilters(tableId);
      });
    });
  }

  function initCardGrid(config) {
    const grid = document.getElementById(config.gridId);
    if (!grid) return;

    const items = [...grid.querySelectorAll(config.itemSelector || '[data-filter-item]')];
    if (!items.length) return;

    const searchInput = config.searchId ? document.getElementById(config.searchId) : null;
    const countEl = config.countId ? document.getElementById(config.countId) : null;
    let emptyEl = config.emptyId ? document.getElementById(config.emptyId) : null;
    let currentPage = 1;

    if (!emptyEl && config.emptyId) {
      emptyEl = document.createElement('div');
      emptyEl.id = config.emptyId;
      emptyEl.className = 'text-center text-muted py-5 d-none';
      emptyEl.innerHTML =
        '<i class="bi bi-search display-6 d-block mb-2 opacity-50"></i><p class="mb-0">No results match your search or filters.</p>';
      grid.parentElement?.appendChild(emptyEl);
    }

    function getColumnWrapper(item) {
      return item.closest('[data-filter-col], .col-md-6, .col-xl-4, .col') || item;
    }

    function getSearchQuery() {
      return normalize(searchInput?.value || '');
    }

    function getFilters() {
      return config.getFilters ? config.getFilters() : {};
    }

    function updatePagination(totalPages) {
      if (!config.paginationId) return;
      const pagination = document.getElementById(config.paginationId);
      if (!pagination) return;

      const shouldShow = totalPages > 1;
      pagination.classList.toggle('d-none', !shouldShow);

      if (!shouldShow) return;

      const ul = pagination.querySelector('ul');
      if (!ul) return;

      ul.innerHTML = '';
      for (let p = 1; p <= totalPages; p++) {
        const li = document.createElement('li');
        li.className = `page-item ${p === currentPage ? 'active' : ''}`;
        const link = document.createElement('a');
        link.className = 'page-link';
        link.href = '#';
        link.textContent = String(p);
        link.addEventListener('click', (e) => {
          e.preventDefault();
          currentPage = p;
          apply();
          window.scrollTo({ top: grid.offsetTop - 100, behavior: 'smooth' });
        });
        li.appendChild(link);
        ul.appendChild(li);
      }
    }

    function apply() {
      const query = getSearchQuery();
      const filters = getFilters();
      const hasFilters = config.hasActiveFilters?.(filters);
      const itemsPerPage = config.itemsPerPage || 12;
      const matched = [];

      items.forEach((item) => {
        const col = getColumnWrapper(item);
        const match = config.matchItem(item, query, filters);
        col.classList.add('d-none');
        if (match) {
          matched.push(col);
        }
      });

      const shouldPaginate = query === '' && !hasFilters && matched.length > itemsPerPage;
      const totalPages = shouldPaginate ? Math.ceil(matched.length / itemsPerPage) : 1;

      if (currentPage > totalPages) {
        currentPage = 1;
      }

      matched.forEach((col, idx) => {
        const visible = !shouldPaginate || (idx >= (currentPage - 1) * itemsPerPage && idx < currentPage * itemsPerPage);
        col.classList.toggle('d-none', !visible);
      });

      const visibleCount = matched.filter((col) => !col.classList.contains('d-none')).length;
      if (countEl) {
        countEl.textContent = `${visibleCount} of ${items.length} shown`;
      }
      if (emptyEl) {
        emptyEl.classList.toggle('d-none', visibleCount > 0);
      }

      updatePagination(shouldPaginate ? totalPages : 1);
    }

    if (searchInput?.dataset.initialValue) {
      searchInput.value = searchInput.dataset.initialValue;
    }

    searchInput?.addEventListener('input', () => {
      currentPage = 1;
      apply();
    });

    config.bindFilterEvents?.(() => {
      currentPage = 1;
      apply();
    });

    if (config.initialFilters) {
      config.initialFilters();
    }

    currentPage = 1;
    apply();
  }

  function initCourseGrid() {
    const searchInput = document.getElementById('courseSearch');
    const countEl = document.getElementById('courseFilterCount');
    const emptyEl = document.getElementById('courseGridEmpty');
    const wrappers = [...document.querySelectorAll('#courseGrid [data-filter-col]')];

    function getSelectedCategories() {
      return [...document.querySelectorAll('.filter-category:checked')].map((cb) => normalize(cb.value));
    }

    function applyCourseFilters() {
      const query = normalize(searchInput ? searchInput.value : '');
      const selectedCategories = getSelectedCategories();
      const minRating = parseFloat(document.querySelector('.filter-rating:checked')?.value || '0');
      const maxPrice = parseFloat(document.getElementById('priceMax')?.value || '100');
      let visibleCount = 0;

      wrappers.forEach((wrapper) => {
        const card = wrapper.querySelector('.course-card');
        if (!card) {
          wrapper.classList.add('d-none');
          return;
        }

        const title = normalize(card.querySelector('.card-title')?.textContent || '');
        const teacher = normalize(card.dataset.teacher || '');
        const category = normalize(card.dataset.category || '');
        const searchText = normalize(card.dataset.search || `${title} ${teacher} ${category}`);
        const price = parseFloat(card.dataset.price || '0');
        const rating = parseFloat(card.dataset.rating || '0');

        const matchesQuery = !query || searchText.includes(query) || title.includes(query) || teacher.includes(query) || category.includes(query);
        const matchesCategory = selectedCategories.length === 0 || selectedCategories.some((selected) => {
          return category === selected || category.includes(selected) || selected.includes(category);
        });
        const matchesPrice = price <= maxPrice;
        const matchesRating = rating >= minRating;
        const show = matchesQuery && matchesCategory && matchesPrice && matchesRating;

        wrapper.classList.toggle('d-none', !show);
        if (show) visibleCount += 1;
      });

      if (countEl) {
        countEl.textContent = `${visibleCount} of ${wrappers.length} shown`;
      }
      if (emptyEl) {
        emptyEl.classList.toggle('d-none', visibleCount > 0);
      }
    }

    const params = new URLSearchParams(location.search);
    const initialQuery = params.get('q');
    const initialCategory = params.get('category');

    if (initialQuery && searchInput) {
      searchInput.value = initialQuery;
    }

    if (initialCategory) {
      document.querySelectorAll('.filter-category').forEach((cb) => {
        cb.checked = normalize(cb.value) === normalize(initialCategory);
      });
    }

    searchInput?.addEventListener('input', applyCourseFilters);
    document.querySelectorAll('.filter-category, .filter-rating').forEach((el) => {
      el.addEventListener('change', applyCourseFilters);
    });

    document.getElementById('priceMax')?.addEventListener('input', () => {
      const slider = document.getElementById('priceMax');
      const label = document.getElementById('priceLabel');
      if (slider && label) {
        label.textContent = 'PKR ' + slider.value;
      }
      applyCourseFilters();
    });

    document.getElementById('clearCourseFilters')?.addEventListener('click', (e) => {
      e.preventDefault();
      if (searchInput) searchInput.value = '';
      document.querySelectorAll('.filter-category').forEach((cb) => {
        cb.checked = false;
      });
      document.querySelectorAll('.filter-rating').forEach((r) => {
        r.checked = r.value === '0';
      });
      const priceMax = document.getElementById('priceMax');
      if (priceMax) {
        priceMax.value = priceMax.max || '100';
        const label = document.getElementById('priceLabel');
        if (label) label.textContent = 'PKR ' + priceMax.value;
      }
      applyCourseFilters();
    });

    applyCourseFilters();
  }

  function initTeacherGrid() {
    initCardGrid({
      gridId: 'teacherGrid',
      searchId: 'teacherSearch',
      countId: 'teacherFilterCount',
      emptyId: 'teacherGridEmpty',
      itemSelector: '.teacher-card',
      getFilters() {
        const categories = [...document.querySelectorAll('.filter-teacher-category:checked')].map((c) => c.value);
        const subjects = [...document.querySelectorAll('.filter-teacher-subject:checked')].map((c) => c.value);
        const minRating = parseFloat(document.querySelector('.filter-teacher-rating:checked')?.value || '0');
        const minExperience = parseInt(document.getElementById('experienceMin')?.value || '0', 10);
        return { categories, subjects, minRating, minExperience };
      },
      hasActiveFilters(filters) {
        return filters.categories.length > 0 || filters.subjects.length > 0 || filters.minRating > 0 || filters.minExperience > 0;
      },
      matchItem(card, query, { categories, subjects, minRating, minExperience }) {
        const searchText = normalize(card.dataset.search || card.textContent);
        const name = normalize(card.querySelector('h3')?.textContent);
        const categoriesForTeacher = (card.dataset.category || '').split('|').filter(Boolean);
        const teacherSubjects = (card.dataset.subject || '').split('|').filter(Boolean);
        const rating = parseFloat(card.dataset.rating || '0');
        const experience = parseInt(card.dataset.experience || '0', 10);

        const matchQuery = !query || searchText.includes(query) || name.includes(query);
        const matchCategory = categories.length === 0 || categories.some((category) => categoriesForTeacher.includes(category));
        const matchSubject = subjects.length === 0 || subjects.some((subject) => teacherSubjects.includes(subject));
        const matchRating = rating >= minRating;
        const matchExperience = experience >= minExperience;

        return matchQuery && matchCategory && matchSubject && matchRating && matchExperience;
      },
      bindFilterEvents(apply) {
        document.querySelectorAll('.filter-teacher-category, .filter-teacher-subject, .filter-teacher-rating').forEach((el) => {
          el.addEventListener('change', apply);
        });
        document.getElementById('experienceMin')?.addEventListener('input', () => {
          const label = document.getElementById('experienceLabel');
          const slider = document.getElementById('experienceMin');
          if (label && slider) {
            label.textContent = slider.value === '0' ? 'Any' : slider.value + '+ years';
          }
          apply();
        });
        document.getElementById('clearTeacherFilters')?.addEventListener('click', (e) => {
          e.preventDefault();
          const search = document.getElementById('teacherSearch');
          if (search) search.value = '';
          document.querySelectorAll('.filter-teacher-category').forEach((c) => {
            c.checked = false;
          });
          document.querySelectorAll('.filter-teacher-subject').forEach((c) => {
            c.checked = false;
          });
          document.querySelectorAll('.filter-teacher-rating').forEach((r) => {
            r.checked = r.value === '0';
          });
          const exp = document.getElementById('experienceMin');
          if (exp) {
            exp.value = '0';
            const label = document.getElementById('experienceLabel');
            if (label) label.textContent = 'Any';
          }
          apply();
        });
      },
      initialFilters() {
        const category = new URLSearchParams(location.search).get('category') || new URLSearchParams(location.search).get('subject');
        if (!category) return;

        document.querySelectorAll('.filter-teacher-category, .filter-teacher-subject').forEach((checkbox) => {
          checkbox.checked = checkbox.value === category;
        });
      },
    });
  }

  function syncFilterStateFromDom() {
    document.querySelectorAll('[data-filter-table].active').forEach((btn) => {
      const tableId = btn.dataset.filterTable;
      if (!tableId) return;
      const state = getTableState(tableId);
      if (btn.dataset.filterRole !== undefined) {
        state.role = btn.dataset.filterRole;
      }
      if (btn.dataset.filterStatus !== undefined) {
        state.status = btn.dataset.filterStatus;
      }
    });
  }

  syncFilterStateFromDom();
  initTableFilterPills();
  initTableSearch();
  initCourseGrid();
  initTeacherGrid();
})();
