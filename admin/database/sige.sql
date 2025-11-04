-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-11-2025 a las 02:32:48
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
-- Base de datos: `sige`
--
CREATE DATABASE IF NOT EXISTS `sige` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `sige`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `about_us`
--

DROP TABLE IF EXISTS `about_us`;
CREATE TABLE `about_us` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `main_image` varchar(255) NOT NULL,
  `image_alt` varchar(255) NOT NULL,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `about_us`
--

INSERT INTO `about_us` (`id`, `title`, `content`, `main_image`, `image_alt`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Bienvenidos a Nuestro Portal Escolar', 'Somos una institución educativa comprometida con la excelencia académica y la formación integral de nuestros estudiantes. Nuestra misión es proporcionar un ambiente de aprendizaje enriquecedor que fomente el crecimiento intelectual, emocional y social.\r\n\r\nContamos con un equipo de educadores altamente calificados y dedicados, que utilizan métodos pedagógicos innovadores para inspirar el amor por el aprendizaje en cada estudiante.\r\n\r\nNuestros valores se centran en el respeto, la responsabilidad, la honestidad y la solidaridad, preparando a nuestros alumnos para los desafíos del futuro.', 'uploads/about_us/68b0dbab4b189.png', 'U.E.N ROBERTO MARTINEZ CENTENO', 1, '2025-10-03 16:01:15', '2025-10-13 02:49:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `academic_page`
--

DROP TABLE IF EXISTS `academic_page`;
CREATE TABLE `academic_page` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `academic_page`
--

INSERT INTO `academic_page` (`id`, `title`, `content`, `banner_image`, `created_at`, `updated_at`) VALUES
(1, 'Excelencia Educativa', '<p class=\"lead\">Nuestra institución se enorgullece de ofrecer programas académicos de alta calidad que preparan a los estudiantes para los desafíos del futuro. Contamos con un plan de estudios integral que combina conocimientos teóricos con aplicaciones prácticas.</p><p>Nuestro enfoque pedagógico se centra en el desarrollo integral de los estudiantes, fomentando el pensamiento crítico, la creatividad y los valores éticos. Utilizamos metodologías innovadoras y tecnología educativa para enriquecer el proceso de enseñanza-aprendizaje.</p>', NULL, '2025-09-02 00:26:24', '2025-09-02 00:26:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `academic_programs`
--

DROP TABLE IF EXISTS `academic_programs`;
CREATE TABLE `academic_programs` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `level` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `more_info_url` varchar(255) DEFAULT NULL,
  `program_order` int(11) DEFAULT 0,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `academic_programs`
--

INSERT INTO `academic_programs` (`id`, `name`, `level`, `description`, `icon`, `more_info_url`, `program_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Educación Primaria', 'Ciclo Básico', 'Programa integral para estudiantes de 6 a 12 años, enfocado en el desarrollo de competencias básicas.', 'fas fa-pencil-alt', 'educacion-primaria.php', 1, 1, '2025-09-02 00:26:24', '2025-10-13 03:30:57'),
(2, 'Educación Secundaria', 'Bachillerato', 'Formación preparatoria para la educación superior con enfoque en diversas áreas del conocimiento.', 'fas fa-graduation-cap', 'educacion-secundaria.php', 2, 1, '2025-09-02 00:26:24', '2025-10-13 03:41:55'),
(3, 'Programas Especiales', 'Extracurriculares', 'Actividades complementarias que incluyen arte, deportes, tecnología y liderazgo.', 'fas fa-star', 'programas-especiales.php', 3, 1, '2025-09-02 00:26:24', '2025-10-13 03:42:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `academic_resources`
--

DROP TABLE IF EXISTS `academic_resources`;
CREATE TABLE `academic_resources` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `academic_resources`
--

INSERT INTO `academic_resources` (`id`, `title`, `category`, `description`, `url`, `icon`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Guías de estudio por materia', 'Biblioteca', 'Material de apoyo para todas las materias', '#', 'fas fa-file-pdf', 1, '2025-09-02 00:26:24', '2025-09-02 00:26:24'),
(2, 'Libros de texto digitales', 'Biblioteca', 'Textos oficiales en formato digital', '#', 'fas fa-book', 1, '2025-09-02 00:26:24', '2025-09-02 00:26:24'),
(3, 'Revistas académicas', 'Biblioteca', 'Publicaciones periódicas especializadas', '#', 'fas fa-newspaper', 1, '2025-09-02 00:26:24', '2025-09-02 00:26:24'),
(4, 'Tutoriales y video-lecciones', 'Biblioteca', 'Contenido multimedia educativo', '#', 'fas fa-video', 1, '2025-09-02 00:26:24', '2025-09-02 00:26:24'),
(5, 'Aula Virtual', 'Plataformas', 'Acceso a clases virtuales', '#', 'fas fa-globe', 1, '2025-09-02 00:26:24', '2025-09-02 00:26:24'),
(6, 'Sistema de Gestión de Aprendizaje', 'Plataformas', 'Plataforma principal de estudios', '#', 'fas fa-chalkboard-teacher', 1, '2025-09-02 00:26:24', '2025-09-02 00:26:24'),
(7, 'Plataforma de Calificaciones', 'Plataformas', 'Consulta de calificaciones en línea', '#', 'fas fa-chart-bar', 1, '2025-09-02 00:26:24', '2025-09-02 00:26:24'),
(8, 'Foros de Discusión', 'Plataformas', 'Espacio para debates académicos', '#', 'fas fa-comments', 1, '2025-09-02 00:26:24', '2025-09-02 00:26:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` text NOT NULL,
  `date_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administrativos`
--

DROP TABLE IF EXISTS `administrativos`;
CREATE TABLE `administrativos` (
  `id_administrativo` int(11) NOT NULL,
  `persona_id` int(11) NOT NULL,
  `fyh_creacion` datetime DEFAULT NULL,
  `fyh_actualizacion` datetime DEFAULT NULL,
  `estado` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `administrativos`
--

INSERT INTO `administrativos` (`id_administrativo`, `persona_id`, `fyh_creacion`, `fyh_actualizacion`, `estado`) VALUES
(7, 28, '2025-10-31 00:00:00', '2025-11-03 00:00:00', '1'),
(8, 29, '2025-10-31 00:00:00', '2025-11-03 00:00:00', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaciones_profesor`
--

DROP TABLE IF EXISTS `asignaciones_profesor`;
CREATE TABLE `asignaciones_profesor` (
  `id_asignacion` int(11) NOT NULL,
  `id_profesor` int(11) NOT NULL,
  `id_materia` int(11) NOT NULL,
  `id_seccion` int(11) NOT NULL,
  `id_gestion` int(11) NOT NULL,
  `estado` tinyint(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `asignaciones_profesor`
--

INSERT INTO `asignaciones_profesor` (`id_asignacion`, `id_profesor`, `id_materia`, `id_seccion`, `id_gestion`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(52, 3, 3, 98, 34, 1, '2025-10-31 21:42:12', '2025-11-03 22:52:56'),
(53, 3, 5, 97, 34, 1, '2025-10-31 21:53:40', '2025-10-31 21:53:40'),
(54, 3, 3, 100, 34, 1, '2025-10-31 21:55:51', '2025-10-31 21:55:51'),
(55, 3, 4, 100, 34, 1, '2025-10-31 22:07:50', '2025-10-31 22:07:50'),
(56, 3, 3, 99, 34, 1, '2025-10-31 22:17:44', '2025-10-31 22:17:44'),
(57, 3, 6, 97, 34, 1, '2025-10-31 22:18:22', '2025-10-31 22:18:22'),
(58, 3, 1, 100, 34, 1, '2025-10-31 23:40:01', '2025-10-31 23:40:01'),
(59, 4, 3, 98, 34, 1, '2025-11-01 05:50:30', '2025-11-01 05:50:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calendario_academico`
--

DROP TABLE IF EXISTS `calendario_academico`;
CREATE TABLE `calendario_academico` (
  `id` int(11) NOT NULL,
  `evento` varchar(255) NOT NULL,
  `tipo_evento` enum('inscripcion','inicio_clases','fin_lapso','vacaciones','evaluacion','otro') NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `nivel_educativo` enum('inicial','primaria','secundaria','media','todos') DEFAULT 'todos',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `calendario_academico`
--

INSERT INTO `calendario_academico` (`id`, `evento`, `tipo_evento`, `fecha_inicio`, `fecha_fin`, `descripcion`, `nivel_educativo`, `activo`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Inscripciones para Educación Inicial', 'inscripcion', '2024-09-02', '2024-09-13', 'Período de inscripción para estudiantes de educación inicial', 'inicial', 1, '2025-09-02 00:55:31', '2025-09-02 00:55:31'),
(2, 'Inscripciones para Educación Primaria', 'inscripcion', '2024-09-02', '2024-09-13', 'Período de inscripción para estudiantes de educación primaria', 'primaria', 1, '2025-09-02 00:55:31', '2025-09-02 00:55:31'),
(3, 'Inscripciones para Educación Media', 'inscripcion', '2024-09-02', '2024-09-13', 'Período de inscripción para estudiantes de educación media', 'media', 1, '2025-09-02 00:55:31', '2025-09-02 00:55:31'),
(4, 'Inicio del Año Escolar', 'inicio_clases', '2024-09-16', NULL, 'Inicio del año escolar 2024-2025 en todo el país', 'todos', 1, '2025-09-02 00:55:31', '2025-09-02 00:55:31'),
(5, 'Finalización del Primer Lapso', 'fin_lapso', '2024-12-13', NULL, 'Culminación del primer lapso del año escolar', 'todos', 1, '2025-09-02 00:55:31', '2025-09-02 00:55:31'),
(6, 'Inicio del Segundo Lapso', 'inicio_clases', '2025-01-06', NULL, 'Inicio del segundo lapso del año escolar', 'todos', 1, '2025-09-02 00:55:31', '2025-09-02 00:55:31'),
(7, 'Finalización del Segundo Lapso', 'fin_lapso', '2025-03-28', NULL, 'Culminación del segundo lapso del año escolar', 'todos', 1, '2025-09-02 00:55:31', '2025-09-02 00:55:31'),
(8, 'Inicio del Tercer Lapso', 'inicio_clases', '2025-04-07', NULL, 'Inicio del tercer lapso del año escolar', 'todos', 1, '2025-09-02 00:55:31', '2025-09-02 00:55:31'),
(9, 'Finalización del Tercer Lapso', 'fin_lapso', '2025-07-11', NULL, 'Culminación del tercer lapso y fin del año escolar', 'todos', 1, '2025-09-02 00:55:31', '2025-09-02 00:55:31'),
(10, 'Vacaciones de Navidad', 'vacaciones', '2024-12-16', '2025-01-05', 'Periodo vacacional de navidad y año nuevo', 'todos', 1, '2025-09-02 00:55:31', '2025-09-02 00:55:31'),
(11, 'Vacaciones de Semana Santa', 'vacaciones', '2025-04-14', '2025-04-20', 'Periodo vacacional de semana santa', 'todos', 1, '2025-09-02 00:55:31', '2025-09-02 00:55:31'),
(12, 'Evaluaciones Primer Lapso', 'evaluacion', '2024-12-02', '2024-12-12', 'Periodo de evaluaciones del primer lapso', 'todos', 1, '2025-09-02 00:55:31', '2025-09-02 00:55:31'),
(13, 'Evaluaciones Segundo Lapso', 'evaluacion', '2025-03-17', '2025-03-27', 'Periodo de evaluaciones del segundo lapso', 'todos', 1, '2025-09-02 00:55:31', '2025-09-02 00:55:31'),
(14, 'Evaluaciones Tercer Lapso', 'evaluacion', '2025-06-30', '2025-07-10', 'Periodo de evaluaciones del tercer lapso', 'todos', 1, '2025-09-02 00:55:31', '2025-09-02 00:55:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carnets_emitidos`
--

DROP TABLE IF EXISTS `carnets_emitidos`;
CREATE TABLE `carnets_emitidos` (
  `id_emision` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `id_diseno` int(11) NOT NULL,
  `fecha_emision` date NOT NULL,
  `fecha_expiracion` date NOT NULL,
  `codigo_qr` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carnets_estudiantiles`
--

DROP TABLE IF EXISTS `carnets_estudiantiles`;
CREATE TABLE `carnets_estudiantiles` (
  `id_carnet` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `id_plantilla` int(11) NOT NULL,
  `codigo_barras` varchar(50) DEFAULT NULL,
  `qr_code` varchar(50) DEFAULT NULL,
  `fecha_emision` datetime DEFAULT current_timestamp(),
  `fecha_vencimiento` date DEFAULT NULL,
  `estatus` enum('activo','vencido','inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrusel`
--

DROP TABLE IF EXISTS `carrusel`;
CREATE TABLE `carrusel` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen_path` varchar(255) NOT NULL DEFAULT 'default.png',
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carrusel`
--

INSERT INTO `carrusel` (`id`, `titulo`, `descripcion`, `imagen_path`, `fecha_inicio`, `fecha_fin`, `activo`, `fecha_creacion`) VALUES
(1, 'Bienvenido al Portal Escolar', NULL, '1.png', '2025-10-12', '2026-07-24', 1, '2025-10-13 02:27:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_conexiones`
--

DROP TABLE IF EXISTS `chat_conexiones`;
CREATE TABLE `chat_conexiones` (
  `id_conexion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `socket_id` varchar(255) DEFAULT NULL,
  `ultima_conexion` datetime DEFAULT current_timestamp(),
  `estado` enum('online','offline') DEFAULT 'online'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_mensajes`
--

DROP TABLE IF EXISTS `chat_mensajes`;
CREATE TABLE `chat_mensajes` (
  `id_mensaje` int(11) NOT NULL,
  `id_remitente` int(11) NOT NULL,
  `id_destinatario` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `archivo` varchar(255) DEFAULT NULL,
  `fecha_envio` datetime DEFAULT current_timestamp(),
  `leido` tinyint(1) DEFAULT 0,
  `estado` tinyint(1) DEFAULT 1,
  `editado` enum('0','1') DEFAULT '0',
  `reacciones` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`reacciones`)),
  `fecha_edicion` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `chat_mensajes`
--

INSERT INTO `chat_mensajes` (`id_mensaje`, `id_remitente`, `id_destinatario`, `mensaje`, `archivo`, `fecha_envio`, `leido`, `estado`, `editado`, `reacciones`, `fecha_edicion`) VALUES
(1, 1, 75, 'Hola Buenas noches', NULL, '2025-10-14 22:06:39', 1, 1, '0', NULL, NULL),
(2, 75, 1, 'Buenas noches administrador como esta', NULL, '2025-10-14 22:10:05', 1, 1, '0', NULL, NULL),
(3, 1, 75, '', 'app/uploads/1_20251014_222135_85a4abcb.png', '2025-10-14 22:21:35', 1, 1, '0', NULL, NULL),
(4, 1, 75, '', 'app/uploads/agustinzamora_20251016_160218_98d8f0f5.jpg', '2025-10-16 16:02:18', 1, 1, '0', '[]', NULL),
(5, 1, 75, 'Hola', NULL, '2025-10-16 16:03:05', 1, 1, '0', '[]', NULL),
(6, 1, 75, 'hola', NULL, '2025-10-16 16:03:17', 1, 1, '0', '[]', NULL),
(7, 1, 75, 'holaaa', NULL, '2025-10-16 16:04:54', 1, 1, '0', '[]', NULL),
(8, 1, 75, '', 'app/uploads/673cb963-e1e1-4996-97bb-5897de8c55d3_20251016_160517_d65b98de.jfif', '2025-10-16 16:05:17', 1, 1, '0', '[]', NULL),
(9, 76, 1, 'hola', NULL, '2025-11-03 13:17:05', 1, 1, '0', '[]', NULL),
(10, 76, 1, '', 'app/uploads/escudo_20251103_131745_fb07eacf.jfif', '2025-11-03 13:17:45', 1, 1, '0', '[]', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_reacciones`
--

DROP TABLE IF EXISTS `chat_reacciones`;
CREATE TABLE `chat_reacciones` (
  `id_reaccion` int(11) NOT NULL,
  `id_mensaje` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo_reaccion` enum('like','love','haha','wow','sad','angry') NOT NULL,
  `fecha_reaccion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas_nacimiento`
--

DROP TABLE IF EXISTS `citas_nacimiento`;
CREATE TABLE `citas_nacimiento` (
  `id` int(11) NOT NULL,
  `nombre_completo` varchar(255) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `fecha_cita` date NOT NULL,
  `hora_cita` time NOT NULL,
  `confirmacion_docs` tinyint(1) NOT NULL,
  `estado` enum('pendiente','confirmada','completada','cancelada') DEFAULT 'pendiente',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `codigo_confirmacion` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `citas_nacimiento`
--

INSERT INTO `citas_nacimiento` (`id`, `nombre_completo`, `cedula`, `telefono`, `email`, `fecha_cita`, `hora_cita`, `confirmacion_docs`, `estado`, `fecha_registro`, `codigo_confirmacion`) VALUES
(1, 'Daniel Eduardo Villanueva Quintero', 'V-29720599', '04164634936', 'dv47762@gmail.com', '2025-08-29', '14:00:00', 1, 'pendiente', '2025-08-28 16:04:55', '2SE7MR'),
(2, 'Daniela Alejandra Villanueva Quintero', 'V-20720599', '04164634936', 'dv47762@gmail.com', '2025-08-29', '16:00:00', 1, 'pendiente', '2025-08-28 19:31:51', '37F0M8');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `collaborators`
--

DROP TABLE IF EXISTS `collaborators`;
CREATE TABLE `collaborators` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `collaborators`
--

INSERT INTO `collaborators` (`id`, `name`, `logo`, `website`, `status`, `created_at`, `updated_at`) VALUES
(1, 'El Ministerio del Poder Popular para la Educación', 'uploads/collaborators/collaborators_1756346146_68afb722a3145.png', 'https://www.mppe.gob.ve/', 1, '2025-10-03 15:53:16', '2025-10-03 15:53:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_instituciones`
--

DROP TABLE IF EXISTS `configuracion_instituciones`;
CREATE TABLE `configuracion_instituciones` (
  `id_config_institucion` int(11) NOT NULL,
  `nombre_institucion` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `fondo` varchar(100) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `telefono` varchar(100) DEFAULT NULL,
  `celular` varchar(100) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `fyh_creacion` datetime DEFAULT NULL,
  `fyh_actualizacion` datetime DEFAULT NULL,
  `estado` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion_instituciones`
--

INSERT INTO `configuracion_instituciones` (`id_config_institucion`, `nombre_institucion`, `logo`, `fondo`, `direccion`, `telefono`, `celular`, `correo`, `fyh_creacion`, `fyh_actualizacion`, `estado`) VALUES
(1, 'U.E.N ROBERTO MARTINEZ CENTENO', '2025-05-03-15-01-46logo.jpg', '', 'Parroquia Caricuao, Avenida Este 0, Caracas, Distrito Capital, adscrito a la Zona Educativa del Estado Distrito Capital', '02124331080', '', 'admin@gmail.com', '2023-12-28 20:29:10', '2025-05-03 00:00:00', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `config_carnets`
--

DROP TABLE IF EXISTS `config_carnets`;
CREATE TABLE `config_carnets` (
  `id_config` int(11) NOT NULL,
  `universidad_linea1` varchar(100) NOT NULL,
  `universidad_linea2` varchar(100) NOT NULL,
  `nombre_universidad` varchar(100) NOT NULL,
  `sede` varchar(100) NOT NULL,
  `siglas` varchar(20) NOT NULL,
  `sedes` varchar(100) NOT NULL,
  `texto_pie1` text NOT NULL,
  `texto_pie2` text NOT NULL,
  `firma_nombre` varchar(100) NOT NULL,
  `firma_cargo` varchar(100) NOT NULL,
  `telefono_emergencia` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contactos`
--

DROP TABLE IF EXISTS `contactos`;
CREATE TABLE `contactos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `estado` varchar(100) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `comentarios` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `datos_institucion`
--

DROP TABLE IF EXISTS `datos_institucion`;
CREATE TABLE `datos_institucion` (
  `id_institucion` int(11) NOT NULL,
  `nombre_institucion` varchar(100) NOT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `sitio_web` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `diseno_carnets`
--

DROP TABLE IF EXISTS `diseno_carnets`;
CREATE TABLE `diseno_carnets` (
  `id_diseno` int(11) NOT NULL,
  `nombre_diseno` varchar(100) NOT NULL DEFAULT 'Predeterminado',
  `logo_path` varchar(255) DEFAULT NULL,
  `logo_pos_x` int(11) DEFAULT 10,
  `logo_pos_y` int(11) DEFAULT 10,
  `logo_width` int(11) DEFAULT 30,
  `logo_height` int(11) DEFAULT 30,
  `foto_estudiante_pos_x` int(11) DEFAULT 15,
  `foto_estudiante_pos_y` int(11) DEFAULT 50,
  `foto_estudiante_width` int(11) DEFAULT 25,
  `foto_estudiante_height` int(11) DEFAULT 30,
  `qr_pos_x` int(11) DEFAULT 60,
  `qr_pos_y` int(11) DEFAULT 50,
  `qr_size` int(11) DEFAULT 25,
  `color_fondo` varchar(20) DEFAULT '#FFFFFF',
  `color_texto` varchar(20) DEFAULT '#000000',
  `fuente_principal` varchar(50) DEFAULT 'Arial',
  `mostrar_qr` tinyint(1) DEFAULT 1,
  `texto_superior` text DEFAULT NULL,
  `texto_inferior` text DEFAULT NULL,
  `firma_path` varchar(255) DEFAULT NULL,
  `firma_pos_x` int(11) DEFAULT 50,
  `firma_pos_y` int(11) DEFAULT 80,
  `firma_width` int(11) DEFAULT 30,
  `firma_height` int(11) DEFAULT 15,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `diseno_carnets`
--

INSERT INTO `diseno_carnets` (`id_diseno`, `nombre_diseno`, `logo_path`, `logo_pos_x`, `logo_pos_y`, `logo_width`, `logo_height`, `foto_estudiante_pos_x`, `foto_estudiante_pos_y`, `foto_estudiante_width`, `foto_estudiante_height`, `qr_pos_x`, `qr_pos_y`, `qr_size`, `color_fondo`, `color_texto`, `fuente_principal`, `mostrar_qr`, `texto_superior`, `texto_inferior`, `firma_path`, `firma_pos_x`, `firma_pos_y`, `firma_width`, `firma_height`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Predeterminado', NULL, 10, 10, 30, 30, 15, 50, 25, 30, 60, 50, 25, '#FFFFFF', '#000000', 'Arial', 1, 'Universidad Nacional Experimental|de los Llanos Occidentales|Ezequiel Zamora|UNELLEZ', 'Credencial Estudiantil|ViceRectorado de Producción Agrícola|Carnet válido hasta: {fecha_expiracion}', NULL, 50, 80, 30, 15, 1, '2025-05-12 01:31:00', '2025-05-12 01:31:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docentes`
--

DROP TABLE IF EXISTS `docentes`;
CREATE TABLE `docentes` (
  `id_docente` int(11) NOT NULL,
  `persona_id` int(11) NOT NULL,
  `especialidad` varchar(255) NOT NULL,
  `antiguedad` varchar(255) NOT NULL,
  `fyh_creacion` date DEFAULT NULL,
  `fyh_actualizacion` date DEFAULT NULL,
  `estado` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documento`
--

DROP TABLE IF EXISTS `documento`;
CREATE TABLE `documento` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(250) NOT NULL,
  `archivo` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos`
--

DROP TABLE IF EXISTS `documentos`;
CREATE TABLE `documentos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `archivo` longblob DEFAULT NULL,
  `tipo_archivo` varchar(100) DEFAULT NULL,
  `tamaño` int(11) NOT NULL,
  `archivo_nombre` varchar(255) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1,
  `descargas` int(11) DEFAULT 0,
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `documentos`
--

INSERT INTO `documentos` (`id`, `nombre`, `tipo`, `descripcion`, `archivo`, `tipo_archivo`, `tamaño`, `archivo_nombre`, `fecha_creacion`, `activo`, `descargas`, `usuario_id`) VALUES
(1, 'Línea Presión Agua Fría', 'PDF', 'Ideal para sistemas de agua potable en cualquier tipo de edificación, sistemas para piscinas, sistemas de agua helada (aire acondicionado) y riego. No transmite ni olor ni sabor al agua o fluido que por ella circula. Es inmune a la corrosión, no se oxida.', 0x363862343765643832653963385f313735363635393431362e706466, '', 218072, 'Línea Presión Agua Fría', '2025-08-31 16:57:04', 1, 0, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos_internos`
--

DROP TABLE IF EXISTS `documentos_internos`;
CREATE TABLE `documentos_internos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `archivo` longblob DEFAULT NULL,
  `tipo_archivo` varchar(100) DEFAULT NULL,
  `tamaño` int(11) NOT NULL,
  `archivo_nombre` varchar(255) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1,
  `descargas` int(11) DEFAULT 0,
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `documentos_internos`
--

INSERT INTO `documentos_internos` (`id`, `nombre`, `tipo`, `descripcion`, `archivo`, `tipo_archivo`, `tamaño`, `archivo_nombre`, `fecha_creacion`, `activo`, `descargas`, `usuario_id`) VALUES
(1, 'Línea Presión Agua Fría', 'PDF', 'Ideal para sistemas de agua potable en cualquier tipo de edificación, sistemas para piscinas, sistemas de agua helada (aire acondicionado) y riego. No transmite ni olor ni sabor al agua o fluido que por ella circula. Es inmune a la corrosión, no se oxida.', 0x363862343765643832653963385f313735363635393431362e706466, NULL, 218072, NULL, '2025-08-31 16:56:56', 1, 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes`
--

DROP TABLE IF EXISTS `estudiantes`;
CREATE TABLE `estudiantes` (
  `id_estudiante` int(11) NOT NULL,
  `tipo_cedula` varchar(50) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `cedula_escolar` varchar(255) DEFAULT NULL,
  `posicion_hijo` int(11) DEFAULT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `genero` enum('masculino','femenino') NOT NULL,
  `correo_electronico` varchar(100) NOT NULL,
  `direccion` varchar(200) NOT NULL,
  `numeros_telefonicos` varchar(20) NOT NULL,
  `id_representante` int(11) NOT NULL,
  `turno_id` int(11) NOT NULL,
  `estatus` enum('activo','inactivo') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tipo_discapacidad` varchar(50) DEFAULT NULL,
  `foto` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estudiantes`
--

INSERT INTO `estudiantes` (`id_estudiante`, `tipo_cedula`, `cedula`, `cedula_escolar`, `posicion_hijo`, `nombres`, `apellidos`, `fecha_nacimiento`, `genero`, `correo_electronico`, `direccion`, `numeros_telefonicos`, `id_representante`, `turno_id`, `estatus`, `created_at`, `updated_at`, `tipo_discapacidad`, `foto`) VALUES
(1, 'V', '30045678', NULL, 0, 'Carlos Eduardo', 'Pérez López', '2015-05-10', 'masculino', 'carloseduardo15@gmail.com', 'San Juan de Los Morros', '0412-1234567', 1, 1, 'inactivo', '2025-01-24 12:27:15', '2025-01-25 01:57:29', 'ninguna', ''),
(2, 'V', '30045679', NULL, 0, 'Ana Lucía', 'Pérez López', '2016-06-15', 'femenino', 'analucia15@gmail.com', 'El Junquito', '0414-1234568', 1, 1, 'inactivo', '2025-01-24 12:27:15', '2025-01-25 01:57:56', 'ninguna', ''),
(3, 'V', '30045680', NULL, 0, 'Luis Miguel', 'Pérez López', '2017-07-20', 'masculino', 'luismiguel15@gmail.com', 'La Candelaria', '0416-1234569', 1, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(4, 'V', '31234567', NULL, 0, 'María Fernanda', 'González Torres', '2015-08-25', 'femenino', 'mariafernanda31@gmail.com', 'Santa Teresa', '0424-1234570', 2, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(5, 'V', '31234568', '', 0, 'Diego Alejandro', 'González Torres', '2016-09-30', 'masculino', 'diegoalejandro31@gmail.com', 'Los Palos Grandes', '04164634936', 2, 1, 'activo', '2025-01-24 12:27:15', '2025-02-08 03:28:44', 'ninguna', ''),
(6, 'V', '31234569', NULL, 0, 'Sofía Alejandra', 'González Torres', '2017-10-10', 'femenino', 'sofiaalejandra31@gmail.com', 'El Hatillo', '0412-1234572', 2, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(7, 'V', '32135798', NULL, 0, 'Javier Alejandro', 'Martínez Ruiz', '2015-11-10', 'masculino', 'javieralejandro32@gmail.com', 'Caricuao', '0414-1234580', 3, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(8, 'V', '32135799', NULL, 0, 'Lucía Fernanda', 'Martínez Ruiz', '2016-12-15', 'femenino', 'luciafernanda32@gmail.com', 'Los Rosales', '0416-1234581', 3, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(9, 'V', '32135800', NULL, 0, 'María José', 'Martínez Ruiz', '2017-01-20', 'femenino', 'mariajose32@gmail.com', 'Coche', '0424-1234582', 3, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(10, 'V', '33345678', NULL, 0, 'Carlos Andrés', 'Díaz López', '2015-05-30', 'masculino', 'carlosandres34@gmail.com', 'La Vega', '0426-1234590', 4, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(11, 'V', '33345679', NULL, 0, 'Isabella', 'Díaz López', '2016-06-25', 'femenino', 'isabelladiaz34@gmail.com', 'Los Teques', '0412-1234591', 4, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(12, 'V', '33345680', NULL, 0, 'Santiago', 'Díaz López', '2017-07-15', 'masculino', 'santiagodiaz34@gmail.com', 'Chacao', '0414-1234592', 4, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(13, 'V', '30167891', NULL, 0, 'Valentina', 'Hernández García', '2015-08-25', 'femenino', 'valentinahernandez30@gmail.com', 'Los Dos Caminos', '0416-1234593', 5, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(14, 'V', '30167892', NULL, 0, 'Fernando', 'Hernández García', '2016-09-20', 'masculino', 'fernandohernandez30@gmail.com', 'El Paraíso', '0424-1234501', 5, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(15, 'V', '30167893', NULL, 0, 'Gabriel', 'Hernández García', '2017-10-15', 'masculino', 'gabrielhernandez30@gmail.com', 'Las Mercedes', '0426-1234502', 5, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(16, 'V', '31890123', NULL, 0, 'Mateo', 'Ramírez Fernández', '2015-11-30', 'masculino', 'mateoramirez31@gmail.com', 'Sabana Grande', '0412-1234503', 6, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(17, 'V', '31890124', NULL, 0, 'Camila', 'Ramírez Fernández', '2016-12-31', 'femenino', 'camilaramirez31@gmail.com', 'Los Chaguaramos', '0414-1234504', 6, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(18, 'V', '31890125', NULL, 0, 'Diego', 'Ramírez Fernández', '2017-01-15', 'masculino', 'diegoramirez31@gmail.com', 'Catia', '0416-1234505', 6, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(19, 'V', '32678910', NULL, 0, 'Camilo', 'Morales López', '2015-02-25', 'masculino', 'camilomorales32@gmail.com', 'La Urbina', '0424-1234506', 7, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(20, 'V', '32678911', NULL, 0, 'Natalia', 'Morales López', '2016-03-16', 'femenino', 'nataliamorales32@gmail.com', 'Boleíta', '0426-1234507', 7, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(21, 'V', '32678912', NULL, 0, 'Arturo', 'Morales López', '2017-04-17', 'masculino', 'arturomorales32@gmail.com', 'El Cafetal', '0412-1234508', 7, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(22, 'V', '33789012', NULL, 0, 'Leo', 'Ortega Medina', '2015-05-18', 'masculino', 'leootegamedina33@gmail.com', 'Catedral', '0414-1234509', 8, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(23, 'V', '33789013', NULL, 0, 'Valeria', 'Ortega Medina', '2016-06-29', 'femenino', 'valeriaortegamedina33@gmail.com', 'Calle Real', '0416-1234510', 8, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(24, 'V', '33789014', NULL, 0, 'Esteban', 'Ortega Medina', '2017-07-10', 'masculino', 'estebanortegamedina33@gmail.com', 'San Bernardino', '0424-1234511', 8, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(25, 'V', '30231456', NULL, 0, 'Claudia', 'Chapman Ruiz', '2015-08-26', 'femenino', 'claudiachapman30@gmail.com', 'Los Palos Grandes', '0426-1234512', 9, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(26, 'V', '30231457', NULL, 0, 'Felipe', 'Chapman Ruiz', '2016-09-12', 'masculino', 'felipechapman30@gmail.com', 'Plaza Venezuela', '0412-1234513', 9, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(27, 'V', '30231458', NULL, 0, 'Juan', 'Chapman Ruiz', '2017-10-05', 'masculino', 'juanchapman30@gmail.com', 'Miranda', '0414-1234514', 9, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(28, 'V', '31567890', NULL, 0, 'Simón', 'Salazar Pérez', '2015-11-11', 'masculino', 'simonsalazar31@gmail.com', 'Catia La Mar', '0416-1234515', 10, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(29, 'V', '31567891', NULL, 0, 'María', 'Salazar Pérez', '2016-12-12', 'femenino', 'mariasalazar31@gmail.com', 'Tarqui', '0424-1234516', 10, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(30, 'V', '31567892', NULL, 0, 'Leonardo', 'Salazar Pérez', '2017-01-13', 'masculino', 'leonardosalazar31@gmail.com', 'La Grita', '0426-1234517', 10, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(31, 'V', '32987654', NULL, 0, 'Estefanía', 'Carrillo Martínez', '2015-02-14', 'femenino', 'estefaniacarrillo32@gmail.com', 'El Valle', '0412-1234518', 11, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(32, 'V', '32987655', NULL, 0, 'Diego', 'Carrillo Martínez', '2016-03-15', 'masculino', 'diegocarrillo32@gmail.com', 'La Bandera', '0414-1234519', 11, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(33, 'V', '32987656', NULL, 0, 'Gabriela', 'Carrillo Martínez', '2017-04-16', 'femenino', 'gabrielacarrillo32@gmail.com', 'Antímano', '0416-1234520', 11, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(34, 'V', '33555555', NULL, 0, 'Pablo', 'García López', '2015-02-14', 'masculino', 'pablogarcia33@gmail.com', 'Río de Janeiro', '0412-1234571', 12, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(35, 'V', '33555556', NULL, 0, 'Laura', 'García López', '2016-03-15', 'femenino', 'lauragarcia33@gmail.com', 'Avenida Bolívar', '0414-1234572', 12, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(36, 'V', '33555557', NULL, 0, 'Ricardo', 'García López', '2017-04-16', 'masculino', 'ricardogarcia33@gmail.com', 'Bello Campo', '0416-1234573', 12, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(37, 'V', '30654321', NULL, 0, 'Martina', 'Blanco Rodríguez', '2015-05-18', 'femenino', 'martinablancor33@gmail.com', 'La Yaguara', '0424-1234581', 13, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(38, 'V', '30654322', NULL, 0, 'Santiago', 'Blanco Rodríguez', '2016-06-21', 'masculino', 'santiagoblanco33@gmail.com', 'Tamanaco', '0426-1234582', 13, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(39, 'V', '30654323', NULL, 0, 'Gabriela', 'Blanco Rodríguez', '2017-07-24', 'femenino', 'gabrielablanco33@gmail.com', 'Montalbán', '0412-1234583', 13, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(40, 'V', '31112233', NULL, 0, 'Fernando', 'Castillo Mendoza', '2015-08-26', 'masculino', 'fernandocastillo33@gmail.com', 'Las Acacias', '0414-1234591', 14, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(41, 'V', '31112234', NULL, 0, 'Maria', 'Castillo Mendoza', '2016-09-27', 'femenino', 'mariacastillo33@gmail.com', 'Palo Verde', '0416-1234592', 14, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(42, 'V', '31112235', NULL, 0, 'Javier', 'Castillo Mendoza', '2017-10-18', 'masculino', 'javiercastillo33@gmail.com', 'Cerro Verde', '0424-1234593', 14, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(43, 'V', '32443322', NULL, 0, 'Esteban', 'Rivas Araujo', '2015-11-30', 'masculino', 'estebanrivas33@gmail.com', 'Los Teques', '0426-1234501', 15, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(44, 'V', '32443323', NULL, 0, 'Anabella', 'Rivas Araujo', '2016-12-12', 'femenino', 'anabellarivas33@gmail.com', 'Baruta', '0412-1234502', 15, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(45, 'V', '32443324', NULL, 0, 'Joaquín', 'Rivas Araujo', '2017-01-18', 'masculino', 'joaquinrivas33@gmail.com', 'Guarenas', '0414-1234503', 15, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(46, 'V', '33334455', NULL, 0, 'Marisol', 'Soto Castillo', '2015-02-17', 'femenino', 'marisolsotoc33@gmail.com', 'Santa Fe', '0416-1234501', 16, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(47, 'V', '33334456', NULL, 0, 'Ramón', 'Soto Castillo', '2016-03-31', 'masculino', 'ramonsotoc33@gmail.com', 'La Trinidad', '0424-1234502', 16, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(48, 'V', '33334457', NULL, 0, 'Virginia', 'Soto Castillo', '2017-05-18', 'femenino', 'virginiasotoc33@gmail.com', 'La Candelaria', '0426-1234503', 16, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(49, 'V', '30112233', NULL, 0, 'Óscar', 'Vásquez Pérez', '2015-06-30', 'masculino', 'oscargasquez33@gmail.com', 'Peñalver', '0412-1234501', 17, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(50, 'V', '30112234', NULL, 0, 'Evelyn', 'Vásquez Pérez', '2016-07-14', 'femenino', 'evelynvasquez33@gmail.com', 'Antímano', '0414-1234502', 17, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(51, 'V', '30112235', NULL, 0, 'Mateo', 'Vásquez Pérez', '2017-08-19', 'masculino', 'mateovasquez33@gmail.com', 'Cantaura', '0416-1234503', 17, 1, 'activo', '2025-01-24 12:27:15', '2025-01-24 12:47:31', 'ninguna', ''),
(56, 'V', '33200918', NULL, 0, 'Misael David', 'Marquez Cruz', '2019-05-24', 'masculino', 'misaelmarquez@gmail.com', 'Parroquia Caricuao Ud1', '04121988817', 113, 0, 'activo', '2025-01-24 11:23:20', '2025-01-24 11:23:20', 'ninguna', ''),
(61, 'V', '33200919', NULL, 0, 'Juan Carlos', 'Pérez López', '2018-04-15', 'masculino', 'juancarlos@gmail.com', 'Parroquia Caricuao Ud1', '04121234567', 113, 0, 'activo', '2025-01-24 11:30:25', '2025-01-24 11:30:25', 'ninguna', ''),
(62, 'V', '33200920', NULL, 0, 'Ana María', 'González Torres', '2017-03-10', 'femenino', 'anagonzalez@gmail.com', 'Parroquia Caricuao Ud1', '04121234568', 113, 0, 'activo', '2025-01-24 11:30:25', '2025-01-24 11:30:25', 'ninguna', ''),
(63, 'V', '33200921', NULL, 0, 'Luis Fernando', 'Martínez Ruiz', '2016-02-20', 'masculino', 'luismartinez@gmail.com', 'Parroquia Caricuao Ud1', '04121234569', 113, 0, 'activo', '2025-01-24 11:30:25', '2025-01-24 11:30:25', 'ninguna', ''),
(64, 'V', '', 'V21914756124', 2, 'Sofía Isabel', 'Ramírez López', '2019-01-15', 'femenino', 'sofiaramirez@gmail.com', 'Parroquia Caricuao Ud1', '04121234570', 113, 0, 'activo', '2025-01-24 11:30:25', '2025-01-24 11:33:09', 'ninguna', ''),
(65, 'V', '33200922', NULL, 0, 'Carlos Alberto', 'Hernández Pérez', '2018-05-10', 'masculino', 'carloshp@gmail.com', 'Parroquia Caricuao Ud1', '04121234571', 105, 0, 'activo', '2025-01-24 11:47:17', '2025-01-24 11:47:17', 'ninguna', ''),
(66, 'V', '33200923', NULL, 0, 'María José', 'López García', '2017-06-15', 'femenino', 'mariajose@gmail.com', 'Parroquia Caricuao Ud1', '04121234572', 105, 0, 'activo', '2025-01-24 11:47:17', '2025-01-24 11:47:17', 'ninguna', ''),
(67, 'V', '33200924', NULL, 0, 'Andrés Felipe', 'Martínez Torres', '2016-07-20', 'masculino', 'andresfelipe@gmail.com', 'Parroquia Caricuao Ud1', '04121234573', 105, 0, 'activo', '2025-01-24 11:47:17', '2025-01-24 11:47:17', 'ninguna', ''),
(68, 'V', '33200925', NULL, 0, 'Isabella', 'Ramírez López', '2019-08-25', 'femenino', 'isabellar@gmail.com', 'Parroquia Caricuao Ud1', '04121234574', 105, 0, 'activo', '2025-01-24 11:47:17', '2025-01-24 11:47:17', 'ninguna', ''),
(69, 'V', '33200926', NULL, 0, 'Diego Alejandro', 'González Ruiz', '2015-09-30', 'masculino', 'diegoalejandro@gmail.com', 'Parroquia Caricuao Ud1', '04121234575', 105, 0, 'activo', '2025-01-24 11:47:17', '2025-01-24 11:47:17', 'ninguna', ''),
(75, 'V', '31982330', NULL, 0, 'Fernando José', 'Pérez Martínez', '2018-01-10', 'masculino', 'fernandoj@gmail.com', 'Parroquia Caricuao Ud1', '04121234581', 114, 0, 'activo', '2025-01-24 11:54:21', '2025-01-24 11:54:21', 'ninguna', ''),
(76, 'V', '31982331', NULL, 0, 'Lucía Fernanda', 'González Torres', '2017-02-15', 'femenino', 'luciafernanda@gmail.com', 'Parroquia Caricuao Ud1', '04121234582', 114, 0, 'activo', '2025-01-24 11:54:21', '2025-01-24 11:54:21', 'ninguna', ''),
(77, 'V', '31982332', NULL, 0, 'Javier Alejandro', 'Martínez López', '2016-03-20', 'masculino', 'javieralejandro@gmail.com', 'Parroquia Caricuao Ud1', '04121234583', 114, 0, 'activo', '2025-01-24 11:54:21', '2025-01-24 11:54:21', 'ninguna', ''),
(78, 'V', '', 'V21911985583', 2, 'Sofía Valentina', 'Ramírez Pérez', '2019-04-25', 'femenino', 'sofiavalentina@gmail.com', 'Parroquia Caricuao Ud1', '04121234584', 114, 0, 'activo', '2025-01-24 11:54:21', '2025-01-24 11:54:38', 'ninguna', ''),
(79, 'V', '', 'V11511985583', 1, 'Diego Armando', 'Hernández Ruiz', '2015-05-30', 'masculino', 'diegoarmando@gmail.com', 'Parroquia Caricuao Ud1', '04121234585', 114, 0, 'activo', '2025-01-24 11:54:21', '2025-01-24 11:55:23', 'ninguna', ''),
(80, 'V', '30652798', NULL, 0, 'Daniela Alejandra', 'Gimenez Delgado', '2015-03-18', 'femenino', 'danielag2009@gmail.com', 'Caracas', '04164564199', 1, 0, 'activo', '2025-08-25 19:44:55', '2025-08-25 19:44:55', 'ninguna', ''),
(81, 'V', '', 'V42014023560', 4, 'Anderson Andres', 'Lopez Delgado', '2020-01-01', 'masculino', 'migueljoselopez@gmail.com', 'Caracas', '04164564199', 1, 0, 'activo', '2025-08-25 19:50:34', '2025-08-25 19:50:34', 'ninguna', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gestiones`
--

DROP TABLE IF EXISTS `gestiones`;
CREATE TABLE `gestiones` (
  `id_gestion` int(11) NOT NULL,
  `desde` date NOT NULL,
  `hasta` date NOT NULL,
  `fyh_creacion` date DEFAULT NULL,
  `fyh_actualizacion` date DEFAULT NULL,
  `estado` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `gestiones`
--

INSERT INTO `gestiones` (`id_gestion`, `desde`, `hasta`, `fyh_creacion`, `fyh_actualizacion`, `estado`) VALUES
(1, '2026-07-08', '2027-06-15', '2023-12-28', '2025-10-21', '0'),
(33, '2025-10-14', '2026-10-05', '2025-10-16', '2025-10-21', '0'),
(34, '2025-10-31', '2026-09-14', '2025-10-31', NULL, '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grados`
--

DROP TABLE IF EXISTS `grados`;
CREATE TABLE `grados` (
  `id_grado` int(11) NOT NULL,
  `nivel` varchar(20) NOT NULL,
  `grado` varchar(20) NOT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT 1,
  `fyh_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `trayecto` varchar(20) NOT NULL,
  `trimestre` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `grados`
--

INSERT INTO `grados` (`id_grado`, `nivel`, `grado`, `estado`, `fyh_creacion`, `trayecto`, `trimestre`) VALUES
(50, 'Secundaria', 'PRIMER AÑO', 1, '2025-10-31 17:24:09', '', ''),
(51, 'Secundaria', 'SEGUNDO AÑO', 1, '2025-10-31 17:24:37', '', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grados_materias`
--

DROP TABLE IF EXISTS `grados_materias`;
CREATE TABLE `grados_materias` (
  `id` int(11) NOT NULL,
  `id_grado` int(11) NOT NULL,
  `id_materia` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_cambios_notas`
--

DROP TABLE IF EXISTS `historial_cambios_notas`;
CREATE TABLE `historial_cambios_notas` (
  `id_historial` int(11) NOT NULL,
  `id_nota` int(11) DEFAULT NULL,
  `id_estudiante` int(11) DEFAULT NULL,
  `id_materia` int(11) DEFAULT NULL,
  `id_lapso` int(11) DEFAULT NULL,
  `nota_anterior` decimal(4,2) DEFAULT NULL,
  `nota_nueva` decimal(4,2) DEFAULT NULL,
  `id_profesor` int(11) DEFAULT NULL,
  `fecha_cambio` timestamp NOT NULL DEFAULT current_timestamp(),
  `motivo_cambio` text DEFAULT NULL,
  `ip_cambio` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_notas`
--

DROP TABLE IF EXISTS `historial_notas`;
CREATE TABLE `historial_notas` (
  `id_historial` bigint(20) UNSIGNED NOT NULL,
  `id_nota` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `id_materia` int(11) NOT NULL,
  `id_lapso` int(11) NOT NULL,
  `calificacion_anterior` decimal(5,2) DEFAULT NULL,
  `calificacion_nueva` decimal(5,2) NOT NULL,
  `observaciones_anterior` text DEFAULT NULL,
  `observaciones_nueva` text DEFAULT NULL,
  `fecha_cambio` timestamp NOT NULL DEFAULT current_timestamp(),
  `usuario_cambio` varchar(255) NOT NULL,
  `tipo_cambio` varchar(50) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_notas`
--

INSERT INTO `historial_notas` (`id_historial`, `id_nota`, `id_estudiante`, `id_materia`, `id_lapso`, `calificacion_anterior`, `calificacion_nueva`, `observaciones_anterior`, `observaciones_nueva`, `fecha_cambio`, `usuario_cambio`, `tipo_cambio`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(86, 54, 10, 4, 4, 10.00, 20.00, 'Error de tipificacion', 'Error de tipificacion', '2025-10-21 19:43:25', 'Heldyn David Diaz Daboin', 'ACTUALIZACION', 1, '2025-10-21 19:43:25', '2025-10-21 19:43:25'),
(87, 57, 19, 5, 4, NULL, 20.00, NULL, '', '2025-10-22 00:13:29', 'Heldyn David Diaz Daboin', 'CREACION', 1, '2025-10-22 00:13:29', '2025-10-22 00:13:29'),
(88, 58, 20, 5, 4, NULL, 20.00, NULL, '', '2025-10-22 00:13:29', 'Heldyn David Diaz Daboin', 'CREACION', 1, '2025-10-22 00:13:29', '2025-10-22 00:13:29'),
(89, 52, 19, 3, 4, 14.00, 18.00, 'error', '', '2025-10-22 00:15:45', 'Heldyn David Diaz Daboin', 'ACTUALIZACION', 1, '2025-10-22 00:15:45', '2025-10-22 00:15:45'),
(90, 52, 19, 3, 4, 18.00, 20.00, '', 'Error de tipificacion', '2025-10-22 00:17:54', 'Heldyn David Diaz Daboin', 'ACTUALIZACION', 1, '2025-10-22 00:17:54', '2025-10-22 00:17:54'),
(91, 59, 3, 3, 6, NULL, 20.00, NULL, '', '2025-11-01 03:49:11', 'Heldyn David Diaz Daboin', 'CREACION', 1, '2025-11-01 03:49:11', '2025-11-01 03:49:11'),
(92, 59, 3, 3, 6, 20.00, 0.00, '', 'errorrr', '2025-11-01 03:50:30', 'Heldyn David Diaz Daboin', 'ACTUALIZACION', 1, '2025-11-01 03:50:30', '2025-11-01 03:50:30'),
(93, 59, 3, 3, 6, 0.00, 20.00, 'errorrr', 'error', '2025-11-01 04:07:00', 'Heldyn David Diaz Daboin', 'ACTUALIZACION', 1, '2025-11-01 04:07:00', '2025-11-01 04:07:00'),
(94, 60, 3, 3, 7, NULL, 15.00, NULL, '', '2025-11-01 04:07:13', 'Heldyn David Diaz Daboin', 'CREACION', 1, '2025-11-01 04:07:13', '2025-11-01 04:07:13'),
(95, 61, 3, 3, 8, NULL, 18.00, NULL, '', '2025-11-01 04:07:23', 'Heldyn David Diaz Daboin', 'CREACION', 1, '2025-11-01 04:07:23', '2025-11-01 04:07:23'),
(96, 62, 3, 4, 8, NULL, 15.00, NULL, '', '2025-11-01 04:07:33', 'Heldyn David Diaz Daboin', 'CREACION', 1, '2025-11-01 04:07:33', '2025-11-01 04:07:33'),
(97, 63, 3, 4, 7, NULL, 19.00, NULL, '', '2025-11-01 04:07:46', 'Heldyn David Diaz Daboin', 'CREACION', 1, '2025-11-01 04:07:46', '2025-11-01 04:07:46'),
(98, 64, 3, 4, 6, NULL, 18.00, NULL, '', '2025-11-01 04:07:57', 'Heldyn David Diaz Daboin', 'CREACION', 1, '2025-11-01 04:07:57', '2025-11-01 04:07:57'),
(99, 65, 3, 1, 6, NULL, 16.00, NULL, '', '2025-11-01 04:08:09', 'Heldyn David Diaz Daboin', 'CREACION', 1, '2025-11-01 04:08:09', '2025-11-01 04:08:09'),
(100, 66, 3, 1, 7, NULL, 20.00, NULL, '', '2025-11-01 04:08:18', 'Heldyn David Diaz Daboin', 'CREACION', 1, '2025-11-01 04:08:18', '2025-11-01 04:08:18'),
(101, 67, 3, 1, 8, NULL, 2.00, NULL, '', '2025-11-01 04:08:26', 'Heldyn David Diaz Daboin', 'CREACION', 1, '2025-11-01 04:08:26', '2025-11-01 04:08:26'),
(102, 68, 10, 3, 6, NULL, 15.00, NULL, '', '2025-11-01 05:52:42', 'Saned Arya Diaz Daboin', 'CREACION', 1, '2025-11-01 05:52:42', '2025-11-01 05:52:42'),
(103, 69, 80, 3, 6, NULL, 20.00, NULL, '', '2025-11-03 00:12:05', 'Heldyn David Diaz Daboin', 'CREACION', 1, '2025-11-03 00:12:05', '2025-11-03 00:12:05'),
(104, 59, 3, 3, 6, 20.00, 20.00, 'error', '', '2025-11-03 00:12:05', 'Heldyn David Diaz Daboin', 'ACTUALIZACION', 1, '2025-11-03 00:12:05', '2025-11-03 00:12:05'),
(105, 70, 80, 4, 6, NULL, 20.00, NULL, '', '2025-11-03 00:12:41', 'Heldyn David Diaz Daboin', 'CREACION', 1, '2025-11-03 00:12:41', '2025-11-03 00:12:41'),
(106, 71, 80, 1, 7, NULL, 20.00, NULL, '', '2025-11-03 00:14:36', 'Heldyn David Diaz Daboin', 'CREACION', 1, '2025-11-03 00:14:36', '2025-11-03 00:14:36'),
(107, 72, 81, 3, 6, NULL, 15.00, NULL, '', '2025-11-03 15:16:03', 'Heldyn David Diaz Daboin', 'CREACION', 1, '2025-11-03 15:16:03', '2025-11-03 15:16:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

DROP TABLE IF EXISTS `horarios`;
CREATE TABLE `horarios` (
  `id_horario` int(11) NOT NULL,
  `id_gestion` int(11) NOT NULL,
  `id_grado` int(11) NOT NULL,
  `id_seccion` int(11) NOT NULL,
  `aula` varchar(20) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'BORRADOR',
  `aprobado_por` int(11) DEFAULT NULL,
  `aprobado_en` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `horarios`
--

INSERT INTO `horarios` (`id_horario`, `id_gestion`, `id_grado`, `id_seccion`, `aula`, `fecha_inicio`, `fecha_fin`, `estado`, `aprobado_por`, `aprobado_en`) VALUES
(1, 34, 50, 98, '', '2025-11-04', '2025-10-29', 'PUBLICADO', 1, '2025-11-03 15:48:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horario_detalle`
--

DROP TABLE IF EXISTS `horario_detalle`;
CREATE TABLE `horario_detalle` (
  `id_detalle` int(11) NOT NULL,
  `id_horario` int(11) NOT NULL,
  `dia_semana` varchar(10) NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `id_materia` int(11) NOT NULL,
  `id_profesor` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `horario_detalle`
--

INSERT INTO `horario_detalle` (`id_detalle`, `id_horario`, `dia_semana`, `hora_inicio`, `hora_fin`, `id_materia`, `id_profesor`) VALUES
(3, 2, 'Lunes', '07:50:00', '08:30:00', 3, 1),
(5, 3, 'Lunes', '07:50:00', '08:30:00', 3, 1),
(7, 4, 'Lunes', '07:50:00', '08:30:00', 3, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripciones`
--

DROP TABLE IF EXISTS `inscripciones`;
CREATE TABLE `inscripciones` (
  `id` int(11) NOT NULL,
  `id_gestion` varchar(50) NOT NULL,
  `nivel_id` varchar(50) NOT NULL,
  `grado` varchar(50) NOT NULL,
  `nombre_seccion` varchar(50) NOT NULL,
  `turno_id` varchar(50) NOT NULL,
  `talla_camisa` varchar(10) DEFAULT NULL,
  `talla_pantalon` varchar(10) DEFAULT NULL,
  `talla_zapatos` varchar(10) DEFAULT NULL,
  `id_estudiante` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `estado` varchar(255) DEFAULT NULL,
  `id_seccion` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inscripciones`
--

INSERT INTO `inscripciones` (`id`, `id_gestion`, `nivel_id`, `grado`, `nombre_seccion`, `turno_id`, `talla_camisa`, `talla_pantalon`, `talla_zapatos`, `id_estudiante`, `created_at`, `updated_at`, `estado`, `id_seccion`) VALUES
(238, '34', 'Secundaria', '51', 'B', 'M', 'S', '14', '26', 3, '2025-10-31 21:29:50', '2025-10-31 21:29:50', 'activo', 100),
(239, '34', 'Secundaria', '50', 'A', 'M', 'S', 'S', '25', 10, '2025-10-31 23:52:16', '2025-10-31 23:52:16', 'activo', 98),
(240, '34', 'Secundaria', '51', 'B', 'M', 'S', 'S', '25', 7, '2025-11-01 04:48:11', '2025-11-01 04:48:11', 'activo', 100),
(241, '34', 'Secundaria', '51', 'B', 'M', 'S', '14', '30', 80, '2025-11-01 04:48:33', '2025-11-01 04:48:33', 'activo', 100),
(242, '34', 'Secundaria', '51', 'B', 'M', 'S', '14', '30', 81, '2025-11-01 04:48:52', '2025-11-01 04:48:52', 'activo', 100);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lapsos`
--

DROP TABLE IF EXISTS `lapsos`;
CREATE TABLE `lapsos` (
  `id_lapso` int(11) NOT NULL,
  `nombre_lapso` varchar(50) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `id_gestion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `lapsos`
--

INSERT INTO `lapsos` (`id_lapso`, `nombre_lapso`, `fecha_inicio`, `fecha_fin`, `id_gestion`) VALUES
(1, 'Primer lapso', '2024-10-01', '2025-02-07', 1),
(2, 'Segundo lapso', '2025-02-17', '2025-04-25', 1),
(3, 'Tercer lapso', '2025-05-05', '2025-07-25', 1),
(4, 'Primer lapso', '2025-10-20', '2025-10-21', 33),
(5, 'Segundo Lapso', '2025-10-14', '2025-10-14', 33),
(6, 'Primer lapso', '2025-10-22', '2025-11-01', 34),
(7, 'Segundo Lapso', '2026-01-15', '2026-02-19', 34),
(8, 'Tercer Lapso', '2028-09-21', '2028-10-26', 34);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias`
--

DROP TABLE IF EXISTS `materias`;
CREATE TABLE `materias` (
  `id_materia` int(11) NOT NULL,
  `nombre_materia` varchar(100) NOT NULL,
  `id_grado` int(11) NOT NULL,
  `nivel_educativo` enum('Preescolar','Primaria','Secundaria') NOT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `codigo` varchar(20) NOT NULL,
  `abreviatura` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `materias`
--

INSERT INTO `materias` (`id_materia`, `nombre_materia`, `id_grado`, `nivel_educativo`, `estado`, `codigo`, `abreviatura`) VALUES
(1, 'Matemáticas', 19, 'Preescolar', 1, '', ''),
(3, 'Educación Física', 20, 'Preescolar', 1, '', ''),
(4, 'Ingles', 27, 'Preescolar', 1, '', ''),
(5, 'Física', 21, 'Preescolar', 1, '', ''),
(6, 'Lenguaje y Comunicación', 21, 'Preescolar', 1, '', ''),
(7, 'Química', 22, 'Preescolar', 1, '', ''),
(8, 'Orientación y convivencia', 23, 'Preescolar', 1, '', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `niveles`
--

DROP TABLE IF EXISTS `niveles`;
CREATE TABLE `niveles` (
  `id_nivel` int(11) NOT NULL,
  `gestion_id` int(11) NOT NULL,
  `nivel` varchar(255) NOT NULL,
  `fyh_creacion` datetime DEFAULT NULL,
  `fyh_actualizacion` datetime DEFAULT NULL,
  `estado` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `niveles`
--

INSERT INTO `niveles` (`id_nivel`, `gestion_id`, `nivel`, `fyh_creacion`, `fyh_actualizacion`, `estado`) VALUES
(3, 1, 'PRIMARIA', '2024-10-27 00:00:00', '2024-10-27 00:00:00', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas_estudiantes`
--

DROP TABLE IF EXISTS `notas_estudiantes`;
CREATE TABLE `notas_estudiantes` (
  `id_nota` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `id_materia` int(11) NOT NULL,
  `id_lapso` int(11) NOT NULL,
  `calificacion` decimal(4,2) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notas_estudiantes`
--

INSERT INTO `notas_estudiantes` (`id_nota`, `id_estudiante`, `id_materia`, `id_lapso`, `calificacion`, `observaciones`, `fecha_registro`) VALUES
(1, 1, 1, 1, 19.00, NULL, '2025-04-15 02:20:07'),
(2, 10, 1, 1, 14.00, NULL, '2025-04-15 03:40:00'),
(3, 1, 3, 1, 5.00, NULL, '2025-05-11 18:11:48'),
(4, 1, 1, 2, 12.00, NULL, '2025-05-11 18:12:36'),
(5, 1, 3, 3, 20.00, NULL, '2025-05-11 18:12:43'),
(6, 1, 5, 1, 10.00, NULL, '2025-05-11 18:18:06'),
(7, 1, 4, 1, 1.00, NULL, '2025-05-11 18:18:07'),
(8, 1, 6, 1, 12.00, NULL, '2025-05-11 18:18:07'),
(9, 1, 8, 1, 11.00, NULL, '2025-05-11 18:18:07'),
(10, 1, 7, 1, 19.00, NULL, '2025-05-11 18:18:07'),
(11, 1, 3, 2, 10.00, NULL, '2025-05-11 18:19:42'),
(12, 1, 4, 2, 20.00, NULL, '2025-05-11 18:19:42'),
(13, 1, 5, 2, 13.00, NULL, '2025-05-11 18:19:42'),
(14, 1, 6, 2, 17.00, NULL, '2025-05-11 18:19:43'),
(15, 1, 7, 2, 18.00, NULL, '2025-05-11 18:19:43'),
(16, 1, 8, 2, 19.00, NULL, '2025-05-11 18:19:43'),
(17, 1, 1, 3, 10.00, NULL, '2025-05-11 18:21:28'),
(18, 1, 4, 3, 18.00, NULL, '2025-05-11 18:21:28'),
(19, 1, 5, 3, 20.00, NULL, '2025-05-11 18:21:28'),
(20, 1, 6, 3, 15.00, NULL, '2025-05-11 18:21:28'),
(21, 1, 7, 3, 16.00, NULL, '2025-05-11 18:21:28'),
(22, 1, 8, 3, 14.00, NULL, '2025-05-11 18:21:28'),
(23, 80, 1, 1, 10.00, NULL, '2025-08-25 16:25:37'),
(24, 80, 3, 2, 15.00, NULL, '2025-08-25 16:25:54'),
(25, 80, 5, 3, 15.00, NULL, '2025-08-25 16:26:03'),
(26, 81, 1, 1, 10.00, NULL, '2025-08-25 16:27:15'),
(27, 81, 3, 1, 20.00, NULL, '2025-08-25 16:27:15'),
(28, 81, 6, 1, 15.00, NULL, '2025-08-25 16:27:15'),
(29, 81, 1, 2, 12.00, NULL, '2025-08-25 16:27:35'),
(30, 81, 3, 2, 15.00, NULL, '2025-08-25 16:27:35'),
(31, 81, 6, 2, 5.00, NULL, '2025-08-25 16:27:35'),
(32, 81, 1, 3, 12.00, NULL, '2025-08-25 16:28:04'),
(33, 81, 3, 3, 15.00, NULL, '2025-08-25 16:28:04'),
(34, 81, 6, 3, 10.00, NULL, '2025-08-25 16:28:04'),
(35, 32, 3, 4, 20.00, NULL, '2025-10-20 18:21:48'),
(36, 31, 3, 4, 20.00, NULL, '2025-10-20 18:21:48'),
(37, 32, 6, 4, 11.00, NULL, '2025-10-20 18:21:43'),
(38, 31, 6, 4, 15.00, NULL, '2025-10-20 18:21:43'),
(39, 19, 4, 4, 20.00, NULL, '2025-10-20 18:32:28'),
(40, 20, 4, 4, 20.00, NULL, '2025-10-20 18:32:28'),
(41, 21, 3, 4, 20.00, NULL, '2025-10-20 18:32:57'),
(42, 22, 3, 4, 20.00, NULL, '2025-10-20 18:32:57'),
(43, 21, 5, 4, 10.00, '', '2025-10-20 22:44:59'),
(44, 22, 5, 4, 13.00, '', '2025-10-20 22:45:24'),
(45, 21, 4, 4, 20.00, NULL, '2025-10-20 18:33:29'),
(46, 22, 4, 4, 13.00, NULL, '2025-10-20 22:45:12'),
(47, 32, 5, 4, 20.00, NULL, '2025-10-20 18:34:55'),
(48, 31, 5, 4, 20.00, NULL, '2025-10-20 18:34:55'),
(49, 21, 6, 4, 20.00, NULL, '2025-10-20 19:31:00'),
(50, 10, 6, 4, 20.00, NULL, '2025-10-20 20:28:28'),
(51, 9, 6, 4, 10.00, NULL, '2025-10-20 20:28:28'),
(52, 19, 3, 4, 20.00, 'Error de tipificacion', '2025-10-21 14:54:34'),
(53, 20, 3, 4, 20.00, NULL, '2025-10-20 20:45:54'),
(54, 10, 4, 4, 20.00, 'Error de tipificacion', '2025-10-21 12:39:58'),
(55, 9, 4, 4, 20.00, '', '2025-10-21 01:06:33'),
(56, 22, 6, 4, 15.00, '', '2025-10-20 22:44:33'),
(57, 19, 5, 4, 20.00, '', '2025-10-21 20:13:29'),
(58, 20, 5, 4, 20.00, '', '2025-10-21 20:13:29'),
(59, 3, 3, 6, 20.00, '', '2025-10-31 23:49:11'),
(60, 3, 3, 7, 15.00, '', '2025-11-01 00:07:13'),
(61, 3, 3, 8, 18.00, '', '2025-11-01 00:07:23'),
(62, 3, 4, 8, 15.00, '', '2025-11-01 00:07:33'),
(63, 3, 4, 7, 19.00, '', '2025-11-01 00:07:46'),
(64, 3, 4, 6, 18.00, '', '2025-11-01 00:07:57'),
(65, 3, 1, 6, 16.00, '', '2025-11-01 00:08:09'),
(66, 3, 1, 7, 20.00, '', '2025-11-01 00:08:18'),
(67, 3, 1, 8, 2.00, '', '2025-11-01 00:08:26'),
(68, 10, 3, 6, 15.00, '', '2025-11-01 01:52:42'),
(69, 80, 3, 6, 20.00, '', '2025-11-02 20:12:05'),
(70, 80, 4, 6, 20.00, '', '2025-11-02 20:12:41'),
(71, 80, 1, 7, 20.00, '', '2025-11-02 20:14:36'),
(72, 81, 3, 6, 15.00, '', '2025-11-03 11:16:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `periodos_anuales`
--

DROP TABLE IF EXISTS `periodos_anuales`;
CREATE TABLE `periodos_anuales` (
  `id` int(11) NOT NULL,
  `año` int(4) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `periodo_actual` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `periodos_anuales`
--

INSERT INTO `periodos_anuales` (`id`, `año`, `fecha_inicio`, `fecha_fin`, `descripcion`, `activo`, `periodo_actual`, `fecha_creacion`) VALUES
(1, 2025, '2025-01-01', '2025-12-31', '', 1, 1, '2025-08-29 03:45:41'),
(3, 2026, '2026-01-01', '2026-12-31', '', 0, 0, '2025-08-29 17:50:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

DROP TABLE IF EXISTS `permisos`;
CREATE TABLE `permisos` (
  `id_permiso` int(11) NOT NULL,
  `nombre_url` varchar(100) NOT NULL,
  `url` text NOT NULL,
  `fyh_creacion` datetime DEFAULT NULL,
  `fyh_actualizacion` datetime DEFAULT NULL,
  `estado` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`id_permiso`, `nombre_url`, `url`, `fyh_creacion`, `fyh_actualizacion`, `estado`) VALUES
(1, 'Configuraciones', 'admin/configuraciones/institucion/', '2024-10-26 18:50:54', NULL, '1'),
(2, 'Periodo academico', 'admin/configuraciones/gestion/', '2024-10-26 18:51:45', NULL, '1'),
(3, 'Panel administrador', 'admin/', '2024-10-26 18:52:18', NULL, '1'),
(4, 'Inscripción', 'admin/inscripciones/', '2024-10-26 18:52:56', '2024-10-26 18:53:37', '1'),
(5, 'Lista de estudiante', 'admin/estudiantes/', '2024-10-26 18:54:02', NULL, '1'),
(6, 'Lista de turnos', 'admin/niveles/', '2024-10-26 18:55:34', NULL, '1'),
(7, 'Grados', 'admin/grados/', '2024-10-26 18:55:56', NULL, '1'),
(8, 'Secciones', 'http://localhost/Daniel/SIGE/admin/seccion/', '2024-10-26 18:56:15', NULL, '1'),
(9, 'Roles', 'admin/roles/', '2024-10-26 18:56:35', NULL, '1'),
(10, 'Permisos del sistema', 'admin/roles/permisos.php', '2024-10-26 18:57:11', NULL, '1'),
(11, 'Registro de usuarios', 'admin/usuarios/', '2024-10-26 18:57:58', NULL, '1'),
(12, 'Personal administrativo', 'admin/administrativos/', '2024-10-26 18:58:23', NULL, '1'),
(13, 'Personal docente', 'admin/docentes/', '2024-10-26 18:58:47', NULL, '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personas`
--

DROP TABLE IF EXISTS `personas`;
CREATE TABLE `personas` (
  `id_persona` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nombres` varchar(50) NOT NULL,
  `apellidos` varchar(50) NOT NULL,
  `ci` varchar(20) NOT NULL,
  `fecha_nacimiento` varchar(20) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `celular` varchar(20) NOT NULL,
  `fyh_creacion` date DEFAULT NULL,
  `fyh_actualizacion` date DEFAULT NULL,
  `estado` varchar(11) DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `personas`
--

INSERT INTO `personas` (`id_persona`, `usuario_id`, `nombres`, `apellidos`, `ci`, `fecha_nacimiento`, `direccion`, `celular`, `fyh_creacion`, `fyh_actualizacion`, `estado`, `foto_perfil`) VALUES
(28, 79, 'Keila ', 'Naveda', '27985583', '2025-10-30', 'Parroquia Caricuao Ud1', '04124331080', '2025-10-31', '2025-11-03', '1', NULL),
(29, 80, 'Heldyn David', 'Diaz Daboin', '15888555', '2025-10-23', 'Parroquia Caricuao Ud1', '04124331080', '2025-10-31', '2025-11-03', '1', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plantillas_carnet`
--

DROP TABLE IF EXISTS `plantillas_carnet`;
CREATE TABLE `plantillas_carnet` (
  `id_plantilla` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `archivo_plantilla` varchar(255) DEFAULT NULL,
  `ancho` int(11) DEFAULT 85,
  `alto` int(11) DEFAULT 54,
  `margen_superior` int(11) DEFAULT 5,
  `margen_inferior` int(11) DEFAULT 5,
  `margen_izquierdo` int(11) DEFAULT 5,
  `margen_derecho` int(11) DEFAULT 5,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `estatus` enum('activo','inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas_seguridad`
--

DROP TABLE IF EXISTS `preguntas_seguridad`;
CREATE TABLE `preguntas_seguridad` (
  `id_pregunta` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `pregunta1` varchar(255) NOT NULL,
  `respuesta1` varchar(255) NOT NULL,
  `pregunta2` varchar(255) NOT NULL,
  `respuesta2` varchar(255) NOT NULL,
  `fyh_creacion` datetime DEFAULT current_timestamp(),
  `fyh_actualizacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `features` text DEFAULT NULL,
  `applications` text DEFAULT NULL,
  `specifications` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `features`, `applications`, `specifications`, `image`, `status`, `featured`, `created_at`) VALUES
(1, 1, 'Tubería PVC Agua Fría 1/2\"', 'Tubería de PVC para agua fría de 1/2 pulgada', 'Resistente a la corrosión, fácil instalación, bajo peso', 'Sistemas de agua potable, riego, instalaciones residenciales', 'Diámetro: 1/2\", Presión máxima: 150 PSI, Longitud: 6m', NULL, 1, 0, '2025-08-31 05:14:46'),
(2, 1, 'Tubería PVC Agua Fría 3/4\"', 'Tubería de PVC para agua fría de 3/4 pulgada', 'Resistente a la corrosión, fácil instalación, bajo peso', 'Sistemas de agua potable, riego, instalaciones residenciales', 'Diámetro: 3/4\", Presión máxima: 150 PSI, Longitud: 6m', NULL, 1, 0, '2025-08-31 05:14:46'),
(3, 2, 'Tubería CPVC Agua Caliente 1/2\"', 'Tubería de CPVC para agua caliente de 1/2 pulgada', 'Resistente a altas temperaturas, no se corroe', 'Sistemas de agua caliente, instalaciones industriales', 'Diámetro: 1/2\", Temperatura máxima: 90°C, Longitud: 6m', NULL, 1, 0, '2025-08-31 05:14:46'),
(4, 4, 'Tubería Conduit 1\"', 'Tubería para protección de cables eléctricos de 1 pulgada', 'Protección contra impactos, aislante eléctrico', 'Instalaciones eléctricas residenciales e industriales', 'Diámetro: 1\", Resistencia: Alta, Longitud: 3m', NULL, 1, 0, '2025-08-31 05:14:46'),
(5, 5, 'Tubería Sanitaria 4\"', 'Tubería para sistemas sanitarios de 4 pulgadas', 'Alta resistencia, superficie lisa, fácil instalación', 'Sistemas de drenaje, alcantarillado sanitario', 'Diámetro: 4\", Longitud: 6m, Clase: A', NULL, 1, 0, '2025-08-31 05:14:46'),
(6, 7, 'Tubería Alcantarillado 6\"', 'Tubería para sistemas de alcantarillado de 6 pulgadas', 'Alta resistencia a cargas, durabilidad', 'Sistemas de alcantarillado municipal, drenaje pluvial', 'Diámetro: 6\", Longitud: 6m, Clase: B', NULL, 1, 0, '2025-08-31 05:14:46'),
(7, 9, 'Ducto Eléctrico 2\"', 'Ducto para instalaciones eléctricas de 2 pulgadas', 'Protección mecánica, resistencia al impacto', 'Instalaciones eléctricas en edificaciones', 'Diámetro: 2\", Longitud: 3m, Color: Gris', NULL, 1, 0, '2025-08-31 05:14:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `product_categories`
--

DROP TABLE IF EXISTS `product_categories`;
CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `product_categories`
--

INSERT INTO `product_categories` (`id`, `name`, `description`, `icon`, `status`, `created_at`) VALUES
(1, 'Tubería de Presión Agua Fría', 'Tuberías de PVC para sistemas de agua fría a presión', 'fas fa-faucet', 1, '2025-08-31 05:14:46'),
(2, 'Tubería de Presión Agua Caliente (CPVC)', 'Tuberías de CPVC para sistemas de agua caliente', 'fas fa-fire', 1, '2025-08-31 05:14:46'),
(3, 'Tubería de Polipropileno', 'Tuberías de polipropileno para diversos usos', 'fas fa-pipe', 1, '2025-08-31 05:14:46'),
(4, 'Tubería Conduit', 'Tuberías para conducción y protección de cables eléctricos', 'fas fa-bolt', 1, '2025-08-31 05:14:46'),
(5, 'Tubería Sanitaria Clase A y B', 'Tuberías para sistemas sanitarios y de drenaje', 'fas fa-shower', 1, '2025-08-31 05:14:46'),
(6, 'Tubería de Soldadura', 'Tuberías especiales para sistemas de soldadura', 'fas fa-tools', 1, '2025-08-31 05:14:46'),
(7, 'Tubería de Alcantarillado', 'Tuberías para sistemas de alcantarillado', 'fas fa-water', 1, '2025-08-31 05:14:46'),
(8, 'Tubería de Acueducto', 'Tuberías para sistemas de acueducto', 'fas fa-tint', 1, '2025-08-31 05:14:46'),
(9, 'Ducto Eléctrico y Telefónico', 'Ductos para instalaciones eléctricas y telefónicas', 'fas fa-phone', 1, '2025-08-31 05:14:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesores`
--

DROP TABLE IF EXISTS `profesores`;
CREATE TABLE `profesores` (
  `id_profesor` int(11) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `especialidad` varchar(100) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `usuario` int(11) DEFAULT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `profesores`
--

INSERT INTO `profesores` (`id_profesor`, `cedula`, `nombres`, `apellidos`, `email`, `telefono`, `especialidad`, `estado`, `fecha_creacion`, `fecha_actualizacion`, `usuario`, `password`) VALUES
(1, '1234567890', 'Juan', 'Pérez', 'juan.perez@example.com', '0987654321', 'Matemáticas', 1, '2025-05-13 14:15:38', NULL, 1, 'e10adc3949ba59abbe56e057f20f883e'),
(3, '27985583', 'Heldyn David', 'Diaz Daboin', 'heldyndiaz19@gmail.com', '04121988817', 'Educacion Fisica', 1, '2025-10-20 16:02:12', '2025-11-03 18:51:12', 0, '$2y$10$wZJBiqDScc2RgPHBpl/N9.wd6yPxRIXKkbu8/GVDZsLoaXnjAwyhS'),
(4, '27985584', 'Saned Arya', 'Diaz Daboin', 'docente@gmail.com', '02124331080', 'CIENCIAS SOCIALES', 1, '2025-10-21 20:23:56', NULL, 0, '$2y$10$zc9wchmp4M0syuycKEBuKewwPcb8hLdknyo9W/O98Gm.X0SIFCFWq');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesor_seccion_materia`
--

DROP TABLE IF EXISTS `profesor_seccion_materia`;
CREATE TABLE `profesor_seccion_materia` (
  `id_relacion` int(11) NOT NULL,
  `id_profesor` int(11) DEFAULT NULL,
  `id_seccion` int(11) DEFAULT NULL,
  `id_materia` int(11) DEFAULT NULL,
  `id_gestion` int(11) DEFAULT NULL,
  `estado` tinyint(4) DEFAULT 1,
  `fecha_asignacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `project_list`
--

DROP TABLE IF EXISTS `project_list`;
CREATE TABLE `project_list` (
  `id` int(30) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `status` tinyint(2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `manager_id` int(30) NOT NULL,
  `user_ids` text NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `project_list`
--

INSERT INTO `project_list` (`id`, `name`, `description`, `status`, `start_date`, `end_date`, `manager_id`, `user_ids`, `date_created`) VALUES
(1, 'Sample Project', '																				&lt;span style=&quot;color: rgb(0, 0, 0); font-family: &amp;quot;Open Sans&amp;quot;, Arial, sans-serif; font-size: 14px; text-align: justify;&quot;&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. In elementum, metus vitae malesuada mollis, urna nisi luctus ligula, vitae volutpat massa eros eu ligula. Nunc dui metus, iaculis id dolor non, luctus tristique libero. Aenean et sagittis sem. Nulla facilisi. Mauris at placerat augue. Nullam porttitor felis turpis, ac varius eros placerat et. Nunc ut enim scelerisque, porta lacus vitae, viverra justo. Nam mollis turpis nec dolor feugiat, sed bibendum velit placerat. Etiam in hendrerit leo. Nullam mollis lorem massa, sit amet tincidunt dolor lacinia at.&lt;/span&gt;																	', 0, '2020-11-03', '2021-01-20', 2, '3,4,5', '2020-12-03 09:56:56'),
(2, 'Sample Project 102', 'Sample Only', 0, '2020-12-02', '2020-12-31', 2, '3', '2020-12-03 13:51:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

DROP TABLE IF EXISTS `reportes`;
CREATE TABLE `reportes` (
  `id_reporte` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `periodo_academico` varchar(50) NOT NULL,
  `nivel_id` varchar(50) NOT NULL,
  `grado` varchar(50) NOT NULL,
  `nombre_seccion` varchar(50) NOT NULL,
  `turno_id` varchar(50) NOT NULL,
  `talla_camisa` varchar(50) NOT NULL,
  `talla_pantalon` varchar(50) NOT NULL,
  `talla_zapatos` varchar(50) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `representantes`
--

DROP TABLE IF EXISTS `representantes`;
CREATE TABLE `representantes` (
  `id_representante` int(11) NOT NULL,
  `tipo_cedula` enum('V','E') DEFAULT NULL,
  `cedula` int(8) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `estado_civil` enum('Soltero','Casado','Viudo') NOT NULL,
  `afinidad` enum('mama','papa','abuelo','tio') NOT NULL,
  `genero` varchar(50) NOT NULL,
  `correo_electronico` varchar(255) NOT NULL,
  `tipo_sangre` enum('A+','A-','AB+','AB-','B+','B-','O+','O-') NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `numeros_telefonicos` varchar(20) NOT NULL,
  `estatus` enum('Activo','Inactivo') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `representantes`
--

INSERT INTO `representantes` (`id_representante`, `tipo_cedula`, `cedula`, `nombres`, `apellidos`, `fecha_nacimiento`, `estado_civil`, `afinidad`, `genero`, `correo_electronico`, `tipo_sangre`, `direccion`, `numeros_telefonicos`, `estatus`, `created_at`) VALUES
(1, 'V', 14023560, 'Carlos Alberto', 'Pérez López', '1980-05-10', 'Casado', '', 'masculino', 'carlosperez@gmail.com', 'O+', 'Caracas', '04121234501', 'Activo', '2025-01-24 11:59:04'),
(2, 'V', 14023561, 'Ana María', 'González Torres', '1985-06-15', 'Soltero', '', 'femenino', 'anamaria@gmail.com', 'A+', 'Caracas', '04121234502', 'Inactivo', '2025-01-24 11:59:04'),
(3, 'V', 13023562, 'Luis Fernando', 'Martínez Ruiz', '1990-07-20', 'Casado', '', 'masculino', 'luisfernando@gmail.com', 'B+', 'Caracas', '04121234503', 'Activo', '2025-01-24 11:59:04'),
(4, 'V', 13023563, 'Sofía Valentina', 'Ramírez Pérez', '1995-08-25', 'Soltero', '', 'femenino', 'sofiaramirez@gmail.com', 'AB+', 'Caracas', '04121234504', 'Activo', '2025-01-24 11:59:04'),
(5, 'V', 10023564, 'Diego Armando', 'Hernández Ruiz', '1988-09-30', 'Casado', '', 'masculino', 'diegohernandez@gmail.com', 'O-', 'Caracas', '04121234505', 'Activo', '2025-01-24 11:59:04'),
(6, 'V', 10023565, 'María José', 'López García', '1992-10-05', 'Soltero', '', 'femenino', 'mariajose@gmail.com', 'B-', 'Caracas', '04121234506', 'Activo', '2025-01-24 11:59:04'),
(7, 'V', 7202356, 'Fernando José', 'Cruz Mierez', '1983-11-10', 'Casado', '', 'masculino', 'fernandoj@gmail.com', 'O+', 'Caracas', '04121234507', 'Activo', '2025-01-24 11:59:04'),
(8, 'V', 7202567, 'Lucía Fernanda', 'Daboin Rodriguez', '1987-12-15', 'Soltero', '', 'femenino', 'luciafernanda@gmail.com', 'A+', 'Caracas', '04121234508', 'Activo', '2025-01-24 11:59:04'),
(9, 'V', 8023568, 'Javier Alejandro', 'Martínez López', '1991-01-20', 'Casado', '', 'masculino', 'javieralejandro@gmail.com', 'B+', 'Caracas', '04121234509', 'Activo', '2025-01-24 11:59:04'),
(10, 'V', 8202569, 'Isabella', 'Ramírez López', '1994-02-25', 'Soltero', '', 'femenino', 'isabellar@gmail.com', 'AB+', 'Caracas', '04121234510', 'Activo', '2025-01-24 11:59:04'),
(11, 'V', 14023570, 'Carlos Eduardo', 'González Torres', '1980-03-10', 'Casado', '', 'masculino', 'carloseduardo@gmail.com', 'O+', 'Caracas', '04121234511', 'Activo', '2025-01-24 11:59:04'),
(12, 'V', 13023571, 'María Fernanda', 'Pérez López', '1985-04-15', 'Soltero', '', 'femenino', 'mariafernanda@gmail.com', 'A+', 'Caracas', '04121234512', 'Activo', '2025-01-24 11:59:04'),
(13, 'V', 10023572, 'Luis Miguel', 'Martínez Ruiz', '1990-05-20', 'Casado', '', 'masculino', 'luismiguel@gmail.com', 'B+', 'Caracas', '04121234513', 'Activo', '2025-01-24 11:59:04'),
(14, 'V', 6203573, 'Sofía Alejandra', 'Hernández Ruiz', '1995-06-25', 'Soltero', '', 'femenino', 'sofiaalejandra@gmail.com', 'AB+', 'Caracas', '04121234514', 'Activo', '2025-01-24 11:59:04'),
(15, 'V', 9202574, 'Diego Alejandro', 'Cruz Mierez', '1988-07-30', 'Casado', '', 'masculino', 'diegoalejandro@gmail.com', 'O-', 'Caracas', '04121234515', 'Activo', '2025-01-24 11:59:04'),
(16, 'V', 14023575, 'María Elena', 'Daboin Rodriguez', '1992-08-05', 'Soltero', '', 'femenino', 'mariaelena@gmail.com', 'B-', 'Caracas', '04121234516', 'Activo', '2025-01-24 11:59:04'),
(17, 'V', 13023576, 'Fernando Andrés', 'Martínez López', '1995-09-10', 'Casado', '', 'masculino', 'fernandoandres@gmail.com', 'O+', 'Caracas', '04121234517', 'Activo', '2025-01-24 11:59:04'),
(105, 'V', 27985583, 'Marcos José', 'Cruz Mierez', '2006-12-20', 'Soltero', 'mama', 'masculino', 'marcos1904@gmail.com', 'O+', 'Caricuao', '04121988817', 'Activo', '2025-01-09 21:23:26'),
(113, 'V', 14756124, 'Marilyn del Carmen', 'Daboin Rodriguez', '2007-01-16', 'Soltero', 'mama', 'femenino', 'mary@gmail.com', 'B+', 'Parroquia Caricuao Ud1', '04164655292', 'Activo', '2025-01-16 21:07:44'),
(114, 'V', 11985583, 'Maria Lupita', 'Aray Acosta', '2007-01-22', 'Casado', 'mama', 'masculino', 'marialupita@gmail.com', 'O+', 'Parroquia Caricuao Ud1', '04121988817', 'Activo', '2025-01-22 16:50:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(255) NOT NULL,
  `fyh_creacion` datetime DEFAULT NULL,
  `fyh_actualizacion` datetime DEFAULT NULL,
  `estado` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`, `fyh_creacion`, `fyh_actualizacion`, `estado`) VALUES
(1, 'ADMINISTRADOR', '2024-10-26 19:22:07', '2024-11-12 00:00:00', '1'),
(2, 'DIRECTOR', '2024-10-26 19:23:06', NULL, '1'),
(3, 'SUBDIRETOR', '2024-10-26 19:23:14', NULL, '1'),
(4, 'PERSONAL ADMINISTRATIVO', '2024-10-26 19:23:33', NULL, '1'),
(5, 'DOCENTE', '2024-10-26 19:23:43', NULL, '1'),
(7, 'REPRESENTANTE', '2024-10-27 00:00:00', '2025-01-17 00:00:00', '1'),
(8, 'ADMINISTRATIVOS', '2024-10-27 00:00:00', NULL, '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles_permisos`
--

DROP TABLE IF EXISTS `roles_permisos`;
CREATE TABLE `roles_permisos` (
  `id_rol_permiso` int(11) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `permiso_id` int(11) NOT NULL,
  `fyh_creacion` datetime DEFAULT NULL,
  `fyh_actualizacion` datetime DEFAULT NULL,
  `estado` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles_permisos`
--

INSERT INTO `roles_permisos` (`id_rol_permiso`, `rol_id`, `permiso_id`, `fyh_creacion`, `fyh_actualizacion`, `estado`) VALUES
(1, 1, 1, '2024-10-26 19:22:12', NULL, '1'),
(2, 1, 7, '2024-10-26 19:22:14', NULL, '1'),
(3, 1, 4, '2024-10-26 19:22:19', NULL, '1'),
(4, 1, 5, '2024-10-26 19:22:22', NULL, '1'),
(5, 1, 6, '2024-10-26 19:22:25', NULL, '1'),
(6, 1, 3, '2024-10-26 19:22:29', NULL, '1'),
(7, 1, 2, '2024-10-26 19:22:31', NULL, '1'),
(8, 1, 10, '2024-10-26 19:22:39', NULL, '1'),
(9, 1, 12, '2024-10-26 19:22:41', NULL, '1'),
(10, 1, 13, '2024-10-26 19:22:46', NULL, '1'),
(11, 1, 11, '2024-10-26 19:22:49', NULL, '1'),
(12, 1, 9, '2024-10-26 19:22:52', NULL, '1'),
(13, 1, 8, '2024-10-26 19:22:55', NULL, '1'),
(14, 4, 1, '2025-01-17 00:00:00', NULL, '1'),
(15, 4, 4, '2025-01-17 00:00:00', NULL, '1'),
(16, 7, 1, '2025-01-17 00:00:00', NULL, '1'),
(18, 7, 4, '2025-01-17 00:00:00', NULL, '1'),
(19, 7, 3, '2025-01-17 00:00:00', NULL, '1'),
(20, 7, 10, '2025-01-17 00:00:00', NULL, '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sangre`
--

DROP TABLE IF EXISTS `sangre`;
CREATE TABLE `sangre` (
  `sangre_id` int(30) NOT NULL,
  `tipo_sangre` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sangre`
--

INSERT INTO `sangre` (`sangre_id`, `tipo_sangre`) VALUES
(1, 'A+'),
(2, 'A-'),
(3, 'B+'),
(4, 'B-'),
(5, 'AB+'),
(6, 'AB-'),
(7, 'O+'),
(8, 'O-'),
(1, 'A+'),
(2, 'A-'),
(3, 'B+'),
(4, 'B-'),
(5, 'AB+'),
(6, 'AB-'),
(7, 'O+'),
(8, 'O-');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `secciones`
--

DROP TABLE IF EXISTS `secciones`;
CREATE TABLE `secciones` (
  `id_seccion` int(11) NOT NULL,
  `turno` char(1) DEFAULT NULL,
  `capacidad` int(11) NOT NULL,
  `id_gestion` int(11) NOT NULL,
  `id_grado` int(11) NOT NULL,
  `estado` tinyint(4) DEFAULT 1,
  `nombre_seccion` varchar(255) NOT NULL,
  `fyh_creacion` datetime DEFAULT current_timestamp(),
  `cupo_actual` int(11) DEFAULT 0,
  `aula` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `secciones`
--

INSERT INTO `secciones` (`id_seccion`, `turno`, `capacidad`, `id_gestion`, `id_grado`, `estado`, `nombre_seccion`, `fyh_creacion`, `cupo_actual`, `aula`) VALUES
(97, 'M', 35, 34, 50, 1, 'B', '2025-10-31 17:25:01', 0, ''),
(98, 'M', 25, 34, 50, 1, 'A', '2025-10-31 17:25:13', 1, ''),
(99, 'M', 25, 34, 50, 1, 'C', '2025-10-31 17:25:39', 0, ''),
(100, 'M', 30, 34, 51, 1, 'B', '2025-10-31 17:29:23', 4, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sexos`
--

DROP TABLE IF EXISTS `sexos`;
CREATE TABLE `sexos` (
  `sexo_id` int(11) NOT NULL,
  `sexo` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sexos`
--

INSERT INTO `sexos` (`sexo_id`, `sexo`) VALUES
(1, 'Masculino'),
(2, 'Femenino'),
(1, 'Masculino'),
(2, 'Femenino');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `social_media`
--

DROP TABLE IF EXISTS `social_media`;
CREATE TABLE `social_media` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL,
  `icon_type` enum('fontawesome','image') NOT NULL DEFAULT 'fontawesome',
  `color` varchar(7) NOT NULL DEFAULT '#3b5998',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `social_media`
--

INSERT INTO `social_media` (`id`, `name`, `url`, `icon`, `icon_type`, `color`, `status`, `date_created`, `date_updated`) VALUES
(1, 'Facebook', 'https://facebook.com/tuempresa', 'fab fa-facebook', 'fontawesome', '#3b5998', 1, '2025-08-27 18:08:50', '2025-10-12 23:20:10'),
(2, 'Twitter', 'https://twitter.com/tuempresa', 'fab fa-twitter', 'fontawesome', '#000000', 1, '2025-08-27 18:08:50', '2025-10-12 23:21:12'),
(3, 'Instagram', 'https://instagram.com/tuempresa', 'fab fa-instagram', 'fontawesome', '#E1306C', 1, '2025-08-27 18:08:50', '2025-10-12 23:21:29'),
(4, 'WhatsApp', 'https://web.whatsapp.com/', 'fab fa-whatsapp', 'fontawesome', '#00FF00', 1, '2025-08-27 18:08:50', '2025-10-12 23:21:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_constancias`
--

DROP TABLE IF EXISTS `solicitudes_constancias`;
CREATE TABLE `solicitudes_constancias` (
  `id_solicitud` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `cedula_estudiante` varchar(20) NOT NULL,
  `nombre_estudiante` varchar(100) NOT NULL,
  `grado_seccion` varchar(50) NOT NULL,
  `id_tipo_constancia` int(11) NOT NULL,
  `nombre_representante` varchar(100) NOT NULL,
  `cedula_representante` varchar(20) NOT NULL,
  `parentesco` varchar(50) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_solicitud` datetime NOT NULL,
  `fecha_aprobacion` datetime DEFAULT NULL,
  `fecha_entrega` datetime DEFAULT NULL,
  `estado` enum('Pendiente','Aprobada','Rechazada','Entregada') NOT NULL DEFAULT 'Pendiente',
  `id_usuario_aprobador` int(11) DEFAULT NULL,
  `id_usuario_entrega` int(11) DEFAULT NULL,
  `ruta_pdf` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `solicitudes_constancias`
--

INSERT INTO `solicitudes_constancias` (`id_solicitud`, `id_estudiante`, `cedula_estudiante`, `nombre_estudiante`, `grado_seccion`, `id_tipo_constancia`, `nombre_representante`, `cedula_representante`, `parentesco`, `observaciones`, `fecha_solicitud`, `fecha_aprobacion`, `fecha_entrega`, `estado`, `id_usuario_aprobador`, `id_usuario_entrega`, `ruta_pdf`, `created_at`, `updated_at`) VALUES
(1, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:17:54', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:17:54', '2025-04-20 04:17:54'),
(2, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:17:54', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:17:54', '2025-04-20 04:17:54'),
(3, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:22:52', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:22:52', '2025-04-20 04:22:52'),
(4, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:22:52', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:22:52', '2025-04-20 04:22:52'),
(5, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Representante Legal', '', '2025-04-20 06:33:53', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:33:53', '2025-04-20 04:33:53'),
(6, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Representante Legal', '', '2025-04-20 06:33:55', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:33:55', '2025-04-20 04:33:55'),
(7, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:39:03', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:39:03', '2025-04-20 04:39:03'),
(8, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:39:06', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:39:06', '2025-04-20 04:39:06'),
(9, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:40:36', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:40:36', '2025-04-20 04:40:36'),
(10, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:40:37', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:40:37', '2025-04-20 04:40:37'),
(11, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:41:25', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:41:25', '2025-04-20 04:41:25'),
(12, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:41:26', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:41:26', '2025-04-20 04:41:26'),
(13, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:42:52', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:42:52', '2025-04-20 04:42:52'),
(14, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:48:49', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:48:49', '2025-04-20 04:48:49'),
(15, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:48:51', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:48:51', '2025-04-20 04:48:51'),
(16, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:49:59', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:49:59', '2025-04-20 04:49:59'),
(17, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:50:00', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:50:00', '2025-04-20 04:50:00'),
(18, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:51:53', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:51:53', '2025-04-20 04:51:53'),
(19, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:51:55', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:51:55', '2025-04-20 04:51:55'),
(20, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:53:35', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:53:35', '2025-04-20 04:53:35'),
(21, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:53:37', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:53:37', '2025-04-20 04:53:37'),
(22, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:54:28', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:54:28', '2025-04-20 04:54:28'),
(23, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:54:31', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:54:31', '2025-04-20 04:54:31'),
(24, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:55:55', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:55:55', '2025-04-20 04:55:55'),
(25, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:55:57', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:55:57', '2025-04-20 04:55:57'),
(26, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:57:15', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:57:15', '2025-04-20 04:57:15'),
(27, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:57:17', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:57:17', '2025-04-20 04:57:17'),
(28, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:59:46', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:59:46', '2025-04-20 04:59:46'),
(29, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 06:59:48', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 04:59:48', '2025-04-20 04:59:48'),
(30, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 07:00:29', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 05:00:29', '2025-04-20 05:00:29'),
(31, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 07:00:31', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 05:00:31', '2025-04-20 05:00:31'),
(32, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 07:03:35', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 05:03:35', '2025-04-20 05:03:35'),
(33, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 07:03:37', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 05:03:37', '2025-04-20 05:03:37'),
(34, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Representante Legal', '', '2025-04-20 18:20:45', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 16:20:45', '2025-04-20 16:20:45'),
(35, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Representante Legal', '', '2025-04-20 18:20:45', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 16:20:45', '2025-04-20 16:20:45'),
(36, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 18:41:09', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 16:41:09', '2025-04-20 16:41:09'),
(37, 1, 'V-30045678', 'Carlos Eduardo Pérez López', 'N/A - N/A', 1, 'Carlos Alberto Pérez López', 'V-14023560', 'Padre', '', '2025-04-20 18:41:11', NULL, NULL, 'Pendiente', NULL, NULL, NULL, '2025-04-20 16:41:11', '2025-04-20 16:41:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','image','color') DEFAULT 'text',
  `is_logo` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `is_logo`, `description`, `created_at`, `updated_at`) VALUES
(1, 'SISTEMA INTEGRAL DE GESTIÓN', 'uploads/settings/1756425951_Captura de pantalla 2025-08-28 183911.png', 'image', '1', 'Título del sistema que aparece en el login', '2025-08-28 23:46:26', '2025-08-29 00:27:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tallas`
--

DROP TABLE IF EXISTS `tallas`;
CREATE TABLE `tallas` (
  `talla_id` int(30) NOT NULL,
  `talla` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tallas`
--

INSERT INTO `tallas` (`talla_id`, `talla`) VALUES
(1, 'XS'),
(2, 'S'),
(3, 'M'),
(4, 'L'),
(5, 'XL'),
(6, 'XXL'),
(1, 'XS'),
(2, 'S'),
(3, 'M'),
(4, 'L'),
(5, 'XL'),
(6, 'XXL');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `task_list`
--

DROP TABLE IF EXISTS `task_list`;
CREATE TABLE `task_list` (
  `id` int(30) NOT NULL,
  `project_id` int(30) NOT NULL,
  `task` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `status` tinyint(4) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `task_list`
--

INSERT INTO `task_list` (`id`, `project_id`, `task`, `description`, `status`, `date_created`) VALUES
(1, 1, 'Sample Task 1', '								&lt;span style=&quot;color: rgb(0, 0, 0); font-family: &amp;quot;Open Sans&amp;quot;, Arial, sans-serif; font-size: 14px; text-align: justify;&quot;&gt;Fusce ullamcorper mattis semper. Nunc vel risus ipsum. Sed maximus dapibus nisl non laoreet. Pellentesque quis mauris odio. Donec fermentum facilisis odio, sit amet aliquet purus scelerisque eget.&amp;nbsp;&lt;/span&gt;													', 3, '2020-12-03 11:08:58'),
(2, 1, 'Sample Task 2', 'Sample Task 2							', 1, '2020-12-03 13:50:15'),
(3, 2, 'Task Test', 'Sample', 1, '2020-12-03 13:52:25'),
(4, 2, 'test 23', 'Sample test 23', 1, '2020-12-03 13:52:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblcategory`
--

DROP TABLE IF EXISTS `tblcategory`;
CREATE TABLE `tblcategory` (
  `id` int(11) NOT NULL,
  `CategoryName` varchar(255) NOT NULL,
  `Description` text DEFAULT NULL,
  `PostingDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NULL DEFAULT NULL,
  `Is_Active` int(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tblcategory`
--

INSERT INTO `tblcategory` (`id`, `CategoryName`, `Description`, `PostingDate`, `UpdationDate`, `Is_Active`) VALUES
(1, 'Eventos Escolares', 'Eventos y actividades especiales organizadas por la institución educativa', '2025-08-31 15:20:33', NULL, 1),
(2, 'Logros Académicos', 'Reconocimientos y logros alcanzados por estudiantes y profesores', '2025-08-31 16:46:33', NULL, 1),
(3, 'Actividades Deportivas', 'Competencias, torneos y actividades deportivas escolares', '2025-08-31 16:46:43', NULL, 1),
(4, 'Talleres y Capacitaciones', 'Talleres, seminarios y programas de capacitación para la comunidad educativa', '2025-08-31 16:46:54', NULL, 1),
(5, 'Proyectos Estudiantiles', 'Proyectos innovadores desarrollados por los estudiantes', '2025-08-31 16:47:07', NULL, 1),
(6, 'Cultura y Arte', 'Actividades culturales, artísticas y presentaciones estudiantiles', '2025-08-31 16:47:15', NULL, 1),
(7, 'Anuncios Importantes', 'Comunicados oficiales y anuncios de la dirección escolar', '2025-08-31 16:47:25', NULL, 1),
(8, 'Voluntariado y Servicio', 'Actividades de servicio comunitario y programas de voluntariado', '2025-08-31 16:47:37', NULL, 1),
(9, 'Tecnología Educativa', 'Avances tecnológicos y recursos digitales para el aprendizaje', '2025-08-31 16:47:49', NULL, 1),
(10, 'Investigación Científica', 'Proyectos de investigación y ferias científicas estudiantiles', '2025-08-31 16:47:57', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblcomments`
--

DROP TABLE IF EXISTS `tblcomments`;
CREATE TABLE `tblcomments` (
  `id` int(11) NOT NULL,
  `postId` char(11) DEFAULT NULL,
  `name` varchar(120) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `comment` mediumtext DEFAULT NULL,
  `postingDate` timestamp NULL DEFAULT current_timestamp(),
  `status` int(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblposts`
--

DROP TABLE IF EXISTS `tblposts`;
CREATE TABLE `tblposts` (
  `id` int(11) NOT NULL,
  `PostTitle` longtext DEFAULT NULL,
  `CategoryId` int(11) DEFAULT NULL,
  `SubCategoryId` int(11) DEFAULT NULL,
  `PostDetails` longtext CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `PostingDate` timestamp NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `Is_Active` int(1) DEFAULT NULL,
  `PostUrl` mediumtext DEFAULT NULL,
  `PostImage` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `tblposts`
--

INSERT INTO `tblposts` (`id`, `PostTitle`, `CategoryId`, `SubCategoryId`, `PostDetails`, `PostingDate`, `UpdationDate`, `Is_Active`, `PostUrl`, `PostImage`) VALUES
(1, 'Inauguración del Nuevo Laboratorio de Ciencias', 1, 1, 'El pasado viernes se inauguró oficialmente el nuevo laboratorio de ciencias equipado con tecnología de última generación para beneficio de nuestros estudiantes.', '2024-01-15 14:00:00', '2025-10-13 04:31:25', 1, 'inauguracion-laboratorio-ciencias', 'ciencias1.png'),
(2, 'Ganadores del Concurso de Matemáticas Regional', 2, 3, 'Nuestros estudiantes obtuvieron el primer lugar en el concurso regional de matemáticas, demostrando excelencia académica.', '2024-01-12 18:30:00', '2025-10-13 05:01:16', 1, 'ganadores-concurso-matematicas', 'matematicas1.png'),
(3, 'Charla sobre Orientación Vocacional para Bachilleres', 3, 5, 'Programa especial de orientación vocacional para estudiantes de último año, con participación de universidades locales.', '2024-01-10 13:00:00', '2025-10-13 04:12:53', 1, 'charla-orientacion-vocacional', 'orientacion1.png'),
(4, 'Festival Deportivo Interescolar 2024', 4, 7, 'Gran éxito del festival deportivo que reunió a más de 15 instituciones educativas en competencias amistosas.', '2024-01-08 20:45:00', '2025-10-13 04:58:27', 1, 'festival-deportivo-interescolar-2024', 'deportes1.png'),
(5, 'Nuevo Programa de Inglés Intensivo', 5, 9, 'Implementación del programa de inglés intensivo con metodología comunicativa para todos los niveles.', '2024-01-05 15:20:00', '2025-10-13 04:56:10', 1, 'programa-ingles-intensivo', 'ingles1.png'),
(6, 'Celebración del Día del Maestro', 6, 11, 'Emotiva celebración en honor a nuestros docentes, reconociendo su invaluable labor educativa.', '2024-01-03 12:00:00', '2025-10-13 04:23:51', 1, 'celebracion-dia-del-maestro', 'maestros1.png'),
(7, 'Proyecto Ecológico: Huerto Escolar', 7, 13, 'Los estudiantes implementaron un huerto escolar como parte del proyecto de conciencia ambiental.', '2023-12-28 17:15:00', '2025-10-13 04:19:45', 1, 'proyecto-ecologico-huerto-escolar', 'ecologia1.png'),
(8, 'Concierto de Navidad del Coro Estudiantil', 8, 15, 'El coro estudiantil presentó un emotivo concierto navideño ante la comunidad educativa.', '2023-12-20 22:30:00', '2025-10-13 04:49:52', 1, 'concierto-navidad-coro-estudiantil', 'musica1.png'),
(9, 'Taller de Robótica para Primaria', 1, 2, 'Introducción a la robótica educativa para estudiantes de primaria, desarrollando habilidades STEM.', '2023-12-18 14:00:00', '2025-10-13 04:28:24', 1, 'taller-robotica-primaria', 'robotica1.png'),
(10, 'Convenio con Universidad Nacional', 3, 6, 'Firma de convenio que beneficiará a nuestros bachilleres con acceso preferencial a la universidad.', '2023-12-15 16:00:00', '2025-10-13 04:08:17', 1, 'convenio-universidad-nacional', 'universidad1.png'),
(11, 'Exposición de Arte Estudiantil', 9, 17, 'Exhibición de trabajos artísticos realizados por estudiantes durante el semestre.', '2023-12-12 19:30:00', '2025-10-13 04:47:09', 1, 'exposicion-arte-estudiantil', 'arte1.png'),
(12, 'Campamento de Liderazgo Juvenil', 10, 19, 'Estudiantes participaron en campamento para desarrollar habilidades de liderazgo y trabajo en equipo.', '2023-12-10 11:00:00', '2025-10-13 04:44:41', 1, 'campamento-liderazgo-juvenil', 'liderazgo1.png'),
(13, 'Mejoras en la Infraestructura Deportiva', 4, 8, 'Completadas las mejoras en canchas deportivas e instalaciones para educación física.', '2023-12-08 18:00:00', '2025-10-13 04:42:55', 1, 'mejoras-infraestructura-deportiva', 'deportes2.png'),
(14, 'Charla sobre Salud Mental Adolescente', 11, 21, 'Especialistas en psicología adolescente brindaron charlas sobre manejo del estrés y salud mental.', '2023-12-05 15:00:00', '2025-10-13 04:40:21', 1, 'charla-salud-mental-adolescente', 'salud1.png'),
(15, 'Competencia de Spelling Bee 2023', 5, 10, 'Finalizó con éxito la competencia anual de Spelling Bee con participación récord de estudiantes.', '2023-12-03 13:30:00', '2025-10-13 04:36:14', 1, 'competencia-spelling-bee-2023', 'ingles2.png'),
(16, 'Proyecto de Reciclaje Escolar', 7, 14, 'Iniciativa estudiantil logró recolectar más de 500 kg de material reciclable en un mes.', '2023-11-30 17:45:00', '2025-10-13 04:17:24', 1, 'proyecto-reciclaje-escolar', 'ecologia2.png'),
(17, 'Visita al Museo de Ciencias Naturales', 1, 1, 'Estudiantes de secundaria realizaron visita educativa al museo como complemento a sus clases.', '2023-11-28 12:30:00', '2025-10-13 04:26:06', 1, 'visita-museo-ciencias-naturales', 'ciencias2.png'),
(18, 'Festival de Talentos Estudiantiles', 12, 23, 'Descubrimiento de talentos ocultos en música, baile, teatro y otras expresiones artísticas.', '2023-11-25 21:00:00', '2025-10-13 04:33:42', 1, 'festival-talentos-estudiantiles', 'talento1.png'),
(19, 'Programa de Refuerzo Académico', 2, 4, 'Implementación de programa de refuerzo para estudiantes que requieren apoyo adicional.', '2023-11-22 19:00:00', '2025-10-13 04:02:46', 1, 'programa-refuerzo-academico', 'academico1.png'),
(20, 'Ceremonia de Graduación 2023', 3, 5, 'Emotiva ceremonia de graduación para la promoción 2023, con 98% de aprobados.', '2023-11-20 23:00:00', '2025-10-03 18:57:04', 1, 'ceremonia-graduacion-2023', 'graduacion1.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblsubcategory`
--

DROP TABLE IF EXISTS `tblsubcategory`;
CREATE TABLE `tblsubcategory` (
  `SubCategoryId` int(11) NOT NULL,
  `CategoryId` int(11) NOT NULL,
  `Subcategory` varchar(255) NOT NULL,
  `SubCatDescription` text DEFAULT NULL,
  `PostingDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NULL DEFAULT NULL,
  `Is_Active` int(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tblsubcategory`
--

INSERT INTO `tblsubcategory` (`SubCategoryId`, `CategoryId`, `Subcategory`, `SubCatDescription`, `PostingDate`, `UpdationDate`, `Is_Active`) VALUES
(1, 1, 'Festivales Anuales', 'Celebraciones y festivales tradicionales de la institución como día del estudiante, aniversario, etc.', '2025-08-31 15:21:26', NULL, 1),
(2, 1, 'Ceremonias de Graduación', 'Eventos de graduación y entrega de diplomas para diferentes niveles educativos', '2025-08-31 16:51:55', NULL, 1),
(3, 2, 'Olimpiadas del Conocimiento', 'Participación y resultados en competencias académicas intercolegiales', '2025-08-31 16:52:10', NULL, 1),
(4, 2, 'Reconocimientos Honoríficos', 'Premios y distinciones a estudiantes y docentes por excelencia académica', '2025-08-31 16:52:25', NULL, 1),
(5, 3, 'Torneos Intercursos', 'Competencias deportivas entre diferentes cursos y grados', '2025-08-31 16:52:40', NULL, 1),
(6, 3, 'Juegos Intercolegiales', 'Participación en competencias deportivas con otras instituciones', '2025-08-31 16:52:55', NULL, 1),
(7, 4, 'Talleres para Padres', 'Programas de capacitación y orientación para padres de familia', '2025-08-31 16:53:10', NULL, 1),
(8, 4, 'Desarrollo Docente', 'Capacitaciones y actualizaciones para el personal docente', '2025-08-31 16:53:25', NULL, 1),
(9, 5, 'Ferias de Ciencias', 'Exposición de proyectos científicos y tecnológicos estudiantiles', '2025-08-31 16:53:40', NULL, 1),
(10, 5, 'Emprendimiento Estudiantil', 'Proyectos de emprendimiento desarrollados por los estudiantes', '2025-08-31 16:53:55', NULL, 1),
(11, 6, 'Presentaciones Artísticas', 'Shows de teatro, danza, música y otras expresiones artísticas', '2025-08-31 16:54:10', NULL, 1),
(12, 6, 'Exposiciones Culturales', 'Exhibiciones de arte, fotografía y trabajos creativos estudiantiles', '2025-08-31 16:54:25', NULL, 1),
(13, 7, 'Convocatorias Oficiales', 'Llamados y convocatorias oficiales de la dirección académica', '2025-08-31 16:54:40', NULL, 1),
(14, 7, 'Cambios de Horario', 'Avisos sobre modificaciones en horarios y calendarios académicos', '2025-08-31 16:54:55', NULL, 1),
(15, 8, 'Programas de Voluntariado', 'Oportunidades de servicio comunitario y voluntariado estudiantil', '2025-08-31 16:55:10', NULL, 1),
(16, 8, 'Proyectos Sociales', 'Iniciativas de apoyo a la comunidad y proyectos de impacto social', '2025-08-31 16:55:25', NULL, 1),
(17, 9, 'Plataformas Digitales', 'Implementación y uso de nuevas plataformas educativas digitales', '2025-08-31 16:55:40', NULL, 1),
(18, 9, 'Recursos Educativos', 'Nuevos recursos tecnológicos y herramientas digitales para el aprendizaje', '2025-08-31 16:55:55', NULL, 1),
(19, 10, 'Proyectos de Investigación', 'Investigaciones científicas desarrolladas por estudiantes y docentes', '2025-08-31 16:56:10', NULL, 1),
(20, 10, 'Publicaciones Académicas', 'Artículos y trabajos de investigación publicados por la comunidad educativa', '2025-08-31 16:56:25', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `team_members`
--

DROP TABLE IF EXISTS `team_members`;
CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `position_order` int(11) DEFAULT 0,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_constancia`
--

DROP TABLE IF EXISTS `tipos_constancia`;
CREATE TABLE `tipos_constancia` (
  `id_tipo_constancia` int(11) NOT NULL,
  `nombre_tipo_constancia` varchar(255) NOT NULL,
  `descripcion_tipo_constancia` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos_constancia`
--

INSERT INTO `tipos_constancia` (`id_tipo_constancia`, `nombre_tipo_constancia`, `descripcion_tipo_constancia`, `fecha_creacion`) VALUES
(1, 'Constancia de Estudio', NULL, '2025-04-20 03:35:35'),
(2, 'Constancia de Conducta', NULL, '2025-04-20 03:35:35'),
(3, 'Constancia de Notas', NULL, '2025-04-20 03:35:35'),
(4, 'Constancia de Matrícula', NULL, '2025-04-20 03:35:35'),
(5, 'Constancia de Regularidad', NULL, '2025-04-20 03:35:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `turnos`
--

DROP TABLE IF EXISTS `turnos`;
CREATE TABLE `turnos` (
  `id_turno` int(11) NOT NULL,
  `nombre_turno` varchar(50) NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `turnos`
--

INSERT INTO `turnos` (`id_turno`, `nombre_turno`, `estado`, `created_at`, `updated_at`) VALUES
(1, 'Mañana', 'activo', '2025-01-09 21:28:38', '2025-01-09 21:28:38'),
(2, 'Tarde', 'activo', '2025-01-09 21:28:38', '2025-01-09 21:28:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(30) NOT NULL,
  `firstname` varchar(200) NOT NULL,
  `lastname` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` text NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 2 COMMENT '1 = admin, 2 = staff',
  `avatar` text NOT NULL DEFAULT 'no-image-available.png',
  `status` int(11) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `email`, `password`, `type`, `avatar`, `status`, `date_created`) VALUES
(1, 'Administrator', '', 'admin@admin.com', '0192023a7bbd73250516f069df18b500', 1, 'no-image-available.png', 1, '2020-11-26 10:57:04'),
(2, 'John', 'Smith', 'jsmith@sample.com', '1254737c076cf867dc53d60a0364f38e', 2, '1606978560_avatar.jpg', 1, '2020-12-03 09:26:03'),
(3, 'Claire', 'Blake', 'cblake@sample.com', '4744ddea876b11dcb1d169fadf494418', 3, '1606958760_47446233-clean-noir-et-gradient-sombre-image-de-fond-abstrait-.jpg', 1, '2020-12-03 09:26:42'),
(4, 'George', 'Wilson', 'gwilson@sample.com', 'd40242fb23c45206fadee4e2418f274f', 3, '1606963560_avatar.jpg', 1, '2020-12-03 10:46:41'),
(5, 'Mike', 'Williams', 'mwilliams@sample.com', '3cc93e9a6741d8b40460457139cf8ced', 3, '1606963620_47446233-clean-noir-et-gradient-sombre-image-de-fond-abstrait-.jpg', 1, '2020-12-03 10:47:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_productivity`
--

DROP TABLE IF EXISTS `user_productivity`;
CREATE TABLE `user_productivity` (
  `id` int(30) NOT NULL,
  `project_id` int(30) NOT NULL,
  `task_id` int(30) NOT NULL,
  `comment` text NOT NULL,
  `subject` varchar(200) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `user_id` int(30) NOT NULL,
  `time_rendered` float NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `user_productivity`
--

INSERT INTO `user_productivity` (`id`, `project_id`, `task_id`, `comment`, `subject`, `date`, `start_time`, `end_time`, `user_id`, `time_rendered`, `date_created`) VALUES
(1, 1, 1, '							&lt;p&gt;Sample Progress&lt;/p&gt;&lt;ul&gt;&lt;li&gt;Test 1&lt;/li&gt;&lt;li&gt;Test 2&lt;/li&gt;&lt;li&gt;Test 3&lt;/li&gt;&lt;/ul&gt;																			', 'Sample Progress', '2020-12-03', '08:00:00', '10:00:00', 1, 2, '2020-12-03 12:13:28'),
(2, 1, 1, '							Sample Progress						', 'Sample Progress 2', '2020-12-03', '13:00:00', '14:00:00', 1, 1, '2020-12-03 13:48:28'),
(3, 1, 2, '							Sample						', 'Test', '2020-12-03', '08:00:00', '09:00:00', 5, 1, '2020-12-03 13:57:22'),
(4, 1, 2, 'asdasdasd', 'Sample Progress', '2020-12-02', '08:00:00', '10:00:00', 2, 2, '2020-12-03 14:36:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` text NOT NULL,
  `fyh_creacion` datetime DEFAULT NULL,
  `fyh_actualizacion` datetime DEFAULT NULL,
  `estado` varchar(11) DEFAULT NULL,
  `token_recuperacion` varchar(255) DEFAULT NULL,
  `expiracion_token` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `rol_id`, `email`, `password`, `fyh_creacion`, `fyh_actualizacion`, `estado`, `token_recuperacion`, `expiracion_token`) VALUES
(1, 1, 'admin@gmail.com', '$2y$10$NVhkeupcyKUPFqx.l7t7n.qELV7X5LxKjmOV3WwyRQ3CfJquHF0P2', '2023-12-28 20:29:10', '2024-11-12 00:00:00', '1', NULL, NULL),
(76, 5, 'heldyndiaz19@gmail.com', '$2y$10$wZJBiqDScc2RgPHBpl/N9.wd6yPxRIXKkbu8/GVDZsLoaXnjAwyhS', '2025-10-20 16:02:12', NULL, '1', 'ea5cf2280006ee376689b4ae44344a8739367920847d4efd3c049cdf87693459', '2025-11-03 21:11:45'),
(77, 5, 'docente@gmail.com', '$2y$10$zc9wchmp4M0syuycKEBuKewwPcb8hLdknyo9W/O98Gm.X0SIFCFWq', '2025-10-21 20:23:56', NULL, '1', NULL, NULL),
(79, 2, 'keila@gmail.com', '$2y$10$D.P0yn6rxIbwgiOzidG3I.7FdyiMeto5pq1Qr5hCOPIIgYOWHL4Fe', '2025-10-31 00:00:00', '2025-11-03 00:00:00', '1', NULL, NULL),
(80, 2, 'heldyndiaz@gmail.com', '$2y$10$KaHW.NQX2HNuEZaMtrkwPOxPkEGiE06N7W3vivQn4hNsWztAha/Lq', '2025-10-31 00:00:00', '2025-11-03 00:00:00', '1', NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `about_us`
--
ALTER TABLE `about_us`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `academic_page`
--
ALTER TABLE `academic_page`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `academic_programs`
--
ALTER TABLE `academic_programs`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `academic_resources`
--
ALTER TABLE `academic_resources`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `administrativos`
--
ALTER TABLE `administrativos`
  ADD PRIMARY KEY (`id_administrativo`),
  ADD KEY `persona_id` (`persona_id`);

--
-- Indices de la tabla `asignaciones_profesor`
--
ALTER TABLE `asignaciones_profesor`
  ADD PRIMARY KEY (`id_asignacion`),
  ADD UNIQUE KEY `uk_asignacion` (`id_profesor`,`id_materia`,`id_seccion`,`id_gestion`),
  ADD KEY `id_materia` (`id_materia`),
  ADD KEY `id_seccion` (`id_seccion`),
  ADD KEY `id_gestion` (`id_gestion`);

--
-- Indices de la tabla `calendario_academico`
--
ALTER TABLE `calendario_academico`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `carrusel`
--
ALTER TABLE `carrusel`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `chat_mensajes`
--
ALTER TABLE `chat_mensajes`
  ADD PRIMARY KEY (`id_mensaje`);

--
-- Indices de la tabla `chat_reacciones`
--
ALTER TABLE `chat_reacciones`
  ADD PRIMARY KEY (`id_reaccion`),
  ADD UNIQUE KEY `unique_reaccion` (`id_mensaje`,`id_usuario`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `citas_nacimiento`
--
ALTER TABLE `citas_nacimiento`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cita` (`fecha_cita`,`hora_cita`);

--
-- Indices de la tabla `collaborators`
--
ALTER TABLE `collaborators`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `configuracion_instituciones`
--
ALTER TABLE `configuracion_instituciones`
  ADD PRIMARY KEY (`id_config_institucion`);

--
-- Indices de la tabla `contactos`
--
ALTER TABLE `contactos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `datos_institucion`
--
ALTER TABLE `datos_institucion`
  ADD PRIMARY KEY (`id_institucion`);

--
-- Indices de la tabla `diseno_carnets`
--
ALTER TABLE `diseno_carnets`
  ADD PRIMARY KEY (`id_diseno`);

--
-- Indices de la tabla `docentes`
--
ALTER TABLE `docentes`
  ADD PRIMARY KEY (`id_docente`),
  ADD KEY `persona_id` (`persona_id`);

--
-- Indices de la tabla `documento`
--
ALTER TABLE `documento`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `documentos_internos`
--
ALTER TABLE `documentos_internos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD PRIMARY KEY (`id_estudiante`),
  ADD KEY `id_representante` (`id_representante`),
  ADD KEY `turno_id` (`turno_id`);

--
-- Indices de la tabla `gestiones`
--
ALTER TABLE `gestiones`
  ADD PRIMARY KEY (`id_gestion`);

--
-- Indices de la tabla `grados`
--
ALTER TABLE `grados`
  ADD PRIMARY KEY (`id_grado`);

--
-- Indices de la tabla `grados_materias`
--
ALTER TABLE `grados_materias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_grado` (`id_grado`),
  ADD KEY `id_materia` (`id_materia`);

--
-- Indices de la tabla `historial_cambios_notas`
--
ALTER TABLE `historial_cambios_notas`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `id_estudiante` (`id_estudiante`),
  ADD KEY `id_materia` (`id_materia`),
  ADD KEY `id_lapso` (`id_lapso`),
  ADD KEY `id_profesor` (`id_profesor`);

--
-- Indices de la tabla `historial_notas`
--
ALTER TABLE `historial_notas`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `id_nota` (`id_nota`),
  ADD KEY `id_estudiante` (`id_estudiante`),
  ADD KEY `id_materia` (`id_materia`),
  ADD KEY `id_lapso` (`id_lapso`),
  ADD KEY `usuario_cambio` (`usuario_cambio`);

--
-- Indices de la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD PRIMARY KEY (`id_horario`),
  ADD KEY `id_gestion` (`id_gestion`),
  ADD KEY `id_grado` (`id_grado`),
  ADD KEY `id_seccion` (`id_seccion`);

--
-- Indices de la tabla `horario_detalle`
--
ALTER TABLE `horario_detalle`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_horario` (`id_horario`),
  ADD KEY `id_materia` (`id_materia`),
  ADD KEY `id_profesor` (`id_profesor`);

--
-- Indices de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_id_seccion` (`id_seccion`);

--
-- Indices de la tabla `lapsos`
--
ALTER TABLE `lapsos`
  ADD PRIMARY KEY (`id_lapso`),
  ADD KEY `id_gestion` (`id_gestion`);

--
-- Indices de la tabla `materias`
--
ALTER TABLE `materias`
  ADD PRIMARY KEY (`id_materia`);

--
-- Indices de la tabla `niveles`
--
ALTER TABLE `niveles`
  ADD PRIMARY KEY (`id_nivel`),
  ADD UNIQUE KEY `gestion_id_2` (`gestion_id`),
  ADD KEY `gestion_id` (`gestion_id`);

--
-- Indices de la tabla `notas_estudiantes`
--
ALTER TABLE `notas_estudiantes`
  ADD PRIMARY KEY (`id_nota`),
  ADD UNIQUE KEY `id_estudiante` (`id_estudiante`,`id_materia`,`id_lapso`),
  ADD KEY `id_materia` (`id_materia`),
  ADD KEY `id_lapso` (`id_lapso`);

--
-- Indices de la tabla `periodos_anuales`
--
ALTER TABLE `periodos_anuales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_anio` (`año`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id_permiso`);

--
-- Indices de la tabla `personas`
--
ALTER TABLE `personas`
  ADD PRIMARY KEY (`id_persona`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `plantillas_carnet`
--
ALTER TABLE `plantillas_carnet`
  ADD PRIMARY KEY (`id_plantilla`);

--
-- Indices de la tabla `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `profesores`
--
ALTER TABLE `profesores`
  ADD PRIMARY KEY (`id_profesor`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cedula` (`cedula`);

--
-- Indices de la tabla `profesor_seccion_materia`
--
ALTER TABLE `profesor_seccion_materia`
  ADD PRIMARY KEY (`id_relacion`),
  ADD KEY `id_profesor` (`id_profesor`),
  ADD KEY `id_seccion` (`id_seccion`),
  ADD KEY `id_materia` (`id_materia`),
  ADD KEY `id_gestion` (`id_gestion`);

--
-- Indices de la tabla `project_list`
--
ALTER TABLE `project_list`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`id_reporte`),
  ADD KEY `id_estudiante` (`id_estudiante`);

--
-- Indices de la tabla `representantes`
--
ALTER TABLE `representantes`
  ADD PRIMARY KEY (`id_representante`),
  ADD KEY `correo_electrónico` (`correo_electronico`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre_rol` (`nombre_rol`);

--
-- Indices de la tabla `roles_permisos`
--
ALTER TABLE `roles_permisos`
  ADD PRIMARY KEY (`id_rol_permiso`),
  ADD KEY `rol_id` (`rol_id`),
  ADD KEY `permiso_id` (`permiso_id`);

--
-- Indices de la tabla `secciones`
--
ALTER TABLE `secciones`
  ADD PRIMARY KEY (`id_seccion`),
  ADD KEY `id_gestion` (`id_gestion`),
  ADD KEY `id_grado` (`id_grado`);

--
-- Indices de la tabla `social_media`
--
ALTER TABLE `social_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status_index` (`status`),
  ADD KEY `icon_type_index` (`icon_type`);

--
-- Indices de la tabla `solicitudes_constancias`
--
ALTER TABLE `solicitudes_constancias`
  ADD PRIMARY KEY (`id_solicitud`),
  ADD KEY `id_estudiante` (`id_estudiante`),
  ADD KEY `id_tipo_constancia` (`id_tipo_constancia`),
  ADD KEY `id_usuario_aprobador` (`id_usuario_aprobador`),
  ADD KEY `id_usuario_entrega` (`id_usuario_entrega`);

--
-- Indices de la tabla `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indices de la tabla `task_list`
--
ALTER TABLE `task_list`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tblcategory`
--
ALTER TABLE `tblcategory`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tblcomments`
--
ALTER TABLE `tblcomments`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tblposts`
--
ALTER TABLE `tblposts`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tblsubcategory`
--
ALTER TABLE `tblsubcategory`
  ADD PRIMARY KEY (`SubCategoryId`),
  ADD KEY `CategoryId` (`CategoryId`);

--
-- Indices de la tabla `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tipos_constancia`
--
ALTER TABLE `tipos_constancia`
  ADD PRIMARY KEY (`id_tipo_constancia`),
  ADD UNIQUE KEY `nombre_tipo_constancia` (`nombre_tipo_constancia`);

--
-- Indices de la tabla `turnos`
--
ALTER TABLE `turnos`
  ADD PRIMARY KEY (`id_turno`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `user_productivity`
--
ALTER TABLE `user_productivity`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `rol_id` (`rol_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `about_us`
--
ALTER TABLE `about_us`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `academic_page`
--
ALTER TABLE `academic_page`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `academic_programs`
--
ALTER TABLE `academic_programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `academic_resources`
--
ALTER TABLE `academic_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `administrativos`
--
ALTER TABLE `administrativos`
  MODIFY `id_administrativo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `asignaciones_profesor`
--
ALTER TABLE `asignaciones_profesor`
  MODIFY `id_asignacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT de la tabla `calendario_academico`
--
ALTER TABLE `calendario_academico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `carrusel`
--
ALTER TABLE `carrusel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `chat_mensajes`
--
ALTER TABLE `chat_mensajes`
  MODIFY `id_mensaje` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `chat_reacciones`
--
ALTER TABLE `chat_reacciones`
  MODIFY `id_reaccion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `citas_nacimiento`
--
ALTER TABLE `citas_nacimiento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `collaborators`
--
ALTER TABLE `collaborators`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `configuracion_instituciones`
--
ALTER TABLE `configuracion_instituciones`
  MODIFY `id_config_institucion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `contactos`
--
ALTER TABLE `contactos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `datos_institucion`
--
ALTER TABLE `datos_institucion`
  MODIFY `id_institucion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `diseno_carnets`
--
ALTER TABLE `diseno_carnets`
  MODIFY `id_diseno` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `docentes`
--
ALTER TABLE `docentes`
  MODIFY `id_docente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `documento`
--
ALTER TABLE `documento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `documentos`
--
ALTER TABLE `documentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `documentos_internos`
--
ALTER TABLE `documentos_internos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  MODIFY `id_estudiante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT de la tabla `gestiones`
--
ALTER TABLE `gestiones`
  MODIFY `id_gestion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `grados`
--
ALTER TABLE `grados`
  MODIFY `id_grado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT de la tabla `grados_materias`
--
ALTER TABLE `grados_materias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historial_cambios_notas`
--
ALTER TABLE `historial_cambios_notas`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historial_notas`
--
ALTER TABLE `historial_notas`
  MODIFY `id_historial` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT de la tabla `horarios`
--
ALTER TABLE `horarios`
  MODIFY `id_horario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `horario_detalle`
--
ALTER TABLE `horario_detalle`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=243;

--
-- AUTO_INCREMENT de la tabla `lapsos`
--
ALTER TABLE `lapsos`
  MODIFY `id_lapso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `materias`
--
ALTER TABLE `materias`
  MODIFY `id_materia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `niveles`
--
ALTER TABLE `niveles`
  MODIFY `id_nivel` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `notas_estudiantes`
--
ALTER TABLE `notas_estudiantes`
  MODIFY `id_nota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT de la tabla `periodos_anuales`
--
ALTER TABLE `periodos_anuales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `personas`
--
ALTER TABLE `personas`
  MODIFY `id_persona` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `plantillas_carnet`
--
ALTER TABLE `plantillas_carnet`
  MODIFY `id_plantilla` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `profesores`
--
ALTER TABLE `profesores`
  MODIFY `id_profesor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `profesor_seccion_materia`
--
ALTER TABLE `profesor_seccion_materia`
  MODIFY `id_relacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `project_list`
--
ALTER TABLE `project_list`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `id_reporte` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `representantes`
--
ALTER TABLE `representantes`
  MODIFY `id_representante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `roles_permisos`
--
ALTER TABLE `roles_permisos`
  MODIFY `id_rol_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `secciones`
--
ALTER TABLE `secciones`
  MODIFY `id_seccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT de la tabla `social_media`
--
ALTER TABLE `social_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `solicitudes_constancias`
--
ALTER TABLE `solicitudes_constancias`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `task_list`
--
ALTER TABLE `task_list`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tblcategory`
--
ALTER TABLE `tblcategory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `tblcomments`
--
ALTER TABLE `tblcomments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tblposts`
--
ALTER TABLE `tblposts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `tblsubcategory`
--
ALTER TABLE `tblsubcategory`
  MODIFY `SubCategoryId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipos_constancia`
--
ALTER TABLE `tipos_constancia`
  MODIFY `id_tipo_constancia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `turnos`
--
ALTER TABLE `turnos`
  MODIFY `id_turno` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `user_productivity`
--
ALTER TABLE `user_productivity`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `administrativos`
--
ALTER TABLE `administrativos`
  ADD CONSTRAINT `administrativos_ibfk_1` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id_persona`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `asignaciones_profesor`
--
ALTER TABLE `asignaciones_profesor`
  ADD CONSTRAINT `asignaciones_profesor_ibfk_1` FOREIGN KEY (`id_profesor`) REFERENCES `profesores` (`id_profesor`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `asignaciones_profesor_ibfk_2` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `asignaciones_profesor_ibfk_3` FOREIGN KEY (`id_seccion`) REFERENCES `secciones` (`id_seccion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `asignaciones_profesor_ibfk_4` FOREIGN KEY (`id_gestion`) REFERENCES `gestiones` (`id_gestion`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `chat_reacciones`
--
ALTER TABLE `chat_reacciones`
  ADD CONSTRAINT `chat_reacciones_ibfk_1` FOREIGN KEY (`id_mensaje`) REFERENCES `chat_mensajes` (`id_mensaje`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_reacciones_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `docentes`
--
ALTER TABLE `docentes`
  ADD CONSTRAINT `docentes_ibfk_1` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id_persona`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD CONSTRAINT `estudiantes_ibfk_1` FOREIGN KEY (`id_representante`) REFERENCES `representantes` (`id_representante`);

--
-- Filtros para la tabla `grados_materias`
--
ALTER TABLE `grados_materias`
  ADD CONSTRAINT `grados_materias_ibfk_1` FOREIGN KEY (`id_grado`) REFERENCES `grados` (`id_grado`),
  ADD CONSTRAINT `grados_materias_ibfk_2` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`);

--
-- Filtros para la tabla `historial_cambios_notas`
--
ALTER TABLE `historial_cambios_notas`
  ADD CONSTRAINT `historial_cambios_notas_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id_estudiante`),
  ADD CONSTRAINT `historial_cambios_notas_ibfk_2` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`),
  ADD CONSTRAINT `historial_cambios_notas_ibfk_3` FOREIGN KEY (`id_lapso`) REFERENCES `lapsos` (`id_lapso`),
  ADD CONSTRAINT `historial_cambios_notas_ibfk_4` FOREIGN KEY (`id_profesor`) REFERENCES `profesores` (`id_profesor`);

--
-- Filtros para la tabla `historial_notas`
--
ALTER TABLE `historial_notas`
  ADD CONSTRAINT `historial_notas_ibfk_1` FOREIGN KEY (`id_nota`) REFERENCES `notas_estudiantes` (`id_nota`) ON DELETE CASCADE,
  ADD CONSTRAINT `historial_notas_ibfk_2` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id_estudiante`),
  ADD CONSTRAINT `historial_notas_ibfk_3` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`),
  ADD CONSTRAINT `historial_notas_ibfk_4` FOREIGN KEY (`id_lapso`) REFERENCES `lapsos` (`id_lapso`);

--
-- Filtros para la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD CONSTRAINT `horarios_ibfk_1` FOREIGN KEY (`id_gestion`) REFERENCES `gestiones` (`id_gestion`),
  ADD CONSTRAINT `horarios_ibfk_2` FOREIGN KEY (`id_grado`) REFERENCES `grados` (`id_grado`),
  ADD CONSTRAINT `horarios_ibfk_3` FOREIGN KEY (`id_seccion`) REFERENCES `secciones` (`id_seccion`);

--
-- Filtros para la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD CONSTRAINT `fk_id_seccion` FOREIGN KEY (`id_seccion`) REFERENCES `secciones` (`id_seccion`);

--
-- Filtros para la tabla `lapsos`
--
ALTER TABLE `lapsos`
  ADD CONSTRAINT `lapsos_ibfk_1` FOREIGN KEY (`id_gestion`) REFERENCES `gestiones` (`id_gestion`);

--
-- Filtros para la tabla `niveles`
--
ALTER TABLE `niveles`
  ADD CONSTRAINT `niveles_ibfk_1` FOREIGN KEY (`gestion_id`) REFERENCES `gestiones` (`id_gestion`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notas_estudiantes`
--
ALTER TABLE `notas_estudiantes`
  ADD CONSTRAINT `notas_estudiantes_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id_estudiante`),
  ADD CONSTRAINT `notas_estudiantes_ibfk_2` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`),
  ADD CONSTRAINT `notas_estudiantes_ibfk_3` FOREIGN KEY (`id_lapso`) REFERENCES `lapsos` (`id_lapso`);

--
-- Filtros para la tabla `profesor_seccion_materia`
--
ALTER TABLE `profesor_seccion_materia`
  ADD CONSTRAINT `profesor_seccion_materia_ibfk_1` FOREIGN KEY (`id_profesor`) REFERENCES `profesores` (`id_profesor`),
  ADD CONSTRAINT `profesor_seccion_materia_ibfk_2` FOREIGN KEY (`id_seccion`) REFERENCES `secciones` (`id_seccion`),
  ADD CONSTRAINT `profesor_seccion_materia_ibfk_3` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`),
  ADD CONSTRAINT `profesor_seccion_materia_ibfk_4` FOREIGN KEY (`id_gestion`) REFERENCES `gestiones` (`id_gestion`);

--
-- Filtros para la tabla `secciones`
--
ALTER TABLE `secciones`
  ADD CONSTRAINT `secciones_ibfk_1` FOREIGN KEY (`id_gestion`) REFERENCES `gestiones` (`id_gestion`) ON DELETE CASCADE,
  ADD CONSTRAINT `secciones_ibfk_2` FOREIGN KEY (`id_grado`) REFERENCES `grados` (`id_grado`);

--
-- Filtros para la tabla `tblsubcategory`
--
ALTER TABLE `tblsubcategory`
  ADD CONSTRAINT `tblsubcategory_ibfk_1` FOREIGN KEY (`CategoryId`) REFERENCES `tblcategory` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
