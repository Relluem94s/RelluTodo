/**
 * Author:  rellu
 * Created: 19.12.2020
 */

UPDATE `todo` SET `text` = ?, updated = now(), updatedby = ? WHERE `todo`.`id` = ?;