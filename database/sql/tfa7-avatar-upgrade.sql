-- Apply ONCE to the existing TFA6 POS database through phpMyAdmin.
-- No user or customer records are deleted.
ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL;
INSERT INTO migrations (migration, batch)
SELECT '2026_11_21_000003_add_avatar_to_users_table', COALESCE(MAX(batch), 0) + 1
FROM (SELECT batch FROM migrations) AS previous_batches;
-- If avatar already exists, stop: check whether the upgrade was applied already.
