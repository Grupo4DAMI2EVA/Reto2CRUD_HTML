let currentRating = 0;
let reviewText = "";
let isSelecting = false;

async function countUserReviews(profileCode) {
  try {
    const response = await fetch("../../api/GetUserReviews.php");
    
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

document.addEventListener("DOMContentLoaded", async function () {
  const user = await comprobarSesion();
  if (!user) return;

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

      const wholeStars = Math.floor(currentRating);
      const hasHalf = currentRating % 1 !== 0;

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

function submitReview() {
  const textarea = document.getElementById("review-textarea");

  if (currentRating === 0) {
    alert("Por favor, selecciona una puntuación antes de enviar.");
    return;
  }

  if (reviewText.trim().length === 0) {
    alert("Por favor, escribe un comentario antes de enviar.");
    return;
  }

  const reviewData = {
    rating: currentRating,
    review: reviewText,
    game: document.querySelector(".game-title").textContent,
    timestamp: new Date().toISOString(),
  };

  console.log("Reseña enviada:", reviewData);

  alert(
    `¡Gracias por tu reseña de ${currentRating.toFixed(
      1
    )} estrellas! Tu valoración ha sido enviada.`
  );

  resetForm();
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
