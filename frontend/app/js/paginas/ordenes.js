$(document).ready(function () {
    var table = $('#myTable').DataTable({
        "serverSide": true,
        "processing": true,
        "ordering": false,
        "ajax": {
            "url": API_URL + 'orden',
            "type": "GET",
            "headers": {
                "Authorization": `Bearer ${authToken}`,
            },
            "data": function (d) {
                // Recoger los valores de los filtros
                const cliente = $('#clienteFiltro').val();
                const hasta = $('#fechaHastaFiltro').val();
                const desde = $('#fechaDesdeFiltro').val();
                const numero = $('#ordenFiltro').val();

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
            { "data": "numero_orden" },
            { "data": "fecha" },
            { "data": "fecha_entrega" },
            { "data": "cantidad" },
            { "data": "cliente_nombre" },
            { "data": "total" },
            { "data": "saldo" },
            { "data": "estado" },
            {
                "data": "id_presupuesto",
                "className": "text-center",
                "render": function (data) {
                    return `
                    <button class="btn btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Editar Orden" onclick="editar(${data})">
                       <svg class="icon icon-xs"><use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use></svg>
                    </button>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Generar PDF Cliente" onclick="printCliente(${data})">
                       <svg class="icon icon-xs"><use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-print"></use></svg>
                    </button>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Generar PDF Taller" onclick="printTaller(${data})">
                       <svg class="icon icon-xs"><use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-industry"></use></svg>
                    </button>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Cobrar" onclick="cobrar(${data})">
                       <svg class="icon icon-xs"><use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-dollar"></use></svg>
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
        $('#nombreFiltro').val('');
        $('#telefonoFiltro').val('');
        $('#domicilioFiltro').val('');

        // Recargar la tabla sin filtros aplicados
        table.ajax.reload();
    });

});

function editar(id) {
    window.location.href = `ordenesVER.php?id=${id}`;
}

function cobrar(id) {
    window.location.href = `reciboABM.php?id=${id}`;
}

async function printCliente(id) {
    try {
        const response = await fetch(`${API_URL}orden/pdf/cliente/${id}`, {
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
        a.download = `orden_cliente_${id}.pdf`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Error:', error);
    }
}

async function printTaller(id) {
    try {
        const response = await fetch(`${API_URL}orden/pdf/taller/${id}`, {
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
        a.download = `orden_taller_${id}.pdf`;
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
