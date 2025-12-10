<?php
session_start();

// ¿Ya está autenticado?
$adminAutenticado = isset($_SESSION['admin']) && $_SESSION['admin'] === true;
$errorLogin = false;

// 1) Procesar login (antes de cualquier otra cosa)
if (!$adminAutenticado && $_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['seccion'])) {
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($password === 'cocada20web25') {
        $_SESSION['admin'] = true;
        header('Location: tienda.php');
        exit;
    } else {
        $errorLogin = true;
        $adminAutenticado = false;
    }
}

// Si ya está autenticado, cargamos BD y preparamos lógica de secciones
$vista = 'productos'; // vista por defecto
$mensaje = '';
$mensajeError = '';
$productos = [];
$pedidos = [];

// Tablas para vista "Base de datos"
$tablaProductos         = [];
$tablaPedidos           = [];
$tablaProductosPedido   = [];
$tablaPaquetesComprados = [];
$tablaPaqueteDetalle    = [];

if ($adminAutenticado) {
    require_once 'db.php';

    // Vistas válidas
    if (isset($_GET['vista']) && in_array($_GET['vista'], ['productos', 'pedidos', 'basedatos'], true)) {
        $vista = $_GET['vista'];
    }

    // 2) Procesar acciones de formularios internos (productos / pedidos)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['seccion'])) {
        $seccion = $_POST['seccion'];
        $accion  = $_POST['accion'] ?? '';

        // Sección: productos (alta y baja)
        if ($seccion === 'productos') {
          if ($accion === 'crear') {
              $nombre      = trim($_POST['nombre'] ?? '');
              $categoria   = trim($_POST['categoria'] ?? '');
              $precio      = trim($_POST['precio'] ?? '');
              $descripcion = trim($_POST['descripcion'] ?? '');

              // Validaciones básicas
              if ($nombre === '' || $categoria === '' || $precio === '' || $descripcion === '') {
                  $mensajeError = 'Todos los campos de producto son obligatorios.';
              } elseif (!is_numeric($precio)) {
                  $mensajeError = 'El precio debe ser un número válido.';
              } elseif (!isset($_FILES['imagen_file']) || $_FILES['imagen_file']['error'] !== UPLOAD_ERR_OK) {
                  $mensajeError = 'Debes seleccionar una imagen válida para el producto.';
              } else {

                  // Procesar imagen
                  $tmpName  = $_FILES['imagen_file']['tmp_name'];
                  $origName = basename($_FILES['imagen_file']['name']);
                  $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

                  $extPermitidas = ['jpg','jpeg','png','webp'];
                  if (!in_array($ext, $extPermitidas)) {
                      $mensajeError = 'Formato de imagen no permitido. Usa JPG, PNG o WEBP.';
                  } else {
                      $nuevoNombre = 'prod_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
                      $rutaRelativa = 'img/productos/' . $nuevoNombre;
                      $rutaDestino  = __DIR__ . '/' . $rutaRelativa;

                      if (!is_dir(__DIR__ . '/img/productos')) {
                          @mkdir(__DIR__ . '/img/productos', 0775, true);
                      }

                      if (!move_uploaded_file($tmpName, $rutaDestino)) {
                          $mensajeError = 'No se pudo guardar la imagen en el servidor.';
                      } else {
                          // Insertar en la base de datos
                          $stmt = $mysqli->prepare(
                              'INSERT INTO productos (nombre, categoria, precio, descripcion, imagen)
                              VALUES (?, ?, ?, ?, ?)'
                          );
                          if ($stmt) {
                              $precioFloat = (float)$precio;
                              $stmt->bind_param('ssdss', $nombre, $categoria, $precioFloat, $descripcion, $rutaRelativa);
                              if ($stmt->execute()) {
                                  $mensaje = 'Producto agregado correctamente.';
                              } else {
                                  $mensajeError = 'Error al agregar el producto en la base de datos.';
                              }
                              $stmt->close();
                          } else {
                              $mensajeError = 'No se pudo preparar la consulta de inserción.';
                          }
                      }
                  }
              }

              $vista = 'productos';
          }
          if ($accion === 'eliminar') {

            $id = intval($_POST['id'] ?? 0);

            if ($id <= 0) {
                $mensajeError = 'ID de producto no válido.';
            } else {

                // 1) Obtener la ruta de la imagen antes de borrar
                $rutaImagen = null;

                $stmtImg = $mysqli->prepare("SELECT imagen FROM productos WHERE id = ?");
                if ($stmtImg) {
                    $stmtImg->bind_param("i", $id);
                    $stmtImg->execute();
                    $stmtImg->bind_result($rutaImagen);
                    $stmtImg->fetch();
                    $stmtImg->close();
                }

                // 2) Borrar el registro en la BD
                $stmt = $mysqli->prepare("DELETE FROM productos WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("i", $id);

                    if ($stmt->execute()) {
                        $mensaje = "Producto eliminado correctamente.";

                        // 3) Borrar la imagen física si existe
                        if ($rutaImagen) {
                            $rutaFisica = __DIR__ . "/" . $rutaImagen;
                            if (file_exists($rutaFisica)) {
                                @unlink($rutaFisica);
                            }
                        }

                    } else {
                        $mensajeError = "No se pudo eliminar el producto. Puede estar ligado a pedidos.";
                    }

                    $stmt->close();
                } else {
                    $mensajeError = "Error al preparar la eliminación del producto.";
                }
            }

            $vista = 'productos';
          }

        }
        // Sección: pedidos (actualizar estado)
        if ($seccion === 'pedidos' && $accion === 'actualizar_estado') {
            $idPedido = intval($_POST['id_pedido'] ?? 0);
            $estado   = $_POST['estado'] ?? '';

            if ($idPedido > 0 && in_array($estado, ['En proceso', 'En envío', 'Entregado'], true)) {
                $stmt = $mysqli->prepare('UPDATE pedidos SET estado = ? WHERE id = ?');
                if ($stmt) {
                    $stmt->bind_param('si', $estado, $idPedido);
                    if ($stmt->execute()) {
                        $mensaje = 'Estado del pedido actualizado correctamente.';
                    } else {
                        $mensajeError = 'Error al actualizar el estado del pedido.';
                    }
                    $stmt->close();
                } else {
                    $mensajeError = 'No se pudo preparar la consulta de actualización.';
                }
            }

            $vista = 'pedidos';
        }
    }

    // 3) Obtener datos para mostrar en las tablas principales

    // Productos (para vista productos)
    $resultProd = $mysqli->query('SELECT id, nombre, categoria, precio, imagen FROM productos ORDER BY id DESC');
    if ($resultProd) {
        while ($row = $resultProd->fetch_assoc()) {
            $productos[] = $row;
        }
        $resultProd->free();
    }

    // Pedidos (para vista pedidos)
    $resultPed = $mysqli->query('SELECT id, folio, cliente, fecha, total, estado FROM pedidos ORDER BY id DESC');
    if ($resultPed) {
        while ($row = $resultPed->fetch_assoc()) {
            $pedidos[] = $row;
        }
        $resultPed->free();
    }

    // 4) Cargar datos completos para vista "basedatos"
    if ($vista === 'basedatos') {

        // Tabla productos
        $q1 = $mysqli->query("SELECT * FROM productos ORDER BY id DESC");
        if ($q1) {
            while ($row = $q1->fetch_assoc()) {
                $tablaProductos[] = $row;
            }
            $q1->free();
        }

        // Tabla pedidos
        $q2 = $mysqli->query("SELECT * FROM pedidos ORDER BY id DESC");
        if ($q2) {
            while ($row = $q2->fetch_assoc()) {
                $tablaPedidos[] = $row;
            }
            $q2->free();
        }

        // Tabla producto_pedido (detalle de productos comprados)
        $q3 = $mysqli->query("SELECT * FROM producto_pedido ORDER BY id_pedido DESC, id_producto_pedido ASC");
        if ($q3) {
            while ($row = $q3->fetch_assoc()) {
                $tablaProductosPedido[] = $row;
            }
            $q3->free();
        }

        // Tabla paquete_comprado (paquetes que realmente se compraron)
        $q4 = $mysqli->query("SELECT * FROM paquete_comprado ORDER BY id_paquete_comprado DESC");
        if ($q4) {
            while ($row = $q4->fetch_assoc()) {
                $tablaPaquetesComprados[] = $row;
            }
            $q4->free();
        }

        // Tabla paquete_detalle (dulces dentro de cada paquete)
        $q5 = $mysqli->query("SELECT * FROM paquete_detalle ORDER BY id_detalle DESC");
        if ($q5) {
            while ($row = $q5->fetch_assoc()) {
                $tablaPaqueteDetalle[] = $row;
            }
            $q5->free();
        }
    }

}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title>La Cocada WEB - Tienda (modo administrador)</title>
  <meta name="description" content="Acceso al modo administrador de La Cocada WEB mediante contraseña.">

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Estilos unificados -->
  <link rel="stylesheet" href="cocada.css">

</head>
<body class="bg-[var(--c-bg)] min-h-screen flex flex-col">

  <!-- Barra superior -->
  <header class="sticky top-0 z-30 barra-principal">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 flex items-center gap-3 flex-wrap">

      <!-- LOGO -->
      <a href="index.html" class="flex items-center gap-3">
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

      <!-- MENÚ SOLO CUANDO NO ESTÁ AUTENTICADO -->
      <?php if (!$adminAutenticado): ?>
        <nav class="flex items-center gap-2 flex-wrap ml-2 text-xs sm:text-sm">
          <a href="index.php" class="boton boton-outline">Inicio</a>
          <a href="mayoreo.php" class="boton boton-outline">Mayoreo</a>
          <a href="promociones.html" class="boton boton-outline">Artículos</a>
          <a href="tienda.php" class="boton boton-primario">Administrador</a>
        </nav>
      <?php endif; ?>

      <!-- ZONA DERECHA (Admin información o texto de modo admin) -->
      <div class="ml-auto flex items-center gap-3">

        <?php if ($adminAutenticado): ?>

          <div class="text-xs sm:text-sm text-[#ffe3c7] text-right leading-tight">
            <div>Modo administrador activo</div>
            <div class="text-[0.75rem]">Cierra sesión para volver al sitio</div>
          </div>

          <form action="cerrar_sesion.php" method="post">
            <button class="boton boton-outline text-xs sm:text-sm">Cerrar sesión</button>
          </form>

        <?php else: ?>

          <div class="text-xs sm:text-sm text-[#ffe3c7]">
            Modo administrador
          </div>

        <?php endif; ?>

      </div>

    </div>
  </header>

<?php if (!$adminAutenticado): ?>

  <!-- Pantalla de acceso -->
  <main class="flex-1 max-w-6xl mx-auto px-4 sm:px-6 py-10 flex items-center justify-center">
    <div class="card w-full max-w-sm mx-4 p-5 sm:p-6 space-y-4">
      <div class="space-y-1 text-center">
        <p class="badge inline-block">Acceso restringido</p>
        <h2 class="text-lg sm:text-xl font-extrabold text-[var(--c-cafe)] tracking-[0.04em]">
          Ingreso al modo administrador
        </h2>
      </div>

      <form method="post" class="space-y-3">
        <div class="space-y-1">
          <label for="passwordInput" class="text-xs sm:text-sm text-[#5a371f]">
            Contraseña de administrador
          </label>
          <input
            id="passwordInput"
            name="password"
            type="password"
            required
            class="w-full border border-[#f4c29a] rounded-full px-3 py-2 text-sm bg-[#fff7ef] text-[#5a371f] placeholder:text-[#b58157] focus:outline-none focus:ring-2 focus:ring-[#f3a766]"
            placeholder="Escribe la contraseña"
          />
        </div>

        <?php if ($errorLogin): ?>
          <p class="text-xs text-red-700">Contraseña incorrecta. Inténtalo de nuevo.</p>
        <?php endif; ?>

        <button type="submit" class="boton boton-primario w-full justify-center text-sm">
          Entrar
        </button>
      </form>
    </div>
  </main>

<?php else: ?>

  <!-- Panel con secciones -->
  <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8 space-y-6">

    <!-- Tabs de secciones -->
    <section class="card p-4 sm:p-5 space-y-3">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="space-y-1">
          <span class="badge">Panel de administración</span>
          <h1 class="text-2xl sm:text-3xl font-extrabold text-[var(--c-cafe)]">
            Gestión interna de La Cocada WEB
          </h1>
        </div>
      </div>

      <div class="flex flex-wrap gap-2 mt-2">
        <a href="tienda.php?vista=productos"
          class="boton boton-outline text-xs sm:text-sm <?php echo $vista === 'productos' ? 'tab-activa' : ''; ?>">
          Productos
        </a>

        <a href="tienda.php?vista=pedidos"
          class="boton boton-outline text-xs sm:text-sm <?php echo $vista === 'pedidos' ? 'tab-activa' : ''; ?>">
          Pedidos
        </a>

        <a href="tienda.php?vista=basedatos"
          class="boton boton-outline text-xs sm:text-sm <?php echo $vista === 'basedatos' ? 'tab-activa' : ''; ?>">
          Base de datos
        </a>
      </div>


      <?php if ($mensaje !== ''): ?>
        <p class="text-sm text-green-700 mt-2"><?php echo htmlspecialchars($mensaje); ?></p>
      <?php endif; ?>
      <?php if ($mensajeError !== ''): ?>
        <p class="text-sm text-red-700 mt-2"><?php echo htmlspecialchars($mensajeError); ?></p>
      <?php endif; ?>
    </section>

    <?php if ($vista === 'productos'): ?>

    <section class="card p-5 sm:p-6 space-y-4">

      <h2 class="text-xl font-extrabold text-[var(--c-cafe)] mb-4">
        Gestión de productos
      </h2>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- COLUMNA IZQUIERDA: FORMULARIO DE AGREGAR -->
        <div class="space-y-4">

          <h3 class="text-lg font-bold text-[#5a371f]">Agregar producto</h3>

          <form method="post" enctype="multipart/form-data" class="space-y-3 text-sm">
            <input type="hidden" name="seccion" value="productos">
            <input type="hidden" name="accion" value="crear">

            <div>
              <label class="block mb-1 text-[#5a371f]">Nombre del producto</label>
              <input name="nombre" required
                class="w-full border border-[#f4c29a] rounded-full px-3 py-2 bg-[#fff7ef]">
            </div>

            <div>
              <label class="block mb-1 text-[#5a371f]">Categoría</label>
              <input name="categoria" required
                placeholder="Ej. Coco, Piña, Chocolate"
                class="w-full border border-[#f4c29a] rounded-full px-3 py-2 bg-[#fff7ef]">
            </div>

            <div>
              <label class="block mb-1 text-[#5a371f]">Precio</label>
              <input type="number" step="0.01" name="precio" required
                class="w-full border border-[#f4c29a] rounded-full px-3 py-2 bg-[#fff7ef]">
            </div>

            <div>
              <label class="block mb-1 text-[#5a371f]">Imagen</label>
              <input type="file" name="imagen_file" accept="image/*" required
                class="w-full border border-[#f4c29a] rounded-full px-3 py-2 bg-[#fff7ef]">
            </div>

            <div>
              <label class="block mb-1 text-[#5a371f]">Descripción</label>
              <textarea name="descripcion" rows="3" required
                class="w-full border border-[#f4c29a] rounded-xl px-3 py-2 bg-[#fff7ef]"></textarea>
            </div>

            <button class="boton boton-primario text-sm w-full justify-center">
              Guardar producto
            </button>
          </form>

        </div>

        <!-- COLUMNA DERECHA: LISTA DE PRODUCTOS -->
        <div>

          <h3 class="text-lg font-bold text-[#5a371f] mb-2">Productos registrados</h3>

          <div class="overflow-y-auto max-h_[600px] max-h-[600px] border border-[#f4c29a] rounded-xl bg-[#fff8f0]">

            <table class="min-w-full text-xs sm:text-sm text-left">
              <thead class="bg-[#fbe0c3] text-[#5a371f]">
                <tr>
                  <th class="px-3 py-2">ID</th>
                  <th class="px-3 py-2">Nombre</th>
                  <th class="px-3 py-2">Categoría</th>
                  <th class="px-3 py-2">Precio</th>
                  <th class="px-3 py-2">Acciones</th>
                </tr>
              </thead>

              <tbody class="divide-y divide-[#f4c29a]">

                <?php if (count($productos) === 0): ?>
                  <tr>
                    <td colspan="5" class="px-3 py-3 text-center text-[#704127]">
                      No hay productos registrados.
                    </td>
                  </tr>

                <?php else: ?>
                  <?php foreach ($productos as $p): ?>
                    <tr>
                      <td class="px-3 py-2"><?php echo $p['id']; ?></td>
                      <td class="px-3 py-2"><?php echo $p['nombre']; ?></td>
                      <td class="px-3 py-2"><?php echo $p['categoria']; ?></td>
                      <td class="px-3 py-2">$<?php echo number_format($p['precio'], 2); ?></td>

                      <td class="px-3 py-2">
                        <form method="post" onsubmit="return confirm('¿Eliminar producto?');">
                          <input type="hidden" name="seccion" value="productos">
                          <input type="hidden" name="accion" value="eliminar">
                          <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                          <button class="boton boton-outline text-xs">Eliminar</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>

              </tbody>
            </table>

          </div>

        </div>

      </div>

    </section>

    <?php endif; ?>

    <?php if ($vista === 'basedatos'): ?>
      <section class="card p-5 sm:p-6 space-y-6 text-sm">

        <h2 class="text-lg sm:text-xl font-extrabold text-[var(--c-cafe)]">
          Base de datos del sistema
        </h2>

        <!-- TABLA: PRODUCTOS -->
        <div class="border border-[#f4c29a] rounded-xl overflow-hidden">
          <button
            onclick="toggleDB('tablaProductos')"
            class="w-full text-left px-4 py-2 bg-[#fbe0c3] text-[#5a371f] font-bold">
            ▶ Productos
          </button>

          <div id="tablaProductos" class="hidden px-2 py-3 bg-[#fff8f0]">
            <div class="overflow-x-auto">
              <table class="min-w-full text-xs sm:text-sm">
                <thead>
                  <tr>
                    <?php if (!empty($tablaProductos)): ?>
                      <?php foreach (array_keys($tablaProductos[0]) as $col): ?>
                        <th class="px-3 py-2"><?php echo htmlspecialchars($col); ?></th>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <th class="px-3 py-2">Sin columnas</th>
                    <?php endif; ?>
                  </tr>
                </thead>
                <tbody class="divide-y divide-[#f4c29a]">
                  <?php if (empty($tablaProductos)): ?>
                    <tr><td class="px-3 py-2">Sin datos</td></tr>
                  <?php else: ?>
                    <?php foreach ($tablaProductos as $row): ?>
                      <tr>
                        <?php foreach ($row as $value): ?>
                          <td class="px-3 py-2"><?php echo htmlspecialchars($value); ?></td>
                        <?php endforeach; ?>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- TABLA: PEDIDOS -->
        <div class="border border-[#f4c29a] rounded-xl overflow-hidden">
          <button
            onclick="toggleDB('tablaPedidos')"
            class="w-full text-left px-4 py-2 bg-[#fbe0c3] text-[#5a371f] font-bold">
            ▶ Pedidos
          </button>

          <div id="tablaPedidos" class="hidden px-2 py-3 bg-[#fff8f0]">
            <div class="overflow-x-auto">
              <table class="min-w-full text-xs sm:text-sm">
                <thead>
                  <tr>
                    <?php if (!empty($tablaPedidos)): ?>
                      <?php foreach (array_keys($tablaPedidos[0]) as $col): ?>
                        <th class="px-3 py-2"><?php echo htmlspecialchars($col); ?></th>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <th class="px-3 py-2">Sin columnas</th>
                    <?php endif; ?>
                  </tr>
                </thead>
                <tbody class="divide-y divide-[#f4c29a]">
                  <?php if (empty($tablaPedidos)): ?>
                    <tr><td class="px-3 py-2">Sin datos</td></tr>
                  <?php else: ?>
                    <?php foreach ($tablaPedidos as $row): ?>
                      <tr>
                        <?php foreach ($row as $value): ?>
                          <td class="px-3 py-2"><?php echo htmlspecialchars($value); ?></td>
                        <?php endforeach; ?>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- TABLA: PRODUCTO_PEDIDO -->
        <div class="border border-[#f4c29a] rounded-xl overflow-hidden">
          <button
            onclick="toggleDB('tablaProductosPedido')"
            class="w-full text-left px-4 py-2 bg-[#fbe0c3] text-[#5a371f] font-bold">
            ▶ Productos comprados por pedido
          </button>

          <div id="tablaProductosPedido" class="hidden px-2 py-3 bg-[#fff8f0]">
            <div class="overflow-x-auto">
              <table class="min-w-full text-xs sm:text-sm">
                <thead>
                  <tr>
                    <?php if (!empty($tablaProductosPedido)): ?>
                      <?php foreach (array_keys($tablaProductosPedido[0]) as $col): ?>
                        <th class="px-3 py-2"><?php echo htmlspecialchars($col); ?></th>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <th class="px-3 py-2">Sin columnas</th>
                    <?php endif; ?>
                  </tr>
                </thead>
                <tbody class="divide-y divide-[#f4c29a]">
                  <?php if (empty($tablaProductosPedido)): ?>
                    <tr><td class="px-3 py-2">Sin datos</td></tr>
                  <?php else: ?>
                    <?php foreach ($tablaProductosPedido as $row): ?>
                      <tr>
                        <?php foreach ($row as $value): ?>
                          <td class="px-3 py-2"><?php echo htmlspecialchars($value); ?></td>
                        <?php endforeach; ?>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- TABLA: PAQUETE_COMPRADO -->
        <div class="border border-[#f4c29a] rounded-xl overflow-hidden">
          <button
            onclick="toggleDB('tablaPaquetesComprados')"
            class="w-full text-left px-4 py-2 bg-[#fbe0c3] text-[#5a371f] font-bold">
            ▶ Paquetes por pedido
          </button>

          <div id="tablaPaquetesComprados" class="hidden px-2 py-3 bg-[#fff8f0]">
            <div class="overflow-x-auto">
              <table class="min-w-full text-xs sm:text-sm">
                <thead>
                  <tr>
                    <?php if (!empty($tablaPaquetesComprados)): ?>
                      <?php foreach (array_keys($tablaPaquetesComprados[0]) as $col): ?>
                        <th class="px-3 py-2"><?php echo htmlspecialchars($col); ?></th>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <th class="px-3 py-2">Sin columnas</th>
                    <?php endif; ?>
                  </tr>
                </thead>
                <tbody class="divide-y divide-[#f4c29a]">
                  <?php if (empty($tablaPaquetesComprados)): ?>
                    <tr><td class="px-3 py-2">Sin datos</td></tr>
                  <?php else: ?>
                    <?php foreach ($tablaPaquetesComprados as $row): ?>
                      <tr>
                        <?php foreach ($row as $value): ?>
                          <td class="px-3 py-2"><?php echo htmlspecialchars($value); ?></td>
                        <?php endforeach; ?>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- TABLA: PAQUETE_DETALLE -->
        <div class="border border-[#f4c29a] rounded-xl overflow-hidden">
          <button
            onclick="toggleDB('tablaPaqueteDetalle')"
            class="w-full text-left px-4 py-2 bg-[#fbe0c3] text-[#5a371f] font-bold">
            ▶ Dulces dentro de cada paquete
          </button>

          <div id="tablaPaqueteDetalle" class="hidden px-2 py-3 bg-[#fff8f0]">
            <div class="overflow-x-auto">
              <table class="min-w-full text-xs sm:text-sm">
                <thead>
                  <tr>
                    <?php if (!empty($tablaPaqueteDetalle)): ?>
                      <?php foreach (array_keys($tablaPaqueteDetalle[0]) as $col): ?>
                        <th class="px-3 py-2"><?php echo htmlspecialchars($col); ?></th>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <th class="px-3 py-2">Sin columnas</th>
                    <?php endif; ?>
                  </tr>
                </thead>
                <tbody class="divide-y divide-[#f4c29a]">
                  <?php if (empty($tablaPaqueteDetalle)): ?>
                    <tr><td class="px-3 py-2">Sin datos</td></tr>
                  <?php else: ?>
                    <?php foreach ($tablaPaqueteDetalle as $row): ?>
                      <tr>
                        <?php foreach ($row as $value): ?>
                          <td class="px-3 py-2"><?php echo htmlspecialchars($value); ?></td>
                        <?php endforeach; ?>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </section>
    <?php endif; ?>


    <?php if ($vista === 'pedidos'): ?>
      <!-- Sección Pedidos -->
      <section class="card p-5 sm:p-6 space-y-4 text-sm">
        <h2 class="text-lg sm:text-xl font-extrabold text-[var(--c-cafe)]">
          Pedidos registrados y estatus
        </h2>

        <div class="overflow-x-auto mt-3">
          <table class="min-w-full text-xs sm:text-sm text-left text-[#5a371f]">
            <thead class="bg-[#fbe0c3] text-[#5a371f]">
              <tr>
                <th class="px-3 py-2">Folio</th>
                <th class="px-3 py-2">Cliente</th>
                <th class="px-3 py-2">Fecha</th>
                <th class="px-3 py-2">Total</th>
                <th class="px-3 py-2">Estado</th>
                <th class="px-3 py-2">Acción</th>
              </tr>
            </thead>

            <tbody class="bg-[#fff8f0] divide-y divide-[#f4c29a]">

              <?php if (count($pedidos) === 0): ?>
                <tr>
                  <td colspan="6" class="px-3 py-3 text-center text-[#704127]">
                    Todavía no hay pedidos registrados.
                  </td>
                </tr>

              <?php else: ?>
                <?php foreach ($pedidos as $ped): ?>

                  <?php
                    // Determinar color según estado guardado en BD
                    $estado = $ped['estado'];
                    $color = "#f4c542"; // Amarillo = En proceso
                    if ($estado === "En envío")   $color = "#3b82f6";  // Azul
                    if ($estado === "Entregado")  $color = "#16a34a";  // Verde
                  ?>

                  <tr>
                    <td class="px-3 py-2"><?php echo htmlspecialchars($ped['folio']); ?></td>
                    <td class="px-3 py-2"><?php echo htmlspecialchars($ped['cliente']); ?></td>
                    <td class="px-3 py-2"><?php echo htmlspecialchars($ped['fecha']); ?></td>
                    <td class="px-3 py-2">$<?php echo number_format($ped['total'],2); ?></td>

                    <!-- ESTADO CON BORDE DE COLOR -->
                    <td class="px-3 py-2">
                      <span style="border:2px solid <?php echo $color; ?>; color:<?php echo $color; ?>;"
                            class="px-2 py-1 rounded-full text-xs font-semibold">
                        <?php echo htmlspecialchars($estado); ?>
                      </span>
                    </td>

                    <!-- FORMULARIO PARA ACTUALIZAR ESTADO -->
                    <td class="px-3 py-2">
                      <form method="post" class="flex items-center gap-2">
                        <input type="hidden" name="seccion" value="pedidos">
                        <input type="hidden" name="accion" value="actualizar_estado">
                        <input type="hidden" name="id_pedido" value="<?php echo htmlspecialchars($ped['id']); ?>">

                        <select name="estado"
                                class="border border-[#f4c29a] rounded-full px-2 py-1 bg-[#fff7ef] text-xs">
                          <option value="En proceso"  <?php echo $estado==='En proceso'  ? 'selected' : ''; ?>>En proceso</option>
                          <option value="En envío"    <?php echo $estado==='En envío'    ? 'selected' : ''; ?>>En envío</option>
                          <option value="Entregado"   <?php echo $estado==='Entregado'   ? 'selected' : ''; ?>>Entregado</option>
                        </select>

                        <button class="boton boton-primario text-xs">Guardar</button>
                      </form>
                    </td>

                  </tr>

                <?php endforeach; ?>
              <?php endif; ?>

            </tbody>
          </table>
        </div>
      </section>
    <?php endif; ?>

  </main>

<?php endif; ?>

  <footer class="mt-8 border-t border-[#f1b98f] bg-[#c94c35]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-5 text-xs sm:text-sm text-[#ffe7cf] flex flex-col md:flex-row items-center justify-between gap-2">
      <span>La Cocada WEB · Panel de administración</span>
      <span>Uso interno de la dulcería</span>
    </div>
  </footer>

  <script>
    const input = document.getElementById("passwordInput");
    if (input) {
      window.addEventListener("load", () => input.focus());
    }
  </script>
  <script>
    function toggleDB(id) {
      const box = document.getElementById(id);
      box.classList.toggle("hidden");
    }
    </script>


</body>
</html>
