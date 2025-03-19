// $(document).ready(function () {
//     var table = $('#myTable').DataTable({
//         "serverSide": true,
//         "processing": true,
//         "ordering": false,
//         "ajax": {
//             "url": API_URL+'caja_detalle',
//             "type": "GET",
//             "headers": {
//                 "Authorization": `Bearer ${authToken}`,
//             },
//             "data": function (d) {
//                 // Recoger los valores de los filtros
//                 const fecha = $('#FechaFiltro').val();

//                 const filters = {};
//                 if (fecha) filters.fecha = fecha;
                
//                 return {
//                     page: (d.start / d.length) + 1,
//                     perPage: d.length,
//                     ...filters
//                 }; 
//             },
//             "dataSrc": function (json) {
//                 if (json.success) {
//                     json.recordsTotal = json.response.cantidad_total;
//                     json.recordsFiltered = json.response.cantidad_total;
//                     return json.response.results;
//                 } else {
//                     return []; // En caso de error, retorna una lista vacía
//                 }
//             },
//             "error": function (xhr, error, thrown) {
//                 handleError('Error al cargar los datos de la tabla:', error);
//             }
            
//         },
//         "columns": [
//             { "data": "numero" },
//             { "data": "orden" },
//             { "data": "cliente" },
//             { "data": "efectivo" },
//             { "data": "tarjeta" },
//             { "data": "deposito" },
//             { "data": "total" },
//             {
//                 "data": "id",
//                 "className": "text-center",
//                 "render": function (data) {
//                     return `
//                         <button class="btn btn-primary btn-sm" onclick="ver(${data})">Ver</button>
                        
//                     `;
//                 }
//             }
//         ],
//         "responsive": true,
//         "searching": false,
//         "language": {
//             "url": "es-ES.json"
//         },
//         "paging": true,
//         "pageLength": 10,
//         "lengthMenu": [10, 25, 50, 100]
//     });

//     // Evento que se ejecuta cuando se hace clic en el botón "Filtrar"
//     $('#filtrarBtn').on('click', function () {
//         // Recarga la tabla con los filtros aplicados
//         table.ajax.reload();
//     });

   
// });

$(document).ready(function () {
    var table = $('#myTable').DataTable({
        "serverSide": true,
        "processing": true,
        "ordering": false,
        "ajax": {
            "url": API_URL + 'caja_detalle',
            "type": "GET",
            "headers": {
                "Authorization": `Bearer ${authToken}`,
            },
            "data": function (d) {
                const fecha = $('#FechaFiltro').val();
                const filters = {};
                if (fecha) filters.fecha = fecha;

                return {
                    page: (d.start / d.length) + 1,
                    perPage: d.length,
                    ...filters
                };
            },
            "dataSrc": function (json) {
                if (json.success) {
                    json.recordsTotal = json.response.cantidad_total;
                    json.recordsFiltered = json.response.cantidad_total;
                    return json.response.results;
                } else {
                    return [];
                }
            },
            "error": function (xhr, error, thrown) {
                handleError('Error al cargar los datos de la tabla:', error);
            }
        },
        "columns": [
            { "data": "numero" },
            { "data": "orden" },
            { "data": "cliente" },
            { "data": "efectivo" },
            { "data": "tarjeta" },
            { "data": "deposito" },
            { "data": "total" },
            {
                "data": "id",
                "className": "text-center",
                "render": function (data) {
                    return `<button class="btn btn-info btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Ver" onclick="ver(${data})"> 
                                <svg class="icon icon-xs"><use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use></svg>
                            </button>`;
                }
            }
        ],
        "responsive": true,
        "searching": false,
        "language": {
            "url": "es-ES.json"
        },
        "paging": true,
        "pageLength": 10,
        "lengthMenu": [10, 25, 50, 100],
        "footerCallback": function (row, data, start, end, display) {
            var api = this.api();

            // Función para obtener la suma de una columna específica
            var intVal = function (i) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '') * 1 :
                    typeof i === 'number' ? i : 0;
            };

            // Calcular totales
            var totalEfectivo = api.column(3, { page: 'current' }).data().reduce((a, b) => intVal(a) + intVal(b), 0);
            var totalTarjeta = api.column(4, { page: 'current' }).data().reduce((a, b) => intVal(a) + intVal(b), 0);
            var totalDeposito = api.column(5, { page: 'current' }).data().reduce((a, b) => intVal(a) + intVal(b), 0);
            var totalTotal = api.column(6, { page: 'current' }).data().reduce((a, b) => intVal(a) + intVal(b), 0);

            // Insertar valores en el footer
            $(api.column(3).footer()).html(totalEfectivo.toLocaleString());
            $(api.column(4).footer()).html(totalTarjeta.toLocaleString());
            $(api.column(5).footer()).html(totalDeposito.toLocaleString());
            $(api.column(6).footer()).html(totalTotal.toLocaleString());
        }
    });
    $('#myTable').on('draw.dt', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    $('#filtrarBtn').on('click', function () {
        table.ajax.reload();
    });

});

    function pdf() {
        let id = $('#sucursal').val();
        let fecha = $('#FechaFiltro').val();

        window.open(`${API_URL}caja/pdf/${id}?fecha=${fecha}`, '_blank');
    }

    async function ver(id) {

        try {
            const response = await fetch(`${API_URL}recibos/pdf/${id}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${authToken}`,
                    'Content-Type': 'application/json',
                },
            });
    
            if (!response.ok) {
                throw new Error('Error al descargar el archivo');
            }
    
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `recibo_${id}.pdf`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        } catch (error) {
            console.error('Error:', error);
        }
    }


// Función para manejar errores
function handleError(message, error = '') {
    //console.error(message, error);
    alert(message);
}
