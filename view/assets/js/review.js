// Script para el sistema de valoración de 5 estrellas con media estrella

// Variables globales
let currentRating = 0;
let reviewText = '';
let isSelecting = false;

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    initializeRatingSystem();
    initializeTextArea();
    initializeButtons();
});

// Sistema de valoración con 5 estrellas
function initializeRatingSystem() {
    const stars = document.querySelectorAll('.star');
    const ratingValue = document.getElementById('rating-value');
    const ratingStars = document.getElementById('rating-stars');
    
    // Configurar eventos para cada estrella
    stars.forEach(star => {
        star.addEventListener('mouseenter', function(e) {
            if (!isSelecting) return;
            
            const value = parseInt(this.dataset.value);
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const isHalf = x < rect.width / 2;
            
            updateStarsDisplay(value, isHalf, stars, ratingValue);
        });
        
        star.addEventListener('mousemove', function(e) {
            if (!isSelecting) return;
            
            const value = parseInt(this.dataset.value);
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const isHalf = x < rect.width / 2;
            
            updateStarsDisplay(value, isHalf, stars, ratingValue);
        });
        
        star.addEventListener('click', function(e) {
            isSelecting = true;
            const value = parseInt(this.dataset.value);
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const isHalf = x < rect.width / 2;
            
            // Calcular rating final
            currentRating = isHalf ? value - 0.5 : value;
            
            updateStarsDisplay(value, isHalf, stars, ratingValue);
        });
        
        star.addEventListener('mouseleave', function() {
            if (!isSelecting) return;
            
            // Mantener la selección actual
            const wholeStars = Math.floor(currentRating);
            const hasHalf = currentRating % 1 !== 0;
            
            updateStarsByRating(currentRating, stars, ratingValue);
        });
    });
    
    // Inicializar con valor 0
    updateStarsByRating(0, stars, ratingValue);
    
    // Permitir empezar la selección desde cualquier estrella
    ratingStars.addEventListener('mousedown', function() {
        isSelecting = true;
    });
    
    // Detener selección al soltar el mouse en cualquier parte
    document.addEventListener('mouseup', function() {
        isSelecting = false;
    });
}

// Actualizar visualización durante interacción
function updateStarsDisplay(hoverValue, isHalf, stars, ratingValue) {
    const tempRating = isHalf ? hoverValue - 0.5 : hoverValue;
    
    stars.forEach(star => {
        const starValue = parseInt(star.dataset.value);
        
        // Limpiar clases
        star.classList.remove('full', 'half');
        
        if (starValue < hoverValue) {
            // Estrellas anteriores completas
            star.classList.add('full');
        } else if (starValue === hoverValue) {
            // Estrella actual puede ser media o completa
            if (isHalf) {
                star.classList.add('half');
            } else {
                star.classList.add('full');
            }
        }
        // Estrellas posteriores permanecen vacías
    });
    
    // Actualizar valor numérico temporalmente
    ratingValue.textContent = tempRating.toFixed(1);
}

// Actualizar visualización según rating final
function updateStarsByRating(rating, stars, ratingValue) {
    const wholeStars = Math.floor(rating);
    const hasHalf = rating % 1 !== 0;
    
    stars.forEach(star => {
        const starValue = parseInt(star.dataset.value);
        
        // Limpiar clases
        star.classList.remove('full', 'half');
        
        if (starValue <= wholeStars) {
            // Estrellas completas
            star.classList.add('full');
        } else if (starValue === wholeStars + 1 && hasHalf) {
            // Media estrella
            star.classList.add('half');
        }
    });
    
    // Actualizar valor numérico
    ratingValue.textContent = rating.toFixed(1);
}

// Sistema de área de texto y contador
function initializeTextArea() {
    const textarea = document.getElementById('review-textarea');
    const charCounter = document.getElementById('char-counter');
    
    // Contador de caracteres
    textarea.addEventListener('input', function() {
        const length = this.value.length;
        reviewText = this.value;
        updateCharacterCounter(length, charCounter);
    });
    
    // Inicializar contador
    updateCharacterCounter(0, charCounter);
}

// Actualizar contador de caracteres
function updateCharacterCounter(length, charCounter) {
    charCounter.textContent = length + '/500 caracteres';
    
    // Cambiar color según el número de caracteres
    if (length >= 450) {
        charCounter.style.color = '#e27171';
    } else if (length >= 400) {
        charCounter.style.color = '#ffa500';
    } else {
        charCounter.style.color = '#6b6b6b';
    }
}

// Inicializar botones
function initializeButtons() {
    const submitBtn = document.getElementById('btn-submit');
    const cancelBtn = document.getElementById('btn-cancel');
    
    // Botón Enviar
    submitBtn.addEventListener('click', function() {
        submitReview();
    });
    
    // Botón Cancelar
    cancelBtn.addEventListener('click', function() {
        cancelReview();
    });
}

// Enviar reseña
function submitReview() {
    const textarea = document.getElementById('review-textarea');
    
    if (currentRating === 0) {
        alert('Por favor, selecciona una puntuación antes de enviar.');
        return;
    }
    
    if (reviewText.trim().length === 0) {
        alert('Por favor, escribe un comentario antes de enviar.');
        return;
    }
    
    // Simular envío de datos
    const reviewData = {
        rating: currentRating,
        review: reviewText,
        game: document.querySelector('.game-title').textContent,
        timestamp: new Date().toISOString()
    };
    
    console.log('Reseña enviada:', reviewData);
    
    // Mostrar mensaje de confirmación
    alert(`¡Gracias por tu reseña de ${currentRating.toFixed(1)} estrellas! Tu valoración ha sido enviada.`);
    
    // Resetear formulario
    resetForm();
}

// Cancelar reseña
function cancelReview() {
    const confirmCancel = confirm('¿Estás seguro de que quieres cancelar? Se perderán los cambios.');
    
    if (confirmCancel) {
        resetForm();
        console.log('Reseña cancelada');
    }
}

// Resetear formulario
function resetForm() {
    const stars = document.querySelectorAll('.star');
    const ratingValue = document.getElementById('rating-value');
    const textarea = document.getElementById('review-textarea');
    const charCounter = document.getElementById('char-counter');
    
    // Resetear valores
    currentRating = 0;
    reviewText = '';
    isSelecting = false;
    
    // Actualizar visualización
    updateStarsByRating(0, stars, ratingValue);
    textarea.value = '';
    updateCharacterCounter(0, charCounter);
    
    // Quitar foco del textarea
    textarea.blur();
}