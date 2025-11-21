-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-11-2025 a las 00:58:02
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
-- Base de datos: `colegio_bd`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividades`
--

CREATE TABLE `actividades` (
  `id` int(11) NOT NULL,
  `programacion_id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `archivo_adjunto` varchar(255) DEFAULT NULL,
  `tipo` enum('material','tarea','prueba') NOT NULL,
  `fecha_limite` datetime DEFAULT NULL,
  `porcentaje` int(11) NOT NULL DEFAULT 10,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `actividades`
--

INSERT INTO `actividades` (`id`, `programacion_id`, `titulo`, `descripcion`, `archivo_adjunto`, `tipo`, `fecha_limite`, `porcentaje`, `created_at`) VALUES
(5, 2, 'AdoLuche', 'a', '1763599825_Instrucciones_InformeEVA4AIA2025.docx', 'tarea', '2025-11-19 21:49:00', 10, '2025-11-20 00:50:25'),
(6, 2, 'hola', 'prueba', NULL, 'material', '2025-11-21 15:20:00', 10, '2025-11-20 22:04:11'),
(7, 2, 'hola', 'prueba', NULL, 'material', '2025-11-21 15:20:00', 10, '2025-11-20 22:08:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `anotaciones`
--

CREATE TABLE `anotaciones` (
  `id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `autor_id` int(11) NOT NULL,
  `tipo` enum('positiva','negativa') NOT NULL,
  `detalle` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `anotaciones`
--

INSERT INTO `anotaciones` (`id`, `alumno_id`, `autor_id`, `tipo`, `detalle`, `fecha`) VALUES
(1, 4, 3, 'negativa', '-', '2025-11-20 00:56:15'),
(2, 4, 3, 'positiva', '+', '2025-11-20 21:01:38'),
(3, 8, 3, 'negativa', 'Porque si', '2025-11-21 03:04:59'),
(4, 8, 3, 'negativa', 'Por pegarle al Profe', '2025-11-21 03:05:07'),
(5, 8, 3, 'negativa', 'Por Abir un extintor', '2025-11-21 03:05:19'),
(6, 8, 3, 'negativa', 'Por pegarle a un compañero', '2025-11-21 03:05:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `anuncios`
--

CREATE TABLE `anuncios` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `mensaje` text NOT NULL,
  `autor_id` int(11) NOT NULL,
  `tipo` enum('informativo','urgente') DEFAULT 'informativo',
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `anuncios`
--

INSERT INTO `anuncios` (`id`, `titulo`, `mensaje`, `autor_id`, `tipo`, `fecha`) VALUES
(1, 'hola', 'hola', 1, 'informativo', '2025-11-21 02:24:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas`
--

CREATE TABLE `asignaturas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asignaturas`
--

INSERT INTO `asignaturas` (`id`, `nombre`) VALUES
(1, 'Matemáticas'),
(2, 'Lenguaje'),
(3, 'Historia'),
(4, 'PEPE');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia`
--

CREATE TABLE `asistencia` (
  `id` int(11) NOT NULL,
  `programacion_id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `horario_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `estado` enum('presente','ausente','atrasado') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asistencia`
--

INSERT INTO `asistencia` (`id`, `programacion_id`, `alumno_id`, `horario_id`, `fecha`, `estado`, `created_at`) VALUES
(3, 2, 4, 4, '2025-11-20', 'ausente', '2025-11-20 21:01:50'),
(4, 2, 4, 7, '2025-11-21', 'presente', '2025-11-21 02:47:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

CREATE TABLE `cursos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cursos`
--

INSERT INTO `cursos` (`id`, `nombre`) VALUES
(1, '4°A'),
(2, '4°B'),
(3, '5°B');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entregas`
--

CREATE TABLE `entregas` (
  `id` int(11) NOT NULL,
  `actividad_id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `archivo_entrega` varchar(255) DEFAULT NULL,
  `comentario_alumno` text DEFAULT NULL,
  `fecha_entrega` timestamp NOT NULL DEFAULT current_timestamp(),
  `nota` decimal(3,1) DEFAULT NULL,
  `comentario_profesor` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `entregas`
--

INSERT INTO `entregas` (`id`, `actividad_id`, `alumno_id`, `archivo_entrega`, `comentario_alumno`, `fecha_entrega`, `nota`, `comentario_profesor`) VALUES
(1, 5, 4, '1763599898_ENTREGA_AdoLuche3.jpg', NULL, '2025-11-20 00:51:38', 7.0, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `familia`
--

CREATE TABLE `familia` (
  `id` int(11) NOT NULL,
  `apoderado_id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `familia`
--

INSERT INTO `familia` (`id`, `apoderado_id`, `alumno_id`) VALUES
(1, 6, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

CREATE TABLE `horarios` (
  `id` int(11) NOT NULL,
  `programacion_id` int(11) NOT NULL,
  `dia` enum('Lunes','Martes','Miércoles','Jueves','Viernes') NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `aula` varchar(50) DEFAULT 'Sala Base',
  `estado` enum('activo','suspendido') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `horarios`
--

INSERT INTO `horarios` (`id`, `programacion_id`, `dia`, `hora_inicio`, `hora_fin`, `aula`, `estado`) VALUES
(1, 1, 'Lunes', '08:00:00', '09:30:00', 'Sala Base', 'activo'),
(2, 1, 'Miércoles', '10:00:00', '11:30:00', 'Sala Base', 'activo'),
(3, 2, 'Martes', '08:00:00', '09:30:00', 'Sala Base', 'activo'),
(4, 2, 'Jueves', '12:00:00', '13:30:00', 'Sala Base', 'activo'),
(5, 2, 'Miércoles', '15:46:00', '02:40:00', '104', 'activo'),
(6, 1, 'Martes', '15:40:00', '15:41:00', '', 'activo'),
(7, 2, 'Viernes', '08:48:00', '01:48:00', '104', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `matriculas`
--

CREATE TABLE `matriculas` (
  `id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `anio` year(4) DEFAULT 2025
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `matriculas`
--

INSERT INTO `matriculas` (`id`, `alumno_id`, `curso_id`, `anio`) VALUES
(1, 4, 1, '2025'),
(2, 8, 1, '2025');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

CREATE TABLE `mensajes` (
  `id` int(11) NOT NULL,
  `remitente_id` int(11) NOT NULL,
  `destinatario_id` int(11) NOT NULL,
  `asunto` varchar(150) NOT NULL,
  `mensaje` text NOT NULL,
  `leido` tinyint(1) DEFAULT 0,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mensajes`
--

INSERT INTO `mensajes` (`id`, `remitente_id`, `destinatario_id`, `asunto`, `mensaje`, `leido`, `fecha`) VALUES
(1, 3, 4, 'Puto', 'Puto', 0, '2025-11-20 22:08:47'),
(2, 3, 4, 'Puto', 'Puto', 0, '2025-11-20 22:12:14'),
(3, 3, 4, 'Puto', 'Puto', 0, '2025-11-20 22:13:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `mensaje` varchar(255) NOT NULL,
  `enlace` varchar(255) DEFAULT NULL,
  `leido` tinyint(1) DEFAULT 0,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id`, `usuario_id`, `mensaje`, `enlace`, `leido`, `fecha`) VALUES
(1, 4, 'Nuevo contenido en Lenguaje: hola', 'ver_curso.php?id=2', 1, '2025-11-20 22:04:11'),
(2, 4, 'Nuevo contenido en Lenguaje: hola', 'ver_curso.php?id=2', 1, '2025-11-20 22:08:25'),
(3, 4, 'Nuevo mensaje de Profe Lenguaje: Puto', 'mensajes.php', 1, '2025-11-20 22:08:47'),
(4, 4, 'Nuevo mensaje de Profe Lenguaje: Puto', 'mensajes.php', 1, '2025-11-20 22:12:14'),
(5, 4, 'Nuevo mensaje de Profe Lenguaje: Puto', 'mensajes.php', 1, '2025-11-20 22:13:42'),
(6, 8, 'Tienes una nueva Observación en tu Hoja de Vida.', 'mis_anotaciones.php', 1, '2025-11-21 03:04:59'),
(7, 8, 'Tienes una nueva Observación en tu Hoja de Vida.', 'mis_anotaciones.php', 1, '2025-11-21 03:05:07'),
(8, 8, 'Tienes una nueva Observación en tu Hoja de Vida.', 'mis_anotaciones.php', 1, '2025-11-21 03:05:19'),
(9, 8, 'Tienes una nueva Observación en tu Hoja de Vida.', 'mis_anotaciones.php', 1, '2025-11-21 03:05:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programacion_academica`
--

CREATE TABLE `programacion_academica` (
  `id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `profesor_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `programacion_academica`
--

INSERT INTO `programacion_academica` (`id`, `curso_id`, `asignatura_id`, `profesor_id`) VALUES
(1, 1, 1, 2),
(2, 1, 2, 3),
(3, 3, 4, 6),
(4, 2, 2, 3),
(5, 2, 1, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('director','profesor','alumno','apoderado','administrador') NOT NULL,
  `foto` varchar(255) DEFAULT 'default.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`, `foto`, `created_at`) VALUES
(1, 'Sra. Directora', 'director@cole.cl', '$2y$10$Appm3frDuvZeN4w99tdreuPO6BkoFplXtQxlpTXT5HR/ognY2.MnG', 'director', 'default.jpg', '2025-11-20 00:32:34'),
(2, 'Profe Matemáticas', 'matematica@cole.cl', '12345', 'profesor', 'default.jpg', '2025-11-20 00:32:34'),
(3, 'Profe Lenguaje', 'lenguaje@cole.cl', '$2y$10$vLNC6Txo12AVg1mrl37pSOWAGR6yuPFaOgevsh0e3ediUHTxr2Sum', 'profesor', 'perfil_3_1763672376.jpg', '2025-11-20 00:32:34'),
(4, 'Pepito Alumno', 'pepito@cole.cl', '$2y$10$6e1LzBLCoNfoNgJmdc7iMuelXGpQE3TBwNL.l0Vf3Z3bFAvJow7XO', 'alumno', 'perfil_4_1763668426.jpg', '2025-11-20 00:32:34'),
(5, 'Super Admin', 'admin@cole.cl', '$2y$10$VauC602Vz4JQ.fsD8yLC6eh1TilJXEfCCi4KJtmL/TQpAu6Zq0aeu', 'administrador', 'default.jpg', '2025-11-20 01:02:33'),
(6, 'AdoLuche', 'adoluche@gmail.com', '$2y$10$t/uYPcV7CeWA2XyX8/8ziuGnP1t9vBm.SOJ1SLYTSisDT/84.euwK', 'profesor', 'default.jpg', '2025-11-20 01:06:03'),
(7, 'Papá de Pepito', 'papa@cole.cl', '12345', 'apoderado', 'default.jpg', '2025-11-20 01:09:52'),
(8, 'Darcko Mella', 'Darcko@gmail.com', '$2y$10$ZEn8n5tWWsqqounJ7XXnRehdzSnH43ypBgfitg3O.nMf/u5o4ifr.', 'alumno', 'perfil_8_1763769273.png', '2025-11-21 03:02:53');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `programacion_id` (`programacion_id`);

--
-- Indices de la tabla `anotaciones`
--
ALTER TABLE `anotaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alumno_id` (`alumno_id`),
  ADD KEY `autor_id` (`autor_id`);

--
-- Indices de la tabla `anuncios`
--
ALTER TABLE `anuncios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `autor_id` (`autor_id`);

--
-- Indices de la tabla `asignaturas`
--
ALTER TABLE `asignaturas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `programacion_id` (`programacion_id`),
  ADD KEY `alumno_id` (`alumno_id`),
  ADD KEY `horario_id` (`horario_id`);

--
-- Indices de la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `entregas`
--
ALTER TABLE `entregas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `actividad_id` (`actividad_id`),
  ADD KEY `alumno_id` (`alumno_id`);

--
-- Indices de la tabla `familia`
--
ALTER TABLE `familia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `apoderado_id` (`apoderado_id`),
  ADD KEY `alumno_id` (`alumno_id`);

--
-- Indices de la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `programacion_id` (`programacion_id`);

--
-- Indices de la tabla `matriculas`
--
ALTER TABLE `matriculas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alumno_id` (`alumno_id`),
  ADD KEY `curso_id` (`curso_id`);

--
-- Indices de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `remitente_id` (`remitente_id`),
  ADD KEY `destinatario_id` (`destinatario_id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `programacion_academica`
--
ALTER TABLE `programacion_academica`
  ADD PRIMARY KEY (`id`),
  ADD KEY `curso_id` (`curso_id`),
  ADD KEY `asignatura_id` (`asignatura_id`),
  ADD KEY `profesor_id` (`profesor_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividades`
--
ALTER TABLE `actividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `anotaciones`
--
ALTER TABLE `anotaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `anuncios`
--
ALTER TABLE `anuncios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `asignaturas`
--
ALTER TABLE `asignaturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `entregas`
--
ALTER TABLE `entregas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `familia`
--
ALTER TABLE `familia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `horarios`
--
ALTER TABLE `horarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `matriculas`
--
ALTER TABLE `matriculas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `programacion_academica`
--
ALTER TABLE `programacion_academica`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD CONSTRAINT `actividades_ibfk_1` FOREIGN KEY (`programacion_id`) REFERENCES `programacion_academica` (`id`);

--
-- Filtros para la tabla `anotaciones`
--
ALTER TABLE `anotaciones`
  ADD CONSTRAINT `anotaciones_ibfk_1` FOREIGN KEY (`alumno_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `anotaciones_ibfk_2` FOREIGN KEY (`autor_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `anuncios`
--
ALTER TABLE `anuncios`
  ADD CONSTRAINT `anuncios_ibfk_1` FOREIGN KEY (`autor_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD CONSTRAINT `asistencia_ibfk_1` FOREIGN KEY (`programacion_id`) REFERENCES `programacion_academica` (`id`),
  ADD CONSTRAINT `asistencia_ibfk_2` FOREIGN KEY (`alumno_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `asistencia_ibfk_3` FOREIGN KEY (`horario_id`) REFERENCES `horarios` (`id`);

--
-- Filtros para la tabla `entregas`
--
ALTER TABLE `entregas`
  ADD CONSTRAINT `entregas_ibfk_1` FOREIGN KEY (`actividad_id`) REFERENCES `actividades` (`id`),
  ADD CONSTRAINT `entregas_ibfk_2` FOREIGN KEY (`alumno_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `familia`
--
ALTER TABLE `familia`
  ADD CONSTRAINT `familia_ibfk_1` FOREIGN KEY (`apoderado_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `familia_ibfk_2` FOREIGN KEY (`alumno_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD CONSTRAINT `horarios_ibfk_1` FOREIGN KEY (`programacion_id`) REFERENCES `programacion_academica` (`id`);

--
-- Filtros para la tabla `matriculas`
--
ALTER TABLE `matriculas`
  ADD CONSTRAINT `matriculas_ibfk_1` FOREIGN KEY (`alumno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matriculas_ibfk_2` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD CONSTRAINT `mensajes_ibfk_1` FOREIGN KEY (`remitente_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `mensajes_ibfk_2` FOREIGN KEY (`destinatario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `programacion_academica`
--
ALTER TABLE `programacion_academica`
  ADD CONSTRAINT `programacion_academica_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`),
  ADD CONSTRAINT `programacion_academica_ibfk_2` FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`),
  ADD CONSTRAINT `programacion_academica_ibfk_3` FOREIGN KEY (`profesor_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
