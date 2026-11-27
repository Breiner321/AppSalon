-- =====================================================================
--  AppSalon · Script SQL completo de la base de datos appsalon_mvc_php
--  Proyecto: AppSalon (Laravel 12 + MySQL/MariaDB + Blade) · Entrega Final
--  Generado: 2026-11-27 con mysqldump (MariaDB 10.4 de XAMPP) después de
--            php artisan migrate:fresh --seed, así que equivale a las
--            migraciones y los seeders del proyecto. Se probó importándolo
--            en una base vacía.
-- ---------------------------------------------------------------------
--  Contenido: estructura de todas las tablas, llaves foráneas y datos de
--  prueba realistas.
--    · Del negocio: usuarios (con los roles admin, empleado y cliente),
--      servicios, citas, citasServicios (con el precio de cada servicio
--      al reservar), horarios (horario semanal del salón) y bloqueos
--      (fechas sin atención).
--    · De Laravel: personal_access_tokens (tokens de la API, Sanctum),
--      migrations, sessions, password_reset_tokens, cache, cache_locks,
--      jobs, job_batches y failed_jobs.
--  Datos: 11 usuarios (1 admin, 1 empleada y 9 clientes: uno sin
--  confirmar y uno desactivado), 10 servicios, horario lunes a sábado,
--  1 fecha bloqueada y ~145 citas entre el 16 de octubre y el 17 de
--  diciembre de 2026.
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
--    Administrador: admin@appsalon.com    / Admin123
--    Empleada:      empleado@appsalon.com / Empleado123
--    Cliente:       cliente@appsalon.com  / Cliente123
--    Otros clientes (p. ej. camila.rodriguez@correo.com) / Cliente123
-- ---------------------------------------------------------------------
--  Nota: el nombre `citasServicios` se dejó con mayúscula, como en las
--  migraciones. En Windows MySQL/MariaDB lo guarda en minúsculas; en
--  Linux la mayúscula es necesaria para que Laravel encuentre la tabla.
-- =====================================================================

-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: appsalon_mvc_php
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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
/*!40101 SET character_set_client = utf8 */;
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
INSERT INTO `bloqueos` VALUES (1,'2026-12-18','Capacitación del personal','2026-11-27 08:06:44');
/*!40000 ALTER TABLE `bloqueos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=145 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citas`
--

LOCK TABLES `citas` WRITE;
/*!40000 ALTER TABLE `citas` DISABLE KEYS */;
INSERT INTO `citas` VALUES (1,'2026-11-30','10:00:00',85,2,57.00,'pendiente','2026-11-24 22:38:00','2026-11-27 08:06:44'),(2,'2026-12-04','15:30:00',120,2,120.00,'confirmada','2026-11-27 20:17:00','2026-11-27 08:06:44'),(3,'2026-10-16','14:00:00',60,5,45.00,'completada','2026-10-12 13:04:00','2026-11-27 08:06:44'),(4,'2026-10-16','12:00:00',45,7,35.00,'completada','2026-10-07 13:27:00','2026-11-27 08:06:44'),(5,'2026-10-16','17:00:00',110,10,78.00,'completada','2026-10-12 15:18:00','2026-11-27 08:06:44'),(6,'2026-10-17','09:30:00',200,2,218.00,'completada','2026-10-09 19:41:00','2026-11-27 08:06:44'),(7,'2026-10-17','13:30:00',50,7,38.00,'completada','2026-10-13 23:05:00','2026-11-27 08:06:44'),(8,'2026-10-19','13:30:00',180,2,180.00,'cancelada','2026-10-12 14:23:00','2026-11-27 08:06:44'),(9,'2026-10-19','14:30:00',45,7,35.00,'completada','2026-10-16 17:15:00','2026-11-27 08:06:44'),(10,'2026-10-19','15:30:00',110,10,78.00,'completada','2026-10-09 18:06:00','2026-11-27 08:06:44'),(11,'2026-10-20','11:30:00',190,4,167.00,'completada','2026-10-15 13:51:00','2026-11-27 08:06:44'),(12,'2026-10-20','15:30:00',200,6,268.00,'completada','2026-10-13 22:48:00','2026-11-27 08:06:44'),(13,'2026-10-21','09:00:00',210,7,220.00,'completada','2026-10-14 20:21:00','2026-11-27 08:06:44'),(14,'2026-10-21','16:00:00',180,2,165.00,'completada','2026-10-15 13:42:00','2026-11-27 08:06:44'),(15,'2026-10-21','12:30:00',100,5,62.00,'completada','2026-10-16 15:36:00','2026-11-27 08:06:44'),(16,'2026-10-22','11:30:00',120,7,80.00,'completada','2026-10-20 19:15:00','2026-11-27 08:06:44'),(17,'2026-10-22','14:30:00',90,10,80.00,'completada','2026-10-18 22:29:00','2026-11-27 08:06:44'),(18,'2026-10-22','16:00:00',80,4,58.00,'cancelada','2026-10-21 16:49:00','2026-11-27 08:06:44'),(19,'2026-10-23','11:00:00',20,5,18.00,'completada','2026-10-14 21:48:00','2026-11-27 08:06:44'),(20,'2026-10-23','14:30:00',40,8,22.00,'completada','2026-10-18 15:18:00','2026-11-27 08:06:45'),(21,'2026-10-23','11:30:00',180,6,250.00,'completada','2026-10-16 14:33:00','2026-11-27 08:06:45'),(22,'2026-10-23','16:00:00',180,3,250.00,'completada','2026-10-14 19:46:00','2026-11-27 08:06:45'),(23,'2026-10-24','10:30:00',115,4,77.00,'completada','2026-10-17 19:22:00','2026-11-27 08:06:45'),(24,'2026-10-24','12:30:00',30,4,25.00,'completada','2026-10-16 15:11:00','2026-11-27 08:06:45'),(25,'2026-10-26','10:00:00',210,4,270.00,'completada','2026-10-18 23:39:00','2026-11-27 08:06:45'),(26,'2026-10-26','14:30:00',270,4,300.00,'completada','2026-10-20 13:16:00','2026-11-27 08:06:45'),(27,'2026-10-27','12:00:00',60,2,40.00,'completada','2026-10-19 19:23:00','2026-11-27 08:06:45'),(28,'2026-10-27','13:30:00',330,7,360.00,'completada','2026-10-22 18:12:00','2026-11-27 08:06:45'),(29,'2026-10-28','10:30:00',160,5,142.00,'completada','2026-10-23 21:25:00','2026-11-27 08:06:45'),(30,'2026-10-28','14:00:00',120,6,120.00,'completada','2026-10-19 13:52:00','2026-11-27 08:06:45'),(31,'2026-10-29','09:30:00',120,8,105.00,'completada','2026-10-19 20:21:00','2026-11-27 08:06:45'),(32,'2026-10-29','14:00:00',60,5,45.00,'cancelada','2026-10-23 00:22:00','2026-11-27 08:06:45'),(33,'2026-10-30','12:00:00',125,3,113.00,'completada','2026-10-23 15:54:00','2026-11-27 08:06:45'),(34,'2026-10-30','15:00:00',240,10,290.00,'completada','2026-10-21 00:53:00','2026-11-27 08:06:45'),(35,'2026-10-31','12:00:00',30,6,20.00,'completada','2026-10-25 19:32:00','2026-11-27 08:06:45'),(36,'2026-10-31','12:30:00',120,7,120.00,'cancelada','2026-10-27 21:51:00','2026-11-27 08:06:45'),(37,'2026-10-31','13:30:00',50,2,38.00,'completada','2026-10-28 00:11:00','2026-11-27 08:06:45'),(38,'2026-11-02','16:00:00',120,10,80.00,'completada','2026-10-31 18:58:00','2026-11-27 08:06:45'),(39,'2026-11-02','10:00:00',60,2,60.00,'completada','2026-10-31 20:39:00','2026-11-27 08:06:45'),(40,'2026-11-03','10:00:00',200,4,268.00,'completada','2026-10-28 19:36:00','2026-11-27 08:06:45'),(41,'2026-11-03','14:00:00',180,3,250.00,'completada','2026-10-26 16:20:00','2026-11-27 08:06:45'),(42,'2026-11-03','17:00:00',100,6,62.00,'completada','2026-11-01 13:57:00','2026-11-27 08:06:45'),(43,'2026-11-04','14:00:00',130,5,87.00,'completada','2026-11-01 14:09:00','2026-11-27 08:06:45'),(44,'2026-11-04','10:00:00',230,5,238.00,'cancelada','2026-10-31 16:51:00','2026-11-27 08:06:45'),(45,'2026-11-04','09:00:00',290,8,318.00,'cancelada','2026-10-25 20:34:00','2026-11-27 08:06:45'),(46,'2026-11-04','16:30:00',125,6,93.00,'completada','2026-10-29 22:00:00','2026-11-27 08:06:45'),(47,'2026-11-05','11:00:00',45,5,35.00,'completada','2026-11-04 19:21:00','2026-11-27 08:06:45'),(48,'2026-11-05','17:30:00',45,7,35.00,'completada','2026-10-30 18:33:00','2026-11-27 08:06:45'),(49,'2026-11-05','13:30:00',220,4,182.00,'completada','2026-10-29 13:00:00','2026-11-27 08:06:45'),(50,'2026-11-06','10:30:00',255,10,255.00,'completada','2026-11-03 22:58:00','2026-11-27 08:06:45'),(51,'2026-11-06','15:00:00',240,6,240.00,'completada','2026-10-28 17:17:00','2026-11-27 08:06:45'),(52,'2026-11-07','14:00:00',60,8,45.00,'cancelada','2026-10-31 01:14:00','2026-11-27 08:06:45'),(53,'2026-11-07','11:30:00',140,7,118.00,'completada','2026-10-29 14:13:00','2026-11-27 08:06:45'),(54,'2026-11-07','09:00:00',120,6,100.00,'completada','2026-11-05 00:57:00','2026-11-27 08:06:45'),(55,'2026-11-09','14:00:00',225,8,195.00,'completada','2026-11-03 14:58:00','2026-11-27 08:06:45'),(56,'2026-11-09','09:30:00',180,7,250.00,'completada','2026-11-02 19:47:00','2026-11-27 08:06:45'),(57,'2026-11-10','15:30:00',135,4,100.00,'cancelada','2026-11-09 23:37:00','2026-11-27 08:06:45'),(58,'2026-11-10','12:30:00',165,10,135.00,'completada','2026-11-06 00:59:00','2026-11-27 08:06:45'),(59,'2026-11-11','12:00:00',30,10,20.00,'completada','2026-11-08 23:48:00','2026-11-27 08:06:45'),(60,'2026-11-11','16:00:00',180,7,250.00,'completada','2026-11-10 19:55:00','2026-11-27 08:06:45'),(61,'2026-11-12','10:30:00',210,5,240.00,'completada','2026-11-11 14:19:00','2026-11-27 08:06:45'),(62,'2026-11-12','18:00:00',30,4,25.00,'completada','2026-11-07 23:41:00','2026-11-27 08:06:45'),(63,'2026-11-13','10:30:00',45,5,35.00,'completada','2026-11-06 01:55:00','2026-11-27 08:06:45'),(64,'2026-11-13','16:30:00',40,5,22.00,'completada','2026-11-08 14:20:00','2026-11-27 08:06:45'),(65,'2026-11-14','12:00:00',100,2,62.00,'completada','2026-11-08 23:08:00','2026-11-27 08:06:45'),(66,'2026-11-14','10:30:00',30,5,20.00,'completada','2026-11-12 13:02:00','2026-11-27 08:06:45'),(67,'2026-11-16','13:00:00',120,7,120.00,'completada','2026-11-07 01:18:00','2026-11-27 08:06:45'),(68,'2026-11-16','09:00:00',200,10,268.00,'cancelada','2026-11-15 13:20:00','2026-11-27 08:06:45'),(69,'2026-11-17','15:30:00',140,8,118.00,'completada','2026-11-12 20:16:00','2026-11-27 08:06:46'),(70,'2026-11-17','10:00:00',285,7,325.00,'completada','2026-11-11 17:51:00','2026-11-27 08:06:46'),(71,'2026-11-18','09:30:00',300,8,320.00,'completada','2026-11-09 16:09:00','2026-11-27 08:06:46'),(72,'2026-11-18','14:30:00',270,10,300.00,'completada','2026-11-15 21:38:00','2026-11-27 08:06:46'),(73,'2026-11-19','13:00:00',330,4,430.00,'completada','2026-11-16 21:04:00','2026-11-27 08:06:46'),(74,'2026-11-19','09:00:00',240,7,265.00,'completada','2026-11-10 23:12:00','2026-11-27 08:06:46'),(75,'2026-11-20','09:30:00',75,5,55.00,'cancelada','2026-11-16 00:08:00','2026-11-27 08:06:46'),(76,'2026-11-20','14:30:00',225,7,240.00,'completada','2026-11-18 00:23:00','2026-11-27 08:06:46'),(77,'2026-11-21','13:00:00',90,4,65.00,'completada','2026-11-12 01:03:00','2026-11-27 08:06:46'),(78,'2026-11-21','10:30:00',140,7,138.00,'completada','2026-11-12 01:03:00','2026-11-27 08:06:46'),(79,'2026-11-21','09:00:00',90,2,60.00,'completada','2026-11-18 18:13:00','2026-11-27 08:06:46'),(80,'2026-11-23','18:30:00',30,10,20.00,'completada','2026-11-17 21:44:00','2026-11-27 08:06:46'),(81,'2026-11-23','09:30:00',120,5,120.00,'completada','2026-11-20 22:15:00','2026-11-27 08:06:46'),(82,'2026-11-23','13:30:00',230,8,288.00,'cancelada','2026-11-19 22:31:00','2026-11-27 08:06:46'),(83,'2026-11-23','12:00:00',40,3,22.00,'completada','2026-11-21 17:09:00','2026-11-27 08:06:46'),(84,'2026-11-23','13:00:00',180,2,250.00,'completada','2026-11-14 15:18:00','2026-11-27 08:06:46'),(85,'2026-11-24','11:00:00',120,10,120.00,'cancelada','2026-11-14 21:35:00','2026-11-27 08:06:46'),(86,'2026-11-24','14:30:00',210,3,220.00,'completada','2026-11-17 23:24:00','2026-11-27 08:06:46'),(87,'2026-11-24','10:30:00',45,8,35.00,'cancelada','2026-11-23 00:08:00','2026-11-27 08:06:46'),(88,'2026-11-25','13:00:00',120,4,120.00,'completada','2026-11-17 22:36:00','2026-11-27 08:06:46'),(89,'2026-11-25','17:00:00',75,3,55.00,'completada','2026-11-19 14:23:00','2026-11-27 08:06:46'),(90,'2026-11-25','15:00:00',120,10,120.00,'completada','2026-11-20 22:05:00','2026-11-27 08:06:46'),(91,'2026-11-25','10:00:00',45,2,35.00,'completada','2026-11-20 16:03:00','2026-11-27 08:06:46'),(92,'2026-11-25','11:00:00',120,4,100.00,'completada','2026-11-22 23:50:00','2026-11-27 08:06:46'),(93,'2026-11-26','12:30:00',100,4,82.00,'completada','2026-11-16 14:07:00','2026-11-27 08:06:46'),(94,'2026-11-26','14:30:00',270,7,310.00,'cancelada','2026-11-20 15:30:00','2026-11-27 08:06:46'),(95,'2026-11-26','09:00:00',180,2,160.00,'completada','2026-11-25 15:02:00','2026-11-27 08:06:46'),(96,'2026-11-26','17:00:00',120,8,120.00,'completada','2026-11-21 13:59:00','2026-11-27 08:06:46'),(97,'2026-11-27','12:30:00',360,10,430.00,'pendiente','2026-11-24 21:33:00','2026-11-27 08:06:46'),(98,'2026-11-27','10:30:00',115,7,82.00,'confirmada','2026-11-27 01:46:00','2026-11-27 08:06:46'),(99,'2026-11-28','13:30:00',60,4,40.00,'pendiente','2026-11-22 15:22:00','2026-11-27 08:06:46'),(100,'2026-11-28','11:30:00',40,7,22.00,'pendiente','2026-11-21 16:25:00','2026-11-27 08:06:46'),(101,'2026-11-30','13:30:00',310,6,322.00,'pendiente','2026-11-23 21:13:00','2026-11-27 08:06:46'),(102,'2026-12-01','11:30:00',20,3,18.00,'confirmada','2026-11-21 15:24:00','2026-11-27 08:06:46'),(103,'2026-12-01','15:30:00',105,2,95.00,'pendiente','2026-11-27 20:33:00','2026-11-27 08:06:46'),(104,'2026-12-01','12:30:00',100,5,82.00,'pendiente','2026-11-22 21:44:00','2026-11-27 08:06:46'),(105,'2026-12-01','10:00:00',70,3,42.00,'confirmada','2026-11-30 15:13:00','2026-11-27 08:06:46'),(106,'2026-12-02','11:30:00',330,4,360.00,'cancelada','2026-11-22 19:21:00','2026-11-27 08:06:46'),(107,'2026-12-02','13:00:00',170,4,163.00,'pendiente','2026-11-29 19:16:00','2026-11-27 08:06:46'),(108,'2026-12-02','09:00:00',215,3,233.00,'confirmada','2026-11-24 17:29:00','2026-11-27 08:06:46'),(109,'2026-12-02','16:30:00',60,5,40.00,'pendiente','2026-11-23 20:24:00','2026-11-27 08:06:46'),(110,'2026-12-03','13:00:00',95,3,73.00,'pendiente','2026-11-25 20:08:00','2026-11-27 08:06:46'),(111,'2026-12-03','09:00:00',65,2,53.00,'pendiente','2026-11-30 01:23:00','2026-11-27 08:06:46'),(112,'2026-12-03','15:00:00',220,2,222.00,'confirmada','2026-11-24 20:21:00','2026-11-27 08:06:46'),(113,'2026-12-04','09:30:00',190,7,202.00,'pendiente','2026-12-04 00:27:00','2026-11-27 08:06:46'),(114,'2026-12-04','14:30:00',40,2,22.00,'confirmada','2026-11-28 00:27:00','2026-11-27 08:06:46'),(115,'2026-12-04','17:30:00',70,7,47.00,'confirmada','2026-12-01 14:03:00','2026-11-27 08:06:46'),(116,'2026-12-05','11:00:00',90,6,85.00,'confirmada','2026-11-27 15:46:00','2026-11-27 08:06:46'),(117,'2026-12-05','12:30:00',90,6,60.00,'pendiente','2026-12-04 19:50:00','2026-11-27 08:06:46'),(118,'2026-12-07','14:30:00',80,4,63.00,'confirmada','2026-12-02 18:31:00','2026-11-27 08:06:46'),(119,'2026-12-07','10:00:00',250,8,262.00,'pendiente','2026-12-06 13:33:00','2026-11-27 08:06:47'),(120,'2026-12-08','14:00:00',210,2,200.00,'pendiente','2026-12-06 20:48:00','2026-11-27 08:06:47'),(121,'2026-12-08','09:00:00',120,6,120.00,'confirmada','2026-12-06 14:05:00','2026-11-27 08:06:47'),(122,'2026-12-08','11:30:00',60,2,40.00,'pendiente','2026-12-03 13:04:00','2026-11-27 08:06:47'),(123,'2026-12-09','10:00:00',30,5,20.00,'confirmada','2026-12-05 18:40:00','2026-11-27 08:06:47'),(124,'2026-12-09','12:30:00',330,4,395.00,'confirmada','2026-12-02 01:38:00','2026-11-27 08:06:47'),(125,'2026-12-09','18:30:00',30,8,25.00,'confirmada','2026-11-29 13:10:00','2026-11-27 08:06:47'),(126,'2026-12-09','18:00:00',30,7,25.00,'pendiente','2026-12-08 01:15:00','2026-11-27 08:06:47'),(127,'2026-12-10','11:00:00',60,7,40.00,'confirmada','2026-12-05 16:15:00','2026-11-27 08:06:47'),(128,'2026-12-10','12:00:00',250,5,242.00,'confirmada','2026-12-03 23:16:00','2026-11-27 08:06:47'),(129,'2026-12-10','09:00:00',105,4,95.00,'confirmada','2026-12-03 13:01:00','2026-11-27 08:06:47'),(130,'2026-12-11','10:00:00',250,5,292.00,'pendiente','2026-12-01 18:58:00','2026-11-27 08:06:47'),(131,'2026-12-11','16:30:00',60,4,60.00,'confirmada','2026-12-05 21:42:00','2026-11-27 08:06:47'),(132,'2026-12-11','15:00:00',75,6,55.00,'confirmada','2026-12-06 16:52:00','2026-11-27 08:06:47'),(133,'2026-12-11','09:00:00',45,2,35.00,'pendiente','2026-12-08 18:55:00','2026-11-27 08:06:47'),(134,'2026-12-12','12:30:00',30,8,20.00,'pendiente','2026-12-04 17:03:00','2026-11-27 08:06:47'),(135,'2026-12-14','09:30:00',360,2,430.00,'pendiente','2026-12-07 14:26:00','2026-11-27 08:06:47'),(136,'2026-12-14','15:30:00',210,5,225.00,'pendiente','2026-12-12 18:26:00','2026-11-27 08:06:47'),(137,'2026-12-15','10:00:00',75,8,55.00,'cancelada','2026-12-12 18:13:00','2026-11-27 08:06:47'),(138,'2026-12-15','09:00:00',225,7,215.00,'pendiente','2026-12-11 17:58:00','2026-11-27 08:06:47'),(139,'2026-12-16','09:00:00',120,2,100.00,'pendiente','2026-12-15 16:51:00','2026-11-27 08:06:47'),(140,'2026-12-16','15:30:00',30,7,25.00,'pendiente','2026-12-13 16:42:00','2026-11-27 08:06:47'),(141,'2026-12-17','14:30:00',30,4,25.00,'cancelada','2026-12-15 00:17:00','2026-11-27 08:06:47'),(142,'2026-12-17','14:00:00',60,4,60.00,'pendiente','2026-12-16 19:49:00','2026-11-27 08:06:47'),(143,'2026-12-17','15:30:00',195,8,175.00,'pendiente','2026-12-09 22:22:00','2026-11-27 08:06:47'),(144,'2026-12-17','09:00:00',255,2,255.00,'confirmada','2026-12-08 13:19:00','2026-11-27 08:06:47');
/*!40000 ALTER TABLE `citas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `citasServicios`
--

DROP TABLE IF EXISTS `citasServicios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=287 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citasServicios`
--

LOCK TABLES `citasServicios` WRITE;
/*!40000 ALTER TABLE `citasServicios` DISABLE KEYS */;
INSERT INTO `citasServicios` VALUES (1,1,1,35.00),(2,1,7,22.00),(3,2,4,120.00),(4,3,2,25.00),(5,3,3,20.00),(6,4,1,35.00),(7,5,3,20.00),(8,5,8,40.00),(9,5,10,18.00),(10,6,3,20.00),(11,6,5,180.00),(12,6,10,18.00),(13,7,3,20.00),(14,7,10,18.00),(15,8,4,120.00),(16,8,6,60.00),(17,9,1,35.00),(18,10,3,20.00),(19,10,8,40.00),(20,10,10,18.00),(21,11,2,25.00),(22,11,4,120.00),(23,11,7,22.00),(24,12,9,250.00),(25,12,10,18.00),(26,13,5,180.00),(27,13,8,40.00),(28,14,2,25.00),(29,14,3,20.00),(30,14,4,120.00),(31,15,7,22.00),(32,15,8,40.00),(33,16,7,22.00),(34,16,8,40.00),(35,16,10,18.00),(36,17,3,20.00),(37,17,6,60.00),(38,18,8,40.00),(39,18,10,18.00),(40,19,10,18.00),(41,20,7,22.00),(42,21,9,250.00),(43,22,9,250.00),(44,23,1,35.00),(45,23,3,20.00),(46,23,7,22.00),(47,24,2,25.00),(48,25,3,20.00),(49,25,9,250.00),(50,26,4,120.00),(51,26,5,180.00),(52,27,8,40.00),(53,28,4,120.00),(54,28,5,180.00),(55,28,6,60.00),(56,29,4,120.00),(57,29,7,22.00),(58,30,4,120.00),(59,31,2,25.00),(60,31,3,20.00),(61,31,6,60.00),(62,32,2,25.00),(63,32,3,20.00),(64,33,1,35.00),(65,33,6,60.00),(66,33,10,18.00),(67,34,8,40.00),(68,34,9,250.00),(69,35,3,20.00),(70,36,4,120.00),(71,37,3,20.00),(72,37,10,18.00),(73,38,7,22.00),(74,38,8,40.00),(75,38,10,18.00),(76,39,6,60.00),(77,40,9,250.00),(78,40,10,18.00),(79,41,9,250.00),(80,42,7,22.00),(81,42,8,40.00),(82,43,2,25.00),(83,43,7,22.00),(84,43,8,40.00),(85,44,5,180.00),(86,44,8,40.00),(87,44,10,18.00),(88,45,4,120.00),(89,45,5,180.00),(90,45,10,18.00),(91,46,1,35.00),(92,46,8,40.00),(93,46,10,18.00),(94,47,1,35.00),(95,48,1,35.00),(96,49,4,120.00),(97,49,7,22.00),(98,49,8,40.00),(99,50,1,35.00),(100,50,5,180.00),(101,50,8,40.00),(102,51,3,20.00),(103,51,5,180.00),(104,51,8,40.00),(105,52,2,25.00),(106,52,3,20.00),(107,53,6,60.00),(108,53,8,40.00),(109,53,10,18.00),(110,54,6,60.00),(111,54,8,40.00),(112,55,1,35.00),(113,55,4,120.00),(114,55,8,40.00),(115,56,9,250.00),(116,57,1,35.00),(117,57,2,25.00),(118,57,8,40.00),(119,58,1,35.00),(120,58,6,60.00),(121,58,8,40.00),(122,59,3,20.00),(123,60,9,250.00),(124,61,5,180.00),(125,61,6,60.00),(126,62,2,25.00),(127,63,1,35.00),(128,64,7,22.00),(129,65,7,22.00),(130,65,8,40.00),(131,66,3,20.00),(132,67,4,120.00),(133,68,9,250.00),(134,68,10,18.00),(135,69,6,60.00),(136,69,8,40.00),(137,69,10,18.00),(138,70,1,35.00),(139,70,8,40.00),(140,70,9,250.00),(141,71,3,20.00),(142,71,4,120.00),(143,71,5,180.00),(144,72,4,120.00),(145,72,5,180.00),(146,73,5,180.00),(147,73,9,250.00),(148,74,2,25.00),(149,74,5,180.00),(150,74,6,60.00),(151,75,1,35.00),(152,75,3,20.00),(153,76,1,35.00),(154,76,2,25.00),(155,76,5,180.00),(156,77,2,25.00),(157,77,8,40.00),(158,78,4,120.00),(159,78,10,18.00),(160,79,3,20.00),(161,79,8,40.00),(162,80,3,20.00),(163,81,4,120.00),(164,82,3,20.00),(165,82,9,250.00),(166,82,10,18.00),(167,83,7,22.00),(168,84,9,250.00),(169,85,4,120.00),(170,86,5,180.00),(171,86,7,22.00),(172,86,10,18.00),(173,87,1,35.00),(174,88,4,120.00),(175,89,1,35.00),(176,89,3,20.00),(177,90,4,120.00),(178,91,1,35.00),(179,92,6,60.00),(180,92,8,40.00),(181,93,6,60.00),(182,93,7,22.00),(183,94,3,20.00),(184,94,8,40.00),(185,94,9,250.00),(186,95,4,120.00),(187,95,8,40.00),(188,96,4,120.00),(189,97,4,120.00),(190,97,6,60.00),(191,97,9,250.00),(192,98,1,35.00),(193,98,2,25.00),(194,98,7,22.00),(195,99,8,40.00),(196,100,7,22.00),(197,101,4,120.00),(198,101,5,180.00),(199,101,7,22.00),(200,102,10,18.00),(201,103,1,35.00),(202,103,6,60.00),(203,104,6,60.00),(204,104,7,22.00),(205,105,3,20.00),(206,105,7,22.00),(207,106,4,120.00),(208,106,5,180.00),(209,106,6,60.00),(210,107,2,25.00),(211,107,4,120.00),(212,107,10,18.00),(213,108,1,35.00),(214,108,5,180.00),(215,108,10,18.00),(216,109,8,40.00),(217,110,1,35.00),(218,110,3,20.00),(219,110,10,18.00),(220,111,1,35.00),(221,111,10,18.00),(222,112,3,20.00),(223,112,5,180.00),(224,112,7,22.00),(225,113,5,180.00),(226,113,7,22.00),(227,114,7,22.00),(228,115,2,25.00),(229,115,7,22.00),(230,116,2,25.00),(231,116,6,60.00),(232,117,3,20.00),(233,117,8,40.00),(234,118,2,25.00),(235,118,3,20.00),(236,118,10,18.00),(237,119,5,180.00),(238,119,6,60.00),(239,119,7,22.00),(240,120,3,20.00),(241,120,4,120.00),(242,120,6,60.00),(243,121,4,120.00),(244,122,8,40.00),(245,123,3,20.00),(246,124,2,25.00),(247,124,4,120.00),(248,124,9,250.00),(249,125,2,25.00),(250,126,2,25.00),(251,127,8,40.00),(252,128,5,180.00),(253,128,7,22.00),(254,128,8,40.00),(255,129,1,35.00),(256,129,6,60.00),(257,130,3,20.00),(258,130,7,22.00),(259,130,9,250.00),(260,131,6,60.00),(261,132,1,35.00),(262,132,3,20.00),(263,133,1,35.00),(264,134,3,20.00),(265,135,4,120.00),(266,135,6,60.00),(267,135,9,250.00),(268,136,2,25.00),(269,136,3,20.00),(270,136,5,180.00),(271,137,1,35.00),(272,137,3,20.00),(273,138,1,35.00),(274,138,4,120.00),(275,138,6,60.00),(276,139,6,60.00),(277,139,8,40.00),(278,140,2,25.00),(279,141,2,25.00),(280,142,6,60.00),(281,143,1,35.00),(282,143,3,20.00),(283,143,4,120.00),(284,144,1,35.00),(285,144,5,180.00),(286,144,8,40.00);
/*!40000 ALTER TABLE `citasServicios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
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
INSERT INTO `horarios` VALUES (1,0,'09:00:00','13:00:00',0),(2,1,'09:00:00','19:00:00',1),(3,2,'09:00:00','19:00:00',1),(4,3,'09:00:00','19:00:00',1),(5,4,'09:00:00','19:00:00',1),(6,5,'09:00:00','19:00:00',1),(7,6,'09:00:00','15:00:00',1);
/*!40000 ALTER TABLE `horarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_10_23_000001_create_servicios_table',1),(5,'2026_10_23_000002_create_citas_table',1),(6,'2026_10_23_000003_create_citas_servicios_table',1),(7,'2026_11_20_000001_add_activo_to_usuarios_table',1),(8,'2026_11_20_000002_add_duracion_to_citas_table',1),(9,'2026_11_20_000003_create_horarios_table',1),(10,'2026_11_20_000004_create_personal_access_tokens_table',1),(11,'2026_11_26_000001_add_precio_to_citas_servicios_table',1),(12,'2026_11_27_000001_add_empleado_to_usuarios_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
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
INSERT INTO `servicios` VALUES (1,'Corte de cabello mujer',35.00,'Corte a la medida con lavado y secado.',45,1,'2026-11-27 08:06:44'),(2,'Corte de cabello hombre',25.00,'Corte clásico o moderno con acabado a máquina y tijera.',30,1,'2026-11-27 08:06:44'),(3,'Corte infantil',20.00,'Corte para niños y niñas de hasta 12 años.',30,1,'2026-11-27 08:06:44'),(4,'Tinte completo',120.00,'Coloración de raíz a puntas con productos sin amoniaco.',120,1,'2026-11-27 08:06:44'),(5,'Mechas o balayage',180.00,'Iluminación del cabello con técnica de mechas o balayage.',150,1,'2026-11-27 08:06:44'),(6,'Peinado para evento',60.00,'Recogido o peinado suelto para bodas, grados y fiestas.',60,1,'2026-11-27 08:06:44'),(7,'Manicure tradicional',22.00,'Limpieza, limado y esmaltado de uñas de las manos.',40,1,'2026-11-27 08:06:44'),(8,'Pedicure spa',40.00,'Exfoliación, hidratación y esmaltado de uñas de los pies.',60,1,'2026-11-27 08:06:44'),(9,'Tratamiento de keratina',250.00,'Alisado y nutrición profunda que reduce el frizz.',180,1,'2026-11-27 08:06:44'),(10,'Diseño de cejas',18.00,'Depilación y perfilado de cejas con cera o pinza.',20,1,'2026-11-27 08:06:44');
/*!40000 ALTER TABLE `servicios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) NOT NULL,
  `apellido` varchar(60) NOT NULL,
  `email` varchar(30) NOT NULL,
  `password` varchar(60) NOT NULL,
  `telefono` varchar(10) DEFAULT NULL,
  `admin` tinyint(1) NOT NULL DEFAULT 0,
  `empleado` tinyint(1) NOT NULL DEFAULT 0,
  `confirmado` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `token` varchar(15) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuarios_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Admin','Salon','admin@appsalon.com','$2y$12$pOzVC9AEIEgynlYaKdNHEOPt6wuNnjdMT7SqwAgBeg2f0DVtXotNq','3001234567',1,0,1,1,NULL,'2026-11-27 08:06:39','2026-11-27 08:06:39'),(2,'Laura','Gomez','cliente@appsalon.com','$2y$12$dScML1mptSPyR9J2A9w7KOgd3UvGHtDzTHgUBNl82stEkIxf2wbJq','3007654321',0,0,1,1,NULL,'2026-11-27 08:06:40','2026-11-27 08:06:40'),(3,'Camila','Rodríguez','camila.rodriguez@correo.com','$2y$12$ZKs/iNgnJuyfVcOFPy8a9uE134eSEp2sxk8siqtvR4Sj9BlojWrBe','3104567890',0,0,1,1,NULL,'2026-11-27 08:06:40','2026-11-27 08:06:40'),(4,'Andrés','Martínez','andres.martinez@correo.com','$2y$12$1YRj/ABuZgtGUdwNKEKK8e0uxNzhDQAo864PWzawPC/YQ5720y41m','3125551234',0,0,1,1,NULL,'2026-11-27 08:06:41','2026-11-27 08:06:41'),(5,'Valentina','López','valentina.lopez@correo.com','$2y$12$RlkxgtN.LXqAFqW1FLU12ufGgPOjPA1Ald4MgLTUEptcwrsnLxm/6','3156789012',0,0,1,1,NULL,'2026-11-27 08:06:41','2026-11-27 08:06:41'),(6,'Santiago','Hernández','santi.hernandez@correo.com','$2y$12$OVofbXAZbNLXfxq8vlOQbe3nM4IwTWhxVdMIvwKz8en6H6/1aAAYO','3001112233',0,0,1,1,NULL,'2026-11-27 08:06:42','2026-11-27 08:06:42'),(7,'Daniela','Torres','daniela.torres@correo.com','$2y$12$eN9ALgqtvzOaVTYYCoQVN.7FnKOqrb3AheddQYGA3IV.N7tNsKTgK','3017894561',0,0,1,1,NULL,'2026-11-27 08:06:42','2026-11-27 08:06:42'),(8,'Mariana','Ramírez','mariana.ramirez@correo.com','$2y$12$VwrwzHFWF2.BilJkcv8.EOPb2G1bz.VJzxFMGIYGJHCIVUyM5vl0K','3204445566',0,0,1,1,NULL,'2026-11-27 08:06:43','2026-11-27 08:06:43'),(9,'Julián','Castro','julian.castro@correo.com','$2y$12$u4OdSpAtSn3mVMnfpwlqSuuBNY8s64E9vs1wpZ.E1uwpVGlaXPosa','3189990011',0,0,0,1,NULL,'2026-11-27 08:06:43','2026-11-27 08:06:43'),(10,'Paula','Moreno','paula.moreno@correo.com','$2y$12$EKl7ZNicv81IoJkwCeWovOJZDVKlZ.rnfpJ..3y92m.CtHCOfTlw6','3162223344',0,0,1,0,NULL,'2026-11-27 08:06:43','2026-11-27 08:06:43'),(11,'Sofía','Vargas','empleado@appsalon.com','$2y$12$aG4P7joSSahhUC1SBWVpoeIrEKUHpCBJkhqztQNUEt7rpOBiCvRt6','3009876543',0,1,1,1,NULL,'2026-11-27 08:06:44','2026-11-27 08:06:44');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed
