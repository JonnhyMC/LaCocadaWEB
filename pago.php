<?php
// pago.php
require_once 'db.php';

// Solo aceptar POST con "confirmar"
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['confirmar'])) {
    header('Location: index.php');
    exit;
}

// =====================================================
// 1) Leer datos del formulario
// =====================================================
$cliente     = trim($_POST['cliente'] ?? '');
$telefono    = trim($_POST['telefono'] ?? '');
$domicilio   = trim($_POST['domicilio'] ?? '');
$comentarios = trim($_POST['comentarios'] ?? '');
$metodo_pago = trim($_POST['metodo_pago'] ?? 'tarjeta');

// Datos de tarjeta SOLO para validación, NO se guardan
$tarjeta    = trim($_POST['tarjeta'] ?? '');
$expiracion = trim($_POST['expiracion'] ?? '');
$cvv        = trim($_POST['cvv'] ?? '');

// Carrito enviado desde carrito.js
$carrito_json = $_POST['carrito_json'] ?? '';
$items = json_decode($carrito_json, true);

// =====================================================
// 2) Validaciones de formulario (lado servidor)
// =====================================================
$errores = [];

// Campos obligatorios
if ($cliente === '')   $errores[] = "Debes escribir el nombre completo.";
if ($telefono === '')  $errores[] = "Debes escribir el número de teléfono.";
if ($domicilio === '') $errores[] = "Debes escribir el domicilio de entrega.";
if ($tarjeta === '')   $errores[] = "Debes escribir el número de tarjeta.";
if ($expiracion === '')$errores[] = "Debes escribir la fecha de expiración de la tarjeta.";
if ($cvv === '')       $errores[] = "Debes escribir el código de seguridad (CVV).";

// Teléfono: exactamente 10 dígitos
if ($telefono !== '' && !preg_match('/^\d{10}$/', $telefono)) {
    $errores[] = "El teléfono debe tener exactamente 10 dígitos numéricos.";
}

// Tarjeta: 16 dígitos (ignorando espacios y guiones)
$tarjetaSoloDigitos = preg_replace('/\D/', '', $tarjeta);
if ($tarjeta !== '' && !preg_match('/^\d{16}$/', $tarjetaSoloDigitos)) {
    $errores[] = "El número de tarjeta debe tener exactamente 16 dígitos.";
}

// Expiración: formato MM/YY con mes 01–12
if ($expiracion !== '' && !preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiracion)) {
    $errores[] = "La fecha de expiración debe tener el formato MM/YY y un mes válido.";
}

// CVV: exactamente 3 dígitos
if ($cvv !== '' && !preg_match('/^\d{3}$/', $cvv)) {
    $errores[] = "El CVV debe tener exactamente 3 dígitos numéricos.";
}

// Carrito válido
if (!is_array($items) || count($items) === 0) {
    $errores[] = "El carrito está vacío o no se recibió correctamente.";
}

// Si hay errores, mostramos página de error amigable
if (count($errores) > 0) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
      <meta charset="utf-8">
      <title>Datos incorrectos - La Cocada WEB</title>
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <script src="https://cdn.tailwindcss.com"></script>
      <link rel="stylesheet" href="cocada.css">
    </head>
    <body class="bg-[var(--c-bg)] min-h-screen flex items-center justify-center">
      <div class="card max-w-md w-full p-6 space-y-4">
        <h1 class="text-2xl font-extrabold text-[var(--c-cafe)] text-center">
          Revisa los datos del pago
        </h1>

        <p class="text-sm text-[#704127] text-center">
          Encontramos algunos problemas con la información capturada. Corrige lo siguiente e intenta de nuevo.
        </p>

        <ul class="text-sm text-[#7a231b] list-disc list-inside space-y-1 text-left">
          <?php foreach ($errores as $err): ?>
            <li><?php echo htmlspecialchars($err); ?></li>
          <?php endforeach; ?>
        </ul>

        <p class="text-xs text-[#8b4a27] text-center">
          Por seguridad, los datos de tarjeta no se guardan en el sistema. Vuelve a escribirlos al regresar.
        </p>

        <button onclick="window.history.back();"
                class="boton boton-primario w-full justify-center mt-2">
          Volver al formulario de pago
        </button>
      </div>
    </body>
    </html>
    <?php
    exit;
}

// =====================================================
// 3) Calcular total del pedido
// =====================================================
$total = 0;
foreach ($items as $item) {
    $cantidad = isset($item['cantidad']) ? (int)$item['cantidad'] : 1;
    $precio   = isset($item['precio'])   ? (float)$item['precio']   : 0;
    $total   += $cantidad * $precio;
}

// =====================================================
// 4) Datos del pedido
// =====================================================
$folio  = 'LC-' . date('Ymd-His');
$fecha  = date('Y-m-d H:i:s');
$estado = 'En proceso';

// =====================================================
// 5) Transacción: pedidos + producto_pedido + paquetes
// =====================================================
$mysqli->begin_transaction();

try {

    // 5.1 Insertar pedido principal en "pedidos"
    // pedidos(id, folio, cliente, telefono, domicilio, metodo_pago, comentarios, fecha, total, estado)
    $stmt = $mysqli->prepare(
        "INSERT INTO pedidos
         (folio, cliente, telefono, domicilio, metodo_pago, comentarios, fecha, total, estado)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    if (!$stmt) {
        throw new Exception("Error al preparar INSERT en pedidos: " . $mysqli->error);
    }

    $stmt->bind_param(
        "sssssssds",
        $folio,
        $cliente,
        $telefono,
        $domicilio,
        $metodo_pago,
        $comentarios,
        $fecha,
        $total,
        $estado
    );

    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar INSERT en pedidos: " . $stmt->error);
    }

    $id_pedido = $stmt->insert_id;
    $stmt->close();

    // 5.2 Preparar sentencias de detalle

    // 5.2.a Productos sueltos:
    // producto_pedido(id_producto_pedido, id_pedido, id_producto, nombre_producto, cantidad, precio_unitario, subtotal)
    $stmtProd = $mysqli->prepare(
        "INSERT INTO producto_pedido
         (id_pedido, id_producto, nombre_producto, cantidad, precio_unitario, subtotal)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    if (!$stmtProd) {
        throw new Exception("Error al preparar INSERT en producto_pedido: " . $mysqli->error);
    }

    // 5.2.b Paquetes comprados:
    // paquete_comprado(id_paquete_comprado, id_pedido, nombre_paquete, total_dulces, total_paquete, fecha_compra)
    $stmtPack = $mysqli->prepare(
        "INSERT INTO paquete_comprado
         (id_pedido, nombre_paquete, total_dulces, total_paquete, fecha_compra)
         VALUES (?, ?, ?, ?, ?)"
    );
    if (!$stmtPack) {
        throw new Exception("Error al preparar INSERT en paquete_comprado: " . $mysqli->error);
    }

    // 5.2.c Detalle de cada paquete:
    // paquete_detalle(id_detalle, id_paquete_comprado, id_producto, nombre_producto, cantidad)
    $stmtPackDet = $mysqli->prepare(
        "INSERT INTO paquete_detalle
         (id_paquete_comprado, id_producto, nombre_producto, cantidad)
         VALUES (?, ?, ?, ?)"
    );
    if (!$stmtPackDet) {
        throw new Exception("Error al preparar INSERT en paquete_detalle: " . $mysqli->error);
    }

    // 5.3 Recorrer items del carrito
    foreach ($items as $item) {
        $tipo   = $item['tipo']   ?? 'producto';
        $nombre = $item['nombre'] ?? '';
        $cant   = isset($item['cantidad']) ? (int)$item['cantidad'] : 1;
        $precio = isset($item['precio'])   ? (float)$item['precio'] : 0;
        $subtotalLinea = $cant * $precio;

        if ($tipo === 'producto') {

            // Producto individual
            $idProducto = (isset($item['id']) && is_numeric($item['id']))
                          ? (int)$item['id']
                          : 0;

            $nombreProd = $nombre;

            $stmtProd->bind_param(
                "iisidd",
                $id_pedido,
                $idProducto,
                $nombreProd,
                $cant,
                $precio,
                $subtotalLinea
            );

            if (!$stmtProd->execute()) {
                throw new Exception("Error al insertar en producto_pedido: " . $stmtProd->error);
            }

        } elseif ($tipo === 'paquete') {

            // Paquete armado en mayoreo
            $detalle = (isset($item['detalle']) && is_array($item['detalle']))
                       ? $item['detalle']
                       : [];

            // Total de dulces dentro del paquete
            $totalDulces = 0;
            foreach ($detalle as $d) {
                $totalDulces += (int)($d['cantidad'] ?? 0);
            }

            $fecha_compra_paquete = $fecha;

            $stmtPack->bind_param(
                "isids",
                $id_pedido,
                $nombre,
                $totalDulces,
                $subtotalLinea,
                $fecha_compra_paquete
            );

            if (!$stmtPack->execute()) {
                throw new Exception("Error al insertar en paquete_comprado: " . $stmtPack->error);
            }

            $id_paquete_comprado = $stmtPack->insert_id;

            // Detalle de cada dulce dentro del paquete
            foreach ($detalle as $d) {
                $idProdDet = (isset($d['idProducto']) && is_numeric($d['idProducto']))
                             ? (int)$d['idProducto']
                             : 0;
                $nomDet  = $d['nombre'] ?? '';
                $cantDet = (int)($d['cantidad'] ?? 0);

                $stmtPackDet->bind_param(
                    "iisi",
                    $id_paquete_comprado,
                    $idProdDet,
                    $nomDet,
                    $cantDet
                );

                if (!$stmtPackDet->execute()) {
                    throw new Exception("Error al insertar en paquete_detalle: " . $stmtPackDet->error);
                }
            }
        }
    }

    $stmtProd->close();
    $stmtPack->close();
    $stmtPackDet->close();

    // Si todo salió bien
    $mysqli->commit();

} catch (Exception $e) {
    // Algo falló → rollback y mensaje
    $mysqli->rollback();
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
      <meta charset="utf-8">
      <title>Error al procesar el pedido - La Cocada WEB</title>
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <script src="https://cdn.tailwindcss.com"></script>
      <link rel="stylesheet" href="cocada.css">
    </head>
    <body class="bg-[var(--c-bg)] min-h-screen flex items-center justify-center">
      <div class="card max-w-md w-full p-6 space-y-4 text-center">
        <h1 class="text-2xl font-extrabold text-[var(--c-cafe)]">
          Ocurrió un problema
        </h1>
        <p class="text-sm text-[#704127]">
          No pudimos guardar tu pedido en el sistema. Por favor, inténtalo de nuevo
          en unos momentos o comunícate directamente con la dulcería.
        </p>

        <p class="text-xs text-[#8b4a27] mt-2">
          Detalle técnico: <?php echo htmlspecialchars($e->getMessage()); ?>
        </p>

        <button onclick="window.location.href='index.php';"
                class="boton boton-primario w-full justify-center mt-2">
          Volver al inicio
        </button>
      </div>
    </body>
    </html>
    <?php
    exit;
}

// =====================================================
// 6) Página de confirmación (éxito)
// =====================================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Pedido confirmado - La Cocada WEB</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="cocada.css">
</head>
<body class="bg-[var(--c-bg)] min-h-screen flex items-center justify-center">
  <div class="card max-w-md w-full p-6 space-y-4 text-center">
    <h1 class="text-2xl font-extrabold text-[var(--c-cafe)]">
      ¡Gracias por tu pedido!
    </h1>

    <p class="text-sm text-[#704127]">
      Hemos registrado tu pedido con el folio:
    </p>

    <p class="text-lg font-bold text-[#7a231b]">
      <?php echo htmlspecialchars($folio); ?>
    </p>

    <p class="text-sm text-[#704127]">
      Total pagado: $<?php echo number_format($total, 2); ?> MXN
    </p>

    <p class="text-xs text-[#8b4a27]">
      El monto final y los detalles se confirman directamente en la dulcería al momento de la entrega.
    </p>

    <button onclick="window.location.href='index.php';"
            class="boton boton-primario w-full justify-center mt-2">
      Volver al inicio
    </button>
  </div>
</body>
</html>
