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
        // Asegurar que el precio sea numérico
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
  
  <title>La Cocada WEB - Inicio</title>
  <meta name="description" content="Venta de dulces artesanales tradicionales: cocadas, glorias, jamoncillos y tamarindos. Envíos locales." />

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Archivo de estilos unificado -->
  <link rel="stylesheet" href="cocada.css">

</head>

<body class="min-h-full">

  <!-- Barra superior -->
  <header class="sticky tosp-0 z-30 barra-principal">
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
        <a href="index.php" class="boton boton-primario">Inicio</a>
        <a href="mayoreo.php" class="boton boton-outline">Mayoreo</a>
        <a href="promociones.html" class="boton boton-outline">Más Artículos</a>
        <a href="tienda.php" class="boton boton-outline">Administrador</a>
      </nav>

      <!-- BOTONES A LA DERECHA (CARRITO) -->
      <div class="ml-auto flex items-center gap-2">

        <!-- Botón carrito -->
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

  <main class="max-w-6xl mx-auto px-4 sm:px-6 py-6 space-y-8">

    <!-- Hero -->
    <section class="card overflow-hidden grid grid-cols-1 md:grid-cols-2">
      <div class="p-6 flex flex-col justify-center gap-4">
        <span class="badge">Envíos locales en Parral</span>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-[var(--c-cafe)] tracking-[0.04em]">
          La Cocada WEB: catálogo de dulces artesanales en línea
        </h1>

        <p class="text-base sm:text-lg text-[#59301e] leading-relaxed">
          La Cocada WEB es la versión en línea de la dulcería tradicional de Hidalgo del Parral, Chihuahua.
          Aquí el cliente puede ver el catálogo de cocadas, glorias, jamoncillos y tamarindos, agregar sus
          favoritos al carrito y preparar su pedido manteniendo el sabor casero de la tienda original.
        </p>
      </div>

      <div
        class="p-6 flex items-center bg-cover bg-center bg-no-repeat rounded-r-2xl"
        style="background-image: url('img/local.jpeg');">
      </div>
    </section>

    <!-- Filtros + catálogo -->
    <section id="catalogo" class="grid md:grid-cols-[280px,minmax(0,1fr)] gap-6 items-start">

      <!-- Columna izquierda: filtros + anuncio -->
      <aside class="space-y-5">

        <!-- Filtros -->
        <div class="card p-5 space-y-4">
          <h2 class="text-lg font-bold text-[var(--c-cafe)] tracking-[0.08em] uppercase">
            Filtros
          </h2>

          <div class="space-y-2">
            <p class="text-sm font-semibold text-[#5a371f]">Categorías</p>
            <div class="flex flex-wrap gap-2">
              <button class="chip activa" data-category="todas">Todas</button>
              <button class="chip" data-category="Coco">Coco</button>
              <button class="chip" data-category="Piña">Piña</button>
              <button class="chip" data-category="Nuez">Nuez</button>
              <button class="chip" data-category="Leche quemada">Leche quemada</button>
              <button class="chip" data-category="Tamarindo">Tamarindo</button>
              <button class="chip" data-category="Chocolate">Chocolate</button>
              <button class="chip" data-category="Guayaba">Guayaba</button>
              <button class="chip" data-category="Cacahuate">Cacahuate</button>
              <button class="chip" data-category="Jamoncillo">Jamoncillo</button>
              <button class="chip" data-category="Mixto">Mixto</button>
            </div>
          </div>

          <div class="space-y-2">
            <p class="text-sm font-semibold text-[#5a371f]">Ordenar por</p>
            <select
              id="ordenCatalogo"
              class="w-full border border-[#f4c29a] rounded-full px-3 py-2 text-sm bg-[#fff7ef] text-[#5a371f] focus:outline-none focus:ring-2 focus:ring-[#f3a766]">
              <option value="recomendado">Recomendado</option>
              <option value="precio_asc">Precio: bajo a alto</option>
              <option value="precio_desc">Precio: alto a bajo</option>
              <option value="nombre_asc">Nombre: A–Z</option>
            </select>
          </div>
        </div>

        <!-- Recuadro independiente de anuncio -->
        <div class="card p-5 space-y-4">

          <p class="text-lg font-bold text-[#5a371f]">
            Recuerdos de nuestra localidad
          </p>

          <div class="relative overflow-hidden rounded-2xl border border-[#f1b98f] bg-[#fff8f0]">
            <img
              id="souvenirImage"
              src="img/recuerdo_min.jpeg"
              alt="Recuerdos de Parralito"
              class="w-full h-48 object-cover" />

            <div class="absolute bottom-0 left-0 right-0 bg-black/40 text-[#fff8f0] text-sm px-3 py-1">
              Llévate uno de los recuerdos de la historia
            </div>
          </div>

          <button
            id="souvenirButton"
            class="boton boton-primario w-full justify-center">
            Me interesa
          </button>
        </div>

      </aside>

      <!-- Catálogo -->
      <div class="space-y-3">
        <div>
          <h2 class="text-xl font-bold text-[var(--c-cafe)] tracking-[0.06em] uppercase">
            Catálogo de productos
          </h2>
        </div>

        <div id="productsGrid" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5"></div>

        <p id="emptyState" class="hidden text-sm text-[#704127] text-center mt-2">
          No se encontraron productos con esos filtros.
        </p>
      </div>
    </section>
  </main>

  <!-- Carrito lateral (lo usa carrito.js) -->
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

        <button
          id="checkoutButton"
          class="boton boton-primario w-full justify-center"
        >
          Continuar con el pedido
        </button>

        <button
          id="clearCart"
          class="boton boton-outline w-full justify-center bg-[#fff8f0]"
        >
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
        <!-- Aquí JS insertará el resumen del carrito -->
      </div>

      <form id="paymentForm" class="space-y-3">

        <!-- Campo oculto para el método de pago -->
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

  <!-- Modal para ver imagen ampliada -->
  <div id="imgModal"
      class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50">

    <div class="relative max-w-[90%] max-h-[90%]">
      <img id="imgModalSrc" src="" class="max-w-full max-h-full rounded-xl shadow-xl">

      <!-- Botón cerrar -->
      <button id="imgModalClose"
        class="absolute top-2 right-2 bg-white/80 text-black px-3 py-1 rounded-full shadow">
        ✕
      </button>
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

  
  <div id="modalProducto"
    class="fixed inset-0 hidden bg-black/70 backdrop-blur-sm flex items-center justify-center z-50">

    <span id="closeModal"
          class="absolute top-4 right-4 text-white text-4xl font-bold cursor-pointer select-none">
        ×
    </span>

    <img id="modalImg"
        src=""
        class="max-w-[90%] max-h-[90%] object-contain rounded-lg shadow-lg"
        alt="Imagen ampliada">
  </div>

  <!-- Aquí inyectamos los productos desde la base de datos -->
  <script>
    window.PRODUCTS = <?php echo json_encode($productos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
  </script>

  <!-- Carrito -->
  <script src="carrito.js"></script>

  <script>
    const formatoMXN = new Intl.NumberFormat("es-MX",{style:"currency",currency:"MXN"});

    const PRODUCTS = window.PRODUCTS || [];

    const state = {
      query: "",
      categoria: "todas",
      orden: "recomendado"
    };

    const searchHeader = document.getElementById("searchHeader");
    const ordenSelect = document.getElementById("ordenCatalogo");
    const chips = document.querySelectorAll(".chip");
    const productsGrid = document.getElementById("productsGrid");
    const emptyState = document.getElementById("emptyState");

    function aplicarFiltros(){
      let lista = [...PRODUCTS];

      if(state.query.trim()!==""){
        const q = state.query.toLowerCase();
        lista = lista.filter(p =>
          p.nombre.toLowerCase().includes(q) ||
          p.descripcion.toLowerCase().includes(q)
        );
      }

      if(state.categoria!=="todas"){
        lista = lista.filter(p => p.categoria===state.categoria);
      }

      if(state.orden==="precio_asc"){
        lista.sort((a,b)=>a.precio-b.precio);
      } else if(state.orden==="precio_desc"){
        lista.sort((a,b)=>b.precio-a.precio);
      } else {
        lista.sort((a,b)=>a.id-b.id);
      }

      productsGrid.innerHTML = "";

      if(!lista.length){
        emptyState.classList.remove("hidden");
        return;
      }
      emptyState.classList.add("hidden");

      lista.forEach(prod=>{
        const card = document.createElement("article");
        card.className = "card overflow-hidden flex flex-col";

        const header = document.createElement("div");
        header.className = "h-32 w-full overflow-hidden rounded-t-2xl bg-[#f3d1aa]";

        const img = document.createElement("img");
        img.src = prod.imagen || "img/default.jpg";
        img.alt = prod.nombre;
        img.className = "w-full h-full object-cover cursor-pointer";

        header.appendChild(img);
        card.appendChild(header);

        const cuerpo = document.createElement("div");
        cuerpo.className = "flex-1 p-4";

        const categoria = document.createElement("span");
        categoria.className = "text-xs uppercase tracking-[0.08em] text-[#9a5a2a]";
        categoria.textContent = prod.categoria;
        cuerpo.appendChild(categoria);

        const nombre = document.createElement("h3");
        nombre.className = "mt-1 text-base font-semibold text-[#4a2614]";
        nombre.textContent = prod.nombre;
        cuerpo.appendChild(nombre);

        const desc = document.createElement("p");
        desc.className = "text-sm text-[#704127]";
        desc.textContent = prod.descripcion;
        cuerpo.appendChild(desc);

        const fila = document.createElement("div");
        fila.className = "mt-1 flex items-center justify-between";

        const precio = document.createElement("span");
        precio.className = "text-base font-semibold text-[#7a231b]";
        precio.textContent = formatoMXN.format(prod.precio);
        fila.appendChild(precio);

        const boton = document.createElement("button");
        boton.className = "boton boton-primario text-xs px-3 py-1";
        boton.textContent = "Agregar al carrito";
        boton.addEventListener("click", () => {
          Carrito.addProduct({
            id: prod.id,
            tipo: "producto",
            nombre: prod.nombre,
            precio: prod.precio
          });
        });
        fila.appendChild(boton);

        cuerpo.appendChild(fila);
        card.appendChild(cuerpo);

        productsGrid.appendChild(card);

        img.addEventListener("click", () => {
          const modal = document.getElementById("modalProducto");
          const modalImg = document.getElementById("modalImg");

          modalImg.src = prod.imagen || "img/default.jpg";
          modal.classList.remove("hidden");
        });

      });
    }

    if (searchHeader) {
      searchHeader.addEventListener("input",e=>{
        state.query = e.target.value;
        aplicarFiltros();
      });
    }

    chips.forEach(chip=>{
      chip.addEventListener("click",()=>{
        chips.forEach(c=>c.classList.remove("activa"));
        chip.classList.add("activa");
        state.categoria = chip.dataset.category;
        aplicarFiltros();
      });
    });

    ordenSelect?.addEventListener("change",e=>{
      state.orden = e.target.value;
      aplicarFiltros();
    });

    aplicarFiltros();

    const souvenirImg = document.getElementById("souvenirImage");
    const souvenirBtn = document.getElementById("souvenirButton");

    if (souvenirImg) {
      const souvenirs = [
        { src: "img/taza1.jpeg", alt: "Recuerdo típico de Parral" },
        { src: "img/taza2.jpeg", alt: "Producto especial de Parralito" },
        { src: "img/recuerdo_min.jpeg", alt: "Detalle artesanal de Parral" },
        { src: "img/recuerdo_puert.jpeg", alt: "Detalle artesanal de Parral" },
        { src: "img/recuerdo_villa.jpeg", alt: "Detalle artesanal de Parral" }
      ];

      let idx = 0;
      setInterval(() => {
        idx = (idx + 1) % souvenirs.length;
        souvenirImg.src = souvenirs[idx].src;
        souvenirImg.alt = souvenirs[idx].alt;
      }, 5000);
    }

    if (souvenirBtn) {
      souvenirBtn.addEventListener("click", () => {
        window.location.href = "promociones.html#recuerdos";
      });
    }

    const modal = document.getElementById("modalProducto");
    const closeModal = document.getElementById("closeModal");

    closeModal.addEventListener("click", () => {
      modal.classList.add("hidden");
    });

    modal.addEventListener("click", (e) => {
      if (e.target === modal) {
        modal.classList.add("hidden");
      }
    });

  </script>

</body>
</html>
