ALTER TABLE shopping_list_items ADD COLUMN amount INT NOT NULL DEFAULT 1 AFTER name;
UPDATE meta_info SET value = '2' WHERE `key` = 'schema_version';
