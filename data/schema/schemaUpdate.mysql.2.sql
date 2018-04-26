ALTER TABLE `user_role_linker` MODIFY `role_id` VARCHAR(255) NOT NULL;

ALTER TABLE `entry_common` ADD `notification_state` INT NOT NULL;
