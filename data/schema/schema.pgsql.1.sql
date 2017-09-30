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
    "username"     VARCHAR(255) DEFAULT NULL UNIQUE,
    "email"        VARCHAR(255) DEFAULT NULL UNIQUE,
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
    "locale" VARCHAR(20) NOT NULL, -- Currently known max length is 11 char.
    CONSTRAINT "user_settings_pk" PRIMARY KEY ("user_id"),
    CONSTRAINT "user_settings_fk1" FOREIGN KEY ("user_id") REFERENCES "user" ("user_id") ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE "user_languages" ( 
    "user_id" BIGINT NOT NULL,
    "locale" VARCHAR(20) NOT NULL, -- Currently known max length for primary locale is 3 char.
    CONSTRAINT "user_languages_pk" PRIMARY KEY ("user_id", "locale"),
    CONSTRAINT "user_languages_fk1" FOREIGN KEY ("user_id") REFERENCES "user" ("user_id") ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE INDEX "user_languages_ik1" ON "user_languages" ("user_id");

CREATE TABLE "team" (
    "id" BIGSERIAL,
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
