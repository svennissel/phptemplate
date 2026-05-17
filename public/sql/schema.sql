CREATE TABLE IF NOT EXISTS meta_info (
    `key`   VARCHAR(64) PRIMARY KEY,
    value VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO meta_info (`key`, value) VALUES ('schema_version', '4')
    ON DUPLICATE KEY UPDATE value = VALUES(value);

CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120) NOT NULL,
    hash        VARCHAR(64)  NOT NULL UNIQUE,
    is_admin    TINYINT(1)   NOT NULL DEFAULT 0,
    profile_image VARCHAR(255) DEFAULT NULL,
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
    user_id     INT          DEFAULT NULL,
    name        VARCHAR(255) NOT NULL,
    amount      INT          NOT NULL DEFAULT 1,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_shopping_list_items_list
        FOREIGN KEY (list_id) REFERENCES shopping_lists(id) ON DELETE CASCADE,
    CONSTRAINT fk_shopping_list_items_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
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
