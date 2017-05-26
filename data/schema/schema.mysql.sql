CREATE TABLE `user` (
    `user_id`      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `username`     VARCHAR(255) DEFAULT NULL UNIQUE,
    `email`        VARCHAR(255) DEFAULT NULL UNIQUE,
    `display_name` VARCHAR(50) DEFAULT NULL,
    `password`     VARCHAR(128) NOT NULL,
    `state`        SMALLINT(5) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_role_linker` (
    `user_id` INT(11) UNSIGNED NOT NULL,
    `role_id` VARCHAR(45) NOT NULL,
    PRIMARY KEY (`user_id`,`role_id`),
    KEY `user_role_linker_fk1_idx` (`user_id`),
    CONSTRAINT `user_role_linker_fk1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `team` (
    `id`   INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_settings` (
    `user_id` INT(11) UNSIGNED NOT NULL PRIMARY KEY,
    `locale`  VARCHAR(20) NOT NULL, -- Currently known max length is 11 char.
    `team_id` INT(11) UNSIGNED DEFAULT NULL,
    CONSTRAINT `user_settings_fk1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `user_settings_fk2` FOREIGN KEY (`team_id`) REFERENCES `team` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `team_member` (
    `user_id` INT(11) UNSIGNED NOT NULL,
    `team_id` INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY (`user_id`,`team_id`),
    KEY `team_member_fk1_idx` (`user_id`),
    KEY `team_member_fk2_idx` (`team_id`),
    CONSTRAINT `team_member_fk1_idx` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `team_member_fk2_idx` FOREIGN KEY (`team_id`) REFERENCES `team` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `app` (
    `id`                 INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `team_id`            INT(11) UNSIGNED DEFAULT NULL,
    `name`               VARCHAR(255) DEFAULT NULL,
    `git_repository`     VARCHAR(4096) DEFAULT NULL,
    `path_to_res_folder` VARCHAR(4096) DEFAULT NULL,
    CONSTRAINT `app_fk1` FOREIGN KEY (`team_id`) REFERENCES `team` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `app_resource` (
    `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `app_id`      INT(11) UNSIGNED NOT NULL,
    `name`        VARCHAR(255) NOT NULL,
    `locale`      VARCHAR(20) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    UNIQUE KEY `app_resource_uk1` (`app_id`, `name`),
    CONSTRAINT `app_resource_fk1` FOREIGN KEY (`app_id`) REFERENCES `app` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `app_resource_file` (
    `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `app_id`      INT(11) UNSIGNED NOT NULL,
    `name`        VARCHAR(255) NOT NULL,
    UNIQUE KEY `app_resource_file_uk1` (`app_id`, `name`),
    CONSTRAINT `app_resource_file_fk1` FOREIGN KEY (`app_id`) REFERENCES `app` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
