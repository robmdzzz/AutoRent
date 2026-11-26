-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.4.3 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para autorent_db
CREATE DATABASE IF NOT EXISTS `autorent_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `autorent_db`;

-- Volcando estructura para tabla autorent_db.automoviles
CREATE TABLE IF NOT EXISTS `automoviles` (
  `id_auto` int NOT NULL AUTO_INCREMENT,
  `id_categoria` int DEFAULT NULL,
  `marca` varchar(50) NOT NULL,
  `modelo` varchar(50) NOT NULL,
  `año` int NOT NULL,
  `precio_dia` decimal(10,2) NOT NULL,
  `pasajeros` int DEFAULT NULL,
  `transmision` enum('automatica','manual') NOT NULL,
  `descripcion` text,
  `imagen` varchar(255) DEFAULT NULL,
  `estado` enum('disponible','rentado','mantenimiento') NOT NULL DEFAULT 'disponible',
  PRIMARY KEY (`id_auto`),
  KEY `id_categoria` (`id_categoria`),
  CONSTRAINT `automoviles_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla autorent_db.automoviles: ~18 rows (aproximadamente)
INSERT INTO `automoviles` (`id_auto`, `id_categoria`, `marca`, `modelo`, `año`, `precio_dia`, `pasajeros`, `transmision`, `descripcion`, `imagen`, `estado`) VALUES
	(43, 1, 'Mazda', 'CX-30', 2023, 950.00, 5, 'automatica', 'SUV subcompacta con diseño premium, ideal para la ciudad y viajes cortos.', 'mazda_cx30.png', 'disponible'),
	(44, 1, 'Nissan', 'Kicks', 2023, 900.00, 5, 'automatica', 'Camioneta ágil y moderna, excelente rendimiento de combustible.', 'nissan_kicks.png', 'rentado'),
	(45, 1, 'Toyota', 'RAV4', 2024, 1200.00, 5, 'automatica', 'La SUV híbrida más confiable del mercado, amplia y segura.', 'toyota_rav4.png', 'disponible'),
	(46, 1, 'Volkswagen', 'Tiguan', 2024, 1350.00, 7, 'automatica', 'SUV familiar con tercera fila de asientos, confort y potencia alemana.', 'vw_tiguan.png', 'disponible'),
	(47, 1, 'Hyundai', 'Creta', 2023, 1000.00, 5, 'automatica', 'Diseño vanguardista, tecnología de punta y gran espacio interior.', 'hyundai_creta.png', 'disponible'),
	(48, 3, 'Nissan', 'Versa', 2024, 650.00, 5, 'automatica', 'El sedán más vendido en México, económico y con gran cajuela.', 'nissan_versa.png', 'disponible'),
	(49, 3, 'Chevrolet', 'Aveo', 2023, 600.00, 5, 'manual', 'Sedán compacto perfecto para el día a día, excelente relación calidad-precio.', 'chevrolet_aveo.png', 'disponible'),
	(50, 3, 'Kia', 'Optima', 2022, 800.00, 5, 'automatica', 'Sedán ejecutivo con diseño deportivo, muy cómodo y equipado.', 'kia_optima.png', 'disponible'),
	(51, 3, 'Honda', 'Civic', 2024, 850.00, 5, 'automatica', 'Ícono de la confiabilidad, manejo suave y excelente tecnología a bordo.', 'honda_civic.png', 'disponible'),
	(52, 2, 'Ford', 'Mustang GT', 2024, 2500.00, 4, 'automatica', 'El muscle car americano por excelencia. Poder, rugido y diseño agresivo.', 'ford_mustang.png', 'disponible'),
	(53, 2, 'Nissan', 'GT-R', 2024, 4500.00, 4, 'automatica', 'Godzilla. Un superdeportivo japonés con tracción integral y potencia brutal.', 'nissan_gtr.png', 'disponible'),
	(54, 2, 'Mazda', 'MX-5', 2023, 1500.00, 2, 'manual', 'El roadster más famoso del mundo. Diversión pura al aire libre.', 'mazda_mx5.png', 'disponible'),
	(55, 2, 'Subaru', 'WRX STI', 2023, 1800.00, 5, 'manual', 'Leyenda del rally. Tracción AWD y un motor bóxer inconfundible.', 'subaru_wrx.png', 'disponible'),
	(56, 4, 'Chevrolet', 'Onix', 2024, 550.00, 5, 'automatica', 'Compacto moderno con pantalla táctil, muy eficiente en ciudad.', 'chevrolet_onix.png', 'disponible'),
	(57, 4, 'Hyundai', 'Grand i10', 2023, 450.00, 5, 'manual', 'Ágil, pequeño y fácil de estacionar. Ideal para trayectos urbanos.', 'hyundai_i10.png', 'disponible'),
	(58, 4, 'Nissan', 'March', 2024, 400.00, 5, 'automatica', 'El rey de la ciudad. Económico, confiable y divertido de manejar.', 'nissan_march.png', 'disponible'),
	(59, 4, 'Renault', 'Kwid', 2023, 380.00, 4, 'manual', 'El auto más accesible, con estilo de SUV y bajo consumo de gasolina.', 'renault_kwid.png', 'disponible'),
	(60, 4, 'Mitsubishi', 'Mirage', 2022, 420.00, 5, 'automatica', 'Compacto con gran rendimiento de combustible y excelente garantía.', 'mitsubishi_mirage.png', 'disponible');

-- Volcando estructura para tabla autorent_db.categorias
CREATE TABLE IF NOT EXISTS `categorias` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text,
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla autorent_db.categorias: ~4 rows (aproximadamente)
INSERT INTO `categorias` (`id_categoria`, `nombre`, `descripcion`) VALUES
	(1, 'SUV', 'Vehículos utilitarios deportivos, amplios y cómodos.'),
	(2, 'Deportivo', 'Automóviles de alto rendimiento y diseño aerodinámico.'),
	(3, 'Sedán', 'Automóviles de cuatro puertas, ideales para la ciudad.'),
	(4, 'Compacto', 'Vehículos pequeños, eficientes y fáciles de estacionar.');

-- Volcando estructura para tabla autorent_db.mensajes
CREATE TABLE IF NOT EXISTS `mensajes` (
  `id_mensaje` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `asunto` varchar(200) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha_envio` datetime DEFAULT CURRENT_TIMESTAMP,
  `leido` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_mensaje`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla autorent_db.mensajes: ~0 rows (aproximadamente)
INSERT INTO `mensajes` (`id_mensaje`, `nombre`, `correo`, `asunto`, `mensaje`, `fecha_envio`, `leido`) VALUES
	(1, 'Roberto Mendez', 'rob@correo', 'Test', 'Hola, soy el primer mensaje :)', '2026-11-16 18:40:02', 0);

-- Volcando estructura para tabla autorent_db.promociones
CREATE TABLE IF NOT EXISTS `promociones` (
  `id_promocion` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `porcentaje_descuento` decimal(5,2) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('activa','inactiva') NOT NULL DEFAULT 'activa',
  PRIMARY KEY (`id_promocion`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla autorent_db.promociones: ~4 rows (aproximadamente)
INSERT INTO `promociones` (`id_promocion`, `nombre`, `descripcion`, `porcentaje_descuento`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES
	(1, 'Descuento de Verano', 'Aprovecha un 15% de descuento en la renta de cualquier vehículo de nuestro catálogo.', 15.00, '2026-01-01', '2026-12-31', 'activa'),
	(2, 'Escapada de Fin de Semana', 'Renta por 2 días o más y obtén un 10% de descuento en tu reservación total.', 10.00, '2026-01-01', '2026-12-31', 'activa'),
	(3, 'Diversión al Máximo', 'Clientes nuevos: obtén un 20% de descuento en tu primera renta.', 20.00, '2026-01-01', '2026-12-31', 'activa'),
	(4, 'Promoción Vencida', 'Esta promoción ya expiró para probar los filtros.', 50.00, '2026-01-01', '2025-12-31', 'inactiva');

-- Volcando estructura para tabla autorent_db.reservaciones
CREATE TABLE IF NOT EXISTS `reservaciones` (
  `id_reservacion` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `id_auto` int NOT NULL,
  `id_promocion` int DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_final` date NOT NULL,
  `numero_dias` int NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL,
  `estado` enum('confirmada','pendiente','cancelada') NOT NULL DEFAULT 'pendiente',
  `fecha_reservacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_reservacion`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_auto` (`id_auto`),
  KEY `id_promocion` (`id_promocion`),
  CONSTRAINT `reservaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `reservaciones_ibfk_2` FOREIGN KEY (`id_auto`) REFERENCES `automoviles` (`id_auto`),
  CONSTRAINT `reservaciones_ibfk_3` FOREIGN KEY (`id_promocion`) REFERENCES `promociones` (`id_promocion`)
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla autorent_db.reservaciones: ~93 rows (aproximadamente)
INSERT INTO `reservaciones` (`id_reservacion`, `id_usuario`, `id_auto`, `id_promocion`, `fecha_inicio`, `fecha_final`, `numero_dias`, `subtotal`, `descuento`, `total`, `estado`, `fecha_reservacion`) VALUES
	(5, 3, 52, NULL, '2026-11-24', '2026-12-01', 8, 20000.00, 0.00, 20000.00, 'cancelada', '2026-11-16 17:54:47'),
	(6, 3, 44, NULL, '2026-11-17', '2026-11-20', 4, 3600.00, 0.00, 3600.00, 'confirmada', '2026-11-16 17:55:28'),
	(7, 3, 49, NULL, '2026-11-17', '2026-11-18', 2, 1200.00, 0.00, 1200.00, 'cancelada', '2026-11-16 17:56:24'),
	(8, 3, 43, NULL, '2026-11-09', '2026-11-14', 5, 4750.00, 0.00, 4750.00, 'confirmada', '2026-11-06 08:20:37'),
	(9, 3, 43, NULL, '2026-10-04', '2026-10-07', 3, 2850.00, 0.00, 2850.00, 'confirmada', '2026-09-23 14:31:43'),
	(10, 3, 43, NULL, '2026-08-29', '2026-09-03', 5, 4750.00, 0.00, 4750.00, 'confirmada', '2026-08-20 10:42:49'),
	(11, 3, 43, NULL, '2026-07-24', '2026-07-27', 3, 2850.00, 0.00, 2850.00, 'cancelada', '2026-07-17 16:53:55'),
	(12, 3, 43, NULL, '2026-06-18', '2026-06-23', 5, 4750.00, 0.00, 4750.00, 'confirmada', '2026-06-13 13:05:01'),
	(13, 3, 44, NULL, '2026-11-07', '2026-11-11', 4, 3600.00, 0.00, 3600.00, 'confirmada', '2026-11-03 08:41:14'),
	(14, 3, 44, 1, '2026-10-02', '2026-10-04', 2, 1800.00, 270.00, 1530.00, 'cancelada', '2026-09-30 14:52:20'),
	(15, 3, 44, NULL, '2026-08-27', '2026-08-31', 4, 3600.00, 0.00, 3600.00, 'confirmada', '2026-08-17 11:03:26'),
	(16, 3, 44, 1, '2026-07-22', '2026-07-24', 2, 1800.00, 270.00, 1530.00, 'confirmada', '2026-07-14 17:14:32'),
	(17, 3, 44, NULL, '2026-06-16', '2026-06-20', 4, 3600.00, 0.00, 3600.00, 'confirmada', '2026-06-10 13:25:38'),
	(18, 3, 45, NULL, '2026-11-05', '2026-11-08', 3, 3600.00, 0.00, 3600.00, 'confirmada', '2026-10-31 09:01:51'),
	(19, 3, 45, NULL, '2026-09-30', '2026-10-05', 5, 6000.00, 0.00, 6000.00, 'confirmada', '2026-09-27 15:12:57'),
	(20, 3, 45, NULL, '2026-08-25', '2026-08-28', 3, 3600.00, 0.00, 3600.00, 'confirmada', '2026-08-14 11:24:03'),
	(21, 3, 45, NULL, '2026-07-20', '2026-07-25', 5, 6000.00, 0.00, 6000.00, 'confirmada', '2026-07-11 17:35:09'),
	(22, 3, 45, NULL, '2026-06-14', '2026-06-17', 3, 3600.00, 0.00, 3600.00, 'cancelada', '2026-06-07 13:46:15'),
	(23, 3, 46, 1, '2026-11-03', '2026-11-05', 2, 2700.00, 405.00, 2295.00, 'confirmada', '2026-10-28 09:22:28'),
	(24, 3, 46, NULL, '2026-09-28', '2026-10-02', 4, 5400.00, 0.00, 5400.00, 'confirmada', '2026-09-24 15:33:34'),
	(25, 3, 46, 1, '2026-08-23', '2026-08-25', 2, 2700.00, 405.00, 2295.00, 'cancelada', '2026-08-21 11:44:40'),
	(26, 3, 46, NULL, '2026-07-18', '2026-07-22', 4, 5400.00, 0.00, 5400.00, 'confirmada', '2026-07-08 17:55:46'),
	(27, 3, 46, 1, '2026-06-12', '2026-06-14', 2, 2700.00, 405.00, 2295.00, 'confirmada', '2026-06-04 14:06:52'),
	(28, 3, 47, NULL, '2026-11-01', '2026-11-06', 5, 5000.00, 0.00, 5000.00, 'cancelada', '2026-10-25 09:43:05'),
	(29, 3, 47, NULL, '2026-09-26', '2026-09-29', 3, 3000.00, 0.00, 3000.00, 'confirmada', '2026-09-21 15:54:11'),
	(30, 3, 47, NULL, '2026-08-21', '2026-08-26', 5, 5000.00, 0.00, 5000.00, 'confirmada', '2026-08-18 12:05:17'),
	(31, 3, 47, NULL, '2026-07-16', '2026-07-19', 3, 3000.00, 0.00, 3000.00, 'confirmada', '2026-07-05 08:16:23'),
	(32, 3, 47, NULL, '2026-06-10', '2026-06-15', 5, 5000.00, 0.00, 5000.00, 'confirmada', '2026-06-01 14:27:29'),
	(33, 3, 48, NULL, '2026-10-30', '2026-11-03', 4, 2600.00, 0.00, 2600.00, 'confirmada', '2026-10-22 10:03:42'),
	(34, 3, 48, 1, '2026-09-24', '2026-09-26', 2, 1300.00, 195.00, 1105.00, 'confirmada', '2026-09-18 16:14:48'),
	(35, 3, 48, NULL, '2026-08-19', '2026-08-23', 4, 2600.00, 0.00, 2600.00, 'confirmada', '2026-08-15 12:25:54'),
	(36, 3, 48, 1, '2026-07-14', '2026-07-16', 2, 1300.00, 195.00, 1105.00, 'cancelada', '2026-07-12 08:37:00'),
	(37, 3, 48, NULL, '2026-06-08', '2026-06-12', 4, 2600.00, 0.00, 2600.00, 'confirmada', '2026-05-29 14:48:06'),
	(38, 3, 49, NULL, '2026-10-28', '2026-10-31', 3, 1800.00, 0.00, 1800.00, 'confirmada', '2026-10-19 10:24:19'),
	(39, 3, 49, NULL, '2026-09-22', '2026-09-27', 5, 3000.00, 0.00, 3000.00, 'cancelada', '2026-09-15 16:35:25'),
	(40, 3, 49, NULL, '2026-08-17', '2026-08-20', 3, 1800.00, 0.00, 1800.00, 'confirmada', '2026-08-12 12:46:31'),
	(41, 3, 49, NULL, '2026-07-12', '2026-07-17', 5, 3000.00, 0.00, 3000.00, 'confirmada', '2026-07-09 08:57:37'),
	(42, 3, 49, NULL, '2026-06-06', '2026-06-09', 3, 1800.00, 0.00, 1800.00, 'confirmada', '2026-05-26 15:08:43'),
	(43, 3, 50, 1, '2026-10-26', '2026-10-28', 2, 1600.00, 240.00, 1360.00, 'confirmada', '2026-10-16 10:44:56'),
	(44, 3, 50, NULL, '2026-09-20', '2026-09-24', 4, 3200.00, 0.00, 3200.00, 'confirmada', '2026-09-12 16:56:02'),
	(45, 3, 50, 1, '2026-08-15', '2026-08-17', 2, 1600.00, 240.00, 1360.00, 'confirmada', '2026-08-09 13:07:08'),
	(46, 3, 50, NULL, '2026-07-10', '2026-07-14', 4, 3200.00, 0.00, 3200.00, 'confirmada', '2026-07-06 09:18:14'),
	(47, 3, 50, 1, '2026-06-04', '2026-06-06', 2, 1600.00, 240.00, 1360.00, 'cancelada', '2026-06-02 15:29:20'),
	(48, 3, 51, NULL, '2026-10-24', '2026-10-29', 5, 4250.00, 0.00, 4250.00, 'confirmada', '2026-10-13 11:05:33'),
	(49, 3, 51, NULL, '2026-09-18', '2026-09-21', 3, 2550.00, 0.00, 2550.00, 'confirmada', '2026-09-09 17:16:39'),
	(50, 3, 51, NULL, '2026-08-13', '2026-08-18', 5, 4250.00, 0.00, 4250.00, 'cancelada', '2026-08-06 13:27:45'),
	(51, 3, 51, NULL, '2026-07-08', '2026-07-11', 3, 2550.00, 0.00, 2550.00, 'confirmada', '2026-07-03 09:38:51'),
	(52, 3, 51, NULL, '2026-06-02', '2026-06-07', 5, 4250.00, 0.00, 4250.00, 'confirmada', '2026-05-30 15:49:57'),
	(53, 3, 52, NULL, '2026-10-22', '2026-10-26', 4, 10000.00, 0.00, 10000.00, 'cancelada', '2026-10-20 11:26:10'),
	(54, 3, 52, 1, '2026-09-16', '2026-09-18', 2, 5000.00, 750.00, 4250.00, 'confirmada', '2026-09-06 17:37:16'),
	(55, 3, 52, NULL, '2026-08-11', '2026-08-15', 4, 10000.00, 0.00, 10000.00, 'confirmada', '2026-08-03 13:48:22'),
	(56, 3, 52, 1, '2026-07-06', '2026-07-08', 2, 5000.00, 750.00, 4250.00, 'confirmada', '2026-06-30 09:59:28'),
	(57, 3, 52, NULL, '2026-05-31', '2026-06-04', 4, 10000.00, 0.00, 10000.00, 'confirmada', '2026-05-27 16:10:34'),
	(58, 3, 53, NULL, '2026-10-20', '2026-10-23', 3, 13500.00, 0.00, 13500.00, 'confirmada', '2026-10-17 11:46:47'),
	(59, 3, 53, NULL, '2026-09-14', '2026-09-19', 5, 22500.00, 0.00, 22500.00, 'confirmada', '2026-09-03 17:57:53'),
	(60, 3, 53, NULL, '2026-08-09', '2026-08-12', 3, 13500.00, 0.00, 13500.00, 'confirmada', '2026-07-31 14:08:59'),
	(61, 3, 53, NULL, '2026-07-04', '2026-07-09', 5, 22500.00, 0.00, 22500.00, 'cancelada', '2026-06-27 10:20:05'),
	(62, 3, 53, NULL, '2026-05-29', '2026-06-01', 3, 13500.00, 0.00, 13500.00, 'confirmada', '2026-05-24 16:31:11'),
	(63, 3, 54, 1, '2026-10-18', '2026-10-20', 2, 3000.00, 450.00, 2550.00, 'confirmada', '2026-10-14 12:07:24'),
	(64, 3, 54, NULL, '2026-09-12', '2026-09-16', 4, 6000.00, 0.00, 6000.00, 'cancelada', '2026-09-10 08:18:30'),
	(65, 3, 54, 1, '2026-08-07', '2026-08-09', 2, 3000.00, 450.00, 2550.00, 'confirmada', '2026-07-28 14:29:36'),
	(66, 3, 54, NULL, '2026-07-02', '2026-07-06', 4, 6000.00, 0.00, 6000.00, 'confirmada', '2026-06-24 10:40:42'),
	(67, 3, 54, 1, '2026-05-27', '2026-05-29', 2, 3000.00, 450.00, 2550.00, 'confirmada', '2026-05-21 16:51:48'),
	(68, 3, 55, NULL, '2026-10-16', '2026-10-21', 5, 9000.00, 0.00, 9000.00, 'confirmada', '2026-10-11 12:28:01'),
	(69, 3, 55, NULL, '2026-09-10', '2026-09-13', 3, 5400.00, 0.00, 5400.00, 'confirmada', '2026-09-07 08:39:07'),
	(70, 3, 55, NULL, '2026-08-05', '2026-08-10', 5, 9000.00, 0.00, 9000.00, 'confirmada', '2026-07-25 14:50:13'),
	(71, 3, 55, NULL, '2026-06-30', '2026-07-03', 3, 5400.00, 0.00, 5400.00, 'confirmada', '2026-06-21 11:01:19'),
	(72, 3, 55, NULL, '2026-05-25', '2026-05-30', 5, 9000.00, 0.00, 9000.00, 'cancelada', '2026-05-18 17:12:25'),
	(73, 3, 56, NULL, '2026-10-14', '2026-10-18', 4, 2200.00, 0.00, 2200.00, 'confirmada', '2026-10-08 12:48:38'),
	(74, 3, 56, 1, '2026-09-08', '2026-09-10', 2, 1100.00, 165.00, 935.00, 'confirmada', '2026-09-04 08:59:44'),
	(75, 3, 56, NULL, '2026-08-03', '2026-08-07', 4, 2200.00, 0.00, 2200.00, 'cancelada', '2026-08-01 15:10:50'),
	(76, 3, 56, 1, '2026-06-28', '2026-06-30', 2, 1100.00, 165.00, 935.00, 'confirmada', '2026-06-18 11:21:56'),
	(77, 3, 56, NULL, '2026-05-23', '2026-05-27', 4, 2200.00, 0.00, 2200.00, 'confirmada', '2026-05-15 17:33:02'),
	(78, 3, 57, NULL, '2026-10-12', '2026-10-15', 3, 1350.00, 0.00, 1350.00, 'cancelada', '2026-10-05 13:09:15'),
	(79, 3, 57, NULL, '2026-09-06', '2026-09-11', 5, 2250.00, 0.00, 2250.00, 'confirmada', '2026-09-01 09:20:21'),
	(80, 3, 57, NULL, '2026-08-01', '2026-08-04', 3, 1350.00, 0.00, 1350.00, 'confirmada', '2026-07-29 15:31:27'),
	(81, 3, 57, NULL, '2026-06-26', '2026-07-01', 5, 2250.00, 0.00, 2250.00, 'confirmada', '2026-06-15 11:42:33'),
	(82, 3, 57, NULL, '2026-05-21', '2026-05-24', 3, 1350.00, 0.00, 1350.00, 'confirmada', '2026-05-12 17:53:39'),
	(83, 3, 58, 1, '2026-10-10', '2026-10-12', 2, 800.00, 120.00, 680.00, 'confirmada', '2026-10-02 13:29:52'),
	(84, 3, 58, NULL, '2026-09-04', '2026-09-08', 4, 1600.00, 0.00, 1600.00, 'confirmada', '2026-08-29 09:40:58'),
	(85, 3, 58, 1, '2026-07-30', '2026-08-01', 2, 800.00, 120.00, 680.00, 'confirmada', '2026-07-26 15:52:04'),
	(86, 3, 58, NULL, '2026-06-24', '2026-06-28', 4, 1600.00, 0.00, 1600.00, 'cancelada', '2026-06-22 12:03:10'),
	(87, 3, 58, 1, '2026-05-19', '2026-05-21', 2, 800.00, 120.00, 680.00, 'confirmada', '2026-05-09 08:14:16'),
	(88, 3, 59, NULL, '2026-10-08', '2026-10-13', 5, 1900.00, 0.00, 1900.00, 'confirmada', '2026-09-29 13:50:29'),
	(89, 3, 59, NULL, '2026-09-02', '2026-09-05', 3, 1140.00, 0.00, 1140.00, 'cancelada', '2026-08-26 10:01:35'),
	(90, 3, 59, NULL, '2026-07-28', '2026-08-02', 5, 1900.00, 0.00, 1900.00, 'confirmada', '2026-07-23 16:12:41'),
	(91, 3, 59, NULL, '2026-06-22', '2026-06-25', 3, 1140.00, 0.00, 1140.00, 'confirmada', '2026-06-19 12:23:47'),
	(92, 3, 59, NULL, '2026-05-17', '2026-05-22', 5, 1900.00, 0.00, 1900.00, 'confirmada', '2026-05-06 08:34:53'),
	(93, 3, 60, NULL, '2026-10-06', '2026-10-10', 4, 1680.00, 0.00, 1680.00, 'confirmada', '2026-09-26 14:11:06'),
	(94, 3, 60, 1, '2026-08-31', '2026-09-02', 2, 840.00, 126.00, 714.00, 'confirmada', '2026-08-23 10:22:12'),
	(95, 3, 60, NULL, '2026-07-26', '2026-07-30', 4, 1680.00, 0.00, 1680.00, 'confirmada', '2026-07-20 16:33:18'),
	(96, 3, 60, 1, '2026-06-20', '2026-06-22', 2, 840.00, 126.00, 714.00, 'confirmada', '2026-06-16 12:44:24'),
	(97, 3, 60, NULL, '2026-05-15', '2026-05-19', 4, 1680.00, 0.00, 1680.00, 'cancelada', '2026-05-13 08:55:30'),
	(135, 3, 52, 3, '2026-11-24', '2026-12-01', 8, 20000.00, 4000.00, 16000.00, 'confirmada', '2026-11-16 22:37:08'),
	(136, 3, 52, 3, '2026-11-17', '2026-11-19', 3, 7500.00, 1500.00, 6000.00, 'confirmada', '2026-11-16 23:34:07');

-- Volcando estructura para tabla autorent_db.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `rol` enum('cliente','administrador') NOT NULL DEFAULT 'cliente',
  `estado` enum('activo','suspendido') NOT NULL DEFAULT 'activo',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla autorent_db.usuarios: ~3 rows (aproximadamente)
INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellidos`, `correo`, `contrasena`, `telefono`, `rol`, `estado`, `fecha_registro`) VALUES
	(1, 'Javier', 'Rangel', 'correo@test.com', '$2y$10$H0CXQP3lAqZi4MmKHL8IeeJgpftL3X87MqZTtHqKVL4zLtV4AhQbm', '9999999999', 'cliente', 'suspendido', '2026-11-12 17:22:37'),
	(2, 'Admin', 'AutoRent', 'admin@autorent.com', '$2y$10$GEcc4MecR/J8xPVBKXiy6u8IDub9KkgClyiyOvtyrF4X2yemOmtuS', '1234567890', 'administrador', 'activo', '2026-11-12 17:37:45'),
	(3, 'Roberto', 'Méndez', 'prueba@test.com', '$2y$10$zmmzri9t0z6tCKRzFSuBV.hT2fEoXbJggFemDHReZCBFDxhdP7VEG', '1111111111', 'cliente', 'activo', '2026-11-14 20:33:58');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
