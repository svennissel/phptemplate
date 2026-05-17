ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL;
ALTER TABLE shopping_list_items ADD COLUMN user_id INT DEFAULT NULL;
ALTER TABLE shopping_list_items ADD CONSTRAINT fk_shopping_list_items_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

UPDATE meta_info SET value = '4' WHERE `key` = 'schema_version';
