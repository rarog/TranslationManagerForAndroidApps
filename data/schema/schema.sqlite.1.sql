CREATE TABLE "log" (
    "id"               INTEGER NOT NULL,
    "timestamp"        TEXT NOT NULL,
    "priority"         INTEGER NOT NULL,
    "priority_name"    TEXT NOT NULL,
    "message"          TEXT NOT NULL,
    "message_extended" TEXT DEFAULT NULL,
    "file"             TEXT DEFAULT NULL,
    "class"            TEXT DEFAULT NULL,
    "line"             INTEGER DEFAULT NULL,
    "function"         TEXT DEFAULT NULL,
    CONSTRAINT "log_pk" PRIMARY KEY ("id")
);

CREATE INDEX "log_ik1" ON "log" ("priority");
CREATE INDEX "log_ik2" ON "log" ("class");
CREATE INDEX "log_ik3" ON "log" ("function");

CREATE TABLE "user"
(
    "user_id"      INTEGER NOT NULL,
    "username"     TEXT DEFAULT NULL UNIQUE,
    "email"        TEXT DEFAULT NULL UNIQUE,
    "display_name" TEXT DEFAULT NULL,
    "password"     TEXT NOT NULL,
    "state"        INTEGER DEFAULT NULL,
    CONSTRAINT "user_pk" PRIMARY KEY ("user_id"),
    CONSTRAINT "user_uk1" UNIQUE ("username"),
    CONSTRAINT "user_uk2" UNIQUE ("email")
);

CREATE TABLE "user_role_linker"
(
    "user_id" INTEGER NOT NULL,
    "role_id" TEXT NOT NULL,
    CONSTRAINT "user_role_linker_pk" PRIMARY KEY ("user_id", "role_id"),
    CONSTRAINT "user_role_linker_fk1" FOREIGN KEY ("user_id") REFERENCES "user" ("user_id") ON DELETE CASCADE ON UPDATE CASCADE 
);

CREATE INDEX "user_role_linker_ik1" ON "user_role_linker" ("user_id");

CREATE TABLE team (
    id   INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    name TEXT DEFAULT NULL
);

CREATE TABLE user_settings (
    user_id INTEGER NOT NULL PRIMARY KEY,
    locale  TEXT NOT NULL, -- Currently known max length is 11 char.
    team_id INTEGER DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE user_languages (
    user_id INTEGER NOT NULL,
    locale  TEXT NOT NULL, -- Currently known max length for primary locale is 3 char.
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
    name               TEXT DEFAULT NULL,
    path_to_res_folder TEXT DEFAULT NULL,
    git_repository     TEXT DEFAULT NULL,
    git_username       TEXT DEFAULT NULL,
    git_password       TEXT DEFAULT NULL,
    git_user           TEXT DEFAULT NULL,
    git_email          TEXT DEFAULT NULL,
    FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE app_resource (
    id             INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    app_id         INTEGER NOT NULL,
    name           TEXT NOT NULL,
    locale         TEXT NOT NULL,
    primary_locale TEXT NOT NULL,
    description    TEXT DEFAULT NULL,
    UNIQUE (app_id,name),
    FOREIGN KEY (app_id) REFERENCES app (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE INDEX app_resource_ik1 ON app_resource (primary_locale);

CREATE TABLE app_resource_file (
    id     INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    app_id INTEGER NOT NULL,
    name   TEXT NOT NULL,
    UNIQUE (app_id,name),
    FOREIGN KEY (app_id) REFERENCES app (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE resource_type (
    id        INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    name      TEXT NOT NULL,
    node_name TEXT NOT NULL
);

INSERT INTO resource_type (id, name, node_name) VALUES (1, 'String', 'string');

CREATE TABLE resource_file_entry (
    id                   INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    app_resource_file_id INTEGER NOT NULL,
    resource_type_id     INTEGER NOT NULL,
    name                 TEXT NOT NULL,
    product              TEXT NOT NULL,
    description          TEXT DEFAULT NULL,
    deleted              INTEGER NOT NULL,
    translatable         INTEGER NOT NULL,
    FOREIGN KEY (app_resource_file_id) REFERENCES app_resource_file (id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (resource_type_id) REFERENCES resource_type (id) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE INDEX resource_file_entry_ik1 ON resource_file_entry (deleted);
CREATE INDEX resource_file_entry_ik2 ON resource_file_entry (translatable);

CREATE TABLE entry_common (
    id                     INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    app_resource_id        INTEGER NOT NULL,
    resource_file_entry_id INTEGER NOT NULL,
    last_change            INTEGER NOT NULL,
    FOREIGN KEY (app_resource_id) REFERENCES app_resource (id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (resource_file_entry_id) REFERENCES resource_file_entry (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE INDEX entry_common_ik1 ON entry_common (last_change);

CREATE TABLE entry_string (
    entry_common_id INTEGER NOT NULL UNIQUE,
    value           TEXT NOT NULL,
    FOREIGN KEY (entry_common_id) REFERENCES entry_common (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE suggestion (
    id              INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    entry_common_id INTEGER NOT NULL,
    user_id         INTEGER NOT NULL,
    last_change     INTEGER NOT NULL,
    FOREIGN KEY (entry_common_id) REFERENCES entry_common (id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE INDEX suggestion_ik1 ON suggestion (last_change);

CREATE TABLE suggestion_string (
    suggestion_id INTEGER NOT NULL UNIQUE,
    value         TEXT NOT NULL,
    FOREIGN KEY (suggestion_id) REFERENCES suggestion (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE suggestion_vote (
    suggestion_id INTEGER NOT NULL,
    user_id       INTEGER NOT NULL,
    PRIMARY KEY (suggestion_id,user_id),
    CONSTRAINT suggestion_vote_fk1 FOREIGN KEY (suggestion_id) REFERENCES suggestion (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT suggestion_vote_fk2 FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE INDEX suggestion_vote_fk1 ON suggestion (suggestion_id);
CREATE INDEX suggestion_vote_fk2 ON suggestion (user_id);
