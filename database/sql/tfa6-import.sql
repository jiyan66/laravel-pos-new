-- TFA6 Laravel POS. Import ONCE into a NEW EMPTY MySQL/MariaDB database.
-- No existing table is dropped. Do not import into the Tasks project database.
SET NAMES utf8mb4;
SET @seed_now = NOW();

CREATE TABLE customers (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY users_username_unique (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE migrations (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    migration VARCHAR(255) NOT NULL,
    batch INT NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

START TRANSACTION;
INSERT INTO customers (full_name, email, phone, created_at, updated_at) VALUES
('Ana Reyes', 'ana.reyes@example.com', '09170000001', @seed_now, @seed_now),
('Mark Santos', 'mark.santos@example.com', '09170000002', @seed_now, @seed_now),
('Carla Cruz', 'carla.cruz@example.com', '09170000003', @seed_now, @seed_now),
('John Garcia', 'john.garcia@example.com', '09170000004', @seed_now, @seed_now),
('Ella Ramos', 'ella.ramos@example.com', '09170000005', @seed_now, @seed_now);

INSERT INTO users (username, full_name, created_at, updated_at) VALUES
('gian.mistica', 'Joseph Gian Carlo Mistica', @seed_now, @seed_now),
('maria.lopez', 'Maria Lopez', @seed_now, @seed_now),
('paolo.rivera', 'Paolo Rivera', @seed_now, @seed_now),
('sofia.torres', 'Sofia Torres', @seed_now, @seed_now),
('luis.mendoza', 'Luis Mendoza', @seed_now, @seed_now);

INSERT INTO migrations (migration, batch) VALUES
('2026_11_21_000001_create_customers_table', 1),
('2026_11_21_000002_create_users_table', 1);
COMMIT;

SELECT COUNT(*) AS customer_count FROM customers;
SELECT COUNT(*) AS user_count FROM users;
-- Both counts should be 5 immediately after import.
