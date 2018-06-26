ALTER TABLE "entry_common" ADD COLUMN "notification_status" INTEGER NOT NULL;

CREATE TABLE "setting" (
    "id"    INTEGER NOT NULL,
    "path"  VARCHAR(255) NOT NULL,
    "value" TEXT DEFAULT NULL,
    CONSTRAINT "setting_pk" PRIMARY KEY ("id"),
    CONSTRAINT "setting_uk1" UNIQUE ("path")
);
