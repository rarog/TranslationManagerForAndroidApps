CREATE TABLE `user` (
  `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` varchar(255) DEFAULT NULL UNIQUE,
  `email` varchar(255) DEFAULT NULL UNIQUE,
  `display_name` varchar(50) DEFAULT NULL,
  `password` varchar(128) NOT NULL,
  `state` smallint(5) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_role_linker` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `role_id` varchar(45) NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `user_role_linker_fk1_idx` (`user_id`),
  CONSTRAINT `user_role_linker_fk1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
