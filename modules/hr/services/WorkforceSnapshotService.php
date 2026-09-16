<?php
namespace app\modules\hr\services;

/** Shared workforce date semantics; extracted without changing the existing SQL. */
class WorkforceSnapshotService
{
    private const EXIT_STATUS_CODES = [
        '2',  // ลาออก
        '3', '4', '5', '6', // เกษียณอายุราชการ (ทุกแบบ)
        '7', '8', // ถึงแก่กรรม
        '9', '10', // ปลดออก
        '13', // ย้าย
        '22', '23', '34', // ลาออกรับบำนาญ/บำเหน็จ
        '24', // เลิกจ้าง
        '25', // ไล่ออก
        '26', // หมดสัญญาจ้าง
        '27', '28', '29', '30', // ให้ออก
        '31', // ให้โอน
        '33', '35', '36', '37', // บำนาญ/บำเหน็จถึงแก่กรรม
    ];
    public static function sql(): string
    {
        $exitIn = "'" . implode("','", self::EXIT_STATUS_CODES) . "'";

        return "(SELECT emp.*,
                        COALESCE(emp.join_date, hp.first_start) AS hire_date,
                        CASE WHEN emp.status IN ({$exitIn})
                             THEN COALESCE(emp.end_date, lp.ds, '1900-01-01')
                        END AS exit_date
                 FROM employees emp
                 LEFT JOIN (
                    SELECT emp_id, MIN(STR_TO_DATE(JSON_UNQUOTE(data_json->'$.date_start'), '%Y-%m-%d')) AS first_start
                    FROM employee_detail WHERE name = 'position' GROUP BY emp_id
                 ) hp ON hp.emp_id = emp.id
                 LEFT JOIN (
                    SELECT emp_id, st, ds FROM (
                        SELECT emp_id,
                               JSON_UNQUOTE(data_json->'$.status') AS st,
                               STR_TO_DATE(JSON_UNQUOTE(data_json->'$.date_start'), '%Y-%m-%d') AS ds,
                               ROW_NUMBER() OVER (
                                   PARTITION BY emp_id
                                   ORDER BY STR_TO_DATE(JSON_UNQUOTE(data_json->'$.date_start'), '%Y-%m-%d') DESC, id DESC
                               ) AS rn
                        FROM employee_detail WHERE name = 'position'
                    ) ranked WHERE rn = 1
                 ) lp ON lp.emp_id = emp.id AND lp.st = emp.status)";
    }
    public static function inServiceWhereSql(string $alias = 'e'): string
    {
        $p = $alias !== '' ? $alias . '.' : '';

        return "({$p}hire_date IS NULL OR {$p}hire_date <= :as_of)"
            . " AND ({$p}exit_date IS NULL OR {$p}exit_date > :as_of)";
    }
}
