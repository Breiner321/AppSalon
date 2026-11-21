-- =====================================================================
--  AppSalon · Script SQL completo de la base de datos appsalon_mvc_php
--  Proyecto: AppSalon (Laravel 12 + MySQL/MariaDB + Blade) · Entrega Final
--  Generado: 2026-11-21 con mysqldump (MariaDB 10.11) después de
--            php artisan migrate:fresh --seed, así que equivale a las
--            migraciones y los seeders del proyecto. Se probó importándolo
--            en una base vacía. Compatible con la MariaDB 10.4 de XAMPP.
-- ---------------------------------------------------------------------
--  Contenido: estructura de todas las tablas, llaves foráneas y datos de
--  prueba realistas.
--    · Del negocio: usuarios, servicios, citas, citasServicios, horarios
--      (horario semanal del salón) y bloqueos (fechas sin atención).
--    · De Laravel: personal_access_tokens (tokens de la API, Sanctum),
--      migrations, sessions, password_reset_tokens, cache, cache_locks,
--      jobs, job_batches y failed_jobs.
--  Datos: 10 usuarios (1 admin, 9 clientes: uno sin confirmar y uno
--  desactivado), 10 servicios, horario lunes a sábado, 1 fecha bloqueada
--  y ~150 citas entre el 10 de octubre y el 12 de diciembre de 2026.
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
(1,'2026-12-14','Capacitación del personal','2026-11-21 01:27:01');
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
) ENGINE=InnoDB AUTO_INCREMENT=149 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citas`
--

LOCK TABLES `citas` WRITE;
/*!40000 ALTER TABLE `citas` DISABLE KEYS */;
INSERT INTO `citas` VALUES
(1,'2026-11-24','10:00:00',85,2,57.00,'pendiente','2026-11-18 17:38:00','2026-11-21 01:27:01'),
(2,'2026-11-30','15:30:00',120,2,120.00,'confirmada','2026-11-23 15:17:00','2026-11-21 01:27:01'),
(3,'2026-10-10','11:00:00',180,5,200.00,'completada','2026-10-06 08:04:00','2026-11-21 01:27:01'),
(4,'2026-10-10','09:30:00',60,5,60.00,'completada','2026-09-30 18:56:00','2026-11-21 01:27:01'),
(5,'2026-10-12','11:00:00',345,3,405.00,'cancelada','2026-10-03 14:20:00','2026-11-21 01:27:01'),
(6,'2026-10-12','14:00:00',80,6,78.00,'completada','2026-10-03 13:16:00','2026-11-21 01:27:01'),
(7,'2026-10-12','16:00:00',90,7,85.00,'completada','2026-10-08 18:05:00','2026-11-21 01:27:01'),
(8,'2026-10-12','10:30:00',75,2,60.00,'cancelada','2026-10-05 09:23:00','2026-11-21 01:27:01'),
(9,'2026-10-13','11:30:00',270,3,300.00,'completada','2026-10-11 10:14:00','2026-11-21 01:27:01'),
(10,'2026-10-14','12:30:00',150,5,145.00,'completada','2026-10-07 14:29:00','2026-11-21 01:27:01'),
(11,'2026-10-14','15:00:00',220,8,272.00,'completada','2026-10-10 14:19:00','2026-11-21 01:27:01'),
(12,'2026-10-15','14:30:00',30,8,25.00,'completada','2026-10-06 10:10:00','2026-11-21 01:27:01'),
(13,'2026-10-15','15:30:00',70,6,47.00,'completada','2026-10-08 11:38:00','2026-11-21 01:27:01'),
(14,'2026-10-15','09:00:00',30,8,20.00,'completada','2026-10-11 16:20:00','2026-11-21 01:27:01'),
(15,'2026-10-16','12:30:00',80,5,58.00,'completada','2026-10-09 12:31:00','2026-11-21 01:27:01'),
(16,'2026-10-16','16:30:00',100,3,82.00,'cancelada','2026-10-08 20:58:00','2026-11-21 01:27:01'),
(17,'2026-10-16','16:00:00',120,3,120.00,'completada','2026-10-11 12:33:00','2026-11-21 01:27:01'),
(18,'2026-10-17','09:30:00',105,3,75.00,'completada','2026-10-07 14:57:00','2026-11-21 01:27:01'),
(19,'2026-10-17','13:30:00',75,5,60.00,'completada','2026-10-11 08:38:00','2026-11-21 01:27:01'),
(20,'2026-10-19','15:00:00',150,8,180.00,'completada','2026-10-14 10:18:00','2026-11-21 01:27:01'),
(21,'2026-10-19','09:00:00',200,2,198.00,'completada','2026-10-11 16:00:00','2026-11-21 01:27:01'),
(22,'2026-10-20','16:00:00',40,3,22.00,'completada','2026-10-11 14:46:00','2026-11-21 01:27:01'),
(23,'2026-10-20','11:30:00',120,6,120.00,'completada','2026-10-16 14:16:00','2026-11-21 01:27:01'),
(24,'2026-10-20','14:30:00',90,5,80.00,'completada','2026-10-19 18:47:00','2026-11-21 01:27:01'),
(25,'2026-10-21','09:00:00',390,2,470.00,'cancelada','2026-10-20 18:47:00','2026-11-21 01:27:01'),
(26,'2026-10-21','13:30:00',105,2,75.00,'completada','2026-10-18 19:35:00','2026-11-21 01:27:01'),
(27,'2026-10-22','16:00:00',105,6,75.00,'completada','2026-10-13 17:07:00','2026-11-21 01:27:01'),
(28,'2026-10-22','10:30:00',60,3,40.00,'completada','2026-10-20 16:04:00','2026-11-21 01:27:01'),
(29,'2026-10-22','11:30:00',180,5,250.00,'completada','2026-10-14 11:43:00','2026-11-21 01:27:01'),
(30,'2026-10-22','18:00:00',45,3,35.00,'completada','2026-10-13 15:48:00','2026-11-21 01:27:01'),
(31,'2026-10-23','17:00:00',100,2,82.00,'completada','2026-10-22 16:03:00','2026-11-21 01:27:01'),
(32,'2026-10-23','10:00:00',375,3,465.00,'completada','2026-10-21 11:39:00','2026-11-21 01:27:01'),
(33,'2026-10-24','10:30:00',180,8,180.00,'completada','2026-10-15 11:05:00','2026-11-21 01:27:01'),
(34,'2026-10-24','09:00:00',70,7,42.00,'completada','2026-10-15 11:05:00','2026-11-21 01:27:01'),
(35,'2026-10-26','13:00:00',60,7,60.00,'completada','2026-10-20 13:30:00','2026-11-21 01:27:01'),
(36,'2026-10-26','09:30:00',180,5,165.00,'completada','2026-10-23 19:33:00','2026-11-21 01:27:01'),
(37,'2026-10-26','14:30:00',75,10,60.00,'completada','2026-10-24 11:13:00','2026-11-21 01:27:01'),
(38,'2026-10-26','16:00:00',160,6,142.00,'cancelada','2026-10-19 13:19:00','2026-11-21 01:27:01'),
(39,'2026-10-27','12:00:00',180,5,250.00,'completada','2026-10-26 19:52:00','2026-11-21 01:27:01'),
(40,'2026-10-27','15:00:00',180,5,180.00,'completada','2026-10-18 11:06:00','2026-11-21 01:27:01'),
(41,'2026-10-27','10:00:00',60,3,40.00,'completada','2026-10-20 10:13:00','2026-11-21 01:27:01'),
(42,'2026-10-27','09:30:00',30,10,20.00,'completada','2026-10-17 13:20:00','2026-11-21 01:27:01'),
(43,'2026-10-28','13:00:00',180,7,250.00,'completada','2026-10-25 13:38:00','2026-11-21 01:27:01'),
(44,'2026-10-28','09:00:00',235,4,237.00,'completada','2026-10-18 12:59:00','2026-11-21 01:27:01'),
(45,'2026-10-28','18:30:00',30,3,20.00,'completada','2026-10-26 15:50:00','2026-11-21 01:27:01'),
(46,'2026-10-28','17:00:00',65,3,53.00,'completada','2026-10-23 17:21:00','2026-11-21 01:27:01'),
(47,'2026-10-29','09:30:00',60,2,45.00,'completada','2026-10-26 18:39:00','2026-11-21 01:27:01'),
(48,'2026-10-29','15:00:00',20,8,18.00,'completada','2026-10-23 20:57:00','2026-11-21 01:27:01'),
(49,'2026-10-29','15:30:00',135,7,95.00,'completada','2026-10-22 15:50:00','2026-11-21 01:27:01'),
(50,'2026-10-30','09:00:00',180,2,250.00,'cancelada','2026-10-27 14:55:00','2026-11-21 01:27:01'),
(51,'2026-10-30','15:00:00',180,2,200.00,'cancelada','2026-10-25 15:04:00','2026-11-21 01:27:01'),
(52,'2026-10-30','11:00:00',50,5,38.00,'cancelada','2026-10-25 15:46:00','2026-11-21 01:27:01'),
(53,'2026-10-30','15:00:00',240,8,290.00,'cancelada','2026-10-22 20:14:00','2026-11-21 01:27:01'),
(54,'2026-10-30','13:30:00',105,7,75.00,'completada','2026-10-21 09:13:00','2026-11-21 01:27:01'),
(55,'2026-10-31','09:00:00',180,8,180.00,'completada','2026-10-25 09:02:00','2026-11-21 01:27:01'),
(56,'2026-10-31','13:00:00',60,4,40.00,'completada','2026-10-25 20:14:00','2026-11-21 01:27:01'),
(57,'2026-11-02','09:30:00',190,5,202.00,'completada','2026-10-24 17:26:00','2026-11-21 01:27:01'),
(58,'2026-11-02','16:00:00',135,7,115.00,'completada','2026-10-23 18:19:00','2026-11-21 01:27:01'),
(59,'2026-11-02','14:00:00',80,7,78.00,'completada','2026-10-28 19:59:00','2026-11-21 01:27:01'),
(60,'2026-11-03','16:30:00',40,10,22.00,'completada','2026-10-31 18:48:00','2026-11-21 01:27:01'),
(61,'2026-11-03','14:00:00',60,7,40.00,'completada','2026-11-02 14:55:00','2026-11-21 01:27:01'),
(62,'2026-11-04','13:00:00',180,5,180.00,'completada','2026-11-03 09:19:00','2026-11-21 01:27:01'),
(63,'2026-11-04','09:30:00',120,4,120.00,'completada','2026-10-30 18:41:00','2026-11-21 01:27:01'),
(64,'2026-11-05','14:00:00',30,5,25.00,'completada','2026-10-28 20:55:00','2026-11-21 01:27:01'),
(65,'2026-11-05','12:00:00',120,5,120.00,'completada','2026-10-31 09:20:00','2026-11-21 01:27:01'),
(66,'2026-11-06','12:00:00',105,2,95.00,'completada','2026-10-31 18:08:00','2026-11-21 01:27:01'),
(67,'2026-11-06','10:30:00',20,5,18.00,'completada','2026-11-04 08:02:00','2026-11-21 01:27:01'),
(68,'2026-11-07','09:30:00',30,7,25.00,'completada','2026-10-28 20:18:00','2026-11-21 01:27:01'),
(69,'2026-11-07','10:00:00',225,10,285.00,'cancelada','2026-11-06 08:20:00','2026-11-21 01:27:01'),
(70,'2026-11-07','10:00:00',240,2,265.00,'completada','2026-10-31 15:14:00','2026-11-21 01:27:01'),
(71,'2026-11-09','13:00:00',180,8,200.00,'completada','2026-11-07 11:41:00','2026-11-21 01:27:01'),
(72,'2026-11-09','18:00:00',30,2,25.00,'cancelada','2026-11-04 15:28:00','2026-11-21 01:27:01'),
(73,'2026-11-10','14:00:00',150,10,180.00,'completada','2026-11-07 20:53:00','2026-11-21 01:27:01'),
(74,'2026-11-10','09:30:00',190,5,167.00,'cancelada','2026-11-05 11:02:00','2026-11-21 01:27:01'),
(75,'2026-11-10','10:00:00',180,4,160.00,'completada','2026-11-03 13:02:00','2026-11-21 01:27:01'),
(76,'2026-11-11','13:30:00',210,6,220.00,'completada','2026-11-03 19:03:00','2026-11-21 01:27:01'),
(77,'2026-11-12','11:00:00',150,6,140.00,'completada','2026-11-02 13:02:00','2026-11-21 01:27:01'),
(78,'2026-11-12','16:30:00',135,3,115.00,'completada','2026-11-03 18:53:00','2026-11-21 01:27:01'),
(79,'2026-11-12','14:00:00',75,2,55.00,'completada','2026-11-06 11:19:00','2026-11-21 01:27:01'),
(80,'2026-11-13','14:00:00',180,3,250.00,'completada','2026-11-05 14:49:00','2026-11-21 01:27:01'),
(81,'2026-11-13','10:00:00',170,2,198.00,'completada','2026-11-11 09:45:00','2026-11-21 01:27:01'),
(82,'2026-11-14','11:00:00',180,5,250.00,'completada','2026-11-11 17:15:00','2026-11-21 01:27:01'),
(83,'2026-11-16','14:30:00',30,7,25.00,'completada','2026-11-14 12:31:00','2026-11-21 01:27:01'),
(84,'2026-11-16','10:30:00',180,7,160.00,'completada','2026-11-06 14:54:00','2026-11-21 01:27:01'),
(85,'2026-11-16','15:00:00',105,2,75.00,'completada','2026-11-07 20:09:00','2026-11-21 01:27:01'),
(86,'2026-11-17','13:00:00',340,7,392.00,'cancelada','2026-11-11 10:31:00','2026-11-21 01:27:01'),
(87,'2026-11-17','10:30:00',130,2,82.00,'completada','2026-11-12 10:30:00','2026-11-21 01:27:01'),
(88,'2026-11-17','09:00:00',60,7,40.00,'completada','2026-11-08 14:09:00','2026-11-21 01:27:01'),
(89,'2026-11-17','13:00:00',60,10,45.00,'completada','2026-11-11 08:40:00','2026-11-21 01:27:01'),
(90,'2026-11-18','12:00:00',300,10,370.00,'completada','2026-11-14 13:29:00','2026-11-21 01:27:01'),
(91,'2026-11-18','18:00:00',45,3,35.00,'completada','2026-11-13 13:20:00','2026-11-21 01:27:01'),
(92,'2026-11-19','10:30:00',90,4,60.00,'completada','2026-11-14 14:12:00','2026-11-21 01:27:01'),
(93,'2026-11-19','13:30:00',60,4,40.00,'completada','2026-11-15 12:08:00','2026-11-21 01:27:01'),
(94,'2026-11-19','17:30:00',40,5,22.00,'completada','2026-11-09 10:17:00','2026-11-21 01:27:01'),
(95,'2026-11-19','10:00:00',30,8,25.00,'completada','2026-11-18 08:18:00','2026-11-21 01:27:01'),
(96,'2026-11-20','10:00:00',315,5,335.00,'completada','2026-11-11 20:24:00','2026-11-21 01:27:01'),
(97,'2026-11-20','15:30:00',210,5,200.00,'completada','2026-11-16 19:26:00','2026-11-21 01:27:01'),
(98,'2026-11-21','10:30:00',90,7,60.00,'pendiente','2026-11-17 15:40:00','2026-11-21 01:27:01'),
(99,'2026-11-21','12:00:00',150,4,145.00,'pendiente','2026-11-17 10:06:00','2026-11-21 01:27:01'),
(100,'2026-11-23','14:00:00',300,4,325.00,'pendiente','2026-11-21 17:36:00','2026-11-21 01:27:01'),
(101,'2026-11-23','10:30:00',20,3,18.00,'confirmada','2026-11-18 08:24:00','2026-11-21 01:27:01'),
(102,'2026-11-23','11:00:00',160,2,142.00,'confirmada','2026-11-19 17:48:00','2026-11-21 01:27:01'),
(103,'2026-11-24','14:00:00',210,6,200.00,'confirmada','2026-11-15 17:03:00','2026-11-21 01:27:01'),
(104,'2026-11-24','11:30:00',110,7,83.00,'confirmada','2026-11-14 15:38:00','2026-11-21 01:27:01'),
(105,'2026-11-24','17:30:00',80,6,58.00,'confirmada','2026-11-14 15:09:00','2026-11-21 01:27:01'),
(106,'2026-11-24','09:00:00',60,5,40.00,'pendiente','2026-11-19 14:05:00','2026-11-21 01:27:01'),
(107,'2026-11-25','17:00:00',120,7,100.00,'pendiente','2026-11-21 12:50:00','2026-11-21 01:27:01'),
(108,'2026-11-25','14:00:00',60,2,60.00,'confirmada','2026-11-23 13:19:00','2026-11-21 01:27:01'),
(109,'2026-11-25','15:00:00',120,7,120.00,'confirmada','2026-11-18 17:25:00','2026-11-21 01:27:01'),
(110,'2026-11-25','09:00:00',270,6,330.00,'pendiente','2026-11-19 18:41:00','2026-11-21 01:27:01'),
(111,'2026-11-26','14:30:00',90,6,60.00,'pendiente','2026-11-19 19:08:00','2026-11-21 01:27:01'),
(112,'2026-11-26','16:00:00',135,5,95.00,'confirmada','2026-11-19 14:21:00','2026-11-21 01:27:01'),
(113,'2026-11-26','09:00:00',30,7,20.00,'confirmada','2026-11-20 08:49:00','2026-11-21 01:27:01'),
(114,'2026-11-27','09:30:00',280,2,312.00,'confirmada','2026-11-21 14:46:00','2026-11-21 01:27:01'),
(115,'2026-11-27','16:00:00',180,7,250.00,'pendiente','2026-11-26 19:25:00','2026-11-21 01:27:02'),
(116,'2026-11-28','10:30:00',220,2,182.00,'pendiente','2026-11-21 09:40:00','2026-11-21 01:27:02'),
(117,'2026-11-28','09:00:00',80,5,58.00,'pendiente','2026-11-25 08:24:00','2026-11-21 01:27:02'),
(118,'2026-11-30','09:00:00',105,6,75.00,'confirmada','2026-11-26 14:36:00','2026-11-21 01:27:02'),
(119,'2026-11-30','12:00:00',30,2,25.00,'confirmada','2026-11-23 15:39:00','2026-11-21 01:27:02'),
(120,'2026-12-01','12:00:00',240,3,245.00,'confirmada','2026-11-22 17:42:00','2026-11-21 01:27:02'),
(121,'2026-12-01','11:00:00',30,2,20.00,'pendiente','2026-11-28 18:23:00','2026-11-21 01:27:02'),
(122,'2026-12-01','09:00:00',120,3,120.00,'pendiente','2026-11-29 19:25:00','2026-11-21 01:27:02'),
(123,'2026-12-02','12:00:00',225,3,285.00,'confirmada','2026-11-30 12:03:00','2026-11-21 01:27:02'),
(124,'2026-12-02','17:00:00',90,6,80.00,'confirmada','2026-11-23 19:36:00','2026-11-21 01:27:02'),
(125,'2026-12-03','10:00:00',210,3,270.00,'pendiente','2026-11-29 19:39:00','2026-11-21 01:27:02'),
(126,'2026-12-03','13:30:00',245,4,303.00,'confirmada','2026-12-01 19:35:00','2026-11-21 01:27:02'),
(127,'2026-12-04','10:00:00',345,7,405.00,'confirmada','2026-11-25 09:39:00','2026-11-21 01:27:02'),
(128,'2026-12-04','17:30:00',90,6,60.00,'pendiente','2026-11-28 11:00:00','2026-11-21 01:27:02'),
(129,'2026-12-05','12:00:00',80,2,58.00,'confirmada','2026-12-01 20:38:00','2026-11-21 01:27:02'),
(130,'2026-12-05','09:00:00',180,8,250.00,'cancelada','2026-12-02 14:42:00','2026-11-21 01:27:02'),
(131,'2026-12-07','16:00:00',45,2,35.00,'pendiente','2026-11-30 19:04:00','2026-11-21 01:27:02'),
(132,'2026-12-07','18:00:00',20,3,18.00,'cancelada','2026-11-28 19:48:00','2026-11-21 01:27:02'),
(133,'2026-12-07','09:00:00',330,5,390.00,'confirmada','2026-12-02 19:10:00','2026-11-21 01:27:02'),
(134,'2026-12-07','18:00:00',30,3,25.00,'confirmada','2026-12-05 11:23:00','2026-11-21 01:27:02'),
(135,'2026-12-08','12:00:00',180,6,200.00,'confirmada','2026-12-02 15:16:00','2026-11-21 01:27:02'),
(136,'2026-12-08','16:00:00',180,7,250.00,'confirmada','2026-12-01 09:34:00','2026-11-21 01:27:02'),
(137,'2026-12-08','10:00:00',85,8,57.00,'cancelada','2026-12-05 13:13:00','2026-11-21 01:27:02'),
(138,'2026-12-09','14:30:00',240,2,245.00,'confirmada','2026-11-30 13:10:00','2026-11-21 01:27:02'),
(139,'2026-12-09','11:30:00',180,5,205.00,'pendiente','2026-12-07 12:33:00','2026-11-21 01:27:02'),
(140,'2026-12-09','09:00:00',20,7,18.00,'confirmada','2026-12-07 10:31:00','2026-11-21 01:27:02'),
(141,'2026-12-10','14:30:00',50,6,43.00,'pendiente','2026-12-07 12:10:00','2026-11-21 01:27:02'),
(142,'2026-12-10','09:00:00',185,7,173.00,'pendiente','2026-12-09 13:02:00','2026-11-21 01:27:02'),
(143,'2026-12-10','16:00:00',115,2,82.00,'confirmada','2026-12-01 10:08:00','2026-11-21 01:27:02'),
(144,'2026-12-10','12:30:00',90,5,60.00,'pendiente','2026-12-08 14:35:00','2026-11-21 01:27:02'),
(145,'2026-12-11','13:30:00',100,7,82.00,'confirmada','2026-12-03 11:38:00','2026-11-21 01:27:02'),
(146,'2026-12-11','16:30:00',40,7,22.00,'confirmada','2026-12-06 18:34:00','2026-11-21 01:27:02'),
(147,'2026-12-12','09:30:00',70,7,47.00,'pendiente','2026-12-06 09:51:00','2026-11-21 01:27:02'),
(148,'2026-12-12','11:00:00',200,2,218.00,'confirmada','2026-12-07 12:00:00','2026-11-21 01:27:02');
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
  PRIMARY KEY (`id`),
  KEY `citasservicios_citaid_foreign` (`citaId`),
  KEY `citasservicios_servicioid_foreign` (`servicioId`),
  CONSTRAINT `citasservicios_citaid_foreign` FOREIGN KEY (`citaId`) REFERENCES `citas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `citasservicios_servicioid_foreign` FOREIGN KEY (`servicioId`) REFERENCES `servicios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=280 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citasServicios`
--

LOCK TABLES `citasServicios` WRITE;
/*!40000 ALTER TABLE `citasServicios` DISABLE KEYS */;
INSERT INTO `citasServicios` VALUES
(1,1,1),
(2,1,7),
(3,2,4),
(4,3,3),
(5,3,5),
(6,4,6),
(7,5,1),
(8,5,4),
(9,5,9),
(10,6,6),
(11,6,10),
(12,7,2),
(13,7,6),
(14,8,1),
(15,8,2),
(16,9,4),
(17,9,5),
(18,10,2),
(19,10,4),
(20,11,7),
(21,11,9),
(22,12,2),
(23,13,2),
(24,13,7),
(25,14,3),
(26,15,8),
(27,15,10),
(28,16,6),
(29,16,7),
(30,17,4),
(31,18,1),
(32,18,8),
(33,19,1),
(34,19,2),
(35,20,5),
(36,21,4),
(37,21,6),
(38,21,10),
(39,22,7),
(40,23,4),
(41,24,3),
(42,24,6),
(43,25,5),
(44,25,8),
(45,25,9),
(46,26,1),
(47,26,7),
(48,26,10),
(49,27,1),
(50,27,8),
(51,28,8),
(52,29,9),
(53,30,1),
(54,31,6),
(55,31,7),
(56,32,1),
(57,32,5),
(58,32,9),
(59,33,4),
(60,33,6),
(61,34,3),
(62,34,7),
(63,35,6),
(64,36,2),
(65,36,3),
(66,36,4),
(67,37,1),
(68,37,2),
(69,38,4),
(70,38,7),
(71,39,9),
(72,40,4),
(73,40,6),
(74,41,8),
(75,42,3),
(76,43,9),
(77,44,1),
(78,44,5),
(79,44,7),
(80,45,3),
(81,46,1),
(82,46,10),
(83,47,2),
(84,47,3),
(85,48,10),
(86,49,1),
(87,49,3),
(88,49,8),
(89,50,9),
(90,51,3),
(91,51,5),
(92,52,3),
(93,52,10),
(94,53,8),
(95,53,9),
(96,54,1),
(97,54,7),
(98,54,10),
(99,55,4),
(100,55,6),
(101,56,8),
(102,57,5),
(103,57,7),
(104,58,1),
(105,58,3),
(106,58,6),
(107,59,6),
(108,59,10),
(109,60,7),
(110,61,8),
(111,62,4),
(112,62,6),
(113,63,4),
(114,64,2),
(115,65,4),
(116,66,1),
(117,66,6),
(118,67,10),
(119,68,2),
(120,69,1),
(121,69,9),
(122,70,2),
(123,70,5),
(124,70,6),
(125,71,3),
(126,71,5),
(127,72,2),
(128,73,5),
(129,74,2),
(130,74,4),
(131,74,7),
(132,75,4),
(133,75,7),
(134,75,10),
(135,76,5),
(136,76,8),
(137,77,3),
(138,77,4),
(139,78,1),
(140,78,3),
(141,78,6),
(142,79,1),
(143,79,3),
(144,80,9),
(145,81,5),
(146,81,10),
(147,82,9),
(148,83,2),
(149,84,4),
(150,84,8),
(151,85,1),
(152,85,8),
(153,86,4),
(154,86,7),
(155,86,9),
(156,87,3),
(157,87,7),
(158,87,8),
(159,88,8),
(160,89,2),
(161,89,3),
(162,90,4),
(163,90,9),
(164,91,1),
(165,92,3),
(166,92,8),
(167,93,8),
(168,94,7),
(169,95,2),
(170,96,1),
(171,96,4),
(172,96,5),
(173,97,3),
(174,97,4),
(175,97,6),
(176,98,3),
(177,98,7),
(178,98,10),
(179,99,2),
(180,99,4),
(181,100,2),
(182,100,4),
(183,100,5),
(184,101,10),
(185,102,4),
(186,102,7),
(187,103,3),
(188,103,4),
(189,103,6),
(190,104,2),
(191,104,8),
(192,104,10),
(193,105,8),
(194,105,10),
(195,106,8),
(196,107,6),
(197,107,8),
(198,108,6),
(199,109,4),
(200,110,3),
(201,110,6),
(202,110,9),
(203,111,3),
(204,111,7),
(205,111,10),
(206,112,1),
(207,112,3),
(208,112,8),
(209,113,3),
(210,114,7),
(211,114,8),
(212,114,9),
(213,115,9),
(214,116,4),
(215,116,7),
(216,116,8),
(217,117,8),
(218,117,10),
(219,118,1),
(220,118,7),
(221,118,10),
(222,119,2),
(223,120,2),
(224,120,5),
(225,120,8),
(226,121,3),
(227,122,4),
(228,123,1),
(229,123,9),
(230,124,3),
(231,124,6),
(232,125,3),
(233,125,9),
(234,126,1),
(235,126,9),
(236,126,10),
(237,127,1),
(238,127,4),
(239,127,9),
(240,128,3),
(241,128,8),
(242,129,8),
(243,129,10),
(244,130,9),
(245,131,1),
(246,132,10),
(247,133,3),
(248,133,4),
(249,133,9),
(250,134,2),
(251,135,3),
(252,135,5),
(253,136,9),
(254,137,1),
(255,137,7),
(256,138,2),
(257,138,5),
(258,138,8),
(259,139,2),
(260,139,5),
(261,140,10),
(262,141,2),
(263,141,10),
(264,142,1),
(265,142,4),
(266,142,10),
(267,143,1),
(268,143,2),
(269,143,7),
(270,144,3),
(271,144,8),
(272,145,6),
(273,145,7),
(274,146,7),
(275,147,2),
(276,147,7),
(277,148,3),
(278,148,5),
(279,148,10);
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
(10,'2026_11_20_000004_create_personal_access_tokens_table',1);
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
(1,'Corte de cabello mujer',35.00,'Corte a la medida con lavado y secado.',45,1,'2026-11-21 01:27:01'),
(2,'Corte de cabello hombre',25.00,'Corte clásico o moderno con acabado a máquina y tijera.',30,1,'2026-11-21 01:27:01'),
(3,'Corte infantil',20.00,'Corte para niños y niñas de hasta 12 años.',30,1,'2026-11-21 01:27:01'),
(4,'Tinte completo',120.00,'Coloración de raíz a puntas con productos sin amoniaco.',120,1,'2026-11-21 01:27:01'),
(5,'Mechas o balayage',180.00,'Iluminación del cabello con técnica de mechas o balayage.',150,1,'2026-11-21 01:27:01'),
(6,'Peinado para evento',60.00,'Recogido o peinado suelto para bodas, grados y fiestas.',60,1,'2026-11-21 01:27:01'),
(7,'Manicure tradicional',22.00,'Limpieza, limado y esmaltado de uñas de las manos.',40,1,'2026-11-21 01:27:01'),
(8,'Pedicure spa',40.00,'Exfoliación, hidratación y esmaltado de uñas de los pies.',60,1,'2026-11-21 01:27:01'),
(9,'Tratamiento de keratina',250.00,'Alisado y nutrición profunda que reduce el frizz.',180,1,'2026-11-21 01:27:01'),
(10,'Diseño de cejas',18.00,'Depilación y perfilado de cejas con cera o pinza.',20,1,'2026-11-21 01:27:01');
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
(1,'Admin','Salon','admin@appsalon.com','$2y$12$61Wv5AM.ssMcwDeZ.NKfWuWcC0ySlEfW6HlXugb5OY9kxcexWu5om','3001234567',1,1,1,NULL,'2026-11-21 01:26:59','2026-11-21 01:26:59'),
(2,'Laura','Gomez','cliente@appsalon.com','$2y$12$L87aCHXX0QauenHOaFdatOV0.jd0OGc5pe6TVrf8fmXvSadb8s.vS','3007654321',0,1,1,NULL,'2026-11-21 01:26:59','2026-11-21 01:26:59'),
(3,'Camila','Rodríguez','camila.rodriguez@correo.com','$2y$12$YFMeEq0qRisPxX/7WHzzbuNeuB1.1K/R2vF3sft2zhM3HXBRSU0ai','3104567890',0,1,1,NULL,'2026-11-21 01:26:59','2026-11-21 01:26:59'),
(4,'Andrés','Martínez','andres.martinez@correo.com','$2y$12$S2pgkVMAaGVzF/EZA9CZteXoUQGRIPWohEvYKm8rQUXQS2JR2jiAK','3125551234',0,1,1,NULL,'2026-11-21 01:26:59','2026-11-21 01:26:59'),
(5,'Valentina','López','valentina.lopez@correo.com','$2y$12$6i.jgGT54tgGiLy9pHFP/u/hpOPgjZ66CJaixF13BIBA7IYjelkAK','3156789012',0,1,1,NULL,'2026-11-21 01:27:00','2026-11-21 01:27:00'),
(6,'Santiago','Hernández','santi.hernandez@correo.com','$2y$12$tBI88aLeF077f1DlVUX4Ve.yYi1QI9uXjrsr9gJFa8cCG8f5AVtWK','3001112233',0,1,1,NULL,'2026-11-21 01:27:00','2026-11-21 01:27:00'),
(7,'Daniela','Torres','daniela.torres@correo.com','$2y$12$U/JjfhWUNThz3kdeVc2JYe90cN/HzRlKAXX8IRXg49KzMFZArZ6tW','3017894561',0,1,1,NULL,'2026-11-21 01:27:00','2026-11-21 01:27:00'),
(8,'Mariana','Ramírez','mariana.ramirez@correo.com','$2y$12$rbYKn6aGk7rRtOxXqul4MOK37TmjTJxeXVgQ.u1q.a1TSXJBVz2Y2','3204445566',0,1,1,NULL,'2026-11-21 01:27:00','2026-11-21 01:27:00'),
(9,'Julián','Castro','julian.castro@correo.com','$2y$12$TN69zVt2K4gPwNZP6gMvMus7IUZP7aEvIcLXTezSGzZwHSae0UDem','3189990011',0,0,1,NULL,'2026-11-21 01:27:00','2026-11-21 01:27:00'),
(10,'Paula','Moreno','paula.moreno@correo.com','$2y$12$vWSmc5A2KmgyA7TqnCYdmuuMCis8AHWyH0w6edTyUZae6.FvneGe6','3162223344',0,1,0,NULL,'2026-11-21 01:27:01','2026-11-21 01:27:01');
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
