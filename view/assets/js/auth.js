// Verifica la sesión del usuario en cada página

async function comprobarSesion() {
  try {
    const response = await fetch("../../api/me.php", {
      method: "GET",
      credentials: "include",
      headers: {
        "Content-Type": "application/json",
      },
    });

    if (response.status === 401) {
      // No hay sesión válida
      console.log("Sesión expirada o no válida");
      window.location.href = "login.html";
      return null;
    }

    if (response.ok) {
      const userData = await response.json();

      // Adaptar la nueva estructura devuelta por el servidor
      // Ejemplo:
      // {
      //   "0": { ...perfil... },
      //   "status": 200,
      //   "exito": true
      // }
      if (userData?.exito === false || userData?.status === 401) {
        window.location.href = "login.html";
        return null;
      }

      // Caso: el perfil viene dentro de la clave "0" o como primer elemento de un array
      if (userData && typeof userData === "object") {
        if ("0" in userData) {
          return userData["0"];
        }
        if (Array.isArray(userData)) {
          return userData[0];
        }
      }

      // Fallback: devolver lo recibido
      return userData;
    } else {
      console.error("Error al verificar sesión");
      window.location.href = "login.html";
      return null;
    }
  } catch (error) {
    console.error("Error en comprobarSesion:", error);
    window.location.href = "login.html";
    return null;
  }
}

async function logout() {
  try {
    const response = await fetch("../../api/logout.php", {
      method: "POST",
      credentials: "include",
      headers: {
        "Content-Type": "application/json",
      },
    });

    if (response.ok) {
      window.location.href = "login.html";
    }
  } catch (error) {
    console.error("Error al hacer logout:", error);
    window.location.href = "login.html";
  }
}

// Redirige a main si el usuario ya está logueado (para login y signup)
async function redirigirSiLogueado() {
  try {
    const response = await fetch("../../api/me.php", {
      method: "GET",
      credentials: "include",
      headers: {
        "Content-Type": "application/json",
      },
    });

    if (response.ok) {
      // Sesión válida, redirigir a main
      window.location.href = "main.html";
    }
  } catch (error) {
    console.error("Error al verificar sesión:", error);
  }
}
