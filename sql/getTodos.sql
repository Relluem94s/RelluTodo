/**
 * Author:  rellu
 * Created: 19.12.2020
 */

select t.id, t.`text`, date_format(t.created, "%d.%m.%Y %H:%i:%s") as created, date_format(t.updated, "%d.%m.%Y %H:%i:%s") as updated , date_format(t.deleted, "%d.%m.%Y %H:%i:%s") as deleted, t.createdby, t.updatedby, t.deletedby from todo t where t.deleted is null or ( MONTH(t.deleted) = MONTH(now()) and YEAR(t.deleted) = YEAR(now())) order by t.id desc