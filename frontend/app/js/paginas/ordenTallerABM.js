let id = null;


$(document).ready(function () {
    cargarTablaMateriales('tablaMolduras',1);
    cargarTablaMateriales('tablaVidrios',2);
    cargarTablaMateriales('tablaPaspartout',4);
    cargarTablaOtros('tablaOtros',4);
    
  var table = $('#tablaBuscadortClientes').DataTable({
      "serverSide": true,
      "processing": true,
      "ordering": false,
      searching: false, //Oculto el buscador de la grilla,
      dom: 'rt<"bottom"><"clear">',
      "ajax": {
          "url": API_URL+"clientes",
          "type": "GET",
          "headers": {
              "Authorization": `Bearer ${authToken}`,
          },
          "data": function (d) {
              // Recoger los valores de los filtros
              const buscadorNombreCliente = $('#buscadorNombreCliente').val();

              const filters = {};
              if (buscadorNombreCliente) filters.nombre = buscadorNombreCliente;
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
          { "data": "documento" },
          {
              "data": "id",
              "className": "text-center",
              "render": function (data) {
                  return `
                      <button class="btn btn-primary btn-sm" onclick="seleccionarCliente(${data})">Seleccionar</button>
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

  var tablaBuscadorMaterial = $('#tablaBuscadorMaterial').DataTable({
    "serverSide": true,
    "processing": true,
    "ordering": false,
    searching: false, //Oculto el buscador de la grilla,
    dom: 'rt<"bottom"><"clear">',
    "ajax": {
        "url": API_URL+"materiales",
        "type": "GET",
        "headers": {
            "Authorization": `Bearer ${authToken}`,
        },
        "data": function (d) {
            // Recoger los valores de los filtros
            const buscadorNombreCliente = $('#buscadorNombreCliente').val();
            const buscadortipoMaterial = $('#buscadorIdTipoMaterial').val();


            const filters = {};
            if (buscadorNombreCliente) filters.nombre = buscadorNombreCliente;
            if (buscadortipoMaterial) filters.id_tipo_material = buscadortipoMaterial;
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
        { "data": "nombreMaterial" },
        {
            "data": "id",
            "className": "text-center",
            "render": function (data) {
                return `
                    <button class="btn btn-primary btn-sm" onclick="seleccionarMaterial(${data})">Seleccionar</button>
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

    $('#btnBuscarMoldura').click(function () {
        $('#buscadorIdTipoMaterial').val(1)
        tablaBuscadorMaterial.ajax.reload();
    });
    $('#btnBuscarVidrio').click(function () {
        $('#buscadorIdTipoMaterial').val(2)
        tablaBuscadorMaterial.ajax.reload();
    });
    $('#btnBuscarPaspartout').click(function () {
        $('#buscadorIdTipoMaterial').val(4)
        tablaBuscadorMaterial.ajax.reload();
    });
    $('#buscarCliente').click(function () {
        table.ajax.reload();
    });

});

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


  $('#tablaOtros').on('change', '.valor-input-otros', function () {
        const $input = $(this);
        const rowId = $input.closest('tr').attr('data-id'); // Obtener ID de la fila
        const rowData = table.row($input.closest('tr')).data(); // Datos completos de la fila

        // Actualizar el dato correspondiente en rowData
        const columnClass = $input.attr('class');
        if (columnClass.includes('descripcionOtros')) {
            rowData.descripcion = $input.val();
        } else if (columnClass.includes('cantidad')) {
            rowData.cantidad = $input.val();
        } else if (columnClass.includes('precioUnitario')) {
            rowData.precio_unitario = $input.val();
        }

        // Enviar los datos actualizados a la API
        guardarOtros(rowData);
  });
}

function guardarOtros(rowData) {
  $.ajax({
      url: API_URL + 'presupuestos_extras/' + rowData.id, // Endpoint para guardar
      method: 'PUT', // Cambiar según el método que acepte tu API
      contentType: 'application/json',
      data: JSON.stringify({
          descripcion: rowData.descripcion,
          cantidad: rowData.cantidad,
          precio_unitario: rowData.precio_unitario
      }),
      headers: {
          "Authorization": `Bearer ${authToken}`
      },
      success: function (response) {
          console.log('Fila guardada correctamente:', response);
      },
      error: function (error) {
          console.error('Error al guardar la fila:', error);
          alert('No se pudo guardar la fila en la API.');
      }
  });
}


function cargarTablaMateriales(tabla, idTipoMaterial) {

  if (tabla == 'tablaPaspartout') {
    var table = $('#' + tabla).DataTable({
      "serverSide": true,
      "processing": true,
      "ordering": false,
      searching: false, //Oculto el buscador de la grilla,
      dom: 'rt<"bottom"><"clear">',
      "ajax": {
        "url": API_URL + "presupuestos_detalle",
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
        {
          "data": "observaciones",
          render: function (data, type, row, meta) {
            // Retorna un textbox con el valor correspondiente
            return `<input type="text" class="form-control valor-input" 
                          value="${data}" data-id="${row.id}" disabled />`;
          }
        },
        {
          "data": "cm",
          render: function (data, type, row, meta) {
            // Retorna un textbox con el valor correspondiente
            return `<input type="text" class="form-control valor-cm" 
                        value="${data}" data-id="${row.id}" disabled/>`;
          }
        },
        { // Nueva columna para los radio buttons
          "data": "cs", // Se usa "cs" para decidir qué opción marcar
          "className": "text-center",
          "render": function (data, type, row) {
            let checked1 = data === "C" ? "checked" : "";
            let checked2 = data === "S" ? "checked" : "";

            return `
                <div class="form-check form-check-inline">
                  <input class="form-check-input valor-cs" type="radio" data-id="${row.id}" name="radio-${row.id}" id="radio1-${row.id}" value="C" ${checked1}>
                  <label class="form-check-label" for="radio1-${row.id}">C</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input valor-cs" type="radio" data-id="${row.id}" name="radio-${row.id}" id="radio2-${row.id}" value="S" ${checked2}>
                  <label class="form-check-label" for="radio2-${row.id}">S</label>
                </div>`;
          }
        },
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

    $('#' + tabla).on('blur', '.valor-cm', function () {
      const id = $(this).data('id'); // Obtiene el ID de la fila
      const valor = $(this).val();  // Obtiene el nuevo valor
      console.log(`ID: ${id}, Nuevo valor: ${valor}`);
      subirCm(id, valor);

    });

    $('#' + tabla).on('change', '.valor-cs', function () {
      const id = $(this).data('id'); // Obtiene el ID de la fila
      const valor = $(this).val();  // Obtiene el nuevo valor
      console.log(`ID: ${id}, Nuevo valor: ${valor}`);
      subirCs(id, valor);

    });

  } else {
    var table = $('#' + tabla).DataTable({
      "serverSide": true,
      "processing": true,
      "ordering": false,
      searching: false, //Oculto el buscador de la grilla,
      dom: 'rt<"bottom"><"clear">',
      "ajax": {
        "url": API_URL + "presupuestos_detalle",
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
        {
          "data": "observaciones",
          render: function (data, type, row, meta) {
            // Retorna un textbox con el valor correspondiente
            return `<input type="text" class="form-control valor-input" 
                            value="${data}" data-id="${row.id}" disabled/>`;
          }
        },
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
  const tab = document.getElementById(tabla).querySelector('tbody');
  Sortable.create(tab, {
    animation: 150, // Animación para arrastrar
    handle: 'tr',   // Especifica que las filas son reordenables
    onEnd: function (evt) {
      const orden = Array.from(tab.children).map(row => row.getAttribute('data-id'));
      console.log('Nuevo orden:', orden);
      posiciones(orden);
    },
  });

  $('#' + tabla).on('blur', '.valor-input', function () {
    const id = $(this).data('id'); // Obtiene el ID de la fila
    const valor = $(this).val();  // Obtiene el nuevo valor
    console.log(`ID: ${id}, Nuevo valor: ${valor}`);
    subirComentario(id, valor);

  });
}



async function eliminarMaterial(id){
    try {
        const response = await fetch(`${API_URL}presupuestos_detalle/`+ id, {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${authToken}`,
            'Content-Type': 'application/json',
          },
        
        });
    
        if (!response.ok) throw new Error(`Error: ${response.statusText}`);
    
        const data = await response.json();
        console.log (data);
        if (data.success) {
            $('#tablaMolduras').DataTable().ajax.reload();
            $('#tablaVidrios').DataTable().ajax.reload();
            $('#tablaPaspartout').DataTable().ajax.reload();
            $('#tablaOtros').DataTable().ajax.reload();

        } else {
          console.error('Error en la respuesta de la API');
        }
      } catch (error) {
        handleError("Error al obtener sucursales", error);
      }


}

async function eliminarOtros(id){
  try {
      const response = await fetch(`${API_URL}presupuestos_extras/`+ id, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Content-Type': 'application/json',
        },
      
      });
  
      if (!response.ok) throw new Error(`Error: ${response.statusText}`);
  
      const data = await response.json();
      console.log (data);
      if (data.success) {
          $('#tablaOtros').DataTable().ajax.reload();

      } else {
        console.error('Error en la respuesta de la API');
      }
    } catch (error) {
      handleError("Error al obtener sucursales", error);
    }


}

async function subirComentario(id, comentario){

    const datos = {
        id_presupuesto_detalle: id,
        observaciones: comentario
    };

    try {
        const response = await fetch(`${API_URL}presupuestos_detalle/observaciones`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${authToken}`,
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(datos)
        });
    
        if (!response.ok) throw new Error(`Error: ${response.statusText}`);
    
        const data = await response.json();
        console.log (data);
        if (data.success) {
            
       } else {
          console.error('Error en la respuesta de la API');
        }
      } catch (error) {
        handleError("Error al guardar observacion", error);
      }


}


async function subirExtra(id, descripcion, contidad, precio){

  const datos = {
      id: id,
      descripcion: descripcion,
      cantidad: contidad,
      precio_unitario: precio
    };

  try {
      const response = await fetch(`${API_URL}presupuestos_extra`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(datos)
      });
  
      if (!response.ok) throw new Error(`Error: ${response.statusText}`);
  
      const data = await response.json();
      console.log (data);
      if (data.success) {
          
     } else {
        console.error('Error en la respuesta de la API');
      }
    } catch (error) {
      handleError("Error al guardar observacion", error);
    }


}

async function posiciones(posiciones){

    const datos =  posiciones;

    try {
        const response = await fetch(`${API_URL}presupuestos_detalle/posiciones`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${authToken}`,
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(datos)
        });
    
        if (!response.ok) throw new Error(`Error: ${response.statusText}`);
    
        const data = await response.json();
        console.log (data);
        if (data.success) {
            
       } else {
          console.error('Error en la respuesta de la API');
        }
      } catch (error) {
        handleError("Error al guardar observacion", error);
      }


}


async function seleccionarCliente(id) {
    $('#cliente').val(id);
    // consultar WS de cliente y cargar los datos
    
        try {
          const response = await fetch(`${API_URL}clientes/${id}`, {
            method: 'GET',
            headers: {
              'Authorization': `Bearer ${authToken}`,
              'Content-Type': 'application/json',
            },
          });
      
          if (!response.ok) throw new Error(`Error: ${response.statusText}`);
      
          const data = await response.json();
      
          if (data.success) {
            $('#idCliente').val(id);
            $('#nombre').val(data.response.nombre);
            $('#telefono').val(data.response.telefono);
            $('#email').val(data.response.email);
            $('#domicilio').val(data.response.domicilio);
            $("#descuento").val(data.response.descuento);
          } else {
            console.error('Error en la respuesta de la API');
          }
        } catch (error) {
          handleError("Error al obtener sucursales", error);
        }
      

    $('#modalBuscadorClientes').modal('hide');
}

async function seleccionarMaterial(id){

    const datos = {
        id_presupuesto: $('#idPresupuesto').val(),
        id_material: id,
        posicion: 1,
        precio: 0,
        observaciones: "",
        id_sucursal: $('#sucursal').val()
    };

    try {
        const response = await fetch(`${API_URL}presupuestos_detalle`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${authToken}`,
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(datos)
        });
    
        if (!response.ok) throw new Error(`Error: ${response.statusText}`);
    
        const data = await response.json();
        console.log (data);
        if (data.success) {
            $('#idPresupuesto').val(data.response.datos.id_presupuesto);
            $('#tablaMolduras').DataTable().ajax.reload();
            $('#tablaVidrios').DataTable().ajax.reload();
            $('#tablaPaspartout').DataTable().ajax.reload();
            //$('#tablaOtros').DataTable().ajax.reload();
            
        } else {
          console.error('Error en la respuesta de la API');
        }
      } catch (error) {
        handleError("Error al obtener sucursales", error);
      }



}

async function AgregarOtros(){

  const datos = {
      id_presupuesto: $('#idPresupuesto').val(),
      observaciones: "",
      precio: 0,
      cantidad: 1
  };

  try {
      const response = await fetch(`${API_URL}presupuestos_extras`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(datos)
      });
  
      if (!response.ok) throw new Error(`Error: ${response.statusText}`);
  
      const data = await response.json();
      console.log (data);
      if (data.success) {
          $('#idPresupuesto').val(data.response.datos.id_presupuesto);
          $('#tablaOtros').DataTable().ajax.reload();
      } else {
        console.error('Error en la respuesta de la API');
      }
    } catch (error) {
      handleError("Error al obtener sucursales", error);
    }
}

async function actualizar() {
  var comentarios = $('#comentarios_taller').val();
  var id_estado = $('#estado').val();
  var id = $('#idPresupuesto').val(); 

  try {
      let response = await fetch(`${API_URL}orden/status/${id}`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${authToken}`, },
          body: JSON.stringify({ id, comentarios, id_estado })
      });

      let result = await response.json();
      //console.log(result);
      if (result.success) {
        //showSuccessModal('Actualizado!', 'Se actualizó correctamente.')
          alert('Actualización exitosa');
      } else {
          alert('Error en la actualización');
      }
  } catch (error) {
      console.error('Error en la petición:', error);
      alert('Ocurrió un error');
  }
}



async function guardarPresupuesto() {

    const datos = {
            "id": $('#idPresupuesto').val(),
            "id_cliente": $('#idCliente').val(),
            "id_tipo_enmarcacion": $("#tipoEnmarcacion").val(),
            "comentarios": "esto es una prueba",
            "cliente_nombre": $('#nombre').val(),
            "cliente_telefono": $('#telefono').val(),
            "cliente_email": $('#email').val(),
            "cliente_domicilio": $('#domicilio').val(),
            "alto": $("#alto").val(), 
            "ancho": $("#ancho").val(),
            "id_objeto_a_enmarcar": $("#tipoObjeto").val(),
            "modelo": $("#modelo").val(),
            "propio": $('#propio').prop('checked') ? 1 : 0,
            "descuento":$("#descuento").val(),
        
    };

    try {
        const response = await fetch(`${API_URL}presupuestos`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${authToken}`,
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(datos)
        });
    
        if (!response.ok) throw new Error(`Error: ${response.statusText}`);
    
        const data = await response.json();
        console.log (data);
        if (data.success) {
            $('#subtotal').val(data.response.datos.sub_total);
            $('#total').val(data.response.datos.total);

        } else {
          console.error('Error en la respuesta de la API');
        }
      } catch (error) {
        handleError("Error al obtener sucursales", error);
      }

}

// Función para obtener los datos de una sucursal