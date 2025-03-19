$(document).ready(function () {
    var table = $('#myTable').DataTable({
        "serverSide": true,
        "processing": true,
        "ordering": false,
        "ajax": {
            "url": API_URL+'objetos_enmarcar',
            "type": "GET",
            "headers": {
                "Authorization": `Bearer ${authToken}`,
            },
            "data": function (d) {
                // Recoger los valores de los filtros
                const nombre = $('#nombreFiltro').val();
                const sucursal = $('#id_sucursal').val();

                const filters = {};
                if (nombre) filters.nombre = nombre;
                if (sucursal) filters.id_sucursal = sucursal;
                


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
                    return []; // En caso de error, retorna una lista vacía
                }
            },
            "error": function (xhr, error, thrown) {
                handleError('Error al cargar los datos de la tabla:', error);
            }
        },
        "columns": [
            { "data": "nombre" },
            { "data": "extra_fijo" },
            { "data": "extra_porcentual" },
            {
                "data": "id",
                "className": "text-center",
                "render": function (data) {
                    return `<button class="btn btn-warning btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Editar Objeto" onclick="editar(${data})">
                            <svg class="icon icon-xs"><use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-pencil"></use></svg>
                            </button>
                            <button class="btn btn-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar Objeto" onclick="eliminar(${data})">
                            <svg class="icon icon-xs"><use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-trash"></use></svg>
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
        "lengthMenu": [10, 25, 50, 100]
    });

    $('#myTable').on('draw.dt', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    // Evento que se ejecuta cuando se hace clic en el botón "Filtrar"
    $('#filtrarBtn').on('click', function () {
        // Recarga la tabla con los filtros aplicados
        table.ajax.reload();
    });

    // Evento que se ejecuta cuando se hace clic en el botón "Limpiar filtros"
    $('#limpiarFiltrosBtn').on('click', function () {
        // Limpiar los campos de filtro
        $('#nombreFiltro').val('');
        $('#telefonoFiltro').val('');
        $('#domicilioFiltro').val('');

        // Recargar la tabla sin filtros aplicados
        table.ajax.reload();
    });
});

function editar(id) {
    window.location.href = `objetosEnmarcarABM.php?id=${id}`;
}

// Función para eliminar
async function eliminar(id) {
    const confirm = await showConfirmDeleteModal(); // Espera la decisión del usuario

    if (confirm) {
        try {
            const response = await fetch(`${API_URL}objetos_enmarcar/${id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${authToken}`,
                    'Content-Type': 'application/json',
                },
            });
            if (response.ok) {
                swal('Eliminado', 'El registro ha sido eliminado.', 'success');
                $('#myTable').DataTable().ajax.reload();
            } else {
                swal('Error', 'No se pudo eliminar el registro.', 'error');
            }
        } catch (error) {
            swal('Error', 'Hubo un problema al procesar la solicitud.', 'error');
        }
    } 
}

// Función para manejar errores
function handleError(message, error = '') {
    console.error(message, error);
    alert(message);
}