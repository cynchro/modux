let idForm = null;

if (!authToken) {
  alert("Token de autenticación no encontrado.");
}

// Función para actualizar el formulario con los datos de la sucursal
function actualizarFormulario(sucursal) {
  $("#id").val(sucursal.id);
  $("#nombre").val(sucursal.nombre);
  $("#domicilio").val(sucursal.domicilio);
  $("#telefono").val(sucursal.telefono);
}

// Función para crear o editar sucursal
$("#createEditSucursalForm").submit(function (e) {
  e.preventDefault();

  const nombre = $("#nombre").val();
  const domicilio = $("#domicilio").val();
  const telefono = $("#telefono").val();
  const idForm = $("#id").val();

  if (idForm) {
    actualizarSucursal(idForm, { nombre, domicilio, telefono });
  } else {
    crearSucursal({ nombre, domicilio, telefono });
  }
});

// Función para crear nueva sucursal (POST)
async function crearSucursal(data) {
  try {
    // Limpiar errores previos
    document.querySelectorAll(".form-text.text-danger").forEach(el => el.textContent = "");

    const response = await fetch(`${API_URL}sucursales`, {
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
        // alert("Sucursal creada exitosamente.");
        window.location.href = "sucursales.php";
      }
    } else {
      alert("Error al crear la sucursal.");
    }
  } catch (error) {
    handleError("Error al crear la sucursal", error);
  }
}

async function actualizarSucursal(id, data) {
  try {
    // Limpiar errores previos
    document.querySelectorAll(".form-text.text-danger").forEach(el => el.textContent = "");

    const response = await fetch(`${API_URL}sucursales/${id}`, {
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
        // alert("Sucursal actualizada exitosamente.");
        window.location.href = "sucursales.php";
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

