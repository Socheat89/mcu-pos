-- Migration: Add store locking for users
-- When locked_store_id is set, the user can ONLY access that store
-- and cannot switch to other stores.
-- Safe for MariaDB & MySQL: uses a conditional check before ALTER.

SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'users'
  AND COLUMN_NAME = 'locked_store_id';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE users ADD COLUMN locked_store_id INT DEFAULT NULL COMMENT ''User locked to this store (NULL=can switch freely)'' AFTER current_store_id',
    'SELECT ''locked_store_id already exists, skipping.'' AS msg'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
