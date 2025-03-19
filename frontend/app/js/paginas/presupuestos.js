//const authToken = getCookie('auth_token');
//const API_URL = "https://cbx-test.com.ar/presupuestos";

$(document).ready(function () {
    var table = $('#myTable').DataTable({
        "serverSide": true,
        "processing": true,
        "ordering": false,
        "ajax": {
            "url": API_URL + 'presupuestos',
            "type": "GET",
            "headers": {
                "Authorization": `Bearer ${authToken}`,
            },
            "data": function (d) {
                // Recoger los valores de los filtros
                const cliente = $('#clienteFiltro').val();
                const hasta = $('#fechaHastaFiltro').val();
                const desde = $('#fechaDesdeFiltro').val();
                const numero = $('#numeroFiltro').val();

                const filters = {};
                if (cliente) filters.cliente = cliente;
                if (desde) filters.desde = desde;
                if (hasta) filters.hasta = hasta;
                if (numero) filters.numero = numero;

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
            { "data": "numero_presupuesto" },
            { "data": "fecha" },
            { "data": "nombre_cliente" },
            { "data": "total" },
            { "data": "creado_por" },
            {
                "data": "id",
                "className": "text-center",
                "render": function (data, type, row) {
                    iconos = `<button class="btn btn-info btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Generar PDF" onclick="descargar(${data})"> 
                                <svg class="icon icon-xs"><use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-cloud-download"></use></svg>
                            </button> `;
                    if (row.id_estado == 1) {
                        iconos += `
                                                <button class="btn btn-warning btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Editar Presupuesto" onclick="editar(${data})">
                                                <svg class="icon icon-xs"><use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-pencil"></use></svg>
                                                </button>`;
                    }
                    if (row.id_estado == 1 || row.id_estado == 2) {
                        iconos += `
                                                <button class="btn btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Generar Orden" onclick="generarOrden(${data})">
                                                <svg class="icon icon-xs"><use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-check"></use></svg>
                                                </button>`};
                    return iconos;

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


async function generarOrden(id) {
    window.location.href = "ordenesABM.php?id=" + id;

}


async function descargar(id) {

    try {
        const response = await fetch(`${API_URL}presupuestos/pdf/${id}`, {
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
        a.download = `presupuesto_${id}.pdf`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        $('#myTable').DataTable().ajax.reload();
    } catch (error) {
        console.error('Error:', error);
    }

}


function editar(id) {
    window.location.href = `presupuestosABM.php?id=${id}`;
}

// Función para eliminar
async function eliminar(id) {
    if (confirm(`¿Estás seguro de que deseas eliminar la presupuesto Nº ${id}?`)) {
        try {
            const response = await fetch(`${API_URL}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${authToken}`,
                    'Content-Type': 'application/json',
                },
            });

            if (response.ok) {
                alert('Presupuesto eliminado correctamente');
                // Recargar la tabla después de eliminar
                $('#myTable').DataTable().ajax.reload();
            } else {
                handleError('Error al eliminar elPresupuesto');
            }
        } catch (error) {
            handleError('Error al contactar con el servidor', error);
        }
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
