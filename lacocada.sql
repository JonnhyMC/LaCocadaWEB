-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-12-2025 a las 09:15:50
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `lacocada`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paquetes`
--

CREATE TABLE `paquetes` (
  `id_paquete` int(11) NOT NULL,
  `folio_pedido` varchar(50) DEFAULT NULL,
  `tipo_paquete` varchar(20) DEFAULT NULL,
  `cantidad_total` int(11) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `cliente` varchar(150) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `comentarios` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paquete_comprado`
--

CREATE TABLE `paquete_comprado` (
  `id_paquete_comprado` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `nombre_paquete` varchar(150) NOT NULL,
  `total_dulces` int(11) NOT NULL,
  `total_paquete` decimal(10,2) NOT NULL,
  `fecha_compra` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `paquete_comprado`
--

INSERT INTO `paquete_comprado` (`id_paquete_comprado`, `id_pedido`, `nombre_paquete`, `total_dulces`, `total_paquete`, `fecha_compra`) VALUES
(9, 22, 'Paquete mediano (20 dulces)', 20, 492.00, '2025-12-10 07:14:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paquete_detalle`
--

CREATE TABLE `paquete_detalle` (
  `id_detalle` int(11) NOT NULL,
  `id_paquete_comprado` int(11) NOT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `nombre_producto` varchar(150) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `paquete_detalle`
--

INSERT INTO `paquete_detalle` (`id_detalle`, `id_paquete_comprado`, `id_producto`, `nombre_producto`, `cantidad`) VALUES
(13, 9, 7, 'Cocada cuadro de nuez', 7),
(14, 9, 12, 'Cocada con arándano', 4),
(15, 9, 18, 'Bola de nuez pequeña', 9);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `folio` varchar(30) NOT NULL,
  `cliente` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `domicilio` varchar(255) NOT NULL DEFAULT '',
  `metodo_pago` varchar(30) NOT NULL,
  `comentarios` text DEFAULT NULL,
  `fecha` datetime NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `estado` enum('En proceso','En envío','Entregado') NOT NULL DEFAULT 'En proceso'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `folio`, `cliente`, `telefono`, `domicilio`, `metodo_pago`, `comentarios`, `fecha`, `total`, `estado`) VALUES
(22, 'LC-20251210-071444', 'Jonathan', '6271064850', 'Bartolome de Medina 65 Fatima', 'tarjeta', 'Me urgue el pedido', '2025-12-10 07:14:44', 612.00, 'En proceso');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `descripcion` text NOT NULL,
  `imagen` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `categoria`, `precio`, `descripcion`, `imagen`) VALUES
(1, 'Cocada con piña', 'Piña', 30.00, 'Cocada tradicional con trozos de piña, sabor fresco y dulce.', 'img/cocada_piña.jpeg'),
(2, 'Cocada clásica', 'Coco', 30.00, 'Cocada de coco clásica, con la receta tradicional de La Cocada.', 'img/cocada_clasica.jpeg'),
(3, 'Cocada con caramelo', 'Coco', 30.00, 'Cocada de coco bañada con caramelo para un sabor más dulce.', 'img/cocada_caramelo.jpeg'),
(4, 'Cocada con leche quemada', 'Leche quemada', 30.00, 'Cocada combinada con leche quemada, de textura suave y cremosa.', 'img/cocada_leche.jpeg'),
(5, 'Gloria de nuez', 'Nuez', 30.00, 'Dulce tipo gloria con leche y trozos de nuez.', 'img/gloria_nuez.jpeg'),
(6, 'Bola de nuez', 'Nuez', 30.00, 'Dulce en forma de bola elaborado con nuez y leche.', 'img/bola_nuez.jpeg'),
(7, 'Cocada cuadro de nuez', 'Nuez', 30.00, 'Cocada en forma de cuadro con trozos de nuez.', 'img/cuadro_nuez.jpeg'),
(8, 'Cocada de guayaba relleno de leche quemada', 'Mixto', 30.00, 'Cocada de guayaba con relleno de leche quemada.', 'img/relleno_leche.jpeg'),
(9, 'Natilla con nuez rellena de guayaba', 'Mixto', 30.00, 'Natilla de leche con nuez y relleno de guayaba.', 'img/natilla_nuez_guayaba.jpeg'),
(11, 'Negrito con coco', 'Chocolate', 30.00, 'Dulce tipo negrito de chocolate con coco.', 'img/negrito_coco_peque.jpeg'),
(12, 'Cocada con arándano', 'Mixto', 30.00, 'Cocada de coco con arándanos deshidratados.', 'img/cocada_arandano.jpeg'),
(13, 'Chispas de chocolate y nuez', 'Chocolate', 30.00, 'Dulce con chispas de chocolate y trozos de nuez.', 'img/chispas_choconuez.jpeg'),
(15, 'Cocada greñuda de cacahuate', 'Cacahuate', 30.00, 'Cocada greñuda combinada con cacahuate.', 'img/arandano_chocolate_b.jpeg'),
(17, 'Jamoncillo con canela', 'Jamoncillo', 30.00, 'Jamoncillo de leche con un toque de canela.', 'img/jamoncillo_canela.jpeg'),
(18, 'Bola de nuez pequeña', 'Nuez', 18.00, 'Presentación pequeña de bola de nuez.', 'img/bola_nuez_peque.jpeg'),
(19, 'Gloria de nuez pequeña', 'Nuez', 18.00, 'Presentación pequeña de gloria de nuez.', 'img/gloria_nuez_peque.jpeg'),
(20, 'Cocada de coco con piña pequeña', 'Piña', 18.00, 'Cocada pequeña de coco con piña.', 'img/coco_piña_peque.jpeg'),
(21, 'Cuadro de nuez pequeño', 'Nuez', 18.00, 'Cuadro de nuez en presentación pequeña.', 'img/nuez_peque.jpeg'),
(22, 'Cocada de nuez pequeña', 'Nuez', 18.00, 'Cocada de nuez en tamaño pequeño.', 'img/cocada_nuez_peque.jpeg'),
(24, 'Negrito con coco pequeño', 'Chocolate', 18.00, 'Negrito de chocolate con coco en tamaño pequeño.', 'img/negrito_coco_peque.jpeg'),
(25, 'Cocada de chocolate menta', 'Chocolate', 30.00, 'Cocada con sabor a chocolate y menta.', 'img/chocolate_menta_peque.jpeg'),
(26, 'Cocada con coco pequeña', 'Coco', 18.00, 'Cocada de coco en presentación pequeña.', 'img/coco_peque.jpeg'),
(27, 'Cocada de almendras de coco', 'Coco', 30.00, 'Cocada de coco con almendras.', 'img/almendras_coco.jpeg'),
(28, 'Tamarindo salado', 'Tamarindo', 25.00, 'Dulce de tamarindo con sabor salado.', 'img/tamarindo_salado.jpeg'),
(29, 'Tamarindo de azúcar', 'Tamarindo', 25.00, 'Dulce de tamarindo cubierto con azúcar.', 'img/tamarindo_azucar.jpeg'),
(30, 'Tamarindo dulce picoso mixto', 'Tamarindo', 25.00, 'Tamarindo con mezcla de dulce y picante.', 'img/picoso_mixto.jpeg'),
(32, 'Cocada relleno de guayaba grande', 'Guayaba', 60.00, 'Cocada grande con relleno de guayaba.', 'img/guayaba_coco.jpeg'),
(33, 'Cocada de coco relleno de guayaba grande', 'Mixto', 60.00, 'Cocada grande de coco con relleno de guayaba.', 'img/guayaba_nuez.jpeg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_pedido`
--

CREATE TABLE `producto_pedido` (
  `id_producto_pedido` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `nombre_producto` varchar(150) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `producto_pedido`
--

INSERT INTO `producto_pedido` (`id_producto_pedido`, `id_pedido`, `id_producto`, `nombre_producto`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(9, 22, 5, 'Gloria de nuez', 4, 30.00, 120.00);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `paquetes`
--
ALTER TABLE `paquetes`
  ADD PRIMARY KEY (`id_paquete`);

--
-- Indices de la tabla `paquete_comprado`
--
ALTER TABLE `paquete_comprado`
  ADD PRIMARY KEY (`id_paquete_comprado`),
  ADD KEY `fk_pc_pedido` (`id_pedido`);

--
-- Indices de la tabla `paquete_detalle`
--
ALTER TABLE `paquete_detalle`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `fk_paquete_detalle_paquete_comprado` (`id_paquete_comprado`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `producto_pedido`
--
ALTER TABLE `producto_pedido`
  ADD PRIMARY KEY (`id_producto_pedido`),
  ADD KEY `fk_pp_pedido` (`id_pedido`),
  ADD KEY `fk_pp_producto` (`id_producto`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `paquetes`
--
ALTER TABLE `paquetes`
  MODIFY `id_paquete` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `paquete_comprado`
--
ALTER TABLE `paquete_comprado`
  MODIFY `id_paquete_comprado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `paquete_detalle`
--
ALTER TABLE `paquete_detalle`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `producto_pedido`
--
ALTER TABLE `producto_pedido`
  MODIFY `id_producto_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `paquete_comprado`
--
ALTER TABLE `paquete_comprado`
  ADD CONSTRAINT `fk_pc_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id`);

--
-- Filtros para la tabla `paquete_detalle`
--
ALTER TABLE `paquete_detalle`
  ADD CONSTRAINT `fk_paquete_detalle_paquete_comprado` FOREIGN KEY (`id_paquete_comprado`) REFERENCES `paquete_comprado` (`id_paquete_comprado`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `producto_pedido`
--
ALTER TABLE `producto_pedido`
  ADD CONSTRAINT `fk_pp_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id`),
  ADD CONSTRAINT `fk_pp_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
