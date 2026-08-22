-- SQL to create the admins table for multiple admin users
-- Drop the table if it exists (be careful: this deletes all admin accounts!)
DROP TABLE IF EXISTS admins;

CREATE TABLE admins (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL UNIQUE,
  email VARCHAR(160) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- To import: Use phpMyAdmin, MySQL Workbench, or the mysql CLI
-- Example CLI: mysql -u username -p database_name < create_admins_table.sql
