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



//  update leave status
UPDATE `leave` l
LEFT JOIN (
    SELECT 
        a.from_id,
        MAX(a.level) AS approve_level
    FROM approve a
    WHERE a.name = 'leave'
      AND a.status = 'pass'
    GROUP BY a.from_id
) a ON a.from_id = l.id
SET 
    l.status = CASE 
        WHEN a.approve_level = 1 THEN 'Checking1_pass'
        WHEN a.approve_level = 2 THEN 'Checking2_pass'
        ELSE l.status
    END
WHERE l.status = 'Checking';



SELECT 
    l.status,
    a.id AS approve_id,
    a.level AS approve_level,
    a.status
FROM `leave` l
LEFT JOIN approve a 
    ON a.from_id = l.id 
    AND a.name = 'leave'
    AND a.status = 'pass'
    AND a.level = (
        SELECT MAX(a2.level)
        FROM approve a2
        WHERE a2.from_id = l.id
          AND a2.name = 'leave'
          AND a2.status = 'pass'
    )
    WHERE l.status = 'Checking'
    <!-- ** เงื่อไข approve_level 
    = 1 update l.status = 'Checking1_pass'
    = 2 update l.status = 'Checking2_pass' -->

    -- ลบข้อมูลเดิมทั้งหมดของ leave_status
DELETE FROM categorise WHERE name = 'leave_status';

-- เพิ่มสถานะใหม่
INSERT INTO categorise (code, name, title) VALUES
('Pending', 'leave_status', 'รอ หน.เห็นชอบ'),
('Checking1_pass', 'leave_status', 'หน.เห็นชอบ'),
('Checking1_reject', 'leave_status', 'หน.ไม่เห็นชอบ'),
('Checking2_pass', 'leave_status', 'หน.กลุ่มงานเห็นชอบ'),
('Checking2_reject', 'leave_status', 'หน.กลุ่มงานไม่เห็นชอบ'),
('Checkup_pass', 'leave_status', 'ตรวจสอบผ่าน'),
('Checkup_reject', 'leave_status', 'ตรวจสอบไม่ผ่าน'),
('Approve', 'leave_status', 'ผอ.อนุมัติ'),
('ReqCancel', 'leave_status', 'ขอยกเลิก'),
('Cancel', 'leave_status', 'ยกเลิก'),
('Reject', 'leave_status', 'ไม่อนุมัติ');


