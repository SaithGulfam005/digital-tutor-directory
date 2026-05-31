(function () {
  'use strict';

  const form = document.getElementById('contactForm');
  if (!form) return;

  const submitBtn = document.getElementById('contactSubmitBtn');
  const successAlert = document.getElementById('contactFormSuccess');
  const apiUrl = (window.BASE_URL || '') + '/api/contact.php';

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!form.checkValidity()) {
      form.classList.add('was-validated');
      return;
    }

    const defaultHtml = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';

    try {
      const body = new FormData(form);
      const response = await fetch(apiUrl, { method: 'POST', body });
      const data = await response.json();
      if (!data.ok) {
        throw new Error(data.message || 'Failed to send message.');
      }

      form.reset();
      form.classList.remove('was-validated');
      successAlert?.classList.remove('d-none');
      window.showToast?.(data.message, 'success');
    } catch (err) {
      window.showToast?.(err.message || 'Failed to send message.', 'danger');
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = defaultHtml;
    }
  });
})();
