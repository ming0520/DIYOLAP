SELECT DISTINCT SUM(fee) fee,
        dates.quater,
        dates.year
    FROM
        attendences
    RIGHT JOIN dates ON attendences.did = dates.dateid
    GROUP BY
        dates.quater,
        dates.year








SELECT tab2.quater, tab2.year,
CASE WHEN tab2.quater='1' THEN tab2.fee END AS 'q1',
CASE WHEN tab2.quater='2' THEN tab2.fee END AS 'q2',
CASE WHEN tab2.quater='3' THEN tab2.fee END AS 'q3',
CASE WHEN tab2.quater='4' THEN tab2.fee END AS 'q4'

FROM(
SELECT DISTINCT SUM(fee) fee,
        dates.quater,
        dates.year
    FROM
        attendences
    RIGHT JOIN dates ON attendences.did = dates.dateid
    GROUP BY
        dates.quater,
        dates.year
)tab2














SELECT 
    tab3.year,
    COALESCE (SUM(q1),0) AS '1',
    COALESCE (SUM(q2),0) AS '2',
    COALESCE (SUM(q3),0) AS '3',
    COALESCE (SUM(q4),0) AS '4'
FROM(
    SELECT tab2.quater, tab2.year,
    CASE WHEN tab2.quater='1' THEN tab2.fee END AS 'q1',
    CASE WHEN tab2.quater='2' THEN tab2.fee END AS 'q2',
    CASE WHEN tab2.quater='3' THEN tab2.fee END AS 'q3',
    CASE WHEN tab2.quater='4' THEN tab2.fee END AS 'q4'

    FROM(
        SELECT DISTINCT SUM(fee) fee,
                dates.quater,
                dates.year
            FROM
                attendences
            RIGHT JOIN dates ON attendences.did = dates.dateid
            GROUP BY
                dates.quater,
                dates.year
    )tab2
)tab3
GROUP BY
tab3.year







SELECT 
    tab3.year,
    COALESCE (SUM(tab3.1),0) AS '1',
    COALESCE (SUM(tab3.2),0) AS '2',
    COALESCE (SUM(tab3.3),0) AS '3',
    COALESCE (SUM(tab3.4),0) AS '4'
FROM(
    SELECT tab2.quater, tab2.year,
    CASE WHEN tab2.quater='1' THEN tab2.fee END AS '1',
    CASE WHEN tab2.quater='2' THEN tab2.fee END AS '2',
    CASE WHEN tab2.quater='3' THEN tab2.fee END AS '3',
    CASE WHEN tab2.quater='4' THEN tab2.fee END AS '4'

    FROM(
        SELECT DISTINCT SUM(fee) fee,
                dates.quater,
                dates.year
            FROM
                attendences
            RIGHT JOIN dates ON attendences.did = dates.dateid
            GROUP BY
                dates.quater,
                dates.year
    )tab2
)tab3
GROUP BY
tab3.year






SELECT DISTINCT  
        dates.year,
        SUM(IF(quater = '1', fee, 0)) AS q1,
        SUM(IF(quater = '2', fee, 0)) AS q2,
        SUM(IF(quater = '3', fee, 0)) AS q3,
        SUM(IF(quater = '4', fee, 0)) AS q4
    FROM
        attendences
    RIGHT JOIN dates ON attendences.did = dates.dateid
    GROUP BY
        dates.year





SELECT DISTINCT  
        dates.year,
        SUM(CASE WHEN quater = '1' THEN fee ELSE 0 END) AS q1,
        SUM(CASE WHEN quater = '2' THEN fee ELSE 0 END) AS q2,
        SUM(CASE WHEN quater = '3' THEN fee ELSE 0 END) AS q3,
        SUM(CASE WHEN quater = '4' THEN fee ELSE 0 END) AS q4
    FROM
        attendences
    RIGHT JOIN dates ON attendences.did = dates.dateid
    GROUP BY
        dates.year



SELECT DISTINCT locations.country, 
    SUM(CASE WHEN quater='1' THEN fee ELSE 0 END) AS q1,
    SUM(CASE WHEN quater='2' THEN fee ELSE 0 END) AS q2, 
    SUM(CASE WHEN quater='3' THEN fee ELSE 0 END) AS q3, 
    SUM(CASE WHEN quater='4' THEN fee ELSE 0 END) AS q4


















SELECT DISTINCT  
        locations.country,
        SUM(CASE WHEN quater = '1' THEN fee ELSE 0 END) AS q1,
        SUM(CASE WHEN quater = '2' THEN fee ELSE 0 END) AS q2,
        SUM(CASE WHEN quater = '3' THEN fee ELSE 0 END) AS q3,
        SUM(CASE WHEN quater = '4' THEN fee ELSE 0 END) AS q4
    FROM
        attendences
    RIGHT JOIN dates ON attendences.did = dates.dateid
    RIGHT JOIN locations ON attendences.lid = locations.locationid
    GROUP BY
        locations.country

SELECT DISTINCT  
        locations.country,
        COUNT(CASE WHEN ecategory = 'Private' THEN fee ELSE 0 END) AS Private,
        COUNT(CASE WHEN ecategory = 'Public' THEN fee ELSE 0 END) AS Public
    FROM
        attendences
    RIGHT JOIN events ON attendences.eid = events.eventid
    RIGHT JOIN locations ON attendences.lid = locations.locationid
    GROUP BY
        locations.country


SELECT DISTINCT events.ecategory,
    COUNT(CASE WHEN pcategory = '0' THEN aid ELSE 0 END) AS type0,
    COUNT(CASE WHEN pcategory = '1' THEN aid ELSE 0 END) AS type1
FROM
    attendences
RIGHT JOIN participants ON attendences.pid = participants.participantid
RIGHT JOIN events ON attendences.eid = events.eventid
GROUP BY events.ecategory

SELECT DISTINCT 
    events.ecategory, 
    SUM(CASE WHEN quater='1' THEN fee ELSE 0 END) AS quater1, 
    SUM(CASE WHEN quater='2' THEN fee ELSE 0 END) AS quater2, 
    SUM(CASE WHEN quater='3' THEN fee ELSE 0 END) AS quater3, 
    SUM(CASE WHEN quater='4' THEN fee ELSE 0 END) AS quater4 
FROM 
    attendences 
RIGHT JOIN dates ON attendences.did = dates.dateid 
RIGHT JOIN events ON attendences.eid = events.eventid 
GROUP BY events.ecategory

pseudocode is

SELECT DISTINCT
    rowTable.rowColumn,
    measureOperation(CASE WHEN colTable.colName="column_unique_value" THEN measure ELSE 0 END) AS colTable.colName.column_unique_value
FROM
    fact_table
RIGHT JOIN rowTable ON fact_table.foriegn_key = rowTable.primary_key
RIGHT JOIN colTable ON fact_table.foriegn_key = colTable.primary_key
GROUP BY rowTable.rowColumn

SELECT DISTINCT 
events.ecategory, 
SUM(CASE WHEN quater='1' THEN fee ELSE null END) AS 'q1', 
SUM(CASE WHEN quater='2' THEN fee ELSE null END) AS 'q2', 
SUM(CASE WHEN quater='3' THEN fee ELSE null END) AS 'q3', 
SUM(CASE WHEN quater='4' THEN fee ELSE null END) AS 'q4' 
FROM attendences 
RIGHT JOIN dates ON attendences.did = dates.dateid 
RIGHT JOIN events ON attendences.eid = events.eventid 
GROUP BY events.ecategory