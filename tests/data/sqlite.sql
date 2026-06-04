DROP TABLE IF EXISTS "product";

CREATE TABLE "product"
(
	"id"         INTEGER PRIMARY KEY AUTOINCREMENT,
	"name"       varchar(255) NOT NULL,
	"picture_id" bigint DEFAULT NULL,
	"manual_id"  bigint DEFAULT NULL
);

CREATE INDEX "idx_product_picture_id" ON "product" ("picture_id");
CREATE INDEX "idx_product_manual_id" ON "product" ("manual_id");
