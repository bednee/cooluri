#
# Modifying pages table
#
CREATE TABLE pages (
	tx_realurl_pathsegment varchar(30) default '',
	tx_cooluri_exclude tinyint(1) unsigned default '0',
	tx_cooluri_excludealways tinyint(1) unsigned default '0'
);

#
# Modifying pages_language_overlay table
#
CREATE TABLE pages_language_overlay (
	tx_realurl_pathsegment varchar(255) DEFAULT '' NOT NULL
);