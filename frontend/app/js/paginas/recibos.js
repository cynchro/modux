//const authToken = getCookie('auth_token');
//const API_URL = "https://cbx-test.com.ar/presupuestos";

$(document).ready(function () {
    var table = $('#Listado').DataTable({
        "serverSide": true,
        "processing": true,
        "ordering": false,
        "ajax": {
            "url": API_URL + 'recibos',
            "type": "GET",
            "headers": {
                "Authorization": `Bearer ${authToken}`,
            },
            "data": function (d) {
                // Recoger los valores de los filtros
                const cliente = $('#clienteFiltro').val();
                const hasta = $('#fechaHastaFiltro').val();
                const desde = $('#fechaDesdeFiltro').val();
                const orden = $('#ordenFiltro').val();

                const filters = {};
                if (cliente) filters.cliente = cliente;
                if (desde) filters.desde = desde;
                if (hasta) filters.hasta = hasta;
                if (orden) filters.orden = orden;

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
            { "data": "numero" },
            { "data": "fecha" },
            { "data": "numero_orden" },
            { "data": "cliente_nombre" },
            { "data": "total" },
            {
                "data": "id",
                "className": "text-center",
                "render": function (data) {
                    return `
                    <button class="btn btn-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Ealiminar Recibo" onclick="eliminar(${data})">
                       <svg class="icon icon-xs"><use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-trash"></use></svg>
                    </button>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Descargar Recibo" onclick="descargar(${data})">
                       <svg class="icon icon-xs"><use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-cloud-download"></use></svg>
                    </button>
                    `;
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
        $('#telefonoFiltro').val('');
        $('#domicilioFiltro').val('');

        // Recargar la tabla sin filtros aplicados
        table.ajax.reload();
    });

});




function editar(id) {
    window.location.href = `recibosABM.php?id=${id}`;
}

// Función para eliminar
async function eliminar(id) {
    const confirm = await showConfirmDeleteModal(); // Espera la decisión del usuario

    if (confirm) {
        try {
            const response = await fetch(`${API_URL}recibos/${id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${authToken}`,
                    'Content-Type': 'application/json',
                },
            });
            if (response.ok) {
                swal('Eliminado', 'El registro ha sido eliminado.', 'success');
                $('#Listado').DataTable().ajax.reload();
            } else {
                swal('Error', 'No se pudo eliminar el registro.', 'error');
            }
        } catch (error) {
            swal('Error', 'Hubo un problema al procesar la solicitud.', 'error');
        }
    }
}

async function descargar(id) {
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
    console.error(message, error);
    alert(message);
}

// Función para obtener cookies
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    console.warn(`Cookie "${name}" no encontrada.`);
    return null;
}
