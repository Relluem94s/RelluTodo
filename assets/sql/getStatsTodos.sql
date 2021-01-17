/**
 * Author:  rellu
 * Created: 19.12.2020
 */

SELECT sum(CASE
               WHEN t.deletedby IS NULL THEN 1
           END) AS "open",
       sum(CASE
            WHEN Day(t.created) = Day(Now())
            AND YEARWEEK(t.created, 1) = YEARWEEK(Now(), 1)
            AND Month(t.created) = Month(Now())
            AND Year(t.created) = Year(Now()) THEN 1
           END) AS "createdDay",
       sum(CASE
               WHEN t.deletedby IS NOT NULL
                AND Day(t.deleted) = Day(Now())
                AND YEARWEEK(t.deleted, 1) = YEARWEEK(Now(), 1)
                AND Month(t.deleted) = Month(Now())
                AND Year(t.deleted) = Year(Now()) THEN 1
           END) AS "deletedDay",
       sum(CASE
               WHEN YEARWEEK(t.created, 1) = YEARWEEK(Now(), 1)
                AND Month(t.created) = Month(Now())
                AND Year(t.created) = Year(Now()) THEN 1
           END) AS "createdWeek",
       sum(CASE
               WHEN t.deletedby IS NOT NULL
                AND YEARWEEK(t.deleted, 1) = YEARWEEK(Now(), 1)
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