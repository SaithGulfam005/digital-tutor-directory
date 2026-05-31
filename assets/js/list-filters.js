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

    if (!emptyEl && config.emptyId) {
      emptyEl = document.createElement('div');
      emptyEl.id = config.emptyId;
      emptyEl.className = 'text-center text-muted py-5 d-none';
      emptyEl.innerHTML =
        '<i class="bi bi-search display-6 d-block mb-2 opacity-50"></i><p class="mb-0">No results match your search or filters.</p>';
      grid.parentElement?.appendChild(emptyEl);
    }

    function getSearchQuery() {
      return normalize(searchInput?.value || '');
    }

    function apply() {
      const query = getSearchQuery();
      const filters = config.getFilters ? config.getFilters() : {};
      let visible = 0;

      items.forEach((item) => {
        const col = item.closest('.col, [data-filter-col]') || item;
        const match = config.matchItem(item, query, filters);
        col.classList.toggle('d-none', !match);
        if (match) visible += 1;
      });

      if (countEl) {
        countEl.textContent = `${visible} of ${items.length} shown`;
      }
      if (emptyEl) {
        emptyEl.classList.toggle('d-none', visible > 0);
      }
      if (config.paginationId) {
        const pagination = document.getElementById(config.paginationId);
        if (pagination) {
          pagination.classList.toggle('d-none', query !== '' || config.hasActiveFilters?.(filters));
        }
      }
    }

    if (searchInput?.dataset.initialValue) {
      searchInput.value = searchInput.dataset.initialValue;
    }

    searchInput?.addEventListener('input', apply);
    config.bindFilterEvents?.(apply);
    apply();

    if (config.initialFilters) {
      config.initialFilters();
      apply();
    }
  }

  function initCourseGrid() {
    initCardGrid({
      gridId: 'courseGrid',
      searchId: 'courseSearch',
      countId: 'courseFilterCount',
      emptyId: 'courseGridEmpty',
      paginationId: 'coursePagination',
      itemSelector: '.course-card',
      getFilters() {
        const categories = [...document.querySelectorAll('.filter-category:checked')].map((c) => c.value);
        const minRating = parseFloat(document.querySelector('.filter-rating:checked')?.value || '0');
        const maxPrice = parseFloat(document.getElementById('priceMax')?.value || 'Infinity');
        return { categories, minRating, maxPrice };
      },
      hasActiveFilters(filters) {
        return (
          filters.categories.length > 0 ||
          filters.minRating > 0 ||
          filters.maxPrice < 100
        );
      },
      matchItem(card, query, { categories, minRating, maxPrice }) {
        const searchText = normalize(card.dataset.search || card.textContent);
        const title = normalize(card.querySelector('.card-title')?.textContent);
        const teacher = normalize(card.dataset.teacher);
        const category = card.dataset.category || '';
        const price = parseFloat(card.dataset.price || '0');
        const rating = parseFloat(card.dataset.rating || '0');

        const matchQuery =
          !query ||
          searchText.includes(query) ||
          title.includes(query) ||
          teacher.includes(query) ||
          normalize(category).includes(query);

        const matchCategory = categories.length === 0 || categories.includes(category);
        const matchPrice = price <= maxPrice;
        const matchRating = rating >= minRating;

        return matchQuery && matchCategory && matchPrice && matchRating;
      },
      bindFilterEvents(apply) {
        document.getElementById('priceMax')?.addEventListener('input', () => {
          const label = document.getElementById('priceLabel');
          const slider = document.getElementById('priceMax');
          if (label && slider) label.textContent = '$' + slider.value;
          apply();
        });
        document.querySelectorAll('.filter-category, .filter-rating').forEach((el) => {
          el.addEventListener('change', apply);
        });
        document.getElementById('clearCourseFilters')?.addEventListener('click', (e) => {
          e.preventDefault();
          const search = document.getElementById('courseSearch');
          if (search) search.value = '';
          document.querySelectorAll('.filter-category').forEach((c) => {
            c.checked = false;
          });
          document.querySelectorAll('.filter-rating').forEach((r) => {
            r.checked = r.value === '0';
          });
          const priceMax = document.getElementById('priceMax');
          if (priceMax) {
            priceMax.value = priceMax.max || '100';
            const label = document.getElementById('priceLabel');
            if (label) label.textContent = '$' + priceMax.value;
          }
          apply();
        });
      },
      initialFilters() {
        const params = new URLSearchParams(location.search);
        const q = params.get('q');
        const category = params.get('category');
        const search = document.getElementById('courseSearch');

        if (q && search) {
          search.value = q;
        }
        if (category) {
          document.querySelectorAll('.filter-category').forEach((cb) => {
            if (cb.value === category) cb.checked = true;
          });
        }
      },
    });
  }

  function initTeacherGrid() {
    initCardGrid({
      gridId: 'teacherGrid',
      searchId: 'teacherSearch',
      countId: 'teacherFilterCount',
      emptyId: 'teacherGridEmpty',
      itemSelector: '.teacher-card',
      getFilters() {
        const subjects = [...document.querySelectorAll('.filter-teacher-subject:checked')].map((c) => c.value);
        const minRating = parseFloat(document.querySelector('.filter-teacher-rating:checked')?.value || '0');
        const minExperience = parseInt(document.getElementById('experienceMin')?.value || '0', 10);
        return { subjects, minRating, minExperience };
      },
      hasActiveFilters(filters) {
        return filters.subjects.length > 0 || filters.minRating > 0 || filters.minExperience > 0;
      },
      matchItem(card, query, { subjects, minRating, minExperience }) {
        const searchText = normalize(card.dataset.search || card.textContent);
        const name = normalize(card.querySelector('h3')?.textContent);
        const subject = card.dataset.subject || '';
        const rating = parseFloat(card.dataset.rating || '0');
        const experience = parseInt(card.dataset.experience || '0', 10);

        const matchQuery = !query || searchText.includes(query) || name.includes(query);
        const matchSubject = subjects.length === 0 || subjects.includes(subject);
        const matchRating = rating >= minRating;
        const matchExperience = experience >= minExperience;

        return matchQuery && matchSubject && matchRating && matchExperience;
      },
      bindFilterEvents(apply) {
        document.querySelectorAll('.filter-teacher-subject, .filter-teacher-rating').forEach((el) => {
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
