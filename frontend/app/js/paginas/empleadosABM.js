let idForm = null;

if (!authToken) {
  alert("Token de autenticación no encontrado.");
}

function actualizarFormulario(empleado) {
  $("#id").val(empleado.id);
  $("#nombre").val(empleado.nombre);
  $("#telefono").val(empleado.telefono);
  $("#email").val(empleado.email);
  $("#sucursal").val(empleado.sucursal);
  $("#nombre_usuario").val(empleado.nombre_usuario);
  $("#clave").val(empleado.clave);
}


// Función para crear o editar sucursal
$("#createEditEmpleadosForm").submit(function (e) {
  e.preventDefault();

  const nombre = $("#nombre").val();
  const telefono = $("#telefono").val();
  const email = $("#email").val();
  const id_sucursal = $("#sucursal").val();
  const usuario = $("#usuario").val();
  const clave = $("#clave").val();
  const rol = $("#rol").val();
  const idForm = $("#id").val();

  if (idForm) {
    actualizarEmpleado(idForm, { nombre, telefono, email, id_sucursal, usuario, clave, rol });
  } else {
    crearEmpleado({ nombre, telefono, email, id_sucursal, usuario, clave, rol });
  }
});

// Función para crear nueva cliente (POST)
async function crearEmpleado(data) {
  try {
    // Limpiar errores previos
    document.querySelectorAll(".form-text.text-danger").forEach(el => el.textContent = "");

    const response = await fetch(`${API_URL}empleados`, {
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
        window.location.href = "empleados.php";
      }
    } else {
      alert("Error al crear un empleado.");
    }
  } catch (error) {
    handleError("Error al crear un empleado", error);
  }
}

async function actualizarEmpleado(id, data) {
  try {
    // Limpiar errores previos
    document.querySelectorAll(".form-text.text-danger").forEach(el => el.textContent = "");

    const response = await fetch(`${API_URL}empleados/${id}`, {
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
        //alert("Empleado actualizada exitosamente.");
        window.location.href = "empleados.php";
      }
    } else {
      alert("Error al actualizar el empleado.");
    }
  } catch (error) {
    handleError("Error al actualizar el empleado", error);
  }
}


// Función de manejo de errores
function handleError(message, error) {
  console.error(message, error);
  alert(`Ocurrió un error: ${message}`);
}

// Función para obtener cookies
function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(';').shift();
  console.warn(`Cookie "${name}" no encontrada.`);
  return null;
}

