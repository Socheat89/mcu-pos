-- Migration: Add cost_price to ingredients table for profit tracking
ALTER TABLE ingredients ADD COLUMN cost_price DECIMAL(10,3) DEFAULT 0.000 COMMENT 'Cost price per unit for profit tracking' AFTER min_stock_alert;
