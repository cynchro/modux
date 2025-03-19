let idForm = null;

if (!authToken) {
  alert("Token de autenticación no encontrado.");
}

// Función para actualizar el formulario con los datos del material
function actualizarFormulario(objetos_enmarcar) {
  $("#id").val(objetos_enmarcar.id);
  $("#nombre").val(objetos_enmarcar.nombre);
  $("#extra_fijo").val(objetos_enmarcar.precio);
  $("#extra_porcentual").val(objetos_enmarcar.id_objetos_enmarcar);


}

// Función para crear o editar Material
$("#createEditObjetosEnmarcarForm").submit(function (e) {
  e.preventDefault();

  const nombre = $("#nombre").val();
  const extra_fijo = $("#extra_fijo").val();
  const extra_porcentual = $("#extra_porcentual").val();
  const id_sucursal = $("#id_sucursal").val();
  const idForm = $("#id").val();

  if (idForm) {
    actualizarObjetosEnmarcar(idForm, { nombre, extra_fijo, extra_porcentual, id_sucursal });
  } else {
    crearObjetosEnmarcar({ nombre, extra_fijo, extra_porcentual, id_sucursal });
  }
});

// Función para crear nueva Material (POST)
async function crearObjetosEnmarcar(data) {
  try {
    // Limpiar errores previos
    document.querySelectorAll(".form-text.text-danger").forEach(el => el.textContent = "");

    const response = await fetch(`${API_URL}objetos_enmarcar`, {
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
        window.location.href = "objetosEnmarcar.php";
      }
    } else {
      alert("Error al crear el objeto a enmarcar.");
    }
  } catch (error) {
    handleError("Error al crear el objeto a enmarcar", error);
  }
}

async function actualizarObjetosEnmarcar(id, data) {
  try {
    // Limpiar errores previos
    document.querySelectorAll(".form-text.text-danger").forEach(el => el.textContent = "");

    const response = await fetch(`${API_URL}objetos_enmarcar/${id}`, {
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
        window.location.href = "objetosEnmarcar.php";
      }
    } else {
      alert("Error al actualizar el objeto a enmarcar.");
    }
  } catch (error) {
    handleError("Error al actualizar el objeto a enmarcar", error);
  }
}


// Función de manejo de errores
function handleError(message, error) {
  console.error(message, error);
  alert(`Ocurrió un error: ${message}`);
}

