ALTER TABLE "user_role_linker" ALTER COLUMN "role_id" TYPE VARCHAR(255);

ALTER TABLE "entry_common" ADD COLUMN "notification_status" INT NOT NULL;
