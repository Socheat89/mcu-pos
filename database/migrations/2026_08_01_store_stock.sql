-- Migration: Per-Store Stock Transfer System
-- Each store has its own stock quantity per product
-- Admin transfers stock from main store to branch stores

CREATE TABLE IF NOT EXISTS store_stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    store_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_store_product (store_id, product_id),
    INDEX idx_tenant_store (tenant_id, store_id),
    INDEX idx_product (product_id),
    FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add store_id column to stock_logs if not exists
ALTER TABLE stock_logs
    ADD COLUMN IF NOT EXISTS store_id INT DEFAULT NULL AFTER tenant_id,
    ADD COLUMN IF NOT EXISTS note VARCHAR(255) DEFAULT NULL AFTER reason;

-- Seed: Copy existing global stock_quantity into the default (main) store store_stock
-- This runs once. Existing products start with their current stock under the main store
INSERT INTO store_stock (tenant_id, store_id, product_id, quantity)
SELECT
    p.tenant_id,
    s.id AS store_id,
    p.id AS product_id,
    GREATEST(0, COALESCE(p.stock_quantity, 0)) AS quantity
FROM products p
JOIN stores s ON s.tenant_id = p.tenant_id AND s.is_default = 1
ON DUPLICATE KEY UPDATE quantity = VALUES(quantity);
