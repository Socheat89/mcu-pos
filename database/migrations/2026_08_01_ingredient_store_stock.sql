-- Migration: Per-Store Ingredient Stock for Coffee Business Type
-- Ingredients are transferred between stores (not products)
-- When selling, deduct ingredients based on recipe, not product stock

CREATE TABLE IF NOT EXISTS ingredient_store_stock (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id   INT NOT NULL,
    store_id    INT NOT NULL,
    ingredient_id INT NOT NULL,
    quantity    DECIMAL(10,3) NOT NULL DEFAULT 0,
    unit        VARCHAR(50)  DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_store_ing (store_id, ingredient_id),
    INDEX idx_tenant_store (tenant_id, store_id),
    INDEX idx_ingredient (ingredient_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add store_id and note to ingredient_stock_logs if not exists
ALTER TABLE ingredient_stock_logs
    ADD COLUMN IF NOT EXISTS store_id INT DEFAULT NULL AFTER tenant_id,
    ADD COLUMN IF NOT EXISTS note VARCHAR(255) DEFAULT NULL AFTER reason;

-- Seed: Copy existing global ingredient stock into the default store
INSERT INTO ingredient_store_stock (tenant_id, store_id, ingredient_id, quantity)
SELECT
    i.tenant_id,
    s.id AS store_id,
    i.id AS ingredient_id,
    GREATEST(0, COALESCE(i.stock_quantity, 0)) AS quantity
FROM ingredients i
JOIN stores s ON s.tenant_id = i.tenant_id AND s.is_default = 1
ON DUPLICATE KEY UPDATE quantity = VALUES(quantity);
