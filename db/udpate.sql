DROP TABLE `categorise`, `employees`, `employee_detail`, `tree`, `uploads`;

CREATE TABLE `chombung`.`categorise` (
  `id` int(11) NOT NULL,
  `ref` varchar(255) DEFAULT NULL,
  `category_id` varchar(255) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL COMMENT 'รหัส',
  `emp_id` varchar(255) DEFAULT NULL COMMENT 'พนักงาน',
  `name` varchar(255) NOT NULL COMMENT 'ชนิดข้อมูล',
  `title` varchar(255) DEFAULT NULL COMMENT 'ชื่อ',
  `description` varchar(255) DEFAULT NULL COMMENT 'รายละเอียดเพิ่มเติม',
  `data_json` json DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `chombung`.`categorise` ADD PRIMARY KEY (`id`);
ALTER TABLE `chombung`.`categorise` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1859 ;

SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';

INSERT INTO `chombung`.`categorise`(`id`, `ref`, `category_id`, `code`, `emp_id`, `name`, `title`, `description`, `data_json`, `active`) SELECT `id`, `ref`, `category_id`, `code`, `emp_id`, `name`, `title`, `description`, `data_json`, `active` FROM `backup_18_04_2024`.`categorise`;
CREATE TABLE `chombung`.`employees` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ref` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `photo` blob,
  `phone` varchar(20) DEFAULT NULL,
  `cid` varchar(17) DEFAULT NULL COMMENT 'เลขบัตรประชาชน',
  `email` varchar(255) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL COMMENT 'เพศ',
  `prefix` varchar(20) DEFAULT NULL COMMENT 'คำนำหน้า',
  `fname` varchar(200) NOT NULL COMMENT 'ชื่อ',
  `lname` varchar(200) NOT NULL COMMENT 'นามสกุล',
  `fname_en` varchar(200) DEFAULT NULL COMMENT 'ชื่อ(TH)',
  `lname_en` varchar(200) DEFAULT NULL COMMENT 'นามสกุล(EN)',
  `birthday` date DEFAULT NULL COMMENT 'วันเกิด',
  `join_date` date DEFAULT NULL COMMENT 'เริ่มงาน',
  `end_date` date DEFAULT NULL COMMENT 'ทำงานวันสุดท้าย',
  `address` varchar(255) DEFAULT NULL COMMENT 'ที่อยู่',
  `province` int(11) DEFAULT NULL COMMENT 'จังหวัด',
  `amphure` int(11) DEFAULT NULL COMMENT 'อำเภอ',
  `district` int(11) DEFAULT NULL COMMENT 'ตำบล',
  `zipcode` int(11) DEFAULT NULL COMMENT 'รหัสไปรษณีย์',
  `position_group` varchar(100) DEFAULT NULL COMMENT 'ประเภท/กลุ่มงาน',
  `expertise` int(11) DEFAULT NULL COMMENT 'ความเชี่ยวชาญ',
  `position_name` varchar(100) DEFAULT NULL COMMENT 'ตำแหน่ง',
  `position_type` varchar(100) DEFAULT NULL COMMENT 'ตำแหน่ง',
  `position_level` varchar(100) DEFAULT NULL COMMENT 'ตำแหน่ง',
  `position_number` varchar(100) DEFAULT NULL COMMENT 'ตำแหน่ง',
  `position_manage` int(11) DEFAULT NULL COMMENT 'ตำแหน่งบริหาร',
  `education` int(11) DEFAULT NULL COMMENT 'การศึกษา',
  `department` int(11) DEFAULT NULL COMMENT 'แผนก/ฝ่าย',
  `salary` int(11) DEFAULT NULL COMMENT 'เงินเดือน',
  `status` int(11) DEFAULT NULL COMMENT 'สถานะ',
  `data_json` json DEFAULT NULL,
  `emergency_contact` json DEFAULT NULL COMMENT 'ติดต่อในกรณีฉุกเฉิน',
  `updated_at` datetime DEFAULT NULL COMMENT 'วันเวลาแก้ไข',
  `created_at` datetime DEFAULT NULL COMMENT 'วันเวลาสร้าง',
  `created_by` int(11) DEFAULT NULL COMMENT 'ผู้สร้าง',
  `updated_by` int(11) DEFAULT NULL COMMENT 'ผู้แก้ไข'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `chombung`.`employees` ADD PRIMARY KEY (`id`);
ALTER TABLE `chombung`.`employees` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=588 ;

SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';

INSERT INTO `chombung`.`employees`(`id`, `user_id`, `ref`, `avatar`, `photo`, `phone`, `cid`, `email`, `gender`, `prefix`, `fname`, `lname`, `fname_en`, `lname_en`, `birthday`, `join_date`, `end_date`, `address`, `province`, `amphure`, `district`, `zipcode`, `position_group`, `expertise`, `position_name`, `position_type`, `position_level`, `position_number`, `position_manage`, `education`, `department`, `salary`, `status`, `data_json`, `emergency_contact`, `updated_at`, `created_at`, `created_by`, `updated_by`) SELECT `id`, `user_id`, `ref`, `avatar`, `photo`, `phone`, `cid`, `email`, `gender`, `prefix`, `fname`, `lname`, `fname_en`, `lname_en`, `birthday`, `join_date`, `end_date`, `address`, `province`, `amphure`, `district`, `zipcode`, `position_group`, `expertise`, `position_name`, `position_type`, `position_level`, `position_number`, `position_manage`, `education`, `department`, `salary`, `status`, `data_json`, `emergency_contact`, `updated_at`, `created_at`, `created_by`, `updated_by` FROM `backup_18_04_2024`.`employees`;
CREATE TABLE `chombung`.`employee_detail` (
  `id` int(11) NOT NULL,
  `ref` varchar(255) DEFAULT NULL,
  `emp_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `data_json` json DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL COMMENT 'ผู้สร้าง',
  `updated_by` int(11) DEFAULT NULL COMMENT 'ผู้แก้ไข'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `chombung`.`employee_detail` ADD PRIMARY KEY (`id`);
ALTER TABLE `chombung`.`employee_detail` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=555 ;

SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';

INSERT INTO `chombung`.`employee_detail`(`id`, `ref`, `emp_id`, `name`, `data_json`, `updated_at`, `created_at`, `created_by`, `updated_by`) SELECT `id`, `ref`, `emp_id`, `name`, `data_json`, `updated_at`, `created_at`, `created_by`, `updated_by` FROM `backup_18_04_2024`.`employee_detail`;
CREATE TABLE `chombung`.`tree` (
  `id` bigint(20) NOT NULL,
  `root` int(11) DEFAULT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `lvl` smallint(5) NOT NULL,
  `name` varchar(60) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `tb_name` varchar(255) DEFAULT NULL,
  `leader` int(11) DEFAULT NULL,
  `data_json` json DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `icon_type` smallint(1) NOT NULL DEFAULT '1',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `selected` tinyint(1) NOT NULL DEFAULT '0',
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  `readonly` tinyint(1) NOT NULL DEFAULT '0',
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `collapsed` tinyint(1) NOT NULL DEFAULT '0',
  `movable_u` tinyint(1) NOT NULL DEFAULT '1',
  `movable_d` tinyint(1) NOT NULL DEFAULT '1',
  `movable_l` tinyint(1) NOT NULL DEFAULT '1',
  `movable_r` tinyint(1) NOT NULL DEFAULT '1',
  `removable` tinyint(1) NOT NULL DEFAULT '1',
  `removable_all` tinyint(1) NOT NULL DEFAULT '0',
  `child_allowed` tinyint(1) NOT NULL DEFAULT '1',
  `visibleOrig` tinyint(1) DEFAULT '1',
  `disabledOrig` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `chombung`.`tree` ADD PRIMARY KEY (`id`), ADD KEY `tree_NK1` (`root`), ADD KEY `tree_NK2` (`lft`), ADD KEY `tree_NK3` (`rgt`), ADD KEY `tree_NK4` (`lvl`), ADD KEY `tree_NK5` (`active`);
ALTER TABLE `chombung`.`tree` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33 ;

SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';

INSERT INTO `chombung`.`tree`(`id`, `root`, `lft`, `rgt`, `lvl`, `name`, `code`, `tb_name`, `leader`, `data_json`, `icon`, `icon_type`, `active`, `selected`, `disabled`, `readonly`, `visible`, `collapsed`, `movable_u`, `movable_d`, `movable_l`, `movable_r`, `removable`, `removable_all`, `child_allowed`, `visibleOrig`, `disabledOrig`) SELECT `id`, `root`, `lft`, `rgt`, `lvl`, `name`, `code`, `tb_name`, `leader`, `data_json`, `icon`, `icon_type`, `active`, `selected`, `disabled`, `readonly`, `visible`, `collapsed`, `movable_u`, `movable_d`, `movable_l`, `movable_r`, `removable`, `removable_all`, `child_allowed`, `visibleOrig`, `disabledOrig` FROM `backup_18_04_2024`.`tree`;
CREATE TABLE `chombung`.`uploads` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `ref` varchar(255) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `real_filename` varchar(255) DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `type` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `chombung`.`uploads` ADD PRIMARY KEY (`id`);
ALTER TABLE `chombung`.`uploads` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=477 ;

SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';

INSERT INTO `chombung`.`uploads`(`id`, `name`, `ref`, `filename`, `file_name`, `real_filename`, `size`, `type`) SELECT `id`, `name`, `ref`, `filename`, `file_name`, `real_filename`, `size`, `type` FROM `backup_18_04_2024`.`uploads`;