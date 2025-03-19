//API_URL="https://cbx-test.com.ar/";
//API_URL="http://agarte:8080/";
API_URL="http://127.0.0.1:8080/";
const authToken = getCookie('auth_token');

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    console.warn(`Cookie "${name}" no encontrada.`);
    return null;
}

function handleError(message, error = '') {
    console.error(message, error);
    alert(message);
}

// async function cambioSucursal(sucursal) {
//     try {
//       // Limpiar errores previos
      
  
//       const response = await fetch(`${API_URL}usuarios/sucursal/${sucursal}`, {
//         method: 'PUT',
//         headers: {
//           'Authorization': `Bearer ${authToken}`,
//           'Content-Type': 'application/json',
//         },
        
//       });
  
//       if (!response.ok) throw new Error("Error al contactar con el servidor.");
  
//       const result = await response.json();
  
//       if (!result.success) {

//         alert("Error al actualizar la sucursal.");
//       }
//     } catch (error) {
//       handleError("Error al actualizar la sucursal", error);
//     }
//   }