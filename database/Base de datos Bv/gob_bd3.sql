-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-07-2026 a las 05:30:19
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
-- Base de datos: `gob_bd3`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivos`
--

CREATE TABLE `archivos` (
  `id` int(255) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` varchar(255) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `fecha_subida` datetime NOT NULL,
  `tipo` varchar(255) NOT NULL,
  `tamano` varchar(255) NOT NULL,
  `ruta` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `ss_usuarios_id` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carpetas`
--

CREATE TABLE `carpetas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `ruta` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_06_15_000001_create_computadoras_table', 1),
(2, '2026_06_15_000002_add_network_fields_to_computadoras', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ss_computadoras`
--

CREATE TABLE `ss_computadoras` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL COMMENT 'Nombre o código de la computadora',
  `numero_serie` varchar(100) DEFAULT NULL COMMENT 'Número de serie',
  `codigo_inventario` varchar(100) DEFAULT NULL COMMENT 'Código de inventario institucional',
  `marca` varchar(80) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `tipo` varchar(50) NOT NULL DEFAULT 'Desktop' COMMENT 'Desktop, Laptop, All-in-One, Servidor, Workstation',
  `procesador` varchar(150) DEFAULT NULL,
  `ram` varchar(50) DEFAULT NULL COMMENT 'Ej: 8 GB DDR4',
  `almacenamiento` varchar(100) DEFAULT NULL COMMENT 'Ej: 500 GB SSD',
  `tarjeta_grafica` varchar(150) DEFAULT NULL,
  `sistema_operativo` varchar(100) DEFAULT NULL,
  `operador` varchar(150) NOT NULL COMMENT 'Nombre del operador / usuario asignado',
  `cargo_operador` varchar(100) DEFAULT NULL COMMENT 'Cargo del operador',
  `departamento` varchar(150) NOT NULL COMMENT 'Departamento al que pertenece',
  `direccion_ip` varchar(45) DEFAULT NULL COMMENT 'Dirección IPv4 o IPv6 de la computadora',
  `direccion_mac` varchar(17) DEFAULT NULL COMMENT 'Dirección MAC en formato AA:BB:CC:DD:EE:FF',
  `estado` varchar(50) NOT NULL DEFAULT 'Activo' COMMENT 'Activo, En reparación, Dado de baja',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ss_logs`
--

CREATE TABLE `ss_logs` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` varchar(50) NOT NULL,
  `detalles` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ss_permiso`
--

CREATE TABLE `ss_permiso` (
  `id_permiso` int(255) NOT NULL,
  `nombre_permiso` varchar(255) CHARACTER SET ucs2 COLLATE ucs2_general_ci NOT NULL,
  `descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ss_roles`
--

CREATE TABLE `ss_roles` (
  `id_rol` int(255) NOT NULL,
  `nombre_rol` varchar(255) CHARACTER SET utf16 COLLATE utf16_general_ci NOT NULL,
  `descripcion_rol` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ss_roles_permisos`
--

CREATE TABLE `ss_roles_permisos` (
  `id_rol` int(11) NOT NULL,
  `id_permiso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ss_servidores_externos`
--

CREATE TABLE `ss_servidores_externos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `puerto` int(11) DEFAULT 21,
  `usuario` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `protocolo` varchar(50) DEFAULT 'ftp'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ss_usuarios`
--

CREATE TABLE `ss_usuarios` (
  `id` int(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `rol_id` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ss_usuarios`
--

INSERT INTO `ss_usuarios` (`id`, `username`, `password`, `rol_id`) VALUES
(2, 'admin', '$2y$10$n0zx9zSGY/.PT7zyc1Uciugt0YqaxmAYEGMpp5xMPq8RhFjj7qz32', 1),
(3, 'supervisor', '$2y$10$n0zx9zSGY/.PT7zyc1Uciugt0YqaxmAYEGMpp5xMPq8RhFjj7qz32', 2),
(4, 'secretario', '$2y$10$n0zx9zSGY/.PT7zyc1Uciugt0YqaxmAYEGMpp5xMPq8RhFjj7qz32', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ss_usuarios_roles`
--

CREATE TABLE `ss_usuarios_roles` (
  `id_usuarios` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `archivos`
--
ALTER TABLE `archivos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `carpetas`
--
ALTER TABLE `carpetas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ss_computadoras`
--
ALTER TABLE `ss_computadoras`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ss_logs`
--
ALTER TABLE `ss_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `ss_permiso`
--
ALTER TABLE `ss_permiso`
  ADD PRIMARY KEY (`id_permiso`);

--
-- Indices de la tabla `ss_roles`
--
ALTER TABLE `ss_roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `ss_roles_permisos`
--
ALTER TABLE `ss_roles_permisos`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `ss_servidores_externos`
--
ALTER TABLE `ss_servidores_externos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ss_usuarios`
--
ALTER TABLE `ss_usuarios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ss_usuarios_roles`
--
ALTER TABLE `ss_usuarios_roles`
  ADD PRIMARY KEY (`id_usuarios`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `archivos`
--
ALTER TABLE `archivos`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `carpetas`
--
ALTER TABLE `carpetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `ss_computadoras`
--
ALTER TABLE `ss_computadoras`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `ss_logs`
--
ALTER TABLE `ss_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ss_permiso`
--
ALTER TABLE `ss_permiso`
  MODIFY `id_permiso` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ss_roles`
--
ALTER TABLE `ss_roles`
  MODIFY `id_rol` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ss_roles_permisos`
--
ALTER TABLE `ss_roles_permisos`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ss_servidores_externos`
--
ALTER TABLE `ss_servidores_externos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `ss_usuarios`
--
ALTER TABLE `ss_usuarios`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `ss_usuarios_roles`
--
ALTER TABLE `ss_usuarios_roles`
  MODIFY `id_usuarios` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `ss_logs`
--
ALTER TABLE `ss_logs`
  ADD CONSTRAINT `ss_logs_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `ss_usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
