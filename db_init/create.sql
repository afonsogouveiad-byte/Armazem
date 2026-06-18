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


CREATE DATABASE IF NOT EXISTS armazem
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- Donem permisos a l'usuari 'usuari' per accedir a la base de dades 'armazem'
-- sinó, aquest usuari no podrà veure la base de dades i no podrà accedir a les taules
GRANT ALL PRIVILEGES ON armazem.* TO 'usuari'@'%';
FLUSH PRIVILEGES;


-- Després de crear la base de dades, cal seleccionar-la per treballar-hi
USE armazem;

DROP TABLE IF EXISTS `items`;
CREATE TABLE `items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stock` int NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `items` (`id`, `name`, `stock`, `image`, `description`, `image2`) VALUES
(17,	'Lego',	3,	'uploads/img_6a33aa083f0c11.34222678.jpeg',	NULL,	NULL),
(18,	'Caixa',	3,	'uploads/img_6a33ca9938b7b5.72118385.jpeg',	NULL,	NULL),
(19,	'rwger',	2,	'uploads/img_6a33aa2d316bc3.23883683.jpeg',	NULL,	NULL),
(20,	'dfv',	3,	'uploads/img_6a33aa3672d3a7.14539553.jpeg',	NULL,	NULL),
(21,	'hfh',	4,	'uploads/img_6a33aa426aa757.46262288.jpeg',	NULL,	NULL),
(22,	'gnhj',	0,	'uploads/img_6a33aa4eb1f0d9.62207309.jpeg',	NULL,	NULL),
(23,	'yrtytr',	0,	'uploads/img_6a33aa6cc22c23.75245155.jpeg',	NULL,	NULL),
(24,	'tyjkuy',	0,	'uploads/img_6a33aa7bbda3f3.03525493.jpeg',	NULL,	NULL),
(25,	'etuo',	0,	'uploads/img_6a33aa8614a608.78139316.jpeg',	NULL,	NULL),
(26,	'sedf',	0,	'uploads/img_6a33aa8fb83453.90982729.jpeg',	NULL,	NULL),
(27,	'oiuytr',	0,	'uploads/img_6a33aa9a33b7f3.67310526.jpeg',	NULL,	NULL),
(28,	'iuoyl',	0,	'uploads/img_6a33aaa265a238.19656883.jpeg',	NULL,	NULL),
(29,	't5re',	0,	'uploads/img_6a33aaab9f2e38.30137703.jpeg',	NULL,	NULL),
(30,	'iuty,kom',	0,	'uploads/img_6a33aab32c6b24.85155280.jpeg',	NULL,	NULL),
(31,	'iure',	0,	'uploads/img_6a33aab9c73762.90443868.jpeg',	NULL,	NULL),
(32,	'kuit68okl',	0,	'uploads/img_6a33aac2ef2332.47510063.jpeg',	NULL,	NULL),
(33,	'rwe',	0,	'uploads/img_6a33aacc506fa2.90778098.jpeg',	NULL,	NULL),
(34,	'sedf',	0,	'uploads/img_6a33aad482aa37.51549055.jpeg',	NULL,	NULL),
(35,	'yiulo',	0,	'uploads/img_6a33aaeb89da74.61886368.jpeg',	NULL,	NULL),
(36,	'jtyj',	0,	'uploads/img_6a33aaf2c90699.32623044.jpeg',	NULL,	NULL),
(38,	'098op',	0,	'uploads/img_6a33ab291434f1.53390255.jpeg',	NULL,	NULL),
(39,	'klñ',	0,	'uploads/img_6a33ab31926401.98451953.jpeg',	NULL,	NULL),
(40,	'j7uyio',	0,	'uploads/img_6a33ab39e4d270.66802889.jpeg',	NULL,	NULL),
(41,	'wrtgy',	0,	'uploads/img_6a33ab43ec4d56.51873698.jpeg',	NULL,	NULL),
(43,	'lkuyijl',	0,	'uploads/img_6a33ab65d66148.36843251.jpeg',	NULL,	NULL),
(44,	'wth5y',	0,	'uploads/img_6a33ab6f3665f9.99627235.jpeg',	NULL,	NULL),
(45,	'tuyikuy',	0,	'uploads/img_6a33ab79aa8cb1.86996891.jpeg',	NULL,	NULL),
(46,	'iul',	0,	'uploads/img_6a33ab88cf8c93.67746750.jpeg',	NULL,	NULL);

-- 2026-06-18 10:44:22 UTC