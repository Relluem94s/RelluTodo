/**
 * Author:  rellu
 * Created: 19.12.2020
 */

SELECT t.id, 
       t.deleted, 
       t.created, 
       t.updated, 
       t.createdby, 
       t.updatedby, 
       t.deletedby 
FROM   todo t 
WHERE  t.deleted IS NULL 
        OR ( Month(t.deleted) = Month(Now()) 
             AND Year(t.deleted) = Year(Now()) ) 