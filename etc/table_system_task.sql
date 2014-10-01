CREATE TABLE `control_new`.`system_task` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(64) NULL,
  `data` TEXT NULL COMMENT 'json array',
  `start` INT(10) NULL COMMENT 'unix timestamp',
  `end` INT(10) NULL COMMENT 'unix timestamp',
  `interval` INT(10) NULL COMMENT 'number of seconds',
  `exitcode` SMALLINT(3) NULL COMMENT 'last exit code',
  PRIMARY KEY (`id`));
 
