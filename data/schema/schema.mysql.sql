CREATE TABLE `user` (
    `user_id`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `username`     VARCHAR(255) DEFAULT NULL UNIQUE,
    `email`        VARCHAR(255) DEFAULT NULL UNIQUE,
    `display_name` VARCHAR(50) DEFAULT NULL,
    `password`     VARCHAR(128) NOT NULL,
    `state`        SMALLINT(5) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_role_linker` (
    `user_id` INT(10) UNSIGNED NOT NULL,
    `role_id` VARCHAR(45) NOT NULL,
    PRIMARY KEY (`user_id`,`role_id`),
    KEY `user_role_linker_fk1_idx` (`user_id`),
    CONSTRAINT `user_role_linker_fk1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `project` (
    `id`                 INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name`               VARCHAR(255) DEFAULT NULL,
    `git_repository`     VARCHAR(4096) DEFAULT NULL,
    `path_to_res_folder` VARCHAR(4096) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
