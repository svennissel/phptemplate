DROP DATABASE IF EXISTS testdatabase;
CREATE DATABASE testdatabase;
USE testdatabase;

CREATE TABLE IF NOT EXISTS meta_info (
    `key`   VARCHAR(64) PRIMARY KEY,
    value VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO meta_info (`key`, value) VALUES ('schema_version', '3')
    ON DUPLICATE KEY UPDATE value = VALUES(value);

CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120) NOT NULL,
    hash        VARCHAR(64)  NOT NULL UNIQUE,
    is_admin    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS shopping_lists (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120) NOT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS shopping_list_items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    list_id     INT          NOT NULL,
    name        VARCHAR(255) NOT NULL,
    amount      INT          NOT NULL DEFAULT 1,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_shopping_list_items_list
        FOREIGN KEY (list_id) REFERENCES shopping_lists(id) ON DELETE CASCADE,
    INDEX idx_shopping_list_items_list (list_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS shopping_item_usage (
    list_id     INT          NOT NULL,
    name        VARCHAR(255) NOT NULL,
    usage_count INT          NOT NULL DEFAULT 1,
    last_added_at DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (list_id, name),
    CONSTRAINT fk_shopping_item_usage_list
        FOREIGN KEY (list_id) REFERENCES shopping_lists(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Testbenutzer anlegen (Hash: d8618b0e793fe773a1e53443c7b8297c)
-- Der Hash in der App ist URL-safe Base64. 16 Bytes -> 22 Zeichen.
INSERT INTO users (name, hash, is_admin) VALUES ('Test User', 'test-user-hash-123456', 0);
INSERT INTO users (name, hash, is_admin) VALUES ('Admin User', 'admin-user-hash-123456', 1);
