<?php
require_once 'db.php';

$productos = [];

$result = $mysqli->query(
  "SELECT id, nombre, categoria, precio, descripcion, imagen 
   FROM productos 
   ORDER BY id ASC"
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['precio'] = (float)$row['precio'];
        $productos[] = $row;
    }
    $result->free();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title>La Cocada WEB - Mayoreo</title>
  <meta name="description" content="Paquetes de dulces para mayoreo, eventos y escuelas." />

  <script src="https://cdn.tailwindcss.com"></script>

  <link rel="stylesheet" href="cocada.css">
</head>
<body class="bg-[var(--c-bg)] min-h-screen">

  <header class="sticky top-0 z-30 barra-principal">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 flex items-center gap-3 flex-wrap">

      <!-- LOGO -->
      <a href="index.php" class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-full bg-[#f7d3a6] border-2 border-[#7a231b] grid place-content-center">
          <span class="text-xs tracking-[0.25em] text-[#7a231b] font-semibold">LA</span>
        </div>

        <div class="flex flex-col leading-tight">
          <span class="text-xl sm:text-2xl font-extrabold text-[#ffecdd] tracking-[0.18em] uppercase">
            Cocada
          </span>
          <span class="text-[0.7rem] sm:text-xs text-[#ffe3c7] tracking-[0.22em] uppercase">
            Dulces de leche
          </span>
        </div>
      </a>

      <!-- MENÚ PRINCIPAL -->
      <nav class="flex items-center gap-2 flex-wrap ml-2 text-xs sm:text-sm">
        <a href="index.php" class="boton boton-outline">Inicio</a>
        <a href="mayoreo.php" class="boton boton-primario">Mayoreo</a>
        <a href="promociones.html" class="boton boton-outline">Más Artículos</a>
        <a href="tienda.php" class="boton boton-outline">Administrador</a>
      </nav>

      <!-- BOTONES LATERALES (PAQUETE + CARRITO) -->
      <div class="ml-auto flex items-center gap-2">

        <button id="packageButton" class="boton boton-outline relative text-xs sm:text-sm" aria-label="Abrir paquete">
          <span>Paquete</span>
          <span id="packageCount"
                class="absolute -top-2 -right-2 bg-[#7a231b] text-white text-[0.65rem] rounded-full px-1.5 py-0.5">
            0
          </span>
        </button>

        <button id="cartButton" class="boton boton-outline relative text-xs sm:text-sm" aria-label="Abrir carrito">
          <span aria-hidden="true" class="text-xl">🛒</span>
          <span>Carrito</span>
          <span id="cartCount"
                class="absolute -top-2 -right-2 bg-[#7a231b] text-white text-[0.65rem] rounded-full px-1.5 py-0.5">
            0
          </span>
        </button>

      </div>

    </div>
  </header>

  <main class="max-w-6xl mx-auto px-4 sm:px-6 py-6 space-y-6">
    <section class="space-y-3">
      <span class="badge">Sección de mayoreo</span>
      <h1 class="text-2xl sm:text-3xl font-extrabold text-[var(--c-cafe)] tracking-[0.04em]">
        Paquetes de dulces para mayoreo
      </h1>
      <p class="text-sm sm:text-base text-[#704127] leading-relaxed">
        Elige un paquete (pequeño, mediano, grande o jumbo) y luego selecciona qué dulces llevará. Los dulces que
        agregues se guardan en un paquete especial y, al finalizar, puedes agregar todo el paquete al carrito general
        para seguir con el proceso de pedido.
      </p>
    </section>

    <!-- Selección de paquetes -->
    <section class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 text-sm">
      <article class="card p-4 space-y-3" data-package-card="pequeno">
        <div class="space-y-2">
          <h2 class="text-base font-semibold text-[var(--c-cafe)]">
            Paquete pequeño
          </h2>
          <p class="text-sm text-[#704127]">
            Incluye hasta 10 dulces a elección. Ideal para detalles, regalos pequeños o reuniones chicas.
          </p>
          <p class="text-sm font-semibold text-[#7a231b]">
            Máximo: 10 dulces
          </p>
        </div>
        <button class="boton boton-primario w-full justify-center text-xs" data-package-select="pequeno">
          Seleccionar paquete
        </button>
      </article>

      <article class="card p-4 space-y-3" data-package-card="mediano">
        <div class="space-y-2">
          <h2 class="text-base font-semibold text-[var(--c-cafe)]">
            Paquete mediano
          </h2>
          <p class="text-sm text-[#704127]">
            Incluye hasta 20 dulces a elección. Pensado para familias o grupos pequeños.
          </p>
          <p class="text-sm font-semibold text-[#7a231b]">
            Máximo: 20 dulces
          </p>
        </div>
        <button class="boton boton-primario w-full justify-center text-xs" data-package-select="mediano">
          Seleccionar paquete
        </button>
      </article>

      <article class="card p-4 space-y-3" data-package-card="grande">
        <div class="space-y-2">
          <h2 class="text-base font-semibold text-[var(--c-cafe)]">
            Paquete grande
          </h2>
          <p class="text-sm text-[#704127]">
            Incluye hasta 25 dulces a elección. Útil para pequeños eventos, cumpleaños o reuniones escolares.
          </p>
          <p class="text-sm font-semibold text-[#7a231b]">
            Máximo: 25 dulces
          </p>
        </div>
        <button class="boton boton-primario w-full justify-center text-xs" data-package-select="grande">
          Seleccionar paquete
        </button>
      </article>

      <article class="card p-4 space-y-3" data-package-card="jumbo">
        <div class="space-y-2">
          <h2 class="text-base font-semibold text-[var(--c-cafe)]">
            Paquete jumbo
          </h2>
          <p class="text-sm text-[#704127]">
            Incluye hasta 40 dulces a elección. Recomendado para eventos, fiestas o compras de mayoreo.
          </p>
          <p class="text-sm font-semibold text-[#7a231b]">
            Máximo: 40 dulces
          </p>
        </div>
        <button class="boton boton-primario w-full justify-center text-xs" data-package-select="jumbo">
          Seleccionar paquete
        </button>
      </article>
    </section>

    <!-- Catálogo de dulces para armar el paquete -->
    <section id="catalogo-mayoreo" class="space-y-3">
      <div>
        <h2 class="text-xl font-bold text-[var(--c-cafe)] tracking-[0.06em] uppercase">
          Catálogo de dulces para mayoreo
        </h2>
        <p class="text-sm text-[#704127] mt-1">
          Usa los botones para agregar dulces a tu paquete. Después, podrás agregar el paquete completo al carrito.
        </p>
      </div>

      <div id="productsGrid" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 text-sm"></div>

      <p id="emptyState" class="hidden text-sm text-[#704127] text-center mt-2">
        No hay dulces disponibles.
      </p>
    </section>
  </main>

  <!-- Barra lateral del paquete -->
  <div id="packageDrawer" class="fixed inset-0 z-40 hidden">
    <div class="absolute inset-0 bg-black/40"></div>
    <aside class="absolute right-0 top-0 h-full w-full max-w-sm bg-[#fff8f0] shadow-xl flex flex-col border-l border-[#f1b98f]">
      <div class="px-5 py-4 border-b border-[#f1b98f] flex items-center justify-between bg-[#c94c35] text-[#ffe7cf]">
        <div>
          <h3 class="text-lg font-bold tracking-[0.08em] uppercase">Paquete seleccionado</h3>
          <p id="packageInfo" class="text-xs mt-1 text-[#ffe7cf]"></p>
        </div>
        <button id="closePackage" class="boton boton-outline bg-[#ffede0] text-[#7a231b] border-none">
          Cerrar
        </button>
      </div>

      <div id="packageItems" class="flex-1 overflow-y-auto px-5 py-4 text-sm text-[#5a371f] space-y-3">
        No hay dulces en el paquete.
      </div>

      <div class="border-t border-[#f1b98f] px-5 py-4 space-y-3 bg-[#ffe7cf] text-sm">
        <p id="packageSummary" class="text-[#5a371f] font-semibold">
          Selecciona un paquete para comenzar.
        </p>
        <button id="addPackageToCart" class="boton boton-primario w-full justify-center text-sm">
          Agregar paquete al carrito
        </button>
        <p class="text-xs text-[#8b4a27]">
          El contenido exacto y los precios finales se confirman directamente con la dulcería.
        </p>
      </div>
    </aside>
  </div>

  <!-- Carrito lateral -->
  <div id="cartDrawer" class="fixed inset-0 z-40 hidden">
    <div class="absolute inset-0 bg-black/40"></div>
    <aside class="absolute right-0 top-0 h-full w-full max-w-sm bg-[#fff8f0] shadow-xl flex flex-col border-l border-[#f1b98f]">
      <div class="px-5 py-4 border-b border-[#f1b98f] flex items-center justify-between bg-[#c94c35] text-[#ffe7cf]">
        <h3 class="text-lg font-bold tracking-[0.08em] uppercase">Carrito</h3>
        <button id="closeCart" class="boton boton-outline bg-[#ffede0] text-[#7a231b] border-none">
          Cerrar
        </button>
      </div>

      <div id="cartItems" class="flex-1 overflow-y-auto px-5 py-4 text-sm text-[#5a371f] space-y-3">
        El carrito está vacío.
      </div>

      <div class="border-t border-[#f1b98f] px-5 py-4 space-y-3 bg-[#ffe7cf]">
        <div class="flex items-center justify-between text-base">
          <span class="text-[#5a371f] font-semibold">Subtotal</span>
          <span id="cartSubtotal" class="font-bold text-[#7a231b]">$0.00</span>
        </div>

        <button id="checkoutButton" class="boton boton-primario w-full justify-center">
          Continuar con el pedido
        </button>

        <button id="clearCart" class="boton boton-outline w-full justify-center bg-[#fff8f0]">
          Vaciar carrito
        </button>
      </div>

    </aside>
  </div>

  <!-- Drawer de pago -->
  <div id="paymentDrawer"
    class="hidden fixed inset-0 bg-black/40 flex justify-end z-50">

    <div class="w-full max-w-sm h-full bg-[#fff8f0] shadow-xl p-5 overflow-y-auto">

      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-[#5a371f]">Pago con tarjeta</h2>
        <button id="closePayment" class="text-[#7a231b] text-lg font-bold">
          ✕
        </button>
      </div>

      <div id="paymentResumen" class="space-y-2 mb-4 text-sm text-[#5a371f]">
      </div>

      <form id="paymentForm" class="space-y-3">

        <input type="hidden" name="metodo_pago" value="tarjeta">

        <div>
          <label class="text-sm">Nombre completo</label>
          <input name="cliente" required
            class="w-full border border-[#f4c29a] rounded-full px-3 py-2 bg-white">
        </div>

        <div>
          <label class="text-sm">Teléfono</label>
          <input name="telefono" maxlength="10" pattern="\d{10}" required
            class="w-full border border-[#f4c29a] rounded-full px-3 py-2 bg-white">
        </div>
        <div>
          <label class="text-sm">Domicilio</label>
          <input name="domicilio" required
            placeholder="Calle, número, colonia, ciudad"
            class="w-full border border-[#f4c29a] rounded-full px-3 py-2 bg-white">
        </div>

        <div>
          <label class="text-sm">Número de tarjeta</label>
          <input name="tarjeta" maxlength="16" pattern="\d{16}" required
            placeholder="XXXXXXXXXXXXXXXX"
            class="w-full border border-[#f4c29a] rounded-full px-3 py-2 bg-white">
        </div>

        <div class="flex gap-3">
          <div class="flex-1">
            <label class="text-sm">Expiración</label>
            <input name="expiracion" maxlength="5" placeholder="MM/YY" required
              class="w-full border border-[#f4c29a] rounded-full px-3 py-2 bg-white">
          </div>

          <div class="w-24">
            <label class="text-sm">CVV</label>
            <input name="cvv" maxlength="3" pattern="\d{3}" required
              class="w-full border border-[#f4c29a] rounded-full px-3 py-2 bg-white">
          </div>
        </div>

        <div>
          <label class="text-sm">Comentarios (opcional)</label>
          <textarea name="comentarios" rows="3"
            class="w-full border border-[#f4c29a] rounded-xl px-3 py-2 bg-white"></textarea>
        </div>

        <button type="submit"
          class="boton boton-primario w-full justify-center bg-[#c94c35] text-[#ffe7cf]">
          Confirmar pago
        </button>
      </form>
    </div>
  </div>

  <footer class="mt-10 barra-footer">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-5 text-xs sm:text-sm text-[#ffe7cf] flex flex-col md:flex-row items-center justify-between gap-4">

      <div class="text-center md:text-left leading-tight">
        <span>La Cocada WEB · Dulces artesanales de Parral</span><br>
        <span>Hecho con cariño para nuestros clientes</span>
      </div>

      <div class="text-center md:text-right leading-tight">
        <span class="block">Ubicación del local:</span>
        <span class="block">
          Ignacio Zaragoza & Galeana, Centro, 33800<br>
          Hidalgo del Parral, Chih.
        </span>
        <span class="block mt-1">Teléfono: +52 627 143 5223</span>
      </div>

    </div>
  </footer>


  <!-- Inyectar productos desde la base de datos -->
  <script>
    window.PRODUCTS = <?php echo json_encode($productos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
  </script>

  <script src="carrito.js"></script>

  <script>
    const formatoMXN = new Intl.NumberFormat("es-MX",{style:"currency",currency:"MXN"});
    const PRODUCTS = window.PRODUCTS || [];

    const PACKAGE_CONFIG = {
      pequeno: { nombre: "Paquete pequeño", max: 10 },
      mediano: { nombre: "Paquete mediano", max: 20 },
      grande:  { nombre: "Paquete grande",  max: 25 },
      jumbo:   { nombre: "Paquete jumbo",   max: 40 }
    };

    const statePaquete = {
      tipo: null,
      maxDulces: 0,
      items: []
    };

    const productsGrid      = document.getElementById("productsGrid");
    const emptyState        = document.getElementById("emptyState");
    const packageButton     = document.getElementById("packageButton");
    const packageDrawer     = document.getElementById("packageDrawer");
    const closePackage      = document.getElementById("closePackage");
    const packageItems      = document.getElementById("packageItems");
    const packageInfo       = document.getElementById("packageInfo");
    const packageSummary    = document.getElementById("packageSummary");
    const packageCount      = document.getElementById("packageCount");
    const addPackageToCart  = document.getElementById("addPackageToCart");
    const packageCards      = document.querySelectorAll("[data-package-card]");
    const packageSelectBtns = document.querySelectorAll("[data-package-select]");
    const catalogoSection   = document.getElementById("catalogo-mayoreo");

    const cartButton   = document.getElementById("cartButton");
    const cartDrawer   = document.getElementById("cartDrawer");
    const closeCart    = document.getElementById("closeCart");

    function renderProducts() {
      productsGrid.innerHTML = "";
      if (!PRODUCTS.length) {
        emptyState.classList.remove("hidden");
        return;
      }
      emptyState.classList.add("hidden");

      PRODUCTS.forEach(prod => {
        const card = document.createElement("article");
        card.className = "card p-4 flex gap-3 items-stretch";

        const columnaTexto = document.createElement("div");
        columnaTexto.className = "flex-1 flex flex-col gap-1";

        const titulo = document.createElement("h3");
        titulo.className = "text-base font-semibold text-[var(--c-cafe)]";
        titulo.textContent = prod.nombre;
        columnaTexto.appendChild(titulo);

        const categoria = document.createElement("p");
        categoria.className = "text-xs text-[#8b4a27] uppercase tracking-wide";
        categoria.textContent = prod.categoria;
        columnaTexto.appendChild(categoria);

        const desc = document.createElement("p");
        desc.className = "text-sm text-[#704127]";
        desc.textContent = prod.descripcion;
        columnaTexto.appendChild(desc);

        const fila = document.createElement("div");
        fila.className = "mt-1 flex items-center justify-between";

        const precio = document.createElement("span");
        precio.className = "text-base font-semibold text-[#7a231b]";
        precio.textContent = formatoMXN.format(prod.precio);
        fila.appendChild(precio);

        const bPaquete = document.createElement("button");
        bPaquete.className = "boton boton-primario !text-[0.65rem] !px-1.5 !py-0.5";
        bPaquete.textContent = "Agregar al paquete";
        bPaquete.addEventListener("click", () => agregarAlPaquete(prod.id));
        fila.appendChild(bPaquete);

        columnaTexto.appendChild(fila);

        const columnaImagen = document.createElement("div");
        columnaImagen.className =
          "w-24 sm:w-28 md:w-32 rounded-2xl overflow-hidden flex items-center justify-center bg-[#f3d1aa]";

        if (prod.imagen) {
          const img = document.createElement("img");
          img.src = prod.imagen;
          img.alt = prod.nombre;
          img.className = "w-full h-full object-cover";
          columnaImagen.appendChild(img);
        } else {
          const textoImg = document.createElement("span");
          textoImg.className = "text-xs text-[#8b4a27] text-center px-2";
          textoImg.textContent = prod.categoria;
          columnaImagen.appendChild(textoImg);
        }

        card.appendChild(columnaTexto);
        card.appendChild(columnaImagen);
        productsGrid.appendChild(card);
      });
    }

    function seleccionarPaquete(tipo) {
      const config = PACKAGE_CONFIG[tipo];
      if (!config) return;

      if (statePaquete.tipo && statePaquete.items.length > 0 && statePaquete.tipo !== tipo) {
        const cambiar = confirm("Si cambias de tipo de paquete, se vaciarán los dulces seleccionados. ¿Continuar?");
        if (!cambiar) return;
      }

      statePaquete.tipo = tipo;
      statePaquete.maxDulces = config.max;
      statePaquete.items = [];

      packageCards.forEach(card => {
        card.classList.remove("card-paquete-activo");
        if (card.dataset.packageCard === tipo) card.classList.add("card-paquete-activo");
      });

      actualizarPaqueteUI();

      if (catalogoSection) catalogoSection.scrollIntoView({behavior:"smooth"});
    }

    function totalDulcesPaquete() {
      return statePaquete.items.reduce((s, i) => s + i.cantidad, 0);
    }

    function actualizarPaqueteUI() {
      const tipo = statePaquete.tipo;

      if (!tipo) {
        packageInfo.textContent = "";
        packageItems.textContent = "No hay dulces en el paquete.";
        packageSummary.textContent = "Selecciona un paquete para comenzar.";
        packageCount.textContent = "0";
        return;
      }

      const config = PACKAGE_CONFIG[tipo];
      const total = totalDulcesPaquete();
      const restantes = config.max - total;

      packageInfo.textContent = config.nombre + " – " + config.max + " dulces máximo.";
      packageCount.textContent = String(total);

      if (!statePaquete.items.length) {
        packageItems.textContent = "Todavía no has agregado dulces a este paquete.";
      } else {
        packageItems.innerHTML = "";
        statePaquete.items.forEach(item => {
          const fila = document.createElement("div");
          fila.className = "border border-[#f1b98f] rounded-xl p-3 flex items-center justify-between gap-3 bg-[#fff8f0]";

          const info = document.createElement("div");
          info.className = "text-sm";

          const nombre = document.createElement("p");
          nombre.className = "font-semibold text-[#5a371f]";
          nombre.textContent = item.nombre;
          info.appendChild(nombre);

          const detalle = document.createElement("p");
          detalle.className = "text-[#704127]";
          detalle.textContent = item.cantidad + " dulces";
          info.appendChild(detalle);

          fila.appendChild(info);

          const controles = document.createElement("div");
          controles.className = "flex items-center gap-2";

          const menos = document.createElement("button");
          menos.className = "boton boton-outline px-2 py-1";
          menos.textContent = "−";
          menos.addEventListener("click", () => {
            item.cantidad--;
            if (item.cantidad <= 0) {
              statePaquete.items = statePaquete.items.filter(i => i.id !== item.id);
            }
            actualizarPaqueteUI();
          });
          controles.appendChild(menos);

          const mas = document.createElement("button");
          mas.className = "boton boton-primario px-2 py-1";
          mas.textContent = "+";
          mas.addEventListener("click", () => {
            const totalActual = totalDulcesPaquete();
            if (totalActual >= config.max) {
              alert("Ya alcanzaste el número máximo de dulces para este paquete.");
              return;
            }
            item.cantidad++;
            actualizarPaqueteUI();
          });
          controles.appendChild(mas);

          fila.appendChild(controles);
          packageItems.appendChild(fila);
        });
      }

      packageSummary.textContent =
        "Llevas " + total + " de " + config.max + " dulces. Te faltan " + restantes + " para completar el paquete.";
    }

    function agregarAlPaquete(id) {
      if (!statePaquete.tipo) {
        alert("Primero selecciona un tipo de paquete.");
        return;
      }

      const config = PACKAGE_CONFIG[statePaquete.tipo];
      const prod = PRODUCTS.find(p => p.id === id);
      if (!prod) return;

      const totalActual = totalDulcesPaquete();
      if (totalActual >= config.max) {
        alert("Ya alcanzaste el número máximo de dulces para este paquete.");
        return;
      }

      const idx = statePaquete.items.findIndex(i => i.id === id);
      if (idx >= 0) {
        statePaquete.items[idx].cantidad += 1;
      } else {
        statePaquete.items.push({ id: prod.id, nombre: prod.nombre, cantidad: 1 });
      }

      actualizarPaqueteUI();
      packageDrawer.classList.remove("hidden");
    }

    function agregarPaqueteAlCarrito() {
      if (!statePaquete.tipo || statePaquete.items.length === 0) {
        alert("Primero selecciona un paquete y agrega dulces.");
        return;
      }

      const config = PACKAGE_CONFIG[statePaquete.tipo];
      const totalDulces = totalDulcesPaquete();
      if (totalDulces === 0) {
        alert("El paquete está vacío.");
        return;
      }

      let totalPrecio = 0;
      statePaquete.items.forEach(item => {
        const prod = PRODUCTS.find(p => p.id === item.id);
        if (prod) totalPrecio += prod.precio * item.cantidad;
      });

      const nombrePaquete = config.nombre + " (" + totalDulces + " dulces)";

      const detalle = statePaquete.items.map(item => ({
        idProducto: item.id,
        nombre: item.nombre,
        cantidad: item.cantidad
      }));

      if (window.Carrito) {
        Carrito.addPackage({
          nombre: nombrePaquete,
          precio: totalPrecio,
          detalle: detalle
        });
      }

      statePaquete.items = [];
      actualizarPaqueteUI();
      packageDrawer.classList.add("hidden");
    }

    packageButton.addEventListener("click", () => packageDrawer.classList.remove("hidden"));
    closePackage.addEventListener("click", () => packageDrawer.classList.add("hidden"));
    packageDrawer.addEventListener("click", e => {
      if (e.target === packageDrawer) packageDrawer.classList.add("hidden");
    });

    addPackageToCart.addEventListener("click", agregarPaqueteAlCarrito);

    packageSelectBtns.forEach(btn => {
      btn.addEventListener("click", () => {
        const tipo = btn.dataset.packageSelect;
        seleccionarPaquete(tipo);
        packageDrawer.classList.remove("hidden");
      });
    });

    cartButton.addEventListener("click", () => cartDrawer.classList.remove("hidden"));
    closeCart.addEventListener("click", () => cartDrawer.classList.add("hidden"));
    cartDrawer.addEventListener("click", e => {
      if (e.target === cartDrawer) cartDrawer.classList.add("hidden");
    });

    renderProducts();
    actualizarPaqueteUI();
  </script>
</body>
</html>
