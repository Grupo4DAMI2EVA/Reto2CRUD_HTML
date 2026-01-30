// cart.js — handles loading, rendering and actions on the shopping cart

const api = {
  getCart: "../../api/GetCart.php",
  buy: "../../api/Buy.php",
  deleteItem: "../../api/DeleteFromKart.php",
  updateCart: "../../api/UpdateCart.php",
};

let CART = [];
let selectedItemId = null;

// DOM refs
let cartContainer, totalItemsEl, totalPriceEl, buyBtn;
let selectedItemMessage,
  quantityDisplay,
  decreaseBtn,
  increaseBtn,
  deleteSelectedBtn;
let errorMessageDiv, errorText, balanceSpan;

document.addEventListener("DOMContentLoaded", init);

async function init() {
  // Verificar sesión y cargar datos del usuario
  const user = await comprobarSesion();
  if (!user) return;

  // Pintar nombre y saldo del usuario
  const nameSpan = document.getElementById("storeUserName");
  balanceSpan = document.getElementById("storeUserBalance");

  if (nameSpan) {
    nameSpan.textContent = user.USER_NAME || user.NAME_ || "[User]";
  }

  if (balanceSpan) {
    const balance = user.BALANCE ?? 0;
    balanceSpan.textContent = `${Number(balance).toFixed(2)}€`;
  }

  // Referencias DOM
  cartContainer = document.querySelector('[data-role="cart-items"]');
  totalItemsEl = document.querySelector('[data-role="total-items"]');
  totalPriceEl = document.querySelector('[data-role="total-price"]');
  buyBtn = document.querySelector('[data-role="buy-btn"]');

  // Mensajes y controles
  errorMessageDiv = document.getElementById("errorMessage");
  errorText = document.getElementById("errorText");
  selectedItemMessage = document.getElementById("selectedItemMessage");

  // IMPORTANTE: Usar IDs específicos
  quantityDisplay = document.getElementById("quantityDisplay");
  decreaseBtn = document.getElementById("decreaseQtyBtn");
  increaseBtn = document.getElementById("increaseQtyBtn");
  deleteSelectedBtn = document.querySelector(
    '[data-role="selected-delete-btn"]',
  );

  if (!cartContainer) return console.warn("Cart container not found");

  // Delegated event handling for item selection
  cartContainer.addEventListener("click", onCartItemClick);

  // Conectar botones
  if (buyBtn) buyBtn.addEventListener("click", onBuy);

  // Conectar botones de cantidad
  if (decreaseBtn) {
    decreaseBtn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      changeSelectedQty(-1);
    });
  }

  if (increaseBtn) {
    increaseBtn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      changeSelectedQty(1);
    });
  }

  if (deleteSelectedBtn) {
    deleteSelectedBtn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      deleteSelectedItem();
    });
  }

  // Load cart
  CART = await fetchCart();
  renderCart();
  updateSelectedItemUI();
}

async function fetchCart() {
  try {
    const resp = await fetch(api.getCart);
    if (!resp.ok) throw new Error("No server cart");
    const data = await resp.json();
    if (Array.isArray(data) && data.length >= 0) return data;
  } catch (err) {
    const raw = localStorage.getItem("cart");
    try {
      return raw ? JSON.parse(raw) : [];
    } catch (e) {
      return [];
    }
  }
  return [];
}

function renderCart() {
  cartContainer.innerHTML = "";

  if (!CART || CART.length === 0) {
    cartContainer.innerHTML = `<div style="text-align: center; padding: 40px 20px; color: #6b6b6b; font-style: italic;">---<br>El carrito está vacío<br>---</div>`;
    selectedItemId = null;
    updateTotals();
    updateSelectedItemUI();
    return;
  }

  const fragment = document.createDocumentFragment();

  CART.forEach((item) => {
    const itemEl = document.createElement("div");
    itemEl.className = "cart-item";
    itemEl.dataset.id = item.id;
    itemEl.dataset.price = item.price;

    if (selectedItemId == item.id) {
      itemEl.classList.add("selected");
      itemEl.style.backgroundColor = "#f0f8ff";
      itemEl.style.border = "2px solid #4a90e2";
    }

    itemEl.innerHTML = `
      <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; padding:12px; border-bottom:1px solid #eee; cursor: pointer;">
        <div style="flex:1">
          <div style="font-weight:600">${escapeHtml(item.name)}</div>
          <div style="color:#666">Precio: ${Number(item.price).toFixed(2)}€</div>
        </div>
        <div style="display:flex; align-items:center; gap:8px">
          <span class="qty">Cantidad: ${item.qty}</span>
        </div>
        <div style="min-width:100px; text-align:right">
          <div style="font-weight:600">${(item.price * item.qty).toFixed(2)}€</div>
        </div>
      </div>
    `;

    fragment.appendChild(itemEl);
  });

  cartContainer.appendChild(fragment);
  updateTotals();
  updateSelectedItemUI();
}

function updateTotals() {
  const totalItems = CART.reduce((s, it) => s + Number(it.qty), 0);
  const totalPrice = CART.reduce(
    (s, it) => s + Number(it.price) * Number(it.qty),
    0,
  );

  if (totalItemsEl) totalItemsEl.textContent = totalItems;
  if (totalPriceEl) totalPriceEl.textContent = `${totalPrice.toFixed(2)}€`;

  localStorage.setItem("cart", JSON.stringify(CART));
}

function onCartItemClick(e) {
  if (
    e.target.closest("a") ||
    e.target.closest("button") ||
    e.target.tagName === "A" ||
    e.target.tagName === "BUTTON"
  ) {
    return;
  }

  const itemEl = e.target.closest(".cart-item");
  if (!itemEl) return;

  const id = itemEl.dataset.id;
  selectItem(id);
}

function selectItem(id) {
  selectedItemId = id;
  renderCart();
  updateSelectedItemUI();
}

function updateSelectedItemUI() {
  console.log("Actualizando UI, selectedItemId:", selectedItemId);
  console.log("quantityDisplay:", quantityDisplay);

  // Verificar que tenemos la referencia correcta
  if (!quantityDisplay) {
    // Intentar encontrar el elemento de nuevo por si no se encontró inicialmente
    quantityDisplay = document.getElementById("quantityDisplay");
    console.log("Re-buscando quantityDisplay:", quantityDisplay);
  }

  if (!selectedItemId) {
    // No hay item seleccionado
    if (selectedItemMessage) {
      selectedItemMessage.textContent = "Selecciona un item";
    }
    if (quantityDisplay) {
      quantityDisplay.textContent = "0";
    }
    if (decreaseBtn) decreaseBtn.disabled = true;
    if (increaseBtn) increaseBtn.disabled = true;
    if (deleteSelectedBtn) deleteSelectedBtn.disabled = true;
    return;
  }

  const item = CART.find((it) => String(it.id) === String(selectedItemId));
  if (!item) {
    selectedItemId = null;
    updateSelectedItemUI();
    return;
  }

  // Actualizar UI con el item seleccionado
  if (selectedItemMessage) {
    selectedItemMessage.textContent = `Item seleccionado: ${escapeHtml(item.name)}`;
  }

  // IMPORTANTE: Actualizar el número entre + y -
  if (quantityDisplay) {
    quantityDisplay.textContent = item.qty;
    console.log("Actualizando cantidad display a:", item.qty);
  }

  if (decreaseBtn) decreaseBtn.disabled = false;
  if (increaseBtn) increaseBtn.disabled = false;
  if (deleteSelectedBtn) deleteSelectedBtn.disabled = false;
}

async function changeSelectedQty(delta) {
  console.log(
    "Cambiando cantidad, delta:",
    delta,
    "selectedItemId:",
    selectedItemId,
  );

  if (!selectedItemId) {
    console.log("No hay item seleccionado");
    return;
  }

  const idx = CART.findIndex((it) => String(it.id) === String(selectedItemId));
  if (idx === -1) {
    console.log("Item no encontrado en el carrito");
    selectedItemId = null;
    updateSelectedItemUI();
    return;
  }

  const item = CART[idx];
  const currentQty = Number(item.qty) || 0;
  const newQty = Math.max(0, currentQty + delta);

  console.log(
    `Cambiando cantidad de ${currentQty} a ${newQty} para item ${item.name}`,
  );

  if (newQty === 0) {
    // Eliminar item si la cantidad llega a 0
    deleteSelectedItem();
  } else {
    // Actualizar localmente primero
    item.qty = newQty;
    updateTotals();
    renderCart();
    updateSelectedItemUI();

    // Actualizar en el servidor
    try {
      await updateCartItemQty(selectedItemId, newQty);
    } catch (err) {
      console.error("Error actualizando cantidad:", err);
    }
  }
}

async function updateCartItemQty(id, qty) {
  try {
    const response = await fetch(api.updateCart, {
      method: "POST",
      credentials: "include",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        videogame_code: id,
        quantity: qty,
      }),
    });

    const data = await response.json();
    if (!data.success) {
      console.error("Error actualizando cantidad:", data.error);
      // Recargar carrito desde servidor
      const savedSelectedId = selectedItemId;
      CART = await fetchCart();
      renderCart();
      selectedItemId = savedSelectedId;
      updateSelectedItemUI();
    }
  } catch (err) {
    console.error("Error de red al actualizar cantidad:", err);
    const savedSelectedId = selectedItemId;
    CART = await fetchCart();
    renderCart();
    selectedItemId = savedSelectedId;
    updateSelectedItemUI();
  }
}

function deleteSelectedItem() {
  if (!selectedItemId) return;
  removeItem(selectedItemId);
  selectedItemId = null;
  updateSelectedItemUI();
}

async function removeItem(id) {
  const idx = CART.findIndex((it) => String(it.id) === String(id));
  if (idx === -1) return;
  const removed = CART.splice(idx, 1)[0];

  if (selectedItemId == id) {
    selectedItemId = null;
  }

  renderCart();
  updateSelectedItemUI();

  try {
    const form = new URLSearchParams({ id: String(id) });
    const resp = await fetch(api.deleteItem, { method: "POST", body: form });
    const json = await resp.json().catch(() => ({}));
    if (!resp.ok || json.error) {
      CART.splice(idx, 0, removed);
      renderCart();
      updateSelectedItemUI();
      alert(json.error || "No se pudo eliminar el item en el servidor");
    }
  } catch (err) {
    CART.splice(idx, 0, removed);
    renderCart();
    updateSelectedItemUI();
    console.error(err);
    alert("Error de red al eliminar el item");
  }
}

function showError(message) {
  if (errorMessageDiv && errorText) {
    errorText.innerHTML = message.replace(/\n/g, "<br>");
    errorMessageDiv.style.display = "block";
    errorMessageDiv.scrollIntoView({ behavior: "smooth", block: "nearest" });
  }
}

function hideError() {
  if (errorMessageDiv) {
    errorMessageDiv.style.display = "none";
  }
}

async function onBuy() {
  if (!CART || CART.length === 0) {
    showError("El carrito está vacío");
    return;
  }

  hideError();

  if (!confirm("¿Estás seguro de realizar la compra?")) return;

  try {
    const payload = { items: CART };
    const resp = await fetch(api.buy, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });

    const json = await resp.json().catch(() => ({}));

    if (!resp.ok || json.error || !json.success) {
      let errorMsg =
        json.error || json.message || "Error al procesar la compra";

      if (json.error_type === "insufficient_balance") {
        errorMsg = `❌ SALDO INSUFICIENTE\n\n${errorMsg}`;
        if (json.balance !== undefined && json.required !== undefined) {
          errorMsg += `\n\nSaldo actual: ${Number(json.balance).toFixed(2)}€`;
          errorMsg += `\nTotal requerido: ${Number(json.required).toFixed(2)}€`;
          if (json.needed !== undefined) {
            errorMsg += `\nTe faltan: ${Number(json.needed).toFixed(2)}€`;
          }
        }
      } else if (json.error_type === "insufficient_stock") {
        errorMsg = `❌ STOCK INSUFICIENTE\n\n${errorMsg}`;
      } else if (json.error_type === "empty_cart") {
        errorMsg = `❌ ${errorMsg}`;
      } else {
        errorMsg = `❌ ERROR\n\n${errorMsg}`;
      }

      showError(errorMsg);
      return;
    }

    CART = [];
    renderCart();
    hideError();
    alert(json.message || "Compra realizada con éxito");

    const user = await comprobarSesion();
    if (user && balanceSpan) {
      const balance = user.BALANCE ?? 0;
      balanceSpan.textContent = `${Number(balance).toFixed(2)}€`;
    }

    window.location.href = "store.html";
  } catch (err) {
    console.error(err);
    showError(
      "❌ ERROR DE RED\n\nError de conexión al procesar la compra. Por favor, intenta de nuevo.",
    );
  }
}

function escapeHtml(s) {
  if (!s) return "";
  return String(s)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}
