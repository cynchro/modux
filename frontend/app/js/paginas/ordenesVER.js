let id = null;


$(document).ready(function () {
    cargarTablaMateriales('tablaMolduras',1);
    cargarTablaMateriales('tablaVidrios',2);
    cargarTablaMateriales('tablaPaspartout',4);
    cargarTablaOtros('tablaOtros',4);
    
    $("#btnGuardar").click(function () {
        guardarOrden();
    });

});

function guardarOrden() {
    
    const datos = {
        "id_presupuesto": $('#idPresupuesto').val(),
        "fecha_entrega": $('#fecha').val(),
        "comentarios": $("#comentarios").val()
    };

    $.ajax({
        url: API_URL + 'orden/actualizar',
        type: 'POST',
        headers: {
            "Authorization": `Bearer ${authToken}`,
        },
        data: JSON.stringify(datos),
        contentType: 'application/json',
        success: function (response) {
            window.location.href = "ordenes.php";
        },  
        error: function (error) {
            handleError('Error al guardar la orden:', error);
        }
    });
}


function cargarTablaOtros(){
            var table = $('#tablaOtros').DataTable({
              "serverSide": true,
              "processing": true,
              "ordering": false,
              searching: false, //Oculto el buscador de la grilla,
              dom: 'rt<"bottom"><"clear">',
              "ajax": {
                  "url": API_URL+"presupuestos_extras",
                  "type": "GET",
                  "headers": {
                      "Authorization": `Bearer ${authToken}`,
                  },
                  "data": function (d) {
                      // Recoger los valores de los filtros
                      const filters = {};
                      filters.id_presupuesto = $('#idPresupuesto').val();
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
                  { "data": "descripcion",
                    render: function (data, type, row, meta) {
                      // Retorna un textbox con el valor correspondiente
                      return `<input type="text" class="form-control valor-input-otros descripcionOtros"  
                              value="${data}" data-id="${row.id}" disabled/>`;
                    }
                  },
                  { "data": "cantidad" ,
                      render: function (data, type, row, meta) {
                          // Retorna un textbox con el valor correspondiente
                          return `<input type="text" class="form-control valor-input-otros cantidad " 
                                  value="${data}" data-id="${row.id}" disabled/>`;
                        }
                  },
                  { "data": "precio_unitario" ,
                    render: function (data, type, row, meta) {
                        // Retorna un textbox con el valor correspondiente
                        return `<input type="text" class="form-control valor-input-otros precioUnitario" 
                                value="${data}" data-id="${row.id}" disabled/>`;
                      }
                }
              ],
              createdRow: function (row, data, dataIndex) {
                  // Agregar el atributo data-id con el ID del JSON
                  $(row).attr('data-id', data.id);
                },
              "responsive": true,
              "searching": false,
              "language": {
                  "url": "es-ES.json"
              },
              "paging": true,
              "pageLength": 10,
              "lengthMenu": [10, 25, 50, 100]
          });


}


function cargarTablaMateriales(tabla,idTipoMaterial){
    var table = $('#'+tabla).DataTable({
        "serverSide": true,
        "processing": true,
        "ordering": false,
        searching: false, //Oculto el buscador de la grilla,
        dom: 'rt<"bottom"><"clear">',
        "ajax": {
            "url": API_URL+"presupuestos_detalle",
            "type": "GET",
            "headers": {
                "Authorization": `Bearer ${authToken}`,
            },
            "data": function (d) {
                // Recoger los valores de los filtros
                const filters = {};
                 filters.id_tipo_material = idTipoMaterial;
                 filters.id_presupuesto = $('#idPresupuesto').val();
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
            { "data": "nombre_material" },
            { "data": "observaciones" ,
                render: function (data, type, row, meta) {
                    // Retorna un textbox con el valor correspondiente
                    return `<input type="text" class="form-control valor-input" 
                            value="${data}" data-id="${row.id}" disabled/>`;
                  }
            }
        ],
        createdRow: function (row, data, dataIndex) {
            // Agregar el atributo data-id con el ID del JSON
            $(row).attr('data-id', data.id);
          },
        "responsive": true,
        "searching": false,
        "language": {
            "url": "es-ES.json"
        },
        "paging": true,
        "pageLength": 10,
        "lengthMenu": [10, 25, 50, 100]
    });

   }

   // Función para eliminar
   async function anular(id) {
    const confirm = await showConfirmCancelModal(); // Espera la decisión del usuario

    if (confirm) {
        try {
            const response = await fetch(`${API_URL}orden/status/${id}`, {
                method: 'PUT', // Cambiado a PUT    
                headers: {
                    'Authorization': `Bearer ${authToken}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id_estado: 4 }),
            });

            if (response.ok) {
                swal('Actualizado', 'El registro ha sido actualizado.', 'success');
                window.location.href = "ordenes.php";
                //$('#Listado').DataTable().ajax.reload();
            } else {
                const errorData = await response.json();
                swal('Error', `No se pudo actualizar el registro: ${errorData.message || 'Error desconocido'}`, 'error');
            }
        } catch (error) {
            swal('Error', 'Hubo un problema al procesar la solicitud.', 'error');
        }
    }
}


// Función para obtener los datos de una sucursal