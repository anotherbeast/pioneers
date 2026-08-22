-- SQL to create the articles table for member blogs
-- Drop the table if it exists (be careful: this deletes all articles!)
DROP TABLE IF EXISTS articles;

CREATE TABLE articles (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  member_id INT UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  is_published TINYINT(1) DEFAULT 1,
  FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- To import: Use phpMyAdmin, MySQL Workbench, or the mysql CLI
-- Example CLI: mysql -u username -p database_name < create_articles_table.sql
