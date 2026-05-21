-- ============================================================
-- AI-Powered HR Management System - Database Schema
-- MySQL / MariaDB  |  charset: utf8mb4
-- ============================================================
CREATE DATABASE IF NOT EXISTS hr_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hr_management;

-- ------------------------------------------------------------
-- 1. users  (account + employee profile merged)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email           VARCHAR(255) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    role            ENUM('ADMIN','EMPLOYEE') NOT NULL DEFAULT 'EMPLOYEE',
    status          ENUM('ACTIVE','INACTIVE','LOCKED') NOT NULL DEFAULT 'ACTIVE',
    employee_code   VARCHAR(20)  NOT NULL UNIQUE,
    full_name       VARCHAR(200) NOT NULL,
    phone           VARCHAR(20),
    date_of_birth   DATE,
    gender          ENUM('MALE','FEMALE','OTHER'),
    department      VARCHAR(100),
    position        VARCHAR(100),
    employment_type ENUM('FULL_TIME','PART_TIME','CONTRACT') DEFAULT 'FULL_TIME',
    base_salary     DECIMAL(15,2) DEFAULT 0,
    hourly_rate     DECIMAL(10,2) DEFAULT 0,
    start_date      DATE,
    last_login_at   DATETIME,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME,
    INDEX idx_email          (email),
    INDEX idx_employee_code  (employee_code),
    INDEX idx_status         (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 2. work_records  (schedule periods, shift slots,
--                   registrations, assignments, attendance)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS work_records (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id          INT UNSIGNED,
    record_type        ENUM('SCHEDULE_PERIOD','SHIFT_TEMPLATE','SHIFT_SLOT',
                            'REGISTRATION','ASSIGNMENT','ATTENDANCE') NOT NULL,
    employee_id        INT UNSIGNED,
    week_start_date    DATE,
    week_end_date      DATE,
    work_date          DATE,
    shift_name         VARCHAR(100),
    start_time         TIME,
    end_time           TIME,
    break_minutes      SMALLINT DEFAULT 30,
    required_headcount TINYINT  DEFAULT 1,
    preference_level   TINYINT  DEFAULT 1,
    record_status      VARCHAR(30),
    check_in_at        DATETIME,
    check_out_at       DATETIME,
    worked_minutes     INT DEFAULT 0,
    late_minutes       INT DEFAULT 0,
    overtime_minutes   INT DEFAULT 0,
    is_night_shift     TINYINT(1) DEFAULT 0,
    source             VARCHAR(30) DEFAULT 'MANUAL',
    note               TEXT,
    meta_json          JSON,
    created_by         INT UNSIGNED,
    created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type_date           (record_type, work_date),
    INDEX idx_type_emp_date       (record_type, employee_id, work_date),
    INDEX idx_parent              (parent_id),
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by)  REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 3. leave_requests
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS leave_requests (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id      INT UNSIGNED NOT NULL,
    leave_type       ENUM('ANNUAL','SICK','UNPAID','OTHER') NOT NULL,
    start_date       DATE NOT NULL,
    end_date         DATE NOT NULL,
    reason           TEXT NOT NULL,
    status           ENUM('PENDING','APPROVED','REJECTED','CANCELLED') NOT NULL DEFAULT 'PENDING',
    reviewed_by      INT UNSIGNED,
    reviewed_at      DATETIME,
    rejection_reason TEXT,
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_emp_dates_status (employee_id, start_date, end_date, status),
    FOREIGN KEY (employee_id)  REFERENCES users(id),
    FOREIGN KEY (reviewed_by)  REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 4. payroll_records  (period + item merged)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payroll_records (
    id                        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id                 INT UNSIGNED,
    record_type               ENUM('PERIOD','ITEM') NOT NULL,
    employee_id               INT UNSIGNED,
    name                      VARCHAR(200),
    period_start              DATE,
    period_end                DATE,
    status                    VARCHAR(20) NOT NULL DEFAULT 'DRAFT',
    base_amount               DECIMAL(15,2) DEFAULT 0,
    overtime_amount           DECIMAL(15,2) DEFAULT 0,
    allowance_amount          DECIMAL(15,2) DEFAULT 0,
    deduction_amount          DECIMAL(15,2) DEFAULT 0,
    final_amount              DECIMAL(15,2) DEFAULT 0,
    calculation_snapshot_json JSON,
    published_at              DATETIME,
    created_by                INT UNSIGNED,
    created_at                DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type_period (record_type, period_start, period_end),
    INDEX idx_parent      (parent_id),
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by)  REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 5. system_records  (tickets + audit + ai_reports merged)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS system_records (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    record_type          ENUM('TICKET','AUDIT','AI_REPORT') NOT NULL,
    employee_id          INT UNSIGNED,
    actor_user_id        INT UNSIGNED,
    related_entity_type  VARCHAR(50),
    related_entity_id    INT UNSIGNED,
    status               VARCHAR(30),
    title                VARCHAR(300),
    description          TEXT,
    action               VARCHAR(100),
    input_snapshot_json  JSON,
    output_json          JSON,
    old_value_json       JSON,
    new_value_json       JSON,
    model_name           VARCHAR(100),
    handled_by           INT UNSIGNED,
    handled_at           DATETIME,
    created_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type_entity (record_type, related_entity_type, related_entity_id),
    FOREIGN KEY (employee_id)   REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Seed data (demo admin + 2 employees)
-- password = 123456  (bcrypt)
-- ------------------------------------------------------------
INSERT INTO users (email, password_hash, role, status, employee_code, full_name, department, position, base_salary, hourly_rate, start_date) VALUES
('admin@hr.vn',   '$2y$10$/Ix2AQVbuB9gp7xZVZEUMeq1X/jRAFWxbtxgCy/s5SIteXeN0rEbm', 'ADMIN',    'ACTIVE', 'EMP0000', 'Nguyễn Admin',     'Ban Giám Đốc', 'Quản trị viên', 20000000, 100000, '2024-01-01'),
('nv1@hr.vn',     '$2y$10$/Ix2AQVbuB9gp7xZVZEUMeq1X/jRAFWxbtxgCy/s5SIteXeN0rEbm', 'EMPLOYEE', 'ACTIVE', 'EMP0001', 'Trần Văn An',       'Kỹ thuật',     'Lập trình viên', 15000000, 75000, '2024-03-01'),
('nv2@hr.vn',     '$2y$10$/Ix2AQVbuB9gp7xZVZEUMeq1X/jRAFWxbtxgCy/s5SIteXeN0rEbm', 'EMPLOYEE', 'ACTIVE', 'EMP0002', 'Lê Thị Bình',       'Kinh doanh',   'Nhân viên Sales', 12000000, 60000, '2024-04-15');