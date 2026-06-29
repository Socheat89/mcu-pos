-- Migration to split POS System into 3 tiers
-- Run this to update your database

-- 1. Rename existing POS System (if any) or Insert new ones
-- We will delete the old generic 'POS System' and insert the 3 new ones.
-- Existing subscriptions to 'POS System' (id=1 presumably) might need to be migrated.
-- For safety, we will migrate existing 'POS System' subscribers to 'POS Standard' ($50).

-- Step 1: Insert new systems
INSERT INTO systems (name, description, price) VALUES
('POS Basic', 'Basic POS features', 10.00),
('POS Standard', 'Standard POS features', 50.00),
('POS Premium', 'Full POS features', 100.00);

-- Step 2: Migrate existing tenants
-- Assuming 'POS System' was the name. We need its ID.
SET @old_pos_id = (SELECT id FROM systems WHERE name = 'POS System' LIMIT 1);
SET @new_std_id = (SELECT id FROM systems WHERE name = 'POS Standard' LIMIT 1);

-- Update tenant_systems: change old POS system ID to new POS Standard ID
UPDATE tenant_systems SET system_id = @new_std_id WHERE system_id = @old_pos_id;

-- Step 3: Disable/Remove old POS System
DELETE FROM systems WHERE name = 'POS System';

-- Verify
SELECT * FROM systems;
