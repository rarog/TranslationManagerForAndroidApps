CREATE TABLE `log` (
    `id`               BIGINT(20) NOT NULL AUTO_INCREMENT,
    `timestamp`        VARCHAR(25) NOT NULL,
    `priority`         SMALLINT(5) NOT NULL,
    `priority_name`    VARCHAR(10) NOT NULL,
    `message`          VARCHAR(4096) NOT NULL,
    `message_extended` TEXT DEFAULT NULL,
    `file`             VARCHAR(1024) DEFAULT NULL,
    `class`            VARCHAR(1024) DEFAULT NULL,
    `line`             BIGINT(20) DEFAULT NULL,
    `function`         VARCHAR(1024) DEFAULT NULL,
    CONSTRAINT `log_pk` PRIMARY KEY (`id`),
    INDEX `log_ik1` (`priority`),
    INDEX `log_ik2` (`class`),
    INDEX `log_ik3` (`function`)
);

CREATE TABLE `user` (
    `user_id`      BIGINT(20) NOT NULL AUTO_INCREMENT,
    `username`     VARCHAR(255) DEFAULT NULL UNIQUE,
    `email`        VARCHAR(255) DEFAULT NULL UNIQUE,
    `display_name` VARCHAR(50) DEFAULT NULL,
    `password`     VARCHAR(128) NOT NULL,
    `state`        SMALLINT(5) DEFAULT NULL,
    CONSTRAINT `user_pk` PRIMARY KEY (`user_id`),
    CONSTRAINT `user_uk1` UNIQUE (`username`),
    CONSTRAINT `user_uk2` UNIQUE (`email`)
);

CREATE TABLE `user_role_linker` (
    `user_id` BIGINT(20) NOT NULL,
    `role_id` VARCHAR(45) NOT NULL,
    CONSTRAINT `user_role_linker_pk` PRIMARY KEY (`user_id`, `role_id`),
    CONSTRAINT `user_role_linker_fk1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `user_role_linker_ik1`(`user_id`)
);

CREATE TABLE `user_settings` (
    `user_id` BIGINT(20) NOT NULL,
    `locale`  VARCHAR(20) NOT NULL, -- Currently known max length is 11 char. 
    CONSTRAINT `user_settings_pk` PRIMARY KEY (`user_id`),
    CONSTRAINT `user_settings_fk1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE `user_languages` (
    `user_id` BIGINT(20) NOT NULL,
    `locale`  VARCHAR(20) NOT NULL, -- Currently known max length for primary locale is 3 char.
    CONSTRAINT `user_languages_pk` PRIMARY KEY (`user_id`, `locale`),
    CONSTRAINT `user_languages_fk1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `user_languages_ik1`(`user_id`) 
);

CREATE TABLE `team` (
    `id`   BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) DEFAULT NULL
);

CREATE TABLE `team_member` (
    `user_id` BIGINT(20) NOT NULL,
    `team_id` BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`user_id`,`team_id`),
    INDEX `team_member_fk1` (`user_id`),
    INDEX `team_member_fk2` (`team_id`),
    CONSTRAINT `team_member_fk1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `team_member_fk2` FOREIGN KEY (`team_id`) REFERENCES `team` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `app` (
    `id`                 BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `team_id`            BIGINT(20) UNSIGNED DEFAULT NULL,
    `name`               VARCHAR(255) DEFAULT NULL,
    `path_to_res_folder` VARCHAR(4096) DEFAULT NULL,
    `git_repository`     VARCHAR(4096) DEFAULT NULL,
    `git_username`       VARCHAR(255) DEFAULT NULL,
    `git_password`       VARCHAR(1024) DEFAULT NULL,
    `git_user`           VARCHAR(255) DEFAULT NULL,
    `git_email`          VARCHAR(255) DEFAULT NULL,
    INDEX `app_fk1` (`team_id`),
    CONSTRAINT `app_fk1` FOREIGN KEY (`team_id`) REFERENCES `team` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `app_resource` (
    `id`             BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `app_id`         BIGINT(20) UNSIGNED NOT NULL,
    `name`           VARCHAR(255) NOT NULL,
    `locale`         VARCHAR(20) NOT NULL,
    `primary_locale` VARCHAR(20) NOT NULL, -- Currently known max length for primary locale is 3 char. Field isn't available in model.
    `description`    VARCHAR(255) DEFAULT NULL,
    INDEX `app_resource_fk1` (`app_id`),
    INDEX `app_resource_ik1` (`primary_locale`),
    UNIQUE INDEX `app_resource_uk1` (`app_id`, `name`),
    CONSTRAINT `app_resource_fk1` FOREIGN KEY (`app_id`) REFERENCES `app` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `app_resource_file` (
    `id`     BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `app_id` BIGINT(20) UNSIGNED NOT NULL,
    `name`   VARCHAR(255) NOT NULL,
    INDEX `app_resource_file_fk1` (`app_id`),
    UNIQUE INDEX `app_resource_file_uk1` (`app_id`, `name`),
    CONSTRAINT `app_resource_file_fk1` FOREIGN KEY (`app_id`) REFERENCES `app` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `resource_type` (
    `id`        BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name`      VARCHAR(255) NOT NULL,
    `node_name` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `resource_type` (`id`, `name`, `node_name`) VALUES (1, 'String', 'string');

CREATE TABLE `resource_file_entry` (
    `id`                   BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `app_resource_file_id` BIGINT(20) UNSIGNED NOT NULL,
    `resource_type_id`     BIGINT(20) UNSIGNED NOT NULL,
    `name`                 VARCHAR(255) NOT NULL,
    `product`              VARCHAR(255) NOT NULL,
    `description`          VARCHAR(4096) DEFAULT NULL,
    `deleted`              TINYINT(1) NOT NULL,
    `translatable`         TINYINT(1) NOT NULL,
    INDEX `resource_file_entry_fk1` (`app_resource_file_id`),
    INDEX `resource_file_entry_fk2` (`resource_type_id`),
    INDEX `resource_file_entry_ik1` (`deleted`),
    INDEX `resource_file_entry_ik2` (`translatable`),
    CONSTRAINT `resource_file_entry_fk1` FOREIGN KEY (`app_resource_file_id`) REFERENCES `app_resource_file` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `resource_file_entry_fk2` FOREIGN KEY (`resource_type_id`) REFERENCES `resource_type` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `entry_common` (
    `id`                     BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `app_resource_id`        BIGINT(20) UNSIGNED NOT NULL,
    `resource_file_entry_id` BIGINT(20) UNSIGNED NOT NULL,
    `last_change`            BIGINT(20) NOT NULL,
    INDEX `entry_common_ik1` (`last_change`),
    INDEX `entry_common_fk1` (`app_resource_id`),
    INDEX `entry_common_fk2` (`resource_file_entry_id`),
    CONSTRAINT `entry_common_fk1` FOREIGN KEY (`app_resource_id`) REFERENCES `app_resource` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `entry_common_fk2` FOREIGN KEY (`resource_file_entry_id`) REFERENCES `resource_file_entry` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `entry_string` (
    `entry_common_id` BIGINT(20) UNSIGNED NOT NULL UNIQUE,
    `value`           VARCHAR(20480) NOT NULL,
    INDEX `entry_string_fk1` (`entry_common_id`),
    CONSTRAINT `entry_string_fk1` FOREIGN KEY (`entry_common_id`) REFERENCES `entry_common` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `suggestion` (
    `id`              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `entry_common_id` BIGINT(20) UNSIGNED NOT NULL,
    `user_id`         BIGINT(20) NOT NULL,
    `last_change`     BIGINT(20) NOT NULL,
    INDEX `suggestion_ik1` (`last_change`),
    INDEX `suggestion_fk1` (`entry_common_id`),
    INDEX `suggestion_fk2` (`user_id`),
    CONSTRAINT `suggestion_fk1` FOREIGN KEY (`entry_common_id`) REFERENCES `entry_common` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `suggestion_fk2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `suggestion_string` (
    `suggestion_id` BIGINT(20) UNSIGNED NOT NULL UNIQUE,
    `value`         VARCHAR(20480) NOT NULL,
    INDEX `suggestion_string_fk1` (`suggestion_id`),
    CONSTRAINT `suggestion_string_fk1` FOREIGN KEY (`suggestion_id`) REFERENCES `suggestion` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `suggestion_vote` (
    `suggestion_id` BIGINT(20) UNSIGNED NOT NULL,
    `user_id`       BIGINT(20) NOT NULL,
    PRIMARY KEY (`suggestion_id`,`user_id`),
    INDEX `suggestion_vote_fk1` (`suggestion_id`),
    INDEX `suggestion_vote_fk2` (`user_id`),
    CONSTRAINT `suggestion_vote_fk1` FOREIGN KEY (`suggestion_id`) REFERENCES `suggestion` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `suggestion_vote_fk2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
