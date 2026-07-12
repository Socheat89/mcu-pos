-- Migration: Add multi-store support
-- Each tenant can have multiple stores/branches

CREATE TABLE IF NOT EXISTS stores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) DEFAULT NULL COMMENT 'Short code like MAIN, TKG, SSK',
    address TEXT DEFAULT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    is_default TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_is_default (tenant_id, is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add store_id to products
ALTER TABLE products 
    ADD COLUMN store_id INT DEFAULT NULL AFTER tenant_id,
    ADD INDEX idx_store_id (store_id);

-- Add store_id to orders
ALTER TABLE orders 
    ADD COLUMN store_id INT DEFAULT NULL AFTER tenant_id,
    ADD INDEX idx_store_id (store_id);

-- Add store_id to customers
ALTER TABLE customers 
    ADD COLUMN store_id INT DEFAULT NULL AFTER tenant_id,
    ADD INDEX idx_store_id (store_id);

-- Add store_id to categories
ALTER TABLE categories 
    ADD COLUMN store_id INT DEFAULT NULL AFTER tenant_id,
    ADD INDEX idx_store_id (store_id);

-- Add store_id to stock_logs
ALTER TABLE stock_logs 
    ADD COLUMN store_id INT DEFAULT NULL AFTER tenant_id,
    ADD INDEX idx_store_id (store_id);

-- Add current_store_id to users
ALTER TABLE users 
    ADD COLUMN current_store_id INT DEFAULT NULL AFTER role_id;

-- Session tracking per store
CREATE TABLE IF NOT EXISTS store_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    store_id INT NOT NULL,
    user_id INT NOT NULL,
    opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    closed_at TIMESTAMP NULL DEFAULT NULL,
    opening_cash DECIMAL(10,2) DEFAULT 0,
    closing_cash DECIMAL(10,2) DEFAULT NULL,
    status ENUM('open', 'closed') DEFAULT 'open',
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tenant_store (tenant_id, store_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
