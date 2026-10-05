<?php require FARMAVIDA_ROOT . '/app/Views/layouts/header.php'; ?>

<div class="container text-center mt-5 fade-in">
    <div class="card shadow-lg mx-auto" style="max-width: 600px;">
        <div class="card-body p-5">
            <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 5rem;"></i>
            <h1 class="display-4 mt-4">404</h1>
            <h3 class="mb-4">Página não encontrada</h3>
            <p class="text-muted mb-4">
                A página que você está procurando não existe ou foi movida.
            </p>
            
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <a href="dashboard.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-house-fill"></i> Voltar ao Dashboard
                </a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-box-arrow-in-right"></i> Ir para o Login
                </a>
            <?php endif; ?>
            
        </div>
    </div>
</div>

<?php 
// 4. Inclui o rodapé
require FARMAVIDA_ROOT . '/app/Views/layouts/footer.php'; 
?>