

CREATE TABLE `about_us` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `main_image` varchar(255) NOT NULL,
  `image_alt` varchar(255) NOT NULL,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO about_us VALUES('1','Bienvenidos a Nuestro Portal Escolar','Somos una institución educativa comprometida con la excelencia académica y la formación integral de nuestros estudiantes. Nuestra misión es proporcionar un ambiente de aprendizaje enriquecedor que fomente el crecimiento intelectual, emocional y social.

Contamos con un equipo de educadores altamente calificados y dedicados, que utilizan métodos pedagógicos innovadores para inspirar el amor por el aprendizaje en cada estudiante.

Nuestros valores se centran en el respeto, la responsabilidad, la honestidad y la solidaridad, preparando a nuestros alumnos para los desafíos del futuro.','uploads/about_us/68b0dbab4b189.png','U.E.N ROBERTO MARTINEZ CENTENO','1','2025-10-03 12:01:15','2025-10-12 22:49:54');


CREATE TABLE `academic_page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO academic_page VALUES('1','Excelencia Educativa','<p class=\"lead\">Nuestra institución se enorgullece de ofrecer programas académicos de alta calidad que preparan a los estudiantes para los desafíos del futuro. Contamos con un plan de estudios integral que combina conocimientos teóricos con aplicaciones prácticas.</p><p>Nuestro enfoque pedagógico se centra en el desarrollo integral de los estudiantes, fomentando el pensamiento crítico, la creatividad y los valores éticos. Utilizamos metodologías innovadoras y tecnología educativa para enriquecer el proceso de enseñanza-aprendizaje.</p>','','2025-09-01 20:26:24','2025-09-01 20:26:24');


CREATE TABLE `academic_programs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `level` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `more_info_url` varchar(255) DEFAULT NULL,
  `program_order` int(11) DEFAULT 0,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO academic_programs VALUES('1','Educación Primaria','Ciclo Básico','Programa integral para estudiantes de 6 a 12 años, enfocado en el desarrollo de competencias básicas.','fas fa-pencil-alt','educacion-primaria.php','1','1','2025-09-01 20:26:24','2025-10-12 23:30:57');
INSERT INTO academic_programs VALUES('2','Educación Secundaria','Bachillerato','Formación preparatoria para la educación superior con enfoque en diversas áreas del conocimiento.','fas fa-graduation-cap','educacion-secundaria.php','2','1','2025-09-01 20:26:24','2025-10-12 23:41:55');
INSERT INTO academic_programs VALUES('3','Programas Especiales','Extracurriculares','Actividades complementarias que incluyen arte, deportes, tecnología y liderazgo.','fas fa-star','programas-especiales.php','3','1','2025-09-01 20:26:24','2025-10-12 23:42:05');


CREATE TABLE `academic_resources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO academic_resources VALUES('1','Guías de estudio por materia','Biblioteca','Material de apoyo para todas las materias','#','fas fa-file-pdf','1','2025-09-01 20:26:24','2025-09-01 20:26:24');
INSERT INTO academic_resources VALUES('2','Libros de texto digitales','Biblioteca','Textos oficiales en formato digital','#','fas fa-book','1','2025-09-01 20:26:24','2025-09-01 20:26:24');
INSERT INTO academic_resources VALUES('3','Revistas académicas','Biblioteca','Publicaciones periódicas especializadas','#','fas fa-newspaper','1','2025-09-01 20:26:24','2025-09-01 20:26:24');
INSERT INTO academic_resources VALUES('4','Tutoriales y video-lecciones','Biblioteca','Contenido multimedia educativo','#','fas fa-video','1','2025-09-01 20:26:24','2025-09-01 20:26:24');
INSERT INTO academic_resources VALUES('5','Aula Virtual','Plataformas','Acceso a clases virtuales','#','fas fa-globe','1','2025-09-01 20:26:24','2025-09-01 20:26:24');
INSERT INTO academic_resources VALUES('6','Sistema de Gestión de Aprendizaje','Plataformas','Plataforma principal de estudios','#','fas fa-chalkboard-teacher','1','2025-09-01 20:26:24','2025-09-01 20:26:24');
INSERT INTO academic_resources VALUES('7','Plataforma de Calificaciones','Plataformas','Consulta de calificaciones en línea','#','fas fa-chart-bar','1','2025-09-01 20:26:24','2025-09-01 20:26:24');
INSERT INTO academic_resources VALUES('8','Foros de Discusión','Plataformas','Espacio para debates académicos','#','fas fa-comments','1','2025-09-01 20:26:24','2025-09-01 20:26:24');


CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` text NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `administrativos` (
  `id_administrativo` int(11) NOT NULL AUTO_INCREMENT,
  `persona_id` int(11) NOT NULL,
  `fyh_creacion` datetime DEFAULT NULL,
  `fyh_actualizacion` datetime DEFAULT NULL,
  `estado` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id_administrativo`),
  KEY `persona_id` (`persona_id`),
  CONSTRAINT `administrativos_ibfk_1` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id_persona`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO administrativos VALUES('7','28','2025-10-31 00:00:00','2025-11-04 00:00:00','1');
INSERT INTO administrativos VALUES('8','29','2025-10-31 00:00:00','2025-11-03 00:00:00','1');


CREATE TABLE `asignaciones_profesor` (
  `id_asignacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_profesor` int(11) NOT NULL,
  `id_materia` int(11) NOT NULL,
  `id_seccion` int(11) NOT NULL,
  `id_gestion` int(11) NOT NULL,
  `estado` tinyint(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_asignacion`),
  UNIQUE KEY `uk_asignacion` (`id_profesor`,`id_materia`,`id_seccion`,`id_gestion`),
  KEY `id_materia` (`id_materia`),
  KEY `id_seccion` (`id_seccion`),
  KEY `id_gestion` (`id_gestion`),
  CONSTRAINT `asignaciones_profesor_ibfk_1` FOREIGN KEY (`id_profesor`) REFERENCES `profesores` (`id_profesor`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `asignaciones_profesor_ibfk_2` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `asignaciones_profesor_ibfk_3` FOREIGN KEY (`id_seccion`) REFERENCES `secciones` (`id_seccion`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `asignaciones_profesor_ibfk_4` FOREIGN KEY (`id_gestion`) REFERENCES `gestiones` (`id_gestion`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO asignaciones_profesor VALUES('52','3','3','98','34','1','2025-10-31 17:42:12','2025-11-03 18:52:56');
INSERT INTO asignaciones_profesor VALUES('53','3','5','97','34','1','2025-10-31 17:53:40','2025-10-31 17:53:40');
INSERT INTO asignaciones_profesor VALUES('54','3','3','100','34','1','2025-10-31 17:55:51','2025-10-31 17:55:51');
INSERT INTO asignaciones_profesor VALUES('55','3','4','100','34','1','2025-10-31 18:07:50','2025-10-31 18:07:50');
INSERT INTO asignaciones_profesor VALUES('56','3','3','99','34','1','2025-10-31 18:17:44','2025-10-31 18:17:44');
INSERT INTO asignaciones_profesor VALUES('57','3','6','97','34','1','2025-10-31 18:18:22','2025-10-31 18:18:22');
INSERT INTO asignaciones_profesor VALUES('58','3','1','100','34','1','2025-10-31 19:40:01','2025-10-31 19:40:01');
INSERT INTO asignaciones_profesor VALUES('59','4','3','98','34','1','2025-11-01 01:50:30','2025-11-01 01:50:30');


CREATE TABLE `calendario_academico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evento` varchar(255) NOT NULL,
  `tipo_evento` enum('inscripcion','inicio_clases','fin_lapso','vacaciones','evaluacion','otro') NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `nivel_educativo` enum('inicial','primaria','secundaria','media','todos') DEFAULT 'todos',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO calendario_academico VALUES('1','Inscripciones para Educación Inicial','inscripcion','2024-09-02','2024-09-13','Período de inscripción para estudiantes de educación inicial','inicial','1','2025-09-01 20:55:31','2025-09-01 20:55:31');
INSERT INTO calendario_academico VALUES('2','Inscripciones para Educación Primaria','inscripcion','2024-09-02','2024-09-13','Período de inscripción para estudiantes de educación primaria','primaria','1','2025-09-01 20:55:31','2025-09-01 20:55:31');
INSERT INTO calendario_academico VALUES('3','Inscripciones para Educación Media','inscripcion','2024-09-02','2024-09-13','Período de inscripción para estudiantes de educación media','media','1','2025-09-01 20:55:31','2025-09-01 20:55:31');
INSERT INTO calendario_academico VALUES('4','Inicio del Año Escolar','inicio_clases','2024-09-16','','Inicio del año escolar 2024-2025 en todo el país','todos','1','2025-09-01 20:55:31','2025-09-01 20:55:31');
INSERT INTO calendario_academico VALUES('5','Finalización del Primer Lapso','fin_lapso','2024-12-13','','Culminación del primer lapso del año escolar','todos','1','2025-09-01 20:55:31','2025-09-01 20:55:31');
INSERT INTO calendario_academico VALUES('6','Inicio del Segundo Lapso','inicio_clases','2025-01-06','','Inicio del segundo lapso del año escolar','todos','1','2025-09-01 20:55:31','2025-09-01 20:55:31');
INSERT INTO calendario_academico VALUES('7','Finalización del Segundo Lapso','fin_lapso','2025-03-28','','Culminación del segundo lapso del año escolar','todos','1','2025-09-01 20:55:31','2025-09-01 20:55:31');
INSERT INTO calendario_academico VALUES('8','Inicio del Tercer Lapso','inicio_clases','2025-04-07','','Inicio del tercer lapso del año escolar','todos','1','2025-09-01 20:55:31','2025-09-01 20:55:31');
INSERT INTO calendario_academico VALUES('9','Finalización del Tercer Lapso','fin_lapso','2025-07-11','','Culminación del tercer lapso y fin del año escolar','todos','1','2025-09-01 20:55:31','2025-09-01 20:55:31');
INSERT INTO calendario_academico VALUES('10','Vacaciones de Navidad','vacaciones','2024-12-16','2025-01-05','Periodo vacacional de navidad y año nuevo','todos','1','2025-09-01 20:55:31','2025-09-01 20:55:31');
INSERT INTO calendario_academico VALUES('11','Vacaciones de Semana Santa','vacaciones','2025-04-14','2025-04-20','Periodo vacacional de semana santa','todos','1','2025-09-01 20:55:31','2025-09-01 20:55:31');
INSERT INTO calendario_academico VALUES('12','Evaluaciones Primer Lapso','evaluacion','2024-12-02','2024-12-12','Periodo de evaluaciones del primer lapso','todos','1','2025-09-01 20:55:31','2025-09-01 20:55:31');
INSERT INTO calendario_academico VALUES('13','Evaluaciones Segundo Lapso','evaluacion','2025-03-17','2025-03-27','Periodo de evaluaciones del segundo lapso','todos','1','2025-09-01 20:55:31','2025-09-01 20:55:31');
INSERT INTO calendario_academico VALUES('14','Evaluaciones Tercer Lapso','evaluacion','2025-06-30','2025-07-10','Periodo de evaluaciones del tercer lapso','todos','1','2025-09-01 20:55:31','2025-09-01 20:55:31');


CREATE TABLE `carnets_emitidos` (
  `id_emision` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `id_diseno` int(11) NOT NULL,
  `fecha_emision` date NOT NULL,
  `fecha_expiracion` date NOT NULL,
  `codigo_qr` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



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



CREATE TABLE `carrusel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen_path` varchar(255) NOT NULL DEFAULT 'default.png',
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO carrusel VALUES('1','Bienvenido al Portal Escolar','','1.png','2025-10-12','2026-07-24','1','2025-10-12 22:27:15');


CREATE TABLE `chat_conexiones` (
  `id_conexion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `socket_id` varchar(255) DEFAULT NULL,
  `ultima_conexion` datetime DEFAULT current_timestamp(),
  `estado` enum('online','offline') DEFAULT 'online'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `chat_mensajes` (
  `id_mensaje` int(11) NOT NULL AUTO_INCREMENT,
  `id_remitente` int(11) NOT NULL,
  `id_destinatario` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `archivo` varchar(255) DEFAULT NULL,
  `fecha_envio` datetime DEFAULT current_timestamp(),
  `leido` tinyint(1) DEFAULT 0,
  `estado` tinyint(1) DEFAULT 1,
  `editado` enum('0','1') DEFAULT '0',
  `reacciones` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`reacciones`)),
  `fecha_edicion` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_mensaje`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO chat_mensajes VALUES('1','1','75','Hola Buenas noches','','2025-10-14 22:06:39','1','1','0','','');
INSERT INTO chat_mensajes VALUES('2','75','1','Buenas noches administrador como esta','','2025-10-14 22:10:05','1','1','0','','');
INSERT INTO chat_mensajes VALUES('3','1','75','','app/uploads/1_20251014_222135_85a4abcb.png','2025-10-14 22:21:35','1','1','0','','');
INSERT INTO chat_mensajes VALUES('4','1','75','','app/uploads/agustinzamora_20251016_160218_98d8f0f5.jpg','2025-10-16 16:02:18','1','1','0','[]','');
INSERT INTO chat_mensajes VALUES('5','1','75','Hola','','2025-10-16 16:03:05','1','1','0','[]','');
INSERT INTO chat_mensajes VALUES('6','1','75','hola','','2025-10-16 16:03:17','1','1','0','[]','');
INSERT INTO chat_mensajes VALUES('7','1','75','holaaa','','2025-10-16 16:04:54','1','1','0','[]','');
INSERT INTO chat_mensajes VALUES('8','1','75','','app/uploads/673cb963-e1e1-4996-97bb-5897de8c55d3_20251016_160517_d65b98de.jfif','2025-10-16 16:05:17','1','1','0','[]','');
INSERT INTO chat_mensajes VALUES('9','76','1','hola','','2025-11-03 13:17:05','1','1','0','[]','');
INSERT INTO chat_mensajes VALUES('10','76','1','','app/uploads/escudo_20251103_131745_fb07eacf.jfif','2025-11-03 13:17:45','1','1','0','[]','');


CREATE TABLE `chat_reacciones` (
  `id_reaccion` int(11) NOT NULL AUTO_INCREMENT,
  `id_mensaje` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo_reaccion` enum('like','love','haha','wow','sad','angry') NOT NULL,
  `fecha_reaccion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_reaccion`),
  UNIQUE KEY `unique_reaccion` (`id_mensaje`,`id_usuario`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `chat_reacciones_ibfk_1` FOREIGN KEY (`id_mensaje`) REFERENCES `chat_mensajes` (`id_mensaje`) ON DELETE CASCADE,
  CONSTRAINT `chat_reacciones_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `citas_nacimiento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_completo` varchar(255) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `fecha_cita` date NOT NULL,
  `hora_cita` time NOT NULL,
  `confirmacion_docs` tinyint(1) NOT NULL,
  `estado` enum('pendiente','confirmada','completada','cancelada') DEFAULT 'pendiente',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `codigo_confirmacion` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cita` (`fecha_cita`,`hora_cita`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO citas_nacimiento VALUES('1','Daniel Eduardo Villanueva Quintero','V-29720599','04164634936','dv47762@gmail.com','2025-08-29','14:00:00','1','pendiente','2025-08-28 12:04:55','2SE7MR');
INSERT INTO citas_nacimiento VALUES('2','Daniela Alejandra Villanueva Quintero','V-20720599','04164634936','dv47762@gmail.com','2025-08-29','16:00:00','1','pendiente','2025-08-28 15:31:51','37F0M8');


CREATE TABLE `collaborators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO collaborators VALUES('1','El Ministerio del Poder Popular para la Educación','uploads/collaborators/collaborators_1756346146_68afb722a3145.png','https://www.mppe.gob.ve/','1','2025-10-03 11:53:16','2025-10-03 11:53:16');


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



CREATE TABLE `configuracion_instituciones` (
  `id_config_institucion` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_institucion` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `fondo` varchar(100) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `telefono` varchar(100) DEFAULT NULL,
  `celular` varchar(100) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `fyh_creacion` datetime DEFAULT NULL,
  `fyh_actualizacion` datetime DEFAULT NULL,
  `estado` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id_config_institucion`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO configuracion_instituciones VALUES('1','U.E.N ROBERTO MARTINEZ CENTENO','2025-11-04-10-15-37logo.png','','Parroquia Caricuao, Avenida Este 0, Caracas, Distrito Capital, adscrito a la Zona Educativa del Estado Distrito Capital','02124331080','','admin@gmail.com','2023-12-28 20:29:10','2025-11-04 00:00:00','1');


CREATE TABLE `contactos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `estado` varchar(100) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `comentarios` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `datos_institucion` (
  `id_institucion` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_institucion` varchar(100) NOT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `sitio_web` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_institucion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `diseno_carnets` (
  `id_diseno` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_diseno`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO diseno_carnets VALUES('1','Predeterminado','','10','10','30','30','15','50','25','30','60','50','25','#FFFFFF','#000000','Arial','1','Universidad Nacional Experimental|de los Llanos Occidentales|Ezequiel Zamora|UNELLEZ','Credencial Estudiantil|ViceRectorado de Producción Agrícola|Carnet válido hasta: {fecha_expiracion}','','50','80','30','15','1','2025-05-11 21:31:00','2025-05-11 21:31:00');


CREATE TABLE `docentes` (
  `id_docente` int(11) NOT NULL AUTO_INCREMENT,
  `persona_id` int(11) NOT NULL,
  `especialidad` varchar(255) NOT NULL,
  `antiguedad` varchar(255) NOT NULL,
  `fyh_creacion` date DEFAULT NULL,
  `fyh_actualizacion` date DEFAULT NULL,
  `estado` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id_docente`),
  KEY `persona_id` (`persona_id`),
  CONSTRAINT `docentes_ibfk_1` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id_persona`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `documento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(250) NOT NULL,
  `archivo` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `documentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO documentos VALUES('1','Línea Presión Agua Fría','PDF','Ideal para sistemas de agua potable en cualquier tipo de edificación, sistemas para piscinas, sistemas de agua helada (aire acondicionado) y riego. No transmite ni olor ni sabor al agua o fluido que por ella circula. Es inmune a la corrosión, no se oxida.','68b47ed82e9c8_1756659416.pdf','','218072','Línea Presión Agua Fría','2025-08-31 12:57:04','1','0','1');


CREATE TABLE `documentos_internos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO documentos_internos VALUES('1','Línea Presión Agua Fría','PDF','Ideal para sistemas de agua potable en cualquier tipo de edificación, sistemas para piscinas, sistemas de agua helada (aire acondicionado) y riego. No transmite ni olor ni sabor al agua o fluido que por ella circula. Es inmune a la corrosión, no se oxida.','68b47ed82e9c8_1756659416.pdf','','218072','','2025-08-31 12:56:56','1','0','');


CREATE TABLE `estudiantes` (
  `id_estudiante` int(11) NOT NULL AUTO_INCREMENT,
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
  `foto` varchar(255) NOT NULL DEFAULT 'no-image-available.png',
  PRIMARY KEY (`id_estudiante`),
  KEY `id_representante` (`id_representante`),
  KEY `turno_id` (`turno_id`),
  CONSTRAINT `estudiantes_ibfk_1` FOREIGN KEY (`id_representante`) REFERENCES `representantes` (`id_representante`)
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO estudiantes VALUES('1','V','30045678','','0','Carlos Eduardo','Pérez López','2015-05-10','masculino','carloseduardo15@gmail.com','San Juan de Los Morros','0412-1234567','1','1','inactivo','2025-01-24 08:27:15','2025-01-24 21:57:29','ninguna','');
INSERT INTO estudiantes VALUES('2','V','30045679','','0','Ana Lucía','Pérez López','2016-06-15','femenino','analucia15@gmail.com','El Junquito','0414-1234568','1','1','inactivo','2025-01-24 08:27:15','2025-01-24 21:57:56','ninguna','');
INSERT INTO estudiantes VALUES('3','V','30045680','','0','Luis Miguel','Pérez López','2017-07-20','masculino','luismiguel15@gmail.com','La Candelaria','0416-1234569','1','1','activo','2025-01-24 08:27:15','2025-11-04 14:32:55','ninguna','1760629380_images.jpeg');
INSERT INTO estudiantes VALUES('4','V','31234567','','0','María Fernanda','González Torres','2015-08-25','femenino','mariafernanda31@gmail.com','Santa Teresa','0424-1234570','2','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('5','V','31234568','','0','Diego Alejandro','González Torres','2016-09-30','masculino','diegoalejandro31@gmail.com','Los Palos Grandes','04164634936','2','1','activo','2025-01-24 08:27:15','2025-02-07 23:28:44','ninguna','');
INSERT INTO estudiantes VALUES('6','V','31234569','','0','Sofía Alejandra','González Torres','2017-10-10','femenino','sofiaalejandra31@gmail.com','El Hatillo','0412-1234572','2','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('7','V','32135798','','0','Javier Alejandro','Martínez Ruiz','2015-11-10','masculino','javieralejandro32@gmail.com','Caricuao','0414-1234580','3','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('8','V','32135799','','0','Lucía Fernanda','Martínez Ruiz','2016-12-15','femenino','luciafernanda32@gmail.com','Los Rosales','0416-1234581','3','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('9','V','32135800','','0','María José','Martínez Ruiz','2017-01-20','femenino','mariajose32@gmail.com','Coche','0424-1234582','3','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('10','V','33345678','','0','Carlos Andrés','Díaz López','2015-05-30','masculino','carlosandres34@gmail.com','La Vega','0426-1234590','4','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('11','V','33345679','','0','Isabella','Díaz López','2016-06-25','femenino','isabelladiaz34@gmail.com','Los Teques','0412-1234591','4','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('12','V','33345680','','0','Santiago','Díaz López','2017-07-15','masculino','santiagodiaz34@gmail.com','Chacao','0414-1234592','4','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('13','V','30167891','','0','Valentina','Hernández García','2015-08-25','femenino','valentinahernandez30@gmail.com','Los Dos Caminos','0416-1234593','5','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('14','V','30167892','','0','Fernando','Hernández García','2016-09-20','masculino','fernandohernandez30@gmail.com','El Paraíso','0424-1234501','5','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('15','V','30167893','','0','Gabriel','Hernández García','2017-10-15','masculino','gabrielhernandez30@gmail.com','Las Mercedes','0426-1234502','5','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('16','V','31890123','','0','Mateo','Ramírez Fernández','2015-11-30','masculino','mateoramirez31@gmail.com','Sabana Grande','0412-1234503','6','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('17','V','31890124','','0','Camila','Ramírez Fernández','2016-12-31','femenino','camilaramirez31@gmail.com','Los Chaguaramos','0414-1234504','6','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('18','V','31890125','','0','Diego','Ramírez Fernández','2017-01-15','masculino','diegoramirez31@gmail.com','Catia','0416-1234505','6','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('19','V','32678910','','0','Camilo','Morales López','2015-02-25','masculino','camilomorales32@gmail.com','La Urbina','0424-1234506','7','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('20','V','32678911','','0','Natalia','Morales López','2016-03-16','femenino','nataliamorales32@gmail.com','Boleíta','0426-1234507','7','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('21','V','32678912','','0','Arturo','Morales López','2017-04-17','masculino','arturomorales32@gmail.com','El Cafetal','0412-1234508','7','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('22','V','33789012','','0','Leo','Ortega Medina','2015-05-18','masculino','leootegamedina33@gmail.com','Catedral','0414-1234509','8','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('23','V','33789013','','0','Valeria','Ortega Medina','2016-06-29','femenino','valeriaortegamedina33@gmail.com','Calle Real','0416-1234510','8','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('24','V','33789014','','0','Esteban','Ortega Medina','2017-07-10','masculino','estebanortegamedina33@gmail.com','San Bernardino','0424-1234511','8','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('25','V','30231456','','0','Claudia','Chapman Ruiz','2015-08-26','femenino','claudiachapman30@gmail.com','Los Palos Grandes','0426-1234512','9','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('26','V','30231457','','0','Felipe','Chapman Ruiz','2016-09-12','masculino','felipechapman30@gmail.com','Plaza Venezuela','0412-1234513','9','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('27','V','30231458','','0','Juan','Chapman Ruiz','2017-10-05','masculino','juanchapman30@gmail.com','Miranda','0414-1234514','9','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('28','V','31567890','','0','Simón','Salazar Pérez','2015-11-11','masculino','simonsalazar31@gmail.com','Catia La Mar','0416-1234515','10','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('29','V','31567891','','0','María','Salazar Pérez','2016-12-12','femenino','mariasalazar31@gmail.com','Tarqui','0424-1234516','10','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('30','V','31567892','','0','Leonardo','Salazar Pérez','2017-01-13','masculino','leonardosalazar31@gmail.com','La Grita','0426-1234517','10','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('31','V','32987654','','0','Estefanía','Carrillo Martínez','2015-02-14','femenino','estefaniacarrillo32@gmail.com','El Valle','0412-1234518','11','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('32','V','32987655','','0','Diego','Carrillo Martínez','2016-03-15','masculino','diegocarrillo32@gmail.com','La Bandera','0414-1234519','11','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('33','V','32987656','','0','Gabriela','Carrillo Martínez','2017-04-16','femenino','gabrielacarrillo32@gmail.com','Antímano','0416-1234520','11','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('34','V','33555555','','0','Pablo','García López','2015-02-14','masculino','pablogarcia33@gmail.com','Río de Janeiro','0412-1234571','12','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('35','V','33555556','','0','Laura','García López','2016-03-15','femenino','lauragarcia33@gmail.com','Avenida Bolívar','0414-1234572','12','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('36','V','33555557','','0','Ricardo','García López','2017-04-16','masculino','ricardogarcia33@gmail.com','Bello Campo','0416-1234573','12','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('37','V','30654321','','0','Martina','Blanco Rodríguez','2015-05-18','femenino','martinablancor33@gmail.com','La Yaguara','0424-1234581','13','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('38','V','30654322','','0','Santiago','Blanco Rodríguez','2016-06-21','masculino','santiagoblanco33@gmail.com','Tamanaco','0426-1234582','13','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('39','V','30654323','','0','Gabriela','Blanco Rodríguez','2017-07-24','femenino','gabrielablanco33@gmail.com','Montalbán','0412-1234583','13','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('40','V','31112233','','0','Fernando','Castillo Mendoza','2015-08-26','masculino','fernandocastillo33@gmail.com','Las Acacias','0414-1234591','14','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('41','V','31112234','','0','Maria','Castillo Mendoza','2016-09-27','femenino','mariacastillo33@gmail.com','Palo Verde','0416-1234592','14','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('42','V','31112235','','0','Javier','Castillo Mendoza','2017-10-18','masculino','javiercastillo33@gmail.com','Cerro Verde','0424-1234593','14','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('43','V','32443322','','0','Esteban','Rivas Araujo','2015-11-30','masculino','estebanrivas33@gmail.com','Los Teques','0426-1234501','15','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('44','V','32443323','','0','Anabella','Rivas Araujo','2016-12-12','femenino','anabellarivas33@gmail.com','Baruta','0412-1234502','15','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('45','V','32443324','','0','Joaquín','Rivas Araujo','2017-01-18','masculino','joaquinrivas33@gmail.com','Guarenas','0414-1234503','15','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('46','V','33334455','','0','Marisol','Soto Castillo','2015-02-17','femenino','marisolsotoc33@gmail.com','Santa Fe','0416-1234501','16','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('47','V','33334456','','0','Ramón','Soto Castillo','2016-03-31','masculino','ramonsotoc33@gmail.com','La Trinidad','0424-1234502','16','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('48','V','33334457','','0','Virginia','Soto Castillo','2017-05-18','femenino','virginiasotoc33@gmail.com','La Candelaria','0426-1234503','16','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('49','V','30112233','','0','Óscar','Vásquez Pérez','2015-06-30','masculino','oscargasquez33@gmail.com','Peñalver','0412-1234501','17','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('50','V','30112234','','0','Evelyn','Vásquez Pérez','2016-07-14','femenino','evelynvasquez33@gmail.com','Antímano','0414-1234502','17','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('51','V','30112235','','0','Mateo','Vásquez Pérez','2017-08-19','masculino','mateovasquez33@gmail.com','Cantaura','0416-1234503','17','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna','');
INSERT INTO estudiantes VALUES('56','V','33200918','','0','Misael David','Marquez Cruz','2019-05-24','masculino','misaelmarquez@gmail.com','Parroquia Caricuao Ud1','04121988817','113','0','activo','2025-01-24 07:23:20','2025-01-24 07:23:20','ninguna','');
INSERT INTO estudiantes VALUES('61','V','33200919','','0','Juan Carlos','Pérez López','2018-04-15','masculino','juancarlos@gmail.com','Parroquia Caricuao Ud1','04121234567','113','0','activo','2025-01-24 07:30:25','2025-01-24 07:30:25','ninguna','');
INSERT INTO estudiantes VALUES('62','V','33200920','','0','Ana María','González Torres','2017-03-10','femenino','anagonzalez@gmail.com','Parroquia Caricuao Ud1','04121234568','113','0','activo','2025-01-24 07:30:25','2025-01-24 07:30:25','ninguna','');
INSERT INTO estudiantes VALUES('63','V','33200921','','0','Luis Fernando','Martínez Ruiz','2016-02-20','masculino','luismartinez@gmail.com','Parroquia Caricuao Ud1','04121234569','113','0','activo','2025-01-24 07:30:25','2025-01-24 07:30:25','ninguna','');
INSERT INTO estudiantes VALUES('64','V','','V21914756124','2','Sofía Isabel','Ramírez López','2019-01-15','femenino','sofiaramirez@gmail.com','Parroquia Caricuao Ud1','04121234570','113','0','activo','2025-01-24 07:30:25','2025-01-24 07:33:09','ninguna','');
INSERT INTO estudiantes VALUES('65','V','33200922','','0','Carlos Alberto','Hernández Pérez','2018-05-10','masculino','carloshp@gmail.com','Parroquia Caricuao Ud1','04121234571','105','0','activo','2025-01-24 07:47:17','2025-01-24 07:47:17','ninguna','');
INSERT INTO estudiantes VALUES('66','V','33200923','','0','María José','López García','2017-06-15','femenino','mariajose@gmail.com','Parroquia Caricuao Ud1','04121234572','105','0','activo','2025-01-24 07:47:17','2025-01-24 07:47:17','ninguna','');
INSERT INTO estudiantes VALUES('67','V','33200924','','0','Andrés Felipe','Martínez Torres','2016-07-20','masculino','andresfelipe@gmail.com','Parroquia Caricuao Ud1','04121234573','105','0','activo','2025-01-24 07:47:17','2025-01-24 07:47:17','ninguna','');
INSERT INTO estudiantes VALUES('68','V','33200925','','0','Isabella','Ramírez López','2019-08-25','femenino','isabellar@gmail.com','Parroquia Caricuao Ud1','04121234574','105','0','activo','2025-01-24 07:47:17','2025-01-24 07:47:17','ninguna','');
INSERT INTO estudiantes VALUES('69','V','33200926','','0','Diego Alejandro','González Ruiz','2015-09-30','masculino','diegoalejandro@gmail.com','Parroquia Caricuao Ud1','04121234575','105','0','activo','2025-01-24 07:47:17','2025-01-24 07:47:17','ninguna','');
INSERT INTO estudiantes VALUES('75','V','31982330','','0','Fernando José','Pérez Martínez','2018-01-10','masculino','fernandoj@gmail.com','Parroquia Caricuao Ud1','04121234581','114','0','activo','2025-01-24 07:54:21','2025-01-24 07:54:21','ninguna','');
INSERT INTO estudiantes VALUES('76','V','31982331','','0','Lucía Fernanda','González Torres','2017-02-15','femenino','luciafernanda@gmail.com','Parroquia Caricuao Ud1','04121234582','114','0','activo','2025-01-24 07:54:21','2025-01-24 07:54:21','ninguna','');
INSERT INTO estudiantes VALUES('77','V','31982332','','0','Javier Alejandro','Martínez López','2016-03-20','masculino','javieralejandro@gmail.com','Parroquia Caricuao Ud1','04121234583','114','0','activo','2025-01-24 07:54:21','2025-01-24 07:54:21','ninguna','');
INSERT INTO estudiantes VALUES('78','V','','V21911985583','2','Sofía Valentina','Ramírez Pérez','2019-04-25','femenino','sofiavalentina@gmail.com','Parroquia Caricuao Ud1','04121234584','114','0','activo','2025-01-24 07:54:21','2025-01-24 07:54:38','ninguna','');
INSERT INTO estudiantes VALUES('79','V','','V11511985583','1','Diego Armando','Hernández Ruiz','2015-05-30','masculino','diegoarmando@gmail.com','Parroquia Caricuao Ud1','04121234585','114','0','activo','2025-01-24 07:54:21','2025-01-24 07:55:23','ninguna','');
INSERT INTO estudiantes VALUES('80','V','30652798','','0','Daniela Alejandra','Gimenez Delgado','2015-03-18','femenino','danielag2009@gmail.com','Caracas','04164564199','1','0','activo','2025-08-25 15:44:55','2025-08-25 15:44:55','ninguna','');
INSERT INTO estudiantes VALUES('81','V','','V42014023560','4','Anderson Andres','Lopez Delgado','2020-01-01','masculino','migueljoselopez@gmail.com','Caracas','04164564199','1','0','activo','2025-08-25 15:50:34','2025-08-25 15:50:34','ninguna','');


CREATE TABLE `gestiones` (
  `id_gestion` int(11) NOT NULL AUTO_INCREMENT,
  `desde` date NOT NULL,
  `hasta` date NOT NULL,
  `fyh_creacion` date DEFAULT NULL,
  `fyh_actualizacion` date DEFAULT NULL,
  `estado` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id_gestion`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO gestiones VALUES('1','2026-07-08','2027-06-15','2023-12-28','2025-10-21','0');
INSERT INTO gestiones VALUES('33','2025-10-14','2026-10-05','2025-10-16','2025-10-21','0');
INSERT INTO gestiones VALUES('34','2025-10-31','2026-09-14','2025-10-31','','1');


CREATE TABLE `grados` (
  `id_grado` int(11) NOT NULL AUTO_INCREMENT,
  `nivel` varchar(20) NOT NULL,
  `grado` varchar(20) NOT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT 1,
  `fyh_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `trayecto` varchar(20) NOT NULL,
  `trimestre` varchar(20) NOT NULL,
  PRIMARY KEY (`id_grado`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO grados VALUES('50','Secundaria','PRIMER AÑO','1','2025-10-31 17:24:09','','');
INSERT INTO grados VALUES('51','Secundaria','SEGUNDO AÑO','1','2025-10-31 17:24:37','','');


CREATE TABLE `grados_materias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_grado` int(11) NOT NULL,
  `id_materia` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_grado` (`id_grado`),
  KEY `id_materia` (`id_materia`),
  CONSTRAINT `grados_materias_ibfk_1` FOREIGN KEY (`id_grado`) REFERENCES `grados` (`id_grado`),
  CONSTRAINT `grados_materias_ibfk_2` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `historial_cambios_notas` (
  `id_historial` int(11) NOT NULL AUTO_INCREMENT,
  `id_nota` int(11) DEFAULT NULL,
  `id_estudiante` int(11) DEFAULT NULL,
  `id_materia` int(11) DEFAULT NULL,
  `id_lapso` int(11) DEFAULT NULL,
  `nota_anterior` decimal(4,2) DEFAULT NULL,
  `nota_nueva` decimal(4,2) DEFAULT NULL,
  `id_profesor` int(11) DEFAULT NULL,
  `fecha_cambio` timestamp NOT NULL DEFAULT current_timestamp(),
  `motivo_cambio` text DEFAULT NULL,
  `ip_cambio` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id_historial`),
  KEY `id_estudiante` (`id_estudiante`),
  KEY `id_materia` (`id_materia`),
  KEY `id_lapso` (`id_lapso`),
  KEY `id_profesor` (`id_profesor`),
  CONSTRAINT `historial_cambios_notas_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id_estudiante`),
  CONSTRAINT `historial_cambios_notas_ibfk_2` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`),
  CONSTRAINT `historial_cambios_notas_ibfk_3` FOREIGN KEY (`id_lapso`) REFERENCES `lapsos` (`id_lapso`),
  CONSTRAINT `historial_cambios_notas_ibfk_4` FOREIGN KEY (`id_profesor`) REFERENCES `profesores` (`id_profesor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `historial_notas` (
  `id_historial` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
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
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_historial`),
  KEY `id_nota` (`id_nota`),
  KEY `id_estudiante` (`id_estudiante`),
  KEY `id_materia` (`id_materia`),
  KEY `id_lapso` (`id_lapso`),
  KEY `usuario_cambio` (`usuario_cambio`),
  CONSTRAINT `historial_notas_ibfk_1` FOREIGN KEY (`id_nota`) REFERENCES `notas_estudiantes` (`id_nota`) ON DELETE CASCADE,
  CONSTRAINT `historial_notas_ibfk_2` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id_estudiante`),
  CONSTRAINT `historial_notas_ibfk_3` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`),
  CONSTRAINT `historial_notas_ibfk_4` FOREIGN KEY (`id_lapso`) REFERENCES `lapsos` (`id_lapso`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO historial_notas VALUES('86','54','10','4','4','10.00','20.00','Error de tipificacion','Error de tipificacion','2025-10-21 15:43:25','Heldyn David Diaz Daboin','ACTUALIZACION','1','2025-10-21 15:43:25','2025-10-21 15:43:25');
INSERT INTO historial_notas VALUES('87','57','19','5','4','','20.00','','','2025-10-21 20:13:29','Heldyn David Diaz Daboin','CREACION','1','2025-10-21 20:13:29','2025-10-21 20:13:29');
INSERT INTO historial_notas VALUES('88','58','20','5','4','','20.00','','','2025-10-21 20:13:29','Heldyn David Diaz Daboin','CREACION','1','2025-10-21 20:13:29','2025-10-21 20:13:29');
INSERT INTO historial_notas VALUES('89','52','19','3','4','14.00','18.00','error','','2025-10-21 20:15:45','Heldyn David Diaz Daboin','ACTUALIZACION','1','2025-10-21 20:15:45','2025-10-21 20:15:45');
INSERT INTO historial_notas VALUES('90','52','19','3','4','18.00','20.00','','Error de tipificacion','2025-10-21 20:17:54','Heldyn David Diaz Daboin','ACTUALIZACION','1','2025-10-21 20:17:54','2025-10-21 20:17:54');
INSERT INTO historial_notas VALUES('91','59','3','3','6','','20.00','','','2025-10-31 23:49:11','Heldyn David Diaz Daboin','CREACION','1','2025-10-31 23:49:11','2025-10-31 23:49:11');
INSERT INTO historial_notas VALUES('92','59','3','3','6','20.00','0.00','','errorrr','2025-10-31 23:50:30','Heldyn David Diaz Daboin','ACTUALIZACION','1','2025-10-31 23:50:30','2025-10-31 23:50:30');
INSERT INTO historial_notas VALUES('93','59','3','3','6','0.00','20.00','errorrr','error','2025-11-01 00:07:00','Heldyn David Diaz Daboin','ACTUALIZACION','1','2025-11-01 00:07:00','2025-11-01 00:07:00');
INSERT INTO historial_notas VALUES('94','60','3','3','7','','15.00','','','2025-11-01 00:07:13','Heldyn David Diaz Daboin','CREACION','1','2025-11-01 00:07:13','2025-11-01 00:07:13');
INSERT INTO historial_notas VALUES('95','61','3','3','8','','18.00','','','2025-11-01 00:07:23','Heldyn David Diaz Daboin','CREACION','1','2025-11-01 00:07:23','2025-11-01 00:07:23');
INSERT INTO historial_notas VALUES('96','62','3','4','8','','15.00','','','2025-11-01 00:07:33','Heldyn David Diaz Daboin','CREACION','1','2025-11-01 00:07:33','2025-11-01 00:07:33');
INSERT INTO historial_notas VALUES('97','63','3','4','7','','19.00','','','2025-11-01 00:07:46','Heldyn David Diaz Daboin','CREACION','1','2025-11-01 00:07:46','2025-11-01 00:07:46');
INSERT INTO historial_notas VALUES('98','64','3','4','6','','18.00','','','2025-11-01 00:07:57','Heldyn David Diaz Daboin','CREACION','1','2025-11-01 00:07:57','2025-11-01 00:07:57');
INSERT INTO historial_notas VALUES('99','65','3','1','6','','16.00','','','2025-11-01 00:08:09','Heldyn David Diaz Daboin','CREACION','1','2025-11-01 00:08:09','2025-11-01 00:08:09');
INSERT INTO historial_notas VALUES('100','66','3','1','7','','20.00','','','2025-11-01 00:08:18','Heldyn David Diaz Daboin','CREACION','1','2025-11-01 00:08:18','2025-11-01 00:08:18');
INSERT INTO historial_notas VALUES('101','67','3','1','8','','2.00','','','2025-11-01 00:08:26','Heldyn David Diaz Daboin','CREACION','1','2025-11-01 00:08:26','2025-11-01 00:08:26');
INSERT INTO historial_notas VALUES('102','68','10','3','6','','15.00','','','2025-11-01 01:52:42','Saned Arya Diaz Daboin','CREACION','1','2025-11-01 01:52:42','2025-11-01 01:52:42');
INSERT INTO historial_notas VALUES('103','69','80','3','6','','20.00','','','2025-11-02 20:12:05','Heldyn David Diaz Daboin','CREACION','1','2025-11-02 20:12:05','2025-11-02 20:12:05');
INSERT INTO historial_notas VALUES('104','59','3','3','6','20.00','20.00','error','','2025-11-02 20:12:05','Heldyn David Diaz Daboin','ACTUALIZACION','1','2025-11-02 20:12:05','2025-11-02 20:12:05');
INSERT INTO historial_notas VALUES('105','70','80','4','6','','20.00','','','2025-11-02 20:12:41','Heldyn David Diaz Daboin','CREACION','1','2025-11-02 20:12:41','2025-11-02 20:12:41');
INSERT INTO historial_notas VALUES('106','71','80','1','7','','20.00','','','2025-11-02 20:14:36','Heldyn David Diaz Daboin','CREACION','1','2025-11-02 20:14:36','2025-11-02 20:14:36');
INSERT INTO historial_notas VALUES('107','72','81','3','6','','15.00','','','2025-11-03 11:16:03','Heldyn David Diaz Daboin','CREACION','1','2025-11-03 11:16:03','2025-11-03 11:16:03');


CREATE TABLE `horario_detalle` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `id_horario` int(11) NOT NULL,
  `dia_semana` varchar(10) NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `id_materia` int(11) NOT NULL,
  `id_profesor` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_detalle`),
  KEY `id_horario` (`id_horario`),
  KEY `id_materia` (`id_materia`),
  KEY `id_profesor` (`id_profesor`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO horario_detalle VALUES('3','2','Lunes','07:50:00','08:30:00','3','1');
INSERT INTO horario_detalle VALUES('5','3','Lunes','07:50:00','08:30:00','3','1');
INSERT INTO horario_detalle VALUES('7','4','Lunes','07:50:00','08:30:00','3','1');


CREATE TABLE `horarios` (
  `id_horario` int(11) NOT NULL AUTO_INCREMENT,
  `id_gestion` int(11) NOT NULL,
  `id_grado` int(11) NOT NULL,
  `id_seccion` int(11) NOT NULL,
  `aula` varchar(20) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'BORRADOR',
  `aprobado_por` int(11) DEFAULT NULL,
  `aprobado_en` datetime DEFAULT NULL,
  PRIMARY KEY (`id_horario`),
  KEY `id_gestion` (`id_gestion`),
  KEY `id_grado` (`id_grado`),
  KEY `id_seccion` (`id_seccion`),
  CONSTRAINT `horarios_ibfk_1` FOREIGN KEY (`id_gestion`) REFERENCES `gestiones` (`id_gestion`),
  CONSTRAINT `horarios_ibfk_2` FOREIGN KEY (`id_grado`) REFERENCES `grados` (`id_grado`),
  CONSTRAINT `horarios_ibfk_3` FOREIGN KEY (`id_seccion`) REFERENCES `secciones` (`id_seccion`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO horarios VALUES('1','34','50','98','','2025-11-04','2025-10-29','PUBLICADO','1','2025-11-03 15:48:15');


CREATE TABLE `inscripciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `id_seccion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_id_seccion` (`id_seccion`),
  CONSTRAINT `fk_id_seccion` FOREIGN KEY (`id_seccion`) REFERENCES `secciones` (`id_seccion`)
) ENGINE=InnoDB AUTO_INCREMENT=243 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO inscripciones VALUES('238','34','Secundaria','51','B','M','S','14','26','3','2025-10-31 17:29:50','2025-10-31 17:29:50','activo','100');
INSERT INTO inscripciones VALUES('239','34','Secundaria','50','A','M','S','S','25','10','2025-10-31 19:52:16','2025-10-31 19:52:16','activo','98');
INSERT INTO inscripciones VALUES('240','34','Secundaria','51','B','M','S','S','25','7','2025-11-01 00:48:11','2025-11-01 00:48:11','activo','100');
INSERT INTO inscripciones VALUES('241','34','Secundaria','51','B','M','S','14','30','80','2025-11-01 00:48:33','2025-11-01 00:48:33','activo','100');
INSERT INTO inscripciones VALUES('242','34','Secundaria','51','B','M','S','14','30','81','2025-11-01 00:48:52','2025-11-01 00:48:52','activo','100');


CREATE TABLE `lapsos` (
  `id_lapso` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_lapso` varchar(50) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `id_gestion` int(11) NOT NULL,
  PRIMARY KEY (`id_lapso`),
  KEY `id_gestion` (`id_gestion`),
  CONSTRAINT `lapsos_ibfk_1` FOREIGN KEY (`id_gestion`) REFERENCES `gestiones` (`id_gestion`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO lapsos VALUES('1','Primer lapso','2024-10-01','2025-02-07','1');
INSERT INTO lapsos VALUES('2','Segundo lapso','2025-02-17','2025-04-25','1');
INSERT INTO lapsos VALUES('3','Tercer lapso','2025-05-05','2025-07-25','1');
INSERT INTO lapsos VALUES('4','Primer lapso','2025-10-20','2025-10-21','33');
INSERT INTO lapsos VALUES('5','Segundo Lapso','2025-10-14','2025-10-14','33');
INSERT INTO lapsos VALUES('6','Primer lapso','2025-10-22','2025-11-01','34');
INSERT INTO lapsos VALUES('7','Segundo Lapso','2026-01-15','2026-02-19','34');
INSERT INTO lapsos VALUES('8','Tercer Lapso','2028-09-21','2028-10-26','34');


CREATE TABLE `materias` (
  `id_materia` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_materia` varchar(100) NOT NULL,
  `id_grado` int(11) NOT NULL,
  `nivel_educativo` enum('Preescolar','Primaria','Secundaria') NOT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `codigo` varchar(20) NOT NULL,
  `abreviatura` varchar(20) NOT NULL,
  PRIMARY KEY (`id_materia`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO materias VALUES('1','Matemáticas','19','Preescolar','1','','');
INSERT INTO materias VALUES('3','Educación Física','20','Preescolar','1','','');
INSERT INTO materias VALUES('4','Ingles','27','Preescolar','1','','');
INSERT INTO materias VALUES('5','Física','21','Preescolar','1','','');
INSERT INTO materias VALUES('6','Lenguaje y Comunicación','21','Preescolar','1','','');
INSERT INTO materias VALUES('7','Química','22','Preescolar','1','','');
INSERT INTO materias VALUES('8','Orientación y convivencia','23','Preescolar','1','','');


CREATE TABLE `niveles` (
  `id_nivel` int(11) NOT NULL AUTO_INCREMENT,
  `gestion_id` int(11) NOT NULL,
  `nivel` varchar(255) NOT NULL,
  `fyh_creacion` datetime DEFAULT NULL,
  `fyh_actualizacion` datetime DEFAULT NULL,
  `estado` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id_nivel`),
  UNIQUE KEY `gestion_id_2` (`gestion_id`),
  KEY `gestion_id` (`gestion_id`),
  CONSTRAINT `niveles_ibfk_1` FOREIGN KEY (`gestion_id`) REFERENCES `gestiones` (`id_gestion`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO niveles VALUES('3','1','PRIMARIA','2024-10-27 00:00:00','2024-10-27 00:00:00','');


CREATE TABLE `notas_estudiantes` (
  `id_nota` int(11) NOT NULL AUTO_INCREMENT,
  `id_estudiante` int(11) NOT NULL,
  `id_materia` int(11) NOT NULL,
  `id_lapso` int(11) NOT NULL,
  `calificacion` decimal(4,2) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_nota`),
  UNIQUE KEY `id_estudiante` (`id_estudiante`,`id_materia`,`id_lapso`),
  KEY `id_materia` (`id_materia`),
  KEY `id_lapso` (`id_lapso`),
  CONSTRAINT `notas_estudiantes_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id_estudiante`),
  CONSTRAINT `notas_estudiantes_ibfk_2` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`),
  CONSTRAINT `notas_estudiantes_ibfk_3` FOREIGN KEY (`id_lapso`) REFERENCES `lapsos` (`id_lapso`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO notas_estudiantes VALUES('1','1','1','1','19.00','','2025-04-15 02:20:07');
INSERT INTO notas_estudiantes VALUES('2','10','1','1','14.00','','2025-04-15 03:40:00');
INSERT INTO notas_estudiantes VALUES('3','1','3','1','5.00','','2025-05-11 18:11:48');
INSERT INTO notas_estudiantes VALUES('4','1','1','2','12.00','','2025-05-11 18:12:36');
INSERT INTO notas_estudiantes VALUES('5','1','3','3','20.00','','2025-05-11 18:12:43');
INSERT INTO notas_estudiantes VALUES('6','1','5','1','10.00','','2025-05-11 18:18:06');
INSERT INTO notas_estudiantes VALUES('7','1','4','1','1.00','','2025-05-11 18:18:07');
INSERT INTO notas_estudiantes VALUES('8','1','6','1','12.00','','2025-05-11 18:18:07');
INSERT INTO notas_estudiantes VALUES('9','1','8','1','11.00','','2025-05-11 18:18:07');
INSERT INTO notas_estudiantes VALUES('10','1','7','1','19.00','','2025-05-11 18:18:07');
INSERT INTO notas_estudiantes VALUES('11','1','3','2','10.00','','2025-05-11 18:19:42');
INSERT INTO notas_estudiantes VALUES('12','1','4','2','20.00','','2025-05-11 18:19:42');
INSERT INTO notas_estudiantes VALUES('13','1','5','2','13.00','','2025-05-11 18:19:42');
INSERT INTO notas_estudiantes VALUES('14','1','6','2','17.00','','2025-05-11 18:19:43');
INSERT INTO notas_estudiantes VALUES('15','1','7','2','18.00','','2025-05-11 18:19:43');
INSERT INTO notas_estudiantes VALUES('16','1','8','2','19.00','','2025-05-11 18:19:43');
INSERT INTO notas_estudiantes VALUES('17','1','1','3','10.00','','2025-05-11 18:21:28');
INSERT INTO notas_estudiantes VALUES('18','1','4','3','18.00','','2025-05-11 18:21:28');
INSERT INTO notas_estudiantes VALUES('19','1','5','3','20.00','','2025-05-11 18:21:28');
INSERT INTO notas_estudiantes VALUES('20','1','6','3','15.00','','2025-05-11 18:21:28');
INSERT INTO notas_estudiantes VALUES('21','1','7','3','16.00','','2025-05-11 18:21:28');
INSERT INTO notas_estudiantes VALUES('22','1','8','3','14.00','','2025-05-11 18:21:28');
INSERT INTO notas_estudiantes VALUES('23','80','1','1','10.00','','2025-08-25 16:25:37');
INSERT INTO notas_estudiantes VALUES('24','80','3','2','15.00','','2025-08-25 16:25:54');
INSERT INTO notas_estudiantes VALUES('25','80','5','3','15.00','','2025-08-25 16:26:03');
INSERT INTO notas_estudiantes VALUES('26','81','1','1','10.00','','2025-08-25 16:27:15');
INSERT INTO notas_estudiantes VALUES('27','81','3','1','20.00','','2025-08-25 16:27:15');
INSERT INTO notas_estudiantes VALUES('28','81','6','1','15.00','','2025-08-25 16:27:15');
INSERT INTO notas_estudiantes VALUES('29','81','1','2','12.00','','2025-08-25 16:27:35');
INSERT INTO notas_estudiantes VALUES('30','81','3','2','15.00','','2025-08-25 16:27:35');
INSERT INTO notas_estudiantes VALUES('31','81','6','2','5.00','','2025-08-25 16:27:35');
INSERT INTO notas_estudiantes VALUES('32','81','1','3','12.00','','2025-08-25 16:28:04');
INSERT INTO notas_estudiantes VALUES('33','81','3','3','15.00','','2025-08-25 16:28:04');
INSERT INTO notas_estudiantes VALUES('34','81','6','3','10.00','','2025-08-25 16:28:04');
INSERT INTO notas_estudiantes VALUES('35','32','3','4','20.00','','2025-10-20 18:21:48');
INSERT INTO notas_estudiantes VALUES('36','31','3','4','20.00','','2025-10-20 18:21:48');
INSERT INTO notas_estudiantes VALUES('37','32','6','4','11.00','','2025-10-20 18:21:43');
INSERT INTO notas_estudiantes VALUES('38','31','6','4','15.00','','2025-10-20 18:21:43');
INSERT INTO notas_estudiantes VALUES('39','19','4','4','20.00','','2025-10-20 18:32:28');
INSERT INTO notas_estudiantes VALUES('40','20','4','4','20.00','','2025-10-20 18:32:28');
INSERT INTO notas_estudiantes VALUES('41','21','3','4','20.00','','2025-10-20 18:32:57');
INSERT INTO notas_estudiantes VALUES('42','22','3','4','20.00','','2025-10-20 18:32:57');
INSERT INTO notas_estudiantes VALUES('43','21','5','4','10.00','','2025-10-20 22:44:59');
INSERT INTO notas_estudiantes VALUES('44','22','5','4','13.00','','2025-10-20 22:45:24');
INSERT INTO notas_estudiantes VALUES('45','21','4','4','20.00','','2025-10-20 18:33:29');
INSERT INTO notas_estudiantes VALUES('46','22','4','4','13.00','','2025-10-20 22:45:12');
INSERT INTO notas_estudiantes VALUES('47','32','5','4','20.00','','2025-10-20 18:34:55');
INSERT INTO notas_estudiantes VALUES('48','31','5','4','20.00','','2025-10-20 18:34:55');
INSERT INTO notas_estudiantes VALUES('49','21','6','4','20.00','','2025-10-20 19:31:00');
INSERT INTO notas_estudiantes VALUES('50','10','6','4','20.00','','2025-10-20 20:28:28');
INSERT INTO notas_estudiantes VALUES('51','9','6','4','10.00','','2025-10-20 20:28:28');
INSERT INTO notas_estudiantes VALUES('52','19','3','4','20.00','Error de tipificacion','2025-10-21 14:54:34');
INSERT INTO notas_estudiantes VALUES('53','20','3','4','20.00','','2025-10-20 20:45:54');
INSERT INTO notas_estudiantes VALUES('54','10','4','4','20.00','Error de tipificacion','2025-10-21 12:39:58');
INSERT INTO notas_estudiantes VALUES('55','9','4','4','20.00','','2025-10-21 01:06:33');
INSERT INTO notas_estudiantes VALUES('56','22','6','4','15.00','','2025-10-20 22:44:33');
INSERT INTO notas_estudiantes VALUES('57','19','5','4','20.00','','2025-10-21 20:13:29');
INSERT INTO notas_estudiantes VALUES('58','20','5','4','20.00','','2025-10-21 20:13:29');
INSERT INTO notas_estudiantes VALUES('59','3','3','6','20.00','','2025-10-31 23:49:11');
INSERT INTO notas_estudiantes VALUES('60','3','3','7','15.00','','2025-11-01 00:07:13');
INSERT INTO notas_estudiantes VALUES('61','3','3','8','18.00','','2025-11-01 00:07:23');
INSERT INTO notas_estudiantes VALUES('62','3','4','8','15.00','','2025-11-01 00:07:33');
INSERT INTO notas_estudiantes VALUES('63','3','4','7','19.00','','2025-11-01 00:07:46');
INSERT INTO notas_estudiantes VALUES('64','3','4','6','18.00','','2025-11-01 00:07:57');
INSERT INTO notas_estudiantes VALUES('65','3','1','6','16.00','','2025-11-01 00:08:09');
INSERT INTO notas_estudiantes VALUES('66','3','1','7','20.00','','2025-11-01 00:08:18');
INSERT INTO notas_estudiantes VALUES('67','3','1','8','2.00','','2025-11-01 00:08:26');
INSERT INTO notas_estudiantes VALUES('68','10','3','6','15.00','','2025-11-01 01:52:42');
INSERT INTO notas_estudiantes VALUES('69','80','3','6','20.00','','2025-11-02 20:12:05');
INSERT INTO notas_estudiantes VALUES('70','80','4','6','20.00','','2025-11-02 20:12:41');
INSERT INTO notas_estudiantes VALUES('71','80','1','7','20.00','','2025-11-02 20:14:36');
INSERT INTO notas_estudiantes VALUES('72','81','3','6','15.00','','2025-11-03 11:16:03');


CREATE TABLE `periodos_anuales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `año` int(4) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `periodo_actual` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_anio` (`año`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO periodos_anuales VALUES('1','2025','2025-01-01','2025-12-31','','1','1','2025-08-28 23:45:41');
INSERT INTO periodos_anuales VALUES('3','2026','2026-01-01','2026-12-31','','0','0','2025-08-29 13:50:25');


CREATE TABLE `permisos` (
  `id_permiso` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_url` varchar(100) NOT NULL,
  `url` text NOT NULL,
  `fyh_creacion` datetime DEFAULT NULL,
  `fyh_actualizacion` datetime DEFAULT NULL,
  `estado` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id_permiso`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO permisos VALUES('1','Configuraciones','admin/configuraciones/institucion/','2024-10-26 18:50:54','','1');
INSERT INTO permisos VALUES('2','Periodo academico','admin/configuraciones/gestion/','2024-10-26 18:51:45','','1');
INSERT INTO permisos VALUES('3','Panel administrador','admin/','2024-10-26 18:52:18','','1');
INSERT INTO permisos VALUES('4','Inscripción','admin/inscripciones/','2024-10-26 18:52:56','2024-10-26 18:53:37','1');
INSERT INTO permisos VALUES('5','Lista de estudiante','admin/estudiantes/','2024-10-26 18:54:02','','1');
INSERT INTO permisos VALUES('6','Lista de turnos','admin/niveles/','2024-10-26 18:55:34','','1');
INSERT INTO permisos VALUES('7','Grados','admin/grados/','2024-10-26 18:55:56','','1');
INSERT INTO permisos VALUES('8','Secciones','http://localhost/Daniel/SIGE/admin/seccion/','2024-10-26 18:56:15','','1');
INSERT INTO permisos VALUES('9','Roles','admin/roles/','2024-10-26 18:56:35','','1');
INSERT INTO permisos VALUES('10','Permisos del sistema','admin/roles/permisos.php','2024-10-26 18:57:11','','1');
INSERT INTO permisos VALUES('11','Registro de usuarios','admin/usuarios/','2024-10-26 18:57:58','','1');
INSERT INTO permisos VALUES('12','Personal administrativo','admin/administrativos/','2024-10-26 18:58:23','','1');
INSERT INTO permisos VALUES('13','Personal docente','admin/docentes/','2024-10-26 18:58:47','','1');


CREATE TABLE `personas` (
  `id_persona` int(11) NOT NULL AUTO_INCREMENT,
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
  `foto_perfil` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_persona`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO personas VALUES('28','79','Keila ','Naveda','27985583','1994-01-30','Parroquia Caricuao Ud1','04124331080','2025-10-31','2025-11-04','1','');
INSERT INTO personas VALUES('29','80','Heldyn David','Diaz Daboin','15888555','2025-10-23','Parroquia Caricuao Ud1','04124331080','2025-10-31','2025-11-03','1','');


CREATE TABLE `plantillas_carnet` (
  `id_plantilla` int(11) NOT NULL AUTO_INCREMENT,
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
  `estatus` enum('activo','inactivo') DEFAULT 'activo',
  PRIMARY KEY (`id_plantilla`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



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



CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO product_categories VALUES('1','Tubería de Presión Agua Fría','Tuberías de PVC para sistemas de agua fría a presión','fas fa-faucet','1','2025-08-31 01:14:46');
INSERT INTO product_categories VALUES('2','Tubería de Presión Agua Caliente (CPVC)','Tuberías de CPVC para sistemas de agua caliente','fas fa-fire','1','2025-08-31 01:14:46');
INSERT INTO product_categories VALUES('3','Tubería de Polipropileno','Tuberías de polipropileno para diversos usos','fas fa-pipe','1','2025-08-31 01:14:46');
INSERT INTO product_categories VALUES('4','Tubería Conduit','Tuberías para conducción y protección de cables eléctricos','fas fa-bolt','1','2025-08-31 01:14:46');
INSERT INTO product_categories VALUES('5','Tubería Sanitaria Clase A y B','Tuberías para sistemas sanitarios y de drenaje','fas fa-shower','1','2025-08-31 01:14:46');
INSERT INTO product_categories VALUES('6','Tubería de Soldadura','Tuberías especiales para sistemas de soldadura','fas fa-tools','1','2025-08-31 01:14:46');
INSERT INTO product_categories VALUES('7','Tubería de Alcantarillado','Tuberías para sistemas de alcantarillado','fas fa-water','1','2025-08-31 01:14:46');
INSERT INTO product_categories VALUES('8','Tubería de Acueducto','Tuberías para sistemas de acueducto','fas fa-tint','1','2025-08-31 01:14:46');
INSERT INTO product_categories VALUES('9','Ducto Eléctrico y Telefónico','Ductos para instalaciones eléctricas y telefónicas','fas fa-phone','1','2025-08-31 01:14:46');


CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `features` text DEFAULT NULL,
  `applications` text DEFAULT NULL,
  `specifications` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO products VALUES('1','1','Tubería PVC Agua Fría 1/2\"','Tubería de PVC para agua fría de 1/2 pulgada','Resistente a la corrosión, fácil instalación, bajo peso','Sistemas de agua potable, riego, instalaciones residenciales','Diámetro: 1/2\", Presión máxima: 150 PSI, Longitud: 6m','','1','0','2025-08-31 01:14:46');
INSERT INTO products VALUES('2','1','Tubería PVC Agua Fría 3/4\"','Tubería de PVC para agua fría de 3/4 pulgada','Resistente a la corrosión, fácil instalación, bajo peso','Sistemas de agua potable, riego, instalaciones residenciales','Diámetro: 3/4\", Presión máxima: 150 PSI, Longitud: 6m','','1','0','2025-08-31 01:14:46');
INSERT INTO products VALUES('3','2','Tubería CPVC Agua Caliente 1/2\"','Tubería de CPVC para agua caliente de 1/2 pulgada','Resistente a altas temperaturas, no se corroe','Sistemas de agua caliente, instalaciones industriales','Diámetro: 1/2\", Temperatura máxima: 90°C, Longitud: 6m','','1','0','2025-08-31 01:14:46');
INSERT INTO products VALUES('4','4','Tubería Conduit 1\"','Tubería para protección de cables eléctricos de 1 pulgada','Protección contra impactos, aislante eléctrico','Instalaciones eléctricas residenciales e industriales','Diámetro: 1\", Resistencia: Alta, Longitud: 3m','','1','0','2025-08-31 01:14:46');
INSERT INTO products VALUES('5','5','Tubería Sanitaria 4\"','Tubería para sistemas sanitarios de 4 pulgadas','Alta resistencia, superficie lisa, fácil instalación','Sistemas de drenaje, alcantarillado sanitario','Diámetro: 4\", Longitud: 6m, Clase: A','','1','0','2025-08-31 01:14:46');
INSERT INTO products VALUES('6','7','Tubería Alcantarillado 6\"','Tubería para sistemas de alcantarillado de 6 pulgadas','Alta resistencia a cargas, durabilidad','Sistemas de alcantarillado municipal, drenaje pluvial','Diámetro: 6\", Longitud: 6m, Clase: B','','1','0','2025-08-31 01:14:46');
INSERT INTO products VALUES('7','9','Ducto Eléctrico 2\"','Ducto para instalaciones eléctricas de 2 pulgadas','Protección mecánica, resistencia al impacto','Instalaciones eléctricas en edificaciones','Diámetro: 2\", Longitud: 3m, Color: Gris','','1','0','2025-08-31 01:14:46');


CREATE TABLE `profesor_seccion_materia` (
  `id_relacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_profesor` int(11) DEFAULT NULL,
  `id_seccion` int(11) DEFAULT NULL,
  `id_materia` int(11) DEFAULT NULL,
  `id_gestion` int(11) DEFAULT NULL,
  `estado` tinyint(4) DEFAULT 1,
  `fecha_asignacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_relacion`),
  KEY `id_profesor` (`id_profesor`),
  KEY `id_seccion` (`id_seccion`),
  KEY `id_materia` (`id_materia`),
  KEY `id_gestion` (`id_gestion`),
  CONSTRAINT `profesor_seccion_materia_ibfk_1` FOREIGN KEY (`id_profesor`) REFERENCES `profesores` (`id_profesor`),
  CONSTRAINT `profesor_seccion_materia_ibfk_2` FOREIGN KEY (`id_seccion`) REFERENCES `secciones` (`id_seccion`),
  CONSTRAINT `profesor_seccion_materia_ibfk_3` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`),
  CONSTRAINT `profesor_seccion_materia_ibfk_4` FOREIGN KEY (`id_gestion`) REFERENCES `gestiones` (`id_gestion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `profesores` (
  `id_profesor` int(11) NOT NULL AUTO_INCREMENT,
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
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id_profesor`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `cedula` (`cedula`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO profesores VALUES('1','1234567890','Juan','Pérez','juan.perez@example.com','0987654321','Matemáticas','1','2025-05-13 14:15:38','','1','e10adc3949ba59abbe56e057f20f883e');
INSERT INTO profesores VALUES('3','27985583','Heldyn David','Diaz Daboin','heldyndiaz19@gmail.com','04121988817','Educación Física','1','2025-10-20 16:02:12','2025-11-04 16:51:19','0','$2y$10$wZJBiqDScc2RgPHBpl/N9.wd6yPxRIXKkbu8/GVDZsLoaXnjAwyhS');
INSERT INTO profesores VALUES('4','27985584','Saned Arya','Diaz Daboin','docente@gmail.com','02124331080','CIENCIAS SOCIALES','1','2025-10-21 20:23:56','','0','$2y$10$zc9wchmp4M0syuycKEBuKewwPcb8hLdknyo9W/O98Gm.X0SIFCFWq');
INSERT INTO profesores VALUES('6','29720599','Saned Arya','Diaz Daboin','dv47762@gmail.com','02124331080','CIENCIAS SOCIALES','1','2025-11-04 15:32:25','','0','');


CREATE TABLE `project_list` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `status` tinyint(2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `manager_id` int(30) NOT NULL,
  `user_ids` text NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO project_list VALUES('1','Sample Project','																				&lt;span style=&quot;color: rgb(0, 0, 0); font-family: &amp;quot;Open Sans&amp;quot;, Arial, sans-serif; font-size: 14px; text-align: justify;&quot;&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. In elementum, metus vitae malesuada mollis, urna nisi luctus ligula, vitae volutpat massa eros eu ligula. Nunc dui metus, iaculis id dolor non, luctus tristique libero. Aenean et sagittis sem. Nulla facilisi. Mauris at placerat augue. Nullam porttitor felis turpis, ac varius eros placerat et. Nunc ut enim scelerisque, porta lacus vitae, viverra justo. Nam mollis turpis nec dolor feugiat, sed bibendum velit placerat. Etiam in hendrerit leo. Nullam mollis lorem massa, sit amet tincidunt dolor lacinia at.&lt;/span&gt;																	','0','2020-11-03','2021-01-20','2','3,4,5','2020-12-03 09:56:56');
INSERT INTO project_list VALUES('2','Sample Project 102','Sample Only','0','2020-12-02','2020-12-31','2','3','2020-12-03 13:51:54');


CREATE TABLE `reportes` (
  `id_reporte` int(11) NOT NULL AUTO_INCREMENT,
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
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_reporte`),
  KEY `id_estudiante` (`id_estudiante`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `representantes` (
  `id_representante` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_representante`),
  KEY `correo_electrónico` (`correo_electronico`)
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO representantes VALUES('1','V','14023560','Carlos Alberto','Pérez López','1980-05-10','Casado','','masculino','carlosperez@gmail.com','O+','Caracas','04121234501','Activo','2025-01-24 07:59:04');
INSERT INTO representantes VALUES('2','V','14023561','Ana María','González Torres','1985-06-15','Soltero','','femenino','anamaria@gmail.com','A+','Caracas','04121234502','Inactivo','2025-01-24 07:59:04');
INSERT INTO representantes VALUES('3','V','13023562','Luis Fernando','Martínez Ruiz','1990-07-20','Casado','','masculino','luisfernando@gmail.com','B+','Caracas','04121234503','Activo','2025-01-24 07:59:04');
INSERT INTO representantes VALUES('4','V','13023563','Sofía Valentina','Ramírez Pérez','1995-08-25','Soltero','','femenino','sofiaramirez@gmail.com','AB+','Caracas','04121234504','Activo','2025-01-24 07:59:04');
INSERT INTO representantes VALUES('5','V','10023564','Diego Armando','Hernández Ruiz','1988-09-30','Casado','','masculino','diegohernandez@gmail.com','O-','Caracas','04121234505','Activo','2025-01-24 07:59:04');
INSERT INTO representantes VALUES('6','V','10023565','María José','López García','1992-10-05','Soltero','','femenino','mariajose@gmail.com','B-','Caracas','04121234506','Activo','2025-01-24 07:59:04');
INSERT INTO representantes VALUES('7','V','7202356','Fernando José','Cruz Mierez','1983-11-10','Casado','','masculino','fernandoj@gmail.com','O+','Caracas','04121234507','Activo','2025-01-24 07:59:04');
INSERT INTO representantes VALUES('8','V','7202567','Lucía Fernanda','Daboin Rodriguez','1987-12-15','Soltero','','femenino','luciafernanda@gmail.com','A+','Caracas','04121234508','Activo','2025-01-24 07:59:04');
INSERT INTO representantes VALUES('9','V','8023568','Javier Alejandro','Martínez López','1991-01-20','Casado','','masculino','javieralejandro@gmail.com','B+','Caracas','04121234509','Activo','2025-01-24 07:59:04');
INSERT INTO representantes VALUES('10','V','8202569','Isabella','Ramírez López','1994-02-25','Soltero','','femenino','isabellar@gmail.com','AB+','Caracas','04121234510','Activo','2025-01-24 07:59:04');
INSERT INTO representantes VALUES('11','V','14023570','Carlos Eduardo','González Torres','1980-03-10','Casado','','masculino','carloseduardo@gmail.com','O+','Caracas','04121234511','Activo','2025-01-24 07:59:04');
INSERT INTO representantes VALUES('12','V','13023571','María Fernanda','Pérez López','1985-04-15','Soltero','','femenino','mariafernanda@gmail.com','A+','Caracas','04121234512','Activo','2025-01-24 07:59:04');
INSERT INTO representantes VALUES('13','V','10023572','Luis Miguel','Martínez Ruiz','1990-05-20','Casado','','masculino','luismiguel@gmail.com','B+','Caracas','04121234513','Activo','2025-01-24 07:59:04');
INSERT INTO representantes VALUES('14','V','6203573','Sofía Alejandra','Hernández Ruiz','1995-06-25','Soltero','','femenino','sofiaalejandra@gmail.com','AB+','Caracas','04121234514','Activo','2025-01-24 07:59:04');
INSERT INTO representantes VALUES('15','V','9202574','Diego Alejandro','Cruz Mierez','1988-07-30','Casado','','masculino','diegoalejandro@gmail.com','O-','Caracas','04121234515','Activo','2025-01-24 07:59:04');
INSERT INTO representantes VALUES('16','V','14023575','María Elena','Daboin Rodriguez','1992-08-05','Soltero','','femenino','mariaelena@gmail.com','B-','Caracas','04121234516','Activo','2025-01-24 07:59:04');
INSERT INTO representantes VALUES('17','V','13023576','Fernando Andrés','Martínez López','1995-09-10','Casado','','masculino','fernandoandres@gmail.com','O+','Caracas','04121234517','Activo','2025-01-24 07:59:04');
INSERT INTO representantes VALUES('105','V','27985583','Marcos José','Cruz Mierez','2006-12-20','Soltero','mama','masculino','marcos1904@gmail.com','O+','Caricuao','04121988817','Activo','2025-01-09 17:23:26');
INSERT INTO representantes VALUES('113','V','14756124','Marilyn del Carmen','Daboin Rodriguez','2007-01-16','Soltero','mama','femenino','mary@gmail.com','B+','Parroquia Caricuao Ud1','04164655292','Activo','2025-01-16 17:07:44');
INSERT INTO representantes VALUES('114','V','11985583','Maria Lupita','Aray Acosta','2007-01-22','Casado','mama','masculino','marialupita@gmail.com','O+','Parroquia Caricuao Ud1','04121988817','Activo','2025-01-22 12:50:14');
INSERT INTO representantes VALUES('115','V','16023755','Deivis Jose','Quintero Gimenez','1982-04-01','Casado','mama','masculino','deivisquintero@gmail.com','O-','Caracas,Caricuao, UD1','04144466435','Activo','2025-11-04 16:15:21');


CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_rol` varchar(255) NOT NULL,
  `fyh_creacion` datetime DEFAULT NULL,
  `fyh_actualizacion` datetime DEFAULT NULL,
  `estado` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `nombre_rol` (`nombre_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO roles VALUES('1','ADMINISTRADOR','2024-10-26 19:22:07','2024-11-12 00:00:00','1');
INSERT INTO roles VALUES('2','DIRECTOR','2024-10-26 19:23:06','','1');
INSERT INTO roles VALUES('3','SUBDIRETOR','2024-10-26 19:23:14','','1');
INSERT INTO roles VALUES('4','PERSONAL ADMINISTRATIVO','2024-10-26 19:23:33','','1');
INSERT INTO roles VALUES('5','DOCENTE','2024-10-26 19:23:43','','1');
INSERT INTO roles VALUES('7','REPRESENTANTE','2024-10-27 00:00:00','2025-01-17 00:00:00','1');
INSERT INTO roles VALUES('8','ADMINISTRATIVOS','2024-10-27 00:00:00','','1');


CREATE TABLE `roles_permisos` (
  `id_rol_permiso` int(11) NOT NULL AUTO_INCREMENT,
  `rol_id` int(11) NOT NULL,
  `permiso_id` int(11) NOT NULL,
  `fyh_creacion` datetime DEFAULT NULL,
  `fyh_actualizacion` datetime DEFAULT NULL,
  `estado` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id_rol_permiso`),
  KEY `rol_id` (`rol_id`),
  KEY `permiso_id` (`permiso_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO roles_permisos VALUES('1','1','1','2024-10-26 19:22:12','','1');
INSERT INTO roles_permisos VALUES('2','1','7','2024-10-26 19:22:14','','1');
INSERT INTO roles_permisos VALUES('3','1','4','2024-10-26 19:22:19','','1');
INSERT INTO roles_permisos VALUES('4','1','5','2024-10-26 19:22:22','','1');
INSERT INTO roles_permisos VALUES('5','1','6','2024-10-26 19:22:25','','1');
INSERT INTO roles_permisos VALUES('6','1','3','2024-10-26 19:22:29','','1');
INSERT INTO roles_permisos VALUES('7','1','2','2024-10-26 19:22:31','','1');
INSERT INTO roles_permisos VALUES('8','1','10','2024-10-26 19:22:39','','1');
INSERT INTO roles_permisos VALUES('9','1','12','2024-10-26 19:22:41','','1');
INSERT INTO roles_permisos VALUES('10','1','13','2024-10-26 19:22:46','','1');
INSERT INTO roles_permisos VALUES('11','1','11','2024-10-26 19:22:49','','1');
INSERT INTO roles_permisos VALUES('12','1','9','2024-10-26 19:22:52','','1');
INSERT INTO roles_permisos VALUES('13','1','8','2024-10-26 19:22:55','','1');
INSERT INTO roles_permisos VALUES('14','4','1','2025-01-17 00:00:00','','1');
INSERT INTO roles_permisos VALUES('15','4','4','2025-01-17 00:00:00','','1');
INSERT INTO roles_permisos VALUES('16','7','1','2025-01-17 00:00:00','','1');
INSERT INTO roles_permisos VALUES('18','7','4','2025-01-17 00:00:00','','1');
INSERT INTO roles_permisos VALUES('19','7','3','2025-01-17 00:00:00','','1');
INSERT INTO roles_permisos VALUES('20','7','10','2025-01-17 00:00:00','','1');


CREATE TABLE `sangre` (
  `sangre_id` int(30) NOT NULL,
  `tipo_sangre` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO sangre VALUES('1','A+');
INSERT INTO sangre VALUES('2','A-');
INSERT INTO sangre VALUES('3','B+');
INSERT INTO sangre VALUES('4','B-');
INSERT INTO sangre VALUES('5','AB+');
INSERT INTO sangre VALUES('6','AB-');
INSERT INTO sangre VALUES('7','O+');
INSERT INTO sangre VALUES('8','O-');
INSERT INTO sangre VALUES('1','A+');
INSERT INTO sangre VALUES('2','A-');
INSERT INTO sangre VALUES('3','B+');
INSERT INTO sangre VALUES('4','B-');
INSERT INTO sangre VALUES('5','AB+');
INSERT INTO sangre VALUES('6','AB-');
INSERT INTO sangre VALUES('7','O+');
INSERT INTO sangre VALUES('8','O-');


CREATE TABLE `secciones` (
  `id_seccion` int(11) NOT NULL AUTO_INCREMENT,
  `turno` char(1) DEFAULT NULL,
  `capacidad` int(11) NOT NULL,
  `id_gestion` int(11) NOT NULL,
  `id_grado` int(11) NOT NULL,
  `estado` tinyint(4) DEFAULT 1,
  `nombre_seccion` varchar(255) NOT NULL,
  `fyh_creacion` datetime DEFAULT current_timestamp(),
  `cupo_actual` int(11) DEFAULT 0,
  `aula` varchar(20) NOT NULL,
  PRIMARY KEY (`id_seccion`),
  KEY `id_gestion` (`id_gestion`),
  KEY `id_grado` (`id_grado`),
  CONSTRAINT `secciones_ibfk_1` FOREIGN KEY (`id_gestion`) REFERENCES `gestiones` (`id_gestion`) ON DELETE CASCADE,
  CONSTRAINT `secciones_ibfk_2` FOREIGN KEY (`id_grado`) REFERENCES `grados` (`id_grado`)
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO secciones VALUES('97','M','35','34','50','1','B','2025-10-31 17:25:01','0','');
INSERT INTO secciones VALUES('98','M','25','34','50','1','A','2025-10-31 17:25:13','1','');
INSERT INTO secciones VALUES('99','M','25','34','50','1','C','2025-10-31 17:25:39','0','');
INSERT INTO secciones VALUES('100','M','30','34','51','1','B','2025-10-31 17:29:23','4','');
INSERT INTO secciones VALUES('101','M','30','34','50','1','D','2025-11-04 16:25:35','0','');


CREATE TABLE `sexos` (
  `sexo_id` int(11) NOT NULL,
  `sexo` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO sexos VALUES('1','Masculino');
INSERT INTO sexos VALUES('2','Femenino');
INSERT INTO sexos VALUES('1','Masculino');
INSERT INTO sexos VALUES('2','Femenino');


CREATE TABLE `social_media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL,
  `icon_type` enum('fontawesome','image') NOT NULL DEFAULT 'fontawesome',
  `color` varchar(7) NOT NULL DEFAULT '#3b5998',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `status_index` (`status`),
  KEY `icon_type_index` (`icon_type`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO social_media VALUES('1','Facebook','https://facebook.com/tuempresa','fab fa-facebook','fontawesome','#3b5998','1','2025-08-27 18:08:50','2025-10-12 23:20:10');
INSERT INTO social_media VALUES('2','Twitter','https://twitter.com/tuempresa','fab fa-twitter','fontawesome','#000000','1','2025-08-27 18:08:50','2025-10-12 23:21:12');
INSERT INTO social_media VALUES('3','Instagram','https://instagram.com/tuempresa','fab fa-instagram','fontawesome','#E1306C','1','2025-08-27 18:08:50','2025-10-12 23:21:29');
INSERT INTO social_media VALUES('4','WhatsApp','https://web.whatsapp.com/','fab fa-whatsapp','fontawesome','#00FF00','1','2025-08-27 18:08:50','2025-10-12 23:21:39');


CREATE TABLE `solicitudes_constancias` (
  `id_solicitud` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_solicitud`),
  KEY `id_estudiante` (`id_estudiante`),
  KEY `id_tipo_constancia` (`id_tipo_constancia`),
  KEY `id_usuario_aprobador` (`id_usuario_aprobador`),
  KEY `id_usuario_entrega` (`id_usuario_entrega`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO solicitudes_constancias VALUES('1','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:17:54','','','Pendiente','','','','2025-04-20 00:17:54','2025-04-20 00:17:54');
INSERT INTO solicitudes_constancias VALUES('2','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:17:54','','','Pendiente','','','','2025-04-20 00:17:54','2025-04-20 00:17:54');
INSERT INTO solicitudes_constancias VALUES('3','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:22:52','','','Pendiente','','','','2025-04-20 00:22:52','2025-04-20 00:22:52');
INSERT INTO solicitudes_constancias VALUES('4','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:22:52','','','Pendiente','','','','2025-04-20 00:22:52','2025-04-20 00:22:52');
INSERT INTO solicitudes_constancias VALUES('5','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Representante Legal','','2025-04-20 06:33:53','','','Pendiente','','','','2025-04-20 00:33:53','2025-04-20 00:33:53');
INSERT INTO solicitudes_constancias VALUES('6','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Representante Legal','','2025-04-20 06:33:55','','','Pendiente','','','','2025-04-20 00:33:55','2025-04-20 00:33:55');
INSERT INTO solicitudes_constancias VALUES('7','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:39:03','','','Pendiente','','','','2025-04-20 00:39:03','2025-04-20 00:39:03');
INSERT INTO solicitudes_constancias VALUES('8','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:39:06','','','Pendiente','','','','2025-04-20 00:39:06','2025-04-20 00:39:06');
INSERT INTO solicitudes_constancias VALUES('9','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:40:36','','','Pendiente','','','','2025-04-20 00:40:36','2025-04-20 00:40:36');
INSERT INTO solicitudes_constancias VALUES('10','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:40:37','','','Pendiente','','','','2025-04-20 00:40:37','2025-04-20 00:40:37');
INSERT INTO solicitudes_constancias VALUES('11','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:41:25','','','Pendiente','','','','2025-04-20 00:41:25','2025-04-20 00:41:25');
INSERT INTO solicitudes_constancias VALUES('12','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:41:26','','','Pendiente','','','','2025-04-20 00:41:26','2025-04-20 00:41:26');
INSERT INTO solicitudes_constancias VALUES('13','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:42:52','','','Pendiente','','','','2025-04-20 00:42:52','2025-04-20 00:42:52');
INSERT INTO solicitudes_constancias VALUES('14','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:48:49','','','Pendiente','','','','2025-04-20 00:48:49','2025-04-20 00:48:49');
INSERT INTO solicitudes_constancias VALUES('15','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:48:51','','','Pendiente','','','','2025-04-20 00:48:51','2025-04-20 00:48:51');
INSERT INTO solicitudes_constancias VALUES('16','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:49:59','','','Pendiente','','','','2025-04-20 00:49:59','2025-04-20 00:49:59');
INSERT INTO solicitudes_constancias VALUES('17','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:50:00','','','Pendiente','','','','2025-04-20 00:50:00','2025-04-20 00:50:00');
INSERT INTO solicitudes_constancias VALUES('18','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:51:53','','','Pendiente','','','','2025-04-20 00:51:53','2025-04-20 00:51:53');
INSERT INTO solicitudes_constancias VALUES('19','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:51:55','','','Pendiente','','','','2025-04-20 00:51:55','2025-04-20 00:51:55');
INSERT INTO solicitudes_constancias VALUES('20','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:53:35','','','Pendiente','','','','2025-04-20 00:53:35','2025-04-20 00:53:35');
INSERT INTO solicitudes_constancias VALUES('21','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:53:37','','','Pendiente','','','','2025-04-20 00:53:37','2025-04-20 00:53:37');
INSERT INTO solicitudes_constancias VALUES('22','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:54:28','','','Pendiente','','','','2025-04-20 00:54:28','2025-04-20 00:54:28');
INSERT INTO solicitudes_constancias VALUES('23','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:54:31','','','Pendiente','','','','2025-04-20 00:54:31','2025-04-20 00:54:31');
INSERT INTO solicitudes_constancias VALUES('24','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:55:55','','','Pendiente','','','','2025-04-20 00:55:55','2025-04-20 00:55:55');
INSERT INTO solicitudes_constancias VALUES('25','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:55:57','','','Pendiente','','','','2025-04-20 00:55:57','2025-04-20 00:55:57');
INSERT INTO solicitudes_constancias VALUES('26','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:57:15','','','Pendiente','','','','2025-04-20 00:57:15','2025-04-20 00:57:15');
INSERT INTO solicitudes_constancias VALUES('27','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:57:17','','','Pendiente','','','','2025-04-20 00:57:17','2025-04-20 00:57:17');
INSERT INTO solicitudes_constancias VALUES('28','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:59:46','','','Pendiente','','','','2025-04-20 00:59:46','2025-04-20 00:59:46');
INSERT INTO solicitudes_constancias VALUES('29','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 06:59:48','','','Pendiente','','','','2025-04-20 00:59:48','2025-04-20 00:59:48');
INSERT INTO solicitudes_constancias VALUES('30','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 07:00:29','','','Pendiente','','','','2025-04-20 01:00:29','2025-04-20 01:00:29');
INSERT INTO solicitudes_constancias VALUES('31','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 07:00:31','','','Pendiente','','','','2025-04-20 01:00:31','2025-04-20 01:00:31');
INSERT INTO solicitudes_constancias VALUES('32','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 07:03:35','','','Pendiente','','','','2025-04-20 01:03:35','2025-04-20 01:03:35');
INSERT INTO solicitudes_constancias VALUES('33','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 07:03:37','','','Pendiente','','','','2025-04-20 01:03:37','2025-04-20 01:03:37');
INSERT INTO solicitudes_constancias VALUES('34','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Representante Legal','','2025-04-20 18:20:45','','','Pendiente','','','','2025-04-20 12:20:45','2025-04-20 12:20:45');
INSERT INTO solicitudes_constancias VALUES('35','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Representante Legal','','2025-04-20 18:20:45','','','Pendiente','','','','2025-04-20 12:20:45','2025-04-20 12:20:45');
INSERT INTO solicitudes_constancias VALUES('36','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 18:41:09','','','Pendiente','','','','2025-04-20 12:41:09','2025-04-20 12:41:09');
INSERT INTO solicitudes_constancias VALUES('37','1','V-30045678','Carlos Eduardo Pérez López','N/A - N/A','1','Carlos Alberto Pérez López','V-14023560','Padre','','2025-04-20 18:41:11','','','Pendiente','','','','2025-04-20 12:41:11','2025-04-20 12:41:11');


CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','image','color') DEFAULT 'text',
  `is_logo` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO system_settings VALUES('1','SISTEMA INTEGRAL DE GESTIÓN','uploads/settings/1756425951_Captura de pantalla 2025-08-28 183911.png','image','1','Título del sistema que aparece en el login','2025-08-28 19:46:26','2025-08-28 20:27:39');


CREATE TABLE `tallas` (
  `talla_id` int(30) NOT NULL,
  `talla` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO tallas VALUES('1','XS');
INSERT INTO tallas VALUES('2','S');
INSERT INTO tallas VALUES('3','M');
INSERT INTO tallas VALUES('4','L');
INSERT INTO tallas VALUES('5','XL');
INSERT INTO tallas VALUES('6','XXL');
INSERT INTO tallas VALUES('1','XS');
INSERT INTO tallas VALUES('2','S');
INSERT INTO tallas VALUES('3','M');
INSERT INTO tallas VALUES('4','L');
INSERT INTO tallas VALUES('5','XL');
INSERT INTO tallas VALUES('6','XXL');


CREATE TABLE `task_list` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `project_id` int(30) NOT NULL,
  `task` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `status` tinyint(4) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO task_list VALUES('1','1','Sample Task 1','								&lt;span style=&quot;color: rgb(0, 0, 0); font-family: &amp;quot;Open Sans&amp;quot;, Arial, sans-serif; font-size: 14px; text-align: justify;&quot;&gt;Fusce ullamcorper mattis semper. Nunc vel risus ipsum. Sed maximus dapibus nisl non laoreet. Pellentesque quis mauris odio. Donec fermentum facilisis odio, sit amet aliquet purus scelerisque eget.&amp;nbsp;&lt;/span&gt;													','3','2020-12-03 11:08:58');
INSERT INTO task_list VALUES('2','1','Sample Task 2','Sample Task 2							','1','2020-12-03 13:50:15');
INSERT INTO task_list VALUES('3','2','Task Test','Sample','1','2020-12-03 13:52:25');
INSERT INTO task_list VALUES('4','2','test 23','Sample test 23','1','2020-12-03 13:52:40');


CREATE TABLE `tblcategory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `CategoryName` varchar(255) NOT NULL,
  `Description` text DEFAULT NULL,
  `PostingDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NULL DEFAULT NULL,
  `Is_Active` int(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO tblcategory VALUES('1','Eventos Escolares','Eventos y actividades especiales organizadas por la institución educativa','2025-08-31 11:20:33','','1');
INSERT INTO tblcategory VALUES('2','Logros Académicos','Reconocimientos y logros alcanzados por estudiantes y profesores','2025-08-31 12:46:33','','1');
INSERT INTO tblcategory VALUES('3','Actividades Deportivas','Competencias, torneos y actividades deportivas escolares','2025-08-31 12:46:43','','1');
INSERT INTO tblcategory VALUES('4','Talleres y Capacitaciones','Talleres, seminarios y programas de capacitación para la comunidad educativa','2025-08-31 12:46:54','','1');
INSERT INTO tblcategory VALUES('5','Proyectos Estudiantiles','Proyectos innovadores desarrollados por los estudiantes','2025-08-31 12:47:07','','1');
INSERT INTO tblcategory VALUES('6','Cultura y Arte','Actividades culturales, artísticas y presentaciones estudiantiles','2025-08-31 12:47:15','','1');
INSERT INTO tblcategory VALUES('7','Anuncios Importantes','Comunicados oficiales y anuncios de la dirección escolar','2025-08-31 12:47:25','','1');
INSERT INTO tblcategory VALUES('8','Voluntariado y Servicio','Actividades de servicio comunitario y programas de voluntariado','2025-08-31 12:47:37','','1');
INSERT INTO tblcategory VALUES('9','Tecnología Educativa','Avances tecnológicos y recursos digitales para el aprendizaje','2025-08-31 12:47:49','','1');
INSERT INTO tblcategory VALUES('10','Investigación Científica','Proyectos de investigación y ferias científicas estudiantiles','2025-08-31 12:47:57','','1');


CREATE TABLE `tblcomments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `postId` char(11) DEFAULT NULL,
  `name` varchar(120) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `comment` mediumtext DEFAULT NULL,
  `postingDate` timestamp NULL DEFAULT current_timestamp(),
  `status` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;



CREATE TABLE `tblposts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `PostTitle` longtext DEFAULT NULL,
  `CategoryId` int(11) DEFAULT NULL,
  `SubCategoryId` int(11) DEFAULT NULL,
  `PostDetails` longtext CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `PostingDate` timestamp NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `Is_Active` int(1) DEFAULT NULL,
  `PostUrl` mediumtext DEFAULT NULL,
  `PostImage` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO tblposts VALUES('1','Inauguración del Nuevo Laboratorio de Ciencias','1','1','El pasado viernes se inauguró oficialmente el nuevo laboratorio de ciencias equipado con tecnología de última generación para beneficio de nuestros estudiantes.','2024-01-15 10:00:00','2025-10-13 00:31:25','1','inauguracion-laboratorio-ciencias','ciencias1.png');
INSERT INTO tblposts VALUES('2','Ganadores del Concurso de Matemáticas Regional','2','3','Nuestros estudiantes obtuvieron el primer lugar en el concurso regional de matemáticas, demostrando excelencia académica.','2024-01-12 14:30:00','2025-10-13 01:01:16','1','ganadores-concurso-matematicas','matematicas1.png');
INSERT INTO tblposts VALUES('3','Charla sobre Orientación Vocacional para Bachilleres','3','5','Programa especial de orientación vocacional para estudiantes de último año, con participación de universidades locales.','2024-01-10 09:00:00','2025-10-13 00:12:53','1','charla-orientacion-vocacional','orientacion1.png');
INSERT INTO tblposts VALUES('4','Festival Deportivo Interescolar 2024','4','7','Gran éxito del festival deportivo que reunió a más de 15 instituciones educativas en competencias amistosas.','2024-01-08 16:45:00','2025-10-13 00:58:27','1','festival-deportivo-interescolar-2024','deportes1.png');
INSERT INTO tblposts VALUES('5','Nuevo Programa de Inglés Intensivo','5','9','Implementación del programa de inglés intensivo con metodología comunicativa para todos los niveles.','2024-01-05 11:20:00','2025-10-13 00:56:10','1','programa-ingles-intensivo','ingles1.png');
INSERT INTO tblposts VALUES('6','Celebración del Día del Maestro','6','11','Emotiva celebración en honor a nuestros docentes, reconociendo su invaluable labor educativa.','2024-01-03 08:00:00','2025-10-13 00:23:51','1','celebracion-dia-del-maestro','maestros1.png');
INSERT INTO tblposts VALUES('7','Proyecto Ecológico: Huerto Escolar','7','13','Los estudiantes implementaron un huerto escolar como parte del proyecto de conciencia ambiental.','2023-12-28 13:15:00','2025-10-13 00:19:45','1','proyecto-ecologico-huerto-escolar','ecologia1.png');
INSERT INTO tblposts VALUES('8','Concierto de Navidad del Coro Estudiantil','8','15','El coro estudiantil presentó un emotivo concierto navideño ante la comunidad educativa.','2023-12-20 18:30:00','2025-10-13 00:49:52','1','concierto-navidad-coro-estudiantil','musica1.png');
INSERT INTO tblposts VALUES('9','Taller de Robótica para Primaria','1','2','Introducción a la robótica educativa para estudiantes de primaria, desarrollando habilidades STEM.','2023-12-18 10:00:00','2025-10-13 00:28:24','1','taller-robotica-primaria','robotica1.png');
INSERT INTO tblposts VALUES('10','Convenio con Universidad Nacional','3','6','Firma de convenio que beneficiará a nuestros bachilleres con acceso preferencial a la universidad.','2023-12-15 12:00:00','2025-10-13 00:08:17','1','convenio-universidad-nacional','universidad1.png');
INSERT INTO tblposts VALUES('11','Exposición de Arte Estudiantil','9','17','Exhibición de trabajos artísticos realizados por estudiantes durante el semestre.','2023-12-12 15:30:00','2025-10-13 00:47:09','1','exposicion-arte-estudiantil','arte1.png');
INSERT INTO tblposts VALUES('12','Campamento de Liderazgo Juvenil','10','19','Estudiantes participaron en campamento para desarrollar habilidades de liderazgo y trabajo en equipo.','2023-12-10 07:00:00','2025-10-13 00:44:41','1','campamento-liderazgo-juvenil','liderazgo1.png');
INSERT INTO tblposts VALUES('13','Mejoras en la Infraestructura Deportiva','4','8','Completadas las mejoras en canchas deportivas e instalaciones para educación física.','2023-12-08 14:00:00','2025-10-13 00:42:55','1','mejoras-infraestructura-deportiva','deportes2.png');
INSERT INTO tblposts VALUES('14','Charla sobre Salud Mental Adolescente','11','21','Especialistas en psicología adolescente brindaron charlas sobre manejo del estrés y salud mental.','2023-12-05 11:00:00','2025-10-13 00:40:21','1','charla-salud-mental-adolescente','salud1.png');
INSERT INTO tblposts VALUES('15','Competencia de Spelling Bee 2023','5','10','Finalizó con éxito la competencia anual de Spelling Bee con participación récord de estudiantes.','2023-12-03 09:30:00','2025-10-13 00:36:14','1','competencia-spelling-bee-2023','ingles2.png');
INSERT INTO tblposts VALUES('16','Proyecto de Reciclaje Escolar','7','14','Iniciativa estudiantil logró recolectar más de 500 kg de material reciclable en un mes.','2023-11-30 13:45:00','2025-10-13 00:17:24','1','proyecto-reciclaje-escolar','ecologia2.png');
INSERT INTO tblposts VALUES('17','Visita al Museo de Ciencias Naturales','1','1','Estudiantes de secundaria realizaron visita educativa al museo como complemento a sus clases.','2023-11-28 08:30:00','2025-10-13 00:26:06','1','visita-museo-ciencias-naturales','ciencias2.png');
INSERT INTO tblposts VALUES('18','Festival de Talentos Estudiantiles','12','23','Descubrimiento de talentos ocultos en música, baile, teatro y otras expresiones artísticas.','2023-11-25 17:00:00','2025-10-13 00:33:42','1','festival-talentos-estudiantiles','talento1.png');
INSERT INTO tblposts VALUES('19','Programa de Refuerzo Académico','2','4','Implementación de programa de refuerzo para estudiantes que requieren apoyo adicional.','2023-11-22 15:00:00','2025-10-13 00:02:46','1','programa-refuerzo-academico','academico1.png');
INSERT INTO tblposts VALUES('20','Ceremonia de Graduación 2023','3','5','Emotiva ceremonia de graduación para la promoción 2023, con 98% de aprobados.','2023-11-20 19:00:00','2025-10-03 14:57:04','1','ceremonia-graduacion-2023','graduacion1.png');


CREATE TABLE `tblsubcategory` (
  `SubCategoryId` int(11) NOT NULL AUTO_INCREMENT,
  `CategoryId` int(11) NOT NULL,
  `Subcategory` varchar(255) NOT NULL,
  `SubCatDescription` text DEFAULT NULL,
  `PostingDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NULL DEFAULT NULL,
  `Is_Active` int(1) DEFAULT 1,
  PRIMARY KEY (`SubCategoryId`),
  KEY `CategoryId` (`CategoryId`),
  CONSTRAINT `tblsubcategory_ibfk_1` FOREIGN KEY (`CategoryId`) REFERENCES `tblcategory` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO tblsubcategory VALUES('1','1','Festivales Anuales','Celebraciones y festivales tradicionales de la institución como día del estudiante, aniversario, etc.','2025-08-31 11:21:26','','1');
INSERT INTO tblsubcategory VALUES('2','1','Ceremonias de Graduación','Eventos de graduación y entrega de diplomas para diferentes niveles educativos','2025-08-31 12:51:55','','1');
INSERT INTO tblsubcategory VALUES('3','2','Olimpiadas del Conocimiento','Participación y resultados en competencias académicas intercolegiales','2025-08-31 12:52:10','','1');
INSERT INTO tblsubcategory VALUES('4','2','Reconocimientos Honoríficos','Premios y distinciones a estudiantes y docentes por excelencia académica','2025-08-31 12:52:25','','1');
INSERT INTO tblsubcategory VALUES('5','3','Torneos Intercursos','Competencias deportivas entre diferentes cursos y grados','2025-08-31 12:52:40','','1');
INSERT INTO tblsubcategory VALUES('6','3','Juegos Intercolegiales','Participación en competencias deportivas con otras instituciones','2025-08-31 12:52:55','','1');
INSERT INTO tblsubcategory VALUES('7','4','Talleres para Padres','Programas de capacitación y orientación para padres de familia','2025-08-31 12:53:10','','1');
INSERT INTO tblsubcategory VALUES('8','4','Desarrollo Docente','Capacitaciones y actualizaciones para el personal docente','2025-08-31 12:53:25','','1');
INSERT INTO tblsubcategory VALUES('9','5','Ferias de Ciencias','Exposición de proyectos científicos y tecnológicos estudiantiles','2025-08-31 12:53:40','','1');
INSERT INTO tblsubcategory VALUES('10','5','Emprendimiento Estudiantil','Proyectos de emprendimiento desarrollados por los estudiantes','2025-08-31 12:53:55','','1');
INSERT INTO tblsubcategory VALUES('11','6','Presentaciones Artísticas','Shows de teatro, danza, música y otras expresiones artísticas','2025-08-31 12:54:10','','1');
INSERT INTO tblsubcategory VALUES('12','6','Exposiciones Culturales','Exhibiciones de arte, fotografía y trabajos creativos estudiantiles','2025-08-31 12:54:25','','1');
INSERT INTO tblsubcategory VALUES('13','7','Convocatorias Oficiales','Llamados y convocatorias oficiales de la dirección académica','2025-08-31 12:54:40','','1');
INSERT INTO tblsubcategory VALUES('14','7','Cambios de Horario','Avisos sobre modificaciones en horarios y calendarios académicos','2025-08-31 12:54:55','','1');
INSERT INTO tblsubcategory VALUES('15','8','Programas de Voluntariado','Oportunidades de servicio comunitario y voluntariado estudiantil','2025-08-31 12:55:10','','1');
INSERT INTO tblsubcategory VALUES('16','8','Proyectos Sociales','Iniciativas de apoyo a la comunidad y proyectos de impacto social','2025-08-31 12:55:25','','1');
INSERT INTO tblsubcategory VALUES('17','9','Plataformas Digitales','Implementación y uso de nuevas plataformas educativas digitales','2025-08-31 12:55:40','','1');
INSERT INTO tblsubcategory VALUES('18','9','Recursos Educativos','Nuevos recursos tecnológicos y herramientas digitales para el aprendizaje','2025-08-31 12:55:55','','1');
INSERT INTO tblsubcategory VALUES('19','10','Proyectos de Investigación','Investigaciones científicas desarrolladas por estudiantes y docentes','2025-08-31 12:56:10','','1');
INSERT INTO tblsubcategory VALUES('20','10','Publicaciones Académicas','Artículos y trabajos de investigación publicados por la comunidad educativa','2025-08-31 12:56:25','','1');


CREATE TABLE `team_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `position_order` int(11) DEFAULT 0,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `tipos_constancia` (
  `id_tipo_constancia` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_tipo_constancia` varchar(255) NOT NULL,
  `descripcion_tipo_constancia` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_tipo_constancia`),
  UNIQUE KEY `nombre_tipo_constancia` (`nombre_tipo_constancia`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO tipos_constancia VALUES('1','Constancia de Estudio','','2025-04-19 23:35:35');
INSERT INTO tipos_constancia VALUES('2','Constancia de Conducta','','2025-04-19 23:35:35');
INSERT INTO tipos_constancia VALUES('3','Constancia de Notas','','2025-04-19 23:35:35');
INSERT INTO tipos_constancia VALUES('4','Constancia de Matrícula','','2025-04-19 23:35:35');
INSERT INTO tipos_constancia VALUES('5','Constancia de Regularidad','','2025-04-19 23:35:35');


CREATE TABLE `turnos` (
  `id_turno` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_turno` varchar(50) NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_turno`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO turnos VALUES('1','Mañana','activo','2025-01-09 17:28:38','2025-01-09 17:28:38');
INSERT INTO turnos VALUES('2','Tarde','activo','2025-01-09 17:28:38','2025-01-09 17:28:38');


CREATE TABLE `user_productivity` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `project_id` int(30) NOT NULL,
  `task_id` int(30) NOT NULL,
  `comment` text NOT NULL,
  `subject` varchar(200) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `user_id` int(30) NOT NULL,
  `time_rendered` float NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO user_productivity VALUES('1','1','1','							&lt;p&gt;Sample Progress&lt;/p&gt;&lt;ul&gt;&lt;li&gt;Test 1&lt;/li&gt;&lt;li&gt;Test 2&lt;/li&gt;&lt;li&gt;Test 3&lt;/li&gt;&lt;/ul&gt;																			','Sample Progress','2020-12-03','08:00:00','10:00:00','1','2','2020-12-03 12:13:28');
INSERT INTO user_productivity VALUES('2','1','1','							Sample Progress						','Sample Progress 2','2020-12-03','13:00:00','14:00:00','1','1','2020-12-03 13:48:28');
INSERT INTO user_productivity VALUES('3','1','2','							Sample						','Test','2020-12-03','08:00:00','09:00:00','5','1','2020-12-03 13:57:22');
INSERT INTO user_productivity VALUES('4','1','2','asdasdasd','Sample Progress','2020-12-02','08:00:00','10:00:00','2','2','2020-12-03 14:36:30');


CREATE TABLE `users` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(200) NOT NULL,
  `lastname` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` text NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 2 COMMENT '1 = admin, 2 = staff',
  `avatar` text NOT NULL DEFAULT 'no-image-available.png',
  `status` int(11) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO users VALUES('1','Administrator','','admin@admin.com','0192023a7bbd73250516f069df18b500','1','no-image-available.png','1','2020-11-26 10:57:04');
INSERT INTO users VALUES('2','John','Smith','jsmith@sample.com','1254737c076cf867dc53d60a0364f38e','2','1606978560_avatar.jpg','1','2020-12-03 09:26:03');
INSERT INTO users VALUES('3','Claire','Blake','cblake@sample.com','4744ddea876b11dcb1d169fadf494418','3','1606958760_47446233-clean-noir-et-gradient-sombre-image-de-fond-abstrait-.jpg','1','2020-12-03 09:26:42');
INSERT INTO users VALUES('4','George','Wilson','gwilson@sample.com','d40242fb23c45206fadee4e2418f274f','3','1606963560_avatar.jpg','1','2020-12-03 10:46:41');
INSERT INTO users VALUES('5','Mike','Williams','mwilliams@sample.com','3cc93e9a6741d8b40460457139cf8ced','3','1606963620_47446233-clean-noir-et-gradient-sombre-image-de-fond-abstrait-.jpg','1','2020-12-03 10:47:06');


CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `rol_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` text NOT NULL,
  `fyh_creacion` datetime DEFAULT NULL,
  `fyh_actualizacion` datetime DEFAULT NULL,
  `estado` varchar(11) DEFAULT NULL,
  `token_recuperacion` varchar(255) DEFAULT NULL,
  `expiracion_token` datetime DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `email` (`email`),
  KEY `rol_id` (`rol_id`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO usuarios VALUES('1','1','admin@gmail.com','$2y$10$NVhkeupcyKUPFqx.l7t7n.qELV7X5LxKjmOV3WwyRQ3CfJquHF0P2','2023-12-28 20:29:10','2024-11-12 00:00:00','1','','');
INSERT INTO usuarios VALUES('76','5','heldyndiaz19@gmail.com','$2y$10$EJRIZsv.M5k1SXp5Rz47T.HuZGCVbT9oagqqyxB2ylyQzBvDlzusK','2025-10-20 16:02:12','2025-11-04 16:51:19','1','0917387d4ab86bf05fa79e85f65c0569e0dc188b27f3836e31545e6ee2d11ad3','2025-11-04 00:40:15');
INSERT INTO usuarios VALUES('77','5','dv47762@gmail.com','$2y$10$36FfPxYkOCeTBxp34AA4Lu0zv5h3wpHOr1ri5EILyuK2W1HmTbdaK','2025-10-21 20:23:56','2025-11-04 15:32:25','1','e1956262ab938136fa0ae9c3fd16dc121c7924079ddad51e081be1b29fe2e4ca','2025-11-04 16:51:17');
INSERT INTO usuarios VALUES('79','2','keila@gmail.com','$2y$10$ZBdrzS3ErZW3h2FHN8bCrOmkXADLvdj3mHWTSuYhUMOqcCj7nk8Tq','2025-10-31 00:00:00','2025-11-04 00:00:00','1','','');
INSERT INTO usuarios VALUES('80','2','heldyndiaz@gmail.com','$2y$10$KaHW.NQX2HNuEZaMtrkwPOxPkEGiE06N7W3vivQn4hNsWztAha/Lq','2025-10-31 00:00:00','2025-11-03 00:00:00','1','','');
