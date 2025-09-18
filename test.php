 SELECT x4.*, (x4.days - x4.use_leave) AS total,
                        (
                            CASE 
                                WHEN x4.accumulation = 0 THEN 10
                                WHEN (x4.days - x4.use_leave + 10) > x4.max_days AND x4.accumulation = 1 THEN x4.max_days
                                ELSE (x4.days - x4.use_leave + 10)
                            END
                        ) AS froward_days
                    FROM (
                        SELECT 
                            x3.*,
                            COALESCE(
                                (SELECT days 
                                FROM leave_entitlements 
                                WHERE emp_id = x3.emp_id 
                                AND leave_type_id = x3.leave_type_id 
                                AND thai_year = 2568), 
                                0
                            ) AS days,
                            COALESCE(
                                (SELECT SUM(total_days) 
                                FROM `leave` 
                                WHERE emp_id = x3.emp_id 
                                AND leave_type_id = x3.leave_type_id 
                                AND thai_year = 2568), 
                                0
                            ) AS use_leave
                        FROM (
                            SELECT 
                                x2.*,
                                COALESCE((
                                    SELECT max_days 
                                    FROM `leave_policies` 
                                    WHERE position_type_id = x2.position_type 
                                    AND year_of_service <= x2.years_of_service 
                                    ORDER BY year_of_service DESC 
                                    LIMIT 1
                                ),0) AS max_days
                            FROM (
                                SELECT 
                                    x1.*
                                FROM (
                                    SELECT 
                                        e.id AS emp_id,
                                        CONCAT(e.fname, ' ', e.lname) AS fullname,
                                        lt.title AS leave_type_name,
                                        l.leave_type_id,
                                        e.position_type,
                                        pt.title AS position_type_name,
                                        COALESCE(lp.accumulation,0) as accumulation,
                                        TIMESTAMPDIFF(YEAR, e.join_date, CURDATE()) AS years_of_service
                                    FROM employees e
                                    LEFT JOIN leave_policies lp 
                                        ON lp.position_type_id = e.position_type
                                    LEFT JOIN `leave` l 
                                        ON e.id = l.emp_id 
                                    AND l.leave_type_id = 'LT4'
                                    JOIN categorise lt 
                                        ON l.leave_type_id = lt.code 
                                    AND lt.name = 'leave_type'
                                    JOIN categorise pt 
                                        ON e.position_type = pt.code 
                                    AND pt.name = 'position_type'
                                    GROUP BY e.id
                                    ORDER BY e.id ASC
                                ) AS x1
                            ) AS x2
                        ) AS x3
                    ) AS x4