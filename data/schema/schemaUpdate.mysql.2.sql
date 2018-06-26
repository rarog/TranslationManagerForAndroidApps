ALTER TABLE `user_role_linker` MODIFY `role_id` VARCHAR(255) NOT NULL;

ALTER TABLE `entry_common` ADD `notification_status` INT NOT NULL;

CREATE TABLE `setting` (
    `id`    BIGINT NOT NULL AUTO_INCREMENT,
    `path`  VARCHAR(255) NOT NULL,
    `value` TEXT DEFAULT NULL,
    CONSTRAINT `setting_pk` PRIMARY KEY (`id`),
    CONSTRAINT `setting_uk1` UNIQUE (`path`)
);
