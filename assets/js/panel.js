(function () {
  'use strict';

  function buildVideoHtml(title, url) {
    if (!url) {
      return '<div class="video-placeholder mb-3"><i class="bi bi-play-circle"></i><p class="small text-muted mt-2 mb-0">Video will be available soon.</p></div>';
    }

    const youtube = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([\w-]+)/i);
    if (youtube) {
      return `<div class="ratio ratio-16x9 mb-3"><iframe src="https://www.youtube.com/embed/${youtube[1]}" title="${title}" allowfullscreen></iframe></div>`;
    }

    const vimeo = url.match(/vimeo\.com\/(\d+)/i);
    if (vimeo) {
      return `<div class="ratio ratio-16x9 mb-3"><iframe src="https://player.vimeo.com/video/${vimeo[1]}" title="${title}" allowfullscreen></iframe></div>`;
    }

    return `<video id="courseVideoPlayer" class="w-100 rounded mb-3" controls playsinline src="${url}"></video>`;
  }

  document.querySelectorAll('.lesson-list .list-group-item[data-lesson]').forEach((item) => {
    item.addEventListener('click', (e) => {
      e.preventDefault();
      document.querySelectorAll('.lesson-list .list-group-item').forEach((el) => el.classList.remove('active'));
      item.classList.add('active');

      const title = item.dataset.lessonTitle || '';
      const duration = item.dataset.lessonDuration || '';
      const url = item.dataset.lessonUrl || '';
      const lessonId = item.dataset.lesson;

      const titleEl = document.getElementById('currentLessonTitle');
      const durationEl = document.getElementById('currentLessonDuration');
      const videoWrap = document.getElementById('lessonVideoWrap');
      const completeBtn = document.getElementById('markLessonComplete');

      if (titleEl && title) titleEl.textContent = title;
      if (durationEl && duration) durationEl.textContent = duration;
      if (videoWrap) videoWrap.innerHTML = buildVideoHtml(title, url);
      if (completeBtn && lessonId) completeBtn.dataset.lessonId = lessonId;
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
