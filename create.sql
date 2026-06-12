-- Aquest script NOMÉS s'executa la primera vegada que es crea el contenidor.
-- Si es vol recrear les taules de nou cal esborrar el contenidor, o bé les dades del contenidor
-- és a dir, 
-- esborrar el contingut de la carpeta db_data 
-- o canviant el nom de la carpeta, però atenció a no pujar-la a git


-- És un exemple d'script per crear una base de dades i una taula
-- i afegir-hi dades inicials

-- Si creem la BBDD aquí podem control·lar la codificació i el collation
-- en canvi en el docker-compose no podem especificar el collation ni la codificació

-- Per assegurar-nes de que la codificació dels caràcters d'aquest script és la correcta
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `menus`;
CREATE TABLE `menus` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` float DEFAULT NULL,
  `day` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `week` int DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `menus` (`id`, `name`, `price`, `day`, `week`, `image`) VALUES
(6,	'',	0,	'',	0,	''),
(7,	'Feijoada',	10,	'dimarts',	1,	NULL),
(8,	'Kebab',	10,	'dimecres',	1,	NULL),
(9,	'Batatas com Bacalhao',	10,	'dijous',	1,	NULL),
(10,	'polvo a lagareiro',	10,	'divendres',	1,	NULL),
(11,	'Cosido a portuguesa',	10,	'dilluns',	2,	NULL),
(12,	'Bifana',	10,	'dimarts',	2,	NULL),
(13,	'Fransesinha',	10,	'dimecres',	2,	NULL),
(14,	'Prego',	10,	'dijous',	2,	NULL),
(15,	'Dourada',	10,	'divendres',	2,	NULL),
(16,	'Robalo',	10,	'dilluns',	3,	NULL),
(17,	'Douradinhos',	10,	'dimarts',	3,	NULL),
(18,	'Posta de vaca',	10,	'dimecres',	3,	NULL),
(19,	'Lasanha',	10,	'dijous',	3,	NULL),
(20,	'Massa a bolonhesa',	10,	'divendres',	3,	NULL),
(21,	'Frango ',	10,	'dilluns',	4,	NULL),
(22,	'Filetes de Pescada',	10,	'dimarts',	4,	NULL),
(23,	'Salada Russa',	10,	'dimecres',	4,	NULL),
(24,	'Secretos de Porco',	10,	'dijous',	4,	NULL),
(25,	'Pizza',	10,	'divendres',	4,	NULL),
(26,	'fes',	11,	'dilluns',	1,	NULL);

DROP TABLE IF EXISTS `menus2`;
CREATE TABLE `menus2` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` float DEFAULT NULL,
  `day` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `week` int DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `menus2` (`id`, `name`, `price`, `day`, `week`, `image`) VALUES
(4,	'Hamburger',	11,	'dilluns',	1,	'inspedr.jpg'),
(5,	'',	0,	'',	0,	''),
(6,	'',	0,	'',	0,	''),
(7,	'',	0,	'',	0,	''),
(8,	'',	0,	'',	0,	''),
(9,	'Massa de Bacalhao',	11,	'dilluns',	2,	''),
(10,	'Canelones',	11,	'dimarts',	2,	''),
(11,	'Tacos',	11,	'dimecres',	2,	NULL),
(12,	'Douradinhos',	11,	'dijous',	2,	NULL),
(13,	'Feijoada',	11,	'divendres',	2,	NULL),
(14,	'Caracois',	11,	'dilluns',	3,	NULL),
(15,	'Lulas',	11,	'dimarts',	3,	NULL),
(16,	'Calzones',	11,	'dimecres',	3,	NULL),
(17,	'Mariscada',	11,	'dijous',	3,	NULL),
(18,	'Bifana',	11,	'divendres',	3,	NULL),
(19,	'Massa com Atum',	11,	'dilluns',	4,	NULL),
(20,	'Massa com Salsicha',	11,	'dimarts',	4,	NULL),
(21,	'Lagosta',	11,	'dimecres',	4,	NULL),
(22,	'Risoto',	11,	'dijous',	4,	NULL),
(23,	'Pizza',	11,	'divendres',	4,	NULL),
(25,	'',	0,	'',	0,	''),
(26,	'',	0,	'',	0,	''),
(28,	'',	0,	'',	0,	''),
(29,	'',	0,	'',	0,	''),
(30,	'',	0,	'',	0,	''),
(31,	'Bacalhao',	11,	'dimarts',	1,	''),
(32,	'fsfs',	11,	'dimecres',	1,	'inspedr.jpg'),
(33,	'dasd',	11,	'dijous',	1,	''),
(34,	'dsad',	11,	'divendres',	1,	'inspedr.jpg');

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` float NOT NULL,
  `category` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`id`, `name`, `price`, `category`, `image`) VALUES
(4,	'cafe',	12,	'Begudes',	''),
(5,	'Tallat',	1,	'Begudes',	''),
(6,	'Cafe amb Liet',	1,	'Begudes',	''),
(7,	'Infusions',	1,	'Begudes',	''),
(8,	'Aigua 500ml',	0.7,	'Begudes',	''),
(9,	'Aigua 1500ml',	1,	'Begudes',	''),
(10,	'Liaunes',	1,	'Begudes',	''),
(11,	'Cacaulat',	1.7,	'Begudes',	''),
(12,	'Liet soja i sense lactosa',	1,	'Begudes',	''),
(13,	'Gel',	1,	'Begudes',	''),
(14,	'Croissant',	1,	'Pastes',	''),
(15,	'Donut Sucre',	1,	'Pastes',	''),
(16,	'Donut xoco',	1,	'Pastes',	''),
(17,	'Canya',	1,	'Pastes',	''),
(18,	'Tonyina',	2,	'Entrepans Freds',	''),
(19,	'Pernil savat',	2,	'Entrepans Freds',	''),
(20,	'Fuet ',	2,	'Entrepans Freds',	''),
(21,	'Formatge',	2,	'Entrepans Freds',	''),
(22,	'Xoriço',	2,	'Entrepans Freds',	''),
(23,	'Pernil Dolç',	2,	'Entrepans Freds',	''),
(24,	'Frankfurt',	2,	'Entrepans Calents',	''),
(25,	'Bacon',	2,	'Entrepans Calents',	''),
(26,	'Llom',	3,	'Entrepans Calents',	''),
(27,	'Hamburguesa',	3,	'Entrepans Calents',	''),
(28,	'Truita',	3,	'Entrepans Calents',	''),
(29,	'Mallorqui',	3,	'Entrepans Calents',	''),
(30,	'Bikini',	3,	'Entrepans Calents',	''),
(31,	'Xistorra',	3,	'Entrepans Calents',	''),
(32,	'Serranito',	2,	'Entrepans Calents',	'');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1,	'admin',	'1234');

-- 2026-05-11 09:22:55 UTC



