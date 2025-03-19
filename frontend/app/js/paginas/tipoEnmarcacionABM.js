let idForm = null;

if (!authToken) {
  alert("Token de autenticación no encontrado.");
}

// Función para actualizar el formulario con los datos del material
function actualizarFormulario(tipo_enmarcacion) {
  $("#id").val(tipo_enmarcacion.id);
  $("#nombre").val(tipo_enmarcacion.nombre);
  $("#precio").val(tipo_enmarcacion.precio);
  $("#id_tipo_enmarcacion").val(tipo_enmarcacion.id_tipo_enmarcacion);
}

// Función para crear o editar Material
$("#createEditMaterialForm").submit(function (e) {
  e.preventDefault();

  const nombre = $("#nombre").val();
  const comisionFija = $("#comision_fija").val();
  const comisionPorcentual = $("#comision_porcentual").val();
  const id_sucursal = $("#id_sucursal").val();
  const idForm = $("#id").val();

  if (idForm) {
    actualizarTipoEnmarcacion(idForm, { nombre, comisionFija, comisionPorcentual, id_sucursal });
  } else {
    crearTipoEnmarcacion({ nombre, comisionFija, comisionPorcentual, id_sucursal });
  }
});

// Función para crear nueva Material (POST)
async function crearTipoEnmarcacion(data) {
  try {
    // Limpiar errores previos
    document.querySelectorAll(".form-text.text-danger").forEach(el => el.textContent = "");

    const response = await fetch(`${API_URL}tipo_enmarcacion`, {
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
        //alert("Material creado exitosamente.");
        window.location.href = "tipoEnmarcacion.php";
      }
    } else {
      alert("Error al crear el tipo de enmarcación.");
    }
  } catch (error) {
    handleError("Error al crear el tipo de enmarcación", error);
  }
}

async function actualizarTipoEnmarcacion(id, data) {
  try {
    // Limpiar errores previos
    document.querySelectorAll(".form-text.text-danger").forEach(el => el.textContent = "");

    const response = await fetch(`${API_URL}tipo_enmarcacion/${id}`, {
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
        //alert("Material actualizado exitosamente.");
        window.location.href = "tipoEnmarcacion.php";
      }
    } else {
      alert("Error al actualizar el tipo de enmarcación.");
    }
  } catch (error) {
    handleError("Error al actualizar el tipo de enmarcación", error);
  }
}


// Función de manejo de errores
function handleError(message, error) {
  console.error(message, error);
  alert(`Ocurrió un error: ${message}`);
}

