-- DGTEC Website Database Schema
-- Database: u186120816_egtech

CREATE TABLE IF NOT EXISTS `contacts` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `name`        VARCHAR(100) NOT NULL,
  `email`       VARCHAR(150) NOT NULL,
  `mobile`      VARCHAR(20) NOT NULL,
  `service`     VARCHAR(100) NOT NULL,
  `message`     TEXT,
  `ip_address`  VARCHAR(45),
  `is_read`     TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hero_slides` (
  `id`                INT AUTO_INCREMENT PRIMARY KEY,
  `position`          INT NOT NULL DEFAULT 0,
  `is_active`         TINYINT(1) NOT NULL DEFAULT 1,
  `label`             VARCHAR(255) NOT NULL DEFAULT '',
  `title`             TEXT NOT NULL,
  `highlight_text`    VARCHAR(255) NOT NULL DEFAULT '',
  `highlight_color`   VARCHAR(7) NOT NULL DEFAULT '',
  `description`       TEXT NOT NULL,
  `bg_image`          VARCHAR(500) NOT NULL DEFAULT '',
  `gradient_color1`   VARCHAR(7) NOT NULL DEFAULT '#183f96',
  `gradient_opacity1` DECIMAL(3,2) NOT NULL DEFAULT 0.84,
  `gradient_color2`   VARCHAR(7) NOT NULL DEFAULT '#183f96',
  `gradient_opacity2` DECIMAL(3,2) NOT NULL DEFAULT 0.45,
  `btn1_text`         VARCHAR(100) NOT NULL DEFAULT '',
  `btn1_url`          VARCHAR(500) NOT NULL DEFAULT '',
  `btn2_text`         VARCHAR(100) NOT NULL DEFAULT '',
  `btn2_url`          VARCHAR(500) NOT NULL DEFAULT '',
  `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `admin_users` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `username`      VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `display_name`  VARCHAR(100) NOT NULL DEFAULT '',
  `last_login`    DATETIME NULL,
  `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
