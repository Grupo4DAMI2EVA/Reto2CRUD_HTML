const rules = {
  name: { required: true },
  platform: { required: true },
  company: { required: true },
  stock: { required: true },
  genre: { required: true },
  price: { required: true },
  pegi: { required: true },
  releaseDate: { required: true }
};

function validate(field, value) {
  const rule = rules[field];
  const val = value.trim();

  if (rule.required && !val) {
    return `${field.charAt(0).toUpperCase() + field.slice(1)} is required`;
  }

  return null;
}

function showError(fieldId, message) {
  const errorEl = document.getElementById(fieldId + 'Error');
  const inputEl = document.getElementById(fieldId);
  if (errorEl) {
    errorEl.textContent = message;
    errorEl.style.display = 'block';
  }
  if (inputEl) inputEl.style.borderColor = '#dc3545';
}

function clearError(fieldId) {
  const errorEl = document.getElementById(fieldId + 'Error');
  const inputEl = document.getElementById(fieldId);
  if (errorEl) {
    errorEl.textContent = '';
    errorEl.style.display = 'none';
  }
  if (inputEl) inputEl.style.borderColor = '';
}

function validateForm() {
  let isValid = true;
  Object.keys(rules).forEach(field => {
    const error = validate(field, document.getElementById(field).value);
    if (error) {
      showError(field, error);
      isValid = false;
    } else {
      clearError(field);
    }
  });
  return isValid;
}

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('addGameForm');

  // Real-time validation on blur (When focus is lost)
  Object.keys(rules).forEach(field => {
    const el = document.getElementById(field);
    el.addEventListener('blur', () => {
      const error = validate(field, el.value);
      if (error) showError(field, error);
      else clearError(field);
    });
  });

  // Form submission
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!validateForm()) return;

    try {
      const res = await fetch(form.action, { method: 'POST', body: new FormData(form) });
      const data = await res.json();

      if (data.exito) {
        alert(data.resultado);
        // Clear all errors before reload
        Object.keys(rules).forEach(field => clearError(field));
        location.reload();
      } else {
        // Try to identify which field has the error based on the message
        let fieldWithError = null;
        const message = data.resultado.toLowerCase();
        
        Object.keys(rules).forEach(field => {
          if (message.includes(field.toLowerCase())) {
            fieldWithError = field;
          }
        });
        
        if (fieldWithError) {
          showError(fieldWithError, data.resultado);
        } else {
          alert(data.resultado || 'Error al crear el juego');
        }
      }
    } catch (err) {
      alert('Network error: ' + err.message);
    }
  });
});
