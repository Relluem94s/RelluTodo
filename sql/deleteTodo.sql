/**
 * Author:  rellu
 * Created: 19.12.2020
 */

UPDATE `todo` 
SET    `deleted` = now(), 
       deletedby = ?
WHERE  `todo`.`id` = ?; 