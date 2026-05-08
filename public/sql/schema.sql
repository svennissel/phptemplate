CREATE TABLE IF NOT EXISTS meta_info (
    `key`   VARCHAR(64) PRIMARY KEY,
    value VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO meta_info (`key`, value) VALUES ('schema_version', '1')
    ON DUPLICATE KEY UPDATE value = VALUES(value);

CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120) NOT NULL,
    email       VARCHAR(190) NULL,
    hash        VARCHAR(64)  NOT NULL UNIQUE,
    is_admin    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
