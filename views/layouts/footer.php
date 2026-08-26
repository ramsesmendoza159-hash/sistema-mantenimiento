<?php
// views/layouts/footer.php
// Ubicación: C:\xampp\htdocs\proyecto\views\layouts\footer.php
?>

    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Font Awesome -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<!-- Scripts personalizados -->
<script>
    // Cerrar sidebar en móvil
    document.addEventListener('DOMContentLoaded', function() {
        var sidebarToggle = document.querySelector('[data-bs-toggle="collapse"]');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                var sidebar = document.querySelector('.sidebar');
                if (sidebar) {
                    sidebar.classList.toggle('show');
                }
            });
        }
    });

    // Auto-ocultar alertas después de 5 segundos
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        });
    }, 5000);
</script>

</body>
</html>