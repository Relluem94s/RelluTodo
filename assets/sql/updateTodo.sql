/**
 * Author:  rellu
 * Created: 19.12.2020
 */

UPDATE `todo` 
SET    `text` = ?, 
       updated = Now(), 
       updatedby = ? 
WHERE  `todo`.`id` = ?; 