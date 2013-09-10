-- Database definition for Rating
--
-- Part of BlueSpice for MediaWiki
--
-- @author     Patric Wirth <pwirth@hallowelt.biz>
-- @version    $Id: rating.sql 10349 2013-09-10 08:57:06Z pwirth $
-- @package    BlueSpice_Extensions
-- @subpackage Rating
-- @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
-- @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
-- @filesource

CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/bs_rating (
	rat_id			INT		(6)		unsigned	NOT NULL	AUTO_INCREMENT,
	rat_reftype		VARCHAR	(13)				NOT NULL	DEFAULT 'article',
	rat_ref			VARCHAR	(33)				NOT NULL	DEFAULT '0',
	rat_userid		SMALLINT(5)		unsigned	NULL,
	rat_userip		VARCHAR	(15)				NULL,
	rat_value		SMALLINT(3)					NOT NULL	DEFAULT 0,
	rat_created		VARCHAR (14)				NOT NULL	default '',
	rat_touched		VARCHAR (14)				NOT NULL	default '',
	rat_archived 	BOOLEAN						NOT NULL	DEFAULT 0,
	rat_subtype		VARCHAR	(33)				NOT NULL	DEFAULT '',
	PRIMARY KEY (rat_id),
	UNIQUE KEY rat_id (rat_id)
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/rat_userid	ON /*$wgDBprefix*/bs_rating (rat_userid);
CREATE INDEX /*i*/rat_userip	ON /*$wgDBprefix*/bs_rating (rat_userip);
CREATE INDEX /*i*/rat_ref		ON /*$wgDBprefix*/bs_rating (rat_ref);
CREATE INDEX /*i*/rat_reftype	ON /*$wgDBprefix*/bs_rating (rat_reftype);