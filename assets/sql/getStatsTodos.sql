/**
 * Author:  rellu
 * Created: 19.12.2020
 */

SELECT 
    coalesce(
        sum(
            CASE 
                WHEN t.deletedby IS NULL 
                THEN 1
            END
        ),
        0
    ) AS "open",
    coalesce(
        sum(
            CASE
                WHEN Day(t.created) = Day(Now())
                AND YEARWEEK(t.created, 1) = YEARWEEK(Now(), 1)
                AND Month(t.created) = Month(Now())
                AND Year(t.created) = Year(Now()) THEN 1
            END
        ),
        0
    ) AS "createdDay",
    coalesce(
        sum(
            CASE
                WHEN t.deletedby IS NOT NULL
                AND Day(t.deleted) = Day(Now())
                AND YEARWEEK(t.deleted, 1) = YEARWEEK(Now(), 1)
                AND Month(t.deleted) = Month(Now())
                AND Year(t.deleted) = Year(Now()) THEN 1
            END
        ),
        0
    ) AS "deletedDay",
    coalesce(
        sum(
            CASE
                WHEN YEARWEEK(t.created, 1) = YEARWEEK(Now(), 1)
                AND Month(t.created) = Month(Now())
                AND Year(t.created) = Year(Now()) THEN 1
            END
        ),
        0
    ) AS "createdWeek",
    coalesce(
        sum(
            CASE
                WHEN t.deletedby IS NOT NULL
                AND YEARWEEK(t.deleted, 1) = YEARWEEK(Now(), 1)
                AND Month(t.deleted) = Month(Now())
                AND Year(t.deleted) = Year(Now()) THEN 1
            END
        ),
        0
    ) AS "deletedWeek",
    coalesce(
        sum(
            CASE
                WHEN Month(t.created) = Month(Now())
                AND Year(t.created) = Year(Now()) THEN 1
            END
        ),
        0
    ) AS "createdMonth",
    coalesce(
        sum(
            CASE
                WHEN t.deletedby IS NOT NULL
                AND Month(t.deleted) = Month(Now())
                AND Year(t.deleted) = Year(Now()) THEN 1
            END
        ),
        0
    ) AS "deletedMonth",
    coalesce(
        count(*),
        0
    ) AS "total"
FROM todo t