CREATE TABLE IF NOT EXISTS shopping_item_usage (
    list_id     INT          NOT NULL,
    name        VARCHAR(255) NOT NULL,
    usage_count INT          NOT NULL DEFAULT 1,
    last_added_at DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (list_id, name),
    CONSTRAINT fk_shopping_item_usage_list
        FOREIGN KEY (list_id) REFERENCES shopping_lists(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

UPDATE meta_info SET value = '3' WHERE `key` = 'schema_version';
