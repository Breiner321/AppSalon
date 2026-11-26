-- =====================================================================
--  AppSalon · Script SQL completo de la base de datos appsalon_mvc_php
--  Proyecto: AppSalon (Laravel 12 + MySQL/MariaDB + Blade) · Entrega Final
--  Generado: 2026-11-26 con mysqldump (MariaDB 10.11) después de
--            php artisan migrate:fresh --seed, así que equivale a las
--            migraciones y los seeders del proyecto. Se probó importándolo
--            en una base vacía. Compatible con la MariaDB 10.4 de XAMPP.
-- ---------------------------------------------------------------------
--  Contenido: estructura de todas las tablas, llaves foráneas y datos de
--  prueba realistas.
--    · Del negocio: usuarios, servicios, citas, citasServicios (con el
--      precio de cada servicio al reservar), horarios
--      (horario semanal del salón) y bloqueos (fechas sin atención).
--    · De Laravel: personal_access_tokens (tokens de la API, Sanctum),
--      migrations, sessions, password_reset_tokens, cache, cache_locks,
--      jobs, job_batches y failed_jobs.
--  Datos: 10 usuarios (1 admin, 9 clientes: uno sin confirmar y uno
--  desactivado), 10 servicios, horario lunes a sábado, 1 fecha bloqueada
--  y ~150 citas entre el 15 de octubre y el 16 de diciembre de 2026.
--  Las fechas de las citas son fijas: si importas el script más adelante,
--  regenera los datos con php artisan migrate:fresh --seed (las citas se
--  crean alrededor de la fecha del día).
-- ---------------------------------------------------------------------
--  Cómo importar (crea la base appsalon_mvc_php si no existe y
--  reemplaza sus tablas si ya existían):
--    · phpMyAdmin: pestaña Importar > elegir este archivo > Continuar.
--    · Consola (PowerShell, desde la carpeta del proyecto):
--        cmd /c "C:\xampp\mysql\bin\mysql.exe -u root < database\sql\appsalon_mvc_php.sql"
--      (cambia C:\xampp por la carpeta donde instalaste XAMPP).
--  Después, en el .env: DB_DATABASE=appsalon_mvc_php
-- ---------------------------------------------------------------------
--  Credenciales de prueba (las contraseñas están guardadas con bcrypt):
--    Administrador: admin@appsalon.com   / Admin123
--    Cliente:       cliente@appsalon.com / Cliente123
--    Otros clientes (p. ej. camila.rodriguez@correo.com) / Cliente123
-- ---------------------------------------------------------------------
--  Nota: el nombre `citasServicios` se dejó con mayúscula, como en las
--  migraciones. En Windows MySQL/MariaDB lo guarda en minúsculas; en
--  Linux la mayúscula es necesaria para que Laravel encuentre la tabla.
-- =====================================================================

-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: appsalon_mvc_php
-- ------------------------------------------------------
-- Server version	10.11.14-MariaDB-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `appsalon_mvc_php`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `appsalon_mvc_php` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `appsalon_mvc_php`;

--
-- Table structure for table `bloqueos`
--

DROP TABLE IF EXISTS `bloqueos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bloqueos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `motivo` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `bloqueos_fecha_unique` (`fecha`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bloqueos`
--

LOCK TABLES `bloqueos` WRITE;
/*!40000 ALTER TABLE `bloqueos` DISABLE KEYS */;
INSERT INTO `bloqueos` VALUES
(1,'2026-12-17','Capacitación del personal','2026-11-26 18:43:00');
/*!40000 ALTER TABLE `bloqueos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES
('appsalon-cache-login5c785c036466adea360111aa28563bfd556b5fba','i:4;',1795718642),
('appsalon-cache-login5c785c036466adea360111aa28563bfd556b5fba:timer','i:1795718642;',1795718642);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `citas`
--

DROP TABLE IF EXISTS `citas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `citas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `duracion` smallint(5) unsigned NOT NULL DEFAULT 60,
  `usuarioId` bigint(20) unsigned DEFAULT NULL,
  `total` decimal(6,2) DEFAULT NULL,
  `estado` enum('pendiente','confirmada','completada','cancelada') NOT NULL DEFAULT 'pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `citas_usuarioid_foreign` (`usuarioId`),
  KEY `citas_fecha_estado_index` (`fecha`,`estado`),
  CONSTRAINT `citas_usuarioid_foreign` FOREIGN KEY (`usuarioId`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=154 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citas`
--

LOCK TABLES `citas` WRITE;
/*!40000 ALTER TABLE `citas` DISABLE KEYS */;
INSERT INTO `citas` VALUES
(1,'2026-11-30','10:00:00',85,2,57.00,'pendiente','2026-11-24 17:38:00','2026-11-26 18:43:00'),
(2,'2026-12-03','15:30:00',120,2,120.00,'confirmada','2026-11-26 15:17:00','2026-11-26 18:43:00'),
(3,'2026-10-15','09:00:00',160,5,142.00,'completada','2026-10-11 08:04:00','2026-11-26 18:43:00'),
(4,'2026-10-15','16:00:00',60,7,60.00,'completada','2026-10-06 08:27:00','2026-11-26 18:43:00'),
(5,'2026-10-15','13:30:00',95,10,78.00,'completada','2026-10-11 10:18:00','2026-11-26 18:43:00'),
(6,'2026-10-16','10:30:00',320,2,388.00,'completada','2026-10-08 14:41:00','2026-11-26 18:43:00'),
(7,'2026-10-16','17:30:00',65,10,53.00,'completada','2026-10-11 15:15:00','2026-11-26 18:43:00'),
(8,'2026-10-17','11:00:00',160,2,142.00,'cancelada','2026-10-10 09:23:00','2026-11-26 18:43:00'),
(9,'2026-10-17','09:00:00',180,7,250.00,'completada','2026-10-14 12:15:00','2026-11-26 18:43:00'),
(10,'2026-10-17','13:30:00',80,2,63.00,'completada','2026-10-11 09:21:00','2026-11-26 18:43:00'),
(11,'2026-10-19','11:30:00',210,4,200.00,'completada','2026-10-14 08:51:00','2026-11-26 18:43:00'),
(12,'2026-10-19','15:30:00',210,6,275.00,'completada','2026-10-12 17:48:00','2026-11-26 18:43:00'),
(13,'2026-10-20','11:00:00',140,7,138.00,'completada','2026-10-13 15:21:00','2026-11-26 18:43:00'),
(14,'2026-10-20','13:30:00',255,2,305.00,'completada','2026-10-14 08:42:00','2026-11-26 18:43:00'),
(15,'2026-10-20','09:30:00',90,5,80.00,'completada','2026-10-13 12:31:00','2026-11-26 18:43:00'),
(16,'2026-10-21','11:30:00',165,7,135.00,'completada','2026-10-19 14:15:00','2026-11-26 18:43:00'),
(17,'2026-10-21','15:30:00',210,10,270.00,'completada','2026-10-17 17:29:00','2026-11-26 18:43:00'),
(18,'2026-10-21','09:30:00',80,3,78.00,'completada','2026-10-11 14:57:00','2026-11-26 18:43:00'),
(19,'2026-10-22','13:00:00',150,5,180.00,'completada','2026-10-13 16:48:00','2026-11-26 18:43:00'),
(20,'2026-10-22','17:30:00',20,8,18.00,'completada','2026-10-17 10:18:00','2026-11-26 18:43:00'),
(21,'2026-10-22','09:00:00',185,2,173.00,'completada','2026-10-14 16:00:00','2026-11-26 18:43:00'),
(22,'2026-10-22','18:00:00',40,3,22.00,'completada','2026-10-13 14:46:00','2026-11-26 18:43:00'),
(23,'2026-10-23','13:00:00',185,4,173.00,'completada','2026-10-16 14:22:00','2026-11-26 18:43:00'),
(24,'2026-10-23','09:30:00',210,5,270.00,'completada','2026-10-17 20:32:00','2026-11-26 18:43:00'),
(25,'2026-10-23','16:30:00',110,2,98.00,'cancelada','2026-10-22 18:47:00','2026-11-26 18:43:00'),
(26,'2026-10-24','09:00:00',150,7,180.00,'completada','2026-10-23 15:22:00','2026-11-26 18:43:00'),
(27,'2026-10-24','13:00:00',90,6,65.00,'completada','2026-10-15 17:07:00','2026-11-26 18:43:00'),
(28,'2026-10-24','12:00:00',60,3,40.00,'completada','2026-10-22 16:04:00','2026-11-26 18:43:00'),
(29,'2026-10-26','12:30:00',20,7,18.00,'completada','2026-10-17 19:00:00','2026-11-26 18:43:00'),
(30,'2026-10-26','14:00:00',160,3,142.00,'completada','2026-10-22 08:25:00','2026-11-26 18:43:00'),
(31,'2026-10-26','09:30:00',160,2,142.00,'completada','2026-10-25 16:03:00','2026-11-26 18:43:00'),
(32,'2026-10-26','17:00:00',40,10,22.00,'completada','2026-10-19 13:35:00','2026-11-26 18:43:00'),
(33,'2026-10-27','09:00:00',100,8,62.00,'completada','2026-10-18 11:05:00','2026-11-26 18:43:00'),
(34,'2026-10-27','14:00:00',210,7,220.00,'completada','2026-10-18 11:05:00','2026-11-26 18:43:00'),
(35,'2026-10-27','12:00:00',105,5,75.00,'completada','2026-10-23 13:25:00','2026-11-26 18:43:00'),
(36,'2026-10-28','13:00:00',120,3,120.00,'completada','2026-10-23 20:06:00','2026-11-26 18:43:00'),
(37,'2026-10-28','09:30:00',40,3,22.00,'completada','2026-10-21 16:10:00','2026-11-26 18:43:00'),
(38,'2026-10-28','15:00:00',200,10,268.00,'completada','2026-10-21 20:36:00','2026-11-26 18:43:00'),
(39,'2026-10-29','14:30:00',30,5,25.00,'completada','2026-10-28 19:52:00','2026-11-26 18:43:00'),
(40,'2026-10-29','09:30:00',100,5,82.00,'completada','2026-10-20 11:06:00','2026-11-26 18:43:00'),
(41,'2026-10-29','12:00:00',20,3,18.00,'completada','2026-10-22 10:13:00','2026-11-26 18:43:00'),
(42,'2026-10-29','12:30:00',105,10,95.00,'completada','2026-10-28 16:47:00','2026-11-26 18:43:00'),
(43,'2026-10-29','15:00:00',220,7,182.00,'completada','2026-10-21 15:02:00','2026-11-26 18:43:00'),
(44,'2026-10-30','09:30:00',225,3,235.00,'completada','2026-10-25 13:35:00','2026-11-26 18:43:00'),
(45,'2026-10-30','18:00:00',60,3,40.00,'completada','2026-10-28 15:50:00','2026-11-26 18:43:00'),
(46,'2026-10-30','16:30:00',60,3,40.00,'completada','2026-10-25 17:21:00','2026-11-26 18:43:00'),
(47,'2026-10-31','14:00:00',40,3,22.00,'completada','2026-10-25 11:46:00','2026-11-26 18:43:00'),
(48,'2026-10-31','12:00:00',60,4,60.00,'completada','2026-10-29 19:14:00','2026-11-26 18:43:00'),
(49,'2026-11-02','11:30:00',125,7,113.00,'completada','2026-10-26 15:50:00','2026-11-26 18:43:00'),
(50,'2026-11-02','14:30:00',150,8,180.00,'completada','2026-11-01 13:51:00','2026-11-26 18:43:00'),
(51,'2026-11-03','18:00:00',60,4,40.00,'completada','2026-10-25 12:31:00','2026-11-26 18:43:00'),
(52,'2026-11-03','09:00:00',50,7,43.00,'completada','2026-10-30 10:16:00','2026-11-26 18:43:00'),
(53,'2026-11-04','09:30:00',125,7,93.00,'completada','2026-10-30 09:37:00','2026-11-26 18:43:00'),
(54,'2026-11-04','16:00:00',105,4,80.00,'cancelada','2026-10-31 18:46:00','2026-11-26 18:43:00'),
(55,'2026-11-04','12:00:00',180,2,250.00,'cancelada','2026-10-27 10:45:00','2026-11-26 18:43:00'),
(56,'2026-11-04','14:00:00',270,8,280.00,'completada','2026-10-29 09:58:00','2026-11-26 18:43:00'),
(57,'2026-11-05','11:30:00',150,5,145.00,'completada','2026-10-27 17:26:00','2026-11-26 18:43:00'),
(58,'2026-11-05','15:00:00',145,7,97.00,'completada','2026-10-26 18:19:00','2026-11-26 18:43:00'),
(59,'2026-11-05','09:00:00',140,7,138.00,'completada','2026-10-31 19:59:00','2026-11-26 18:43:00'),
(60,'2026-11-06','16:30:00',40,10,22.00,'completada','2026-11-03 18:48:00','2026-11-26 18:43:00'),
(61,'2026-11-06','12:00:00',180,7,250.00,'completada','2026-11-05 14:55:00','2026-11-26 18:43:00'),
(62,'2026-11-07','10:30:00',240,5,310.00,'completada','2026-11-06 09:19:00','2026-11-26 18:43:00'),
(63,'2026-11-07','09:30:00',45,4,35.00,'completada','2026-11-02 18:41:00','2026-11-26 18:43:00'),
(64,'2026-11-09','11:30:00',180,5,250.00,'completada','2026-11-01 20:55:00','2026-11-26 18:43:00'),
(65,'2026-11-09','09:00:00',40,5,22.00,'completada','2026-11-04 09:20:00','2026-11-26 18:43:00'),
(66,'2026-11-10','13:00:00',60,2,45.00,'completada','2026-11-04 18:08:00','2026-11-26 18:43:00'),
(67,'2026-11-10','12:30:00',30,5,25.00,'completada','2026-11-08 08:02:00','2026-11-26 18:43:00'),
(68,'2026-11-11','10:30:00',60,7,60.00,'completada','2026-11-01 20:18:00','2026-11-26 18:43:00'),
(69,'2026-11-11','17:00:00',90,10,80.00,'cancelada','2026-11-10 08:20:00','2026-11-26 18:43:00'),
(70,'2026-11-11','12:30:00',125,2,93.00,'completada','2026-11-04 15:14:00','2026-11-26 18:43:00'),
(71,'2026-11-12','10:00:00',240,8,290.00,'completada','2026-11-10 11:41:00','2026-11-26 18:43:00'),
(72,'2026-11-12','18:00:00',30,2,25.00,'cancelada','2026-11-07 15:28:00','2026-11-26 18:43:00'),
(73,'2026-11-13','10:00:00',60,10,40.00,'completada','2026-11-10 20:53:00','2026-11-26 18:43:00'),
(74,'2026-11-13','12:00:00',240,5,290.00,'cancelada','2026-11-08 11:02:00','2026-11-26 18:43:00'),
(75,'2026-11-13','14:00:00',270,4,280.00,'completada','2026-11-06 13:02:00','2026-11-26 18:43:00'),
(76,'2026-11-13','11:00:00',140,6,138.00,'completada','2026-11-10 12:07:00','2026-11-26 18:43:00'),
(77,'2026-11-14','10:00:00',20,4,18.00,'completada','2026-11-05 13:17:00','2026-11-26 18:43:00'),
(78,'2026-11-14','10:30:00',225,4,195.00,'completada','2026-11-10 16:52:00','2026-11-26 18:43:00'),
(79,'2026-11-16','14:30:00',250,6,292.00,'completada','2026-11-15 11:25:00','2026-11-26 18:43:00'),
(80,'2026-11-16','11:30:00',30,7,25.00,'completada','2026-11-10 12:56:00','2026-11-26 18:43:00'),
(81,'2026-11-17','13:30:00',120,2,100.00,'completada','2026-11-15 09:45:00','2026-11-26 18:43:00'),
(82,'2026-11-17','09:00:00',200,3,198.00,'completada','2026-11-15 13:32:00','2026-11-26 18:43:00'),
(83,'2026-11-17','15:30:00',40,3,22.00,'cancelada','2026-11-08 12:53:00','2026-11-26 18:43:00'),
(84,'2026-11-18','15:00:00',90,5,85.00,'completada','2026-11-12 18:04:00','2026-11-26 18:43:00'),
(85,'2026-11-18','12:00:00',180,4,250.00,'completada','2026-11-17 16:24:00','2026-11-26 18:43:00'),
(86,'2026-11-18','10:30:00',45,7,35.00,'completada','2026-11-10 14:29:00','2026-11-26 18:43:00'),
(87,'2026-11-19','11:00:00',345,3,405.00,'completada','2026-11-12 18:24:00','2026-11-26 18:43:00'),
(88,'2026-11-19','09:00:00',60,8,60.00,'cancelada','2026-11-17 19:08:00','2026-11-26 18:43:00'),
(89,'2026-11-19','18:00:00',30,4,25.00,'completada','2026-11-11 17:36:00','2026-11-26 18:43:00'),
(90,'2026-11-20','10:00:00',240,10,290.00,'completada','2026-11-16 13:29:00','2026-11-26 18:43:00'),
(91,'2026-11-20','15:30:00',200,6,223.00,'completada','2026-11-14 17:54:00','2026-11-26 18:43:00'),
(92,'2026-11-20','09:00:00',30,8,20.00,'completada','2026-11-16 09:12:00','2026-11-26 18:43:00'),
(93,'2026-11-21','12:00:00',150,8,180.00,'completada','2026-11-11 17:50:00','2026-11-26 18:43:00'),
(94,'2026-11-21','09:00:00',120,8,80.00,'completada','2026-11-19 10:37:00','2026-11-26 18:43:00'),
(95,'2026-11-21','11:30:00',30,8,20.00,'completada','2026-11-20 08:18:00','2026-11-26 18:43:00'),
(96,'2026-11-23','10:00:00',210,5,185.00,'completada','2026-11-14 20:24:00','2026-11-26 18:43:00'),
(97,'2026-11-24','13:00:00',240,7,290.00,'completada','2026-11-17 12:26:00','2026-11-26 18:43:00'),
(98,'2026-11-24','10:00:00',180,5,250.00,'completada','2026-11-22 11:58:00','2026-11-26 18:43:00'),
(99,'2026-11-25','12:00:00',290,6,318.00,'completada','2026-11-23 18:24:00','2026-11-26 18:43:00'),
(100,'2026-11-25','17:00:00',120,7,100.00,'completada','2026-11-18 17:33:00','2026-11-26 18:43:00'),
(101,'2026-11-25','09:00:00',100,8,82.00,'cancelada','2026-11-20 19:56:00','2026-11-26 18:43:00'),
(102,'2026-11-25','09:00:00',165,5,155.00,'completada','2026-11-16 14:53:00','2026-11-26 18:43:00'),
(103,'2026-11-26','11:00:00',230,4,288.00,'completada','2026-11-17 11:23:00','2026-11-26 18:43:00'),
(104,'2026-11-26','15:00:00',180,6,250.00,'cancelada','2026-11-22 14:28:00','2026-11-26 18:43:00'),
(105,'2026-11-26','16:00:00',170,10,158.00,'completada','2026-11-21 09:12:00','2026-11-26 18:43:00'),
(106,'2026-11-27','11:00:00',50,4,38.00,'confirmada','2026-11-21 08:58:00','2026-11-26 18:43:01'),
(107,'2026-11-27','14:00:00',65,7,53.00,'pendiente','2026-11-23 12:50:00','2026-11-26 18:43:01'),
(108,'2026-11-28','09:30:00',220,3,272.00,'confirmada','2026-11-18 16:57:00','2026-11-26 18:43:01'),
(109,'2026-11-28','13:30:00',60,5,40.00,'pendiente','2026-11-19 15:24:00','2026-11-26 18:43:01'),
(110,'2026-11-30','16:30:00',145,3,97.00,'pendiente','2026-11-22 15:08:00','2026-11-26 18:43:01'),
(111,'2026-11-30','11:30:00',240,2,290.00,'pendiente','2026-11-26 20:23:00','2026-11-26 18:43:01'),
(112,'2026-12-01','13:30:00',60,8,40.00,'pendiente','2026-11-29 17:25:00','2026-11-26 18:43:01'),
(113,'2026-12-01','09:30:00',205,7,177.00,'confirmada','2026-11-24 19:39:00','2026-11-26 18:43:01'),
(114,'2026-12-02','13:00:00',70,8,42.00,'confirmada','2026-11-24 13:02:00','2026-11-26 18:43:01'),
(115,'2026-12-02','16:00:00',180,7,160.00,'confirmada','2026-11-29 09:03:00','2026-11-26 18:43:01'),
(116,'2026-12-02','09:30:00',180,6,160.00,'confirmada','2026-11-24 10:46:00','2026-11-26 18:43:01'),
(117,'2026-12-02','14:30:00',75,4,55.00,'pendiente','2026-12-01 10:20:00','2026-11-26 18:43:01'),
(118,'2026-12-03','09:00:00',255,7,310.00,'confirmada','2026-12-02 20:54:00','2026-11-26 18:43:01'),
(119,'2026-12-03','17:30:00',80,6,78.00,'pendiente','2026-11-23 15:59:00','2026-11-26 18:43:01'),
(120,'2026-12-03','13:30:00',120,6,120.00,'pendiente','2026-11-27 13:21:00','2026-11-26 18:43:01'),
(121,'2026-12-04','10:00:00',40,6,22.00,'confirmada','2026-12-02 09:05:00','2026-11-26 18:43:01'),
(122,'2026-12-04','16:30:00',150,2,180.00,'pendiente','2026-11-29 08:04:00','2026-11-26 18:43:01'),
(123,'2026-12-05','10:00:00',180,5,250.00,'confirmada','2026-12-01 13:40:00','2026-11-26 18:43:01'),
(124,'2026-12-05','09:00:00',60,2,60.00,'pendiente','2026-11-26 08:55:00','2026-11-26 18:43:01'),
(125,'2026-12-07','13:00:00',60,7,60.00,'confirmada','2026-11-27 14:33:00','2026-11-26 18:43:01'),
(126,'2026-12-07','09:30:00',210,3,185.00,'confirmada','2026-12-01 20:23:00','2026-11-26 18:43:01'),
(127,'2026-12-07','15:00:00',140,6,138.00,'confirmada','2026-12-01 08:18:00','2026-11-26 18:43:01'),
(128,'2026-12-07','18:00:00',45,6,35.00,'cancelada','2026-11-30 09:45:00','2026-11-26 18:43:01'),
(129,'2026-12-08','12:30:00',180,5,250.00,'cancelada','2026-12-05 15:23:00','2026-11-26 18:43:01'),
(130,'2026-12-08','16:00:00',130,5,102.00,'pendiente','2026-11-28 13:58:00','2026-11-26 18:43:01'),
(131,'2026-12-08','13:00:00',30,4,25.00,'confirmada','2026-12-02 16:42:00','2026-11-26 18:43:01'),
(132,'2026-12-09','12:00:00',180,7,160.00,'pendiente','2026-12-07 14:01:00','2026-11-26 18:43:01'),
(133,'2026-12-09','15:30:00',40,2,22.00,'pendiente','2026-12-06 13:55:00','2026-11-26 18:43:01'),
(134,'2026-12-09','15:00:00',30,5,20.00,'pendiente','2026-12-06 14:13:00','2026-11-26 18:43:01'),
(135,'2026-12-09','17:00:00',30,5,25.00,'confirmada','2026-12-08 16:46:00','2026-11-26 18:43:01'),
(136,'2026-12-09','18:00:00',40,4,22.00,'confirmada','2026-12-08 09:51:00','2026-11-26 18:43:01'),
(137,'2026-12-10','14:30:00',180,8,250.00,'confirmada','2026-12-05 14:34:00','2026-11-26 18:43:01'),
(138,'2026-12-10','12:00:00',120,7,105.00,'confirmada','2026-12-03 10:20:00','2026-11-26 18:43:01'),
(139,'2026-12-10','11:30:00',30,2,20.00,'confirmada','2026-12-07 09:08:00','2026-11-26 18:43:01'),
(140,'2026-12-10','17:30:00',90,5,85.00,'pendiente','2026-12-08 12:33:00','2026-11-26 18:43:01'),
(141,'2026-12-11','12:00:00',190,8,162.00,'pendiente','2026-12-08 12:00:00','2026-11-26 18:43:01'),
(142,'2026-12-11','09:30:00',75,7,55.00,'cancelada','2026-12-06 20:12:00','2026-11-26 18:43:01'),
(143,'2026-12-11','15:30:00',185,2,173.00,'pendiente','2026-12-07 10:50:00','2026-11-26 18:43:01'),
(144,'2026-12-12','10:00:00',260,3,308.00,'confirmada','2026-12-09 20:26:00','2026-11-26 18:43:01'),
(145,'2026-12-14','13:30:00',330,5,430.00,'pendiente','2026-12-08 10:22:00','2026-11-26 18:43:01'),
(146,'2026-12-14','09:30:00',150,3,145.00,'confirmada','2026-12-05 17:42:00','2026-11-26 18:43:01'),
(147,'2026-12-14','12:00:00',50,8,43.00,'pendiente','2026-12-09 09:52:00','2026-11-26 18:43:01'),
(148,'2026-12-15','15:00:00',200,7,218.00,'confirmada','2026-12-13 15:24:00','2026-11-26 18:43:01'),
(149,'2026-12-15','09:30:00',220,6,202.00,'pendiente','2026-12-14 16:53:00','2026-11-26 18:43:01'),
(150,'2026-12-15','13:30:00',80,5,58.00,'confirmada','2026-12-14 13:01:00','2026-11-26 18:43:01'),
(151,'2026-12-16','09:30:00',120,3,100.00,'pendiente','2026-12-15 18:51:00','2026-11-26 18:43:01'),
(152,'2026-12-16','14:30:00',240,5,240.00,'confirmada','2026-12-12 19:37:00','2026-11-26 18:43:01'),
(153,'2026-12-16','13:00:00',75,7,60.00,'confirmada','2026-12-11 14:05:00','2026-11-26 18:43:01');
/*!40000 ALTER TABLE `citas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `citasServicios`
--

DROP TABLE IF EXISTS `citasServicios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `citasServicios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `citaId` bigint(20) unsigned NOT NULL,
  `servicioId` bigint(20) unsigned NOT NULL,
  `precio` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `citasservicios_citaid_foreign` (`citaId`),
  KEY `citasservicios_servicioid_foreign` (`servicioId`),
  CONSTRAINT `citasservicios_citaid_foreign` FOREIGN KEY (`citaId`) REFERENCES `citas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `citasservicios_servicioid_foreign` FOREIGN KEY (`servicioId`) REFERENCES `servicios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=291 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citasServicios`
--

LOCK TABLES `citasServicios` WRITE;
/*!40000 ALTER TABLE `citasServicios` DISABLE KEYS */;
INSERT INTO `citasServicios` VALUES
(1,1,1,35.00),
(2,1,7,22.00),
(3,2,4,120.00),
(4,3,4,120.00),
(5,3,7,22.00),
(6,4,6,60.00),
(7,5,1,35.00),
(8,5,2,25.00),
(9,5,10,18.00),
(10,6,4,120.00),
(11,6,9,250.00),
(12,6,10,18.00),
(13,7,1,35.00),
(14,7,10,18.00),
(15,8,4,120.00),
(16,8,7,22.00),
(17,9,9,250.00),
(18,10,2,25.00),
(19,10,3,20.00),
(20,10,10,18.00),
(21,11,3,20.00),
(22,11,4,120.00),
(23,11,6,60.00),
(24,12,2,25.00),
(25,12,9,250.00),
(26,13,4,120.00),
(27,13,10,18.00),
(28,14,1,35.00),
(29,14,3,20.00),
(30,14,9,250.00),
(31,15,3,20.00),
(32,15,6,60.00),
(33,16,1,35.00),
(34,16,6,60.00),
(35,16,8,40.00),
(36,17,3,20.00),
(37,17,9,250.00),
(38,18,6,60.00),
(39,18,10,18.00),
(40,19,5,180.00),
(41,20,10,18.00),
(42,21,1,35.00),
(43,21,4,120.00),
(44,21,10,18.00),
(45,22,7,22.00),
(46,23,1,35.00),
(47,23,4,120.00),
(48,23,10,18.00),
(49,24,3,20.00),
(50,24,9,250.00),
(51,25,3,20.00),
(52,25,6,60.00),
(53,25,10,18.00),
(54,26,5,180.00),
(55,27,2,25.00),
(56,27,8,40.00),
(57,28,8,40.00),
(58,29,10,18.00),
(59,30,4,120.00),
(60,30,7,22.00),
(61,31,4,120.00),
(62,31,7,22.00),
(63,32,7,22.00),
(64,33,7,22.00),
(65,33,8,40.00),
(66,34,5,180.00),
(67,34,8,40.00),
(68,35,1,35.00),
(69,35,7,22.00),
(70,35,10,18.00),
(71,36,4,120.00),
(72,37,7,22.00),
(73,38,9,250.00),
(74,38,10,18.00),
(75,39,2,25.00),
(76,40,6,60.00),
(77,40,7,22.00),
(78,41,10,18.00),
(79,42,1,35.00),
(80,42,6,60.00),
(81,43,4,120.00),
(82,43,7,22.00),
(83,43,8,40.00),
(84,44,1,35.00),
(85,44,3,20.00),
(86,44,5,180.00),
(87,45,8,40.00),
(88,46,7,22.00),
(89,46,10,18.00),
(90,47,7,22.00),
(91,48,6,60.00),
(92,49,1,35.00),
(93,49,6,60.00),
(94,49,10,18.00),
(95,50,5,180.00),
(96,51,8,40.00),
(97,52,2,25.00),
(98,52,10,18.00),
(99,53,1,35.00),
(100,53,8,40.00),
(101,53,10,18.00),
(102,54,1,35.00),
(103,54,2,25.00),
(104,54,3,20.00),
(105,55,9,250.00),
(106,56,5,180.00),
(107,56,6,60.00),
(108,56,8,40.00),
(109,57,2,25.00),
(110,57,4,120.00),
(111,58,1,35.00),
(112,58,7,22.00),
(113,58,8,40.00),
(114,59,4,120.00),
(115,59,10,18.00),
(116,60,7,22.00),
(117,61,9,250.00),
(118,62,6,60.00),
(119,62,9,250.00),
(120,63,1,35.00),
(121,64,9,250.00),
(122,65,7,22.00),
(123,66,2,25.00),
(124,66,3,20.00),
(125,67,2,25.00),
(126,68,6,60.00),
(127,69,3,20.00),
(128,69,6,60.00),
(129,70,1,35.00),
(130,70,8,40.00),
(131,70,10,18.00),
(132,71,8,40.00),
(133,71,9,250.00),
(134,72,2,25.00),
(135,73,8,40.00),
(136,74,7,22.00),
(137,74,9,250.00),
(138,74,10,18.00),
(139,75,5,180.00),
(140,75,6,60.00),
(141,75,8,40.00),
(142,76,4,120.00),
(143,76,10,18.00),
(144,77,10,18.00),
(145,78,1,35.00),
(146,78,4,120.00),
(147,78,8,40.00),
(148,79,3,20.00),
(149,79,7,22.00),
(150,79,9,250.00),
(151,80,2,25.00),
(152,81,6,60.00),
(153,81,8,40.00),
(154,82,4,120.00),
(155,82,6,60.00),
(156,82,10,18.00),
(157,83,7,22.00),
(158,84,2,25.00),
(159,84,6,60.00),
(160,85,9,250.00),
(161,86,1,35.00),
(162,87,1,35.00),
(163,87,4,120.00),
(164,87,9,250.00),
(165,88,6,60.00),
(166,89,2,25.00),
(167,90,8,40.00),
(168,90,9,250.00),
(169,91,2,25.00),
(170,91,5,180.00),
(171,91,10,18.00),
(172,92,3,20.00),
(173,93,5,180.00),
(174,94,7,22.00),
(175,94,8,40.00),
(176,94,10,18.00),
(177,95,3,20.00),
(178,96,2,25.00),
(179,96,4,120.00),
(180,96,8,40.00),
(181,97,8,40.00),
(182,97,9,250.00),
(183,98,9,250.00),
(184,99,4,120.00),
(185,99,5,180.00),
(186,99,10,18.00),
(187,100,6,60.00),
(188,100,8,40.00),
(189,101,6,60.00),
(190,101,7,22.00),
(191,102,1,35.00),
(192,102,4,120.00),
(193,103,3,20.00),
(194,103,9,250.00),
(195,103,10,18.00),
(196,104,9,250.00),
(197,105,3,20.00),
(198,105,4,120.00),
(199,105,10,18.00),
(200,106,3,20.00),
(201,106,10,18.00),
(202,107,1,35.00),
(203,107,10,18.00),
(204,108,7,22.00),
(205,108,9,250.00),
(206,109,8,40.00),
(207,110,1,35.00),
(208,110,7,22.00),
(209,110,8,40.00),
(210,111,8,40.00),
(211,111,9,250.00),
(212,112,8,40.00),
(213,113,1,35.00),
(214,113,4,120.00),
(215,113,7,22.00),
(216,114,3,20.00),
(217,114,7,22.00),
(218,115,4,120.00),
(219,115,8,40.00),
(220,116,4,120.00),
(221,116,8,40.00),
(222,117,1,35.00),
(223,117,3,20.00),
(224,118,1,35.00),
(225,118,2,25.00),
(226,118,9,250.00),
(227,119,6,60.00),
(228,119,10,18.00),
(229,120,4,120.00),
(230,121,7,22.00),
(231,122,5,180.00),
(232,123,9,250.00),
(233,124,6,60.00),
(234,125,6,60.00),
(235,126,2,25.00),
(236,126,4,120.00),
(237,126,8,40.00),
(238,127,4,120.00),
(239,127,10,18.00),
(240,128,1,35.00),
(241,129,9,250.00),
(242,130,3,20.00),
(243,130,6,60.00),
(244,130,7,22.00),
(245,131,2,25.00),
(246,132,4,120.00),
(247,132,8,40.00),
(248,133,7,22.00),
(249,134,3,20.00),
(250,135,2,25.00),
(251,136,7,22.00),
(252,137,9,250.00),
(253,138,2,25.00),
(254,138,3,20.00),
(255,138,6,60.00),
(256,139,3,20.00),
(257,140,2,25.00),
(258,140,6,60.00),
(259,141,3,20.00),
(260,141,4,120.00),
(261,141,7,22.00),
(262,142,1,35.00),
(263,142,3,20.00),
(264,143,1,35.00),
(265,143,4,120.00),
(266,143,10,18.00),
(267,144,8,40.00),
(268,144,9,250.00),
(269,144,10,18.00),
(270,145,5,180.00),
(271,145,9,250.00),
(272,146,2,25.00),
(273,146,4,120.00),
(274,147,2,25.00),
(275,147,10,18.00),
(276,148,3,20.00),
(277,148,5,180.00),
(278,148,10,18.00),
(279,149,4,120.00),
(280,149,6,60.00),
(281,149,7,22.00),
(282,150,8,40.00),
(283,150,10,18.00),
(284,151,6,60.00),
(285,151,8,40.00),
(286,152,3,20.00),
(287,152,5,180.00),
(288,152,8,40.00),
(289,153,1,35.00),
(290,153,2,25.00);
/*!40000 ALTER TABLE `citasServicios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `horarios`
--

DROP TABLE IF EXISTS `horarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `horarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `diaSemana` tinyint(3) unsigned NOT NULL,
  `apertura` time NOT NULL DEFAULT '09:00:00',
  `cierre` time NOT NULL DEFAULT '18:00:00',
  `abierto` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `horarios_diasemana_unique` (`diaSemana`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `horarios`
--

LOCK TABLES `horarios` WRITE;
/*!40000 ALTER TABLE `horarios` DISABLE KEYS */;
INSERT INTO `horarios` VALUES
(1,0,'09:00:00','13:00:00',0),
(2,1,'09:00:00','19:00:00',1),
(3,2,'09:00:00','19:00:00',1),
(4,3,'09:00:00','19:00:00',1),
(5,4,'09:00:00','19:00:00',1),
(6,5,'09:00:00','19:00:00',1),
(7,6,'09:00:00','15:00:00',1);
/*!40000 ALTER TABLE `horarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'0001_01_01_000000_create_users_table',1),
(2,'0001_01_01_000001_create_cache_table',1),
(3,'0001_01_01_000002_create_jobs_table',1),
(4,'2026_10_23_000001_create_servicios_table',1),
(5,'2026_10_23_000002_create_citas_table',1),
(6,'2026_10_23_000003_create_citas_servicios_table',1),
(7,'2026_11_20_000001_add_activo_to_usuarios_table',1),
(8,'2026_11_20_000002_add_duracion_to_citas_table',1),
(9,'2026_11_20_000003_create_horarios_table',1),
(10,'2026_11_20_000004_create_personal_access_tokens_table',1),
(11,'2026_11_26_000001_add_precio_to_citas_servicios_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `servicios`
--

DROP TABLE IF EXISTS `servicios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `servicios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) NOT NULL,
  `precio` decimal(5,2) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `duracion` int(11) NOT NULL DEFAULT 60,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `servicios`
--

LOCK TABLES `servicios` WRITE;
/*!40000 ALTER TABLE `servicios` DISABLE KEYS */;
INSERT INTO `servicios` VALUES
(1,'Corte de cabello mujer',35.00,'Corte a la medida con lavado y secado.',45,1,'2026-11-26 18:43:00'),
(2,'Corte de cabello hombre',25.00,'Corte clásico o moderno con acabado a máquina y tijera.',30,1,'2026-11-26 18:43:00'),
(3,'Corte infantil',20.00,'Corte para niños y niñas de hasta 12 años.',30,1,'2026-11-26 18:43:00'),
(4,'Tinte completo',120.00,'Coloración de raíz a puntas con productos sin amoniaco.',120,1,'2026-11-26 18:43:00'),
(5,'Mechas o balayage',180.00,'Iluminación del cabello con técnica de mechas o balayage.',150,1,'2026-11-26 18:43:00'),
(6,'Peinado para evento',60.00,'Recogido o peinado suelto para bodas, grados y fiestas.',60,1,'2026-11-26 18:43:00'),
(7,'Manicure tradicional',22.00,'Limpieza, limado y esmaltado de uñas de las manos.',40,1,'2026-11-26 18:43:00'),
(8,'Pedicure spa',40.00,'Exfoliación, hidratación y esmaltado de uñas de los pies.',60,1,'2026-11-26 18:43:00'),
(9,'Tratamiento de keratina',250.00,'Alisado y nutrición profunda que reduce el frizz.',180,1,'2026-11-26 18:43:00'),
(10,'Diseño de cejas',18.00,'Depilación y perfilado de cejas con cera o pinza.',20,1,'2026-11-26 18:43:00');
/*!40000 ALTER TABLE `servicios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) NOT NULL,
  `apellido` varchar(60) NOT NULL,
  `email` varchar(30) NOT NULL,
  `password` varchar(60) NOT NULL,
  `telefono` varchar(10) DEFAULT NULL,
  `admin` tinyint(1) NOT NULL DEFAULT 0,
  `confirmado` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `token` varchar(15) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuarios_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES
(1,'Admin','Salon','admin@appsalon.com','$2y$12$uIIGlOQIC92XuoDK5qub/eOKB.RCcfu6ILa/BoJx6o9UslQ0dLjJe','3001234567',1,1,1,NULL,'2026-11-26 18:42:58','2026-11-26 18:42:58'),
(2,'Laura','Gomez','cliente@appsalon.com','$2y$12$Nlkgv72uBeaNr91uqLOtyu24KQkOHC5oRspDFIQ3/0tZnEmXbKNUi','3007654321',0,1,1,NULL,'2026-11-26 18:42:58','2026-11-26 18:42:58'),
(3,'Camila','Rodríguez','camila.rodriguez@correo.com','$2y$12$UwQHxn6tSBRILznNdOQ5yOFiVPBYbxgd2wvRNM98jhX8Mk.EdPJh2','3104567890',0,1,1,NULL,'2026-11-26 18:42:58','2026-11-26 18:42:58'),
(4,'Andrés','Martínez','andres.martinez@correo.com','$2y$12$33iGpKnbrTUrMDofJlZB0uGQpzoFSFIvlUjV/t/a19QBCL.mh.4iy','3125551234',0,1,1,NULL,'2026-11-26 18:42:58','2026-11-26 18:42:58'),
(5,'Valentina','López','valentina.lopez@correo.com','$2y$12$Pu5UcskxBmgrV3SxAtTFYuBLW9hEhcTWASaxMm8x5aF6krMcledHa','3156789012',0,1,1,NULL,'2026-11-26 18:42:58','2026-11-26 18:42:58'),
(6,'Santiago','Hernández','santi.hernandez@correo.com','$2y$12$VzZIRgll3cH5uH66ismjXuLb8wCEBU6JaxHQ8NGkF748e2HVnBk5O','3001112233',0,1,1,NULL,'2026-11-26 18:42:59','2026-11-26 18:42:59'),
(7,'Daniela','Torres','daniela.torres@correo.com','$2y$12$CgR5gg4JyroNCjHaF8pf6.nfdFqe9EyID2GG62mxltGxlSfIlB2ni','3017894561',0,1,1,NULL,'2026-11-26 18:42:59','2026-11-26 18:42:59'),
(8,'Mariana','Ramírez','mariana.ramirez@correo.com','$2y$12$QMhn2yjFlgIQUDOrd82ON.If8TLwl5Aa9L5Ij/i/jDNY9m0DZa6VG','3204445566',0,1,1,NULL,'2026-11-26 18:42:59','2026-11-26 18:42:59'),
(9,'Julián','Castro','julian.castro@correo.com','$2y$12$CeaSxi4f10ZRpLwndQmd/.MD.1e8ksRd0XObK4O2kg3P97sKvP8n6','3189990011',0,0,1,NULL,'2026-11-26 18:42:59','2026-11-26 18:42:59'),
(10,'Paula','Moreno','paula.moreno@correo.com','$2y$12$5RP4cohNGFOHGYp.ELwat.lOki08VHkJgSmFp/IprVAmdKSIfz7Rq','3162223344',0,1,0,NULL,'2026-11-26 18:43:00','2026-11-26 18:43:00');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'appsalon_mvc_php'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed
