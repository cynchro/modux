$(document).ready(function () {
    // Inicializar DataTable
    const tablaDetalle = $('#tablaDetalle').DataTable({
      "responsive": true,
      "searching": false,
      "language": {
          "url": "es-ES.json"
      },
      "paging": false,
      dom: 'rt<"bottom"><"clear">',
        columns: [
            { title: 'ID', visible: false }, // Ocultar la columna ID
            { title: 'Forma de Pago' },
            { title: 'Observaciones', render: function(data, type, row, meta) {
                return '<input type="text" class="observaciones" value="' + (data || '') + '" style="width:100%"  />';
            }},
            { title: 'Monto', render: function(data, type, row, meta) {
                return '<input type="number" class="monto" value="' + (data || '') + '" step="0.01" style="width:100%" />';
            }}
        ]
    });

    $('#tablaDetalle').on('input', '.monto', function () {
      calcularTotal(); // Recalcular el total cada vez que se modifica un monto
  });


    // Guardar datos
    $('#btnGuardar').click( async function() {
        const datos = [];

        // Iterar sobre las filas del DataTable
        tablaDetalle.rows().every(function() {
            const data = this.data();
            const row = $(this.node());

            datos.push({
                id_forma_de_pago: data[0],
                forma_de_pago: data[1],
                observaciones: row.find('.observaciones').val(),
                monto: row.find('.monto').val()
            });
        });
      datosAEnviar = {id_orden_de_trabajo: $('#idPresupuesto').val(), 
                      total: $('#totalRecibo').val(), 
                      cliente_nombre: $('#cliente_nombre').val(),
                      cliente_email: $('#cliente_email').val(),
                      cliente_domicilio: $('#cliente_domicilio').val(),
                      cliente_telefono: $('#cliente_telefono').val(),
                      detalle: datos

      };
      const response = await fetch(`${API_URL}recibos`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(datosAEnviar),
      });
      if (!response.ok) throw new Error("Error al contactar con el servidor.");
      const result = await response.json();
      if (result.success) {
        const responseData = result.response;
        if (responseData.required) {
          Object.entries(responseData.required).forEach(([field, messages]) => {
            const errorElement = document.getElementById(`error-${field}`);
            if (errorElement) {
              errorElement.textContent = messages.join(", ");
            }
          });
        } else {
          alert("Recibo creado exitosamente.");
          window.location.href = "recibos.php";
        }
      } else {
        alert("Error al crear el recibo.");
      }
     
    });


    var tablaBuscadorFormaDePago = $('#tablaFormaDePago').DataTable({
      "serverSide": true,
      "processing": true,
      "ordering": false,
      searching: false, //Oculto el buscador de la grilla,
      dom: 'rt<"bottom"><"clear">',
      "ajax": {
          "url": API_URL+"forma_de_pago",
          "type": "GET",
          "headers": {
              "Authorization": `Bearer ${authToken}`,
          },
          "data": function (d) {
              // Recoger los valores de los filtros
  
  
              const filters = {};
              return {
                  page: (d.start / d.length) + 1,
                  perPage: d.length,
                  ...filters
              };
          },
          "dataSrc": function (json) {
              if (json.success) {
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
          {
            "className": "text-center",
            data: function(data, type, full, meta) {
              console.log(data);
                     return `
                      <button class="btn btn-primary btn-sm" onclick="seleccionarFormaDePago('${data['id']}','${data['nombre']}')">Seleccionar</button>
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






});

    // Función para calcular el total
    function calcularTotal() {
      let total = 0;
      $('.monto').each(function () {
          const valor = parseFloat($(this).val()) || 0; // Convertir el valor a número, por defecto 0
          total += valor;
      });
      $('#totalRecibo').val(total.toFixed(2)); // Mostrar el total con dos decimales
  }

    // Función para agregar una fila
    function seleccionarFormaDePago(id, formaDePago) {
      $('#tablaDetalle').DataTable().row.add([id, formaDePago, '', '']).draw();
    }


    // Eventos para los botones
    $('#btnTarjeta').click(() => seleccionarFormaDePago(1, 'Tarjeta'));
    $('#btnEfectivo').click(() => seleccionarFormaDePago(2, 'Efectivo'));
    $('#btnTransferencia').click(() => seleccionarFormaDePago(3, 'Transferencia'));

// Función para obtener los datos de una sucursal