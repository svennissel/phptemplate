CREATE TABLE IF NOT EXISTS meta_info (
    `key`   VARCHAR(64) PRIMARY KEY,
    value VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO meta_info (`key`, value) VALUES ('schema_version', '2')
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
