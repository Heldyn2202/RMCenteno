<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-primary navbar-dark">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
            <li class="nav-item">
            <a class="nav-link" href="javascript:void(0)" onclick="confirmLogout()">
                <span>
                    <div class="d-felx badge-pill">
                        <span class="fa fa-power-off mr-2"></span>
                        Cerrar sesión
                    </div>
                </span>
            </a>
        </li>
        </li>
    </ul>
</nav>
<!-- /.navbar -->

<script>
    function confirmLogout() {
        Swal.fire({
            title: '¿Cerrar sesión?',
            text: '¿Estás seguro de que deseas salir?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, salir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'ajax.php?action=logout';
            }
        });
    }
</script>