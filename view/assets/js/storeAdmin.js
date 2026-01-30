let ALL_VIDEOGAMES = [];
let selectedGame = null; // Guardar el juego seleccionado

document.addEventListener("DOMContentLoaded", async () => {
  // Verificar sesión y cargar datos del usuario
  const user = await comprobarSesion();
  if (!user) return;

  // Pintar nombre del usuario
  const nameSpan = document.getElementById("storeUserName");
  const addGameBtn = document.getElementById("addGameBtn");
  const modifyGameBtn = document.getElementById("modifyGameBtn");
  const delGameBtn = document.getElementById("delGameBtn");

  if (nameSpan) {
    // Intentamos usar USER_NAME, si no existe usamos NAME_
    nameSpan.textContent = user.USER_NAME || user.NAME_ || "[User]";
  }

  addGameBtn.onclick = function () {
    window.location.href = "addGames.html";
  };

  modifyGameBtn.onclick = function (e) {
    e.preventDefault();
    if (!selectedGame) {
      alert("Por favor, selecciona un videojuego para modificar");
      return false;
    }
    localStorage.setItem("selectedGame", JSON.stringify(selectedGame));
    window.location.href = "modifyGames.html";
  };

  delGameBtn.onclick = async function () {
    if (!selectedGame) {
      alert("Por favor, selecciona un videojuego para eliminar");
      return;
    }

    if (!confirm("¿Estás seguro de que deseas eliminar este videojuego?")) {
      return;
    }

    try {
      const response = await fetch(
        `../../api/DeleteGame.php?code=${selectedGame.VIDEOGAME_CODE}`,
        {
          method: "POST",
          credentials: "include",
          headers: {
            "Content-Type": "application/json",
          },
        },
      );

      let data = {};
      if (response.ok && response.status !== 204) {
        data = await response.json();
      }

      if (response.ok) {
        alert("Videojuego eliminado correctamente");
        selectedGame = null;
        document.getElementById("selectedGame").textContent = "Select a game";
        await loadVideogames();
      } else {
        alert(data.error || data.result || "Error al eliminar el videojuego");
      }
    } catch (error) {
      console.error("Error:", error);
      alert("Error al conectar con el servidor");
    }
  };

  // Cargar lista de videojuegos
  await loadVideogames();

  setupSearch();

  // Configurar modal de ayuda
  setupHelpModal();
});

async function get_all_videogames() {
  const response = await fetch("../../api/GetAllVideogames.php", {
    credentials: "include",
  });

  const data = await response.json();
  return data.result || [];
}

async function loadVideogames() {
  const tbody = document.querySelector(".storeTable tbody");

  if (!tbody) return;

  // Limpiar cualquier fila anterior (incluida la "Tabla sin contenido")
  tbody.innerHTML = "";

  try {
    ALL_VIDEOGAMES = await get_all_videogames();
  } catch (e) {
    console.error("Error obteniendo videojuegos:", e);
    ALL_VIDEOGAMES = [];
  }

  populateFilters(ALL_VIDEOGAMES);

  renderVideogames(ALL_VIDEOGAMES);
}

function renderVideogames(videogames) {
  const tbody = document.querySelector(".storeTable tbody");
  const selectedGameSpan = document.getElementById("selectedGame");

  if (!tbody) return;

  tbody.innerHTML = "";

  if (!videogames || videogames.length === 0) {
    const row = document.createElement("tr");
    row.classList.add("emptyRow");
    const cell = document.createElement("td");
    cell.colSpan = 8;
    cell.textContent = "No hay videojuegos disponibles";
    row.appendChild(cell);
    tbody.appendChild(row);
    if (selectedGameSpan) {
      selectedGameSpan.textContent = "Select a game";
    }
    return;
  }

  videogames.forEach((game) => {
    const row = document.createElement("tr");

    // Las claves vienen de la tabla VIDEOGAME_: PRICE, NAME_, PLATAFORM, GENRE, PEGI, STOCK, COMPANYNAME, RELEASE_DATE
    const {
      VIDEOGAME_CODE,
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
      selectedGame = game;
      if (selectedGameSpan) {
        selectedGameSpan.textContent = NAME_ || "Unknown";
      }
    });

    tbody.appendChild(row);
  });
}

function populateFilters(videogames) {
  const genreSelect = document.getElementById("searchGenre");
  const platformSelect = document.getElementById("searchPlatform");

  if (!genreSelect || !platformSelect) return;

  // Limpiar, dejando solo la opción "All"
  genreSelect.innerHTML = '<option value="all">All</option>';
  platformSelect.innerHTML = '<option value="all">All</option>';

  const genres = new Set();
  const platforms = new Set();

  videogames.forEach((game) => {
    if (game.GENRE) genres.add(game.GENRE);
    if (game.PLATAFORM) platforms.add(game.PLATAFORM);
  });

  genres.forEach((g) => {
    const opt = document.createElement("option");
    opt.value = g;
    opt.textContent = g;
    genreSelect.appendChild(opt);
  });

  platforms.forEach((p) => {
    const opt = document.createElement("option");
    opt.value = p;
    opt.textContent = p;
    platformSelect.appendChild(opt);
  });
}

function setupSearch() {
  const searchInput = document.getElementById("searchTitle");
  const genreSelect = document.getElementById("searchGenre");
  const platformSelect = document.getElementById("searchPlatform");

  if (!searchInput || !genreSelect || !platformSelect) {
    return;
  }

  const applyFilters = () => {
    const text = searchInput.value.trim().toLowerCase();
    const genre = genreSelect.value;
    const platform = platformSelect.value;

    const filtered = ALL_VIDEOGAMES.filter((game) => {
      const name = (game.NAME_ || "").toLowerCase();
      const matchesText = !text || name.includes(text);

      const matchesGenre = genre === "all" || game.GENRE === genre;
      const matchesPlatform = platform === "all" || game.PLATAFORM === platform;

      return matchesText && matchesGenre && matchesPlatform;
    });

    renderVideogames(filtered);
  };

  searchInput.addEventListener("input", applyFilters);
  genreSelect.addEventListener("change", applyFilters);
  platformSelect.addEventListener("change", applyFilters);
}
function setupHelpModal() {
  const helpBtn = document.getElementById("storeHelpBtn");
  const helpModal = document.getElementById("helpModal");
  const closeBtn = document.getElementById("helpModalClose");
  const okBtn = document.getElementById("helpModalOkBtn");

  if (!helpBtn || !helpModal) return;

  helpBtn.addEventListener("click", () => {
    helpModal.classList.add("show");
  });

  closeBtn.addEventListener("click", () => {
    helpModal.classList.remove("show");
  });

  okBtn.addEventListener("click", () => {
    helpModal.classList.remove("show");
  });

  helpModal.addEventListener("click", (event) => {
    if (event.target === helpModal) {
      helpModal.classList.remove("show");
    }
  });
}
