ALTER TABLE `employees` CHANGE `work_type` `work_shift` VARCHAR(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'normal' COMMENT 'ประเภทเวลาทำงาน: normal=ปกติ, shift=เวร 8 ชั่วโมง';
UPDATE `migration` SET `version` = 'm251007_051359_add_work_shift_to_employee' WHERE `migration`.`version` = 'm251007_051359_add_work_type_to_employee';


//  update เวร 8 จากใบลา
UPDATE employees e
LEFT JOIN (
    SELECT 
        emp_id,
        COUNT(*) AS off_count
    FROM calendar
    WHERE name = 'off'
      AND YEAR(`date_start`) = 2025
    GROUP BY emp_id
) c ON c.emp_id = e.id
SET e.work_shift = CASE
    WHEN c.off_count > 0 THEN 'shift'
    ELSE 'normal'
END;

UPDATE `leave` AS l
JOIN `employees` AS e ON l.emp_id = e.id
SET l.data_json = JSON_SET(
    COALESCE(l.data_json, '{}'),
    '$.work_shift',
    e.work_shift
);