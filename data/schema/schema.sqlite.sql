CREATE TABLE user
(
    user_id      INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    username     VARCHAR(255) DEFAULT NULL UNIQUE,
    email        VARCHAR(255) DEFAULT NULL UNIQUE,
    display_name VARCHAR(50) DEFAULT NULL,
    password     VARCHAR(128) NOT NULL,
    state        SMALLINT
);

CREATE TABLE user_role_linker
(
    user_id INTEGER NOT NULL,
    role_id VARCHAR(55) NOT NULL,
    PRIMARY KEY(user_id,role_id),
    FOREIGN KEY(user_id) REFERENCES user(user_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE team (
    id   INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) DEFAULT NULL
);

CREATE TABLE app (
    id                 INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    team_id            INTEGER NOT NULL,
    name               VARCHAR(255) DEFAULT NULL,
    git_repository     VARCHAR(4096) DEFAULT NULL,
    path_to_res_folder VARCHAR(4096) DEFAULT NULL,
    FOREIGN KEY(team_id) REFERENCES team(id) ON DELETE CASCADE ON UPDATE CASCADE
);
