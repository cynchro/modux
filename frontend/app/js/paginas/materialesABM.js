let idForm = null;

if (!authToken) {
  alert("Token de autenticación no encontrado.");
}

// Función para actualizar el formulario con los datos del material
function actualizarFormulario(tipo_material) {
  $("#id").val(tipo_material.id);
  $("#nombre").val(tipo_material.nombre);
  $("#precio").val(tipo_material.precio);
  $("#id_tipo_material").val(tipo_material.id_tipo_material);
}

// Función para crear o editar Material
$("#createEditMaterialForm").submit(function (e) {
  e.preventDefault();

  const nombre = $("#nombre").val();
  const precio = $("#precio").val();
  const id_tipo_material = $("#id_tipo_material").val();
  const idForm = $("#id").val();

  if (idForm) {
    actualizarMaterial(idForm, { nombre, precio, id_tipo_material });
  } else {
    crearMaterial({ nombre, precio, id_tipo_material });
  }
});

// Función para crear nueva Material (POST)
async function crearMaterial(data) {
  try {
    // Limpiar errores previos
    document.querySelectorAll(".form-text.text-danger").forEach(el => el.textContent = "");

    const response = await fetch(`${API_URL}materiales`, {
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
        window.location.href = "materiales.php";
      }
    } else {
      alert("Error al crear el material.");
    }
  } catch (error) {
    handleError("Error al crear el material", error);
  }
}

async function actualizarMaterial(id, data) {
  try {
    // Limpiar errores previos
    document.querySelectorAll(".form-text.text-danger").forEach(el => el.textContent = "");

    const response = await fetch(`${API_URL}materiales/${id}`, {
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
        window.location.href = "materiales.php";
      }
    } else {
      alert("Error al actualizar el material.");
    }
  } catch (error) {
    handleError("Error al actualizar el material", error);
  }
}


// Función de manejo de errores
function handleError(message, error) {
  console.error(message, error);
  alert(`Ocurrió un error: ${message}`);
}

