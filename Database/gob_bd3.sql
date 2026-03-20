-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-12-2019 a las 13:21:04
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

--
-- Volcado de datos para la tabla `archivos`
--

INSERT INTO `archivos` (`id`, `nombre`, `descripcion`, `fecha_subida`, `tipo`, `tamano`, `ruta`, `ss_usuarios_id`) VALUES
(17, 'Need for Speed Most Wanted.png', '', '0000-00-00 00:00:00', '', '1506912', 'C:\\xampp\\htdocs\\Sistema-NASv2\\Src\\vistas\\admin\\modulos/../../../Almacenamiento/Sistemas Operativos/Windows/Need for Speed Most Wanted.png', ''),
(18, 'Teoria de la probabilidad.docx', '', '0000-00-00 00:00:00', '', '26172', 'C:\\xampp\\htdocs\\Sistema-NASv2\\Src\\vistas\\admin\\modulos/../../../Almacenamiento/Documentos 115/Teoria de la probabilidad.docx', ''),
(20, 'Teoria de la probabilidad.docx', '', '0000-00-00 00:00:00', '', '26172', 'C:\\xampp\\htdocs\\Sistema-NASv2\\Src\\vistas\\admin\\modulos/../../../Almacenamiento/Documentos/Teoria de la probabilidad.docx', ''),
(21, 'Teoria de la probabilidad.docx', '', '0000-00-00 00:00:00', '', '26172', 'C:\\xampp\\htdocs\\Sistema-NASv2\\Src\\vistas\\admin\\modulos/../../../Almacenamiento/Herramientas/Documentos v3/Teoria de la probabilidad.docx', ''),
(22, 'pruebav8.txt', '', '0000-00-00 00:00:00', '', '0', 'C:\\xampp\\htdocs\\Sistema-NASv2\\Src\\vistas\\admin\\modulos/../../../Almacenamiento/Documentos/pruebav8.txt', ''),
(23, 'Presentación proyecto.pptx', '', '0000-00-00 00:00:00', '', '2447078', 'Almacenamiento/Presentación proyecto.pptx', ''),
(24, 'Presentación proyecto.pptx', '', '0000-00-00 00:00:00', '', '2447078', 'C:\\xampp\\htdocs\\Sistema-NASv2\\Src\\vistas\\admin\\modulos/../../../Almacenamiento/Documentos/Presentación proyecto.pptx', ''),
(25, 'Teoria de la probabilidad.docx', '', '0000-00-00 00:00:00', '', '26172', 'C:\\xampp\\htdocs\\Sistema-NASv3\\Src\\vistas\\admin\\modulos/../../../Almacenamiento/VIDEO/Teoria de la probabilidad.docx', ''),
(26, 'DKremix.m3u8', '', '0000-00-00 00:00:00', '', '3556', 'C:\\xampp\\htdocs\\Sistema-NASv3\\Src\\vistas\\admin\\modulos/../../../Almacenamiento/Sistemas/DKremix.m3u8', ''),
(27, 'Teoria de la probabilidad.docx', '', '0000-00-00 00:00:00', '', '26172', 'C:\\xampp\\htdocs\\Sistema-NASv3\\Src\\vistas\\admin\\modulos/../../../Almacenamiento/Videos/Teoria de la probabilidad.docx', ''),
(28, 'DKremixv2.m3u8', '', '0000-00-00 00:00:00', '', '4014', 'C:\\xampp\\htdocs\\Sistema-NASv3\\Src\\vistas\\admin\\modulos/../../../Almacenamiento/Videos/DKremixv2.m3u8', ''),
(29, 'Teoria de la probabilidad.docx', '', '0000-00-00 00:00:00', '', '26172', 'C:\\xampp\\htdocs\\Sistema-NASv3\\Src\\vistas\\admin\\modulos/../../../Almacenamiento/Videos/Teoria de la probabilidad.docx', ''),
(30, 'Sucecion Uzcategui torres Gil.docx', '', '0000-00-00 00:00:00', '', '14768', 'C:\\xampp\\htdocs\\Sistema-NASv3\\Src\\vistas\\admin\\modulos/../../../Almacenamiento/Videos/Sucecion Uzcategui torres Gil.docx', ''),
(31, 'rufus-4.4.exe', '', '0000-00-00 00:00:00', '', '1432648', 'C:\\xampp\\htdocs\\Sistema-NASv3\\Src\\vistas\\admin\\modulos/../../../Almacenamiento/Videos/rufus-4.4.exe', ''),
(32, 'OptITiny 7.iso', '', '0000-00-00 00:00:00', '', '336855040', 'Almacenamiento/OptITiny 7.iso', '');

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
(1, 'SGM123', '$2y$10$N7ltoCSEAvLkOYDDQ7YjL.YQNWfGHjCwsBMMJjbBvdoQcBjBD1.Gq', 1),
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
-- AUTO_INCREMENT de la tabla `ss_usuarios`
--
ALTER TABLE `ss_usuarios`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
