CREATE TABLE "log" (
    "id"               BIGSERIAL,
    "timestamp"        VARCHAR(25) NOT NULL,
    "priority"         SMALLINT NOT NULL,
    "priority_name"    VARCHAR(10) NOT NULL,
    "message"          VARCHAR(4096) NOT NULL,
    "message_extended" TEXT DEFAULT NULL,
    "file"             VARCHAR(1024) DEFAULT NULL,
    "class"            VARCHAR(1024) DEFAULT NULL,
    "line"             BIGINT DEFAULT NULL,
    "function"         VARCHAR(1024) DEFAULT NULL,
    CONSTRAINT "log_pk" PRIMARY KEY ("id")
);
CREATE INDEX "log_ik1" ON "log" ("priority");
CREATE INDEX "log_ik2" ON "log" ("class");
CREATE INDEX "log_ik3" ON "log" ("function");

CREATE TABLE "user" (
    "user_id"      BIGSERIAL,
    "username"     VARCHAR(255) DEFAULT NULL,
    "email"        VARCHAR(255) DEFAULT NULL,
    "display_name" VARCHAR(50) DEFAULT NULL,
    "password"     VARCHAR(128) NOT NULL,
    "state"        SMALLINT DEFAULT NULL,
    CONSTRAINT "user_pk" PRIMARY KEY ("user_id"),
    CONSTRAINT "user_uk1" UNIQUE ("username"),
    CONSTRAINT "user_uk2" UNIQUE ("email")
);

CREATE TABLE "user_role_linker" (
    "user_id" BIGINT NOT NULL,
    "role_id" VARCHAR(45) NOT NULL,
    CONSTRAINT "user_role_linker_pk" PRIMARY KEY ("user_id", "role_id"),
    CONSTRAINT "user_role_linker_fk1" FOREIGN KEY ("user_id") REFERENCES "user" ("user_id") ON DELETE CASCADE ON UPDATE CASCADE 
);
CREATE INDEX "user_role_linker_ik1" ON "user_role_linker" ("user_id");

CREATE TABLE "user_settings" (
    "user_id" BIGINT NOT NULL,
    "locale"  VARCHAR(20) NOT NULL, -- Currently known max length is 11 char.
    CONSTRAINT "user_settings_pk" PRIMARY KEY ("user_id"),
    CONSTRAINT "user_settings_fk1" FOREIGN KEY ("user_id") REFERENCES "user" ("user_id") ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE "user_languages" ( 
    "user_id" BIGINT NOT NULL,
    "locale"  VARCHAR(20) NOT NULL, -- Currently known max length for primary locale is 3 char.
    CONSTRAINT "user_languages_pk" PRIMARY KEY ("user_id", "locale"),
    CONSTRAINT "user_languages_fk1" FOREIGN KEY ("user_id") REFERENCES "user" ("user_id") ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE INDEX "user_languages_ik1" ON "user_languages" ("user_id");

CREATE TABLE "team" (
    "id"     BIGSERIAL,
    "locale" VARCHAR(255),
    CONSTRAINT "team_pk" PRIMARY KEY ("id")
);

CREATE TABLE "team_member" (
    "user_id" BIGINT NOT NULL,
    "team_id" BIGINT NOT NULL,
    CONSTRAINT "team_member_pk" PRIMARY KEY ("user_id", "team_id"),
    CONSTRAINT "team_member_fk1" FOREIGN KEY ("user_id") REFERENCES "user" ("user_id") ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT "team_member_fk2" FOREIGN KEY ("team_id") REFERENCES "team" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE INDEX "team_member_ik1" ON "team_member" ("user_id");
CREATE INDEX "team_member_ik2" ON "team_member" ("team_id");

CREATE TABLE "app" (
    "id"                 BIGSERIAL,
    "team_id"            BIGINT,
    "name"               VARCHAR(255),
    "path_to_res_folder" VARCHAR(4096),
    "git_repository"     VARCHAR(4096),
    "git_username"       VARCHAR(255),
    "git_password"       VARCHAR(1024),
    "git_user"           VARCHAR(255),
    "git_email"          VARCHAR(255),
    CONSTRAINT "app_pk" PRIMARY KEY ("id"),
    CONSTRAINT "app_fk1" FOREIGN KEY ("team_id") REFERENCES "team" ("id") ON DELETE SET NULL ON UPDATE CASCADE
);
CREATE INDEX "app_ik1" ON "app" ("team_id");

CREATE TABLE "app_resource" (
    "id"             BIGSERIAL,
    "app_id"         BIGINT NOT NULL,
    "name"           VARCHAR(255) NOT NULL,
    "locale"         VARCHAR(20) NOT NULL,
    "primary_locale" VARCHAR(20) NOT NULL, -- Currently known max length for primary locale is 3 char. Field isn't available in model.
    "description"    VARCHAR(255),
    CONSTRAINT "app_resource_pk" PRIMARY KEY ("id"),
    CONSTRAINT "app_resource_fk1" FOREIGN KEY ("app_id") REFERENCES "app" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT "app_resource_uk1" UNIQUE ("app_id", "name")
);
CREATE INDEX "app_resource_ik1" ON "app_resource" ("app_id");
CREATE INDEX "app_resource_ik2" ON "app_resource" ("primary_locale");

CREATE TABLE "app_resource_file" (
    "id" BIGINT NOT NULL,
    "app_id" BIGINT NOT NULL,
    "name" VARCHAR(255) NOT NULL,
    CONSTRAINT "app_resource_file_pk" PRIMARY KEY ("id"),
    CONSTRAINT "app_resource_file_fk1" FOREIGN KEY ("app_id") REFERENCES "app" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT "app_resource_file_uk1" UNIQUE ("app_id", "name")
);
CREATE INDEX "app_resource_file_ik1" ON "app_resource_file" ("app_id");
