DROP TABLE IF EXISTS `psm_jobs`;
CREATE TABLE `psm_jobs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `handler` text NOT NULL,
  `queue` varchar(255) NOT NULL DEFAULT 'default',
  `attempts` int(10) unsigned NOT NULL DEFAULT '0',
  `run_at` datetime DEFAULT NULL,
  `locked_at` datetime DEFAULT NULL,
  `schedule` varchar(25) DEFAULT NULL,
  `locked_by` varchar(255) DEFAULT NULL,
  `failed_at` datetime DEFAULT NULL,
  `error` text,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8219 DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;
