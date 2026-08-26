<?php
// views/error/500.php
// Ubicación: C:\xampp\htdocs\proyecto\views\error\500.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$titulo = "Error 500 - Error interno del servidor";
$seccion = "error";
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="text-center py-5">
                <div class="display-1 text-muted">500</div>
                <h1 class="display-4">Error interno del servidor</h1>
                <p class="lead">Algo salió mal. Por favor, inténtalo de nuevo más tarde.</p>
                <a href="/proyecto/dashboard" class="btn btn-primary">
                    <i class="fas fa-home"></i> Volver al inicio
                </a>
            </div>
        </main>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>