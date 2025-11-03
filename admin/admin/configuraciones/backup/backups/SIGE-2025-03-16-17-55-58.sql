

CREATE TABLE `administrativos` (
  `id_administrativo` int(11) NOT NULL AUTO_INCREMENT,
  `persona_id` int(11) NOT NULL,
  `fyh_creacion` datetime DEFAULT NULL,
  `fyh_actualizacion` datetime DEFAULT NULL,
  `estado` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id_administrativo`),
  KEY `persona_id` (`persona_id`),
  CONSTRAINT `administrativos_ibfk_1` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id_persona`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO administrativos VALUES('1','4','2024-10-27 00:00:00','','1');
INSERT INTO administrativos VALUES('2','22','2024-12-26 00:00:00','','1');
INSERT INTO administrativos VALUES('4','24','2025-01-24 00:00:00','','0');
INSERT INTO administrativos VALUES('5','26','2025-01-24 00:00:00','2025-02-08 00:00:00','1');
INSERT INTO administrativos VALUES('6','27','2025-01-24 00:00:00','','1');


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

INSERT INTO configuracion_instituciones VALUES('1','U.E ANGUSTIN ZAMORA QUINTANA','2025-01-17-19-08-03agustinzamora.jpg','','Distrito Capital, Calle Circunvalacion, Urb San Maritn I, Frente a la plaza San Martin.','02124331080','04124331080','agustinzamora@gmail.com','2023-12-28 20:29:10','2025-01-22 00:00:00','1');


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

INSERT INTO docentes VALUES('2','3','INFORMATICA','2 años','2024-10-27','2024-10-30','1');
INSERT INTO docentes VALUES('3','14','Licenciado ed','5','2024-10-28','','1');


CREATE TABLE `documento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(250) NOT NULL,
  `archivo` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



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
  PRIMARY KEY (`id_estudiante`),
  KEY `id_representante` (`id_representante`),
  KEY `turno_id` (`turno_id`),
  CONSTRAINT `estudiantes_ibfk_1` FOREIGN KEY (`id_representante`) REFERENCES `representantes` (`id_representante`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO estudiantes VALUES('1','V','30045678','','0','Carlos Eduardo','Pérez López','2015-05-10','masculino','carloseduardo15@gmail.com','San Juan de Los Morros','0412-1234567','1','1','inactivo','2025-01-24 08:27:15','2025-01-24 21:57:29','ninguna');
INSERT INTO estudiantes VALUES('2','V','30045679','','0','Ana Lucía','Pérez López','2016-06-15','femenino','analucia15@gmail.com','El Junquito','0414-1234568','1','1','inactivo','2025-01-24 08:27:15','2025-01-24 21:57:56','ninguna');
INSERT INTO estudiantes VALUES('3','V','30045680','','0','Luis Miguel','Pérez López','2017-07-20','masculino','luismiguel15@gmail.com','La Candelaria','0416-1234569','1','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('4','V','31234567','','0','María Fernanda','González Torres','2015-08-25','femenino','mariafernanda31@gmail.com','Santa Teresa','0424-1234570','2','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('5','V','31234568','','0','Diego Alejandro','González Torres','2016-09-30','masculino','diegoalejandro31@gmail.com','Los Palos Grandes','04164634936','2','1','activo','2025-01-24 08:27:15','2025-02-07 23:28:44','ninguna');
INSERT INTO estudiantes VALUES('6','V','31234569','','0','Sofía Alejandra','González Torres','2017-10-10','femenino','sofiaalejandra31@gmail.com','El Hatillo','0412-1234572','2','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('7','V','32135798','','0','Javier Alejandro','Martínez Ruiz','2015-11-10','masculino','javieralejandro32@gmail.com','Caricuao','0414-1234580','3','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('8','V','32135799','','0','Lucía Fernanda','Martínez Ruiz','2016-12-15','femenino','luciafernanda32@gmail.com','Los Rosales','0416-1234581','3','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('9','V','32135800','','0','María José','Martínez Ruiz','2017-01-20','femenino','mariajose32@gmail.com','Coche','0424-1234582','3','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('10','V','33345678','','0','Carlos Andrés','Díaz López','2015-05-30','masculino','carlosandres34@gmail.com','La Vega','0426-1234590','4','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('11','V','33345679','','0','Isabella','Díaz López','2016-06-25','femenino','isabelladiaz34@gmail.com','Los Teques','0412-1234591','4','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('12','V','33345680','','0','Santiago','Díaz López','2017-07-15','masculino','santiagodiaz34@gmail.com','Chacao','0414-1234592','4','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('13','V','30167891','','0','Valentina','Hernández García','2015-08-25','femenino','valentinahernandez30@gmail.com','Los Dos Caminos','0416-1234593','5','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('14','V','30167892','','0','Fernando','Hernández García','2016-09-20','masculino','fernandohernandez30@gmail.com','El Paraíso','0424-1234501','5','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('15','V','30167893','','0','Gabriel','Hernández García','2017-10-15','masculino','gabrielhernandez30@gmail.com','Las Mercedes','0426-1234502','5','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('16','V','31890123','','0','Mateo','Ramírez Fernández','2015-11-30','masculino','mateoramirez31@gmail.com','Sabana Grande','0412-1234503','6','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('17','V','31890124','','0','Camila','Ramírez Fernández','2016-12-31','femenino','camilaramirez31@gmail.com','Los Chaguaramos','0414-1234504','6','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('18','V','31890125','','0','Diego','Ramírez Fernández','2017-01-15','masculino','diegoramirez31@gmail.com','Catia','0416-1234505','6','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('19','V','32678910','','0','Camilo','Morales López','2015-02-25','masculino','camilomorales32@gmail.com','La Urbina','0424-1234506','7','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('20','V','32678911','','0','Natalia','Morales López','2016-03-16','femenino','nataliamorales32@gmail.com','Boleíta','0426-1234507','7','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('21','V','32678912','','0','Arturo','Morales López','2017-04-17','masculino','arturomorales32@gmail.com','El Cafetal','0412-1234508','7','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('22','V','33789012','','0','Leo','Ortega Medina','2015-05-18','masculino','leootegamedina33@gmail.com','Catedral','0414-1234509','8','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('23','V','33789013','','0','Valeria','Ortega Medina','2016-06-29','femenino','valeriaortegamedina33@gmail.com','Calle Real','0416-1234510','8','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('24','V','33789014','','0','Esteban','Ortega Medina','2017-07-10','masculino','estebanortegamedina33@gmail.com','San Bernardino','0424-1234511','8','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('25','V','30231456','','0','Claudia','Chapman Ruiz','2015-08-26','femenino','claudiachapman30@gmail.com','Los Palos Grandes','0426-1234512','9','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('26','V','30231457','','0','Felipe','Chapman Ruiz','2016-09-12','masculino','felipechapman30@gmail.com','Plaza Venezuela','0412-1234513','9','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('27','V','30231458','','0','Juan','Chapman Ruiz','2017-10-05','masculino','juanchapman30@gmail.com','Miranda','0414-1234514','9','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('28','V','31567890','','0','Simón','Salazar Pérez','2015-11-11','masculino','simonsalazar31@gmail.com','Catia La Mar','0416-1234515','10','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('29','V','31567891','','0','María','Salazar Pérez','2016-12-12','femenino','mariasalazar31@gmail.com','Tarqui','0424-1234516','10','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('30','V','31567892','','0','Leonardo','Salazar Pérez','2017-01-13','masculino','leonardosalazar31@gmail.com','La Grita','0426-1234517','10','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('31','V','32987654','','0','Estefanía','Carrillo Martínez','2015-02-14','femenino','estefaniacarrillo32@gmail.com','El Valle','0412-1234518','11','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('32','V','32987655','','0','Diego','Carrillo Martínez','2016-03-15','masculino','diegocarrillo32@gmail.com','La Bandera','0414-1234519','11','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('33','V','32987656','','0','Gabriela','Carrillo Martínez','2017-04-16','femenino','gabrielacarrillo32@gmail.com','Antímano','0416-1234520','11','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('34','V','33555555','','0','Pablo','García López','2015-02-14','masculino','pablogarcia33@gmail.com','Río de Janeiro','0412-1234571','12','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('35','V','33555556','','0','Laura','García López','2016-03-15','femenino','lauragarcia33@gmail.com','Avenida Bolívar','0414-1234572','12','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('36','V','33555557','','0','Ricardo','García López','2017-04-16','masculino','ricardogarcia33@gmail.com','Bello Campo','0416-1234573','12','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('37','V','30654321','','0','Martina','Blanco Rodríguez','2015-05-18','femenino','martinablancor33@gmail.com','La Yaguara','0424-1234581','13','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('38','V','30654322','','0','Santiago','Blanco Rodríguez','2016-06-21','masculino','santiagoblanco33@gmail.com','Tamanaco','0426-1234582','13','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('39','V','30654323','','0','Gabriela','Blanco Rodríguez','2017-07-24','femenino','gabrielablanco33@gmail.com','Montalbán','0412-1234583','13','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('40','V','31112233','','0','Fernando','Castillo Mendoza','2015-08-26','masculino','fernandocastillo33@gmail.com','Las Acacias','0414-1234591','14','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('41','V','31112234','','0','Maria','Castillo Mendoza','2016-09-27','femenino','mariacastillo33@gmail.com','Palo Verde','0416-1234592','14','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('42','V','31112235','','0','Javier','Castillo Mendoza','2017-10-18','masculino','javiercastillo33@gmail.com','Cerro Verde','0424-1234593','14','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('43','V','32443322','','0','Esteban','Rivas Araujo','2015-11-30','masculino','estebanrivas33@gmail.com','Los Teques','0426-1234501','15','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('44','V','32443323','','0','Anabella','Rivas Araujo','2016-12-12','femenino','anabellarivas33@gmail.com','Baruta','0412-1234502','15','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('45','V','32443324','','0','Joaquín','Rivas Araujo','2017-01-18','masculino','joaquinrivas33@gmail.com','Guarenas','0414-1234503','15','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('46','V','33334455','','0','Marisol','Soto Castillo','2015-02-17','femenino','marisolsotoc33@gmail.com','Santa Fe','0416-1234501','16','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('47','V','33334456','','0','Ramón','Soto Castillo','2016-03-31','masculino','ramonsotoc33@gmail.com','La Trinidad','0424-1234502','16','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('48','V','33334457','','0','Virginia','Soto Castillo','2017-05-18','femenino','virginiasotoc33@gmail.com','La Candelaria','0426-1234503','16','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('49','V','30112233','','0','Óscar','Vásquez Pérez','2015-06-30','masculino','oscargasquez33@gmail.com','Peñalver','0412-1234501','17','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('50','V','30112234','','0','Evelyn','Vásquez Pérez','2016-07-14','femenino','evelynvasquez33@gmail.com','Antímano','0414-1234502','17','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('51','V','30112235','','0','Mateo','Vásquez Pérez','2017-08-19','masculino','mateovasquez33@gmail.com','Cantaura','0416-1234503','17','1','activo','2025-01-24 08:27:15','2025-01-24 08:47:31','ninguna');
INSERT INTO estudiantes VALUES('56','V','33200918','','0','Misael David','Marquez Cruz','2019-05-24','masculino','misaelmarquez@gmail.com','Parroquia Caricuao Ud1','04121988817','113','0','activo','2025-01-24 07:23:20','2025-01-24 07:23:20','ninguna');
INSERT INTO estudiantes VALUES('61','V','33200919','','0','Juan Carlos','Pérez López','2018-04-15','masculino','juancarlos@gmail.com','Parroquia Caricuao Ud1','04121234567','113','0','activo','2025-01-24 07:30:25','2025-01-24 07:30:25','ninguna');
INSERT INTO estudiantes VALUES('62','V','33200920','','0','Ana María','González Torres','2017-03-10','femenino','anagonzalez@gmail.com','Parroquia Caricuao Ud1','04121234568','113','0','activo','2025-01-24 07:30:25','2025-01-24 07:30:25','ninguna');
INSERT INTO estudiantes VALUES('63','V','33200921','','0','Luis Fernando','Martínez Ruiz','2016-02-20','masculino','luismartinez@gmail.com','Parroquia Caricuao Ud1','04121234569','113','0','activo','2025-01-24 07:30:25','2025-01-24 07:30:25','ninguna');
INSERT INTO estudiantes VALUES('64','V','','V21914756124','2','Sofía Isabel','Ramírez López','2019-01-15','femenino','sofiaramirez@gmail.com','Parroquia Caricuao Ud1','04121234570','113','0','activo','2025-01-24 07:30:25','2025-01-24 07:33:09','ninguna');
INSERT INTO estudiantes VALUES('65','V','33200922','','0','Carlos Alberto','Hernández Pérez','2018-05-10','masculino','carloshp@gmail.com','Parroquia Caricuao Ud1','04121234571','105','0','activo','2025-01-24 07:47:17','2025-01-24 07:47:17','ninguna');
INSERT INTO estudiantes VALUES('66','V','33200923','','0','María José','López García','2017-06-15','femenino','mariajose@gmail.com','Parroquia Caricuao Ud1','04121234572','105','0','activo','2025-01-24 07:47:17','2025-01-24 07:47:17','ninguna');
INSERT INTO estudiantes VALUES('67','V','33200924','','0','Andrés Felipe','Martínez Torres','2016-07-20','masculino','andresfelipe@gmail.com','Parroquia Caricuao Ud1','04121234573','105','0','activo','2025-01-24 07:47:17','2025-01-24 07:47:17','ninguna');
INSERT INTO estudiantes VALUES('68','V','33200925','','0','Isabella','Ramírez López','2019-08-25','femenino','isabellar@gmail.com','Parroquia Caricuao Ud1','04121234574','105','0','activo','2025-01-24 07:47:17','2025-01-24 07:47:17','ninguna');
INSERT INTO estudiantes VALUES('69','V','33200926','','0','Diego Alejandro','González Ruiz','2015-09-30','masculino','diegoalejandro@gmail.com','Parroquia Caricuao Ud1','04121234575','105','0','activo','2025-01-24 07:47:17','2025-01-24 07:47:17','ninguna');
INSERT INTO estudiantes VALUES('75','V','31982330','','0','Fernando José','Pérez Martínez','2018-01-10','masculino','fernandoj@gmail.com','Parroquia Caricuao Ud1','04121234581','114','0','activo','2025-01-24 07:54:21','2025-01-24 07:54:21','ninguna');
INSERT INTO estudiantes VALUES('76','V','31982331','','0','Lucía Fernanda','González Torres','2017-02-15','femenino','luciafernanda@gmail.com','Parroquia Caricuao Ud1','04121234582','114','0','activo','2025-01-24 07:54:21','2025-01-24 07:54:21','ninguna');
INSERT INTO estudiantes VALUES('77','V','31982332','','0','Javier Alejandro','Martínez López','2016-03-20','masculino','javieralejandro@gmail.com','Parroquia Caricuao Ud1','04121234583','114','0','activo','2025-01-24 07:54:21','2025-01-24 07:54:21','ninguna');
INSERT INTO estudiantes VALUES('78','V','','V21911985583','2','Sofía Valentina','Ramírez Pérez','2019-04-25','femenino','sofiavalentina@gmail.com','Parroquia Caricuao Ud1','04121234584','114','0','activo','2025-01-24 07:54:21','2025-01-24 07:54:38','ninguna');
INSERT INTO estudiantes VALUES('79','V','','V11511985583','1','Diego Armando','Hernández Ruiz','2015-05-30','masculino','diegoarmando@gmail.com','Parroquia Caricuao Ud1','04121234585','114','0','activo','2025-01-24 07:54:21','2025-01-24 07:55:23','ninguna');


CREATE TABLE `gestiones` (
  `id_gestion` int(11) NOT NULL AUTO_INCREMENT,
  `desde` date NOT NULL,
  `hasta` date NOT NULL,
  `fyh_creacion` date DEFAULT NULL,
  `fyh_actualizacion` date DEFAULT NULL,
  `estado` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id_gestion`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO gestiones VALUES('1','2025-07-13','2026-05-18','2023-12-28','2025-01-24','1');
INSERT INTO gestiones VALUES('32','2025-01-24','2026-01-23','2025-01-24','','0');


CREATE TABLE `grados` (
  `id_grado` int(11) NOT NULL AUTO_INCREMENT,
  `nivel` varchar(20) NOT NULL,
  `grado` varchar(20) NOT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT 1,
  `fyh_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_grado`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO grados VALUES('19','Inicial','Primer Nivel','1','2025-01-21 09:00:55');
INSERT INTO grados VALUES('20','Inicial','Segundo Nivel','1','2025-01-21 09:00:55');
INSERT INTO grados VALUES('21','Inicial','Tercer Nivel','1','2025-01-21 09:00:55');
INSERT INTO grados VALUES('22','Primaria','Primer Grado','1','2025-01-21 09:00:55');
INSERT INTO grados VALUES('23','Primaria','Segundo Grado','1','2025-01-21 09:00:55');
INSERT INTO grados VALUES('24','Primaria','Tercer Grado','1','2025-01-21 09:00:55');
INSERT INTO grados VALUES('25','Primaria','Cuarto Grado','1','2025-01-21 09:00:55');
INSERT INTO grados VALUES('26','Primaria','Quinto Grado','1','2025-01-21 09:00:55');
INSERT INTO grados VALUES('27','Primaria','Sexto Grado','1','2025-01-21 09:00:55');


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
) ENGINE=InnoDB AUTO_INCREMENT=226 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO inscripciones VALUES('190','1','Primaria','22','A','M','S','10','24','1','2025-01-24 14:38:16','2025-01-24 14:38:16','1','48');
INSERT INTO inscripciones VALUES('191','1','Primaria','22','A','M','M','14','26','2','2025-01-24 14:38:16','2025-01-24 14:38:16','1','48');
INSERT INTO inscripciones VALUES('192','1','Primaria','22','B','M','L','16','28','3','2025-01-24 14:38:16','2025-01-24 14:38:16','1','49');
INSERT INTO inscripciones VALUES('193','1','Primaria','22','B','M','XL','26','30','4','2025-01-24 14:38:16','2025-01-24 14:38:16','1','49');
INSERT INTO inscripciones VALUES('194','1','Primaria','22','C','M','XS','28','32','5','2025-01-24 14:38:16','2025-01-24 14:38:16','1','50');
INSERT INTO inscripciones VALUES('195','1','Primaria','22','C','M','SS','30','24','6','2025-01-24 14:38:16','2025-01-24 14:38:16','1','50');
INSERT INTO inscripciones VALUES('196','1','Primaria','23','A','M','S','10','24','7','2025-01-24 14:38:16','2025-01-24 14:38:16','1','54');
INSERT INTO inscripciones VALUES('197','1','Primaria','23','A','M','M','14','26','8','2025-01-24 14:38:16','2025-01-24 14:38:16','1','54');
INSERT INTO inscripciones VALUES('198','1','Primaria','23','B','M','L','16','28','9','2025-01-24 14:38:16','2025-01-24 14:38:16','1','55');
INSERT INTO inscripciones VALUES('199','1','Primaria','23','B','M','XL','26','30','10','2025-01-24 14:38:16','2025-01-24 14:38:16','1','55');
INSERT INTO inscripciones VALUES('200','1','Primaria','23','C','M','XS','28','32','11','2025-01-24 14:38:16','2025-01-24 14:38:16','1','56');
INSERT INTO inscripciones VALUES('201','1','Primaria','23','C','M','SS','30','24','12','2025-01-24 14:38:16','2025-01-24 14:38:16','1','56');
INSERT INTO inscripciones VALUES('202','1','Primaria','24','A','M','S','10','24','13','2025-01-24 14:38:17','2025-01-24 14:38:17','1','60');
INSERT INTO inscripciones VALUES('203','1','Primaria','24','A','M','M','14','26','14','2025-01-24 14:38:17','2025-01-24 14:38:17','1','60');
INSERT INTO inscripciones VALUES('204','1','Primaria','24','B','M','L','16','28','15','2025-01-24 14:38:17','2025-01-24 14:38:17','1','61');
INSERT INTO inscripciones VALUES('205','1','Primaria','24','B','M','XL','26','30','16','2025-01-24 14:38:17','2025-01-24 14:38:17','1','61');
INSERT INTO inscripciones VALUES('206','1','Primaria','24','C','M','XS','28','32','17','2025-01-24 14:38:17','2025-01-24 14:38:17','1','62');
INSERT INTO inscripciones VALUES('207','1','Primaria','24','C','M','SS','30','24','18','2025-01-24 14:38:17','2025-01-24 14:38:17','1','62');
INSERT INTO inscripciones VALUES('208','1','Primaria','25','A','M','S','10','24','19','2025-01-24 14:38:17','2025-01-24 14:38:17','1','66');
INSERT INTO inscripciones VALUES('209','1','Primaria','25','A','M','M','14','26','20','2025-01-24 14:38:17','2025-01-24 14:38:17','1','66');
INSERT INTO inscripciones VALUES('210','1','Primaria','25','B','M','L','16','28','21','2025-01-24 14:38:17','2025-01-24 14:38:17','1','67');
INSERT INTO inscripciones VALUES('211','1','Primaria','25','B','M','XL','26','30','22','2025-01-24 14:38:17','2025-01-24 14:38:17','1','67');
INSERT INTO inscripciones VALUES('212','1','Primaria','25','C','M','XS','28','32','23','2025-01-24 14:38:17','2025-01-24 14:38:17','1','68');
INSERT INTO inscripciones VALUES('213','1','Primaria','25','C','M','SS','30','24','24','2025-01-24 14:38:17','2025-01-24 14:38:17','1','68');
INSERT INTO inscripciones VALUES('214','1','Primaria','26','A','M','S','10','24','25','2025-01-24 14:38:17','2025-01-24 14:38:17','1','72');
INSERT INTO inscripciones VALUES('215','1','Primaria','26','A','M','M','14','26','26','2025-01-24 14:38:17','2025-01-24 14:38:17','1','72');
INSERT INTO inscripciones VALUES('216','1','Primaria','26','B','M','L','16','28','27','2025-01-24 14:38:17','2025-01-24 14:38:17','1','73');
INSERT INTO inscripciones VALUES('217','1','Primaria','26','B','M','XL','26','30','28','2025-01-24 14:38:17','2025-01-24 14:38:17','1','73');
INSERT INTO inscripciones VALUES('218','1','Primaria','26','C','M','XS','28','32','29','2025-01-24 14:38:17','2025-01-24 14:38:17','1','74');
INSERT INTO inscripciones VALUES('219','1','Primaria','26','C','M','SS','30','24','30','2025-01-24 14:38:17','2025-01-24 14:38:17','1','74');
INSERT INTO inscripciones VALUES('220','1','Primaria','27','A','M','S','10','24','31','2025-01-24 14:38:17','2025-01-24 14:38:17','1','78');
INSERT INTO inscripciones VALUES('221','1','Primaria','27','A','M','M','14','26','32','2025-01-24 14:38:17','2025-01-24 14:38:17','1','78');
INSERT INTO inscripciones VALUES('222','1','Primaria','27','B','M','L','16','28','33','2025-01-24 14:38:17','2025-01-24 14:38:17','1','79');
INSERT INTO inscripciones VALUES('223','1','Primaria','27','B','M','XL','26','30','34','2025-01-24 14:38:17','2025-01-24 14:38:17','1','79');
INSERT INTO inscripciones VALUES('224','1','Primaria','27','C','M','XS','28','32','35','2025-01-24 14:38:17','2025-01-24 14:38:17','1','80');
INSERT INTO inscripciones VALUES('225','1','Primaria','27','C','M','SS','30','24','36','2025-01-24 14:38:17','2025-01-24 14:38:17','1','80');


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
  PRIMARY KEY (`id_persona`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO personas VALUES('3','8','Carlos Jose','Peñas Rivas','123456756565','2000-04-06','Distrito Capital, Calle Circunvalacion, Urb San Maritn I, Frente a la plaza San Martin.','04124564199','2024-10-27','2024-10-30','1');
INSERT INTO personas VALUES('4','10','Daniel Jesus','Duartes Quintero','16023866','1992-12-12','Caracas, el valle','04124564199','2024-10-27','','1');
INSERT INTO personas VALUES('14','56','heldyn david','diaz daboin','2','2024-10-23','San Martin','656','2024-10-28','','1');
INSERT INTO personas VALUES('21','65','heldyn david','Aray','555','2024-10-09',' caracas','656','2024-10-30','','1');
INSERT INTO personas VALUES('22','68','heldyn david','Diaz Daboin','27985583','2024-12-18','Caricuao','04124331080','2024-12-26','','1');
INSERT INTO personas VALUES('24','71','JENNIFER MARIA','GIMÉNEZ','16562458','1985-06-23','Av. Principal San Martin','04242268486','2025-01-24','','1');
INSERT INTO personas VALUES('26','73','YAGERVI DEL CASTILLO','LOPEZ','12798500','1975-05-13','San Martin','04124331080','2025-01-24','2025-02-08','1');
INSERT INTO personas VALUES('27','74','JENNIFER MARIA','GIMÉNEZ','16562458','1976-07-23','Av. Principal San Martin','04242268486','2025-01-24','','1');


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
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO representantes VALUES('1','V','14023560','Carlos Alberto','Pérez López','1980-05-10','Casado','','masculino','carlosperez@gmail.com','O+','Caracas','04121234501','Inactivo','2025-01-24 07:59:04');
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
  PRIMARY KEY (`id_seccion`),
  KEY `id_gestion` (`id_gestion`),
  KEY `id_grado` (`id_grado`),
  CONSTRAINT `secciones_ibfk_1` FOREIGN KEY (`id_gestion`) REFERENCES `gestiones` (`id_gestion`) ON DELETE CASCADE,
  CONSTRAINT `secciones_ibfk_2` FOREIGN KEY (`id_grado`) REFERENCES `grados` (`id_grado`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO secciones VALUES('45','M','25','1','19','1','A','2025-01-24 13:07:51','0');
INSERT INTO secciones VALUES('46','T','25','1','20','1','B','2025-01-24 13:11:22','0');
INSERT INTO secciones VALUES('47','T','25','1','21','1','C','2025-01-24 13:12:11','0');
INSERT INTO secciones VALUES('48','M','25','1','22','1','A','2025-01-24 13:17:23','3');
INSERT INTO secciones VALUES('49','M','25','1','22','1','B','2025-01-24 13:17:23','1');
INSERT INTO secciones VALUES('50','M','25','1','22','1','C','2025-01-24 13:17:23','1');
INSERT INTO secciones VALUES('51','T','25','1','22','1','D','2025-01-24 13:17:23','0');
INSERT INTO secciones VALUES('52','T','25','1','22','1','E','2025-01-24 13:17:23','0');
INSERT INTO secciones VALUES('53','T','25','1','22','1','F','2025-01-24 13:17:23','0');
INSERT INTO secciones VALUES('54','M','35','1','23','1','A','2025-01-24 13:17:23','1');
INSERT INTO secciones VALUES('55','M','35','1','23','1','B','2025-01-24 13:17:23','1');
INSERT INTO secciones VALUES('56','M','35','1','23','1','C','2025-01-24 13:17:23','1');
INSERT INTO secciones VALUES('57','T','35','1','23','1','D','2025-01-24 13:17:23','0');
INSERT INTO secciones VALUES('58','T','35','1','23','1','E','2025-01-24 13:17:23','0');
INSERT INTO secciones VALUES('59','T','35','1','23','1','F','2025-01-24 13:17:23','0');
INSERT INTO secciones VALUES('60','M','35','1','24','1','A','2025-01-24 13:17:23','1');
INSERT INTO secciones VALUES('61','M','35','1','24','1','B','2025-01-24 13:17:23','1');
INSERT INTO secciones VALUES('62','M','35','1','24','1','C','2025-01-24 13:17:23','1');
INSERT INTO secciones VALUES('63','T','35','1','24','1','D','2025-01-24 13:17:23','0');
INSERT INTO secciones VALUES('64','T','35','1','24','1','E','2025-01-24 13:17:23','0');
INSERT INTO secciones VALUES('65','T','35','1','24','1','F','2025-01-24 13:17:23','0');
INSERT INTO secciones VALUES('66','M','35','1','25','1','A','2025-01-24 13:17:23','1');
INSERT INTO secciones VALUES('67','M','35','1','25','1','B','2025-01-24 13:17:23','1');
INSERT INTO secciones VALUES('68','M','35','1','25','1','C','2025-01-24 13:17:23','1');
INSERT INTO secciones VALUES('69','T','35','1','25','1','D','2025-01-24 13:17:23','0');
INSERT INTO secciones VALUES('70','T','35','1','25','1','E','2025-01-24 13:17:23','0');
INSERT INTO secciones VALUES('71','T','35','1','25','1','F','2025-01-24 13:17:23','0');
INSERT INTO secciones VALUES('72','M','35','1','26','1','A','2025-01-24 13:17:23','1');
INSERT INTO secciones VALUES('73','M','35','1','26','1','B','2025-01-24 13:17:23','1');
INSERT INTO secciones VALUES('74','M','35','1','26','1','C','2025-01-24 13:17:23','1');
INSERT INTO secciones VALUES('75','T','35','1','26','1','D','2025-01-24 13:17:23','0');
INSERT INTO secciones VALUES('76','T','35','1','26','1','E','2025-01-24 13:17:23','0');
INSERT INTO secciones VALUES('77','T','35','1','26','1','F','2025-01-24 13:17:23','0');
INSERT INTO secciones VALUES('78','M','35','1','27','1','A','2025-01-24 13:17:24','1');
INSERT INTO secciones VALUES('79','M','35','1','27','1','B','2025-01-24 13:17:24','1');
INSERT INTO secciones VALUES('80','M','35','1','27','1','C','2025-01-24 13:17:24','1');
INSERT INTO secciones VALUES('81','T','35','1','27','1','D','2025-01-24 13:17:24','0');
INSERT INTO secciones VALUES('82','T','35','1','27','1','E','2025-01-24 13:17:24','0');
INSERT INTO secciones VALUES('83','T','35','1','27','1','F','2025-01-24 13:17:24','0');


CREATE TABLE `sexos` (
  `sexo_id` int(11) NOT NULL,
  `sexo` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO sexos VALUES('1','Masculino');
INSERT INTO sexos VALUES('2','Femenino');
INSERT INTO sexos VALUES('1','Masculino');
INSERT INTO sexos VALUES('2','Femenino');


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


CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `rol_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` text NOT NULL,
  `fyh_creacion` datetime DEFAULT NULL,
  `fyh_actualizacion` datetime DEFAULT NULL,
  `estado` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `email` (`email`),
  KEY `rol_id` (`rol_id`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO usuarios VALUES('1','1','agustinzamo@gmail.com','$2y$10$NVhkeupcyKUPFqx.l7t7n.qELV7X5LxKjmOV3WwyRQ3CfJquHF0P2','2023-12-28 20:29:10','2024-11-12 00:00:00','1');
INSERT INTO usuarios VALUES('73','2','yagervic1279@gmail.com','$2y$10$uR.bcT8qUiVP9xdJ2T9ATe8J/1lcwVi6HxV/YWl.2269M45s/pKXy','2025-01-24 00:00:00','2025-02-08 00:00:00','1');
INSERT INTO usuarios VALUES('74','3','jenniferg@gmail.com','$2y$10$VXqBi4.JNbLIgq9Ty3n8heCsIdr702zBmxSHQMr2nHw.04F2bly6q','2025-01-24 00:00:00','','1');
