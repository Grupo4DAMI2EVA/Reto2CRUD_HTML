document.addEventListener("DOMContentLoaded", async () => {
  // Verificar sesión y cargar datos del usuario
  const user = await comprobarSesion();
  if (!user) return;

  // Pintar nombre y saldo del usuario
  const nameSpan = document.getElementById("storeUserName");
  const balanceSpan = document.getElementById("storeUserBalance");
  const addGameBtn = document.getElementById("addGameBtn");

  if (nameSpan) {
    // Intentamos usar USER_NAME, si no existe usamos NAME_
    nameSpan.textContent = user.USER_NAME || user.NAME_ || "[User]";
  }

  if (balanceSpan) {
    const balance = user.CURRENT_ACCOUNT ?? 0;
    balanceSpan.textContent = `${Number(balance).toFixed(2)}€`;
  }

  addGameBtn.onclick = function () {
    window.location.href = "addGames.html";
  };

  // Cargar lista de videojuegos
  await loadVideogames();
});

async function get_all_videogames() {
  const response = await fetch("../../api/GetAllVideogames.php", {
    credentials: "include",
  });

  const data = await response.json();
  return data.resultado || [];
}

async function loadVideogames() {
  const tbody = document.querySelector(".storeTable tbody");
  const selectedGameSpan = document.getElementById("selectedGame");

  if (!tbody) return;

  // Limpiar cualquier fila anterior (incluida la "Tabla sin contenido")
  tbody.innerHTML = "";

  let videogames = [];

  try {
    videogames = await get_all_videogames();
  } catch (e) {
    console.error("Error obteniendo videojuegos:", e);
  }

  if (!videogames || videogames.length === 0) {
    const row = document.createElement("tr");
    row.classList.add("emptyRow");
    const cell = document.createElement("td");
    cell.colSpan = 8;
    cell.textContent = "No hay videojuegos disponibles";
    row.appendChild(cell);
    tbody.appendChild(row);
    return;
  }

  videogames.forEach((game) => {
    const row = document.createElement("tr");

    // Las claves vienen de la tabla VIDEOGAME_: PRICE, NAME_, PLATAFORM, GENRE, PEGI, STOCK, COMPANYNAME, RELEASE_DATE
    const {
      NAME_,
      GENRE,
      PLATAFORM,
      PRICE,
      PEGI,
      STOCK,
      COMPANYNAME,
      RELEASE_DATE,
    } = game;

    row.innerHTML = `
      <td>${NAME_ ?? ""}</td>
      <td>${GENRE ?? ""}</td>
      <td>${PLATAFORM ?? ""}</td>
      <td>${PRICE ?? ""}</td>
      <td>${PEGI ?? ""}</td>
      <td>${STOCK ?? ""}</td>
      <td>${COMPANYNAME ?? ""}</td>
      <td>${RELEASE_DATE ?? ""}</td>
    `;

    // Al hacer click en una fila, marcar juego seleccionado
    row.addEventListener("click", () => {
      if (selectedGameSpan) {
        selectedGameSpan.textContent = NAME_ || "Unknown";
      }
    });

    tbody.appendChild(row);
  });
}
