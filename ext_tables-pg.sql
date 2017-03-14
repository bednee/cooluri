--
-- Generated from mysql2pgsql.perl
-- http://gborg.postgresql.org/project/mysql2psql/
-- (c) 2001 - 2007 Jose M. Duarte, Joseph Speigle
--

-- warnings are printed for drop tables if they do not exist
-- please see http://archives.postgresql.org/pgsql-novice/2004-10/msg00158.php

-- ##############################################################
--
-- Modifying pages table
--
CREATE TABLE  "pages" (
	 "tx_realurl_pathsegment"   varchar(255) default '', 
	 "tx_cooluri_exclude"  smallint CHECK ("tx_cooluri_exclude" >= 0) default '0',
	 "tx_cooluri_excludealways"  smallint CHECK ("tx_cooluri_exclude" >= 0) default '0'
); 
