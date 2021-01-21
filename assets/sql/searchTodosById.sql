/**
 * Author:  rellu
 * Created: 19.12.2020
 */

SELECT t.id, 
       t.`text`, 
       Date_format(t.created, "%d.%m.%y %h:%i:%s") AS created, 
       Date_format(t.updated, "%d.%m.%y %h:%i:%s") AS updated, 
       Date_format(t.deleted, "%d.%m.%y %h:%i:%s") AS deleted, 
       t.createdby, 
       t.updatedby, 
       t.deletedby 
FROM   todo t

WHERE t.id = ?