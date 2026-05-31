(function () {
  'use strict';

  const roleInput = document.getElementById('loginRole');
  const registerRoleInput = document.getElementById('registerRole');

  document.querySelectorAll('#roleTabs .nav-link').forEach((tab) => {
    tab.addEventListener('click', (e) => {
      e.preventDefault();
      document.querySelectorAll('#roleTabs .nav-link').forEach((t) => t.classList.remove('active'));
      tab.classList.add('active');
      if (roleInput) roleInput.value = tab.dataset.role || 'student';
      if (registerRoleInput) registerRoleInput.value = tab.dataset.role || 'student';
      if (typeof setRegisterRole === 'function') {
        setRegisterRole(tab.dataset.role);
      }
    });
  });

  const teacherFields = document.getElementById('teacherFields');

  function setRegisterRole(role) {
    if (!teacherFields) return;
    const isTeacher = role === 'teacher';
    teacherFields.classList.toggle('d-none', !isTeacher);
    teacherFields.querySelectorAll('.teacher-field').forEach((field) => {
      if (field.type !== 'file') {
        field.required = isTeacher;
      }
      if (!isTeacher) field.setCustomValidity('');
    });
  }

  window.setRegisterRole = setRegisterRole;

  const params = new URLSearchParams(location.search);
  const roleParam = params.get('role');
  if (roleParam) {
    const tab = document.querySelector(`#roleTabs .nav-link[data-role="${roleParam}"]`);
    if (tab) tab.click();
  } else if (registerRoleInput) {
    setRegisterRole(registerRoleInput.value || 'student');
  }

  const registerForm = document.getElementById('registerForm');
  if (registerForm) {
    const pass = document.getElementById('pass');
    const cpass = document.getElementById('cpass');

    function validatePasswordMatch() {
      if (!pass || !cpass) return true;
      if (cpass.value && pass.value !== cpass.value) {
        cpass.setCustomValidity('Passwords do not match.');
        return false;
      }
      cpass.setCustomValidity('');
      return true;
    }

    pass?.addEventListener('input', validatePasswordMatch);
    cpass?.addEventListener('input', validatePasswordMatch);

    registerForm.addEventListener('submit', (e) => {
      validatePasswordMatch();
      if (!registerForm.checkValidity()) {
        e.preventDefault();
        registerForm.classList.add('was-validated');
      }
    });
  }

  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
      if (!loginForm.checkValidity()) {
        e.preventDefault();
        loginForm.classList.add('was-validated');
      }
    });
  }
})();
