CREATE TABLE "log" (
    "id"               BIGSERIAL PRIMARY KEY,
    "timestamp"        VARCHAR(25) NOT NULL,
    "priority"         SMALLINT NOT NULL,
    "priority_name"    VARCHAR(10) NOT NULL,
    "message"          VARCHAR(4096) NOT NULL,
    "message_extended" TEXT DEFAULT NULL,
    "file"             VARCHAR(1024) DEFAULT NULL,
    "class"            VARCHAR(1024) DEFAULT NULL,
    "line"             BIGINT DEFAULT NULL,
    "function"         VARCHAR(1024) DEFAULT NULL
);

CREATE INDEX "log_ik1" ON "log" ("priority");
CREATE INDEX "log_ik2" ON "log" ("class");
CREATE INDEX "log_ik3" ON "log" ("function");

CREATE TABLE "user" (
    "user_id"      BIGSERIAL PRIMARY KEY,
    "username"     VARCHAR(255) DEFAULT NULL UNIQUE,
    "email"        VARCHAR(255) DEFAULT NULL UNIQUE,
    "display_name" VARCHAR(50) DEFAULT NULL,
    "password"     VARCHAR(128) NOT NULL,
    "state"        SMALLINT DEFAULT NULL
);
