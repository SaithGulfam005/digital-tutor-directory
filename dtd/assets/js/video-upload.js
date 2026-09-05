(function () {
  'use strict';

  const uploadUrl = (window.BASE_URL || '') + '/api/video-upload.php';

  function getStatusEl(row) {
    let statusEl = row.querySelector('.lesson-upload-status');
    if (!statusEl) {
      statusEl = document.createElement('small');
      statusEl.className = 'lesson-upload-status d-block mt-2';
      row.appendChild(statusEl);
    }
    return statusEl;
  }

  function clearRowErrors(row) {
    row.querySelector('.lesson-video-file')?.classList.remove('is-invalid');
    row.querySelector('.lesson-video-url')?.classList.remove('is-invalid');
    row.querySelector('.lesson-video-feedback')?.classList.remove('d-block');
  }

  function formatDuration(seconds) {
    const total = Math.max(0, Math.round(seconds || 0));
    const hours = Math.floor(total / 3600);
    const minutes = Math.floor((total % 3600) / 60);
    const secs = total % 60;
    if (hours > 0) {
      return hours + ':' + String(minutes).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
    }
    return minutes + ':' + String(secs).padStart(2, '0');
  }

  function setLessonDuration(row, durationSeconds) {
    const durationInput = row.querySelector('input[name="lesson_durations[]"]');
    if (durationInput) {
      durationInput.value = formatDuration(durationSeconds);
    }
  }

  function detectVideoDuration(file) {
    return new Promise((resolve) => {
      if (!file || !window.URL) {
        resolve(0);
        return;
      }
      const objectUrl = window.URL.createObjectURL(file);
      const video = document.createElement('video');
      video.preload = 'metadata';
      video.onloadedmetadata = () => {
        const duration = Number.isFinite(video.duration) ? video.duration : 0;
        window.URL.revokeObjectURL(objectUrl);
        resolve(duration);
      };
      video.onerror = () => {
        window.URL.revokeObjectURL(objectUrl);
        resolve(0);
      };
      video.src = objectUrl;
    });
  }

  async function uploadLessonVideo(fileInput) {
    const row = fileInput.closest('.lesson-row');
    if (!row) return;

    const file = fileInput.files?.[0];
    const urlInput = row.querySelector('.lesson-video-url');
    if (!file) return;

    if (urlInput?.value?.trim() && /^https?:\/\//i.test(urlInput.value.trim())) {
      window.showToast?.('Clear the external URL first to upload a file.', 'warning');
      fileInput.value = '';
      return;
    }

    const statusEl = getStatusEl(row);
    row.dataset.uploading = '1';
    statusEl.className = 'lesson-upload-status d-block mt-2 text-muted';
    statusEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Uploading lesson file...';

    const formData = new FormData();
    formData.append('video', file);

    try {
      const response = await fetch(uploadUrl, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
      });
      const data = await response.json().catch(() => ({}));
      if (!response.ok || !data.ok) {
        throw new Error(data.error || 'Video upload failed.');
      }

      const durationSeconds = await detectVideoDuration(file);
      if (durationSeconds > 0) {
        setLessonDuration(row, durationSeconds);
      }

      if (urlInput) {
        urlInput.value = data.path;
        urlInput.readOnly = true;
      }
      const durationInput = row.querySelector('input[name="lesson_durations[]"]');
      if (durationInput && data.duration) {
        durationInput.value = data.duration;
      }
      fileInput.value = '';
      clearRowErrors(row);
      statusEl.className = 'lesson-upload-status d-block mt-2 text-success';
      statusEl.innerHTML = '<i class="bi bi-check-circle me-1"></i>Video uploaded successfully';
      window.showToast?.('Lesson file uploaded successfully.', 'success');
    } catch (error) {
      fileInput.value = '';
      statusEl.className = 'lesson-upload-status d-block mt-2 text-danger';
      statusEl.textContent = error.message || 'Lesson upload failed.';
      window.showToast?.(error.message || 'Lesson upload failed.', 'danger');
    } finally {
      delete row.dataset.uploading;
    }
  }

  function markExistingUploads(root) {
    (root || document).querySelectorAll('.lesson-video-url').forEach((input) => {
      if (input.value.trim().startsWith('uploads/')) {
        input.readOnly = true;
      }
    });
  }

  function initLessonVideoUploads(root) {
    (root || document).addEventListener('input', (event) => {
      if (!event.target.matches('.lesson-video-url')) return;
      if (/^https?:\/\//i.test(event.target.value.trim())) {
        event.target.readOnly = false;
      }
    });

    (root || document).addEventListener('change', (event) => {
      if (!event.target.matches('.lesson-video-file')) return;
      uploadLessonVideo(event.target);
    });
  }

  window.initLessonVideoUploads = initLessonVideoUploads;
  window.isLessonVideoUploading = function (container) {
    return Boolean((container || document).querySelector('.lesson-row[data-uploading="1"]'));
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      markExistingUploads(document);
      initLessonVideoUploads(document);
    });
  } else {
    markExistingUploads(document);
    initLessonVideoUploads(document);
  }
})();
