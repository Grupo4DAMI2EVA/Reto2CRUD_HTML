document.addEventListener("DOMContentLoaded", () => {
  document
    .getElementById("loginForm")
    .addEventListener("submit", async function (e) {
      e.preventDefault();

      const username = document.getElementById("username").value;
      const password = document.getElementById("password").value;

      let data = await login(username, password);

      if (data) {
        if (data["error"]) {
          alert("El nombre de usuario o la contraseña con incorrectas.");
        } else {
          if (data["result"]) {
            window.location.href = "main.html";
          }
        }
      } else {
        console.log("Error al cargar JSON.");
      }
    });

  async function login(username, password) {
    const response = await fetch(`../../api/Login.php`, {
      method: "POST",
      credentials: "include", // Envía y recibe cookies
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ username, password }),
    });

    let data = await response.json();

    return data;
  }
});
