-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-06-2026 a las 20:23:06
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
-- Base de datos: `agroconnect`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cultivos`
--

CREATE TABLE `cultivos` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `parcela_id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `variedad` varchar(120) DEFAULT '',
  `fecha_siembra` date DEFAULT NULL,
  `fecha_cosecha_prevista` date DEFAULT NULL,
  `estado` enum('sembrado','crecimiento','floracion','cosecha','finalizado') DEFAULT 'crecimiento',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cultivos`
--

INSERT INTO `cultivos` (`id`, `empresa_id`, `parcela_id`, `nombre`, `variedad`, `fecha_siembra`, `fecha_cosecha_prevista`, `estado`, `fecha_creacion`) VALUES
(1, 1, 1, 'Tomate', 'Cherry', '2026-04-01', '2026-08-15', 'crecimiento', '2026-06-09 17:23:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas`
--

CREATE TABLE `empresas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresas`
--

INSERT INTO `empresas` (`id`, `nombre`, `fecha_creacion`) VALUES
(1, 'AgroConnect Demo', '2026-06-09 17:23:36'),
(2, 'Prueba', '2026-06-09 17:58:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `foro_respuestas`
--

CREATE TABLE `foro_respuestas` (
  `id` int(11) NOT NULL,
  `tema_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `respuesta` text NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `foro_temas`
--

CREATE TABLE `foro_temas` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `titulo` varchar(180) NOT NULL,
  `contenido` text NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupos_trabajadores`
--

CREATE TABLE `grupos_trabajadores` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupo_trabajador_miembros`
--

CREATE TABLE `grupo_trabajador_miembros` (
  `grupo_id` int(11) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historico_actividades`
--

CREATE TABLE `historico_actividades` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `parcela_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `tipo` enum('riego','tratamiento','siembra','cosecha','incidencia','observacion') DEFAULT 'observacion',
  `descripcion` text NOT NULL,
  `fecha` date NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incidencias`
--

CREATE TABLE `incidencias` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `titulo` varchar(180) NOT NULL,
  `descripcion` text NOT NULL,
  `prioridad` enum('baja','media','alta') DEFAULT 'media',
  `estado` enum('abierta','en_revision','resuelta') DEFAULT 'abierta',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `parcelas`
--

CREATE TABLE `parcelas` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `ubicacion` varchar(180) DEFAULT '',
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `hectareas` decimal(10,2) DEFAULT 0.00,
  `tipo_suelo` varchar(100) DEFAULT '',
  `estado` enum('activa','en_revision','inactiva') DEFAULT 'activa',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `parcelas`
--

INSERT INTO `parcelas` (`id`, `empresa_id`, `nombre`, `ubicacion`, `latitud`, `longitud`, `hectareas`, `tipo_suelo`, `estado`, `fecha_creacion`) VALUES
(1, 1, 'Parcela Norte', 'Carmona, Sevilla', 37.47150000, -5.64170000, 12.50, 'Arcilloso', 'activa', '2026-06-09 17:23:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recursos`
--

CREATE TABLE `recursos` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `tipo` enum('maquinaria','herramienta','insumo','agua','personal') DEFAULT 'maquinaria',
  `cantidad` decimal(10,2) DEFAULT 0.00,
  `unidad` varchar(30) DEFAULT '',
  `estado` enum('disponible','en_uso','mantenimiento','agotado') DEFAULT 'disponible',
  `notas` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `recursos`
--

INSERT INTO `recursos` (`id`, `empresa_id`, `nombre`, `tipo`, `cantidad`, `unidad`, `estado`, `notas`, `fecha_creacion`) VALUES
(1, 1, 'Tractor principal', 'maquinaria', 1.00, 'ud', 'disponible', 'Revisión al día', '2026-06-09 17:23:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones_activas`
--

CREATE TABLE `sesiones_activas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `ultima_actividad` datetime NOT NULL,
  `ip` varchar(50) DEFAULT '',
  `user_agent` varchar(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas_agricolas`
--

CREATE TABLE `tareas_agricolas` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `parcela_id` int(11) DEFAULT NULL,
  `trabajador_id` int(11) DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `titulo` varchar(180) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` enum('riego','siembra','tratamiento','revision','cosecha','otro') DEFAULT 'revision',
  `prioridad` enum('baja','media','alta') DEFAULT 'media',
  `fecha_programada` date DEFAULT NULL,
  `fecha_limite` date DEFAULT NULL,
  `estado` enum('pendiente','en_proceso','completada','incidencia','cancelada') DEFAULT 'pendiente',
  `responsable` varchar(160) DEFAULT '',
  `observaciones_trabajador` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT NULL,
  `fecha_completada` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `apellido1` varchar(80) DEFAULT '',
  `apellido2` varchar(80) DEFAULT '',
  `email` varchar(160) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('superadmin','jefe','trabajador','peon') NOT NULL DEFAULT 'trabajador',
  `empresa_id` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido1`, `apellido2`, `email`, `password`, `rol`, `empresa_id`, `fecha_creacion`) VALUES
(1, 'SuperAdmin', '', '', 'admin@agroconnect.es', '$2y$12$3QYAFvq5L7Usvr9AaG3YJO4DVwGNwD6BFg5kmpjHo6f10nFE60Pn6', 'superadmin', NULL, '2026-06-09 17:23:36'),
(2, 'Jefe', 'Demo', '', 'jefe@agroconnect.es', '$2y$12$3QYAFvq5L7Usvr9AaG3YJO4DVwGNwD6BFg5kmpjHo6f10nFE60Pn6', 'jefe', 1, '2026-06-09 17:23:36'),
(3, 'Trabajador', 'Demo', '', 'trabajador@agroconnect.es', '$2y$12$3QYAFvq5L7Usvr9AaG3YJO4DVwGNwD6BFg5kmpjHo6f10nFE60Pn6', 'trabajador', 1, '2026-06-09 17:23:36'),
(4, 'Peon', 'Demo', '', 'peon@agroconnect.es', '$2y$12$3QYAFvq5L7Usvr9AaG3YJO4DVwGNwD6BFg5kmpjHo6f10nFE60Pn6', 'peon', 1, '2026-06-09 17:23:36'),
(5, 'Prueba1', 'Misma', '', 'prueba@gmail.com', '$2y$10$RiNGFmtQObuSB0QfXuVWj.ebr6jYVAwrGRSM2E.nTFMxrlP0N7IuO', 'jefe', 2, '2026-06-09 17:58:01');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cultivos`
--
ALTER TABLE `cultivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `parcela_id` (`parcela_id`);

--
-- Indices de la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD UNIQUE KEY `unique_empresa_nombre` (`nombre`);

--
-- Indices de la tabla `foro_respuestas`
--
ALTER TABLE `foro_respuestas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tema_id` (`tema_id`);

--
-- Indices de la tabla `foro_temas`
--
ALTER TABLE `foro_temas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Indices de la tabla `grupos_trabajadores`
--
ALTER TABLE `grupos_trabajadores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `empresa_grupo` (`empresa_id`,`nombre`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Indices de la tabla `grupo_trabajador_miembros`
--
ALTER TABLE `grupo_trabajador_miembros`
  ADD PRIMARY KEY (`grupo_id`,`trabajador_id`),
  ADD KEY `trabajador_id` (`trabajador_id`);

--
-- Indices de la tabla `historico_actividades`
--
ALTER TABLE `historico_actividades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `parcela_id` (`parcela_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `incidencias`
--
ALTER TABLE `incidencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `parcelas`
--
ALTER TABLE `parcelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Indices de la tabla `recursos`
--
ALTER TABLE `recursos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Indices de la tabla `sesiones_activas`
--
ALTER TABLE `sesiones_activas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_session_id` (`session_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `tareas_agricolas`
--
ALTER TABLE `tareas_agricolas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `trabajador_id` (`trabajador_id`),
  ADD KEY `parcela_id` (`parcela_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cultivos`
--
ALTER TABLE `cultivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `foro_respuestas`
--
ALTER TABLE `foro_respuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `foro_temas`
--
ALTER TABLE `foro_temas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `grupos_trabajadores`
--
ALTER TABLE `grupos_trabajadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historico_actividades`
--
ALTER TABLE `historico_actividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `incidencias`
--
ALTER TABLE `incidencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `parcelas`
--
ALTER TABLE `parcelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `recursos`
--
ALTER TABLE `recursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `sesiones_activas`
--
ALTER TABLE `sesiones_activas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `tareas_agricolas`
--
ALTER TABLE `tareas_agricolas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
