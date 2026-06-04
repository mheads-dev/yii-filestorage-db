/**
 * This is the database schema for testing MySQL support of Yii DAO and Active Record.
 * The database setup in config.php is required to perform then relevant tests:
 */

DROP TABLE IF EXISTS `product` CASCADE;

CREATE TABLE `product`
(
	`id`         int unsigned NOT NULL AUTO_INCREMENT,
	`name`       varchar(255) NOT NULL,
	`picture_id` bigint unsigned DEFAULT NULL,
	`manual_id`  bigint unsigned DEFAULT NULL,
	PRIMARY KEY (`id`),
	KEY          `idx_product_picture_id` (`picture_id`),
	KEY          `idx_product_manual_id` (`manual_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
