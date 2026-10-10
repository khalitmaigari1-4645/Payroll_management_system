-- ============================================================
-- PAYROLL MANAGEMENT SYSTEM - COMPLETE DATABASE SCHEMA
-- File: database/database.sql
-- 
-- HOW TO USE:
--   1. Open phpMyAdmin
--   2. Create a database named: payroll_db
--   3. Select it, open the SQL tab
--   4. Paste this entire file and click Go
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. USERS TABLE
-- ============================================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `role` VARCHAR(20) DEFAULT 'User',
  `status` ENUM('Active','Inactive') DEFAULT 'Active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`username`, `password`, `full_name`, `email`, `role`, `status`) VALUES
('admin',    'admin123', 'System Administrator', 'admin@example.com', 'Admin', 'Active'),
('sarki',    'sarki123', 'sarki emmanuel',       'example@gmail.com', 'User',  'Active'),
('cyberboi', 'cyber123', 'walit kamra',          'kamra@gmail.com',   'User',  'Active');

-- ============================================================
-- 2. DEPARTMENTS TABLE
-- ============================================================
DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `status` ENUM('Active','Inactive') DEFAULT 'Active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `departments` (`name`, `status`) VALUES
('Cyber department',       'Active'),
('Information Technology', 'Active'),
('Administration',         'Active'),
('Finance',                'Active'),
('Human Resources',        'Active'),
('Operations',             'Active');

-- ============================================================
-- 3. POSITIONS TABLE
-- ============================================================
DROP TABLE IF EXISTS `positions`;
CREATE TABLE `positions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(100) NOT NULL,
  `department_id` INT(11) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('Active','Inactive') DEFAULT 'Active',
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `positions_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `positions` (`title`, `department_id`, `description`, `status`) VALUES
('cyber security',        1, 'protect the company network',   'Active'),
('accountant',            1, 'jinksdjnkfhisr fusrr',          'Inactive'),
('Administrative Officer',3, 'Administrative Officer',        'Active'),
('Software Developer',    2, 'Build and maintain software',   'Active'),
('Network Engineer',      2, 'Manage network infrastructure', 'Active'),
('HR Officer',            5, 'Handle employee relations',     'Active');

-- ============================================================
-- 4. EMPLOYEES TABLE
-- ============================================================
DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `employee_id_code` VARCHAR(50) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `department_id` INT(11) DEFAULT NULL,
  `position_id` INT(11) DEFAULT NULL,
  `basic_salary` DECIMAL(15,2) DEFAULT 0.00,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `gender` VARCHAR(10) DEFAULT 'Male',
  `employment_type` VARCHAR(30) DEFAULT 'Full-time',
  `phone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('Active','Inactive') DEFAULT 'Active',
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  KEY `position_id` (`position_id`),
  CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `employees` 
(`employee_id_code`, `full_name`, `gender`, `email`, `phone`, `department_id`, `position_id`, `employment_type`, `basic_salary`, `status`) 
VALUES
('23/1098',          'kamara ahmadu ado',       'Male', 'kamara@example.com',  '+2348000000001', 1, 1, 'Full-time', 0.00,      'Active'),
('ef100h',           'khaleed ibrahim maigari', 'Male', 'khaleed@example.com', '+2348000000002', 2, 4, 'Full-time', 250000.00, 'Active'),
('COEW/23/SCI/0098', 'Auwal Cele Adamu',       'Male', 'auwal@example.com',   '+2348000000003', 4, 2, 'Full-time', 180000.00, 'Active');

-- ============================================================
-- 5. ALLOWANCES TABLE
-- ============================================================
DROP TABLE IF EXISTS `allowances`;
CREATE TABLE `allowances` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `employee_id` INT(11) DEFAULT NULL,
  `allowance_name` VARCHAR(100) NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `is_percentage` TINYINT(1) DEFAULT 0,
  `effective_date` DATE NOT NULL,
  `status` ENUM('Active','Inactive') DEFAULT 'Active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `allowances_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `allowances` (`employee_id`, `allowance_name`, `amount`, `is_percentage`, `effective_date`, `status`) VALUES
(NULL, 'Housing Allowance',   20000.00, 0, '2026-08-20', 'Active'),
(NULL, 'Transport Allowance', 10000.00, 0, '2026-08-20', 'Active'),
(NULL, 'House Allow',         15000.00, 0, '2026-08-20', 'Active'),
(NULL, 'Medical Allowance',    5000.00, 0, '2026-08-20', 'Active');

-- ============================================================
-- 6. PAYROLL PERIODS TABLE
--    Note: ENUM includes 'Pending' to support the code path
-- ============================================================
DROP TABLE IF EXISTS `payroll_periods`;
CREATE TABLE `payroll_periods` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `period_name` VARCHAR(100) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `pay_date` DATE NOT NULL,
  `status` ENUM('Draft','Pending','Processed','Paid') DEFAULT 'Draft',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `payroll_periods` (`period_name`, `start_date`, `end_date`, `pay_date`, `status`) VALUES
('August 2026 Payroll',    '2026-08-01', '2026-08-31', '2026-08-30', 'Processed'),
('September 2026 Payroll', '2026-09-01', '2026-09-30', '2026-09-30', 'Draft'),
('July 2026 Payroll',      '2026-07-01', '2026-07-31', '2026-07-31', 'Processed');

-- ============================================================
-- 7. PAYSLIPS TABLE
-- ============================================================
DROP TABLE IF EXISTS `payslips`;
CREATE TABLE `payslips` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `employee_id` INT(11) NOT NULL,
  `payroll_period_id` INT(11) NOT NULL,
  `basic_salary` DECIMAL(15,2) DEFAULT 0.00,
  `total_allowances` DECIMAL(15,2) DEFAULT 0.00,
  `gross_salary` DECIMAL(15,2) DEFAULT 0.00,
  `total_deductions` DECIMAL(15,2) DEFAULT 0.00,
  `net_salary` DECIMAL(15,2) DEFAULT 0.00,
  `status` ENUM('Calculated','Paid') DEFAULT 'Calculated',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `payroll_period_id` (`payroll_period_id`),
  CONSTRAINT `payslips_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payslips_ibfk_2` FOREIGN KEY (`payroll_period_id`) REFERENCES `payroll_periods` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 8. DEDUCTIONS TABLE
--    Note: payslip_id FK has ON DELETE CASCADE so when a
--    payslip is regenerated, its snapshot rows are removed
--    automatically (prevents duplicate accumulation).
-- ============================================================
DROP TABLE IF EXISTS `deductions`;
CREATE TABLE `deductions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `employee_id` INT(11) DEFAULT NULL,
  `payslip_id` INT(11) DEFAULT NULL,
  `deduction_name` VARCHAR(100) NOT NULL,
  `amount` DECIMAL(15,2) DEFAULT 0.00,
  `is_percentage` TINYINT(1) DEFAULT 0,
  `effective_date` DATE DEFAULT NULL,
  `status` ENUM('Active','Inactive') DEFAULT 'Active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `payslip_id` (`payslip_id`),
  CONSTRAINT `fk_deductions_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_deductions_payslip`  FOREIGN KEY (`payslip_id`)  REFERENCES `payslips`  (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Master list deductions (applied to all employees by default)
INSERT INTO `deductions` (`employee_id`, `payslip_id`, `deduction_name`, `amount`, `is_percentage`, `effective_date`, `status`) VALUES
(NULL, NULL, 'Tax',            7.50,    1, '2026-08-20', 'Active'),
(NULL, NULL, 'Pension',        8.00,    1, '2026-08-20', 'Active'),
(NULL, NULL, 'Loan Repayment', 5000.00, 0, '2026-08-20', 'Active');

-- ============================================================
-- 9. PAYMENT METHODS TABLE
-- ============================================================
DROP TABLE IF EXISTS `payment_methods`;
CREATE TABLE `payment_methods` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `method_name` VARCHAR(100) NOT NULL,
  `status` ENUM('Active','Inactive') DEFAULT 'Active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `payment_methods` (`method_name`, `description`, `status`) VALUES
('Bank Transfer', 'Direct deposit into employee bank account', 'Active'),
('Cash',          'Physical cash payment',                     'Active'),
('Cheque',        'Payment by written cheque',                 'Active');

-- ============================================================
SET FOREIGN_KEY_CHECKS = 1;
-- ============================================================
-- END OF SCHEMA
-- ============================================================