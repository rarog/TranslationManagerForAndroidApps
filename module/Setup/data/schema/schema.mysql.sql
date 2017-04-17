CREATE TABLE `database_schema_version`
(
    `version`    INT UNSIGNED NOT NULL PRIMARY KEY,
    `timestamp`  INT NOT NULL
) ENGINE=InnoDB CHARSET="utf8";