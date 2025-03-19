let idForm = null;

if (!authToken) {
  alert("Token de autenticación no encontrado.");
} 

function actualizarFormulario(cliente) {
  $("#id").val(cliente.id);
  $("#nombre").val(cliente.nombre);
  $("#domicilio").val(cliente.domicilio);
  $("#telefono").val(cliente.telefono);
  $("#email").val(cliente.email);
  $("#documento").val(cliente.documento);

  // Actualizar los selectbox con los valores del cliente
  ivaSelectbox(cliente.ivas || [], cliente.id_condicion_iva);
  documentosSelectbox(cliente.documentos || [], cliente.id_tipo_documento);
  localidadesSelectbox(cliente.localidades || [], cliente.id_localidad);
}


// Función para crear o editar sucursal
$("#createEditSucursalForm").submit(function (e) {
  e.preventDefault();

  const nombre = $("#nombre").val();
  const domicilio = $("#domicilio").val();
  const telefono = $("#telefono").val();
  const email = $("#email").val();
  const id_localidad = $("#localidades").val();
  const id_tipo_documento = $("#tipo_documento").val();
  const documento = $("#documento").val();
  const id_condicion_iva = $("#iva").val();
  const descuento = $("#descuento").val();
  const idForm = $("#id").val();

  if (idForm) {
    actualizarCliente(idForm, { nombre, domicilio, telefono, email, id_localidad, id_tipo_documento, documento, id_condicion_iva , descuento});
  } else {
    crearCliente({ nombre, domicilio, telefono, email, id_localidad, id_tipo_documento, documento, id_condicion_iva, descuento });
  }
});

// Función para crear nueva cliente (POST)
async function crearCliente(data) {
  try {
    // Limpiar errores previos
    document.querySelectorAll(".form-text.text-danger").forEach(el => el.textContent = "");

    const response = await fetch(`${API_URL}clientes`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${authToken}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
    });

    if (!response.ok) throw new Error("Error al contactar con el servidor.");

    const result = await response.json();

    if (result.success) {
      const responseData = result.response;

      // Verificar si hay datos en "required"
      if (responseData.required) {
        Object.entries(responseData.required).forEach(([field, messages]) => {
          const errorElement = document.getElementById(`error-${field}`);
          if (errorElement) {
            errorElement.textContent = messages.join(", ");
          }
        });
      } else {
        //alert("Cliente creado exitosamente.");
        window.location.href = "clientes.php";
      }
    } else {
      alert("Error al crear el cliente.");
    }
  } catch (error) {
    handleError("Error al crear el cliente", error);
  }
}

async function actualizarCliente(id, data) {
  try {
    // Limpiar errores previos
    document.querySelectorAll(".form-text.text-danger").forEach(el => el.textContent = "");

    const response = await fetch(`${API_URL}clientes/${id}`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${authToken}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
    });

    if (!response.ok) throw new Error("Error al contactar con el servidor.");

    const result = await response.json();

    if (result.success) {
      const responseData = result.response;

      // Verificar si hay datos en "required"
      if (responseData.required) {
        Object.entries(responseData.required).forEach(([field, messages]) => {
          const errorElement = document.getElementById(`error-${field}`);
          if (errorElement) {
            errorElement.textContent = messages.join(", ");
          }
        });
      } else {
        //alert("Sucursal actualizada exitosamente.");
        window.location.href = "clientes.php";
      }
    } else {
      alert("Error al actualizar la sucursal.");
    }
  } catch (error) {
    handleError("Error al actualizar la sucursal", error);
  }
}


// Función de manejo de errores
function handleError(message, error) {
  console.error(message, error);
  alert(`Ocurrió un error: ${message}`);
}

