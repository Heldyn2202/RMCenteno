<?php
if( (isset($_SESSION['mensaje'])) && (isset($_SESSION['icono']) )){
    $mensaje = $_SESSION['mensaje'];
    $icono = $_SESSION['icono'];
    ?>
    <script>
        Swal.fire({
            position: "top-end",
            icon: "<?=$icono;?>",
            title: "<?=$mensaje;?>",
            showConfirmButton: false,
            timer: 5000
        });
    </script>
<?php
    unset($_SESSION['mensaje']);
    unset($_SESSION['icono']);
}
?>