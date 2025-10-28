ALTER TABLE `employees` CHANGE `work_type` `work_shift` VARCHAR(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'normal' COMMENT 'ประเภทเวลาทำงาน: normal=ปกติ, shift=เวร 8 ชั่วโมง';
UPDATE `migration` SET `version` = 'm251007_051359_add_work_shift_to_employee' WHERE `migration`.`version` = 'm251007_051359_add_work_type_to_employee';


//  update เวร 8 จากใบลา
UPDATE employees e
SET e.work_shift = 'shift'
WHERE e.id IN (
    SELECT emp_id
    FROM `leave` l
    WHERE DATEDIFF(l.date_end, l.date_start) + 1 = l.total_days
      AND l.leave_type_id = 'LT4'
);