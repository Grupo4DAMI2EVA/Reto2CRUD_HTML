// cart.js — handles loading, rendering and actions on the shopping cart

const api = {
  getCart: "/api/GetCart.php", // optional: if not present, fallback to localStorage
  buy: "/api/Buy.php",
  deleteItem: "/api/DeleteFromKart.php",
};

let CART = []; // in-memory cart representation: [{id, name, price, qty}, ...]

// DOM refs
let cartContainer, totalItemsEl, totalPriceEl, buyBtns;

document.addEventListener("DOMContentLoaded", init);

async function init() {
  // Verificar sesión y cargar datos del usuario
  const user = await comprobarSesion();
  if (!user) return;

  // Pintar nombre y saldo del usuario
  const nameSpan = document.getElementById("storeUserName");
  const balanceSpan = document.getElementById("storeUserBalance");

  if (nameSpan) {
    nameSpan.textContent = user.USER_NAME || user.NAME_ || "[User]";
  }

  if (balanceSpan) {
    const balance = user.BALANCE ?? 0;
    balanceSpan.textContent = `${Number(balance).toFixed(2)}€`;
  }

  cartContainer = document.querySelector('[data-role="cart-items"]');
  totalItemsEl = document.querySelector('[data-role="total-items"]');
  totalPriceEl = document.querySelector('[data-role="total-price"]');
  buyBtns = document.querySelectorAll('[data-role="buy-btn"]');

  if (!cartContainer) return console.warn("Cart container not found");

  // Delegated event handling for item buttons
  cartContainer.addEventListener('click', onCartClick);
  buyBtns.forEach(b => b.addEventListener('click', onBuy));

  // Load cart
  CART = await fetchCart();
  renderCart();
}

async function fetchCart() {
  try {
    const resp = await fetch(api.getCart);
    if (!resp.ok) throw new Error("No server cart");
    const data = await resp.json();
    if (Array.isArray(data) && data.length >= 0) return data;
  } catch (err) {
    // fallback to localStorage
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
    // show the empty message (keeps existing HTML inside container if any)
    cartContainer.innerHTML = `<div style="text-align: center; padding: 40px 20px; color: #6b6b6b; font-style: italic;">---<br>El carrito está vacío<br>---</div>`;
    updateTotals();
    return;
  }

  const fragment = document.createDocumentFragment();

  CART.forEach((item) => {
    const itemEl = document.createElement("div");
    itemEl.className = "cart-item";
    itemEl.dataset.id = item.id;
    itemEl.dataset.price = item.price;

    itemEl.innerHTML = `
      <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; padding:12px; border-bottom:1px solid #eee;">
        <div style="flex:1">
          <div style="font-weight:600">${escapeHtml(item.name)}</div>
          <div style="color:#666">Precio: $${Number(item.price).toFixed(
            2
          )}</div>
        </div>
        <div style="display:flex; align-items:center; gap:8px">
          <button class="qty-btn" data-action="decrease">-</button>
          <span class="qty">${item.qty}</span>
          <button class="qty-btn" data-action="increase">+</button>
        </div>
        <div style="min-width:100px; text-align:right">
          <div>$${(item.price * item.qty).toFixed(2)}</div>
          <button class="remove-btn" data-action="remove" style="margin-top:6px">Eliminar</button>
        </div>
      </div>
    `;

    fragment.appendChild(itemEl);
  });

  cartContainer.appendChild(fragment);
  updateTotals();
}

function updateTotals() {
  const totalItems = CART.reduce((s, it) => s + Number(it.qty), 0);
  const totalPrice = CART.reduce(
    (s, it) => s + Number(it.price) * Number(it.qty),
    0
  );

  if (totalItemsEl) totalItemsEl.textContent = totalItems;
  if (totalPriceEl) totalPriceEl.textContent = `$${totalPrice.toFixed(2)}`;

  // store locally as a fallback (keeps UI consistent if server not used)
  localStorage.setItem("cart", JSON.stringify(CART));
}

function onCartClick(e) {
  const actionBtn = e.target.closest("[data-action]");
  if (!actionBtn) return;

  const action = actionBtn.dataset.action;
  const itemEl = actionBtn.closest(".cart-item");
  if (!itemEl) return;
  const id = itemEl.dataset.id;

  if (action === "increase") changeQty(id, 1);
  else if (action === "decrease") changeQty(id, -1);
  else if (action === "remove") removeItem(id);
}

function changeQty(id, delta) {
  const idx = CART.findIndex((it) => String(it.id) === String(id));
  if (idx === -1) return;
  const item = CART[idx];
  item.qty = Math.max(0, Number(item.qty) + delta);

  // Remove item if qty is 0
  if (item.qty === 0) CART.splice(idx, 1);

  renderCart();

  // NOTE: If you have an endpoint to update quantities, call it here.
  // Example: await fetch('/api/UpdateCart.php', {method:'POST', body: new URLSearchParams({id, qty: item.qty})})
}

async function removeItem(id) {
  // Optimistic UI
  const idx = CART.findIndex((it) => String(it.id) === String(id));
  if (idx === -1) return;
  const removed = CART.splice(idx, 1)[0];
  renderCart();

  try {
    const form = new URLSearchParams({ id: String(id) });
    const resp = await fetch(api.deleteItem, { method: "POST", body: form });
    const json = await resp.json().catch(() => ({}));
    if (!resp.ok || json.error) {
      // revert
      CART.splice(idx, 0, removed);
      renderCart();
      alert(json.message || "No se pudo eliminar el item en el servidor");
    }
  } catch (err) {
    // revert
    CART.splice(idx, 0, removed);
    renderCart();
    console.error(err);
    alert("Error de red al eliminar el item");
  }
}

async function onBuy() {
  if (!CART || CART.length === 0) return alert("El carrito está vacío");

  // Confirmation popup
  if (!confirm('¿Estás seguro de realizar la compra?')) return;

  try {
    const payload = { items: CART };
    const resp = await fetch(api.buy, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });

    const json = await resp.json().catch(() => ({}));
    if (!resp.ok || json.error) {
      alert(json.message || "Error al procesar la compra");
      return;
    }

    // assume success: clear cart
    CART = [];
    renderCart();
    alert(json.message || 'Compra realizada con éxito');

    // Redirect back to the store page
    window.location.href = 'store.html';
  } catch (err) {
    console.error(err);
    alert("Error de red al procesar la compra");
  }
}

// small utility to escape HTML when injecting names
function escapeHtml(s) {
  if (!s) return "";
  return String(s)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}
