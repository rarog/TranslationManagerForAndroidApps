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
    "username"     TEXT DEFAULT NULL,
    "email"        TEXT DEFAULT NULL,
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

CREATE TABLE "user_settings" (
    "user_id" INTEGER NOT NULL,
    "locale" TEXT NOT NULL, -- Currently known max length is 11 char.
    CONSTRAINT "user_settings_pk" PRIMARY KEY ("user_id"),
    CONSTRAINT "user_settings_fk1" FOREIGN KEY ("user_id") REFERENCES "user" ("user_id") ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE "user_languages" (
    "user_id" INTEGER NOT NULL,
    "locale"  TEXT NOT NULL, -- Currently known max length for primary locale is 3 char.
    CONSTRAINT "user_languages_pk" PRIMARY KEY ("user_id", "locale"),
    CONSTRAINT "user_languages_fk1" FOREIGN KEY ("user_id") REFERENCES "user" ("user_id") ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE INDEX "user_languages_ik1" ON "user_languages" ("user_id");

CREATE TABLE "team" (
    "id"   INTEGER NOT NULL,
    "name" TEXT DEFAULT NULL,
    CONSTRAINT "team_pk" PRIMARY KEY ("id")
);

CREATE TABLE "team_member" (
    "user_id" INTEGER NOT NULL,
    "team_id" INTEGER NOT NULL,
    CONSTRAINT "team_member_pk" PRIMARY KEY ("user_id", "team_id"),
    CONSTRAINT "team_member_fk1" FOREIGN KEY ("user_id") REFERENCES "user" ("user_id") ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT "team_member_fk2" FOREIGN KEY ("team_id") REFERENCES "team" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE INDEX "team_member_ik1" ON "team_member" ("user_id");
CREATE INDEX "team_member_ik2" ON "team_member" ("team_id");

CREATE TABLE "app" (
    "id"                 INTEGER NOT NULL,
    "team_id"            INTEGER DEFAULT NULL,
    "name"               TEXT DEFAULT NULL,
    "path_to_res_folder" TEXT DEFAULT NULL,
    "git_repository"     TEXT DEFAULT NULL,
    "git_username"       TEXT DEFAULT NULL,
    "git_password"       TEXT DEFAULT NULL,
    "git_user"           TEXT DEFAULT NULL,
    "git_email"          TEXT DEFAULT NULL,
    CONSTRAINT "app_pk" PRIMARY KEY ("id"),
    CONSTRAINT "app_fk1" FOREIGN KEY ("team_id") REFERENCES "team" ("id") ON DELETE SET NULL ON UPDATE CASCADE
);
CREATE INDEX "app_ik1" ON "app" ("team_id");

CREATE TABLE "app_resource" (
    "id"             INTEGER NOT NULL,
    "app_id"         INTEGER NOT NULL,
    "name"           TEXT NOT NULL,
    "locale"         TEXT NOT NULL,
    "primary_locale" TEXT NOT NULL, -- Currently known max length for primary locale is 3 char. Field isn't available in model.
    "description"    TEXT DEFAULT NULL,
    CONSTRAINT "app_resource_pk" PRIMARY KEY ("id"),
    CONSTRAINT "app_resource_fk1" FOREIGN KEY ("app_id") REFERENCES "app" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT "app_resource_uk1" UNIQUE ("app_id", "name")
);
CREATE INDEX "app_resource_ik1" ON "app_resource" ("app_id");
CREATE INDEX "app_resource_ik2" ON "app_resource" ("primary_locale");

CREATE TABLE "app_resource_file" (
    "id"     INTEGER NOT NULL,
    "app_id" INTEGER NOT NULL,
    "name"   TEXT NOT NULL,
    CONSTRAINT "app_resource_file_pk" PRIMARY KEY ("id"),
    CONSTRAINT "app_resource_file_fk1" FOREIGN KEY ("app_id") REFERENCES "app" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT "app_resource_file_uk1" UNIQUE ("app_id", "name")
);
CREATE INDEX "app_resource_file_ik1" ON "app_resource_file" ("app_id");

CREATE TABLE "resource_type" (
    "id"        INTEGER NOT NULL,
    "name"      TEXT NOT NULL,
    "node_name" TEXT NOT NULL,
    CONSTRAINT "resource_type_pk" PRIMARY KEY ("id")
);
CREATE INDEX "resource_type_ik1" ON "resource_type" ("name");
CREATE INDEX "resource_type_ik2" ON "resource_type" ("node_name");
INSERT INTO "resource_type" ("id", "name", "node_name") VALUES (1, 'String', 'string');

CREATE TABLE "resource_file_entry" (
    "id"                   INTEGER NOT NULL,
    "app_resource_file_id" INTEGER NOT NULL,
    "resource_type_id"     INTEGER NOT NULL,
    "name"                 TEXT NOT NULL,
    "product"              TEXT NOT NULL,
    "description"          TEXT DEFAULT NULL,
    "deleted"              INTEGER NOT NULL,
    "translatable"         INTEGER NOT NULL,
    CONSTRAINT "resource_file_entry_pk" PRIMARY KEY ("id"),
    CONSTRAINT "resource_file_entry_fk1" FOREIGN KEY ("app_resource_file_id") REFERENCES "app_resource_file" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT "resource_file_entry_fk2" FOREIGN KEY ("resource_type_id") REFERENCES "resource_type" ("id") ON DELETE RESTRICT ON UPDATE CASCADE
);
CREATE INDEX "resource_file_entry_ik1" ON "resource_file_entry" ("app_resource_file_id");
CREATE INDEX "resource_file_entry_ik2" ON "resource_file_entry" ("resource_type_id");
CREATE INDEX "resource_file_entry_ik3" ON "resource_file_entry" ("deleted");
CREATE INDEX "resource_file_entry_ik4" ON "resource_file_entry" ("translatable");

CREATE TABLE "entry_common" (
    "id"                     INTEGER NOT NULL,
    "app_resource_id"        INTEGER NOT NULL,
    "resource_file_entry_id" INTEGER NOT NULL,
    "last_change"            INTEGER NOT NULL,
    CONSTRAINT "entry_common_pk" PRIMARY KEY ("id"),
    CONSTRAINT "entry_common_fk1" FOREIGN KEY ("app_resource_id") REFERENCES "app_resource" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT "entry_common_fk2" FOREIGN KEY ("resource_file_entry_id") REFERENCES "resource_file_entry" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE INDEX "entry_common_ik1" ON "entry_common" ("app_resource_id");
CREATE INDEX "entry_common_ik2" ON "entry_common" ("resource_file_entry_id");
CREATE INDEX "entry_common_ik3" ON "entry_common" ("last_change");

CREATE TABLE "entry_string" (
    "entry_common_id" INTEGER NOT NULL,
    "value"           TEXT NOT NULL,
    CONSTRAINT "entry_string_pk" PRIMARY KEY ("entry_common_id"),
    CONSTRAINT "entry_string_fk1" FOREIGN KEY ("entry_common_id") REFERENCES "entry_common" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE "suggestion" (
    "id"              INTEGER NOT NULL,
    "entry_common_id" INTEGER NOT NULL,
    "user_id"         INTEGER NOT NULL,
    "last_change"     INTEGER NOT NULL,
    CONSTRAINT "suggestion_pk" PRIMARY KEY ("id"),
    CONSTRAINT "suggestion_fk1" FOREIGN KEY ("entry_common_id") REFERENCES "entry_common" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT "suggestion_fk2" FOREIGN KEY ("user_id") REFERENCES "user" ("user_id") ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE INDEX "suggestion_ik1" ON "suggestion" ("entry_common_id");
CREATE INDEX "suggestion_ik2" ON "suggestion" ("user_id");
CREATE INDEX "suggestion_ik3" ON "suggestion" ("last_change");

CREATE TABLE "suggestion_string" (
    "suggestion_id" INTEGER NOT NULL,
    "value"         TEXT NOT NULL,
    CONSTRAINT "suggestion_string_pk" PRIMARY KEY ("suggestion_id"),
    CONSTRAINT "suggestion_string_fk1" FOREIGN KEY ("suggestion_id") REFERENCES "suggestion" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE "suggestion_vote" (
    "suggestion_id" INTEGER NOT NULL,
    "user_id"       INTEGER NOT NULL,
    CONSTRAINT "suggestion_vote_pk" PRIMARY KEY ("suggestion_id", "user_id"),
    CONSTRAINT "suggestion_vote_fk1" FOREIGN KEY ("suggestion_id") REFERENCES "suggestion" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT "suggestion_vote_fk2" FOREIGN KEY ("user_id") REFERENCES "user" ("user_id") ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE INDEX "suggestion_vote_ik1" ON "suggestion_vote" ("suggestion_id");
CREATE INDEX "suggestion_vote_ik2" ON "suggestion_vote" ("user_id");
