-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: mariaDB
-- Generation Time: Apr 25, 2022 at 09:00 AM
-- Server version: 10.7.3-MariaDB-1:10.7.3+maria~focal
-- PHP Version: 8.0.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tcds_config`
--

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE `account` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `provider` varchar(255) NOT NULL,
  `client_id` varchar(255) NOT NULL,
  `properties` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `auth_assignment`
--

CREATE TABLE `auth_assignment` (
  `item_name` varchar(64) NOT NULL,
  `user_id` varchar(64) NOT NULL,
  `created_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `auth_item`
--

CREATE TABLE `auth_item` (
  `name` varchar(64) NOT NULL,
  `type` int(11) NOT NULL,
  `description` longtext DEFAULT NULL,
  `rule_name` varchar(64) DEFAULT NULL,
  `data` longtext DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `auth_item_child`
--

CREATE TABLE `auth_item_child` (
  `parent` varchar(64) NOT NULL,
  `child` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `auth_rule`
--

CREATE TABLE `auth_rule` (
  `name` varchar(64) NOT NULL,
  `data` longtext DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `auto_number`
--

CREATE TABLE `auto_number` (
  `group` varchar(32) NOT NULL,
  `number` int(11) DEFAULT NULL,
  `optimistic_lock` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `auto_number`
--

INSERT INTO `auto_number` (`group`, `number`, `optimistic_lock`, `update_time`) VALUES
('20N???????', 1686, 1, 1609387877),
('21N???????', 486, 1, 1613192882),
('3c5efc507583c026f27897d7b4cfa6d5', 49808, 1, 1609420682),
('653426cc3620db3bb1080fb646af3b91', 1, 1, 1643795062),
('76f36000b6de17dac8d1650f155f4371', 1, 1, 1590943789),
('83d622a5eb2176f5240dd6df4875f70f', 8687, 1, 1638847147);

-- --------------------------------------------------------

--
-- Table structure for table `session`
--

CREATE TABLE `session` (
  `id` char(40) NOT NULL,
  `expire` int(11) DEFAULT NULL,
  `data` blob DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(200) DEFAULT NULL,
  `login_time` datetime DEFAULT NULL COMMENT 'เสลาที่ login เข้าระบบ',
  `platform` varchar(200) DEFAULT NULL,
  `os` varchar(200) DEFAULT NULL,
  `browser` varchar(200) DEFAULT NULL,
  `browserVersion` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `system_data`
--

CREATE TABLE `system_data` (
  `id` varchar(255) NOT NULL,
  `data` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `system_data`
--

INSERT INTO `system_data` (`id`, `data`) VALUES
('system', '{\"his_api\":\"http://1dce-180-183-106-242.ngrok.io/HIS/index.php/\",\"barcode_api\":\"http://10.1.88.8:5000/barcode-him\",\"socket_api\":\"http://103.10.229.4:3000\"}');

-- --------------------------------------------------------

--
-- Table structure for table `sys_routing_log`
--

CREATE TABLE `sys_routing_log` (
  `id` int(36) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(255) DEFAULT NULL,
  `routing_id` varchar(255) DEFAULT NULL,
  `data_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `token`
--

CREATE TABLE `token` (
  `user_id` int(11) NOT NULL,
  `code` varchar(32) NOT NULL,
  `created_at` int(11) NOT NULL,
  `type` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `token`
--

INSERT INTO `token` (`user_id`, `code`, `created_at`, `type`) VALUES
(1, '4m3JYwE_aHLavHnvPJspwGyJK1QcVSMp', 1536588091, 0),
(2, 'N2kymKNbNGiHrPb25847dKM-JV58g4th', 1536589096, 0),
(3, '2f74if5M0qmXWx1UG7sb8LS6F_zPryLd', 1536589140, 0),
(5, 'oeYesvnCZp7knSFgpnnrAICCbqby6ccB', 1536806652, 0),
(6, 'vT539geLI3UmSA8SI4BqiFMQEjbXSUE8', 1536806687, 0),
(7, 'TrJV_5PcL_2BggfZhQ0--wbQkqezXur-', 1536806723, 0),
(8, 'hTMlixlpPpQzF0rZCdUqDjClhOT6RYYg', 1536806748, 0),
(15, 'kiwerHVI2Rn9hedTufwzs5RCrEQEZgV_', 1122334433, 0),
(13, 'FSWxa3uHyYYL2Q3WD86YkkL6vYsiFI2e', 1554801550, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` bigint(20) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(60) NOT NULL,
  `auth_key` varchar(32) NOT NULL,
  `confirmed_at` int(11) DEFAULT NULL,
  `unconfirmed_email` varchar(255) DEFAULT NULL,
  `blocked_at` int(11) DEFAULT NULL,
  `registration_ip` varchar(45) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `flags` int(11) DEFAULT NULL,
  `last_login_at` int(11) DEFAULT NULL,
  `status` smallint(6) DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `pname` varchar(20) DEFAULT NULL COMMENT 'คำนำหน้า',
  `fullname` varchar(255) DEFAULT NULL COMMENT 'ชื่อ-สกุล',
  `dep_id` int(11) DEFAULT NULL COMMENT 'หน่วงาน',
  `occ_id` int(11) DEFAULT NULL COMMENT 'อาชีพ',
  `occ_no` varchar(20) DEFAULT NULL COMMENT 'เลขที่ใบประกอบวิชาชีพ',
  `pos_id` int(11) DEFAULT NULL COMMENT 'ตำแหน่ง',
  `pos_no` varchar(20) DEFAULT NULL COMMENT 'เลขที่ประจำตำแหน่ง',
  `role` varchar(5) DEFAULT NULL,
  `doctor_id` varchar(10) DEFAULT NULL COMMENT 'รหัสแพทย์',
  `license_number` varchar(255) DEFAULT NULL COMMENT 'เลขใบประกอบฯ',
  `fullname_en` varchar(255) DEFAULT NULL COMMENT 'ชื่อ - สกุลแพทย์(อังกฤษ)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `email`, `password_hash`, `auth_key`, `confirmed_at`, `unconfirmed_email`, `blocked_at`, `registration_ip`, `created_at`, `updated_at`, `flags`, `last_login_at`, `status`, `password_reset_token`, `pname`, `fullname`, `dep_id`, `occ_id`, `occ_no`, `pos_id`, `pos_no`, `role`, `doctor_id`, `license_number`, `fullname_en`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$13$u5UX2ZHrwuKM2tS7eERumuRGVcK5gYdsJ2R6xc6EhekfvEC0aZOoS', 'E91fe3GZnto9i-qnNMR0vK5yniMZBnuF', NULL, NULL, NULL, '::1', 1536588090, 1650368613, NULL, 1581400404, 10, NULL, NULL, 'ปัจวัฒน์ ศรีบุญเรือง', NULL, NULL, NULL, NULL, NULL, '10', '9999', '1400908-009', 'patjawat');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Indexes for table `auto_number`
--
ALTER TABLE `auto_number`
  ADD PRIMARY KEY (`group`);

--
-- Indexes for table `session`
--
ALTER TABLE `session`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_data`
--
ALTER TABLE `system_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_routing_log`
--
ALTER TABLE `sys_routing_log`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `doctor_id` (`doctor_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sys_routing_log`
--
ALTER TABLE `sys_routing_log`
  MODIFY `id` int(36) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=463;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
