(function () {
  'use strict';

  const API = (window.BASE_URL || '') + '/api/admin-action.php';

  const actionMap = {
    approve: null,
    reject: null,
    activate: 'activate_user',
    deactivate: 'deactivate_user',
    delete: null,
    refund: 'refund_payment',
    feature: null,
  };

  document.querySelectorAll('[data-admin-action]').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const uiAction = btn.dataset.adminAction;
      const row = btn.closest('tr');
      const table = row?.closest('table');
      let apiAction = btn.dataset.apiAction || actionMap[uiAction];

      if (table?.id === 'verificationsTable') {
        apiAction = uiAction === 'approve' ? 'approve_teacher' : uiAction === 'reject' ? 'reject_teacher' : apiAction;
      } else if (table?.id === 'coursesTable') {
        apiAction = uiAction === 'approve' ? 'approve_course' : uiAction === 'reject' ? 'reject_course' : uiAction === 'delete' ? 'delete_course' : apiAction;
      } else if (table?.id === 'paymentsTable') {
        apiAction = uiAction === 'approve' ? 'confirm_payment' : uiAction === 'refund' ? 'refund_payment' : apiAction;
      }

      const id = btn.dataset.apiId
        || row?.dataset.userId
        || row?.dataset.courseId
        || row?.dataset.paymentId;

      if (!apiAction || !id) {
        window.showToast?.('Backend action not available for this item.', 'warning');
        return;
      }

      const label = btn.dataset.adminLabel || 'this item';
      const run = () => {
        const body = new FormData();
        body.append('action', apiAction);
        body.append('id', id);
        fetch(API, { method: 'POST', body })
          .then((r) => r.json())
          .then((data) => {
            if (!data.ok) throw new Error(data.message || 'Action failed');
            if (row) {
              if (uiAction === 'approve' || uiAction === 'activate') {
                const badge = row.querySelector('.status-badge');
                if (badge) {
                  if (table?.id === 'paymentsTable' && uiAction === 'approve') {
                    badge.className = 'badge status-badge badge-completed';
                    badge.textContent = 'Completed';
                  } else {
                    badge.className = 'badge status-badge badge-approved';
                    badge.textContent = uiAction === 'approve' ? 'Approved' : 'Active';
                  }
                }
              } else if (uiAction === 'reject' || uiAction === 'deactivate') {
                row.classList.add('opacity-50');
              }
            }
            window.showToast?.(data.message, 'success');
            setTimeout(() => location.reload(), 600);
          })
          .catch((err) => window.showToast?.(err.message, 'danger'));
      };

      const needsConfirm = ['reject', 'delete', 'deactivate', 'refund'].includes(uiAction);
      if (window.showConfirm && needsConfirm) {
        window.showConfirm('Confirm', `Proceed with ${uiAction} for ${label}?`, run);
      } else {
        run();
      }
    });
  });
})();
