/**
 * Author:  rellu
 * Created: 19.12.2020
 */

UPDATE `todo` 
SET    `deleted` = NULL, 
       `updated` = now(), 
       deletedby = NULL, 
       updatedby = ? 
WHERE  `todo`.`id` = ?; 