<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/navbar.js') ?>"></script>
<script src="<?= asset('js/forms.js') ?>"></script>
<script src="<?= asset('js/main.js') ?>"></script>
<script src="<?= asset('js/chatbot.js') ?>"></script>
<script src="<?= asset('js/list-filters.js') ?>"></script>
<script src="<?= asset('js/tables.js') ?>"></script>
<?php if (str_contains($_SERVER['PHP_SELF'] ?? '', 'contact.php')): ?>
<script src="<?= asset('js/contact.js') ?>"></script>
<?php endif; ?>
</body>
</html>
