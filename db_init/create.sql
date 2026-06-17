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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
