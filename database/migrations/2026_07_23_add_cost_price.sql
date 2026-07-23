-- Migration: Add cost_price to products for tracking purchase cost vs selling price
-- Date: 2026-07-23

ALTER TABLE products 
    ADD COLUMN cost_price DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Purchase/cost price' AFTER price;
