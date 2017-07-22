CREATE TABLE user
(
    user_id      INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    username     VARCHAR(255) DEFAULT NULL UNIQUE,
    email        VARCHAR(255) DEFAULT NULL UNIQUE,
    display_name VARCHAR(50) DEFAULT NULL,
    password     VARCHAR(128) NOT NULL,
    state        SMALLINT DEFAULT NULL
);

CREATE TABLE user_role_linker
(
    user_id INTEGER NOT NULL,
    role_id VARCHAR(55) NOT NULL,
    PRIMARY KEY (user_id,role_id),
    FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE team (
    id   INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) DEFAULT NULL
);

CREATE TABLE user_settings (
    user_id INTEGER NOT NULL PRIMARY KEY,
    locale  VARCHAR(20) NOT NULL, -- Currently known max length is 11 char.
    team_id INTEGER DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE user_languages (
    user_id INTEGER NOT NULL,
    locale  VARCHAR(20) NOT NULL, -- Currently known max length for primary locale is 3 char.
    PRIMARY KEY (user_id,locale),
    FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE team_member (
    user_id INTEGER NOT NULL,
    team_id INTEGER NOT NULL,
    PRIMARY KEY (user_id,team_id),
    FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE app (
    id                 INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    team_id            INTEGER DEFAULT NULL,
    name               VARCHAR(255) DEFAULT NULL,
    path_to_res_folder VARCHAR(4096) DEFAULT NULL,
    git_repository     VARCHAR(4096) DEFAULT NULL,
    git_username       VARCHAR(255) DEFAULT NULL,
    git_password       VARCHAR(1024) DEFAULT NULL,
    git_user           VARCHAR(255) DEFAULT NULL,
    git_email          VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE app_resource (
    id             INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    app_id         INTEGER NOT NULL,
    name           VARCHAR(255) NOT NULL,
    locale         VARCHAR(20) NOT NULL,
    primary_locale VARCHAR(20) NOT NULL, -- Currently known max length for primary locale is 3 char. Field isn't available in model.
    description    VARCHAR(255) DEFAULT NULL,
    UNIQUE (app_id,name),
    FOREIGN KEY (app_id) REFERENCES app (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE INDEX app_resource_ik1 ON app_resource (primary_locale);

CREATE TABLE app_resource_file (
    id     INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    app_id INTEGER NOT NULL,
    name   VARCHAR(255) NOT NULL,
    UNIQUE (app_id,name),
    FOREIGN KEY (app_id) REFERENCES app (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE resource_type (
    id        INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    name      VARCHAR(255) NOT NULL,
    node_name VARCHAR(255) NOT NULL
);

INSERT INTO resource_type (id, name, node_name) VALUES (1, 'String', 'string');

CREATE TABLE resource_file_entry (
    id                   INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    app_resource_file_id INTEGER DEFAULT NULL,
    resource_type_id     INTEGER NOT NULL,
    name                 VARCHAR(255) NOT NULL,
    deleted              INTEGER NOT NULL,
    FOREIGN KEY (app_resource_file_id) REFERENCES app_resource_file (id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (resource_type_id) REFERENCES resource_type (id) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE INDEX resource_file_entry_ik1 ON resource_file_entry (deleted);

CREATE TRIGGER resource_file_id_becomes_null AFTER UPDATE ON resource_file_entry
FOR EACH ROW
WHEN (NEW.app_resource_file_id IS NULL)
BEGIN
    UPDATE resource_file_entry SET deleted = 1 WHERE id = NEW.id;
END;

CREATE TABLE resource_file_entry_string (
    id                     INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    app_resource_id        INTEGER NOT NULL,
    resource_file_entry_id INTEGER NOT NULL,
    value                  VARCHAR(20480) NOT NULL,
    FOREIGN KEY (app_resource_id) REFERENCES app_resource (id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (resource_file_entry_id) REFERENCES resource_file_entry (id) ON DELETE CASCADE ON UPDATE CASCADE
);
