import DataTable from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css'; // Estilos base de DT

document.addEventListener('DOMContentLoaded', () => {
    // Inicializar la DataTable en el selector correspondiente
    let table = new DataTable('#impactosTable', {
        responsive: true,
        lengthChange: false, 
        searching: false,   
        paging: false,       // Quitar paginación
        info: false,         // Quitar texto de "Mostrando X de Y"
        columnDefs: [
            { orderable: false, targets: 6 } // Quitar flechas de ordenación en la columna ACCIONES (indice 6)
        ],
        language: {
            "decimal": "",
            "emptyTable": "No hay datos disponibles en la tabla",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
            "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
            "infoFiltered": "(filtrado de _MAX_ entradas totales)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ entradas",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "No se encontraron registros coincidentes",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
            "aria": {
                "sortAscending": ": activar para ordenar la columna ascendente",
                "sortDescending": ": activar para ordenar la columna descendente"
            }
        }
    });
});
