</main>
<?php if (isset($_SESSION['usuario_id']) && !isset($hide_navbar)): ?>
<footer class="footer mt-5 pb-3">
    <div class="container text-center">
        <p class="mb-0 text-muted">&copy; <?= date('Y') ?> Farmácia Vida Saudável - Todos os direitos reservados</p>
    </div>
</footer>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?= isset($extra_js) ? $extra_js : '' ?>
</body>
</html>
