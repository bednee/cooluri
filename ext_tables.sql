#
# Table structure for table "pages"
#
CREATE TABLE pages (
	tx_realurl_pathsegment varchar(255) default '',
	tx_cooluri_exclude tinyint(1) unsigned default '0',
	tx_cooluri_excludealways tinyint(1) unsigned default '0'
);

#
# Table structure for table "pages_language_overlay"
#
CREATE TABLE pages_language_overlay (
	tx_realurl_pathsegment varchar(255) DEFAULT '' NOT NULL
);


CREATE TABLE link_cache (
	id int(10) unsigned NOT NULL auto_increment,
	params blob,
	url char(255),
	tstamp TIMESTAMP default CURRENT_TIMESTAMP,
	crdatetime datetime default NULL,
	sticky tinyint(1) unsigned default 0,

	PRIMARY KEY (id),
	KEY url (url(255)),
	KEY params (params(255))
);

CREATE TABLE link_oldlinks (
	id int(10) unsigned NOT NULL auto_increment,
	link_id int(10) unsigned NOT NULL default 0,
	url char(255),
	tstamp timestamp NOT NULL default CURRENT_TIMESTAMP,
	sticky tinyint(1) unsigned default 0,

	PRIMARY KEY (id),
	UNIQUE KEY id (id),
	KEY url (url(255))
);
