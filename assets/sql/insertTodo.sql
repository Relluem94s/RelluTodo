/**
 * Author:  rellu
 * Created: 19.12.2020
 */

INSERT INTO `todo` (`text`, `deleted`, `created`, `createdby`) 
VALUES(?, null, now(), ?);