#
# Table structure for table "pages"
#
CREATE TABLE pages (
	tx_realurl_pathsegment varchar(30) default '',
	tx_cooluri_exclude tinyint(1) unsigned default '0',
	tx_cooluri_excludealways tinyint(1) unsigned default '0'
);

#
# Table structure for table "pages_language_overlay"
#
CREATE TABLE pages_language_overlay (
	tx_realurl_pathsegment varchar(255) default ''
);

#
# Table structure for table "link_cache"
#
CREATE TABLE link_cache (
	id int(10) unsigned NOT NULL auto_increment,
	params blob,
	url char(255) default '',
	tstamp timestamp NOT NULL default 'CURRENT_TIMESTAMP' on update CURRENT_TIMESTAMP,
	crdatetime datetime default '',
	sticky tinyint(1) unsigned default '0',
	PRIMARY KEY (id),
	KEY url (url),
	KEY params (params(255))
);


#
# Table structure for table "link_oldlinks"
#
CREATE TABLE link_oldlinks (
	id int(10) unsigned NOT NULL auto_increment,
	link_id int(10) unsigned NOT NULL default '0',
	url char(255) default '',
	tstamp timestamp NOT NULL default 'CURRENT_TIMESTAMP' on update CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	UNIQUE id (id),
	KEY url (url)
);
