let currentRating = 0;
let reviewText = "";
let isSelecting = false;
let currentGameId = null;
let currentGameName = "";

async function countUserReviews(profileCode) {
  try {
    const response = await fetch(`../../api/GetUserReviews.php?profile_code=${profileCode}`);
    
    if (!response.ok) {
      console.error("Error en la respuesta del servidor:", response.status);
      return 0;
    }
    
    const data = await response.json();
    
    if (data.exito && data.resultado) {
      return data.resultado.length;
    }
    return 0;
  } catch (err) {
    console.error("Error contando reseñas:", err);
    return 0;
  }
}

// Función para obtener parámetros de la URL
function getUrlParams() {
  const params = {};
  const queryString = window.location.search.substring(1);
  
  if (!queryString) return params;
  
  const pairs = queryString.split('&');
  
  for (const pair of pairs) {
    const [key, value] = pair.split('=');
    if (key) {
      params[decodeURIComponent(key)] = decodeURIComponent(value || '');
    }
  }
  
  return params;
}


// Función para cargar información del juego
async function loadGameInfo(gameId, gameName) {
  try {
    currentGameId = gameId;
    currentGameName = decodeURIComponent(gameName);
    
    // Actualizar el campo oculto
    const gameIdInput = document.getElementById('current-game-id');
    if (gameIdInput) {
      gameIdInput.value = gameId;
    }
    
    // Actualizar el título del juego
    const gameTitleElement = document.getElementById('game-title');
    if (gameTitleElement) {
      gameTitleElement.textContent = currentGameName;
      gameTitleElement.dataset.gameId = gameId;
    }
    
    console.log(`Juego cargado: ${currentGameName} (ID: ${gameId})`);
    
    // Opcional: puedes hacer una petición a la API para obtener más detalles del juego
    // await fetchGameDetails(gameId);
    
  } catch (error) {
    console.error("Error cargando información del juego:", error);
  }
}

document.addEventListener("DOMContentLoaded", async function () {
  const user = await comprobarSesion();
  if (!user) {
    alert("Debes iniciar sesión para dejar una reseña.");
    window.location.href = "login.html";
    return;
  }

  // Obtener parámetros de la URL
  const params = getUrlParams();
  const gameId = params.game_id || params.id || params.videogame_code;
  
  if (!gameId) {
    alert("No se ha especificado un juego para valorar. Regresando a la tienda.");
    window.location.href = "store.html";
    return;
  }
  
  // Obtener nombre del juego
  const gameName = params.game_name || params.name || "Juego Desconocido";
  
  // Cargar información del juego
  await loadGameInfo(gameId, gameName);
  
  const nameSpan = document.getElementById("storeUserName");
  if (nameSpan) {
    nameSpan.textContent = user.USER_NAME || user.NAME_ || "[User]";
  }

  const reviewCountSpan = document.getElementById("storeUserReviewCount");
  if (reviewCountSpan) {
    const userReviewCount = await countUserReviews(user.PROFILE_CODE);
    reviewCountSpan.textContent = userReviewCount;
  }

  initializeRatingSystem();
  initializeTextArea();
  initializeButtons();
});

function initializeRatingSystem() {
  const stars = document.querySelectorAll(".star");
  const ratingValue = document.getElementById("rating-value");
  const ratingStars = document.getElementById("rating-stars");

  stars.forEach((star) => {
    star.addEventListener("mouseenter", function (e) {
      if (!isSelecting) return;

      const value = parseInt(this.dataset.value);
      const rect = this.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const isHalf = x < rect.width / 2;

      updateStarsDisplay(value, isHalf, stars, ratingValue);
    });

    star.addEventListener("mousemove", function (e) {
      if (!isSelecting) return;

      const value = parseInt(this.dataset.value);
      const rect = this.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const isHalf = x < rect.width / 2;

      updateStarsDisplay(value, isHalf, stars, ratingValue);
    });

    star.addEventListener("click", function (e) {
      isSelecting = true;
      const value = parseInt(this.dataset.value);
      const rect = this.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const isHalf = x < rect.width / 2;

      currentRating = isHalf ? value - 0.5 : value;

      updateStarsDisplay(value, isHalf, stars, ratingValue);
    });

    star.addEventListener("mouseleave", function () {
      if (!isSelecting) return;

      updateStarsByRating(currentRating, stars, ratingValue);
    });
  });

  updateStarsByRating(0, stars, ratingValue);

  ratingStars.addEventListener("mousedown", function () {
    isSelecting = true;
  });

  document.addEventListener("mouseup", function () {
    isSelecting = false;
  });
}

function updateStarsDisplay(hoverValue, isHalf, stars, ratingValue) {
  const tempRating = isHalf ? hoverValue - 0.5 : hoverValue;

  stars.forEach((star) => {
    const starValue = parseInt(star.dataset.value);

    star.classList.remove("full", "half");

    if (starValue < hoverValue) {
      star.classList.add("full");
    } else if (starValue === hoverValue) {
      if (isHalf) {
        star.classList.add("half");
      } else {
        star.classList.add("full");
      }
    }
  });

  ratingValue.textContent = tempRating.toFixed(1);
}

function updateStarsByRating(rating, stars, ratingValue) {
  const wholeStars = Math.floor(rating);
  const hasHalf = rating % 1 !== 0;

  stars.forEach((star) => {
    const starValue = parseInt(star.dataset.value);

    star.classList.remove("full", "half");

    if (starValue <= wholeStars) {
      star.classList.add("full");
    } else if (starValue === wholeStars + 1 && hasHalf) {
      star.classList.add("half");
    }
  });

  ratingValue.textContent = rating.toFixed(1);
}

function initializeTextArea() {
  const textarea = document.getElementById("review-textarea");
  const charCounter = document.getElementById("char-counter");

  textarea.addEventListener("input", function () {
    const length = this.value.length;
    reviewText = this.value;
    updateCharacterCounter(length, charCounter);
  });

  updateCharacterCounter(0, charCounter);
}

function updateCharacterCounter(length, charCounter) {
  charCounter.textContent = length + "/500 caracteres";

  if (length >= 450) {
    charCounter.style.color = "#e27171";
  } else if (length >= 400) {
    charCounter.style.color = "#ffa500";
  } else {
    charCounter.style.color = "#6b6b6b";
  }
}

function initializeButtons() {
  const submitBtn = document.getElementById("btn-submit");
  const cancelBtn = document.getElementById("btn-cancel");

  submitBtn.addEventListener("click", function () {
    submitReview();
  });

  cancelBtn.addEventListener("click", function () {
    cancelReview();
  });
}

async function submitReview() {
  if (!currentGameId) {
    alert("Error: No se pudo identificar el juego. Por favor, regresa a la tienda.");
    return;
  }

  if (currentRating === 0) {
    alert("Por favor, selecciona una puntuación antes de enviar.");
    return;
  }

  if (reviewText.trim().length === 0) {
    alert("Por favor, escribe un comentario antes de enviar.");
    return;
  }

  if (reviewText.length > 500) {
    alert("El comentario no puede exceder 500 caracteres.");
    return;
  }

  // Mostrar indicador de carga
  const submitBtn = document.getElementById("btn-submit");
  const originalText = submitBtn.textContent;
  submitBtn.textContent = "Enviando...";
  submitBtn.disabled = true;

  try {
    const reviewData = {
      comment: reviewText.trim(),
      rating: currentRating,
      videogame_code: currentGameId
    };

    console.log("Enviando reseña:", reviewData);

    const response = await fetch("../../api/AddReview.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(reviewData)
    });

    const data = await response.json();

    if (data.success) {
      const gameName = currentGameName || "este juego";
      alert(`¡Gracias por tu reseña de ${currentRating.toFixed(1)} estrellas para ${gameName}! Tu valoración ha sido enviada.`);
      
      // Actualizar contador de reseñas
      const user = await comprobarSesion();
      const reviewCountSpan = document.getElementById("storeUserReviewCount");
      if (reviewCountSpan && user) {
        const userReviewCount = await countUserReviews(user.PROFILE_CODE);
        reviewCountSpan.textContent = userReviewCount;
      }
      
      resetForm();
    } else {
      alert(`Error al enviar la reseña: ${data.error || 'Error desconocido'}`);
    }
  } catch (error) {
    console.error("Error al enviar reseña:", error);
    alert("Error de conexión. Por favor, intenta de nuevo.");
  } finally {
    // Restaurar botón
    submitBtn.textContent = originalText;
    submitBtn.disabled = false;
  }
}

function cancelReview() {
  const confirmCancel = confirm(
    "¿Estás seguro de que quieres cancelar? Se perderán los cambios."
  );

  if (confirmCancel) {
    resetForm();
    console.log("Reseña cancelada");
  }
}

function resetForm() {
  const stars = document.querySelectorAll(".star");
  const ratingValue = document.getElementById("rating-value");
  const textarea = document.getElementById("review-textarea");
  const charCounter = document.getElementById("char-counter");

  currentRating = 0;
  reviewText = "";
  isSelecting = false;

  updateStarsByRating(0, stars, ratingValue);
  textarea.value = "";
  updateCharacterCounter(0, charCounter);

  textarea.blur();
}