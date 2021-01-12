/**
 * Author:  rellu
 * Created: 19.12.2020
 */

CREATE TABLE `todo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime(6) NOT NULL,
  `createdby` varchar(45) NOT NULL,
  `updated` datetime(6) DEFAULT NULL,
  `updatedby` varchar(45) DEFAULT NULL,
  `deleted` datetime(6) DEFAULT NULL,
  `deletedby` varchar(45) DEFAULT NULL,
  `text` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
