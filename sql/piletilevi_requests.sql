CREATE TABLE IF NOT EXISTS `request` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `stat` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` tinyint(3) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `duration` smallint(5) unsigned NOT NULL,
  `status` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
