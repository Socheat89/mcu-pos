-- Migration: Add Free Trial Plan (7 days)
-- Date: 2026-07-14

-- 1. Add is_trial column to tenant_systems
ALTER TABLE tenant_systems 
    ADD COLUMN is_trial TINYINT(1) DEFAULT 0 COMMENT '1=trial subscription, 0=paid' AFTER status;

-- 2. Insert Free Trial plan into systems (only if not exists)
INSERT INTO systems (name, description, price, status, store_limit, cashier_limit)
SELECT 'Free Trial', '7-day free trial with basic POS features. No credit card required.', 0.00, 'active', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM systems WHERE name = 'Free Trial');

-- 3. Add basic features for Free Trial plan in system_modules
-- First get the Free Trial system_id
SET @trial_id = (SELECT id FROM systems WHERE name = 'Free Trial' LIMIT 1);

-- Only insert if trial plan was created and features don't already exist
INSERT INTO system_modules (system_id, module_name, feature_key)
SELECT @trial_id, 'pos', 'core'
WHERE @trial_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM system_modules WHERE system_id = @trial_id AND module_name = 'pos' AND feature_key = 'core');

INSERT INTO system_modules (system_id, module_name, feature_key)
SELECT @trial_id, 'pos', 'orders'
WHERE @trial_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM system_modules WHERE system_id = @trial_id AND module_name = 'pos' AND feature_key = 'orders');

INSERT INTO system_modules (system_id, module_name, feature_key)
SELECT @trial_id, 'pos', 'inventory'
WHERE @trial_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM system_modules WHERE system_id = @trial_id AND module_name = 'pos' AND feature_key = 'inventory');

INSERT INTO system_modules (system_id, module_name, feature_key)
SELECT @trial_id, 'pos', 'customers'
WHERE @trial_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM system_modules WHERE system_id = @trial_id AND module_name = 'pos' AND feature_key = 'customers');

INSERT INTO system_modules (system_id, module_name, feature_key)
SELECT @trial_id, 'pos', 'settings'
WHERE @trial_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM system_modules WHERE system_id = @trial_id AND module_name = 'pos' AND feature_key = 'settings');
