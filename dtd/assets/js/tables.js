// Table search + tab filters are handled by list-filters.js (applyTableFilters).

// Confirm modal helper
window.showConfirm = function (title, message, onConfirm) {
  document.getElementById('confirmModalTitle').textContent = title;
  document.getElementById('confirmModalMessage').textContent = message;
  const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
  const btn = document.getElementById('confirmModalBtn');
  const handler = () => {
    onConfirm?.();
    modal.hide();
    btn.removeEventListener('click', handler);
  };
  btn.addEventListener('click', handler);
  modal.show();
};
