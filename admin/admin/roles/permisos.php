<script>
    function preguntar(event, id_permiso) {
        event.preventDefault();
        Swal.fire({
            title: 'Eliminar registro',
            text: '¿Desea eliminar este registro?',
            icon: 'question',
            showDenyButton: true,
            confirmButtonText: 'Eliminar',
            confirmButtonColor: '#a5161d',
            denyButtonColor: '#270a0a',
            denyButtonText: 'Cancelar',
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('miFormulario' + id_permiso).submit();
            }
        });
    }

    $(function () {
        $("#example1").DataTable({
            "pageLength": 5,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Permisos",
                "infoEmpty": "Mostrando 0 a 0 de 0 Permisos",
                "infoFiltered": "(Filtrado de _MAX_ total Permisos)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ Permisos",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscador:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "responsive": true, "lengthChange": true, "autoWidth": false,
            buttons: [{
                extend: 'collection',
                text: 'Reportes',
                orientation: 'landscape',
                buttons: ['copy', 'pdf', 'csv', 'excel', 'print']
            }, {
                extend: 'colvis',
                text: 'Visor de columnas',
                collectionLayout: 'fixed three-column'
            }],
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });
</script>