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

  // Password visibility toggle
  function initPasswordToggle() {
    const passwordFields = document.querySelectorAll('input[type="password"]');
    
    passwordFields.forEach((field) => {
      const wrapper = document.createElement('div');
      wrapper.className = 'position-relative';
      wrapper.style.cssText = 'width: 100%;';
      field.parentNode.insertBefore(wrapper, field);
      wrapper.appendChild(field);
      
      // Add padding to input to make room for button
      field.style.paddingRight = '3rem';
      
      const toggleBtn = document.createElement('button');
      toggleBtn.type = 'button';
      toggleBtn.className = 'btn btn-link';
      toggleBtn.style.cssText = `
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        padding: 0.25rem;
        color: #495057;
        border: none;
        background: transparent;
        cursor: pointer;
        font-size: 1.1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
      `;
      toggleBtn.innerHTML = '<i class="bi bi-eye"></i>';
      toggleBtn.setAttribute('aria-label', 'Toggle password visibility');
      toggleBtn.setAttribute('tabindex', '-1');
      
      wrapper.appendChild(toggleBtn);
      
      // Hover effect
      toggleBtn.addEventListener('mouseenter', () => {
        toggleBtn.style.color = '#0d6efd';
      });
      toggleBtn.addEventListener('mouseleave', () => {
        toggleBtn.style.color = '#495057';
      });
      
      toggleBtn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const isPassword = field.type === 'password';
        field.type = isPassword ? 'text' : 'password';
        toggleBtn.innerHTML = isPassword ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
      });
    });
  }
  
  document.addEventListener('DOMContentLoaded', initPasswordToggle);
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPasswordToggle);
  } else {
    initPasswordToggle();
  }
})();
