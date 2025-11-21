-- DB: inbolsa_dev (adjust in config.php)
CREATE DATABASE IF NOT EXISTS `inbolsa_dev` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `inbolsa_dev`;

-- Admin users
CREATE TABLE IF NOT EXISTS admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- QR codes
CREATE TABLE IF NOT EXISTS qr_codes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(64) NOT NULL UNIQUE,
  type VARCHAR(64) NOT NULL,
  payload JSON NULL,
  status ENUM('active','revoked') NOT NULL DEFAULT 'active',
  usage_count INT NOT NULL DEFAULT 0,
  usage_limit INT NULL,
  expires_at DATETIME NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (code),
  INDEX (status),
  INDEX (expires_at),
  CONSTRAINT fk_qr_created_by FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed one admin (email + password)
-- Replace the hash with: php -r "echo password_hash('TuPasswordFuerte#2025', PASSWORD_BCRYPT), PHP_EOL;"
INSERT INTO admin_users (email, password_hash)
VALUES ('admin@inbolsa.com', '$2y$10$PUT_BCRYPT_HASH_HERE')
ON DUPLICATE KEY UPDATE email = VALUES(email);
