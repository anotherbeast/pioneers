-- SQL to create the members table for SundayLaw.com
-- Drop the table if it exists (be careful: this deletes all member data!)
DROP TABLE IF EXISTS members;

CREATE TABLE members (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(40),
  address1 VARCHAR(160),
  address2 VARCHAR(160),
  city VARCHAR(80),
  state VARCHAR(80),
  zip VARCHAR(24),
  country VARCHAR(80),
  heard_about VARCHAR(160),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  email_verification_token VARCHAR(128),
  email_verified TINYINT(1) DEFAULT 0,
  reset_token VARCHAR(128),
  reset_token_expires DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- To import: Use phpMyAdmin, MySQL Workbench, or the mysql CLI
-- Example CLI: mysql -u username -p database_name < create_members_table.sql
