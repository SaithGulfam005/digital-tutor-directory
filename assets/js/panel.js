(function () {
  'use strict';

  document.querySelectorAll('.lesson-list .list-group-item[data-lesson]').forEach((item) => {
    item.addEventListener('click', (e) => {
      e.preventDefault();
      document.querySelectorAll('.lesson-list .list-group-item').forEach((el) => el.classList.remove('active'));
      item.classList.add('active');
      const title = item.dataset.lessonTitle;
      const titleEl = document.getElementById('currentLessonTitle');
      if (titleEl && title) titleEl.textContent = title;
    });
  });

  document.getElementById('markLessonComplete')?.addEventListener('click', () => {
    const active = document.querySelector('.lesson-list .list-group-item.active');
    if (active) {
      active.classList.add('text-success');
      const icon = active.querySelector('.lesson-status');
      if (icon) {
        icon.className = 'bi bi-check-circle-fill text-success lesson-status';
      }
    }
    window.showToast?.('Lesson marked complete!', 'success');
  });

  document.getElementById('addLessonBtn')?.addEventListener('click', () => {
    const wrap = document.getElementById('lessonFields');
    if (!wrap) return;
    const n = wrap.querySelectorAll('.input-group').length + 1;
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML =
      '<span class="input-group-text">' +
      n +
      '</span><input type="text" class="form-control" name="lessons[]" placeholder="Lesson title" required>';
    wrap.appendChild(div);
  });

  document.querySelectorAll('[data-panel-form]').forEach((form) => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
      }
      const redirect = form.dataset.redirect;
      window.showToast?.(form.dataset.successMessage || 'Saved successfully.', 'success');
      if (redirect) {
        setTimeout(() => {
          window.location.href = redirect;
        }, 800);
      }
    });
  });
})();
