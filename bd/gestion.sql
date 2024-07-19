-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-07-2024 a las 17:19:01
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
-- Base de datos: `gestion`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

CREATE TABLE `administradores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `celular` varchar(15) NOT NULL,
  `username` varchar(50) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `administradores`
--

INSERT INTO `administradores` (`id`, `nombre`, `apellido`, `cedula`, `celular`, `username`, `contraseña`, `estado`) VALUES
(1, 'richard', 'arturo ', '1126451233', '3125334687', 'admin', '$2y$10$Sct7F9NEJTb.bjV0jXRZeOjlfDZEO1LApw0iIFZ.2udbb/nI8eZra', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `codigo_seguridad`
--

CREATE TABLE `codigo_seguridad` (
  `id` int(11) NOT NULL,
  `codigo` int(4) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `codigo_seguridad`
--

INSERT INTO `codigo_seguridad` (`id`, `codigo`, `fecha_creacion`) VALUES
(1, 9847, '2024-07-11 23:00:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL,
  `ruta_respaldo` varchar(255) NOT NULL,
  `hora_respaldo` time NOT NULL,
  `dias_semana` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `ruta_respaldo`, `hora_respaldo`, `dias_semana`) VALUES
(1, 'D:\\bakup', '14:00:00', 'Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dependencia`
--

CREATE TABLE `dependencia` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `tamaño` int(11) NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `dependencia`
--

INSERT INTO `dependencia` (`id`, `nombre`, `tamaño`, `ruta`, `fecha_creacion`) VALUES
(3, 'contabilidad', 20, 'D:\\\\gestion\\contabilidad', '2024-07-10 23:37:52'),
(4, 'administracion', 50, 'D:\\\\gestion\\administracion', '2024-07-10 23:38:10'),
(5, 'servicios', 50, 'D:\\\\gestion\\servicios', '2024-07-10 23:50:25'),
(6, 'rutas', 50, 'D:\\\\gestion\\rutas', '2024-07-10 23:50:48'),
(7, 'trafico', 50, 'D:\\\\gestion\\trafico', '2024-07-10 23:53:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial`
--

CREATE TABLE `historial` (
  `id` int(11) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `localizacion` text NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial`
--

INSERT INTO `historial` (`id`, `accion`, `usuario_id`, `nombre_archivo`, `localizacion`, `descripcion`, `fecha`) VALUES
(1, 'abrió', 1, 'pos.docx', 'D:\\\\gestion\\contabilidad\\Eleventa\\pos.docx', 'El usuario abrió el archivo.', '2024-07-19 00:39:11'),
(2, 'renombró', 1, 'pos.docx', 'D:\\\\gestion\\contabilidad\\Eleventa', 'El usuario renombró el archivo o carpeta a pos1', '2024-07-19 00:39:54'),
(3, 'renombró', 1, 'pos1.docx', 'D:\\\\gestion\\contabilidad\\Eleventa', 'El usuario renombró el archivo o carpeta a pos', '2024-07-19 00:40:07'),
(4, 'descargó', 1, 'pos.docx', 'D:\\\\gestion\\contabilidad\\Eleventa\\pos.docx', 'El usuario descargó el archivo.', '2024-07-19 00:40:19'),
(5, 'eliminar', 1, 'Eleventa', 'D:\\\\gestion\\contabilidad\\Eleventa', 'Carpeta eliminada', '2024-07-19 02:42:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `raiz`
--

CREATE TABLE `raiz` (
  `id` int(11) NOT NULL,
  `ubicacion` varchar(255) NOT NULL,
  `nombre_carpeta` varchar(255) NOT NULL,
  `ruta_completa` varchar(255) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `raiz`
--

INSERT INTO `raiz` (`id`, `ubicacion`, `nombre_carpeta`, `ruta_completa`, `fecha_creacion`) VALUES
(1, 'D:\\', 'gestion', 'D:\\\\gestion', '2024-07-10 22:51:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respaldo`
--

CREATE TABLE `respaldo` (
  `id` int(11) NOT NULL,
  `estado` enum('correcto','incorrecto') NOT NULL,
  `fecha_inicio` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_fin` timestamp NULL DEFAULT NULL,
  `ruta` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `respaldo`
--

INSERT INTO `respaldo` (`id`, `estado`, `fecha_inicio`, `fecha_fin`, `ruta`) VALUES
(1, 'correcto', '2024-07-13 02:12:21', '2024-07-13 02:12:54', 'D:\\bakup/Viernes/backup_db.sql, D:\\bakup/Viernes/backup_files.zip'),
(2, 'correcto', '2024-07-13 02:15:51', '2024-07-13 02:16:23', 'D:\\bakup/Viernes/backup_db.sql, D:\\bakup/Viernes/backup_files.zip'),
(3, 'correcto', '2024-07-18 22:16:34', '2024-07-18 22:16:42', 'D:\\bakup/Jueves/backup_db.sql, D:\\bakup/Jueves/backup_files.zip'),
(4, 'correcto', '2024-07-18 22:18:15', '2024-07-18 22:18:29', 'D:\\bakup/Jueves/backup_db.sql, D:\\bakup/Jueves/backup_files.zip');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombres` varchar(255) NOT NULL,
  `apellidos` varchar(255) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `celular` varchar(15) NOT NULL,
  `username` varchar(50) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_nacimiento` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombres`, `apellidos`, `cedula`, `celular`, `username`, `contraseña`, `estado`, `fecha_creacion`, `fecha_nacimiento`) VALUES
(1, 'richard', 'peña', '1126451233', '3125334687', 'richard', '$2y$10$HKvLXZ4AosoRDJHCHQ8.QebXixuICsDuR/IdMgoIe.m5I03hlD9lS', 1, '2024-07-12 21:41:19', '1990-07-31'),
(2, 'mayer', 'uno', '12541542', '32145781', 'mayer', '$2y$10$RebolIgxar4HLkq/8TEK1e/4B2UrZG29Rzdk8XLRHDYNuPOkMqFwe', 1, '2024-07-12 22:08:54', '1990-07-31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_dependencias`
--

CREATE TABLE `usuarios_dependencias` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `dependencia_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios_dependencias`
--

INSERT INTO `usuarios_dependencias` (`id`, `usuario_id`, `dependencia_id`) VALUES
(1, 1, 3),
(2, 1, 4),
(3, 2, 3);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `codigo_seguridad`
--
ALTER TABLE `codigo_seguridad`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `dependencia`
--
ALTER TABLE `dependencia`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `historial`
--
ALTER TABLE `historial`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `raiz`
--
ALTER TABLE `raiz`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `respaldo`
--
ALTER TABLE `respaldo`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios_dependencias`
--
ALTER TABLE `usuarios_dependencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `dependencia_id` (`dependencia_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `codigo_seguridad`
--
ALTER TABLE `codigo_seguridad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `dependencia`
--
ALTER TABLE `dependencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `historial`
--
ALTER TABLE `historial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `raiz`
--
ALTER TABLE `raiz`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `respaldo`
--
ALTER TABLE `respaldo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios_dependencias`
--
ALTER TABLE `usuarios_dependencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `historial`
--
ALTER TABLE `historial`
  ADD CONSTRAINT `historial_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `usuarios_dependencias`
--
ALTER TABLE `usuarios_dependencias`
  ADD CONSTRAINT `usuarios_dependencias_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `usuarios_dependencias_ibfk_2` FOREIGN KEY (`dependencia_id`) REFERENCES `dependencia` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
