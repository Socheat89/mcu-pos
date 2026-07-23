-- Migration: Add bank_name to payments and expand method to support bank names
-- Date: 2026-07-23

-- 1. Change method from ENUM to VARCHAR to support bank names
ALTER TABLE payments MODIFY COLUMN method VARCHAR(50) NOT NULL DEFAULT 'cash' COMMENT 'cash, aba, acleda, wing, truemoney, card, other';

-- 2. Add bank_name column
ALTER TABLE payments ADD COLUMN bank_name VARCHAR(50) DEFAULT NULL COMMENT 'Bank name if payment via bank transfer' AFTER method;

-- 3. Add index on bank_name
ALTER TABLE payments ADD INDEX idx_bank_name (bank_name);
