CREATE TABLE `log` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NULL DEFAULT NULL,
	`message` VARCHAR(255) NOT NULL,
	`data` TEXT NULL,
	`created_at` TIMESTAMP NULL DEFAULT NULL,
	`updated_at` TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `fk_log_user_id_user_id` (`user_id`),
	CONSTRAINT `fk_log_user_id_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

DELETE FROM menuitem WHERE name = 'Systeemopdrachten';

INSERT INTO `menuitem` (`id`, `parent`, `name`, `url`, `gid_access`, `order`, `help`) VALUES
	(44, 47, 'Systeemopdrachten', '/staff/system/systemtask', 1050, 6, NULL),
	(47, 5, 'Systeem', NULL, 1050, 6, NULL),
	(48, 47, 'Logs', '/staff/system/log', 1050, 7, NULL);
