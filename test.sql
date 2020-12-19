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