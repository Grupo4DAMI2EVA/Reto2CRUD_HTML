let ALL_VIDEOGAMES = [];
let selectedGame = null; // Almacena el juego seleccionado con todos sus datos

document.addEventListener("DOMContentLoaded", async () => {
  const loader = document.getElementById('pageLoader');
  if (loader) {
    loader.style.opacity = '0';
    loader.style.visibility = 'hidden';
    // Opcional: remover del DOM después de la animación
    setTimeout(() => loader.remove(), 5000);
  }
  
  // Verificar sesión y cargar datos del usuario
  const user = await comprobarSesion();
  if (!user) return;

  // Pintar nombre y saldo del usuario
  const nameSpan = document.getElementById("storeUserName");
  const balanceSpan = document.getElementById("storeUserBalance");

  if (nameSpan) {
    // Intentamos usar USER_NAME, si no existe usamos NAME_
    nameSpan.textContent = user.USER_NAME || user.NAME_ || "[User]";
  }

  if (balanceSpan) {
    const balance = user.BALANCE ?? 0;
    balanceSpan.textContent = `${Number(balance).toFixed(2)}€`;
  }

  // Cargar lista de videojuegos
  await loadVideogames();

  // Configurar eventos de búsqueda
  setupSearch();

  // Configurar botón "Add to Cart"
  setupAddToCart();
  
  // Configurar botón "Review"
  setupReviewButton();
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

  if (!tbody) return;

  // Limpiar cualquier fila anterior (incluida la "Tabla sin contenido")
  tbody.innerHTML = "";

  try {
    ALL_VIDEOGAMES = await get_all_videogames();
  } catch (e) {
    console.error("Error obteniendo videojuegos:", e);
    ALL_VIDEOGAMES = [];
  }

  // Rellenar selects de género y plataforma
  populateFilters(ALL_VIDEOGAMES);

  // Pintar todos los videojuegos inicialmente
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
      NAME_,
      GENRE,
      PLATAFORM,
      PRICE,
      PEGI,
      STOCK,
      COMPANYNAME,
      RELEASE_DATE,
      VIDEOGAME_CODE
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
      // Guardar el juego completo seleccionado
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
  // Botón "Search" (es el único storeBtn primary dentro de los filtros)
  const searchButton = document.querySelector(
    ".storeFilters .storeBtn.primary"
  );

  if (!searchInput || !genreSelect || !platformSelect || !searchButton) return;

  const applyFilters = () => {
    const text = searchInput.value.trim().toLowerCase();
    const genre = genreSelect.value;
    const platform = platformSelect.value;

    const filtered = ALL_VIDEOGAMES.filter((game) => {
      const name = (game.NAME_ || "").toLowerCase();
      const matchesText = !text || name.includes(text);

      const matchesGenre = genre === "all" || game.GENRE === genre;
      const matchesPlatform =
        platform === "all" || game.PLATAFORM === platform;

      return matchesText && matchesGenre && matchesPlatform;
    });

    renderVideogames(filtered);
  };

  searchButton.addEventListener("click", (e) => {
    e.preventDefault();
    applyFilters();
  });

  // Búsqueda inmediata al escribir / cambiar selects (opcional pero cómodo)
  searchInput.addEventListener("input", applyFilters);
  genreSelect.addEventListener("change", applyFilters);
  platformSelect.addEventListener("change", applyFilters);
}

function setupAddToCart() {
  const addToCartBtn = document.querySelector('.storeBtn.accent');
  
  if (!addToCartBtn) return;

  addToCartBtn.addEventListener("click", async () => {
    if (!selectedGame) {
      alert("Por favor, selecciona un juego de la tabla primero");
      return;
    }

    // Verificar que hay stock disponible
    if (!selectedGame.STOCK || selectedGame.STOCK <= 0) {
      alert("Este juego no está disponible en stock");
      return;
    }

    try {
      const response = await fetch("../../api/AddToCart.php", {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          videogame_code: selectedGame.VIDEOGAME_CODE,
          quantity: 1
        }),
      });

      const data = await response.json();

      if (data.success || data.exito) {
        alert(data.message || "Juego añadido al carrito correctamente");
      } else {
        alert(data.error || "Error al añadir el juego al carrito");
      }
    } catch (error) {
      console.error("Error añadiendo al carrito:", error);
      alert("Error de conexión al añadir al carrito");
    }
  });
}

function setupReviewButton() {
  // Encuentra el botón "Review" (ghost) en la tienda
  const reviewButtons = document.querySelectorAll('a[href="review.html"] button, button[onclick*="review.html"]');
  
  reviewButtons.forEach(button => {
    // Reemplazar el comportamiento por defecto
    button.addEventListener("click", function(e) {
      e.preventDefault(); // Prevenir la navegación por defecto
      
      if (!selectedGame) {
        alert("Por favor, selecciona un juego de la tabla primero para dejar una reseña");
        return;
      }
      
      // Navegar a review.html con los parámetros del juego
      navigateToReview(selectedGame);
    });
  });
}

function navigateToReview(game) {
  if (!game || !game.VIDEOGAME_CODE || !game.NAME_) {
    alert("Error: No se pudo obtener la información del juego seleccionado");
    return;
  }
  
  // Codificar el nombre para la URL
  const encodedName = encodeURIComponent(game.NAME_);
  
  // Construir la URL con parámetros
  const reviewUrl = `review.html?game_id=${game.VIDEOGAME_CODE}&game_name=${encodedName}`;
  
  console.log(`Navegando a: ${reviewUrl}`);
  
  // Navegar a la página de reseñas
  window.location.href = reviewUrl;
}