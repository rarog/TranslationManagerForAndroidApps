ALTER TABLE "user_role_linker" ALTER COLUMN "role_id" TYPE VARCHAR(255);

ALTER TABLE "entry_common" ADD COLUMN "notification_status" INT NOT NULL;

CREATE TABLE "setting" (
    "id"    BIGSERIAL,
    "path"  VARCHAR(255) NOT NULL,
    "value" TEXT DEFAULT NULL,
    CONSTRAINT "setting_pk" PRIMARY KEY ("id"),
    CONSTRAINT "setting_uk1" UNIQUE ("path")
);
