<div class="modal fade" id="loginModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0">
        <h5 class="modal-title">Quick Login</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form class="needs-validation" novalidate id="modalLoginForm">
          <div class="form-floating mb-3">
            <input type="email" class="form-control" id="modalEmail" placeholder="Email" required>
            <label for="modalEmail">Email</label>
          </div>
          <div class="form-floating mb-3">
            <input type="password" class="form-control" id="modalPassword" placeholder="Password" required>
            <label for="modalPassword">Password</label>
          </div>
          <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="confirmModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow">
      <div class="modal-body text-center p-4">
        <i class="bi bi-question-circle text-warning display-4 mb-3"></i>
        <h5 id="confirmModalTitle">Are you sure?</h5>
        <p class="text-muted small" id="confirmModalMessage">This action cannot be undone.</p>
        <div class="d-flex gap-2 justify-content-center mt-3">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="confirmModalBtn">Confirm</button>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="toast-container position-fixed bottom-0 end-0 p-3">
  <div id="appToast" class="toast align-items-center text-bg-primary border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body" id="appToastBody">Done.</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>