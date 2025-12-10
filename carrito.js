// carrito.js
(function () {
  const STORAGE_KEY = "cocadaCarrito";

  const formatoMXN = new Intl.NumberFormat("es-MX", {
    style: "currency",
    currency: "MXN"
  });

  const state = {
    items: [] // { id, tipo, nombre, precio, cantidad, detalle? }
  };

  // ============================================
  // Cargar / Guardar carrito
  // ============================================
  function loadCart() {
    try {
      const data = localStorage.getItem(STORAGE_KEY);
      if (data) {
        const parsed = JSON.parse(data);
        if (Array.isArray(parsed)) state.items = parsed;
      }
    } catch (e) {
      console.error("No se pudo cargar el carrito", e);
      state.items = [];
    }
  }

  function saveCart() {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(state.items));
    } catch (e) {
      console.error("No se pudo guardar el carrito", e);
    }
  }

  function getTotalCantidad() {
    return state.items.reduce((s, i) => s + i.cantidad, 0);
  }

  function getSubtotal() {
    return state.items.reduce((s, i) => s + i.cantidad * i.precio, 0);
  }

  function findIndexById(id) {
    return state.items.findIndex(i => i.id === id);
  }

  // ============================================
  // Render del carrito en pantalla
  // ============================================
  function actualizarBadgeCarrito() {
    const cartCountEls = document.querySelectorAll("#cartCount");
    const total = getTotalCantidad();
    cartCountEls.forEach(el => el.textContent = String(total));
  }

  function renderCarrito() {
    const cartItemsEl = document.getElementById("cartItems");
    const cartSubtotalEl = document.getElementById("cartSubtotal");

    actualizarBadgeCarrito();

    if (!cartItemsEl || !cartSubtotalEl) return;

    if (!state.items.length) {
      cartItemsEl.textContent = "El carrito está vacío.";
      cartSubtotalEl.textContent = formatoMXN.format(0);
      return;
    }

    cartItemsEl.innerHTML = "";
    let subtotal = 0;

    state.items.forEach(item => {
      subtotal += item.precio * item.cantidad;

      const fila = document.createElement("div");
      fila.className =
        "border border-[#f1b98f] rounded-xl p-3 flex items-center justify-between gap-3 bg-[#fff8f0]";

      const info = document.createElement("div");
      info.className = "text-sm";

      const nombre = document.createElement("p");
      nombre.className = "font-semibold text-[#5a371f]";
      nombre.textContent = item.nombre;
      info.appendChild(nombre);

      const detalle = document.createElement("p");
      detalle.className = "text-[#704127]";
      detalle.textContent =
        `${item.cantidad} x ${formatoMXN.format(item.precio)} = ${formatoMXN.format(item.precio * item.cantidad)}`;
      info.appendChild(detalle);

      if (item.tipo === "paquete" && Array.isArray(item.detalle)) {
        const detallePaquete = document.createElement("p");
        detallePaquete.className = "text-xs text-[#8b4a27]";
        detallePaquete.textContent =
          "Incluye: " +
          item.detalle.map(d => d.cantidad + "x " + d.nombre).join(", ");
        info.appendChild(detallePaquete);
      }

      fila.appendChild(info);

      const controles = document.createElement("div");
      controles.className = "flex items-center gap-2";

      const menos = document.createElement("button");
      menos.className = "boton boton-outline px-2 py-1";
      menos.textContent = "−";
      menos.addEventListener("click", () => disminuirCantidad(item.id));
      controles.appendChild(menos);

      const mas = document.createElement("button");
      mas.className = "boton boton-primario px-2 py-1";
      mas.textContent = "+";
      mas.addEventListener("click", () => aumentarCantidad(item.id));
      controles.appendChild(mas);

      fila.appendChild(controles);
      cartItemsEl.appendChild(fila);
    });

    cartSubtotalEl.textContent = formatoMXN.format(subtotal);
  }

  // ============================================
  // Modificar carrito
  // ============================================
  function aumentarCantidad(id) {
    const idx = findIndexById(id);
    if (idx === -1) return;
    state.items[idx].cantidad++;
    saveCart();
    renderCarrito();
  }

  function disminuirCantidad(id) {
    const idx = findIndexById(id);
    if (idx === -1) return;
    state.items[idx].cantidad--;
    if (state.items[idx].cantidad <= 0) {
      state.items.splice(idx, 1);
    }
    saveCart();
    renderCarrito();
  }

  function vaciarCarrito() {
    state.items = [];
    saveCart();
    renderCarrito();
  }

  // ============================================
  // Agregar productos o paquetes
  // ============================================
  function addProduct(producto) {
    if (!producto || producto.id == null) return;

    const idx = findIndexById(producto.id);
    if (idx >= 0) {
      state.items[idx].cantidad++;
    } else {
      state.items.push({
        id: producto.id,
        tipo: "producto",
        nombre: producto.nombre,
        precio: producto.precio,
        cantidad: 1
      });
    }

    saveCart();
    renderCarrito();
    abrirDrawerCarrito();
  }

  function addPackage(paquete) {
    if (!paquete || !paquete.nombre || !paquete.precio) return;

    state.items.push({
      id: "paquete-" + Date.now(),
      tipo: "paquete",
      nombre: paquete.nombre,
      precio: paquete.precio,
      cantidad: 1,
      detalle: Array.isArray(paquete.detalle) ? paquete.detalle : []
    });

    saveCart();
    renderCarrito();
    abrirDrawerCarrito();
  }

  // ============================================
  // Drawers
  // ============================================
  function abrirDrawerCarrito() {
    const drawer = document.getElementById("cartDrawer");
    if (drawer) drawer.classList.remove("hidden");
  }

  function cerrarDrawerCarrito() {
    const drawer = document.getElementById("cartDrawer");
    if (drawer) drawer.classList.add("hidden");
  }

  // ============================================
  // Eventos UI
  // ============================================
  function initEventosUI() {

    // Abrir carrito
    const cartButton = document.getElementById("cartButton");
    if (cartButton) cartButton.addEventListener("click", abrirDrawerCarrito);

    // Cerrar carrito
    const closeCart = document.getElementById("closeCart");
    if (closeCart) closeCart.addEventListener("click", cerrarDrawerCarrito);

    // Cerrar al hacer click fuera
    const cartDrawer = document.getElementById("cartDrawer");
    if (cartDrawer) {
      cartDrawer.addEventListener("click", (e) => {
        if (e.target === cartDrawer) cerrarDrawerCarrito();
      });
    }

    // Vaciar carrito
    const clearCart = document.getElementById("clearCart");
    if (clearCart) clearCart.addEventListener("click", vaciarCarrito);

    // ===============================================
    // BOTÓN → CONTINUAR PEDIDO (abre drawer de pago)
    // ===============================================
    const checkoutButton = document.getElementById("checkoutButton");
    if (checkoutButton) {
      checkoutButton.addEventListener("click", () => {
        const items = Carrito.getItems();
        if (!items.length) return alert("El carrito está vacío.");

        cerrarDrawerCarrito();

        const resumenDiv = document.getElementById("paymentResumen");
        resumenDiv.innerHTML = "";
        let total = 0;

        items.forEach(item => {
          total += item.precio * item.cantidad;

          const row = document.createElement("p");
          row.textContent = `${item.cantidad}x ${item.nombre} – $${item.precio * item.cantidad}`;
          resumenDiv.appendChild(row);
        });

        const totalP = document.createElement("p");
        totalP.className = "font-bold text-[#7a231b]";
        totalP.textContent = "Total: $" + total;
        resumenDiv.appendChild(totalP);

        document.getElementById("paymentDrawer").classList.remove("hidden");
      });
    }

    // Cerrar drawer de pago
    const closePayment = document.getElementById("closePayment");
    if (closePayment) {
      closePayment.addEventListener("click", () => {
        document.getElementById("paymentDrawer").classList.add("hidden");
      });
    }

    // ===============================================
    // ENVIAR FORMULARIO DE PAGO → pago.php
    // ===============================================
    const paymentForm = document.getElementById("paymentForm");
    if (paymentForm) {
      paymentForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(paymentForm);
        formData.append("carrito_json", JSON.stringify(Carrito.getItems()));
        formData.append("confirmar", "1");

        // <<–– AQUÍ SE VACÍA CORRECTAMENTE EL CARRITO ––>>
        localStorage.removeItem("cocadaCarrito");

        const res = await fetch("pago.php", {
          method: "POST",
          body: formData
        });

        const html = await res.text();
        document.body.innerHTML = html;
      });
    }
  }

  // ============================================
  // Exponer API pública del carrito
  // ============================================
  window.Carrito = {
    addProduct,
    addPackage,
    getItems: () => [...state.items],
    clear: vaciarCarrito,
    render: renderCarrito
  };

  // Inicializar
  loadCart();
  document.addEventListener("DOMContentLoaded", () => {
    initEventosUI();
    renderCarrito();
  });

})();
