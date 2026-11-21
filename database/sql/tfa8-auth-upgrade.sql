-- Apply ONCE to the existing TFA7 POS database through phpMyAdmin.
-- Back up the database first. No existing customer/user/avatar records are deleted.
-- Existing users remain without login credentials until explicitly configured.
ALTER TABLE users
    ADD COLUMN email VARCHAR(100) NULL,
    ADD COLUMN password VARCHAR(255) NULL,
    ADD COLUMN email_verified_at TIMESTAMP NULL DEFAULT NULL,
    ADD COLUMN remember_token VARCHAR(100) NULL,
    ADD UNIQUE KEY users_email_unique (email);

CREATE TABLE password_reset_tokens (
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO migrations (migration, batch)
SELECT '2026_11_21_000004_add_authentication_to_users', COALESCE(MAX(batch), 0) + 1
FROM (SELECT batch FROM migrations) AS previous_batches;
-- Register a NEW username/email through /register to make the first login.
-- There is deliberately no hard-coded/shared password in this SQL.
