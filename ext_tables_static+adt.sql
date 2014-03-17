
#
# Table structure for table 'link_cache'
DROP TABLE IF EXISTS link_cache;
CREATE TABLE `link_cache` (
	`id` int(10) unsigned NOT NULL auto_increment,
	`params` blob,
	`url` char(255),
	`tstamp` TIMESTAMP default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
	`crdatetime` datetime default NULL,
	`sticky` tinyint(1) unsigned default 0,

	PRIMARY KEY (`id`),
	KEY `url` (`url`(255)),
	KEY `params` (`params`(255))
) ENGINE = MyISAM;

#
# Table structure for table 'link_oldlinks'
DROP TABLE IF EXISTS link_oldlinks;
CREATE TABLE `link_oldlinks` (
	`id` int(10) unsigned NOT NULL auto_increment,
	`link_id` int(10) unsigned NOT NULL default 0,
	`url` char(255),
	`tstamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,

	PRIMARY KEY (`id`),
	UNIQUE KEY `id` (`id`),
	KEY `url` (`url`(255))
) ENGINE = MyISAM;
