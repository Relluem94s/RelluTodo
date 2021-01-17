/**
 * Author:  rellu
 * Created: 19.12.2020
 */

SELECT sum(CASE
               WHEN t.deletedby IS NULL THEN 1
           END) AS "open",
       sum(CASE
            WHEN Day(t.created) = Day(Now())
            AND Week(t.created) = Week(Now())
            AND Month(t.created) = Month(Now())
            AND Year(t.created) = Year(Now()) THEN 1
           END) AS "createdDay",
       sum(CASE
               WHEN t.deletedby IS NOT NULL
                AND Day(t.deleted) = Day(Now())
                AND Week(t.deleted) = Week(Now())
                AND Month(t.deleted) = Month(Now())
                AND Year(t.deleted) = Year(Now()) THEN 1
           END) AS "deletedDay",
       sum(CASE
               WHEN Week(t.created) = Week(Now())
                AND Month(t.created) = Month(Now())
                AND Year(t.created) = Year(Now()) THEN 1
           END) AS "createdWeek",
       sum(CASE
               WHEN t.deletedby IS NOT NULL
                AND Week(t.deleted) = Week(Now())
                AND Month(t.deleted) = Month(Now())
                AND Year(t.deleted) = Year(Now()) THEN 1
           END) AS "deletedWeek",
       sum(CASE
               WHEN Month(t.created) = Month(Now())
                AND Year(t.created) = Year(Now()) THEN 1
           END) AS "createdMonth",
       sum(CASE
               WHEN t.deletedby IS NOT NULL
                AND Month(t.deleted) = Month(Now())
                AND Year(t.deleted) = Year(Now()) THEN 1
           END) AS "deletedMonth",
       count(*) AS "total"
FROM todo t